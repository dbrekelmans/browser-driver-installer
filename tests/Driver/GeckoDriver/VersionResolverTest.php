<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\GeckoDriver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\GeckoDriver\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;

class VersionResolverTest extends TestCase
{
    private MockHttpClient $httpClient;
    private VersionResolver $versionResolver;
    private Browser $chrome;
    private Browser $firefox;

    protected function setUp(): void
    {
        $this->httpClient      = new MockHttpClient(
            static function (string $method, string $url): MockResponse {
                if ($method === 'GET' && $url === 'https://api.github.com/repos/mozilla/geckodriver/releases/latest') {
                    return new MockResponse(file_get_contents(__DIR__ . '/../../fixtures/githubResponseGeckoLatest.json'));
                }

                return new MockResponse('Unknown page', ['http_code' => 404]);
            }
        );
        $this->versionResolver = new VersionResolver($this->httpClient);
        $this->chrome          = new Browser(BrowserName::GOOGLE_CHROME(), Version::fromString('86.0.4240.80'), OperatingSystem::MACOS());
        $this->firefox         = new Browser(BrowserName::FIREFOX(), Version::fromString('81.0.2'), OperatingSystem::MACOS());
    }

    public function testDoesNotSupportChrome(): void
    {
        self::assertFalse($this->versionResolver->supports($this->chrome));
    }

    public function testSupportsFirefox(): void
    {
        self::assertTrue($this->versionResolver->supports($this->firefox));
    }

    public function testLatestVersionForRecentBrowser(): void
    {
        $geckoVersion = $this->versionResolver->fromBrowser($this->firefox);

        self::assertEquals(Version::fromString('0.28.0'), $geckoVersion);
    }

    public function testVersionForOldBrowser(): void
    {
        $firefox = new Browser(BrowserName::FIREFOX(), Version::fromString('57.0.0'), OperatingSystem::MACOS());

        $geckoVersion = $this->versionResolver->fromBrowser($firefox);

        self::assertEquals(Version::fromString('0.25.0'), $geckoVersion);
    }

    public function testNoVersionForVeryOldBrowser(): void
    {
        self::expectException(RuntimeException::class);

        $firefox = new Browser(BrowserName::FIREFOX(), Version::fromString('51.0.0'), OperatingSystem::MACOS());

        $this->versionResolver->fromBrowser($firefox);
    }
}
