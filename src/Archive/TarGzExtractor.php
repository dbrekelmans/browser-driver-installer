<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Archive;

use PharData;
use PharFileInfo;

use function assert;

use const DIRECTORY_SEPARATOR;

final class TarGzExtractor implements Extractor
{
    /**
     * @inheritDoc
     */
    public function extract(string $archive, string $destination): array
    {
        $tarGzData = new PharData($archive);
        $tarData = $tarGzData->decompress();
        $tarData->extractTo($destination);

        $extractedFilenames = [];
        foreach ($tarData as $file) {
            assert($file instanceof PharFileInfo);
            $extractedFilenames[] = $destination . DIRECTORY_SEPARATOR . $file->getFilename();
        }

        return $extractedFilenames;
    }
}
