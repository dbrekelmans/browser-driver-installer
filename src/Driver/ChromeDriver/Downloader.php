<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Archive\Extractor;
use DBrekelmans\BrowserDriverInstaller\Driver\Downloader as DownloaderInterface;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\Exception\Unsupported;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use RuntimeException;
use Safe\Exceptions\FilesystemException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

use function in_array;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\sprintf;
use function str_replace;
use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

final class Downloader implements DownloaderInterface
{
    private const DOWNLOAD_ENDPOINT      = 'https://chromedriver.storage.googleapis.com';
    private const BINARY_LINUX           = 'chromedriver_linux64';
    private const BINARY_MAC             = 'chromedriver_mac64';
    private const BINARY_WINDOWS         = 'chromedriver_win32';
    private const DOWNLOAD_ENDPOINT_JSON = 'https://edgedl.me.gvt1.com/edgedl/chrome/chrome-for-testing';
    private const BINARY_LINUX_JSON      = 'chromedriver-linux64';
    private const BINARY_MAC_JSON        = 'chromedriver-mac-x64';
    private const BINARY_WINDOWS_JSON    = 'chromedriver-win32';


    /** @var Filesystem */
    private $filesystem;

    /** @var HttpClientInterface */
    private $httpClient;

    /** @var Extractor */
    private $archiveExtractor;

    /** @var string */
    private $tempDir;

    public function __construct(Filesystem $filesystem, HttpClientInterface $httpClient, Extractor $archiveExtractor)
    {
        $this->filesystem       = $filesystem;
        $this->httpClient       = $httpClient;
        $this->archiveExtractor = $archiveExtractor;
        $this->tempDir          = sys_get_temp_dir();
    }

    public function supports(Driver $driver): bool
    {
        return $driver->name()->equals(DriverName::CHROME());
    }

    /**
     * @throws RuntimeException
     */
    public function download(Driver $driver, string $location): string
    {
        try {
            $archive = $this->downloadArchive($driver);
        } catch (NotImplemented | FilesystemException | IOException | TransportExceptionInterface $exception) {
            throw new RuntimeException('Something went wrong downloading the chromedriver archive.', 0, $exception);
        }

        try {
            $binary = $this->extractArchive($archive, $driver);
        } catch (IOException | RuntimeException $exception) {
            throw new RuntimeException('Something went wrong extracting the chromedriver archive.', 0, $exception);
        }

        $filePath = $this->getFilePath($location, $driver->operatingSystem());

        if (! $this->filesystem->exists($location)) {
            $this->filesystem->mkdir($location);
        }

        try {
            $this->filesystem->rename($binary, $filePath, true);
        } catch (IOException $exception) {
            throw new RuntimeException(
                sprintf('Something went wrong moving the chromedriver to %s.', $location),
                0,
                $exception
            );
        }

        $mode = 0755;
        try {
            $this->filesystem->chmod($filePath, $mode);
        } catch (IOException $exception) {
            throw new RuntimeException(
                sprintf('Something went wrong setting the permissions of the chromedriver to %d.', $mode),
                0,
                $exception
            );
        }

        return $filePath;
    }

    /**
     * @throws NotImplemented
     * @throws TransportExceptionInterface
     * @throws FilesystemException
     * @throws IOException
     */
    private function downloadArchive(Driver $driver): string
    {
        $temporaryFile = $this->filesystem->tempnam($this->tempDir, 'chromedriver', '.zip');

        $response = $this->httpClient->request(
            'GET',
            sprintf(
                '%s/%s/%s.zip',
                $this->getDownloadEndpoint($driver),
                $driver->version()->toBuildString(),
                $this->getBinaryName($driver)
            )
        );

        $fileHandler = fopen($temporaryFile, 'wb');

        try {
            foreach ($this->httpClient->stream($response) as $chunk) {
                fwrite($fileHandler, $chunk->getContent());
            }
        } catch (TransportExceptionInterface $exception) {
            throw $exception;
        } finally {
            fclose($fileHandler);
        }

        return $temporaryFile;
    }

