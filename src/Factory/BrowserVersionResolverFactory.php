<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\Factory;

use BrowserDriverInstaller\BrowserVersionResolver\UnixGoogleChromeBrowserVersionResolver;
use BrowserDriverInstaller\BrowserVersionResolverInterface;
use BrowserDriverInstaller\Enum\OperatingSystemFamily;
use BrowserDriverInstaller\Exception\NotImplemented;
use BrowserDriverInstaller\ValueObject\Browser;
use RuntimeException;

/**
 * @internal
 */
final class BrowserVersionResolverFactory
{
    public function getResolver(Browser $browser) : BrowserVersionResolverInterface
    {
        switch ($browser->getType()) {
            case Browser::GOOGLE_CHROME:
                if ($browser->getOsFamily() === OperatingSystemFamily::WINDOWS) {
                    throw $this->notImplemented($browser);
                    // TODO: return new WindowsGoogleChromeBrowserVersionResolver();
                }

                return new UnixGoogleChromeBrowserVersionResolver($browser->getPath());
            default:
                throw $this->notImplemented($browser);
        }
    }

    /**
     * @throws RuntimeException
     */
    private function notImplemented(Browser $browser) : NotImplemented
    {
        return new NotImplemented(
            sprintf(
                'Resolving the browser version of %s on %s has not yet been implemented.',
                $browser->getType(),
                $browser->getOsFamily()
            )
        );
    }
}
