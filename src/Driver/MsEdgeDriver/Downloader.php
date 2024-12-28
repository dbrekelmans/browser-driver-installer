<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\MsEdgeDriver;

use DBrekelmans\BrowserDriverInstaller\Archive\Extractor;
use DBrekelmans\BrowserDriverInstaller\Driver\Downloader as DownloaderInterface;
use DBrekelmans\BrowserDriverInstaller\Driver\DownloadUrlResolver;
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

use function in_array;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

final class Downloader implements DownloaderInterface
{
    private string $tempDir;

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly HttpClientInterface $httpClient,
        private readonly Extractor $archiveExtractor,
        private readonly DownloadUrlResolver $downloadUrlResolver,
    ) {
        $this->tempDir = sys_get_temp_dir();
    }

    public function supports(Driver $driver): bool
    {
        return $driver->name === DriverName::MSEDGE;
    }

    /** @throws RuntimeException */
    public function download(Driver $driver, string $location): string
    {
        try {
            $archive = $this->downloadArchive($driver);
        } catch (NotImplemented | FilesystemException | IOException | TransportExceptionInterface $exception) {
            throw new RuntimeException('Something went wrong downloading the msedgedriver archive.', 0, $exception);
        }

        try {
            $binary = $this->extractArchive($archive, $driver);
        } catch (IOException | RuntimeException $exception) {
            throw new RuntimeException('Something went wrong extracting the msedgedriver archive.', 0, $exception);
        }

        $filePath = $this->getFilePath($location, $driver->operatingSystem);

        if (! $this->filesystem->exists($location)) {
            $this->filesystem->mkdir($location);
        }

        try {
            $this->filesystem->rename($binary, $filePath, true);
        } catch (IOException $exception) {
            throw new RuntimeException(
                sprintf('Something went wrong moving the msedgedriver to %s.', $location),
                0,
                $exception,
            );
        }

        $mode = 0755;
        try {
            $this->filesystem->chmod($filePath, $mode);
        } catch (IOException $exception) {
            throw new RuntimeException(
                sprintf('Something went wrong setting the permissions of the msedgedriver to %d.', $mode),
                0,
                $exception,
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
        $temporaryFile = $this->filesystem->tempnam($this->tempDir, 'msedgedriver', '.zip');

        $response = $this->httpClient->request(
            'GET',
            $this->downloadUrlResolver->byDriver($driver),
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
     * @throws RuntimeException
     * @throws IOException
     */
    private function extractArchive(string $archive, Driver $driver): string
    {
        $unzipLocation  = $this->tempDir . DIRECTORY_SEPARATOR . 'msedgedriver';
        $extractedFiles = $this->archiveExtractor->extract($archive, $unzipLocation);
        $filePath = $this->getFilePath($unzipLocation, $driver->operatingSystem);

        if (
            ! in_array(
                $filePath,
                $extractedFiles,
                true,
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
        $fileName = 'msedgedriver';

        if ($operatingSystem === OperatingSystem::WINDOWS) {
            $fileName .= '.exe';
        }

        return $fileName;
    }

    /**
     * @param  string[] $extractedFiles
     *
     * @return string[]
     */
    public function cleanArchiveStructure(Driver $driver, string $unzipLocation, array $extractedFiles): array
    {
        $archiveDirectory = $this->getArchiveDirectory($driver->operatingSystem);
        $filename         = $this->getFileName($driver->operatingSystem);
        $this->filesystem->rename(
            $unzipLocation . DIRECTORY_SEPARATOR . $archiveDirectory . $filename,
            $unzipLocation . DIRECTORY_SEPARATOR . $filename,
            true,
        );

        return str_replace($archiveDirectory, '', $extractedFiles);
    }

    private function getArchiveDirectory(OperatingSystem $operatingSystem): string
    {
        return match ($operatingSystem) {
            OperatingSystem::LINUX => 'msedgedriver-linux64/',
            OperatingSystem::WINDOWS => 'msedgedriver-win32/', // This weirdly contains a forward slash on windows
            OperatingSystem::MACOS => 'msedgedriver-mac-x64/',
        };
    }
}
