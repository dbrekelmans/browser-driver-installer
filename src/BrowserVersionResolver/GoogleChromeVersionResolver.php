<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\BrowserVersionResolver;

use BrowserDriverInstaller\Enum\BrowserName;
use BrowserDriverInstaller\Enum\OperatingSystem;
use BrowserDriverInstaller\Exception\NotImplemented;
use BrowserDriverInstaller\ValueObject\Version;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use function Safe\sprintf;

final class GoogleChromeVersionResolver implements BrowserVersionResolver
{
    public function resolveVersion(OperatingSystem $operatingSystem, ?string $path = null) : Version
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

        throw new NotImplemented(
            sprintf(
                'Support for %s on %s is not yet implemented.',
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

    public function supportedBrowserName() : BrowserName
    {
        return BrowserName::GOOGLE_CHROME();
    }
}
