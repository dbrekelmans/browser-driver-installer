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
 *
 * @psalm-immutable
 */
final class BrowserName extends Enum
{
    public const GOOGLE_CHROME = 'google-chrome';
    public const CHROMIUM = 'chromium';
    public const FIREFOX = 'firefox';
}
