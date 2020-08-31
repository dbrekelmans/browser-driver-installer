<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command;

use DBrekelmans\BrowserDriverInstaller\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverFactory;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\Family;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;
use ZipArchive;

use function implode;
use function sprintf;

use const PHP_OS_FAMILY;

final class DriverCommand extends Command
{
    public const SUCCESS = 0;
    public const FAILURE = 1;

    private const BROWSER_NAME = 'browser';
    private const DRIVER_VERSION = 'driver-version';
    private const BROWSER_PATH = 'browser-path';
    private const LATEST = 'latest';
    private const AUTO = 'auto';
    private const OPERATING_SYSTEM = 'os';
    private const CHROMEDRIVER_API_URL = 'https://chromedriver.storage.googleapis.com';
    private const CHROMEDRIVER_API_VERSION_ENDPOINT = self::CHROMEDRIVER_API_URL . '/LATEST_RELEASE';
    private const BIN_DIRECTORY = __DIR__ . '/../../bin';
    private const DOWNLOAD_FILE_LOCATION = self::BIN_DIRECTORY . '/chromedriver.zip';
    private const CHROMEDRIVER_BINARY_LINUX = 'chromedriver_linux64';
    private const CHROMEDRIVER_BINARY_MAC = 'chromedriver_mac64';
    private const CHROMEDRIVER_BINARY_WINDOWS = 'chromedriver_win32';

    private HttpClientInterface $httpClient;
    private Filesystem $filesystem;
    private ZipArchive $zip;
    private Browser\BrowserFactory $browserFactory;
    private DriverFactory $driverFactory;

    public function __construct(
        HttpClientInterface $httpClient,
        Filesystem $filesystem,
        ZipArchive $zip,
        Browser\BrowserFactory $browserFactory,
        DriverFactory $driverFactory
    ) {
        $this->httpClient = $httpClient;
        $this->filesystem = $filesystem;
        $this->zip = $zip;
        $this->browserFactory = $browserFactory;
        $this->driverFactory = $driverFactory;

        parent::__construct('driver');
    }

    protected function configure() : void
    {
        $this->setDescription('Helps you install the appropriate browser driver.');

        // TODO: Add auto option. Use browser_path to determine browser name.
        $this->addOption(
            self::BROWSER_NAME,
            null,
            InputOption::VALUE_REQUIRED,
            sprintf(
                'The browser for which to install the driver (%s)',
                implode('|', BrowserName::toArray())
            ),
            BrowserName::GOOGLE_CHROME
        );

        $this->addOption(
            self::BROWSER_PATH,
            null,
            InputOption::VALUE_OPTIONAL,
            sprintf(
                'The path to the browser to determine the correct driver version when --%s=%s (%s)',
                self::DRIVER_VERSION,
                self::AUTO,
                implode('|', ['<path-to-browser>', self::AUTO])
            ),
            self::AUTO
        );

        $this->addOption(
            self::OPERATING_SYSTEM,
            null,
            InputOption::VALUE_OPTIONAL,
            sprintf(
                'The operating system for which to install the driver (%s)',
                implode('|', array_merge(OperatingSystem::toArray(), [self::AUTO]))
            ),
            self::AUTO
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('This command is experimental.');

        $operatingSystem = $input->getOption(self::OPERATING_SYSTEM);
        if ($operatingSystem === self::AUTO) {
            $operatingSystem = OperatingSystem::fromFamily(
                new Family(PHP_OS_FAMILY)
            );
        } else {
            $operatingSystem = new OperatingSystem($operatingSystem);
        }

        $browserName = new BrowserName($input->getOption(self::BROWSER_NAME));

        $browserPath = $input->getOption(self::BROWSER_PATH);
        if ($browserPath === self::AUTO) {
            $browser = $this->browserFactory->createFromNameAndOperatingSystem($browserName, $operatingSystem);
        } else {
            $browser = $this->browserFactory->createFromNameOperatingSystemAndPath(
                $browserName,
                $operatingSystem,
                $browserPath
            );
        }

        if ($io->isVerbose()) {
            $io->writeln(sprintf('Found %s %s.', $browser->name()->getValue(), $browser->version()->toBuildString()));
        }

        $driver = $this->driverFactory->createFromBrowser($browser);

        if ($io->isVerbose()) {
            $io->writeln(
                sprintf('Downloading %s %s.', $driver->name()->getValue(), $driver->version()->toBuildString())
            );
        }

        try {
            $this->downloadChromeDriverZip(
                $driver->version(),
                $operatingSystem,
                $io->createProgressBar()
            );
            $io->writeln(' - Download complete.');
        } catch (Throwable $exception) {
            return $this->fail(
                $io,
                sprintf('Unable to download %s %s.', $driver->name()->getValue(), $driver->version()->toString()),
                $exception
            );
        }

        if ($io->isVerbose()) {
            $io->writeln('Extracting downloaded zip archive.');
        }

        try {
            $this->unzipChromeDriverArchive();
        } catch (Throwable $exception) {
            return $this->fail(
                $io,
                sprintf('Unable to unzip the downloaded file at "%s".', self::DOWNLOAD_FILE_LOCATION),
                $exception
            );
        }

        try {
            $this->removeChromeDriverArchive();
        } catch (Throwable $exception) {
            return $this->fail(
                $io,
                sprintf('Unable to remove the downloaded file at "%s".', self::DOWNLOAD_FILE_LOCATION),
                $exception
            );
        }

        $io->success(sprintf('Chrome driver %s successfully installed.', $driver->version()->toBuildString()));

        return self::SUCCESS;
    }

    private function getChromeDriverBinaryName(OperatingSystem $operatingSystem) : string
    {
        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            return self::CHROMEDRIVER_BINARY_WINDOWS;
        }

        if ($operatingSystem->equals(OperatingSystem::MACOS())) {
            return self::CHROMEDRIVER_BINARY_MAC;
        }

        return self::CHROMEDRIVER_BINARY_LINUX;
    }

