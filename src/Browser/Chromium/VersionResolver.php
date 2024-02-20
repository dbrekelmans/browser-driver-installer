<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\Chromium;

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
    private const REG_KEY_STABLE                  = '8A69D345-D564-463c-AFF1-A69D9E530F96';
    private const REG_KEY_BETA                    = '8237E44A-0054-442C-B6B6-EA0509993955';
    private const REG_KEY_DEV                     = '401C381F-E0DE-4B85-8BD8-3F3F14FBDA57';
    private const REG_KEY_CANARY                  = '4ea16ac7-fd5a-47c3-875b-dbf4a2008c20';
    private const VERSION_REG_QUERY_LOCAL_MACHINE = 'reg query HKLM\Software\Google\Update\Clients\{%s} /v pv /reg:32 2> NUL';
    private const VERSION_REG_QUERY_CURRENT_USER  = 'reg query HKCU\Software\Google\Update\Clients\{%s} /v pv /reg:32 2> NUL';

    public function __construct(private readonly CommandLineEnvironment $commandLineEnvironment)
    {
    }

    public function from(OperatingSystem $operatingSystem, string $path): Version
    {
        return match ($operatingSystem) {
            OperatingSystem::LINUX => $this->getVersionFromCommandLine(sprintf('%s --version', $path)),
            OperatingSystem::MACOS => $this->getVersionFromCommandLine(sprintf('%s/Contents/MacOS/Chromium --version', $path)),
            OperatingSystem::WINDOWS => $this->getVersionFromWindows(),
        };
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName === BrowserName::CHROMIUM;
    }

    private function getVersionFromWindows(): Version
    {
        foreach (self::getWindowsCommandsForVersion() as $possibleCommand) {
            try {
                return $this->getVersionFromCommandLine($possibleCommand);
            } catch (InvalidArgumentException) {
                // @ignoreException
            }
        }

        throw new InvalidArgumentException('Version could not be determined.');
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

    /**
     * Provide potential commands to determine Chromium Version on Windows
     *
     * @see https://bugs.chromium.org/p/chromium/issues/attachmentText?aid=387709
     * @see https://bugs.chromium.org/p/chromium/issues/detail?id=158372
     *
     * @return string[]
     */
    private static function getWindowsCommandsForVersion(): array
    {
        $commands = [];
        foreach ([self::REG_KEY_STABLE, self::REG_KEY_BETA, self::REG_KEY_DEV] as $regKey) {
            $commands[] = sprintf(self::VERSION_REG_QUERY_LOCAL_MACHINE, $regKey);
        }

        foreach ([self::REG_KEY_STABLE, self::REG_KEY_BETA, self::REG_KEY_DEV, self::REG_KEY_CANARY] as $regKey) {
            $commands[] = sprintf(self::VERSION_REG_QUERY_CURRENT_USER, $regKey);
        }

        return $commands;
    }
}
