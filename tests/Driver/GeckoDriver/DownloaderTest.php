<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\GeckoDriver;

use DBrekelmans\BrowserDriverInstaller\Archive\Extractor;
use DBrekelmans\BrowserDriverInstaller\Cpu\CpuArchitecture;
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
    private Downloader $downloader;
    private Driver $geckoMac;
    private Driver $geckoMacArm64;
    private Stub&Filesystem $filesystem;
    private MockObject&HttpClientInterface $httpClient;
    private Extractor&MockObject $archiveExtractor;

    public function setUp(): void
    {
        $this->filesystem       = self::createStub(Filesystem::class);
        $this->httpClient       = $this->createMock(HttpClientInterface::class);
        $this->archiveExtractor = $this->createMock(Extractor::class);
        $this->downloader       = new Downloader($this->filesystem, $this->httpClient, $this->archiveExtractor);
        $this->geckoMac         = new Driver(DriverName::GECKO, Version::fromString('0.27.0'), OperatingSystem::MACOS, CpuArchitecture::AMD64);
        $this->geckoMacArm64    = new Driver(DriverName::GECKO, Version::fromString('0.35.0'), OperatingSystem::MACOS, CpuArchitecture::ARM64);
    }

    public function testSupportGecko(): void
    {
        self::assertTrue($this->downloader->supports($this->geckoMac));
        self::assertTrue($this->downloader->supports($this->geckoMacArm64));
    }

    public function testDoesNotSupportChromeDriver(): void
    {
        $chromeDriver = new Driver(DriverName::CHROME, Version::fromString('86.0.4240.22'), OperatingSystem::MACOS, CpuArchitecture::AMD64);
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

    public function testDownloadMacArm64(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload();

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://github.com/mozilla/geckodriver/releases/download/v0.35.0/geckodriver-v0.35.0-macos-aarch64.tar.gz');

        $filePath = $this->downloader->download($this->geckoMacArm64, '.');

        self::assertEquals('./geckodriver', $filePath);
    }

    public function testDownloadLinux(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload();

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://github.com/mozilla/geckodriver/releases/download/v0.27.0/geckodriver-v0.27.0-linux64.tar.gz');

        $geckoLinux = new Driver(DriverName::GECKO, Version::fromString('0.27.0'), OperatingSystem::LINUX, CpuArchitecture::AMD64);
        $filePath   = $this->downloader->download($geckoLinux, '.');

        self::assertEquals('./geckodriver', $filePath);
    }

    public function testDownloadLinuxArm64(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload();

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://github.com/mozilla/geckodriver/releases/download/v0.35.0/geckodriver-v0.35.0-linux-aarch64.tar.gz');

        $geckoLinux = new Driver(DriverName::GECKO, Version::fromString('0.35.0'), OperatingSystem::LINUX, CpuArchitecture::ARM64);
        $filePath   = $this->downloader->download($geckoLinux, '.');

        self::assertEquals('./geckodriver', $filePath);
    }

    public function testDownloadWindows(): void
    {
        $this->filesystem
            ->method('tempnam')
            ->willReturn(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'geckodriver-XXX.zip');

        $this->archiveExtractor
            ->method('extract')
            ->willReturn(['./geckodriver.exe']);

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://github.com/mozilla/geckodriver/releases/download/v0.27.0/geckodriver-v0.27.0-win64.zip');

        $geckoWindows = new Driver(DriverName::GECKO, Version::fromString('0.27.0'), OperatingSystem::WINDOWS, CpuArchitecture::AMD64);
        $filePath     = $this->downloader->download($geckoWindows, '.');

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
