<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Exception;

use UnexpectedValueException;
use function get_debug_type;
use function Safe\sprintf;

final class UnexpectedType extends UnexpectedValueException
{
    /**
     * @param mixed $actual
     */
    public static function expected(string $expected, $actual) : self
    {
        return new self(sprintf('Unexpected type %s. Expected %s.', get_debug_type($actual), $expected));
    }
}
