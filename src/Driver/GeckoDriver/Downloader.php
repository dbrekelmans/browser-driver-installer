<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\GeckoDriver;

use DBrekelmans\BrowserDriverInstaller\Archive\Extractor;
use DBrekelmans\BrowserDriverInstaller\Driver\Downloader as DownloaderInterface;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use RuntimeException;
use Safe\Exceptions\FilesystemException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use const DIRECTORY_SEPARATOR;
use function basename;
use function dirname;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\sprintf;
use function strpos;
use function sys_get_temp_dir;

final class Downloader implements DownloaderInterface
{
    private const DOWNLOAD_PATH_OS_PART_WINDOWS = 'win64';
    private const DOWNLOAD_PATH_OS_PART_MACOS   = 'macos';
    private const DOWNLOAD_PATH_OS_PART_LINUX   = 'linux64';
    private const DOWNLOAD_BASE_PATH            = 'https://github.com/mozilla/geckodriver/releases/download/';

    /** @var Filesystem  */
    private $filesystem;

    /** @var HttpClientInterface  */
    private $httpClient;

    /** @var Extractor */
    private $archiveExtractor;

    public function __construct(Filesystem $filesystem, HttpClientInterface $httpClient, Extractor $archiveExtractor)
    {
        $this->filesystem       = $filesystem;
        $this->httpClient       = $httpClient;
        $this->archiveExtractor = $archiveExtractor;
    }

    public function download(Driver $driver, string $location) : string
    {
        try {
            $archive = $this->downloadArchive($driver);
        } catch (NotImplemented | FilesystemException | IOException | TransportExceptionInterface $exception) {
            throw new RuntimeException('Something went wrong downloading the geckodriver archive.', 0, $exception);
        }

        try {
            $binary = $this->extractArchive($archive);
        } catch (IOException | RuntimeException $exception) {
            throw new RuntimeException('Something went wrong extracting the geckodriver archive.', 0, $exception);
        }

        if (! $this->filesystem->exists($location)) {
            $this->filesystem->mkdir($location);
        }

        $filePath = $location . DIRECTORY_SEPARATOR . basename($binary);

        try {
            $this->filesystem->rename($binary, $filePath, true);
        } catch (IOException $exception) {
            throw new RuntimeException(
                sprintf('Something went wrong moving the geckodriver to %s.', $location),
                0,
                $exception
            );
        }

        return $filePath;
    }

    public function supports(Driver $driver) : bool
    {
        return $driver->name()->equals(DriverName::GECKO());
    }

    /**
     * @throws FilesystemException
     * @throws TransportExceptionInterface
     */
    private function downloadArchive(Driver $driver) : string
    {
        $temporaryFile = $this->filesystem->tempnam(sys_get_temp_dir(), 'geckodriver') . $this->getArchiveExtension($driver);

        $response = $this->httpClient->request('GET', $this->getDownloadPath($driver));

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
    private function getDownloadPath(Driver $driver) : string
    {
        return self::DOWNLOAD_BASE_PATH . sprintf(
            'v%s/geckodriver-v%s-%s%s',
            $driver->version()->toString(),
            $driver->version()->toString(),
            $this->getOsForDownloadPath($driver),
            $this->getArchiveExtension($driver)
        );
    }

    /**
     * @throws NotImplemented
     */
    private function getOsForDownloadPath(Driver $driver) : string
    {
        $operatingSystem = $driver->operatingSystem();

        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            return self::DOWNLOAD_PATH_OS_PART_WINDOWS;
        }

        if ($operatingSystem->equals(OperatingSystem::MACOS())) {
            return self::DOWNLOAD_PATH_OS_PART_MACOS;
        }

        if ($operatingSystem->equals(OperatingSystem::LINUX())) {
            return self::DOWNLOAD_PATH_OS_PART_LINUX;
        }

        throw NotImplemented::feature(
            sprintf('Downloading %s for %s', $driver->name()->getValue(), $operatingSystem->getValue())
        );
    }

    private function extractArchive(string $archive) : string
    {
        $extractedFiles = $this->archiveExtractor->extract($archive, dirname($archive));

        foreach ($extractedFiles as $filename) {
            if (strpos($filename, 'geckodriver') !== false) {
                return $filename;
            }
        }

        throw new RuntimeException(sprintf('Archive %s does not contain any geckodriver file', $archive));
    }

    private function getArchiveExtension(Driver $driver) : string
    {
        if ($driver->operatingSystem()->equals(OperatingSystem::WINDOWS())) {
            return '.zip';
        }

        return '.tar.gz';
    }
}
