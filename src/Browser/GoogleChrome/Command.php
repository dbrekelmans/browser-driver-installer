<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Command\BrowserCommand;

final class Command extends BrowserCommand
{
    protected static function browserName() : BrowserName
    {
        return BrowserName::GOOGLE_CHROME();
    }
}
