<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\ValueObject;

/**
 * @internal
 */
final class Browser
{
    public const GOOGLE_CHROME = 'google-chrome';
    public const CHROMIUM = 'chromium';
    public const FIREFOX = 'firefox';

    private $type;
    private $path;
    private $osFamily;

    public function __construct(string $type, string $path, string $osFamily)
    {
        $this->type = $type;
        $this->path = $path;
        $this->osFamily = $osFamily;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getOsFamily() : string
    {
        return $this->osFamily;
    }
}
