<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\MsEdgeDriver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\VersionResolver as VersionResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Exception\Unsupported;
use DBrekelmans\BrowserDriverInstaller\Version;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

use function chr;
use function sprintf;
use function str_replace;
use function substr;

final class VersionResolver implements VersionResolverInterface
{
    private const LATEST_STABLE_VERSION_ENDPOINT = 'https://msedgedriver.azureedge.net/LATEST_STABLE';
    private const LATEST_BETA_VERSION_ENDPOINT   = 'https://msedgedriver.azureedge.net/LATEST_BETA';

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function fromBrowser(Browser $browser): Version
    {
        if (! $this->supports($browser)) {
            throw new Unsupported(sprintf('%s is not supported.', $browser->name->value));
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
        $response = $this->httpClient->request('GET', self::LATEST_STABLE_VERSION_ENDPOINT);
        $version  = $response->getContent();
        $version  = str_replace(chr(0), '', substr($version, 2));

        return Version::fromString((string) $version);
    }

    public function supports(Browser $browser): bool
    {
        return $browser->name === BrowserName::MSEDGE;
    }

    private function latestBetaVersion(): Version
    {
        $response = $this->httpClient->request('GET', self::LATEST_BETA_VERSION_ENDPOINT);
        $version  = $response->getContent();
        $version  = str_replace(chr(0), '', substr($version, 2));

        return Version::fromString((string) $version);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws DecodingExceptionInterface
     */
    private function getVersionString(Browser $browser): string
    {
        $latestBeta = $this->latestBetaVersion();

        $version = $browser->version;
        if ((int) $version->major() > (int) $latestBeta->major()) {
            // In this case we're dealing with a Dev or Canary version, so we will take the last Beta version.
            $version = $latestBeta;
        }

        return $version->toBuildString();
    }
}
