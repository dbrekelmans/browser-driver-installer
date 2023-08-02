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

class JsonVersionResolver implements VersionResolverInterface
{
    private const VERSION_ENDPOINT = 'https://googlechromelabs.github.io/chrome-for-testing/latest-patch-versions-per-build.json';

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
            $response = $this->httpClient->request('GET', self::VERSION_ENDPOINT);
            $versions = $response->toArray();
            if (! array_key_exists($browser->version()->toString(), $versions['builds'])) {
                throw new Unsupported(
                    sprintf('There is no build for version : %s', $browser->version()->toString())
                );
            }

            $content = $versions['builds'][$browser->version()->toString()]['version'];
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $exception) {
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
        $browserName = $browser->name();

        return ($browserName->equals(BrowserName::GOOGLE_CHROME()) || $browserName->equals(BrowserName::CHROMIUM()))
            && $browser->version()->major() >= VersionResolverInterface::CHROME_MAJOR_VERSION_ENDPOINT_BREAKPOINT;
    }
}
