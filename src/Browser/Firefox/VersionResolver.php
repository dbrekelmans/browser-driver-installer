<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\Firefox;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolver as VersionResolverInterface;
use DBrekelmans\BrowserDriverInstaller\CommandLine\CommandLineEnvironment;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use InvalidArgumentException;

use function sprintf;

final class VersionResolver implements VersionResolverInterface
{
    public function __construct(private CommandLineEnvironment $commandLineEnvironment)
    {
    }

    public function from(OperatingSystem $operatingSystem, string $path): Version
    {
        return match ($operatingSystem) {
            OperatingSystem::LINUX => $this->getVersionFromCommandLine(sprintf('%s --version', $path)),
            OperatingSystem::MACOS => $this->getVersionFromCommandLine(sprintf('%s/Contents/MacOS/firefox --version', $path)),
            OperatingSystem::WINDOWS => $this->getVersionFromCommandLine(sprintf('"%s" --version | more', $path)),
        };
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName === BrowserName::FIREFOX;
    }

    private function getVersionFromCommandLine(string $command): Version
    {
        try {
            $commandOutput = $this->commandLineEnvironment->getCommandLineSuccessfulOutput($command);

            return Version::fromString($commandOutput);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(
                'Version could not be determined.',
                0,
                $exception,
            );
        }
    }
}
