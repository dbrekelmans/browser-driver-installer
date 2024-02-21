<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;

use function sprintf;

final class PathResolverFactory
{
    /** @var array<string, PathResolver> */
    private array $pathResolvers = [];

    /** @throws NotImplemented If no path resolver is implemented for browser. */
    public function createFromName(BrowserName $browserName): PathResolver
    {
        foreach ($this->pathResolvers as $pathResolver) {
            if ($pathResolver->supports($browserName)) {
                return $pathResolver;
            }
        }

        throw NotImplemented::feature(
            sprintf('Resolving %s path', $browserName->value),
        );
    }

    public function register(PathResolver $pathResolver, string|null $identifier = null): void
    {
        $this->pathResolvers[$identifier ?? $pathResolver::class] = $pathResolver;
    }
}
