<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\Factory;

use BrowserDriverInstaller\Enum\BrowserName;
use BrowserDriverInstaller\Exception\NotImplemented;
use BrowserDriverInstaller\Resolver\Version\Browser\BrowserVersionResolver;

use function get_class;
use function Safe\sprintf;

final class BrowserVersionResolverFactory
{
    /** @var array<BrowserVersionResolver>|BrowserVersionResolver[] $browserVersionResolvers */
    private array $browserVersionResolvers;

    public function createFromBrowserName(BrowserName $browserName) : BrowserVersionResolver
    {
        foreach ($this->browserVersionResolvers as $browserVersionResolver) {
            if ($browserVersionResolver->supportedBrowserName()->equals($browserName)) {
                return $browserVersionResolver;
            }
        }

        throw NotImplemented::feature(
            sprintf('Automatically resolving %s version', $browserName->getValue())
        );
    }

    public function register(BrowserVersionResolver $browserVersionResolver) : void
    {
        $this->browserVersionResolvers[get_class($browserVersionResolver)] = $browserVersionResolver;
    }
}
