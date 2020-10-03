<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use function get_class;
use function Safe\sprintf;

final class VersionResolverFactory
{
    /**
     * @psalm-var array<class-string<VersionResolver>, VersionResolver>
     * @var VersionResolver[] $versionResolvers
     */
    private array $versionResolvers = [];

    /**
     * @throws NotImplemented If no version resolver is implemented for browser
     */
    public function createFromName(BrowserName $browserName) : VersionResolver
    {
        foreach ($this->versionResolvers as $versionResolver) {
            if ($versionResolver->supports($browserName)) {
                return $versionResolver;
            }
        }

        throw NotImplemented::feature(
            sprintf('Resolving %s version', $browserName->getValue())
        );
    }

    public function register(VersionResolver $versionResolver) : void
    {
        $this->versionResolvers[get_class($versionResolver)] = $versionResolver;
    }
}
