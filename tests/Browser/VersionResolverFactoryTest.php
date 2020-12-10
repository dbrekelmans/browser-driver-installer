<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolverFactory;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\Tests\UniqueClassName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VersionResolverFactoryTest extends TestCase
{
    use UniqueClassName;

    public function testNoVersionResolverImplemented() : void
    {
        $factory = new VersionResolverFactory();

        $this->expectException(NotImplemented::class);
        $factory->createFromName(BrowserName::GOOGLE_CHROME());
    }

    public function testRegisteredVersionResolverIsReturned() : void
    {
        $versionResolver = $this->createMock(VersionResolver::class);
        $versionResolver->method('supports')->willReturn(true);

        $factory = new VersionResolverFactory();
        $factory->register($versionResolver);

        self::assertSame($versionResolver, $factory->createFromName(BrowserName::GOOGLE_CHROME()));
    }

    public function testSupportedVersionResolverIsReturned() : void
    {
        /** @var VersionResolver&MockObject $versionResolverA */
        $versionResolverA = $this->getMockBuilder(VersionResolver::class)
            ->setMockClassName(self::uniqueClassName(VersionResolver::class))
            ->getMock();
        $versionResolverA->method('supports')->willReturn(false);

        /** @var VersionResolver&MockObject $versionResolverB */
        $versionResolverB = $this->getMockBuilder(VersionResolver::class)
            ->setMockClassName(self::uniqueClassName(VersionResolver::class))
            ->getMock();
        $versionResolverB->method('supports')->willReturn(true);

        /** @var VersionResolver&MockObject $versionResolverC */
        $versionResolverC = $this->getMockBuilder(VersionResolver::class)
            ->setMockClassName(self::uniqueClassName(VersionResolver::class))
            ->getMock();
        $versionResolverC->method('supports')->willReturn(false);

        $factory = new VersionResolverFactory();
        $factory->register($versionResolverA);
        $factory->register($versionResolverB);
        $factory->register($versionResolverC);

        self::assertSame($versionResolverB, $factory->createFromName(BrowserName::GOOGLE_CHROME()));
    }

    public function testFirstSupportedVersionResolverIsReturned() : void
    {
        /** @var VersionResolver&MockObject $versionResolverA */
        $versionResolverA = $this->getMockBuilder(VersionResolver::class)
            ->setMockClassName(self::uniqueClassName(VersionResolver::class))
            ->getMock();
        $versionResolverA->method('supports')->willReturn(true);

        /** @var VersionResolver&MockObject $versionResolverB */
        $versionResolverB = $this->getMockBuilder(VersionResolver::class)
            ->setMockClassName(self::uniqueClassName(VersionResolver::class))
            ->getMock();
        $versionResolverB->method('supports')->willReturn(true);

        $factory = new VersionResolverFactory();
        $factory->register($versionResolverA);
        $factory->register($versionResolverB);

        self::assertSame($versionResolverA, $factory->createFromName(BrowserName::GOOGLE_CHROME()));
    }
}
