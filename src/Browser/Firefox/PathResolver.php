<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\Firefox;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver as PathResolverInterface;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;


final class PathResolver implements PathResolverInterface
{
    public function from(OperatingSystem $operatingSystem): string
    {
        return match ($operatingSystem) {
            OperatingSystem::LINUX => 'firefox',
            OperatingSystem::MACOS => '/Applications/Firefox.app',
            OperatingSystem::WINDOWS => 'C:\\Program Files\\Mozilla Firefox\\firefox',
        };
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName=== BrowserName::FIREFOX;
    }
}
