<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\BrowserVersionResolver;

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
    public function resolveVersion(OperatingSystem $operatingSystem, ?string $path = null) : Version;

    public function supportedBrowserName() : BrowserName;
}
