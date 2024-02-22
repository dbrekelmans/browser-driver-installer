<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;

use function sprintf;

final class VersionResolverFactory
{
    /** @var array<string, VersionResolver> */
    private array $versionResolvers = [];

    /** @throws NotImplemented If no version resolver is implemented for browser. */
    public function createFromName(BrowserName $browserName): VersionResolver
    {
        foreach ($this->versionResolvers as $versionResolver) {
            if ($versionResolver->supports($browserName)) {
                return $versionResolver;
            }
        }

        throw NotImplemented::feature(
            sprintf('Resolving %s version', $browserName->value),
        );
    }

    public function register(VersionResolver $versionResolver, string|null $identifier = null): void
    {
        $this->versionResolvers[$identifier ?? $versionResolver::class] = $versionResolver;
    }
}
