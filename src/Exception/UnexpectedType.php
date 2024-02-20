<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Exception;

use Throwable;
use UnexpectedValueException;

use function get_debug_type;
use function sprintf;

final class UnexpectedType extends UnexpectedValueException
{
    private function __construct(string $message, int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function expected(string $expected, mixed $actual): self
    {
        return new self(sprintf('Unexpected type %s. Expected %s.', get_debug_type($actual), $expected));
    }
}
