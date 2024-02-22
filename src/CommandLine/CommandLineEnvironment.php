<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\CommandLine;

use RuntimeException;

interface CommandLineEnvironment
{
    /** @throws RuntimeException If command is not successful. */
    public function getCommandLineSuccessfulOutput(string $command): string;
}
