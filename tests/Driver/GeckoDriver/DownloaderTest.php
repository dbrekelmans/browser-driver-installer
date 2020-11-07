<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\GeckoDriver;

use DBrekelmans\BrowserDriverInstaller\Archive\Extractor;
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

use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

class DownloaderTest extends TestCase
{
    /** @var Downloader  */
    private $downloader;
    /** @var Driver  */
    private $geckoMac;
    /** @var Stub&Filesystem */
    private $filesystem;
    /** @var MockObject&HttpClientInterface */
    private $httpClient;
    /** @var Extractor&MockObject */
    private $archiveExtractor;

    public function setUp(): void
    {
        $this->filesystem = $this->createStub(Filesystem::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->archiveExtractor = $this->createMock(Extractor::class);
        $this->downloader = new Downloader($this->filesystem, $this->httpClient, $this->archiveExtractor);
        $this->geckoMac = new Driver(DriverName::GECKO(), Version::fromString('0.27.0'), OperatingSystem::MACOS());
    }

    public function testSupportGecko(): void
    {
        self::assertTrue($this->downloader->supports($this->geckoMac));
    }

    public function testDoesNotSupportChromeDriver(): void
    {
        $chromeDriver = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::MACOS());
        self::assertFalse($this->downloader->supports($chromeDriver));
    }

    public function testDownloadMac(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload();

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://github.com/mozilla/geckodriver/releases/download/v0.27.0/geckodriver-v0.27.0-macos.tar.gz');

        $filePath = $this->downloader->download($this->geckoMac, '.');

        self::assertEquals('./geckodriver', $filePath);
    }

    public function testDownloadLinux(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload();

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://github.com/mozilla/geckodriver/releases/download/v0.27.0/geckodriver-v0.27.0-linux64.tar.gz');

        $geckoLinux = new Driver(DriverName::GECKO(), Version::fromString('0.27.0'), OperatingSystem::LINUX());
        $filePath = $this->downloader->download($geckoLinux, '.');

        self::assertEquals('./geckodriver', $filePath);
    }

    public function testDownloadWindows(): void
    {
        $this->filesystem
            ->method('tempnam')
            ->willReturn(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'geckodriver-XXX.tar.gz');

        $this->archiveExtractor
            ->method('extract')
            ->willReturn(['./geckodriver.exe']);

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://github.com/mozilla/geckodriver/releases/download/v0.27.0/geckodriver-v0.27.0-win64.tar.gz');

        $geckoWindows = new Driver(DriverName::GECKO(), Version::fromString('0.27.0'), OperatingSystem::WINDOWS());
        $filePath = $this->downloader->download($geckoWindows, '.');

        self::assertEquals('./geckodriver.exe', $filePath);
    }

    private function mockFsAndArchiveExtractorForSuccessfulDownload(): void
    {
        $this->filesystem
            ->method('tempnam')
            ->willReturn(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'geckodriver-XXX.tar.gz');

        $this->archiveExtractor
            ->method('extract')
            ->willReturn(['./geckodriver']);
    }
}
