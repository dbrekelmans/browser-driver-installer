<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverFactory;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
use DBrekelmans\BrowserDriverInstaller\Driver\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\Driver\VersionResolverFactory;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Tests\UniqueClassName;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DriverFactoryTest extends TestCase
{
    use UniqueClassName;

    #[DataProvider('createFromBrowserDataProvider')]
    public function testCreateFromBrowser(Driver $expectedDriver, Browser $browser): void
    {
        $versionResolver = self::createStub(VersionResolver::class);
        $versionResolver->method('fromBrowser')->willReturn(Version::fromString('1.0.0'));
        $versionResolver->method('supports')->willReturn(true);

        $versionResolverFactory = new VersionResolverFactory();
        $versionResolverFactory->register($versionResolver);

        $factory = new DriverFactory($versionResolverFactory);
        $driver  = $factory->createFromBrowser($browser);

        self::assertSameDriver($expectedDriver, $driver);
    }

    private static function assertSameDriver(Driver $expected, Driver $actual): void
    {
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->version->toBuildString(), $actual->version->toBuildString());
        self::assertSame($expected->operatingSystem, $actual->operatingSystem);
    }

    /** @return array<string, array{Driver, Browser}> */
    public static function createFromBrowserDataProvider(): array
    {
        return [
            'google_chrome' => [
                new Driver(DriverName::CHROME, Version::fromString('1.0.0'), OperatingSystem::LINUX),
                new Browser(BrowserName::GOOGLE_CHROME, Version::fromString('1.0.0'), OperatingSystem::LINUX),
            ],
            'chromium' => [
                new Driver(DriverName::CHROME, Version::fromString('1.0.0'), OperatingSystem::LINUX),
                new Browser(BrowserName::CHROMIUM, Version::fromString('1.0.0'), OperatingSystem::LINUX),
            ],
            'firefox' => [
                new Driver(DriverName::GECKO, Version::fromString('1.0.0'), OperatingSystem::LINUX),
                new Browser(BrowserName::FIREFOX, Version::fromString('1.0.0'), OperatingSystem::LINUX),
            ],
        ];
    }
}
