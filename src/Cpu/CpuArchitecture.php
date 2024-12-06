<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Cpu;

use function php_uname;

enum CpuArchitecture: string
{
    case AMD64 = 'amd64';
    case ARM64 = 'arm64';

    public function toString(): string
    {
        return match ($this) {
            self::AMD64 => ' amd64',
            self::ARM64 => ' arm64',
        };
    }

    public static function detectFromPhp(): CpuArchitecture
    {
        return match (php_uname('m')) {
            'arm64', 'aarch64' => CpuArchitecture::ARM64,
            default => CpuArchitecture::AMD64,
        };
    }
}
