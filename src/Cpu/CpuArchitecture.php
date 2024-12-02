<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Cpu;

use function php_uname;

enum CpuArchitecture: string
{
    case X86_64 = 'x86_64';
    case ARM64  = 'arm64';

    public function toString(): string
    {
        return match ($this) {
            self::X86_64 => '',
            self::ARM64  => ' arm64',
        };
    }

    public static function detectFromPhp(): CpuArchitecture
    {
        return match (php_uname('m')) {
            'arm64', 'aarch64' => CpuArchitecture::ARM64,
            default => CpuArchitecture::X86_64,
        };
    }
}
