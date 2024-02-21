<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolverFactory;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use PHPUnit\Framework\TestCase;

final class VersionResolverFactoryTest extends TestCase
{
    public function testNoVersionResolverImplemented(): void
    {
        $factory = new VersionResolverFactory();

        $this->expectException(NotImplemented::class);
        $factory->createFromName(BrowserName::GOOGLE_CHROME);
    }

    public function testRegisteredVersionResolverIsReturned(): void
    {
        $versionResolver = self::createStub(VersionResolver::class);
        $versionResolver->method('supports')->willReturn(true);

        $factory = new VersionResolverFactory();
        $factory->register($versionResolver);

        self::assertSame($versionResolver, $factory->createFromName(BrowserName::GOOGLE_CHROME));
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

        self::assertSame($versionResolverB, $factory->createFromName(BrowserName::GOOGLE_CHROME));
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

        self::assertSame($versionResolverA, $factory->createFromName(BrowserName::GOOGLE_CHROME));
    }
}
