<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

enum ArgumentMode: int
{
    case REQUIRED = 1;
    case OPTIONAL = 2;
    case IS_ARRAY = 4;
}
