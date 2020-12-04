<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\Firefox;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver as PathResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;

use function Safe\sprintf;

final class PathResolver implements PathResolverInterface
{
    public function from(OperatingSystem $operatingSystem): string
    {
        if ($operatingSystem->equals(OperatingSystem::LINUX())) {
            return 'firefox';
        }

        if ($operatingSystem->equals(OperatingSystem::MACOS())) {
            return '/Applications/Firefox.app';
        }

        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            return 'C:\\Program Files (x86)\\Firefox\\Application\\firefox.exe';
        }

        throw NotImplemented::feature(sprintf('Resolving path on %s', $operatingSystem->getValue()));
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName->equals(BrowserName::FIREFOX());
    }
}
