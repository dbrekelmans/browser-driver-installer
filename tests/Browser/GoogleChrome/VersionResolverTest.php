<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class VersionResolverTest extends TestCase
{
    private VersionResolver $versionResolver;

    protected function setUp(): void
    {
        $this->versionResolver = new VersionResolver();
    }

    public function testSupportChrome(): void
    {
        $this->assertTrue($this->versionResolver->supports(BrowserName::GOOGLE_CHROME()));
    }

    public function testDoesNotSupportFirefox(): void
    {
        $this->assertFalse($this->versionResolver->supports(BrowserName::FIREFOX()));
    }

    public function testFromLinux(): void
    {
        $this->versionResolver->setProcess('google-chrome --version', $this->getSuccessfulProcessMock());

        $this->assertEquals(
            Version::fromString('86.0.4240.80'),
            $this->versionResolver->from(OperatingSystem::LINUX(), 'google-chrome')
        );
    }

    public function testFromMac(): void
    {
        $this->versionResolver->setProcess(
            '/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --version',
            $this->getSuccessfulProcessMock()
        );

        $this->assertEquals(
            Version::fromString('86.0.4240.80'),
            $this->versionResolver->from(OperatingSystem::MACOS(), '/Applications/Google\ Chrome.app')
        );
    }

    public function testFromWindows(): void
    {
        $this->versionResolver->setProcess(
            'wmic datafile where name="C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" get Version /value',
            $this->getSuccessfulProcessMock()
        );

        $this->assertEquals(
            Version::fromString('86.0.4240.80'),
            $this->versionResolver->from(OperatingSystem::WINDOWS(), 'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe')
        );
    }

    /**
     * @return MockObject&Process
     */
    private function getSuccessfulProcessMock(): Process
    {
        $processMock = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
        $processMock->expects($this->any())
            ->method('isSuccessful')
            ->willReturn(true);
        $processMock->expects($this->any())
            ->method('getOutput')
            ->willReturn('Google Chrome 86.0.4240.80');

        return $processMock;
    }
}