<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\ValueObject;

use BrowserDriverInstaller\Enum\BrowserName;

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
