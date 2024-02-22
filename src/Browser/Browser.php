<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;

final class Browser
{
    public function __construct(
        public readonly BrowserName $name,
        public readonly Version $version,
        public readonly OperatingSystem $operatingSystem,
    ) {
    }
}
