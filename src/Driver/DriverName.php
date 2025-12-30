<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

enum DriverName: string
{
    case CHROME = 'chromedriver';
    case GECKO  = 'geckodriver';
    case MSEDGE = 'msedgedriver';
}
