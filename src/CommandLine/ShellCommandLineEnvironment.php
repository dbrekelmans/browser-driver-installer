<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\CommandLine;

use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

use function Safe\sprintf;

class ShellCommandLineEnvironment implements CommandLineEnvironment
{
    public function getCommandLineSuccessfulOutput(string $command): string
    {
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                sprintf('Command %s failed', $command),
                0,
                new ProcessFailedException($process)
            );
        }

        return $process->getOutput();
    }
}
