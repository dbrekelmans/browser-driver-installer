<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use DBrekelmans\BrowserDriverInstaller\Cpu\CpuArchitecture;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;

final class Driver
{
    public function __construct(
        public readonly DriverName $name,
        public readonly Version $version,
        public readonly OperatingSystem $operatingSystem,
        public readonly CpuArchitecture $cpuArchitecture,
    ) {
    }
}
