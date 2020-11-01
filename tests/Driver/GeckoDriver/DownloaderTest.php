<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\GeckoDriver;

use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
use DBrekelmans\BrowserDriverInstaller\Driver\GeckoDriver\Downloader;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DownloaderTest extends TestCase
{
    /** @var Downloader  */
    private $downloader;
    /** @var Driver  */
    private $gecko;
    /** @var Stub&Filesystem */
    private $filesystem;
    /** @var MockObject&HttpClientInterface */
    private $httpClient;

    public function setUp(): void
    {
        $this->filesystem = $this->createStub(Filesystem::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->downloader = new Downloader($this->filesystem, $this->httpClient);
        $this->gecko = new Driver(DriverName::GECKO(), Version::fromString('0.27.0'), OperatingSystem::MACOS());
    }

    public function testSupportGecko(): void
    {
        self::assertTrue($this->downloader->supports($this->gecko));
    }

    public function testDoesNotSupportChromeDriver(): void
    {
        $chromeDriver = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::MACOS());
        self::assertFalse($this->downloader->supports($chromeDriver));
    }
}
