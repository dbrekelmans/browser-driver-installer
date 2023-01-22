<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Archive\Extractor;
use DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver\Downloader;
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
    /** @var Downloader */
    private $downloader;

    /** @var Stub&Filesystem */
    private $filesystem;

    /** @var Stub&Extractor */
    private $archiveExtractor;

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

        $this->httpClient
            ->expects(self::atLeastOnce())
            ->method('request')
            ->with('GET', 'https://chromedriver.storage.googleapis.com/86.0.4240.22/chromedriver_mac64.zip');

        $chromeDriverMac = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::MACOS());
        $filePath        = $this->downloader->download($chromeDriverMac, '.');

        self::assertEquals('./chromedriver', $filePath);
    }

    public function testDownloadLinux(): void
    {
        $this->mockFsAndArchiveExtractorForSuccessfulDownload(OperatingSystem::LINUX());

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
        $this->mockFsAndArchiveExtractorForSuccessfulDownload(OperatingSystem::WINDOWS());

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
    }

    private function mockFsAndArchiveExtractorForSuccessfulDownload(OperatingSystem $operatingSystem): void
    {
        $this->filesystem
            ->method('tempnam')
            ->willReturn(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'chromedriver-XXX.zip');
        $this->filesystem
            ->method('readLink')
            ->willReturn('YYY');

        $binaryFilename = 'chromedriver' . ($operatingSystem->equals(OperatingSystem::WINDOWS()) ? '.exe' : '');

        $extractPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'chromedriver' . DIRECTORY_SEPARATOR;
        $this->archiveExtractor
            ->method('extract')
            ->willReturn([
                $extractPath . $binaryFilename,
                $extractPath . 'LICENSE.chromedriver',
            ]);
    }
}
