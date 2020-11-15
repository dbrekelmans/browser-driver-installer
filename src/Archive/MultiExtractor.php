<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Archive;

use DBrekelmans\BrowserDriverInstaller\Exception\Unsupported;

use function array_merge;
use function array_unique;
use function in_array;
use function pathinfo;
use function Safe\sprintf;

final class MultiExtractor implements Extractor
{
    /** @var Extractor[] */
    private $registeredExtractors = [];

    /** @var string[] */
    private $supportedExtensions = [];

    /**
     * @inheritDoc
     */
    public function extract(string $archive, string $destination): array
    {
        $pathParts = pathinfo($archive);

        if (!isset($pathParts['extension'])) {
            throw new Unsupported(sprintf('Can not find extension for archive %s', $archive));
        }

        foreach ($this->registeredExtractors as $extractor) {
            if (in_array($pathParts['extension'], $extractor->getSupportedExtensions(), true)) {
                return $extractor->extract($archive, $destination);
            }
        }

        throw new Unsupported(sprintf('No archive extractor found supporting %s archive', $pathParts['extension']));
    }

    /**
     * @inheritDoc
     */
    public function getSupportedExtensions(): array
    {
        return $this->supportedExtensions;
    }

    public function register(Extractor $extractor): void
    {
        $this->registeredExtractors[] = $extractor;
        $this->supportedExtensions = array_unique(array_merge($this->supportedExtensions, $extractor->getSupportedExtensions()));
    }
}
