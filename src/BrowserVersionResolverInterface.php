<?php

declare(strict_types=1);

namespace BrowserDriverInstaller;

use RuntimeException;

/** @internal */
interface BrowserVersionResolverInterface
{
    /**
     * @throws RuntimeException If the version could not be resolved.
     */
    public function resolveVersion() : string;
}
