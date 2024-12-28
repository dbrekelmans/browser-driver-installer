<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\MsEdge;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver as PathResolverInterface;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use RuntimeException;

class PathResolver implements PathResolverInterface
{
    public function from(OperatingSystem $operatingSystem): string
    {
        return match ($operatingSystem) {
            OperatingSystem::LINUX => throw new RuntimeException('Not implemented yet.'),
            OperatingSystem::MACOS => '/Applications/Microsoft\ Edge.app',
            OperatingSystem::WINDOWS => 'C:\Program Files (x86)\Microsoft\Edge\Application',
        };
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName === BrowserName::MSEDGE;
    }
}
