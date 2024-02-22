<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Archive;

use DBrekelmans\BrowserDriverInstaller\Exception\UnexpectedType;
use PharData;
use PharFileInfo;

use const DIRECTORY_SEPARATOR;

final class TarGzExtractor implements Extractor
{
    /** @inheritDoc */
    public function extract(string $archive, string $destination): array
    {
        $tarGzData = new PharData($archive);
        $tarData   = $tarGzData->decompress();
        $tarData->extractTo($destination, null, true);

        $extractedFilenames = [];
        foreach ($tarData as $file) {
            if (! $file instanceof PharFileInfo) {
                throw UnexpectedType::expected(PharFileInfo::class, $file);
            }

            $extractedFilenames[] = $destination . DIRECTORY_SEPARATOR . $file->getFilename();
        }

        return $extractedFilenames;
    }

    /** @inheritDoc */
    public function getSupportedExtensions(): array
    {
        return ['gz'];
    }
}
