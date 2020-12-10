<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver;

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
use UnexpectedValueException;
use const DIRECTORY_SEPARATOR;
use function count;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\sprintf;
use function sys_get_temp_dir;

final class Downloader implements DownloaderInterface
{
    private const DOWNLOAD_ENDPOINT = 'https://chromedriver.storage.googleapis.com';
    private const BINARY_LINUX      = 'chromedriver_linux64';
    private const BINARY_MAC        = 'chromedriver_mac64';
    private const BINARY_WINDOWS    = 'chromedriver_win32';

    /** @var Filesystem */
    private $filesystem;

    /** @var HttpClientInterface */
    private $httpClient;

    /** @var Extractor */
    private $archiveExtractor;

    public function __construct(Filesystem $filesystem, HttpClientInterface $httpClient, Extractor $archiveExtractor)
    {
        $this->filesystem       = $filesystem;
        $this->httpClient       = $httpClient;
        $this->archiveExtractor = $archiveExtractor;
    }

    public function supports(Driver $driver) : bool
    {
        return $driver->name()->equals(DriverName::CHROME());
    }

    /**
     * @throws RuntimeException
     */
    public function download(Driver $driver, string $location) : string
    {
        try {
            $archive = $this->downloadArchive($driver);
        } catch (NotImplemented | FilesystemException | IOException | TransportExceptionInterface $exception) {
            throw new RuntimeException('Something went wrong downloading the chromedriver archive.', 0, $exception);
        }

        try {
            $binary = $this->extractArchive($archive);
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
    private function downloadArchive(Driver $driver) : string
    {
        $temporaryFile = $this->filesystem->tempnam(sys_get_temp_dir(), 'chromedriver');

        $response = $this->httpClient->request(
            'GET',
            sprintf(
                '%s/%s/%s.zip',
                self::DOWNLOAD_ENDPOINT,
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
    private function getBinaryName(Driver $driver) : string
    {
        $operatingSystem = $driver->operatingSystem();

        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            return self::BINARY_WINDOWS;
        }

        if ($operatingSystem->equals(OperatingSystem::MACOS())) {
            return self::BINARY_MAC;
        }

        if ($operatingSystem->equals(OperatingSystem::LINUX())) {
            return self::BINARY_LINUX;
        }

        throw NotImplemented::feature(
            sprintf('Downloading %s for %s', $driver->name()->getValue(), $operatingSystem->getValue())
        );
    }

    /**
     * @throws RuntimeException
     * @throws IOException
     */
    private function extractArchive(string $archive) : string
    {
        $unzipLocation  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'chromedriver';
        $extractedFiles = $this->archiveExtractor->extract($archive, $unzipLocation);

        $count = count($extractedFiles);
        if ($count !== 1) {
            throw new UnexpectedValueException(sprintf('Expected exactly one file in the archive. Found %d', $count));
        }

        $file = $this->filesystem->readlink($extractedFiles[0], true);
        if ($file === null) {
            throw new RuntimeException(sprintf('Could not read link %s', $extractedFiles[0]));
        }

        $this->filesystem->remove($archive);

        return $file;
    }

    private function getFilePath(string $location, OperatingSystem $operatingSystem) : string
    {
        $filePath = $location . DIRECTORY_SEPARATOR . 'chromedriver';

        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            $filePath .= '.exe';
        }

        return $filePath;
    }
}
