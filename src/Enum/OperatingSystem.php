<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static self WINDOWS()
 * @method static self MACOS()
 * @method static self LINUX()
 *
 * @extends Enum<string>
 *
 * @internal
 */
final class OperatingSystem extends Enum
{
    public const WINDOWS = 'Windows';
    public const MACOS = 'MacOS';
    public const LINUX = 'Linux';

    public static function fromFamily(OperatingSystemFamily $family) : self
    {
        if ($family->equals(OperatingSystemFamily::WINDOWS())) {
            return self::WINDOWS();
        }

        if ($family->equals(OperatingSystemFamily::DARWIN())) {
            return self::MACOS();
        }

        return self::LINUX();
    }
}
