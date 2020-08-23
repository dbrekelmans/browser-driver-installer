<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\Exception;

use LogicException;

use function Safe\sprintf;

/** @internal */
final class NotImplemented extends LogicException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function feature(string $feature) : self
    {
        return new self(sprintf('%s is not implemented.', $feature));
    }
}
