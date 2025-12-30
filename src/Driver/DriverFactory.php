<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Cpu\CpuArchitecture;

final class DriverFactory
{
    public function __construct(private readonly VersionResolverFactory $versionResolverFactory)
    {
    }

    public function createFromBrowser(Browser $browser): Driver
    {
        $versionResolver = $this->versionResolverFactory->createFromBrowser($browser);
        $version         = $versionResolver->fromBrowser($browser);

        $name = $this->getDriverNameForBrowser($browser);

        // Detect CPU arch
        $cpuArchitecture = CpuArchitecture::detectFromPhp();

        return new Driver($name, $version, $browser->operatingSystem, $cpuArchitecture);
    }

    private function getDriverNameForBrowser(Browser $browser): DriverName
    {
        return match ($browser->name) {
            BrowserName::GOOGLE_CHROME, BrowserName::CHROMIUM => DriverName::CHROME,
            BrowserName::FIREFOX => DriverName::GECKO,
            BrowserName::MSEDGE => DriverName::MSEDGE,
        };
    }
}
