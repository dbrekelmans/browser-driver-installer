<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\Exception\Unsupported;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use UnexpectedValueException;

use function in_array;
use function Safe\json_encode;

class VersionResolverTest extends TestCase
{
    private VersionResolver $versionResolver;

    private Browser $chrome;

    private Browser $chromeJson;

    private Browser $chromium;

    private Browser $firefox;

    public function testSupportChrome(): void
    {
        self::assertTrue($this->versionResolver->supports($this->chrome));
    }

    public function testSupportChromium(): void
    {
        self::assertTrue($this->versionResolver->supports($this->chromium));
    }

    public function testDoesNotSupportFirefox(): void
    {
        self::assertFalse($this->versionResolver->supports($this->firefox));
    }

    public function testFromThrowsExceptionForFirefox(): void
    {
        $this->expectException(Unsupported::class);
        $this->versionResolver->fromBrowser($this->firefox);
    }

    public function testFromGetVersionForChrome(): void
    {
        self::assertEquals(Version::fromString('86.0.4240.22'), $this->versionResolver->fromBrowser($this->chrome));
        self::assertEquals(Version::fromString('115.0.5751.20'), $this->versionResolver->fromBrowser($this->chromeJson));
    }

    public function testFromExceptionIfCanNotParseVersionReceived(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $wrongChrome = new Browser(BrowserName::GOOGLE_CHROME, Version::fromString('1.0.0.0'), OperatingSystem::MACOS);

        $this->versionResolver->fromBrowser($wrongChrome);

        $wrongChromeJson = new Browser(BrowserName::GOOGLE_CHROME, Version::fromString('115.0.0.0'), OperatingSystem::MACOS);

        $this->versionResolver->fromBrowser($wrongChromeJson);
    }

    public function testFromGetBetaVersionForDevChrome(): void
    {
        $devChrome = new Browser(BrowserName::GOOGLE_CHROME, Version::fromString('123.0.6300.3'), OperatingSystem::MACOS);

        self::assertEquals(Version::fromString('122.0.6261.39'), $this->versionResolver->fromBrowser($devChrome));
    }

    public function testLatest(): void
    {
        self::assertEquals(Version::fromString('121.0.6167.184'), $this->versionResolver->latest());
    }

    public function testIsJsonVersion(): void
    {
        self::assertFalse(VersionResolver::isJsonVersion(Version::fromString('114.0.5735.90')));
        self::assertTrue(VersionResolver::isJsonVersion(Version::fromString('115.0.5751')));
    }

    protected function setUp(): void
    {
        $httpClientMock        = new MockHttpClient(
            static function (string $method, string $url): MockResponse {
                $urlsGiving86Version = [
                    'https://chromedriver.storage.googleapis.com/LATEST_RELEASE_86.0.4240',
                    'https://chromedriver.storage.googleapis.com/LATEST_RELEASE_86',
                    'https://chromedriver.storage.googleapis.com/LATEST_RELEASE',
                ];
                if ($method === 'GET') {
                    if (in_array($url, $urlsGiving86Version, true)) {
                        return new MockResponse('86.0.4240.22');
                    }

                    if ($url === 'https://chromedriver.storage.googleapis.com/LATEST_RELEASE_87') {
                        return new MockResponse('87.0.4280.20');
                    }

                    if ($url === 'https://googlechromelabs.github.io/chrome-for-testing/last-known-good-versions.json') {
                        return new MockResponse(
                            json_encode([
                                'channels' => [
                                    'Stable' => ['version' => '121.0.6167.184'],
                                    'Beta' => ['version' => '122.0.6261.39'],
                                ],
                            ]),
                        );
                    }

                    if ($url === 'https://googlechromelabs.github.io/chrome-for-testing/latest-patch-versions-per-build.json') {
                        return new MockResponse(
                            json_encode([
                                'builds' => [
                                    '115.0.5751' => ['version' => '115.0.5751.20'],
                                    '122.0.6261' => ['version' => '122.0.6261.39'],
                                ],
                            ]),
                        );
                    }
                }

                return new MockResponse(
                    '<?xml version=\'1.0\' encoding=\'UTF-8\'?><Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Details>No such object: chromedriver/LATEST_RELEASE_xxx</Details></Error>',
                    ['http_code' => 404],
                );
            },
        );
        $this->versionResolver = new VersionResolver($httpClientMock);

        $this->chrome     = new Browser(BrowserName::GOOGLE_CHROME, Version::fromString('86.0.4240.80'), OperatingSystem::MACOS);
        $this->chromeJson = new Browser(BrowserName::GOOGLE_CHROME, Version::fromString('115.0.5751.2'), OperatingSystem::MACOS);
        $this->chromium   = new Browser(BrowserName::GOOGLE_CHROME, Version::fromString('88.0.4299.0'), OperatingSystem::MACOS);
        $this->firefox    = new Browser(BrowserName::FIREFOX, Version::fromString('81.0.2'), OperatingSystem::MACOS);
    }
}
