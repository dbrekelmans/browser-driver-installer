<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;

use function sprintf;

final class DownloaderFactory
{
    /** @var array<string, Downloader> */
    private array $downloaders = [];

    public function createFromDriver(Driver $driver): Downloader
    {
        foreach ($this->downloaders as $downloader) {
            if ($downloader->supports($driver)) {
                return $downloader;
            }
        }

        throw NotImplemented::feature(sprintf('Downloader for %s %s', $driver->name->value, $driver->cpuArchitecture->value));
    }

    public function register(Downloader $downloader, string|null $identifier = null): void
    {
        $this->downloaders[$identifier ?? $downloader::class] = $downloader;
    }
}
