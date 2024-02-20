<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;

final class DriverFactory
{
    public function __construct(private VersionResolverFactory $versionResolverFactory)
    {
    }

    public function createFromBrowser(Browser $browser): Driver
    {
        $versionResolver = $this->versionResolverFactory->createFromBrowser($browser);
        $version         = $versionResolver->fromBrowser($browser);

        $name = $this->getDriverNameForBrowser($browser);

        return new Driver($name, $version, $browser->operatingSystem());
    }

    /** @throws NotImplemented */
    private function getDriverNameForBrowser(Browser $browser): DriverName
    {
        return match ($browser->name()) {
            BrowserName::GOOGLE_CHROME, BrowserName::CHROMIUM => DriverName::CHROME,
            BrowserName::FIREFOX => DriverName::GECKO,
        };
    }
}