    /**
     * @throws RuntimeException
     * @throws TransportExceptionInterface
     */
    private function downloadChromeDriverZip(
        Version $chromeDriverVersion,
        OperatingSystem $operatingSystem,
        ProgressBar $progressBar
    ) : void {
        if (!$this->filesystem->exists(self::BIN_DIRECTORY)) {
            $this->filesystem->mkdir(self::BIN_DIRECTORY);
        }

        $response = $this->httpClient->request(
            'GET',
            sprintf(
                '%s/%s/%s.zip',
                self::CHROMEDRIVER_API_URL,
                $chromeDriverVersion->toBuildString(),
                $this->getChromeDriverBinaryName($operatingSystem)
            )
        );

        $fileHandler = fopen(self::DOWNLOAD_FILE_LOCATION, 'wb');
        if ($fileHandler === false) {
            throw new RuntimeException(sprintf('Could not open file handler for "%s".', self::DOWNLOAD_FILE_LOCATION));
        }

        try {
            foreach ($this->httpClient->stream($response) as $chunk) {
                fwrite($fileHandler, $chunk->getContent());
                $progressBar->advance();
            }
        } catch (TransportExceptionInterface $exception) {
            throw $exception;
        } finally {
            fclose($fileHandler);

            $progressBar->finish();
        }
    }

    /**
     * @throws RuntimeException
     */
    private function unzipChromeDriverArchive() : void
    {
        $success = $this->zip->open(self::DOWNLOAD_FILE_LOCATION);

        if ($success !== true) {
            throw new RuntimeException(sprintf('Could not open zip archive at "%s".', self::DOWNLOAD_FILE_LOCATION));
        }

        $success = $this->zip->extractTo(self::BIN_DIRECTORY);

        if ($success !== true) {
            throw new RuntimeException(
                sprintf(
                    'Could not extract zip archive at "%s" to "%s".',
                    self::DOWNLOAD_FILE_LOCATION,
                    self::BIN_DIRECTORY
                )
            );
        }

        $success = $this->zip->close();

        if ($success !== true) {
            throw new RuntimeException(sprintf('Could not close zip archive at "%s".', self::DOWNLOAD_FILE_LOCATION));
        }
    }

    private function removeChromeDriverArchive() : void
    {
        $this->filesystem->remove(self::DOWNLOAD_FILE_LOCATION);
    }

    private function fail($io, string $message, Throwable $exception) : int
    {
        $io->error($message);

        if ($io->isDebug()) {
            $io->writeln($exception->getMessage());
        }

        return self::FAILURE;
    }
}
