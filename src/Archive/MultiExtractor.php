<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Archive;

use DBrekelmans\BrowserDriverInstaller\Exception\Unsupported;

use function Safe\mime_content_type;
use function Safe\sprintf;

final class MultiExtractor implements Extractor
{
    /** @var Extractor[] */
    private $registeredExtractors = [];

    /**
     * @inheritDoc
     */
    public function extract(string $archive, string $destination): array
    {
        $mimeType = mime_content_type($archive);

        foreach ($this->registeredExtractors as $supportedMimeType => $extractor) {
            if ($supportedMimeType === $mimeType) {
                return $extractor->extract($archive, $destination);
            }
        }

        throw new Unsupported(sprintf('No archive extractor found supporting %s archive', $mimeType));
    }

    public function register(Extractor $extractor, string $mimeType): void
    {
        $this->registeredExtractors[$mimeType] = $extractor;
    }
}
