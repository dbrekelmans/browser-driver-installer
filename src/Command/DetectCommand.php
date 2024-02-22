<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

use function sprintf;

final class DetectCommand extends Command
{
    public const NAME = 'detect';

    public function __construct()
    {
        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        $this->setDescription('Detects installed browsers and installs corresponding drivers.');

        $this->setDefinition(
            new InputDefinition(
                [
                    new Input\InstallPathArgument(),
                    new Input\OperatingSystemOption(),
                ],
            ),
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $application = $this->getApplication();

        if ($application === null) {
            $io->error('Could not find application from command.');

            return self::FAILURE;
        }

        $arguments = [
            Input\InstallPathArgument::name() => Input\InstallPathArgument::value($input),
            '--' . Input\OperatingSystemOption::name() => Input\OperatingSystemOption::value($input)->value,
        ];

        $returnCode = self::SUCCESS;

        foreach (BrowserName::cases() as $browserName) {
            $commandName = sprintf('%s:%s', BrowserCommand::PREFIX, $browserName->value);

            try {
                $command = $application->find($commandName);
            } catch (CommandNotFoundException $exception) {
                if ($io->isVeryVerbose()) {
                    $io->warning(sprintf('Could not find command "%s".', $commandName));
                }

                if ($io->isDebug()) {
                    $io->writeln($exception->getMessage());
                }

                continue;
            }

            try {
                $innerReturnCode = $command->run(new ArrayInput($arguments), $output);

                if ($innerReturnCode > $returnCode) {
                    $returnCode = $innerReturnCode;
                }
            } catch (Throwable $exception) { // @phpstan-ignore-line
                if ($io->isVerbose()) {
                    $io->warning(sprintf('Could not execute command "%s".', $commandName));
                }

                if ($io->isDebug()) {
                    $io->writeln($exception->getMessage());
                }
            }
        }

        return $returnCode;
    }
}
