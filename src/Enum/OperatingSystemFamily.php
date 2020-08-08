<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\Enum;

use MyCLabs\Enum\Enum;

/** @internal */
final class OperatingSystemFamily extends Enum
{
    public const WINDOWS = 'Windows';
    public const BSD = 'BSD';
    public const DARWIN = 'Darwin';
    public const SOLARIS = 'Solaris';
    public const LINUX = 'Linux';
    public const UNKNOWN = 'Unknown';
}
