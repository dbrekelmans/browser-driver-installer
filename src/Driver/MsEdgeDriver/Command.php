<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\MsEdgeDriver;

use DBrekelmans\BrowserDriverInstaller\Command\DriverCommand;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;

final class Command extends DriverCommand
{
    protected static function driverName(): DriverName
    {
        return DriverName::MSEDGE;
    }
}
