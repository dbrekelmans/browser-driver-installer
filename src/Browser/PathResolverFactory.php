<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;

use function get_class;
use function Safe\sprintf;

class PathResolverFactory
{
    /** @var array<PathResolver>|PathResolver[] $pathResolvers */
    private array $pathResolvers;

    /**
     * @throws NotImplemented If no path resolver is implemented for browser
     */
    public function createBy(BrowserName $browserName) : PathResolver
    {
        foreach ($this->pathResolvers as $pathResolver) {
            if ($pathResolver->supportedBrowserName()->equals($browserName)) {
                return $pathResolver;
            }
        }

        throw NotImplemented::feature(
            sprintf('Automatically resolving %s version', $browserName->getValue())
        );
    }

    public function register(PathResolver $pathResolver) : void
    {
        $this->pathResolvers[get_class($pathResolver)] = $pathResolver;
    }
}