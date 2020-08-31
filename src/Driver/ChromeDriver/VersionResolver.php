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

class VersionResolver implements VersionResolverInterface
{
    private const VERSION_ENDPOINT = 'https://chromedriver.storage.googleapis.com/LATEST_RELEASE';

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function fromBrowser(Browser $browser) : Version
    {
        if (!$this->supports($browser)) {
            throw new Unsupported(sprintf('%s is not supported.', $browser->name()->getValue()));
        }

        try {
            $response = $this->httpClient->request(
                'GET',
                sprintf('%s_%s', self::VERSION_ENDPOINT, $browser->version()->toString())
            );

            $content = $response->getContent();
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

    public function supports(Browser $browser) : bool
    {
        // TODO: Maybe also chromium? Have to test if API works the same.
        return $browser->name()->equals(BrowserName::GOOGLE_CHROME());
    }
}