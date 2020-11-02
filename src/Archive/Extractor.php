<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Archive;

interface Extractor
{
    /**
     * @return string[] List of filenames that were extracted
     */
    public function extract(string $archive, string $destination): array;
}
