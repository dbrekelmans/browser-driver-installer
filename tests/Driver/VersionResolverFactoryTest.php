<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\Driver\VersionResolverFactory;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Tests\UniqueClassName;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class VersionResolverFactoryTest extends TestCase
{
    use UniqueClassName;

    public function testNoVersionResolverImplemented() : void
    {
        $factory = new VersionResolverFactory();

        $this->expectException(NotImplemented::class);
        $factory->createFromBrowser(
            new Browser(
                BrowserName::GOOGLE_CHROME(),
                Version::fromString('1.0.0'),
                OperatingSystem::LINUX()
            )
        );
    }

    public function testRegisteredVersionResolverIsReturned() : void
    {
        $versionResolver = $this->createStub(VersionResolver::class);
        $versionResolver->method('supports')->willReturn(true);

        $factory = new VersionResolverFactory();
        $factory->register($versionResolver);

        self::assertSame(
            $versionResolver,
            $factory->createFromBrowser(
                new Browser(
                    BrowserName::GOOGLE_CHROME(),
                    Version::fromString('1.0.0'),
                    OperatingSystem::LINUX()
                )
            )
        );
    }

    public function testSupportedVersionResolverIsReturned() : void
    {
        /** @var VersionResolver&Stub $versionResolverA */
        $versionResolverA = $this->getMockBuilder(VersionResolver::class)
            ->setMockClassName(self::uniqueClassName(VersionResolver::class))
            ->getMock();
        $versionResolverA->method('supports')->willReturn(false);

        /** @var VersionResolver&Stub $versionResolverB */
        $versionResolverB = $this->getMockBuilder(VersionResolver::class)
            ->setMockClassName(self::uniqueClassName(VersionResolver::class))
            ->getMock();
        $versionResolverB->method('supports')->willReturn(true);

        /** @var VersionResolver&Stub $versionResolverC */
        $versionResolverC = $this->getMockBuilder(VersionResolver::class)
            ->setMockClassName(self::uniqueClassName(VersionResolver::class))
            ->getMock();
        $versionResolverC->method('supports')->willReturn(false);

        $factory = new VersionResolverFactory();
        $factory->register($versionResolverA);
        $factory->register($versionResolverB);
        $factory->register($versionResolverC);

        self::assertSame(
            $versionResolverB,
            $factory->createFromBrowser(
                new Browser(
                    BrowserName::GOOGLE_CHROME(),
                    Version::fromString('1.0.0'),
                    OperatingSystem::LINUX()
                )
            )
        );
    }

    public function testFirstSupportedVersionResolverIsReturned() : void
    {
        /** @var VersionResolver&Stub $versionResolverA */
        $versionResolverA = $this->getMockBuilder(VersionResolver::class)
            ->setMockClassName(self::uniqueClassName(VersionResolver::class))
            ->getMock();
        $versionResolverA->method('supports')->willReturn(true);

        /** @var VersionResolver&Stub $versionResolverB */
        $versionResolverB = $this->getMockBuilder(VersionResolver::class)
            ->setMockClassName(self::uniqueClassName(VersionResolver::class))
            ->getMock();
        $versionResolverB->method('supports')->willReturn(true);

        $factory = new VersionResolverFactory();
        $factory->register($versionResolverA);
        $factory->register($versionResolverB);

        self::assertSame(
            $versionResolverA,
            $factory->createFromBrowser(
                new Browser(
                    BrowserName::GOOGLE_CHROME(),
                    Version::fromString('1.0.0'),
                    OperatingSystem::LINUX()
                )
            )
        );
    }
}
