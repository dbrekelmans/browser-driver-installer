<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\Firefox;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver as PathResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;


final class PathResolver implements PathResolverInterface
{
    public function from(OperatingSystem $operatingSystem): string
    {
        if ($operatingSystem=== OperatingSystem::LINUX) {
            return 'firefox';
        }

        if ($operatingSystem=== OperatingSystem::MACOS) {
            return '/Applications/Firefox.app';
        }

        if ($operatingSystem=== OperatingSystem::WINDOWS) {
            return 'C:\\Program Files\\Mozilla Firefox\\firefox';
        }

        throw NotImplemented::feature(sprintf('Resolving path on %s', $operatingSystem->value));
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName=== BrowserName::FIREFOX;
    }
}
