<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;

final class GoogleChromeCommand extends BrowserCommand
{
    protected static function browserName() : BrowserName
    {
        return BrowserName::GOOGLE_CHROME();
    }
}
