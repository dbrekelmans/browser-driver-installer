<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Tests;

use DBrekelmans\BrowserDriverInstaller\CommandLine\CommandLineEnvironment;
use RuntimeException;

use function Safe\sprintf;

class CommandLineMock implements CommandLineEnvironment
{
    /** @var string[] */
    private array $commandToOutput = [];

    public function givenCommandWillReturnOutput(string $command, string $output): void
    {
        $this->commandToOutput[$command] = $output;
    }

    public function getCommandLineSuccessfulOutput(string $command): string
    {
        if (!isset($this->commandToOutput[$command])) {
            throw new RuntimeException(sprintf('Command %s is not mocked.', $command));
        }

        return $this->commandToOutput[$command];
    }
}
