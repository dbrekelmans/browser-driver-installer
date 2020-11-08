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
    /** @var VersionResolver */
    private $versionResolver;

    /** @var MockObject&CommandLineEnvironment */
    private $commandLineEnvMock;

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
            'reg query HKLM\Software\Google\Update\Clients\{8A69D345-D564-463c-AFF1-A69D9E530F96} /v pv /reg:32 2> NUL',
            'HKEY_LOCAL_MACHINE\Software\Google\Update\Clients\{8A69D345-D564-463c-AFF1-A69D9E530F96}
    pv    REG_SZ    88.0.4299.0'
        );

        self::assertEquals(
            Version::fromString('88.0.4299.0'),
            $this->versionResolver->from(
                OperatingSystem::WINDOWS(),
                'C:\Program Files (x86)\Chromium\Application\chrome.exe'
            )
        );
    }

    protected function setUp(): void
    {
        $this->commandLineEnvMock = $this->createMock(CommandLineEnvironment::class);
        $this->versionResolver = new VersionResolver($this->commandLineEnvMock);
    }

    private function mockCommandLineCommandOutput(string $command, string $output): void
    {
        $this->commandLineEnvMock
            ->method('getCommandLineSuccessfulOutput')
            ->with($command)
            ->willReturn($output);
    }
}
