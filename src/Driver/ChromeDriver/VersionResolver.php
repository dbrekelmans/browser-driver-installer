<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\ChromeDriver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\VersionResolver as VersionResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Exception\Unsupported;
use DBrekelmans\BrowserDriverInstaller\Version;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

use function array_key_exists;
use function sprintf;

final class VersionResolver implements VersionResolverInterface
{
    public const MAJOR_VERSION_ENDPOINT_BREAKPOINT = 115;
    private const LEGACY_ENDPOINT                  = 'https://chromedriver.storage.googleapis.com/LATEST_RELEASE';
    private const LATEST_VERSION_ENDPOINT_JSON     = 'https://googlechromelabs.github.io/chrome-for-testing/last-known-good-versions.json';
    private const VERSION_ENDPOINT_JSON            = 'https://googlechromelabs.github.io/chrome-for-testing/latest-patch-versions-per-build.json';

    public static function isJsonVersion(Version $version): bool
    {
        return $version->major() >= self::MAJOR_VERSION_ENDPOINT_BREAKPOINT;
    }

    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    public function fromBrowser(Browser $browser): Version
    {
        if (! $this->supports($browser)) {
            throw new Unsupported(sprintf('%s is not supported.', $browser->name()->value));
        }

        try {
            $versionString =  $this->getVersionString($browser);
        } catch (
            ClientExceptionInterface
                | RedirectionExceptionInterface
                | ServerExceptionInterface
                | TransportExceptionInterface
                $exception
        ) {
            throw new UnexpectedValueException(
                'Something went wrong getting the driver version from the chromedriver API.',
                0,
                $exception,
            );
        }

        try {
            return Version::fromString($versionString);
        } catch (InvalidArgumentException $exception) {
            throw new UnexpectedValueException(
                'Content received from chromedriver API could not be parsed into a version.',
                0,
                $exception,
            );
        }
    }

    public function latest(): Version
    {
        $response = $this->httpClient->request('GET', self::LATEST_VERSION_ENDPOINT_JSON);
        $versions = $response->toArray();
        if (! isset($versions['channels']['Stable']['version'])) {
            throw new UnexpectedValueException('Could not resolve the latest stable version.');
        }

        return Version::fromString((string) $versions['channels']['Stable']['version']);
    }

    public function supports(Browser $browser): bool
    {
        $browserName = $browser->name();

        return $browserName === BrowserName::GOOGLE_CHROME || $browserName === BrowserName::CHROMIUM;
    }

    private function latestBetaVersion(): Version
    {
        $response = $this->httpClient->request('GET', self::LATEST_VERSION_ENDPOINT_JSON);
        $versions = $response->toArray();
        if (! isset($versions['channels']['Beta']['version'])) {
            throw new UnexpectedValueException('Could not resolve the latest beta version.');
        }

        return Version::fromString((string) $versions['channels']['Beta']['version']);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    private function getVersionString(Browser $browser): string
    {
        if ($browser->version()->major() < self::MAJOR_VERSION_ENDPOINT_BREAKPOINT) {
            return $this->httpClient->request('GET', $this->getBrowserVersionEndpoint($browser))->getContent();
        }

        $response = $this->httpClient->request('GET', self::VERSION_ENDPOINT_JSON);
        $versions = $response->toArray();

        $latestBeta     = $this->latestBetaVersion();
        $versionToFetch = $browser->version();
        if ((int) $versionToFetch->major() > (int) $latestBeta->major()) {
            // In this case we're dealing with a Dev or Canary version, so we will take the last Beta version.
            $versionToFetch = $latestBeta;
        }

        if (! array_key_exists($versionToFetch->toString(), $versions['builds'])) {
            throw new UnexpectedValueException(
                sprintf('There is no build for version : %s', $versionToFetch->toString()),
            );
        }

        return $versions['builds'][$versionToFetch->toString()]['version'];
    }

    /**
     * In case of handling with Chrome from Dev or Canary channel we will then take beta ChromeDriver
     */
    private function getBrowserVersionEndpoint(Browser $browser): string
    {
        $versionEndpoint = sprintf('%s_%s', self::LEGACY_ENDPOINT, $browser->version()->toString());

        $stableVersion    = $this->latest();
        $betaVersionMajor = (int) $stableVersion->major() + 1;

        if ((int) $browser->version()->major() > $betaVersionMajor) {
            $versionEndpoint = sprintf('%s_%s', self::LEGACY_ENDPOINT, (string) $betaVersionMajor);
        }

        return $versionEndpoint;
    }
}
