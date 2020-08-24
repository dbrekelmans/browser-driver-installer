<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use ArrayObject;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;

final class Factory
{
    /** @var array<BrowserName>|BrowserName[] */
    private array $browsers = [];
    private VersionResolverFactory $versionResolverFactory;

    public function __construct(VersionResolverFactory $browserVersionResolverFactory)
    {
        $this->versionResolverFactory = $browserVersionResolverFactory;
    }

    public function createFromNameAndPathAndOperationSystem(
        BrowserName $browserName,
        string $path,
        OperatingSystem $operatingSystem
    ) : Browser {
        $versionResolver = $this->versionResolverFactory->createBy($browserName);

        $version = $versionResolver->from($operatingSystem, $path);

        return new Browser($browserName, $version);
    }

    /** @return array<BrowserName>|BrowserName[] */
    public function registeredBrowsers() : array
    {
        return (new ArrayObject($this->browsers))->getArrayCopy();
    }

    public function register(BrowserName $browserName) : void
    {
        $this->browsers[$browserName->getKey()] = $browserName->getValue();
    }
}
