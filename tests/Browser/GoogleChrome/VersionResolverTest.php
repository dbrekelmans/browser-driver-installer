<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome\VersionResolver;
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
        $this->mockCommandLineCommandOutput('google-chrome --version', 'Google Chrome 86.0.4240.80');

        self::assertEquals(
            Version::fromString('86.0.4240.80'),
            $this->versionResolver->from(OperatingSystem::LINUX(), 'google-chrome')
        );
    }

    public function testFromMac(): void
    {
        $this->mockCommandLineCommandOutput(
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
        $this->mockCommandLineCommandOutput(
            'reg query HKLM\Software\Google\Update\Clients\{8A69D345-D564-463c-AFF1-A69D9E530F96} /v pv /reg:32 2> NUL',
            'HKEY_LOCAL_MACHINE\Software\Google\Update\Clients\{8A69D345-D564-463c-AFF1-A69D9E530F96}
    pv    REG_SZ    86.0.4240.80'
        );

        self::assertEquals(
            Version::fromString('86.0.4240.80'),
            $this->versionResolver->from(
                OperatingSystem::WINDOWS(),
                'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe'
            )
        );
    }

    protected function setUp(): void
    {
        $this->commandLineEnvMock = $this->createMock(CommandLineEnvironment::class);
        $this->versionResolver    = new VersionResolver($this->commandLineEnvMock);
    }

    private function mockCommandLineCommandOutput(string $command, string $output): void
    {
        $this->commandLineEnvMock
            ->method('getCommandLineSuccessfulOutput')
            ->with($command)
            ->willReturn($output);
    }
}
