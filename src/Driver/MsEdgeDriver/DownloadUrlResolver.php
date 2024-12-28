<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\MsEdgeDriver;

use DBrekelmans\BrowserDriverInstaller\Driver\DownloadUrlResolver as DownloadUrlResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;

use function sprintf;

final class DownloadUrlResolver implements DownloadUrlResolverInterface
{
    private const DOWNLOAD_ENDPOINT = 'https://msedgedriver.azureedge.net';

    public function byDriver(Driver $driver): string
    {
        return sprintf(
            '%s/%s/%s.zip',
            self::DOWNLOAD_ENDPOINT,
            $driver->version->toBuildString(),
            $this->getBinaryName($driver),
        );
    }

    private function getBinaryName(Driver $driver): string
    {
        // https://msedgewebdriverstorage.z22.web.core.windows.net/?prefix=131.0.2903.112/
        return match ($driver->operatingSystem) {
            OperatingSystem::LINUX => 'edgedriver_linux64',
            OperatingSystem::MACOS => 'edgedriver_mac64',
            OperatingSystem::WINDOWS => 'edgedriver_win32',
        };
    }
}
