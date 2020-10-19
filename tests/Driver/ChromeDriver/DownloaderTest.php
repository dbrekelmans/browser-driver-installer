<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\ChromeDriver;


use DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver\Downloader;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPStan\Testing\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ZipArchive;

class DownloaderTest extends TestCase
{
    private Downloader $downloader;

    public function setUp(): void
    {
        /** @var MockObject&Filesystem $fsMock */
        $fsMock = $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock();
        /** @var MockObject&HttpClientInterface $httpClientMock */
        $httpClientMock = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        /** @var MockObject&ZipArchive $zipMock */
        $zipMock = $this->getMockBuilder(ZipArchive::class)->getMock();
        $this->downloader = new Downloader($fsMock, $httpClientMock, $zipMock);
    }

    public function testSupportChrome() : void
    {
        $chromeDriver = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::MACOS());
        self::assertTrue($this->downloader->supports($chromeDriver));
    }

    public function testDoesNotSupportGecko() : void
    {
        $geckoDriver = new Driver(DriverName::GECKO(), Version::fromString('0.27.0'), OperatingSystem::MACOS());
        self::assertFalse($this->downloader->supports($geckoDriver));
    }
}