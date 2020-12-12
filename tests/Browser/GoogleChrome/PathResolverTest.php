<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome\PathResolver;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use PHPUnit\Framework\TestCase;

final class PathResolverTest extends TestCase
{
    /** @var PathResolver */
    private $pathResolver;

    public function testFromKnownOs() : void
    {
        self::assertSame('google-chrome', $this->pathResolver->from(OperatingSystem::LINUX()));
        self::assertSame('/Applications/Google\ Chrome.app', $this->pathResolver->from(OperatingSystem::MACOS()));
        self::assertSame(
            'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
            $this->pathResolver->from(OperatingSystem::WINDOWS())
        );
    }

    public function testSupportChrome() : void
    {
        self::assertTrue($this->pathResolver->supports(BrowserName::GOOGLE_CHROME()));
    }

    public function testDoesNotSupportFirefox() : void
    {
        self::assertFalse($this->pathResolver->supports(BrowserName::FIREFOX()));
    }

    protected function setUp() : void
    {
        $this->pathResolver = new PathResolver();
    }
}
