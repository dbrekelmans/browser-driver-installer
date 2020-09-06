<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;

use function get_class;
use function Safe\sprintf;

final class VersionResolverFactory
{
    /** @var array<string, VersionResolver>|VersionResolver[] $versionResolvers */
    private array $versionResolvers;

    /**
     * @throws NotImplemented If no version resolver is implemented for browser
     */
    public function createFromBrowser(Browser $browser) : VersionResolver
    {
        foreach ($this->versionResolvers as $versionResolver) {
            if ($versionResolver->supports($browser)) {
                return $versionResolver;
            }
        }

        throw NotImplemented::feature(
            sprintf('Resolving %s version', $browser->name()->getValue())
        );
    }

    public function register(VersionResolver $versionResolver) : void
    {
        $this->versionResolvers[get_class($versionResolver)] = $versionResolver;
    }
}