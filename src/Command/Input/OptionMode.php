<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use MyCLabs\Enum\Enum;

/**
 * @method static self NONE()
 * @method static self REQUIRED()
 * @method static self OPTIONAL()
 * @method static self IS_ARRAY()
 * @extends Enum<int>
 */
final class OptionMode extends Enum
{
    public const NONE     = 1;
    public const REQUIRED = 2;
    public const OPTIONAL = 4;
    public const IS_ARRAY = 8;
}
