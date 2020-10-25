<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;

use function Safe\sprintf;

final class DriverFactory
{
    private VersionResolverFactory $versionResolverFactory;

    public function __construct(VersionResolverFactory $versionResolverFactory)
    {
        $this->versionResolverFactory = $versionResolverFactory;
    }

    public function createFromBrowser(Browser $browser): Driver
    {
        $versionResolver = $this->versionResolverFactory->createFromBrowser($browser);
        $version = $versionResolver->fromBrowser($browser);

        $name = $this->getDriverNameForBrowser($browser);

        return new Driver($name, $version, $browser->operatingSystem());
    }

    /**
     * @throws NotImplemented
     */
    private function getDriverNameForBrowser(Browser $browser): DriverName
    {
        $browserName = $browser->name();

        if ($browserName->equals(BrowserName::GOOGLE_CHROME()) || $browserName->equals(BrowserName::CHROMIUM())) {
            return DriverName::CHROME();
        }

        if ($browserName->equals(BrowserName::FIREFOX())) {
            return DriverName::GECKO();
        }

        throw NotImplemented::feature(sprintf('Driver for %s', $browserName->getValue()));
    }
}
