<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command;


use DBrekelmans\BrowserDriverInstaller\Browser\BrowserFactory;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\DownloaderFactory;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverFactory;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

use function Safe\sprintf;

final class DriverCommand extends Command
{
    private Filesystem $filesystem;
    private BrowserFactory $browserFactory;
    private DriverFactory $driverFactory;
    private DownloaderFactory $driverDownloaderFactory;

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

        parent::__construct('driver');
    }

    protected function configure() : void
    {
        $this->setDescription('Helps you install the appropriate browser driver.');

        $this->setDefinition(
            new InputDefinition(
                [
                    new Input\InstallPathArgument($this->filesystem),
                    new Input\OperatingSystemOption(),
                    new Input\BrowserPathOption(),
                ]
            )
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('This command is experimental.');

        // TODO: defaults to google chrome. Move to separate command
        $browserName = BrowserName::GOOGLE_CHROME();
        $installPath = $input->getArgument(Input\InstallPathArgument::name());
        $operatingSystem = new OperatingSystem($input->getOption(Input\OperatingSystemOption::name()));
        $browserPath = $input->getOption(Input\BrowserPathOption::name());

        if ($browserPath === null || $browserPath === '') {
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
