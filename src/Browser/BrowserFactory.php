<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;

final class BrowserFactory
{
    public function __construct(
        private PathResolverFactory $pathResolverFactory,
        private VersionResolverFactory $versionResolverFactory,
    ) {
    }

    public function createFromNameAndOperatingSystem(BrowserName $name, OperatingSystem $operatingSystem): Browser
    {
        $pathResolver = $this->pathResolverFactory->createFromName($name);
        $path         = $pathResolver->from($operatingSystem);

        return $this->createFromNameOperatingSystemAndPath($name, $operatingSystem, $path);
    }

    public function createFromNameOperatingSystemAndPath(
        BrowserName $name,
        OperatingSystem $operatingSystem,
        string $path,
    ): Browser {
        $versionResolver = $this->versionResolverFactory->createFromName($name);
        $version         = $versionResolver->from($operatingSystem, $path);

        return new Browser($name, $version, $operatingSystem);
    }
}
