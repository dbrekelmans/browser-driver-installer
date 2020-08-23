<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\Command;

use BrowserDriverInstaller\Enum\BrowserName;
use BrowserDriverInstaller\Enum\OperatingSystem;
use BrowserDriverInstaller\Enum\OperatingSystemFamily;
use BrowserDriverInstaller\Factory\BrowserFactory;
use BrowserDriverInstaller\ValueObject\Browser;
use BrowserDriverInstaller\ValueObject\Version;
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

use const PHP_OS_FAMILY;

final class InstallCommand extends Command
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
    private BrowserFactory $browserFactory;

    public function __construct(
        string $name,
        HttpClientInterface $httpClient,
        Filesystem $filesystem,
        ZipArchive $zip,
        BrowserFactory $browserFactory
    ) {
        $this->httpClient = $httpClient;
        $this->filesystem = $filesystem;
        $this->zip = $zip;
        $this->browserFactory = $browserFactory;

        parent::__construct($name);
    }

    protected function configure() : void
    {
        $this->setDescription('Installs browser driver to let panther control the browser.');

        $this->addOption(
            self::BROWSER_NAME,
            null,
            InputOption::VALUE_OPTIONAL,
            sprintf(
                'The browser for which to install the driver (%s)',
                implode('|', $this->browserFactory->registeredBrowsers())
            ),
            self::AUTO
        );

        $this->addOption(
            self::DRIVER_VERSION,
            null,
            InputOption::VALUE_OPTIONAL,
            sprintf(
                'The browser driver version to install (%s)',
                implode('|', ['<version>', self::LATEST, self::AUTO])
            ),
            self::AUTO
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
                'The operating system used for installing the correct browser driver (%s)',
                implode('|', OperatingSystem::toArray())
            ),
            OperatingSystem::fromFamily(
                new OperatingSystemFamily(PHP_OS_FAMILY)
            )->getValue()
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);

        $io->note('This command is experimental. Use at your own discretion.');

        $driverVersion = $input->getOption(self::DRIVER_VERSION);
        $operatingSystem = new OperatingSystem($input->getOption(self::OPERATING_SYSTEM));

        $browserName = $input->getOption(self::BROWSER_NAME);
        if ($browserName === self::AUTO) {
            $browserName = $this->resolveBrowserName(); // TODO
        } else {
            $browserName = new BrowserName($browserName);
        }

        $browserPath = $input->getOption(self::BROWSER_PATH);
        if ($browserPath === self::AUTO) {
            $browserPath = $this->resolveBrowserPath($browserName, $operatingSystem);
        }

        if ($driverVersion === self::AUTO) {
            try {
                $browser = $this->browserFactory->createFromNameAndPathAndOperationSystem(
                    $browserName,
                    $browserPath,
                    $operatingSystem
                );

                if ($io->isVerbose()) {
                    $io->writeln(sprintf('%s %s found.', $browser->name(), $browser->version()->toBuildString()));
                }

                $driverVersion = $this->getMatchingChromeDriverVersion($browser);
            } catch (Throwable $exception) {
                return $this->fail(
                    $io,
                    sprintf(
                        'Could not determine %s version. Specify the %s path with "--%s" or manually specify a driver version with "--%s".',
                        $browserName->getValue(),
                        $browserName->getValue(),
                        self::BROWSER_PATH,
                        self::DRIVER_VERSION
                    ),
                    $exception
                );
            }
        } elseif ($driverVersion === self::LATEST) {
            try {
                // TODO: refactor to get latest driver version for $browserName
                $driverVersion = $this->getLatestChromeDriverVersion();
            } catch (Throwable $exception) {
                return $this->fail($io, 'Unable to get the latest chrome version from API endpoint.', $exception);
            }
        } else {
            try {
                $driverVersion = Version::fromString($driverVersion);
            } catch (Throwable $exception) {
                return $this->fail(
                    $io,
                    'Unable to parse provided driver version.',
                    $exception
                );
            }
        }

        if ($io->isVerbose()) {
            $io->writeln(sprintf('Downloading %s %s.', '<driver-name>', $driverVersion->toBuildString()));
        }

        try {
            $this->downloadChromeDriverZip(
                $driverVersion,
                $operatingSystem,
                $io->createProgressBar()
            );
            $io->writeln(' - Download complete.');
        } catch (Throwable $exception) {
            return $this->fail(
                $io,
                sprintf('Unable to download %s %s.', '<driver-name>', $driverVersion->toString()),
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

        $io->success(sprintf('Chrome driver %s successfully installed.', $driverVersion->toBuildString()));

        return self::SUCCESS;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getMatchingChromeDriverVersion(Browser $browser) : Version
    {
        $response = $this->httpClient->request(
            'GET',
            sprintf('%s_%s', self::CHROMEDRIVER_API_VERSION_ENDPOINT, $browser->version()->toString())
        );

        return Version::fromString($response->getContent());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getLatestChromeDriverVersion() : Version
    {
        $response = $this->httpClient->request('GET', self::CHROMEDRIVER_API_VERSION_ENDPOINT);

        return Version::fromString($response->getContent());
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

    private function resolveBrowserName() : BrowserName
    {
        // TODO

        return BrowserName::GOOGLE_CHROME();
    }

    private function resolveBrowserPath(BrowserName $browserName, OperatingSystem $operatingSystem) : string
    {
        // TODO

        return 'google-chrome';
    }
}
