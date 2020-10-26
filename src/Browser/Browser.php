<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;

final class Browser
{
    /** @var BrowserName */
    private $name;

    /** @var Version */
    private $version;

    /** @var OperatingSystem */
    private $operatingSystem;

    public function __construct(BrowserName $name, Version $version, OperatingSystem $operatingSystem)
    {
        $this->name = $name;
        $this->version = $version;
        $this->operatingSystem = $operatingSystem;
    }

    public function name(): BrowserName
    {
        return $this->name;
    }

    public function version(): Version
    {
        return $this->version;
    }

    public function operatingSystem(): OperatingSystem
    {
        return $this->operatingSystem;
    }
}
