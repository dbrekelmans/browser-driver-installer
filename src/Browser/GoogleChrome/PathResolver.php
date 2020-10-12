<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Browser\GoogleChrome;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Browser\PathResolver as PathResolverInterface;
use DBrekelmans\BrowserDriverInstaller\Exception\NotImplemented;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

use function Safe\sprintf;

final class PathResolver implements PathResolverInterface
{
    /**
     * @throws NotImplemented
     */
    public function from(OperatingSystem $operatingSystem) : string
    {
        if ($operatingSystem->equals(OperatingSystem::LINUX())) {
            // TODO: command -v google-chrome
            return 'google-chrome';
        }

        if ($operatingSystem->equals(OperatingSystem::MACOS())) {
            // TODO: check if file exists
            return '/Applications/Google\ Chrome.app';
        }

        if ($operatingSystem->equals(OperatingSystem::WINDOWS())) {
            $process = Process::fromShellCommandline(
                '(Get-ItemProperty "Registry::HKEY_CURRENT_USER\SOFTWARE\Google\Update").LastInstallerSuccessLaunchCmdLine'
            );

            try {
                $process->mustRun();
            } catch (ProcessFailedException $exception) {
                new RuntimeException(
                    sprintf('Path could not be determined.'),
                    0,
                    $exception
                );
            }

            return $process->getOutput();
        }

        throw NotImplemented::feature(sprintf('Resolving path on %s', $operatingSystem->getValue()));
    }

    public function supports(BrowserName $browserName) : bool
    {
        return $browserName->equals(BrowserName::GOOGLE_CHROME());
    }
}
