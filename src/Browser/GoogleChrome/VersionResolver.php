<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolver as VersionResolverInterface;
use DBrekelmans\BrowserDriverInstaller\CommandLine\CommandLineEnvironment;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use RuntimeException;

use function Safe\preg_replace;
use function Safe\sprintf;

final class VersionResolver implements VersionResolverInterface
{
    private CommandLineEnvironment $commandLineEnvironment;

    public function __construct(CommandLineEnvironment $commandLineEnvironment)
    {
        $this->commandLineEnvironment = $commandLineEnvironment;
    }

    public function from(OperatingSystem $operatingSystem, string $path) : Version
    {
        if ($operatingSystem->equals(OperatingSystem::LINUX())) {
            return $this->getVersionFromCommandLine(sprintf('%s --version', $path));
        }

        if ($operatingSystem->equals(OperatingSystem::MACOS())) {
            return $this->getVersionFromCommandLine(
                sprintf('%s/Contents/MacOS/Google\ Chrome --version', $path)
            );
        }

        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            $output = $this->commandLineEnvironment->getCommandLineSuccessfulOutput(
                sprintf('wmic datafile where name="%s" get Version /value', $path)
            );

            $sanitizedOutput = preg_replace("/[^\d\.]/", '', $output);

            return Version::fromString($sanitizedOutput);
        }

        throw NotImplemented::feature(
            sprintf(
                'Resolving version on %s',
                $operatingSystem->getValue()
            )
        );
    }

    public function supports(BrowserName $browserName) : bool
    {
        return $browserName->equals(BrowserName::GOOGLE_CHROME());
    }

    private function getVersionFromCommandLine(string $command) : Version
    {
        try {
            $commandOutput = $this->commandLineEnvironment->getCommandLineSuccessfulOutput($command);

            return Version::fromString($commandOutput);
        } catch (RuntimeException $exception) {
            throw new RuntimeException(
                'Version could not be determined.',
                0,
                $exception
            );
        }
    }
}
