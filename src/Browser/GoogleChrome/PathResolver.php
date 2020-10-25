<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver as PathResolverInterface;
use DBrekelmans\BrowserDriverInstaller\CommandLine\CommandLineEnvironment;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;

use function addslashes;
use function Safe\sprintf;

final class PathResolver implements PathResolverInterface
{
    private CommandLineEnvironment $commandLineEnvironment;

    public function __construct(CommandLineEnvironment $commandLineEnvironment)
    {
        $this->commandLineEnvironment = $commandLineEnvironment;
    }

    /**
     * @throws NotImplemented
     */
    public function from(OperatingSystem $operatingSystem): string
    {
        if ($operatingSystem->equals(OperatingSystem::LINUX())) {
            // TODO: command -v google-chrome
            return 'google-chrome';
        }

        if ($operatingSystem->equals(OperatingSystem::MACOS())) {
            // TODO: check if file exists
            return '/Applications/Google\ Chrome.app';
        }

        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            $localAppDataPath = $this->commandLineEnvironment->getCommandLineSuccessfulOutput('echo %LocalAppData%');

            return addslashes(trim($localAppDataPath) . '\Google\Chrome\Application\chrome.exe');
        }

        throw NotImplemented::feature(sprintf('Resolving path on %s', $operatingSystem->getValue()));
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName->equals(BrowserName::GOOGLE_CHROME());
    }
}
