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
use function sprintf;

final class VersionResolver implements VersionResolverInterface
{
    private const LATEST_VERSION_ENDPOINT = 'https://api.github.com/repos/mozilla/geckodriver/releases/latest';

    private const MIN_REQUIRED_BROWSER_VERSION_FOR_LATEST = 60;

    private const MIN_REQUIRED_BROWSER_VERSIONS = [
        57 => '0.25.0',
        55 => '0.20.1',
        53 => '0.18.0',
        52 => '0.17.0',
    ];

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    /** @see https://firefox-source-docs.mozilla.org/testing/geckodriver/Support.html */
    public function fromBrowser(Browser $browser): Version
    {
        $browserMajorVersion = (int) $browser->version->major();

        if ($browserMajorVersion >= self::MIN_REQUIRED_BROWSER_VERSION_FOR_LATEST) {
            return $this->latest();
        }

        $minRequiredBrowserVersions = self::MIN_REQUIRED_BROWSER_VERSIONS;
        krsort($minRequiredBrowserVersions);
        foreach ($minRequiredBrowserVersions as $minReqVersion => $geckoVersion) {
            if ($browserMajorVersion >= $minReqVersion) {
                return Version::fromString($geckoVersion);
            }
        }

        throw new RuntimeException(sprintf(
            'Could not find a geckodriver version for Firefox %s',
            $browser->version->toString(),
        ));
    }

    public function latest(): Version
    {
        $response = $this->httpClient->request('GET', self::LATEST_VERSION_ENDPOINT);

        /** @var array<scalar> $data */
        $data = json_decode($response->getContent(), true);
        if (! isset($data['name'])) {
            throw new RuntimeException('Can not find latest release name');
        }

        return Version::fromString((string) $data['name']);
    }

    public function supports(Browser $browser): bool
    {
        return $browser->name === BrowserName::FIREFOX;
    }
}
