<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserFactory;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\DownloaderFactory;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use function Safe\sprintf;

abstract class BrowserCommand extends Command
{
    protected Filesystem $filesystem;
    protected BrowserFactory $browserFactory;
    protected DriverFactory $driverFactory;
    protected DownloaderFactory $driverDownloaderFactory;

    public function __construct(
        Filesystem $filesystem,
        BrowserFactory $browserFactory,
        DriverFactory $driverFactory,
        DownloaderFactory $driverDownloaderFactory
    ) {
        $this->filesystem = $filesystem;
        $this->browserFactory = $browserFactory;
        $this->driverFactory = $driverFactory;
        $this->driverDownloaderFactory = $driverDownloaderFactory;

        parent::__construct(sprintf('browser:%s', static::browserName()->getValue()));
    }

    abstract protected static function browserName() : BrowserName;

    final protected function configure() : void
    {
        $this->setDescription(sprintf('Helps you install the driver for %s.', static::browserName()->getValue()));

        $this->setDefinition(
            new InputDefinition(
                [
                    new Input\InstallPathArgument(),
                    new Input\OperatingSystemOption(),
                    new Input\BrowserPathOption(),
                ]
            )
        );
    }

    final protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note(
            'This command is experimental. Please report any issues to https://github.com/dbrekelmans/browser-driver-installer/issues'
        );

        $browserName = static::browserName();

        $installPath = Input\InstallPathArgument::value($input);
        $operatingSystem = Input\OperatingSystemOption::value($input);
        $browserPath = Input\BrowserPathOption::value($input);

        if ($browserPath === null) {
            $browser = $this->browserFactory->createFromNameAndOperatingSystem($browserName, $operatingSystem);
        } else {
            $browser = $this->browserFactory->createFromNameOperatingSystemAndPath(
                $browserName,
                $operatingSystem,
                $browserPath
            );
        }

        if ($io->isVerbose()) {
            $io->writeln(sprintf('Found %s %s.', $browser->name()->getValue(), $browser->version()->toBuildString()));
        }

        $driver = $this->driverFactory->createFromBrowser($browser);

        if ($io->isVerbose()) {
            $io->writeln(
                sprintf('Downloading %s %s.', $driver->name()->getValue(), $driver->version()->toBuildString())
            );
        }

        $driverDownloader = $this->driverDownloaderFactory->createFromDriver($driver);
        $filePath = $driverDownloader->download($driver, $installPath);

        $io->success(
            sprintf('Chrome driver %s installed to %s', $driver->version()->toBuildString(), $filePath)
        );

        return self::SUCCESS;
    }
}
