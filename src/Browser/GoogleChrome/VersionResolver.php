<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolver as VersionResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use function Safe\sprintf;

final class VersionResolver implements VersionResolverInterface
{
    /** @var Process[] */
    private array $processes = [];

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

    private function getVersionFromCommandLine(string $command) : Version
    {
        $process = $this->getProcess($command);
        $process->run();

        if (!$process->isSuccessful()) {
            new RuntimeException(
                'Version could not be determined.',
                0,
                new ProcessFailedException($process)
            );
        }

        return Version::fromString($process->getOutput());
    }

    private function getProcess(string $command) : Process
    {
        return $this->processes[$command] ?? Process::fromShellCommandline($command);
    }

    public function supports(BrowserName $browserName) : bool
    {
        return $browserName->equals(BrowserName::GOOGLE_CHROME());
    }

    public function setProcess(string $command, Process $process) : void
    {
        $this->processes[$command] = $process;
    }
}
