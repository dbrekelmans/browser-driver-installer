<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use ArrayObject;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;

final class Factory
{
    /** @var array<Name>|Name[] */
    private array $browserNames = [];
    private VersionResolverFactory $browserVersionResolverFactory;

    public function __construct(VersionResolverFactory $browserVersionResolverFactory)
    {
        $this->browserVersionResolverFactory = $browserVersionResolverFactory;
    }

    public function createFromNameAndPathAndOperationSystem(
        Name $name,
        string $path,
        OperatingSystem $operatingSystem
    ) : Browser {
        $versionResolver = $this->browserVersionResolverFactory->createFromBrowserName($name);

        $version = $versionResolver->from($operatingSystem, $path);

        return new Browser($name, $version);
    }

    /** @return array<Name>|Name[] */
    public function registeredBrowsers() : array
    {
        return (new ArrayObject($this->browserNames))->getArrayCopy();
    }

    public function register(Name $browserName) : void
    {
        $this->browserNames[$browserName->getKey()] = $browserName->getValue();
    }
}
