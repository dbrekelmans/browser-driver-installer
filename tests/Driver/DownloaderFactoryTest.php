<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver;

use DBrekelmans\BrowserDriverInstaller\Driver\Downloader;
use DBrekelmans\BrowserDriverInstaller\Driver\DownloaderFactory;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Tests\UniqueClassName;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class DownloaderFactoryTest extends TestCase
{
    use UniqueClassName;

    public function testNoDownloaderImplemented() : void
    {
        $factory = new DownloaderFactory();

        $this->expectException(NotImplemented::class);
        $factory->createFromDriver(
            new Driver(DriverName::CHROME(), Version::fromString('1.0.0'), OperatingSystem::LINUX())
        );
    }

    public function testRegisteredDownloaderIsReturned() : void
    {
        $downloader = $this->createStub(Downloader::class);
        $downloader->method('supports')->willReturn(true);

        $factory = new DownloaderFactory();
        $factory->register($downloader);

        self::assertSame(
            $downloader,
            $factory->createFromDriver(
                new Driver(DriverName::CHROME(), Version::fromString('1.0.0'), OperatingSystem::LINUX())
            )
        );
    }

    public function testSupportedDownloaderIsReturned() : void
    {
        /** @var Downloader&Stub $downloaderA */
        $downloaderA = $this->getMockBuilder(Downloader::class)
            ->setMockClassName(self::uniqueClassName(Downloader::class))
            ->getMock();
        $downloaderA->method('supports')->willReturn(false);

        /** @var Downloader&Stub $downloaderB */
        $downloaderB = $this->getMockBuilder(Downloader::class)
            ->setMockClassName(self::uniqueClassName(Downloader::class))
            ->getMock();
        $downloaderB->method('supports')->willReturn(true);

        /** @var Downloader&Stub $downloaderC */
        $downloaderC = $this->getMockBuilder(Downloader::class)
            ->setMockClassName(self::uniqueClassName(Downloader::class))
            ->getMock();
        $downloaderC->method('supports')->willReturn(false);

        $factory = new DownloaderFactory();
        $factory->register($downloaderA);
        $factory->register($downloaderB);
        $factory->register($downloaderC);

        self::assertSame(
            $downloaderB,
            $factory->createFromDriver(
                new Driver(DriverName::CHROME(), Version::fromString('1.0.0'), OperatingSystem::LINUX())
            )
        );
    }

    public function testFirstSupportedDownloaderIsReturned() : void
    {
        /** @var Downloader&Stub $downloaderA */
        $downloaderA = $this->getMockBuilder(Downloader::class)
            ->setMockClassName(self::uniqueClassName(Downloader::class))
            ->getMock();
        $downloaderA->method('supports')->willReturn(true);

        /** @var Downloader&Stub $downloaderB */
        $downloaderB = $this->getMockBuilder(Downloader::class)
            ->setMockClassName(self::uniqueClassName(Downloader::class))
            ->getMock();
        $downloaderB->method('supports')->willReturn(true);

        $factory = new DownloaderFactory();
        $factory->register($downloaderA);
        $factory->register($downloaderB);

        self::assertSame(
            $downloaderA,
            $factory->createFromDriver(
                new Driver(DriverName::CHROME(), Version::fromString('1.0.0'), OperatingSystem::LINUX())
            )
        );
    }
}
