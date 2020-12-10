<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\Chromium;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver as PathResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use function Safe\sprintf;

class PathResolver implements PathResolverInterface
{
    public function from(OperatingSystem $operatingSystem) : string
    {
        if ($operatingSystem->equals(OperatingSystem::LINUX())) {
            return 'chromium';
        }

        if ($operatingSystem->equals(OperatingSystem::MACOS())) {
            return '/Applications/Chromium.app';
        }

        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            return 'C:\\Program Files (x86)\\Chromium\\Application\\chrome.exe';
        }

        throw NotImplemented::feature(sprintf('Resolving path on %s', $operatingSystem->getValue()));
    }

    public function supports(BrowserName $browserName) : bool
    {
        return $browserName->equals(BrowserName::CHROMIUM());
    }
}
