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
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            new RuntimeException(
                sprintf('Version could not be determined.'),
                0,
                new ProcessFailedException($process)
            );
        }

        return Version::fromString($process->getOutput());
    }

    public function supports(BrowserName $browserName) : bool
    {
        return $browserName->equals(BrowserName::GOOGLE_CHROME());
    }
}
