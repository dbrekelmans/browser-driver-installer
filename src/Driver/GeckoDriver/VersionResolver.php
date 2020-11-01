<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Driver\GeckoDriver;

use DBrekelmans\BrowserDriverInstaller\Browser\Browser;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\VersionResolver as VersionResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Version;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\json_decode;
use function Safe\sprintf;

final class VersionResolver implements VersionResolverInterface
{
    private const LATEST_VERSION_ENDPOINT = 'https://api.github.com/repos/mozilla/geckodriver/releases/latest';

    /** @var HttpClientInterface */
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @see https://firefox-source-docs.mozilla.org/testing/geckodriver/Support.html
     */
    public function fromBrowser(Browser $browser): Version
    {
        $browserMajorVersion = (int) $browser->version()->major();
        //TODO maybe put this part in a matrix??
        if ($browserMajorVersion >= 60) {
            return $this->latest();
        }

        if ($browserMajorVersion >= 57) {
            return Version::fromString('0.25.0');
        }

        if ($browserMajorVersion >= 55) {
            return Version::fromString('0.20.1');
        }

        if ($browserMajorVersion >= 53) {
            return Version::fromString('0.18.0');
        }

        if ($browserMajorVersion >= 52) {
            return Version::fromString('0.17.0');
        }

        throw new RuntimeException(sprintf('Could not find a geckodriver version for Firefox %s', $browser->version()->toString()));
    }

    public function latest(): Version
    {
        $response = $this->httpClient->request('GET', self::LATEST_VERSION_ENDPOINT);
        /** @var array $data */
        $data = json_decode($response->getContent(), true);
        if (!isset($data['name'])) {
            throw new RuntimeException('Can not find latest release name');
        }

        return Version::fromString((string)$data['name']);
    }

    public function supports(Browser $browser): bool
    {
        return $browser->name()->equals(BrowserName::FIREFOX());
    }
}
