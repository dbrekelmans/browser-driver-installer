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

use function Safe\sprintf;

final class VersionResolver implements VersionResolverInterface
{
    private const VERSION_ENDPOINT = 'https://chromedriver.storage.googleapis.com/LATEST_RELEASE';

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
            $content = $this->httpClient->request('GET', $this->getBrowserVersionEndpoint($browser))->getContent();
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
            return Version::fromString($content);
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
        return $browser->name()->equals(BrowserName::GOOGLE_CHROME()) || $browser->name()->equals(BrowserName::CHROMIUM());
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
