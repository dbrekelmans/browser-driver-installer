<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\Version;

final class Browser
{
    private Name $name;
    private Version $version;

    public function __construct(Name $name, Version $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function name() : Name
    {
        return $this->name;
    }

    public function version() : Version
    {
        return $this->version;
    }
}
