<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\Driver\VersionResolverFactory;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\TestCase;

final class VersionResolverFactoryTest extends TestCase
{
    public function testNoVersionResolverImplemented(): void
    {
        $factory = new VersionResolverFactory();

        $this->expectException(NotImplemented::class);
        $factory->createFromBrowser(
            new Browser(
                BrowserName::GOOGLE_CHROME,
                Version::fromString('1.0.0'),
                OperatingSystem::LINUX,
            ),
        );
    }

    public function testRegisteredVersionResolverIsReturned(): void
    {
        $versionResolver = self::createStub(VersionResolver::class);
        $versionResolver->method('supports')->willReturn(true);

        $factory = new VersionResolverFactory();
        $factory->register($versionResolver);

        self::assertSame(
            $versionResolver,
            $factory->createFromBrowser(
                new Browser(
                    BrowserName::GOOGLE_CHROME,
                    Version::fromString('1.0.0'),
                    OperatingSystem::LINUX,
                ),
            ),
        );
    }

    public function testSupportedVersionResolverIsReturned(): void
    {
        $versionResolverA = self::createStub(VersionResolver::class);
        $versionResolverA->method('supports')->willReturn(false);

        $versionResolverB = self::createStub(VersionResolver::class);
        $versionResolverB->method('supports')->willReturn(true);

        $versionResolverC = self::createStub(VersionResolver::class);
        $versionResolverC->method('supports')->willReturn(false);

        $factory = new VersionResolverFactory();
        $factory->register($versionResolverA, 'A');
        $factory->register($versionResolverB, 'B');
        $factory->register($versionResolverC, 'C');

        self::assertSame(
            $versionResolverB,
            $factory->createFromBrowser(
                new Browser(
                    BrowserName::GOOGLE_CHROME,
                    Version::fromString('1.0.0'),
                    OperatingSystem::LINUX,
                ),
            ),
        );
    }

    public function testFirstSupportedVersionResolverIsReturned(): void
    {
        $versionResolverA = self::createStub(VersionResolver::class);
        $versionResolverA->method('supports')->willReturn(true);

        $versionResolverB = self::createStub(VersionResolver::class);
        $versionResolverB->method('supports')->willReturn(true);

        $factory = new VersionResolverFactory();
        $factory->register($versionResolverA, 'A');
        $factory->register($versionResolverB, 'B');

        self::assertSame(
            $versionResolverA,
            $factory->createFromBrowser(
                new Browser(
                    BrowserName::GOOGLE_CHROME,
                    Version::fromString('1.0.0'),
                    OperatingSystem::LINUX,
                ),
            ),
        );
    }
}
