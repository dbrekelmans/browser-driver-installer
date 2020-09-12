<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolverFactory;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class PathResolverFactoryTest extends TestCase
{
    public function testNoPathResolverImplemented() : void
    {
        $factory = new PathResolverFactory();

        $this->expectException(NotImplemented::class);
        $factory->createFromName(BrowserName::GOOGLE_CHROME());
    }

    public function testRegisteredPathResolverIsReturned() : void
    {
        $PathResolver = $this->createStub(PathResolver::class);
        $PathResolver->method('supports')->willReturn(true);

        $factory = new PathResolverFactory();
        $factory->register($PathResolver);

        self::assertSame($PathResolver, $factory->createFromName(BrowserName::GOOGLE_CHROME()));
    }

    public function testSupportedPathResolverIsReturned() : void
    {
        /** @var PathResolver&Stub $PathResolverA */
        $PathResolverA = $this->getMockBuilder(PathResolver::class)->setMockClassName('PathResolverA')->getMock();
        $PathResolverA->method('supports')->willReturn(false);

        /** @var PathResolver&Stub $PathResolverB */
        $PathResolverB = $this->getMockBuilder(PathResolver::class)->setMockClassName('PathResolverB')->getMock();
        $PathResolverB->method('supports')->willReturn(true);

        /** @var PathResolver&Stub $PathResolverC */
        $PathResolverC = $this->getMockBuilder(PathResolver::class)->setMockClassName('PathResolverC')->getMock();
        $PathResolverC->method('supports')->willReturn(false);

        $factory = new PathResolverFactory();
        $factory->register($PathResolverA);
        $factory->register($PathResolverB);
        $factory->register($PathResolverC);

        self::assertSame($PathResolverB, $factory->createFromName(BrowserName::GOOGLE_CHROME()));
    }

    public function testFirstSupportedPathResolverIsReturned() : void
    {
        /** @var PathResolver&Stub $PathResolverA */
        $PathResolverA = $this->getMockBuilder(PathResolver::class)->setMockClassName('PathResolverA')->getMock();
        $PathResolverA->method('supports')->willReturn(true);

        /** @var PathResolver&Stub $pathResolverB */
        $pathResolverB = $this->getMockBuilder(PathResolver::class)->setMockClassName('PathResolverB')->getMock();
        $pathResolverB->method('supports')->willReturn(true);

        $factory = new PathResolverFactory();
        $factory->register($PathResolverA);
        $factory->register($pathResolverB);

        self::assertSame($PathResolverA, $factory->createFromName(BrowserName::GOOGLE_CHROME()));
    }
}