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

use function Safe\sprintf;

final class VersionResolver implements VersionResolverInterface
{
    /** @var CommandLineEnvironment */
    private $commandLineEnvironment;

    public function __construct(CommandLineEnvironment $commandLineEnvironment)
    {
        $this->commandLineEnvironment = $commandLineEnvironment;
    }

    public function from(OperatingSystem $operatingSystem, string $path): Version
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
            $possibleCommands = [
                'reg query HKLM\Software\Google\Update\Clients\{8A69D345-D564-463c-AFF1-A69D9E530F96} /v pv /reg:32 2> NUL',
                'reg query HKLM\Software\Google\Update\Clients\{8237E44A-0054-442C-B6B6-EA0509993955} /v pv /reg:32 2> NUL',
                'reg query HKLM\Software\Google\Update\Clients\{401C381F-E0DE-4B85-8BD8-3F3F14FBDA57} /v pv /reg:32 2> NUL',
                'reg query HKCU\Software\Google\Update\Clients\{8A69D345-D564-463c-AFF1-A69D9E530F96} /v pv /reg:32 2> NUL',
                'reg query HKCU\Software\Google\Update\Clients\{8237E44A-0054-442C-B6B6-EA0509993955} /v pv /reg:32 2> NUL',
                'reg query HKCU\Software\Google\Update\Clients\{401C381F-E0DE-4B85-8BD8-3F3F14FBDA57} /v pv /reg:32 2> NUL',
                'reg query HKCU\Software\Google\Update\Clients\{4ea16ac7-fd5a-47c3-875b-dbf4a2008c20} /v pv /reg:32 2> NUL',
            ];
            foreach ($possibleCommands as $possibleCommand) {
                try {
                    return $this->getVersionFromCommandLine($possibleCommand);
                } catch (InvalidArgumentException $exception) {
                    // @ignoreException
                }
            }

            throw new InvalidArgumentException('Version could not be determined.');
        }

        throw NotImplemented::feature(
            sprintf(
                'Resolving version on %s',
                $operatingSystem->getValue()
            )
        );
    }

    public function supports(BrowserName $browserName): bool
    {
        return $browserName->equals(BrowserName::GOOGLE_CHROME());
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
}
