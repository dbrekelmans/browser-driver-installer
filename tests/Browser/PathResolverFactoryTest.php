<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolverFactory;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use PHPUnit\Framework\TestCase;

final class PathResolverFactoryTest extends TestCase
{
    public function testNoPathResolverImplemented(): void
    {
        $factory = new PathResolverFactory();

        $this->expectException(NotImplemented::class);
        $factory->createFromName(BrowserName::GOOGLE_CHROME);
    }

    public function testRegisteredPathResolverIsReturned(): void
    {
        $pathResolver = self::createStub(PathResolver::class);
        $pathResolver->method('supports')->willReturn(true);

        $factory = new PathResolverFactory();
        $factory->register($pathResolver);

        self::assertSame($pathResolver, $factory->createFromName(BrowserName::GOOGLE_CHROME));
    }

    public function testSupportedPathResolverIsReturned(): void
    {
        $pathResolverA = self::createStub(PathResolver::class);
        $pathResolverA->method('supports')->willReturn(false);

        $pathResolverB = self::createStub(PathResolver::class);
        $pathResolverB->method('supports')->willReturn(true);

        $pathResolverC = self::createStub(PathResolver::class);
        $pathResolverC->method('supports')->willReturn(false);

        $factory = new PathResolverFactory();
        $factory->register($pathResolverA, 'A');
        $factory->register($pathResolverB, 'B');
        $factory->register($pathResolverC, 'C');

        self::assertSame($pathResolverB, $factory->createFromName(BrowserName::GOOGLE_CHROME));
    }

    public function testFirstSupportedPathResolverIsReturned(): void
    {
        $pathResolverA = self::createStub(PathResolver::class);
        $pathResolverA->method('supports')->willReturn(true);

        $pathResolverB = self::createStub(PathResolver::class);
        $pathResolverB->method('supports')->willReturn(true);

        $factory = new PathResolverFactory();
        $factory->register($pathResolverA, 'A');
        $factory->register($pathResolverB, 'B');

        self::assertSame($pathResolverA, $factory->createFromName(BrowserName::GOOGLE_CHROME));
    }
}
