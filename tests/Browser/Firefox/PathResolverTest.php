<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser\Firefox;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\Firefox\PathResolver;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use PHPUnit\Framework\TestCase;

final class PathResolverTest extends TestCase
{
    /** @var PathResolver */
    private $pathResolver;

    public function testFromKnownOs(): void
    {
        self::assertSame('firefox', $this->pathResolver->from(OperatingSystem::LINUX()));
        self::assertSame('/Applications/Firefox.app', $this->pathResolver->from(OperatingSystem::MACOS()));
        self::assertSame(
            'C:\Program Files (x86)\Firefox\Application\firefox.exe',
            $this->pathResolver->from(OperatingSystem::WINDOWS())
        );
    }

    public function testDoesNotSupportChrome(): void
    {
        self::assertFalse($this->pathResolver->supports(BrowserName::GOOGLE_CHROME()));
    }

    public function testSupportsFirefox(): void
    {
        self::assertTrue($this->pathResolver->supports(BrowserName::FIREFOX()));
    }

    protected function setUp(): void
    {
        $this->pathResolver = new PathResolver();
    }
}
