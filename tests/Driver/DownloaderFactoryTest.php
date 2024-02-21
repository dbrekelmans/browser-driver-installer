<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\Driver;

use DBrekelmans\BrowserDriverInstaller\Driver\Downloader;
use DBrekelmans\BrowserDriverInstaller\Driver\DownloaderFactory;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use PHPUnit\Framework\TestCase;

final class DownloaderFactoryTest extends TestCase
{
    public function testNoDownloaderImplemented(): void
    {
        $factory = new DownloaderFactory();

        $this->expectException(NotImplemented::class);
        $factory->createFromDriver(
            new Driver(DriverName::CHROME, Version::fromString('1.0.0'), OperatingSystem::LINUX),
        );
    }

    public function testRegisteredDownloaderIsReturned(): void
    {
        $downloader = self::createStub(Downloader::class);
        $downloader->method('supports')->willReturn(true);

        $factory = new DownloaderFactory();
        $factory->register($downloader);

        self::assertSame(
            $downloader,
            $factory->createFromDriver(
                new Driver(DriverName::CHROME, Version::fromString('1.0.0'), OperatingSystem::LINUX),
            ),
        );
    }

    public function testSupportedDownloaderIsReturned(): void
    {
        $downloaderA = self::createStub(Downloader::class);
        $downloaderA->method('supports')->willReturn(false);

        $downloaderB = self::createStub(Downloader::class);
        $downloaderB->method('supports')->willReturn(true);

        $downloaderC = self::createStub(Downloader::class);
        $downloaderC->method('supports')->willReturn(false);

        $factory = new DownloaderFactory();
        $factory->register($downloaderA, 'A');
        $factory->register($downloaderB, 'B');
        $factory->register($downloaderC, 'C');

        self::assertSame(
            $downloaderB,
            $factory->createFromDriver(
                new Driver(DriverName::CHROME, Version::fromString('1.0.0'), OperatingSystem::LINUX),
            ),
        );
    }

    public function testFirstSupportedDownloaderIsReturned(): void
    {
        $downloaderA = self::createStub(Downloader::class);
        $downloaderA->method('supports')->willReturn(true);

        $downloaderB = self::createStub(Downloader::class);
        $downloaderB->method('supports')->willReturn(true);

        $factory = new DownloaderFactory();
        $factory->register($downloaderA, 'A');
        $factory->register($downloaderB, 'B');

        self::assertSame(
            $downloaderA,
            $factory->createFromDriver(
                new Driver(DriverName::CHROME, Version::fromString('1.0.0'), OperatingSystem::LINUX),
            ),
        );
    }
}
