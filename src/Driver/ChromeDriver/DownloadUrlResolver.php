<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Driver\DownloadUrlResolver as DownloadUrlResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

use function is_string;
use function sprintf;

final class DownloadUrlResolver implements DownloadUrlResolverInterface
{
    private const LEGACY_DOWNLOAD_ENDPOINT            = 'https://chromedriver.storage.googleapis.com';
    private const LATEST_PATCH_WITH_DOWNLOAD_ENDPOINT = 'https://googlechromelabs.github.io/chrome-for-testing/latest-patch-versions-per-build-with-downloads.json';

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function byDriver(Driver $driver): string
    {
        if (! VersionResolver::isJsonVersion($driver->version)) {
            return sprintf(
                '%s/%s/%s.zip',
                self::LEGACY_DOWNLOAD_ENDPOINT,
                $driver->version->toBuildString(),
                $this->getBinaryName($driver),
            );
        }

        $response = $this->httpClient->request('GET', self::LATEST_PATCH_WITH_DOWNLOAD_ENDPOINT);

        $versions = $response->toArray();
        if (! isset($versions['builds'][$driver->version->toString()]['downloads']['chromedriver'])) {
            throw new UnexpectedValueException(sprintf('Could not find the chromedriver downloads for version %s', $driver->version->toString()));
        }

        $platformName = $this->getPlatformName($driver);
        $downloads    = $versions['builds'][$driver->version->toString()]['downloads']['chromedriver'];
        foreach ($downloads as $download) {
            if ($download['platform'] === $platformName && isset($download['url']) && is_string($download['url'])) {
                return $download['url'];
            }
        }

        $operatingSystem = $driver->operatingSystem;

        throw new UnexpectedValueException(sprintf(
            'Could not resolve chromedriver download url for version %s with binary %s',
            $driver->version->toString(),
            $operatingSystem->value,
        ));
    }

    private function getBinaryName(Driver $driver): string
    {
        return match ($driver->operatingSystem) {
            OperatingSystem::LINUX => 'chromedriver_linux64',
            OperatingSystem::MACOS => 'chromedriver_mac64',
            OperatingSystem::WINDOWS => 'chromedriver_win32',
        };
    }

    private function getPlatformName(Driver $driver): string
    {
        return match ($driver->operatingSystem) {
            OperatingSystem::LINUX => 'linux64',
            OperatingSystem::MACOS => 'mac-x64',
            OperatingSystem::WINDOWS => 'win32',
        };
    }
}
