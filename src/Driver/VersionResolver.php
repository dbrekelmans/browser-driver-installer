<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\Version;
use RuntimeException;

interface VersionResolver
{
    /**
     * @throws RuntimeException If the version could not be resolved.
     * @throws NotImplemented If the browser is not yet supported.
     */
    public function fromBrowser(Browser $browser) : Version;

    public function latest() : Version;

    public function supports(Browser $browser) : bool;
}
