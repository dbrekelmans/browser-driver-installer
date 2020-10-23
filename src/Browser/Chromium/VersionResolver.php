<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\Chromium;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolver as VersionResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolverChrome;
use function Safe\sprintf;

final class VersionResolver extends VersionResolverChrome implements VersionResolverInterface
{
    protected function getMacOSCommandLineForVersion(string $path) : string
    {
        return sprintf('%s/Contents/MacOS/Chromium --version', $path);
    }

    public function supports(BrowserName $browserName) : bool
    {
        return $browserName->equals(BrowserName::CHROMIUM());
    }
}
