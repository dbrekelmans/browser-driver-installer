<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests;

use function stripslashes;

trait UniqueClassName
{
    private static int $uniqueClassNameCounter = 0;

    /**
     * @param class-string $className
     */
    private static function uniqueClassName(string $className): string
    {
        $uniqueClassName = stripslashes(static::class) . stripslashes($className) . self::$uniqueClassNameCounter;

        self::$uniqueClassNameCounter++;

        return $uniqueClassName;
    }
}
