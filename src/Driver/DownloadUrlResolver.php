<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

interface DownloadUrlResolver
{
    public function byDriver(Driver $driver, string $binaryName): string;
}
