<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\OperatingSystem;

use MyCLabs\Enum\Enum;

/**
 * @method static self WINDOWS()
 * @method static self MACOS()
 * @method static self LINUX()
 * @extends Enum<string>
 */
final class OperatingSystem extends Enum
{
    public const WINDOWS = 'windows';
    public const MACOS   = 'macos';
    public const LINUX   = 'linux';

    public static function fromFamily(Family $family): self
    {
        if ($family->equals(Family::WINDOWS())) {
            return self::WINDOWS();
        }

        if ($family->equals(Family::DARWIN())) {
            return self::MACOS();
        }

        return self::LINUX();
    }
}
