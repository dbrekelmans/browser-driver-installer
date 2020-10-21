<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\Exception\Unsupported;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use UnexpectedValueException;

class VersionResolverTest extends TestCase
{
    private VersionResolver $versionResolver;
    private Browser $chrome;
    private Browser $chromium;
    private Browser $firefox;
    /** @var MockObject&HttpClientInterface  */
    private $httpClientMock;

    public function setUp() : void
    {
        $this->httpClientMock = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->versionResolver = new VersionResolver($this->httpClientMock);

        $this->chrome = new Browser(BrowserName::GOOGLE_CHROME(), Version::fromString('86.0.4240.80'), OperatingSystem::MACOS());
        $this->chromium = new Browser(BrowserName::GOOGLE_CHROME(), Version::fromString('88.0.4299.0'), OperatingSystem::MACOS());
        $this->firefox = new Browser(BrowserName::FIREFOX(), Version::fromString('81.0.2'), OperatingSystem::MACOS());
    }

    public function testSupportChrome() : void
    {
        self::assertTrue($this->versionResolver->supports($this->chrome));
    }

    public function testSupportChromium() : void
    {
        self::assertTrue($this->versionResolver->supports($this->chromium));
    }

    public function testDoesNotSupportFirefox() : void
    {
        self::assertFalse($this->versionResolver->supports($this->firefox));
    }

    public function testFromThrowsExceptionForFirefox() : void
    {
        self::expectException(Unsupported::class);
        $this->versionResolver->fromBrowser($this->firefox);
    }

    public function testFromGetVersionForChrome() : void
    {
        $this->mockHttpClientResponseContent(
            'GET',
            'https://chromedriver.storage.googleapis.com/LATEST_RELEASE_86.0.4240',
            '86.0.4240.22'
        );

        self::assertEquals(Version::fromString('86.0.4240.22'), $this->versionResolver->fromBrowser($this->chrome));
    }

    public function testFromExceptionIfCanNotParseVersionReceived() : void
    {
        self::expectException(UnexpectedValueException::class);
        $this->mockHttpClientResponseContent(
            'GET',
            'https://chromedriver.storage.googleapis.com/LATEST_RELEASE_86.0.4240',
            '<?xml version=\'1.0\' encoding=\'UTF-8\'?><Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Details>No such object: chromedriver/LATEST_RELEASE_xxx</Details></Error>'
        );

        $this->versionResolver->fromBrowser($this->chrome);
    }

    public function testLatest() : void
    {
        $this->mockHttpClientResponseContent(
            'GET',
            'https://chromedriver.storage.googleapis.com/LATEST_RELEASE',
            '86.0.4240.22'
        );

        self::assertEquals(Version::fromString('86.0.4240.22'), $this->versionResolver->latest());
    }

    private function mockHttpClientResponseContent(string $method, string $url, string $content) : void
    {
        $responseMock = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $responseMock->expects(self::any())
            ->method('getContent')
            ->willReturn($content);

        $this->httpClientMock->expects(self::any())
            ->method('request')
            ->with($method, $url)
            ->willReturn($responseMock);
    }
}
