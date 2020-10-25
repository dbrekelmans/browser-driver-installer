<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser\Chromium;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\Chromium\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\CommandLine\CommandLineEnvironment;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VersionResolverTest extends TestCase
{
    private VersionResolver $versionResolver;
    /** @var MockObject&CommandLineEnvironment */
    private $commandLineEnvMock;

    protected function setUp(): void
    {
        $this->commandLineEnvMock = $this->getMockBuilder(CommandLineEnvironment::class)->getMock();
        $this->versionResolver = new VersionResolver($this->commandLineEnvMock);
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
        $this->mockCommandLineCommandOutput(
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
        $this->mockCommandLineCommandOutput('chromium --version', 'Chromium 88.0.4299.0');

        self::assertEquals(
            Version::fromString('88.0.4299.0'),
            $this->versionResolver->from(OperatingSystem::LINUX(), 'chromium')
        );
    }

    public function testFromWindows(): void
    {
        $this->mockCommandLineCommandOutput(
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

    private function mockCommandLineCommandOutput(string $command, string $output): void
    {
        $this->commandLineEnvMock
            ->method('getCommandLineSuccessfulOutput')
            ->with($command)
            ->willReturn($output);
    }
}
