<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser\Chromium;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\Chromium\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Tests\CommandLineMock;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\TestCase;

class VersionResolverTest extends TestCase
{
    private VersionResolver $versionResolver;
    private CommandLineMock $commandLineMock;

    protected function setUp(): void
    {
        $this->commandLineMock = new CommandLineMock();
        $this->versionResolver = new VersionResolver($this->commandLineMock);
    }

    public function testSupportChromium(): void
    {
        self::assertTrue($this->versionResolver->supports(BrowserName::CHROMIUM()));
    }

    public function testDoesNotSupportFirefox(): void
    {
        self::assertFalse($this->versionResolver->supports(BrowserName::FIREFOX()));
    }

    public function testFromMac(): void
    {
        $this->commandLineMock->givenCommandWillReturnOutput(
            '/Applications/Chromium.app/Contents/MacOS/Chromium --version',
            'Chromium 88.0.4299.0'
        );

        self::assertEquals(
            Version::fromString('88.0.4299.0'),
            $this->versionResolver->from(OperatingSystem::MACOS(), '/Applications/Chromium.app')
        );
    }

    public function testFromLinux(): void
    {
        $this->commandLineMock->givenCommandWillReturnOutput('chromium --version', 'Chromium 88.0.4299.0');

        self::assertEquals(
            Version::fromString('88.0.4299.0'),
            $this->versionResolver->from(OperatingSystem::LINUX(), 'chromium')
        );
    }

    public function testFromWindows(): void
    {
        $this->commandLineMock->givenCommandWillReturnOutput(
            'wmic datafile where name="C:\Program Files (x86)\Chromium\Application\chrome.exe" get Version /value',
            'Chromium 88.0.4299.0'
        );

        self::assertEquals(
            Version::fromString('88.0.4299.0'),
            $this->versionResolver->from(
                OperatingSystem::WINDOWS(),
                'C:\Program Files (x86)\Chromium\Application\chrome.exe'
            )
        );
    }
}
