<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\OperatingSystem;

enum Family: string
{
    case WINDOWS = 'Windows';
    case BSD     = 'BSD';
    case DARWIN  = 'Darwin';
    case SOLARIS = 'Solaris';
    case LINUX   = 'Linux';
    case UNKNOWN = 'Unknown';
}
