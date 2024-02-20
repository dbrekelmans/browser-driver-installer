<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command;

use DBrekelmans\BrowserDriverInstaller\Driver\DownloaderFactory;
use DBrekelmans\BrowserDriverInstaller\Driver\Driver;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverName;
use DBrekelmans\BrowserDriverInstaller\Driver\VersionResolver;
use DBrekelmans\BrowserDriverInstaller\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

abstract class DriverCommand extends Command
{
    public const PREFIX = 'driver';

    public function __construct(
        private VersionResolver $versionResolver,
        private DownloaderFactory $downloaderFactory,
    ) {
        parent::__construct(sprintf('%s:%s', self::PREFIX, static::driverName()->value));
    }

    abstract protected static function driverName(): DriverName;

    final protected function configure(): void
    {
        $this->setDescription(sprintf('Helps you install the %s.', static::driverName()->value));

        $this->setDefinition(
            new InputDefinition(
                [
                    new Input\InstallPathArgument(),
                    new Input\VersionOption(),
                    new Input\OperatingSystemOption(),
                ],
            ),
        );
    }

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $driverName = static::driverName();

        $installPath     = Input\InstallPathArgument::value($input);
        $versionString   = Input\VersionOption::value($input);
        $operatingSystem = Input\OperatingSystemOption::value($input);

        // TODO: move this into VersionOption class
        if ($versionString === Input\VersionOption::LATEST) {
            $version = $this->versionResolver->latest();

            if ($io->isVerbose()) {
                $io->writeln(
                    sprintf('Latest %s version: %s.', $driverName->value, $version->toBuildString()),
                );
            }
        } else {
            $version = Version::fromString($versionString);
        }

        $driver = new Driver($driverName, $version, $operatingSystem);

        if ($io->isVerbose()) {
            $io->writeln(
                sprintf('Downloading %s %s.', $driver->name()->value, $driver->version()->toBuildString()),
            );
        }

        $driverDownloader = $this->downloaderFactory->createFromDriver($driver);
        $filePath         = $driverDownloader->download($driver, $installPath);

        $io->success(
            sprintf(
                '%s %s installed to %s',
                $driver->name()->value,
                $driver->version()->toBuildString(),
                $filePath,
            ),
        );

        return self::SUCCESS;
    }
}
