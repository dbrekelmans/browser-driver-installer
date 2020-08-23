<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\Factory;

use BrowserDriverInstaller\BrowserVersionResolver\BrowserVersionResolver;
use BrowserDriverInstaller\Enum\BrowserName;
use BrowserDriverInstaller\Exception\NotImplemented;
use function Safe\sprintf;
use function get_class;

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

        throw new NotImplemented(
            sprintf('No version resolver has been implemented for %s.', $browserName->getValue())
        );
    }

    public function register(BrowserVersionResolver $browserVersionResolver) : void
    {
        $this->browserVersionResolvers[get_class($browserVersionResolver)] = $browserVersionResolver;
    }
}
