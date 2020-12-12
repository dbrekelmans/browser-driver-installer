<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Exception;

use LogicException;
use function Safe\sprintf;

/** @internal */
final class NotImplemented extends LogicException
{
    public static function feature(string $feature) : self
    {
        return new self(sprintf('%s is not implemented.', $feature));
    }
}
