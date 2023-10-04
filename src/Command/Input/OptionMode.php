<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

enum OptionMode: int
{
    case NONE     = 1;
    case REQUIRED = 2;
    case OPTIONAL = 4;
    case IS_ARRAY = 8;
}
