<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\OperatingSystem;

enum OperatingSystem: string
{
    case WINDOWS = 'windows';
    case MACOS   = 'macos';
    case LINUX   = 'linux';

    public static function fromFamily(Family $family): self
    {
        return match ($family) {
            Family::WINDOWS => self::WINDOWS,
            Family::DARWIN => self::MACOS,
            default => self::LINUX,
        };
    }
}
