<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolver as VersionResolverInterface;
use DBrekelmans\BrowserDriverInstaller\CommandLine\CommandLineEnvironment;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use InvalidArgumentException;
use Safe\Exceptions\StringsException;


final class VersionResolver implements VersionResolverInterface
{
    private const REG_KEY_STABLE                  = '8A69D345-D564-463c-AFF1-A69D9E530F96';
    private const REG_KEY_BETA                    = '8237E44A-0054-442C-B6B6-EA0509993955';
    private const REG_KEY_DEV                     = '401C381F-E0DE-4B85-8BD8-3F3F14FBDA57';
    private const REG_KEY_CANARY                  = '4ea16ac7-fd5a-47c3-875b-dbf4a2008c20';
    private const VERSION_REG_QUERY_LOCAL_MACHINE = 'reg query HKLM\Software\Google\Update\Clients\{%s} /v pv /reg:32 2> NUL';
    private const VERSION_REG_QUERY_CURRENT_USER  = 'reg query HKCU\Software\Google\Update\Clients\{%s} /v pv /reg:32 2> NUL';

    private CommandLineEnvironment $commandLineEnvironment;

    public function __construct(CommandLineEnvironment $commandLineEnvironment)
    {
        $this->commandLineEnvironment = $commandLineEnvironment;
    }

    public function from(OperatingSystem $operatingSystem, string $path): Version
    {
        if ($operatingSystem=== OperatingSystem::LINUX) {
            return $this->getVersionFromCommandLine(sprintf('%s --version', $path));
        }

        if ($operatingSystem=== OperatingSystem::MACOS) {
            return $this->getVersionFromCommandLine(
                sprintf('%s/Contents/MacOS/Google\ Chrome --version', $path)
            );
        }

        if ($operatingSystem=== OperatingSystem::WINDOWS) {
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

        throw NotImplemented::feature(
            sprintf(
                'Resolving version on %s',
                $operatingSystem->value
            )
        );
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName=== BrowserName::GOOGLE_CHROME;
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
                $exception
            );
        }
    }

    /**
     * Provide potential commands to determine Chrome Version on Windows
     *
     * @see https://bugs.chromium.org/p/chromium/issues/attachmentText?aid=387709
     * @see https://bugs.chromium.org/p/chromium/issues/detail?id=158372
     *
     * @return string[]
     *
     * @throws StringsException
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
