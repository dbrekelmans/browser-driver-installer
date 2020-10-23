<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser;

use DBrekelmans\BrowserDriverInstaller\CommandLine\CommandLineEnvironment;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use RuntimeException;
use function Safe\sprintf;

abstract class VersionResolverChrome implements VersionResolver
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
            return $this->getVersionFromCommandLine($this->getMacOSCommandLineForVersion($path));
        }

        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            return $this->getVersionFromCommandLine(
                sprintf('wmic datafile where name="%s" get Version /value', $path)
            );
        }

        throw NotImplemented::feature(
            sprintf(
                'Resolving version on %s',
                $operatingSystem->getValue()
            )
        );
    }

    abstract protected function getMacOSCommandLineForVersion(string $path) : string;

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
