<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\Command;

use BrowserDriverInstaller\Enum\OperatingSystemFamily;
use BrowserDriverInstaller\ValueObject\Browser;
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
use UnexpectedValueException;
use ZipArchive;

final class InstallCommand extends Command
{
    public const SUCCESS = 0;
    public const FAILURE = 1;

    private const BROWSER = 'browser';
    private const DRIVER_VERSION = 'driver-version';
    private const BROWSER_PATH = 'browser-path';
    private const LATEST = 'latest';
    private const AUTO = 'auto';
    private const OS_FAMILY = 'os-family';
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

    public function __construct(
        string $name,
        HttpClientInterface $httpClient,
        Filesystem $filesystem,
        ZipArchive $zip
    ) {
        parent::__construct($name);

        $this->httpClient = $httpClient;
        $this->filesystem = $filesystem;
        $this->zip = $zip;
    }

    protected function configure() : void
    {
        $this->setDescription('Installs browser driver to let panther control the browser.');

        $this->addOption(
            self::BROWSER,
            null,
            InputOption::VALUE_REQUIRED,
            sprintf(
                'The browser for which to install the driver (%s)',
                implode('|', [
                    Browser::GOOGLE_CHROME,
                    Browser::CHROMIUM,
                    Browser::FIREFOX
                ])
            ),
            Browser::GOOGLE_CHROME
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
                'The path to the browser to determine the correct driver version when --%s=%s',
                self::DRIVER_VERSION,
                self::AUTO
            ),
            Browser::GOOGLE_CHROME
        );

        // TODO: Refactor to provide less confusing options (Windows|MacOS|Linux)
        $this->addOption(
            self::OS_FAMILY,
            null,
            InputOption::VALUE_OPTIONAL,
            sprintf(
                'The OS family used for installing the correct browser driver (%s)',
                implode('|', [
                    OperatingSystemFamily::WINDOWS,
                    OperatingSystemFamily::BSD,
                    OperatingSystemFamily::DARWIN,
                    OperatingSystemFamily::SOLARIS,
                    OperatingSystemFamily::LINUX,
                ])
            ),
            PHP_OS_FAMILY
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);

        $io->note('This command is experimental. Use at your own discretion.');

        $browser = $input->getOption(self::BROWSER);
        $driverVersion = $input->getOption(self::DRIVER_VERSION);
        $browserPath = $input->getOption(self::BROWSER_PATH);
        $osFamily = $input->getOption(self::OS_FAMILY);

        if ($driverVersion === self::AUTO) {
            try {
                $browserVersion = (new BrowserVersionResolverFactory())->getResolver(
                    new Browser($browser, $browserPath, $osFamily)
                )->resolveVersion();

                if ($io->isVerbose()) {
                    $io->writeln(sprintf('Browser version "%s" found.', $browserVersion));
                }

                // TODO: refactor to get matching driver version for $browser
                $driverVersion = $this->getMatchingChromeDriverVersion($browserVersion);
            } catch (Throwable $exception) {
                return $this->fail(
                    $io,
                    sprintf(
                        'Could not determine browser version. Specify the browser path with "--%s" or manually specify a driver version with "--%s".',
                        self::BROWSER_PATH,
                        self::DRIVER_VERSION
                    ),
                    $exception
                );
            }
        } else if ($driverVersion === self::LATEST) {
            try {
                // TODO: refactor to get latest driver version for $browser
                $driverVersion = $this->getLatestChromeDriverVersion();
            } catch (Throwable $exception) {
                return $this->fail($io, 'Unable to get the latest chrome version from API endpoint.', $exception);
            }
        } else {
            try {
                $driverVersion = $this->parseDriverVersion($driverVersion);
            } catch (Throwable $exception) {
                return $this->fail(
                    $io,
                    'Unable to parse provided driver version.',
                    $exception
                );
            }
        }

        if ($io->isVerbose()) {
            $io->writeln(sprintf('Downloading browser driver version "%s".', $driverVersion));
        }

        try {
            $this->downloadChromeDriverZip(
                $driverVersion,
                $osFamily,
                $io->createProgressBar()
            );
            $io->writeln(' Download complete.');

        } catch (Throwable $exception) {
            return $this->fail(
                $io,
                sprintf('Unable to download browser driver version "%s".', $driverVersion),
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

        $io->success(sprintf('Chrome driver %s successfully installed.', $driverVersion));

        return self::SUCCESS;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getMatchingChromeDriverVersion(string $chromeVersion) : string
    {
        $response = $this->httpClient->request(
            'GET',
            sprintf('%s_%s', self::CHROMEDRIVER_API_VERSION_ENDPOINT, $chromeVersion)
        );

        return $this->parseDriverVersion($response->getContent());
    }

    /**
     * @throws UnexpectedValueException
     */
    private function parseDriverVersion(string $driverVersion) : string
    {
        $success = preg_match('/\d+\.\d+\.\d+\.\d+/', $driverVersion, $output);

        if ($success === false) {
            throw new UnexpectedValueException(preg_last_error_msg());
        }

        if ($success === 0) {
            throw new RuntimeException(
                sprintf('Given driver version "%s" could not be parsed.', $driverVersion)
            );
        }

        return reset($output);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getLatestChromeDriverVersion() : string
    {
        $response = $this->httpClient->request('GET', self::CHROMEDRIVER_API_VERSION_ENDPOINT);

        return $this->parseDriverVersion($response->getContent());
    }

    private function getChromeDriverBinaryName(string $osFamily) : string
    {
        if ($osFamily === OperatingSystemFamily::WINDOWS) {
            return self::CHROMEDRIVER_BINARY_WINDOWS;
        }

        if ($osFamily === OperatingSystemFamily::DARWIN) {
            return self::CHROMEDRIVER_BINARY_MAC;
        }

        return self::CHROMEDRIVER_BINARY_LINUX;
    }

    /**
     * @throws RuntimeException
     * @throws TransportExceptionInterface
     */
    private function downloadChromeDriverZip(
        string $chromeDriverVersion,
        string $osFamily,
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
                $chromeDriverVersion,
                $this->getChromeDriverBinaryName($osFamily)
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
