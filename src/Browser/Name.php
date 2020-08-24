<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use MyCLabs\Enum\Enum;

/**
 * @method static self GOOGLE_CHROME()
 * @method static self CHROMIUM()
 * @method static self FIREFOX()
 *
 * @extends Enum<string>
 */
final class Name extends Enum
{
    public const GOOGLE_CHROME = 'Google Chrome';
    public const CHROMIUM = 'Chromium';
    public const FIREFOX = 'Firefox';
}
