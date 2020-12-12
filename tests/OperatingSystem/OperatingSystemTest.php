<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests\OperatingSystem;

use DBrekelmans\BrowserDriverInstaller\OperatingSystem\Family;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use PHPUnit\Framework\TestCase;

class OperatingSystemTest extends TestCase
{
    /**
     * @dataProvider fromFamilyDataProvider
     */
    public function testFromFamily(OperatingSystem $expected, Family $family) : void
    {
        self::assertTrue($expected->equals(OperatingSystem::fromFamily($family)));
    }

    /**
     * @return array<string, array<mixed>>
     *
     * @psalm-return array<string, array{OperatingSystem, Family}>
     */
    public function fromFamilyDataProvider() : array
    {
        return [
            'windows' => [OperatingSystem::WINDOWS(), Family::WINDOWS()],
            'darwin' => [OperatingSystem::MACOS(), Family::DARWIN()],
            'bsd' => [OperatingSystem::LINUX(), Family::BSD()],
            'solaris' => [OperatingSystem::LINUX(), Family::SOLARIS()],
            'linux' => [OperatingSystem::LINUX(), Family::LINUX()],
            'unknown' => [OperatingSystem::LINUX(), Family::UNKNOWN()],
        ];
    }
}
