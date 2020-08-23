<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\Factory;

use ArrayObject;
use BrowserDriverInstaller\Enum\BrowserName;
use BrowserDriverInstaller\Enum\OperatingSystem;
use BrowserDriverInstaller\ValueObject\Browser;

final class BrowserFactory
{
    /** @var array<BrowserName>|BrowserName[] */
    private array $browserNames = [];
    private BrowserVersionResolverFactory $browserVersionResolverFactory;

    public function __construct(BrowserVersionResolverFactory $browserVersionResolverFactory)
    {
        $this->browserVersionResolverFactory = $browserVersionResolverFactory;
    }

    public function createFromNameAndPathAndOperationSystem(
        BrowserName $name,
        string $path,
        OperatingSystem $operatingSystem
    ) : Browser {
        $versionResolver = $this->browserVersionResolverFactory->createFromBrowserName($name);

        $version = $versionResolver->resolveFrom($operatingSystem, $path);

        return new Browser($name, $version);
    }

    /** @return array<BrowserName>|BrowserName[] */
    public function registeredBrowsers() : array
    {
        return (new ArrayObject($this->browserNames))->getArrayCopy();
    }

    public function register(BrowserName $browserName) : void
    {
        $this->browserNames[$browserName->getKey()] = $browserName->getValue();
    }
}
