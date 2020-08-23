<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\Resolver\Version\Browser;

use BrowserDriverInstaller\Enum\BrowserName;
use BrowserDriverInstaller\Enum\OperatingSystem;
use BrowserDriverInstaller\Exception\NotImplemented;
use BrowserDriverInstaller\ValueObject\Version;
use RuntimeException;

/** @internal */
interface BrowserVersionResolver
{
    /**
     * @throws RuntimeException If the version could not be resolved.
     * @throws NotImplemented If the operating system is not yet supported.
     */
    // TODO: Make $path non-nullable
    public function resolveFrom(OperatingSystem $operatingSystem, ?string $path = null) : Version;

    public function supportedBrowserName() : BrowserName;
}
