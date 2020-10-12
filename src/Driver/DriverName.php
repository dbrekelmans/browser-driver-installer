<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use MyCLabs\Enum\Enum;

/**
 * @method static self CHROME()
 * @method static self GECKO()
 *
 * @extends Enum<string>
 *
 * @psalm-immutable
 */
final class DriverName extends Enum
{
    public const CHROME = 'chromedriver';
    public const GECKO = 'geckodriver';
}
