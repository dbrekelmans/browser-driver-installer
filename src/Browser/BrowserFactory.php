<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;

final class BrowserFactory
{
    /**
     * @var PathResolverFactory
     */
    private $pathResolverFactory;

    /**
     * @var VersionResolverFactory
     */
    private $versionResolverFactory;

    public function __construct(
        PathResolverFactory $pathResolverFactory,
        VersionResolverFactory $versionResolverFactory
    ) {
        $this->pathResolverFactory = $pathResolverFactory;
        $this->versionResolverFactory = $versionResolverFactory;
    }

    public function createFromNameAndOperatingSystem(BrowserName $name, OperatingSystem $operatingSystem): Browser
    {
        $pathResolver = $this->pathResolverFactory->createFromName($name);
        $path = $pathResolver->from($operatingSystem);

        return $this->createFromNameOperatingSystemAndPath($name, $operatingSystem, $path);
    }

    public function createFromNameOperatingSystemAndPath(
        BrowserName $name,
        OperatingSystem $operatingSystem,
        string $path
    ): Browser {
        $versionResolver = $this->versionResolverFactory->createFromName($name);
        $version = $versionResolver->from($operatingSystem, $path);

        return new Browser($name, $version, $operatingSystem);
    }
}
