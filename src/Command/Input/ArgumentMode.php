<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use MyCLabs\Enum\Enum;

/**
 * @method static self REQUIRED()
 * @method static self OPTIONAL()
 * @method static self IS_ARRAY()
 * @extends Enum<int>
 * @psalm-immutable
 */
final class ArgumentMode extends Enum
{
    public const REQUIRED = 1;
    public const OPTIONAL = 2;
    public const IS_ARRAY = 4;
}
