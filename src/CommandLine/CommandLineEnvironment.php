<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\CommandLine;

use RuntimeException;

interface CommandLineEnvironment
{
    /**
     * Provides output of a command line if successful
     * Will throw RuntimeException if not successful
     *
     * @throws RuntimeException
     */
    public function getCommandLineSuccessfulOutput(string $command) : string;
}
