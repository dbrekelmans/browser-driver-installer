<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\Name;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use DBrekelmans\BrowserDriverInstaller\Version;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use DBrekelmans\BrowserDriverInstaller\Browser\VersionResolver as VersionResolverInterface;

use function Safe\sprintf;

final class VersionResolver implements VersionResolverInterface
{
    public function from(OperatingSystem $operatingSystem, string $path) : Version
    {
        if ($operatingSystem->equals(OperatingSystem::LINUX())) {
            if ($path === null) {
                $path = 'google-chrome';
            }

            return $this->getVersionFromCommandLine(sprintf('%s --version', $path));
        }

        if ($operatingSystem->equals(OperatingSystem::MACOS())) {
            if ($path === null) {
                $path = '/Applications/Google\ Chrome.app';
            }

            return $this->getVersionFromCommandLine(
                sprintf('%s/Contents/MacOS/Google\ Chrome --version', $path)
            );
        }

        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            if ($path === null) {
                $path = 'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe';
            }

            return $this->getVersionFromCommandLine(
                sprintf('wmic datafile where name="%s" get Version /value', $path)
            );
        }

        throw NotImplemented::feature(
            sprintf(
                'Automatically resolving %s version on %s',
                $this->supportedBrowserName()->getValue(),
                $operatingSystem
            )
        );
    }

    private function getVersionFromCommandLine($command) : Version
    {
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            new RuntimeException(
                sprintf('%s version could not be determined.', $this->supportedBrowserName()->getValue()),
                0,
                new ProcessFailedException($process)
            );
        }

        return Version::fromString($process->getOutput());
    }

    public function supportedBrowserName() : Name
    {
        return Name::GOOGLE_CHROME();
    }
}
