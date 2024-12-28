<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\MsEdge;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver as PathResolverInterface;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;

class PathResolver implements PathResolverInterface
{
    public function from(OperatingSystem $operatingSystem): string
    {
        return match ($operatingSystem) {
            // TODO
//            OperatingSystem::LINUX => 'chromium',
            OperatingSystem::MACOS => '/Applications/Microsoft\ Edge.app',
            // TODO
//            OperatingSystem::WINDOWS => 'C:\\Program Files (x86)\\Chromium\\Application\\chrome.exe',
        };
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName === BrowserName::MSEDGE;
    }
}
