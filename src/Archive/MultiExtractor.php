<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Archive;

use DBrekelmans\BrowserDriverInstaller\Exception\Unsupported;

use function array_merge;
use function array_unique;
use function in_array;
use function Safe\mime_content_type;
use function Safe\sprintf;

final class MultiExtractor implements Extractor
{
    /** @var Extractor[] */
    private $registeredExtractors = [];

    /** @var string[] */
    private $supportedMimeTypes = [];

    /**
     * @inheritDoc
     */
    public function extract(string $archive, string $destination): array
    {
        $mimeType = mime_content_type($archive);

        foreach ($this->registeredExtractors as $extractor) {
            if (in_array($mimeType, $extractor->getSupportedMimeTypes(), true)) {
                return $extractor->extract($archive, $destination);
            }
        }

        throw new Unsupported(sprintf('No archive extractor found supporting %s archive', $mimeType));
    }

    /**
     * @inheritDoc
     */
    public function getSupportedMimeTypes(): array
    {
        return $this->supportedMimeTypes;
    }

    public function register(Extractor $extractor): void
    {
        $this->registeredExtractors[] = $extractor;
        $this->supportedMimeTypes = array_unique(array_merge($this->supportedMimeTypes, $extractor->getSupportedMimeTypes()));
    }
}
