<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Archive;

use DBrekelmans\BrowserDriverInstaller\Exception\Unsupported;

interface Extractor
{
    /**
     * @return string[] List of filenames that were extracted
     *
     * @throws Unsupported
     */
    public function extract(string $archive, string $destination): array;

    /**
     * @return string[]
     */
    public function getSupportedExtensions(): array;
}
