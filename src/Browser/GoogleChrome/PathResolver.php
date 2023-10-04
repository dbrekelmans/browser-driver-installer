<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver as PathResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;


final class PathResolver implements PathResolverInterface
{
    /**
     * @throws NotImplemented
     */
    public function from(OperatingSystem $operatingSystem): string
    {
        if ($operatingSystem=== OperatingSystem::LINUX) {
            // TODO: command -v google-chrome
            return 'google-chrome';
        }

        if ($operatingSystem=== OperatingSystem::MACOS) {
            // TODO: check if file exists
            return '/Applications/Google\ Chrome.app';
        }

        if ($operatingSystem=== OperatingSystem::WINDOWS) {
            // phpcs:ignore TODO: (Get-Item (Get-ItemProperty 'HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe').'(Default)').VersionInfo
            return 'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe';
        }

        throw NotImplemented::feature(sprintf('Resolving path on %s', $operatingSystem->value));
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName=== BrowserName::GOOGLE_CHROME;
    }
}
