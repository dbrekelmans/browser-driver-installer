<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Driver\DownloadUrlResolver as DownloadUrlResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

use function Safe\sprintf;

final class DownloadUrlResolver implements DownloadUrlResolverInterface
{
    private const LEGACY_DOWNLOAD_ENDPOINT            = 'https://chromedriver.storage.googleapis.com';
    private const LATEST_PATCH_WITH_DOWNLOAD_ENDPOINT = 'https://googlechromelabs.github.io/chrome-for-testing/latest-patch-versions-per-build-with-downloads.json';

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function byDriver(Driver $driver, string $binaryName): string
    {
        if (! VersionResolver::isJsonVersion($driver->version())) {
            return sprintf(
                '%s/%s/%s.zip',
                self::LEGACY_DOWNLOAD_ENDPOINT,
                $driver->version()->toBuildString(),
                $binaryName
            );
        }

        $response = $this->httpClient->request(
            'GET',
            self::LATEST_PATCH_WITH_DOWNLOAD_ENDPOINT,
        );

        $versions = $response->toArray();
        if (! isset($versions['builds'][$driver->version()->toString()]['downloads']['chromedriver'])) {
            throw new UnexpectedValueException(sprintf('Could not find the chromedriver downloads for version %s', $driver->version()->toString()));
        }

        $downloads = $versions['builds'][$driver->version()->toString()]['downloads']['chromedriver'];
        foreach ($downloads as $download) {
            if ($download['platform'] === $binaryName) {
                return (string) $download['url'];
            }
        }

        $operatingSystem = $driver->operatingSystem();

        throw NotImplemented::feature(sprintf('Downloading %s for %s', $driver->name()->getValue(), $operatingSystem->getValue()));
    }
}
