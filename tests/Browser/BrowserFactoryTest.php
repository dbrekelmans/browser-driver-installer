<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserFactory;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolverFactory;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolverFactory;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\TestCase;

final class BrowserFactoryTest extends TestCase
{
    private static function assertBrowser(Browser $expected, Browser $actual): void
    {
        self::assertTrue($expected->name()->equals($actual->name()));
        self::assertTrue($expected->operatingSystem()->equals($actual->operatingSystem()));
        self::assertSame($expected->version()->toBuildString(), $actual->version()->toBuildString());
    }

    public function testCreateFromNameOperatingSystem(): void
    {
        $pathResolverFactory = new PathResolverFactory();
        $versionResolverFactory = new VersionResolverFactory();

        $factory = new BrowserFactory($pathResolverFactory, $versionResolverFactory);

        $name = BrowserName::GOOGLE_CHROME();
        $version = Version::fromString('1.0.0');
        $operatingSystem = OperatingSystem::LINUX();

        $versionResolver = $this->createStub(VersionResolver::class);
        $versionResolver->method('supports')->willReturn(true);
        $versionResolver->method('from')->willReturn($version);

        $versionResolverFactory->register($versionResolver);

        $pathResolver = $this->createStub(PathResolver::class);
        $pathResolver->method('supports')->willReturn(true);
        $pathResolver->method('from')->willReturn('');

        $pathResolverFactory->register($pathResolver);

        self::assertBrowser(
            new Browser($name, $version, $operatingSystem),
            $factory->createFromNameAndOperatingSystem($name, $operatingSystem)
        );
    }

    public function testCreateFromNameOperatingSystemAndPath(): void
    {
        $versionResolverFactory = new VersionResolverFactory();

        $factory = new BrowserFactory(new PathResolverFactory(), $versionResolverFactory);

        $name = BrowserName::GOOGLE_CHROME();
        $version = Version::fromString('1.0.0');
        $operatingSystem = OperatingSystem::LINUX();

        $versionResolver = $this->createStub(VersionResolver::class);
        $versionResolver->method('supports')->willReturn(true);
        $versionResolver->method('from')->willReturn($version);

        $versionResolverFactory->register($versionResolver);

        self::assertBrowser(
            new Browser($name, $version, $operatingSystem),
            $factory->createFromNameOperatingSystemAndPath($name, $operatingSystem, '')
        );
    }
}
