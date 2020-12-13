<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Archive\Extractor;
use DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver\Downloader;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPStan\Testing\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

class DownloaderTest extends TestCase
{
    /** @var Downloader */
    private $downloader;

    /** @var Driver */
    private $chromeDriverMac;

    /** @var Stub&Filesystem */
    private $filesystem;

    /** @var Stub&Extractor */
    private $archiveExtractor;

    /** @var MockObject&HttpClientInterface */
    private $httpClient;

    public function testSupportChrome(): void
    {
        self::assertTrue($this->downloader->supports($this->chromeDriverMac));
    }

    public function testDoesNotSupportGecko(): void
    {
        $geckoDriver = new Driver(DriverName::GECKO(), Version::fromString('0.27.0'), OperatingSystem::MACOS());
        self::assertFalse($this->downloader->supports($geckoDriver));
    }

    public function testDownloadMac(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload();

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://chromedriver.storage.googleapis.com/86.0.4240.22/chromedriver_mac64.zip');

        $filePath = $this->downloader->download($this->chromeDriverMac, '.');

        self::assertEquals('./chromedriver', $filePath);
    }

    public function testDownloadLinux(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload();

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://chromedriver.storage.googleapis.com/86.0.4240.22/chromedriver_linux64.zip');

        $chromeDriverLinux = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::LINUX());
        $filePath          = $this->downloader->download($chromeDriverLinux, '.');

        self::assertEquals('./chromedriver', $filePath);
    }

    public function testDownloadWindows(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload();

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://chromedriver.storage.googleapis.com/86.0.4240.22/chromedriver_win32.zip');

        $chromeDriverLinux = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::WINDOWS());
        $filePath          = $this->downloader->download($chromeDriverLinux, '.');

        self::assertEquals('./chromedriver.exe', $filePath);
    }

    protected function setUp(): void
    {
        $this->filesystem       = $this->createStub(Filesystem::class);
        $this->httpClient       = $this->createMock(HttpClientInterface::class);
        $this->archiveExtractor = $this->createStub(Extractor::class);
        $this->downloader       = new Downloader($this->filesystem, $this->httpClient, $this->archiveExtractor);

        $this->chromeDriverMac = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::MACOS());
    }

    private function mockFsAndArchiveExtractorForSuccessfulDownload(): void
    {
        $this->filesystem
            ->method('tempnam')
            ->willReturn(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'chromedriver-XXX.zip');
        $this->filesystem
            ->method('readLink')
            ->willReturn('YYY');

        $this->archiveExtractor
            ->method('extract')
            ->willReturn(['./chromedriver']);
    }
}
