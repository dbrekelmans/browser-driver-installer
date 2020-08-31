<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use DBrekelmans\BrowserDriverInstaller\Version;

final class Driver
{
    private DriverName $name;
    private Version $version;

    public function __construct(DriverName $name, Version $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function name() : DriverName
    {
        return $this->name;
    }

    public function version() : Version
    {
        return $this->version;
    }
}
