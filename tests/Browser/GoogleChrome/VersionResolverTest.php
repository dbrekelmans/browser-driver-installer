<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome\VersionResolver;
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

    public function testSupportChrome(): void
    {
        self::assertTrue($this->versionResolver->supports(BrowserName::GOOGLE_CHROME()));
    }

    public function testDoesNotSupportFirefox(): void
    {
        self::assertFalse($this->versionResolver->supports(BrowserName::FIREFOX()));
    }

    public function testFromLinux(): void
    {
        $this->commandLineMock->givenCommandWillReturnOutput('google-chrome --version', 'Google Chrome 86.0.4240.80');

        self::assertEquals(
            Version::fromString('86.0.4240.80'),
            $this->versionResolver->from(OperatingSystem::LINUX(), 'google-chrome')
        );
    }

    public function testFromMac(): void
    {
        $this->commandLineMock->givenCommandWillReturnOutput(
            '/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --version',
            'Google Chrome 86.0.4240.80'
        );

        self::assertEquals(
            Version::fromString('86.0.4240.80'),
            $this->versionResolver->from(OperatingSystem::MACOS(), '/Applications/Google\ Chrome.app')
        );
    }

    public function testFromWindows(): void
    {
        $this->commandLineMock->givenCommandWillReturnOutput(
            'wmic datafile where name="C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" get Version /value',
            'Google Chrome 86.0.4240.80'
        );

        self::assertEquals(
            Version::fromString('86.0.4240.80'),
            $this->versionResolver->from(
                OperatingSystem::WINDOWS(),
                'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe'
            )
        );
    }
}
