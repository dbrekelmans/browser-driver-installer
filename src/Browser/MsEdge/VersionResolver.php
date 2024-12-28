<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\MsEdge;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolver as VersionResolverInterface;
use DBrekelmans\BrowserDriverInstaller\CommandLine\CommandLineEnvironment;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use RuntimeException;

use function sprintf;

final class VersionResolver implements VersionResolverInterface
{
    public function __construct(private readonly CommandLineEnvironment $commandLineEnvironment)
    {
    }

    public function from(OperatingSystem $operatingSystem, string $path): Version
    {
        return match ($operatingSystem) {
            OperatingSystem::LINUX => throw new RuntimeException('Not implemented yet.'),
            OperatingSystem::MACOS => $this->getVersionFromCommandLine(sprintf('%s/Contents/MacOS/Microsoft\ Edge --version', $path)),
            OperatingSystem::WINDOWS => throw new RuntimeException('Not implemented yet.'),
        };
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName === BrowserName::MSEDGE;
    }

    private function getVersionFromCommandLine(string $command): Version
    {
        try {
            $commandOutput = $this->commandLineEnvironment->getCommandLineSuccessfulOutput($command);

            return Version::fromString($commandOutput);
        } catch (RuntimeException $exception) {
            throw new RuntimeException(
                'Version could not be determined.',
                0,
                $exception,
            );
        }
    }
}
