<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\Version;

final class Browser
{
    private BrowserName $name;
    private Version $version;

    public function __construct(BrowserName $name, Version $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function name() : BrowserName
    {
        return $this->name;
    }

    public function version() : Version
    {
        return $this->version;
    }
}
