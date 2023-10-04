<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;

use function get_class;
use function Safe\sprintf;

final class DownloaderFactory
{
    /**
     * @psalm-var array<string, Downloader>
     * @var Downloader[]
     */
    private array $downloaders = [];

    public function createFromDriver(Driver $driver): Downloader
    {
        foreach ($this->downloaders as $downloader) {
            if ($downloader->supports($driver)) {
                return $downloader;
            }
        }

        throw NotImplemented::feature(sprintf('Downloader for %s', $driver->name()->getValue()));
    }

    public function register(Downloader $downloader): void
    {
        $this->downloaders[get_class($downloader)] = $downloader;
    }
}
