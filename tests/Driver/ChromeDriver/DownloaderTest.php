<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Archive\Extractor;
use DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver\Downloader;
use DBrekelmans\BrowserDriverInstaller\Driver\DownloadUrlResolver;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
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

    /** @var Stub&Filesystem */
    private $filesystem;

    /** @var Stub&Extractor */
    private $archiveExtractor;

    /** @var MockObject&DownloadUrlResolver */
    private $downloadUrlResolver;

    /** @var MockObject&HttpClientInterface */
    private $httpClient;

    public function testSupportChrome(): void
    {
        $chromeDriverLinux = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::LINUX());
        self::assertTrue($this->downloader->supports($chromeDriverLinux));
    }

    public function testDoesNotSupportGecko(): void
    {
        $geckoDriver = new Driver(DriverName::GECKO(), Version::fromString('0.27.0'), OperatingSystem::LINUX());
        self::assertFalse($this->downloader->supports($geckoDriver));
    }

    public function testDownloadMac(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload(OperatingSystem::MACOS());

        $chromeDriverMac = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::MACOS());

        $this->downloadUrlResolver
            ->expects(self::once())
            ->method('byDriver')
            ->with($chromeDriverMac, 'chromedriver_mac64')
            ->willReturn('https://chromedriver.storage.googleapis.com/86.0.4240.22/chromedriver_mac64.zip');

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://chromedriver.storage.googleapis.com/86.0.4240.22/chromedriver_mac64.zip');

        $filePath = $this->downloader->download($chromeDriverMac, '.');

        self::assertEquals('./chromedriver', $filePath);
    }

    public function testDownloadMacJson(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload(OperatingSystem::MACOS(), true);

        $chromeDriverMac = new Driver(DriverName::CHROME(), Version::fromString('115.0.5790.170'), OperatingSystem::MACOS());

        $this->downloadUrlResolver
            ->expects(self::once())
            ->method('byDriver')
            ->with($chromeDriverMac, 'mac-x64')
            ->willReturn('https://dynamic-download-url/driver.zip');

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://dynamic-download-url/driver.zip');

        $filePath = $this->downloader->download($chromeDriverMac, '.');

        self::assertEquals('./chromedriver', $filePath);
    }

    public function testDownloadLinux(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload(OperatingSystem::LINUX());

        $chromeDriverLinux = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::LINUX());

        $this->downloadUrlResolver
            ->expects(self::once())
            ->method('byDriver')
            ->with($chromeDriverLinux, 'chromedriver_linux64')
            ->willReturn('https://chromedriver.storage.googleapis.com/86.0.4240.22/chromedriver_linux64.zip');

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://chromedriver.storage.googleapis.com/86.0.4240.22/chromedriver_linux64.zip');

        $filePath = $this->downloader->download($chromeDriverLinux, '.');

        self::assertEquals('./chromedriver', $filePath);
    }

    public function testDownloadLinuxJson(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload(OperatingSystem::LINUX(), true);

        $chromeDriverLinux = new Driver(DriverName::CHROME(), Version::fromString('115.0.5790.170'), OperatingSystem::LINUX());

        $this->downloadUrlResolver
            ->expects(self::once())
            ->method('byDriver')
            ->with($chromeDriverLinux, 'linux64')
            ->willReturn('https://dynamic-download-url/driver.zip');

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://dynamic-download-url/driver.zip');

        $filePath = $this->downloader->download($chromeDriverLinux, '.');

        self::assertEquals('./chromedriver', $filePath);
    }

    public function testDownloadWindows(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload(OperatingSystem::WINDOWS());

        $chromeDriverWindows = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::WINDOWS());

        $this->downloadUrlResolver
            ->expects(self::once())
            ->method('byDriver')
            ->with($chromeDriverWindows, 'chromedriver_win32')
            ->willReturn('https://chromedriver.storage.googleapis.com/86.0.4240.22/chromedriver_win32.zip');

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://chromedriver.storage.googleapis.com/86.0.4240.22/chromedriver_win32.zip');

        $filePath = $this->downloader->download($chromeDriverWindows, '.');

        self::assertEquals('./chromedriver.exe', $filePath);
    }

    public function testDownloadWindowsJson(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload(OperatingSystem::WINDOWS(), true);

        $chromeDriverWindows = new Driver(DriverName::CHROME(), Version::fromString('115.0.5790.170'), OperatingSystem::WINDOWS());

        $this->downloadUrlResolver
            ->expects(self::once())
            ->method('byDriver')
            ->with($chromeDriverWindows, 'win32')
            ->willReturn('https://dynamic-download-url/driver.zip');

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://dynamic-download-url/driver.zip');

        $filePath = $this->downloader->download($chromeDriverWindows, '.');

        self::assertEquals('./chromedriver.exe', $filePath);
    }

    protected function setUp(): void
    {
        $this->filesystem          = $this->createStub(Filesystem::class);
        $this->httpClient          = $this->createMock(HttpClientInterface::class);
        $this->archiveExtractor    = $this->createStub(Extractor::class);
        $this->downloadUrlResolver = $this->createMock(DownloadUrlResolver::class);
        $this->downloader          = new Downloader($this->filesystem, $this->httpClient, $this->archiveExtractor, $this->downloadUrlResolver);
    }

    private function mockFsAndArchiveExtractorForSuccessfulDownload(
        OperatingSystem $operatingSystem,
        bool $isJsonVersion = false
    ): void {
        $this->filesystem
            ->method('tempnam')
            ->willReturn(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'chromedriver-XXX.zip');
        $this->filesystem
            ->method('readLink')
            ->willReturn('YYY');

        $binaryFilename = 'chromedriver' . ($operatingSystem->equals(OperatingSystem::WINDOWS()) ? '.exe' : '');

        $extractPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'chromedriver' . DIRECTORY_SEPARATOR;
        if ($isJsonVersion) {
            if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
                $extractPath .= 'chromedriver-win32/';
            }

            if ($operatingSystem->equals(OperatingSystem::MACOS())) {
                $extractPath .= 'chromedriver-mac-x64/';
            }

            if ($operatingSystem->equals(OperatingSystem::LINUX())) {
                $extractPath .= 'chromedriver-linux64/';
            }
        }

        $this->archiveExtractor
            ->method('extract')
            ->willReturn([
                $extractPath . $binaryFilename,
                $extractPath . 'LICENSE.chromedriver',
            ]);
    }
}
