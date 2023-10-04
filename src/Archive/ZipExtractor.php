<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Archive;

use RuntimeException;
use ZipArchive;



use const DIRECTORY_SEPARATOR;

final class ZipExtractor implements Extractor
{
    private ZipArchive $zipArchive;

    public function __construct(ZipArchive $zipArchive)
    {
        $this->zipArchive = $zipArchive;
    }

    /**
     * @inheritDoc
     */
    public function extract(string $archive, string $destination): array
    {
        $success = $this->zipArchive->open($archive);

        if ($success !== true) {
            throw new RuntimeException(sprintf('Could not open archive %s.', $archive));
        }

        $success = $this->zipArchive->extractTo($destination);

        if ($success !== true) {
            throw new RuntimeException(sprintf('Could not extract archive %s to %s.', $archive, $destination));
        }

        $extractedFilenames = [];

        for ($i = 0; $i < $this->zipArchive->numFiles; $i++) {
            $extractedFilenames[] = $destination . DIRECTORY_SEPARATOR . $this->zipArchive->getNameIndex($i);
        }

        $success = $this->zipArchive->close();
        if ($success !== true) {
            throw new RuntimeException(sprintf('Could not close zip archive %s.', $archive));
        }

        return $extractedFilenames;
    }

    /**
     * @inheritDoc
     */
    public function getSupportedExtensions(): array
    {
        return ['zip'];
    }
}
