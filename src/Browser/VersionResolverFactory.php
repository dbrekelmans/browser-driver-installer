<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;

use function get_class;
use function Safe\sprintf;

final class VersionResolverFactory
{
    /** @var array<VersionResolver>|VersionResolver[] $browserVersionResolvers */
    private array $browserVersionResolvers;

    public function createFromBrowserName(Name $browserName) : VersionResolver
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

    public function register(VersionResolver $browserVersionResolver) : void
    {
        $this->browserVersionResolvers[get_class($browserVersionResolver)] = $browserVersionResolver;
    }
}
