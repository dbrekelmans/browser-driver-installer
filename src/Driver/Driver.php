<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;

final class Driver
{
    private DriverName $name;
    private Version $version;
    private OperatingSystem $operatingSystem;

    public function __construct(DriverName $name, Version $version, OperatingSystem $operatingSystem)
    {
        $this->name = $name;
        $this->version = $version;
        $this->operatingSystem = $operatingSystem;
    }

    public function name(): DriverName
    {
        return $this->name;
    }

    public function version(): Version
    {
        return $this->version;
    }

    public function operatingSystem(): OperatingSystem
    {
        return $this->operatingSystem;
    }
}
