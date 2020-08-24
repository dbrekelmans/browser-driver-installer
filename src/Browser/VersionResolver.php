<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use RuntimeException;

/** @internal */
interface VersionResolver
{
    /**
     * @throws RuntimeException If the version could not be resolved.
     * @throws NotImplemented If the operating system is not yet supported.
     */
    public function from(OperatingSystem $operatingSystem, string $path) : Version;

    public function supportedBrowser() : BrowserName;
}
