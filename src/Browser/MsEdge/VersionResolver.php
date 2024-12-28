<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\MsEdge;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolver as VersionResolverInterface;
use DBrekelmans\BrowserDriverInstaller\CommandLine\CommandLineEnvironment;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use InvalidArgumentException;
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
            OperatingSystem::WINDOWS => $this->getVersionFromWindows(),
        };
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName === BrowserName::MSEDGE;
    }

    private function getVersionFromWindows(): Version
    {
        $previousException = null;
        foreach (self::getWindowsCommandsForVersion() as $possibleCommand) {
            try {
                return $this->getVersionFromCommandLine($possibleCommand);
            } catch (InvalidArgumentException $exception) {
                $previousException = $exception;
            }
        }

        throw new InvalidArgumentException('Version could not be determined.', 0, $previousException);
    }

    /**
     * Provide potential commands to determine Edge Version on Windows
     *
     * @return string[]
     */
    private static function getWindowsCommandsForVersion(): array
    {
        return [
            "powershell \"(Get-Item 'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe').VersionInfo.ProductVersion\"",
            'reg query HKCU\Software\Microsoft\Edge\BLBeacon /v version',
        ];
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
