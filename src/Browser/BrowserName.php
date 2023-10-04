<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

enum BrowserName: string
{
    case GOOGLE_CHROME = 'google-chrome';
    case CHROMIUM      = 'chromium';
    case FIREFOX       = 'firefox';
}
