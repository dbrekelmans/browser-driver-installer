<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Driver\DownloadUrlResolver as DownloadUrlResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

use function is_string;
use function Safe\sprintf;

final class DownloadUrlResolver implements DownloadUrlResolverInterface
{
    private const BINARY_LINUX                        = 'chromedriver_linux64';
    private const BINARY_MAC                          = 'chromedriver_mac64';
    private const BINARY_WINDOWS                      = 'chromedriver_win32';
    private const LEGACY_DOWNLOAD_ENDPOINT            = 'https://chromedriver.storage.googleapis.com';
    private const LATEST_PATCH_WITH_DOWNLOAD_ENDPOINT = 'https://googlechromelabs.github.io/chrome-for-testing/latest-patch-versions-per-build-with-downloads.json';

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function byDriver(Driver $driver): string
    {
        if (! VersionResolver::isJsonVersion($driver->version())) {
            return sprintf(
                '%s/%s/%s.zip',
                self::LEGACY_DOWNLOAD_ENDPOINT,
                $driver->version()->toBuildString(),
                $this->getBinaryName($driver),
            );
        }

        $response = $this->httpClient->request('GET', self::LATEST_PATCH_WITH_DOWNLOAD_ENDPOINT);

        $versions = $response->toArray();
        if (! isset($versions['builds'][$driver->version()->toString()]['downloads']['chromedriver'])) {
            throw new UnexpectedValueException(sprintf('Could not find the chromedriver downloads for version %s', $driver->version()->toString()));
        }

        $platformName = $this->getPlatformName($driver);
        $downloads    = $versions['builds'][$driver->version()->toString()]['downloads']['chromedriver'];
        foreach ($downloads as $download) {
            if ($download['platform'] === $platformName && isset($download['url']) && is_string($download['url'])) {
                return $download['url'];
            }
        }

        $operatingSystem = $driver->operatingSystem();

        throw new UnexpectedValueException(sprintf(
            'Could not resolve chromedriver download url for version %s with binary %s',
            $driver->version()->toString(),
            $operatingSystem->getValue()
        ));
    }

    private function getBinaryName(Driver $driver): string
    {
        $operatingSystem = $driver->operatingSystem();
        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            return self::BINARY_WINDOWS;
        }

        if ($operatingSystem->equals(OperatingSystem::MACOS())) {
            return self::BINARY_MAC;
        }

        if ($operatingSystem->equals(OperatingSystem::LINUX())) {
            return self::BINARY_LINUX;
        }

        throw NotImplemented::feature(
            sprintf('Downloading %s for %s', $driver->name()->getValue(), $operatingSystem->getValue())
        );
    }

    private function getPlatformName(Driver $driver): string
    {
        $operatingSystem = $driver->operatingSystem();
        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            return 'win32';
        }

        if ($operatingSystem->equals(OperatingSystem::MACOS())) {
            return 'mac-x64';
        }

        if ($operatingSystem->equals(OperatingSystem::LINUX())) {
            return 'linux64';
        }

        throw NotImplemented::feature(
            sprintf('Downloading %s for %s', $driver->name()->getValue(), $operatingSystem->getValue())
        );
    }
}
