<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome\PathResolver;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use PHPUnit\Framework\TestCase;

final class PathResolverTest extends TestCase
{
    public function testFromKnownOs()
    {
        $pathResolver = new PathResolver();
        $this->assertEquals('google-chrome', $pathResolver->from(OperatingSystem::LINUX()));
        $this->assertEquals('/Applications/Google\ Chrome.app', $pathResolver->from(OperatingSystem::MACOS()));
        $this->assertEquals('C:\Program Files (x86)\Google\Chrome\Application\chrome.exe', $pathResolver->from(OperatingSystem::WINDOWS()));
    }

    public function testSupportChrome()
    {
        $pathResolver = new PathResolver();
        $this->assertTrue($pathResolver->supports(BrowserName::GOOGLE_CHROME()));
    }

    public function testDoesNotSupportFirefox()
    {
        $pathResolver = new PathResolver();
        $this->assertFalse($pathResolver->supports(BrowserName::FIREFOX()));
    }
}