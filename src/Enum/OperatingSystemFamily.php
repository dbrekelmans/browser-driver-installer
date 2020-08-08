<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static self WINDOWS()
 * @method static self BSD()
 * @method static self DARWIN()
 * @method static self SOLARIS()
 * @method static self LINUX()
 * @method static self UNKNOWN()
 *
 * @extends Enum<string>
 *
 * @internal
 */
final class OperatingSystemFamily extends Enum
{
    public const WINDOWS = 'Windows';
    public const BSD = 'BSD';
    public const DARWIN = 'Darwin';
    public const SOLARIS = 'Solaris';
    public const LINUX = 'Linux';
    public const UNKNOWN = 'Unknown';
}
