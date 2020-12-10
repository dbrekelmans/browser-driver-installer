<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Browser;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolverFactory;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\Tests\UniqueClassName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PathResolverFactoryTest extends TestCase
{
    use UniqueClassName;

    public function testNoPathResolverImplemented() : void
    {
        $factory = new PathResolverFactory();

        $this->expectException(NotImplemented::class);
        $factory->createFromName(BrowserName::GOOGLE_CHROME());
    }

    public function testRegisteredPathResolverIsReturned() : void
    {
        $pathResolver = $this->createMock(PathResolver::class);
        $pathResolver->method('supports')->willReturn(true);

        $factory = new PathResolverFactory();
        $factory->register($pathResolver);

        self::assertSame($pathResolver, $factory->createFromName(BrowserName::GOOGLE_CHROME()));
    }

    public function testSupportedPathResolverIsReturned() : void
    {
        /** @var PathResolver&MockObject $pathResolverA */
        $pathResolverA = $this->getMockBuilder(PathResolver::class)
            ->setMockClassName(self::uniqueClassName(PathResolver::class))
            ->getMock();
        $pathResolverA->method('supports')->willReturn(false);

        /** @var PathResolver&MockObject $pathResolverB */
        $pathResolverB = $this->getMockBuilder(PathResolver::class)
            ->setMockClassName(self::uniqueClassName(PathResolver::class))
            ->getMock();
        $pathResolverB->method('supports')->willReturn(true);

        /** @var PathResolver&MockObject $pathResolverC */
        $pathResolverC = $this->getMockBuilder(PathResolver::class)
            ->setMockClassName(self::uniqueClassName(PathResolver::class))
            ->getMock();
        $pathResolverC->method('supports')->willReturn(false);

        $factory = new PathResolverFactory();
        $factory->register($pathResolverA);
        $factory->register($pathResolverB);
        $factory->register($pathResolverC);

        self::assertSame($pathResolverB, $factory->createFromName(BrowserName::GOOGLE_CHROME()));
    }

    public function testFirstSupportedPathResolverIsReturned() : void
    {
        /** @var PathResolver&MockObject $pathResolverA */
        $pathResolverA = $this->getMockBuilder(PathResolver::class)
            ->setMockClassName(self::uniqueClassName(PathResolver::class))
            ->getMock();
        $pathResolverA->method('supports')->willReturn(true);

        /** @var PathResolver&MockObject $pathResolverB */
        $pathResolverB = $this->getMockBuilder(PathResolver::class)
            ->setMockClassName(self::uniqueClassName(PathResolver::class))
            ->getMock();
        $pathResolverB->method('supports')->willReturn(true);

        $factory = new PathResolverFactory();
        $factory->register($pathResolverA);
        $factory->register($pathResolverB);

        self::assertSame($pathResolverA, $factory->createFromName(BrowserName::GOOGLE_CHROME()));
    }
}
