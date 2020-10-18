<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome\PathResolver;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use PHPUnit\Framework\TestCase;

final class PathResolverTest extends TestCase
{
    private PathResolver $pathResolver;

    protected function setUp() : void
    {
        $this->pathResolver = new PathResolver();
    }

    public function testFromKnownOs() : void
    {
        $this->assertEquals('google-chrome', $this->pathResolver->from(OperatingSystem::LINUX()));
        $this->assertEquals('/Applications/Google\ Chrome.app', $this->pathResolver->from(OperatingSystem::MACOS()));
        $this->assertEquals('C:\Program Files (x86)\Google\Chrome\Application\chrome.exe', $this->pathResolver->from(OperatingSystem::WINDOWS()));
    }

    public function testSupportChrome() : void
    {
        $this->assertTrue($this->pathResolver->supports(BrowserName::GOOGLE_CHROME()));
    }

    public function testDoesNotSupportFirefox() : void
    {
        $this->assertFalse($this->pathResolver->supports(BrowserName::FIREFOX()));
    }
}
