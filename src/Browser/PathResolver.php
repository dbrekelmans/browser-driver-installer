<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use RuntimeException;

interface PathResolver
{
    /**
     * @throws RuntimeException If the path could not be resolved.
     * @throws NotImplemented If the operating system is not yet supported.
     */
    public function from(OperatingSystem $operatingSystem) : string;

    public function supports(BrowserName $browserName) : bool;
}
