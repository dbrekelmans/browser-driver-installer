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
use function Safe\sprintf;

final class VersionResolver implements VersionResolverInterface
{
    public const MAJOR_VERSION_ENDPOINT_BREAKPOINT = 115;
    private const VERSION_ENDPOINT                 = 'https://chromedriver.storage.googleapis.com/LATEST_RELEASE';
    private const VERSION_ENDPOINT_JSON            = 'https://googlechromelabs.github.io/chrome-for-testing/latest-patch-versions-per-build.json';

    /** @var HttpClientInterface */
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function fromBrowser(Browser $browser): Version
    {
        if (! $this->supports($browser)) {
            throw new Unsupported(sprintf('%s is not supported.', $browser->name()->getValue()));
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
                $exception
            );
        }

        try {
            return Version::fromString($versionString);
        } catch (InvalidArgumentException $exception) {
            throw new UnexpectedValueException(
                'Content received from chromedriver API could not be parsed into a version.',
                0,
                $exception
            );
        }
    }

    public function latest(): Version
    {
        $response      = $this->httpClient->request('GET', self::VERSION_ENDPOINT);
        $versionString = $response->getContent();

        return Version::fromString($versionString);
    }

    public function supports(Browser $browser): bool
    {
        $browserName = $browser->name();

        return $browserName->equals(BrowserName::GOOGLE_CHROME()) || $browserName->equals(BrowserName::CHROMIUM());
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
        if (! array_key_exists($browser->version()->toString(), $versions['builds'])) {
            throw new UnexpectedValueException(
                sprintf('There is no build for version : %s', $browser->version()->toString())
            );
        }

        return $versions['builds'][$browser->version()->toString()]['version'];
    }

    /**
     * In case of handling with Chrome from Dev or Canary channel we will then take beta ChromeDriver
     */
    private function getBrowserVersionEndpoint(Browser $browser): string
    {
        $versionEndpoint = sprintf('%s_%s', self::VERSION_ENDPOINT, $browser->version()->toString());

        $stableVersion    = $this->latest();
        $betaVersionMajor = (int) $stableVersion->major() + 1;

        if ((int) $browser->version()->major() > $betaVersionMajor) {
            $versionEndpoint = sprintf('%s_%s', self::VERSION_ENDPOINT, (string) $betaVersionMajor);
        }

        return $versionEndpoint;
    }
}
