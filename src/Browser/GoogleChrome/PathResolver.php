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
        return match ($operatingSystem) {
            OperatingSystem::LINUX => 'google-chrome', // TODO: command -v google-chrome
            OperatingSystem::MACOS => '/Applications/Google\ Chrome.app', // TODO: check if file exists
            OperatingSystem::WINDOWS => 'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe', // TODO: (Get-Item (Get-ItemProperty 'HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe').'(Default)').VersionInfo
        };
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName=== BrowserName::GOOGLE_CHROME;
    }
}