    /**
     * @throws NotImplemented
     */
    private function getBinaryName(Driver $driver): string
    {
        $operatingSystem = $driver->operatingSystem();
        if ($this->isJsonVersion($driver)) {
            if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
                return 'win32/' . self::BINARY_WINDOWS_JSON;
            }

            if ($operatingSystem->equals(OperatingSystem::MACOS())) {
                return 'mac-x64/' . self::BINARY_MAC_JSON;
            }

            if ($operatingSystem->equals(OperatingSystem::LINUX())) {
                return 'linux64/' . self::BINARY_LINUX_JSON;
            }
        } else {
            if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
                return self::BINARY_WINDOWS;
            }

            if ($operatingSystem->equals(OperatingSystem::MACOS())) {
                return self::BINARY_MAC;
            }

            if ($operatingSystem->equals(OperatingSystem::LINUX())) {
                return self::BINARY_LINUX;
            }
        }

        throw NotImplemented::feature(
            sprintf('Downloading %s for %s', $driver->name()->getValue(), $operatingSystem->getValue())
        );
    }

    /**
     * @throws RuntimeException
     * @throws IOException
     */
    private function extractArchive(string $archive, Driver $driver): string
    {
        $unzipLocation  = $this->tempDir . DIRECTORY_SEPARATOR . 'chromedriver';
        $extractedFiles = $this->archiveExtractor->extract($archive, $unzipLocation);
        if ($this->isJsonVersion($driver)) {
            $extractedFiles = $this->cleanArchiveStructure($driver, $unzipLocation, $extractedFiles);
        }

        $filePath = $this->getFilePath($unzipLocation, $driver->operatingSystem());

        if (
            ! in_array(
                $filePath,
                $extractedFiles,
                true
            )
        ) {
            throw new UnexpectedValueException(sprintf('Could not find "%s" in the extracted files.', $filePath));
        }

        $file = $this->filesystem->readlink($filePath, true);
        if ($file === null) {
            throw new RuntimeException(sprintf('Could not read link %s', $filePath));
        }

        $this->filesystem->remove($archive);

        return $file;
    }

    private function getFilePath(string $location, OperatingSystem $operatingSystem): string
    {
        return $location . DIRECTORY_SEPARATOR . $this->getFileName($operatingSystem);
    }

    private function getFileName(OperatingSystem $operatingSystem): string
    {
        $fileName = 'chromedriver';

        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            $fileName .= '.exe';
        }

        return $fileName;
    }

    private function isJsonVersion(Driver $driver): bool
    {
        return $driver->version()->major() >= VersionResolver::MAJOR_VERSION_ENDPOINT_BREAKPOINT;
    }

    private function getDownloadEndpoint(Driver $driver): string
    {
        return $this->isJsonVersion($driver) ? self::DOWNLOAD_ENDPOINT_JSON : self::DOWNLOAD_ENDPOINT;
    }

    /**
     * @param  string[] $extractedFiles
     *
     * @return string[]
     */
    public function cleanArchiveStructure(Driver $driver, string $unzipLocation, array $extractedFiles): array
    {
        $archiveDirectory = $this->getArchiveDirectory($driver->operatingSystem());
        $filename         = $this->getFileName($driver->operatingSystem());
        $this->filesystem->rename(
            $unzipLocation . DIRECTORY_SEPARATOR . $archiveDirectory . $filename,
            $unzipLocation . DIRECTORY_SEPARATOR . $filename,
            true
        );

        return str_replace($archiveDirectory, '', $extractedFiles);
    }

    private function getArchiveDirectory(OperatingSystem $operatingSystem): string
    {
        switch ($operatingSystem->getValue()) {
            case OperatingSystem::LINUX:
                return self::BINARY_LINUX_JSON . DIRECTORY_SEPARATOR;

            case OperatingSystem::WINDOWS:
                return self::BINARY_WINDOWS_JSON . '/';

            case OperatingSystem::MACOS:
                return self::BINARY_MAC_JSON . DIRECTORY_SEPARATOR;

            default:
                throw new Unsupported('Operating system is not supported');
        }
    }
}
