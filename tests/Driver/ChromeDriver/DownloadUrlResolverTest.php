<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver\DownloadUrlResolver;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\json_encode;

final class DownloadUrlResolverTest extends TestCase
{
    private DownloadUrlResolver $urlResolver;

    /**
     * @return iterable<string, array{0: Driver, 1: string, 2: string}>
     */
    public static function byDriverDataProvider(): iterable
    {
        yield 'legacy version' => [
            new Driver(DriverName::CHROME(), Version::fromString('88.0.4299.0'), OperatingSystem::LINUX()),
            'some_binary',
            'https://chromedriver.storage.googleapis.com/88.0.4299.0/some_binary.zip',
        ];

        yield 'new version' => [
            new Driver(DriverName::CHROME(), Version::fromString('115.0.5790.170'), OperatingSystem::LINUX()),
            'linux64',
            'https://dynamic-url-2/',
        ];
    }

    /**
     * @dataProvider byDriverDataProvider
     */
    public function testByDriver(Driver $driver, string $binaryName, string $expectedUrl): void
    {
        self::assertSame($expectedUrl, $this->urlResolver->byDriver($driver, $binaryName));
    }

    protected function setUp(): void
    {
        $httpClientMock = new MockHttpClient(
            static function (string $method, string $url): MockResponse {
                if ($method === 'GET') {
                    if ($url === 'https://googlechromelabs.github.io/chrome-for-testing/latest-patch-versions-per-build-with-downloads.json') {
                        return new MockResponse(
                            json_encode([
                                'builds' => [
                                    '115.0.5790' => [
                                        'downloads' => [
                                            'chromedriver' => [
                                                ['platform' => 'win32', 'url' => 'https://dynamic-url-1/'],
                                                ['platform' => 'linux64', 'url' => 'https://dynamic-url-2/'],
                                            ],
                                        ],
                                    ],
                                ],
                            ])
                        );
                    }
                }

                return new MockResponse(
                    '<?xml version=\'1.0\' encoding=\'UTF-8\'?><Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Details>No such object: chromedriver/LATEST_RELEASE_xxx</Details></Error>',
                    ['http_code' => 404]
                );
            }
        );

        $this->urlResolver = new DownloadUrlResolver($httpClientMock);
    }
}
