<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver;

use RuntimeException;

interface Downloader
{
    /**
     * @throws RuntimeException
     */
    public function download(Driver $driver, string $location) : string;

    public function supports(Driver $driver) : bool;
}
