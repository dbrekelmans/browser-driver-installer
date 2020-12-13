<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests;

use function stripslashes;

trait UniqueClassName
{
    /** @var int */
    private static $uniqueClassNameCounter = 0;

    /**
     * @psalm-param class-string $className
     */
    private static function uniqueClassName(string $className): string
    {
        $uniqueClassName = stripslashes(static::class) . stripslashes($className) . self::$uniqueClassNameCounter;

        self::$uniqueClassNameCounter++;

        return $uniqueClassName;
    }
}
