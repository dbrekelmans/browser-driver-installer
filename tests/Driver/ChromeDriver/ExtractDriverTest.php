<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Archive\Extractor;
use DBrekelmans\BrowserDriverInstaller\Archive\MultiExtractor;
use DBrekelmans\BrowserDriverInstaller\Archive\TarGzExtractor;
use DBrekelmans\BrowserDriverInstaller\Archive\ZipExtractor;
use DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver\Downloader;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use ZipArchive;

class ExtractDriverTest extends TestCase
{
    /** @var Stub&Extractor */
    private $archiveExtractor;

    /** @var Filesystem */
    private $filesystem;

    public function testExtractLinux(): void
    {
        // A zip file containing a single 'chromedriver' file
        $mockResponse = new MockResponse(file_get_contents(__DIR__.'/../../fixtures/fakeDriverArchives/chromedriver.zip'));
        $mockHttpClient = new MockHttpClient([$mockResponse]);

        $downloader = new Downloader(new Filesystem(), $mockHttpClient, $this->archiveExtractor);

        $chromeDriverLinux = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::LINUX());
        $filePath = $downloader->download($chromeDriverLinux, '.');

        self::assertEquals('./chromedriver', $filePath);

        $this->filesystem->remove($filePath);
    }

    public function testExtractLinuxWithMultipleFiles(): void
    {
        // A zip file containing a 'chromedriver' file among others
        $mockResponse = new MockResponse(file_get_contents(__DIR__.'/../../fixtures/fakeDriverArchives/chromedriverMulti.zip'));
        $mockHttpClient = new MockHttpClient([$mockResponse]);

        $downloader = new Downloader(new Filesystem(), $mockHttpClient, $this->archiveExtractor);

        $chromeDriverLinux = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::LINUX());
        $filePath = $downloader->download($chromeDriverLinux, '.');

        self::assertEquals('./chromedriver', $filePath);

        $this->filesystem->remove($filePath);
    }

    public function testExtractWindows(): void
    {
        // A zip file containing a 'chromedriver.exe' file among others
        $mockResponse = new MockResponse(file_get_contents(__DIR__.'/../../fixtures/fakeDriverArchives/chromedriverMultiWindows.zip'));
        $mockHttpClient = new MockHttpClient([$mockResponse]);

        $downloader = new Downloader(new Filesystem(), $mockHttpClient, $this->archiveExtractor);

        $chromeDriverLinux = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::WINDOWS());
        $filePath = $downloader->download($chromeDriverLinux, '.');

        self::assertEquals('./chromedriver.exe', $filePath);

        $this->filesystem->remove($filePath);
    }

    public function testExtractFailsWithoutADriver(): void
    {
        // A zip file without a 'chromedriver' or 'chromedriver.exe' file
        $mockResponse = new MockResponse(file_get_contents(__DIR__.'/../../fixtures/fakeDriverArchives/notADriver.zip'));
        $mockHttpClient = new MockHttpClient([$mockResponse]);

        $downloaderMock = new Downloader(new Filesystem(), $mockHttpClient, $this->archiveExtractor);

        $chromeDriverLinux = new Driver(DriverName::CHROME(), Version::fromString('86.0.4240.22'), OperatingSystem::LINUX());

        $this->expectException(RuntimeException::class);
        $filePath = $downloaderMock->download($chromeDriverLinux, '.');

        self::assertEquals('./chromedriver', $filePath);
    }

    protected function setUp(): void
    {
        $zipArchive = new ZipArchive();
        $tarGzExtractor = new TarGzExtractor();
        $zipExtractor = new ZipExtractor($zipArchive);
        $this->archiveExtractor = new MultiExtractor();
        $this->archiveExtractor->register($zipExtractor);
        $this->archiveExtractor->register($tarGzExtractor);
        $this->filesystem = new Filesystem();
    }
}
