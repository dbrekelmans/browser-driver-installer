<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser\Firefox;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\Firefox\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\CommandLine\CommandLineEnvironment;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VersionResolverTest extends TestCase
{
    /** @var VersionResolver */
    private $versionResolver;

    /** @var MockObject&CommandLineEnvironment */
    private $commandLineEnvMock;

    public function testDoesNotSupportChrome(): void
    {
        self::assertFalse($this->versionResolver->supports(BrowserName::GOOGLE_CHROME()));
    }

    public function testSupportsFirefox(): void
    {
        self::assertTrue($this->versionResolver->supports(BrowserName::FIREFOX()));
    }

    public function testFromLinux(): void
    {
        $this->mockCommandLineCommandOutput('firefox --version', 'Mozilla Firefox 83.0');

        self::assertEquals(
            Version::fromString('83.0'),
            $this->versionResolver->from(OperatingSystem::LINUX(), 'firefox')
        );
    }

    public function testFromMac(): void
    {
        $this->mockCommandLineCommandOutput(
            '/Applications/Firefox.app/Contents/MacOS/firefox --version',
            'Mozilla Firefox 83.0'
        );

        self::assertEquals(
            Version::fromString('83.0'),
            $this->versionResolver->from(OperatingSystem::MACOS(), '/Applications/Firefox.app')
        );
    }

    public function testFromWindows(): void
    {
        $this->mockCommandLineCommandOutput(
            'C:\\Program Files (x86)\\Firefox\\Application\\firefox --version',
            'Mozilla Firefox 83.0'
        );

        self::assertEquals(
            Version::fromString('83.0'),
            $this->versionResolver->from(OperatingSystem::WINDOWS(), 'C:\\Program Files (x86)\\Firefox\\Application\\firefox')
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
