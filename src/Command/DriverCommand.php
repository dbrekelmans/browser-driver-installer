<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command;

use DBrekelmans\BrowserDriverInstaller\Browser\BrowserFactory;
use DBrekelmans\BrowserDriverInstaller\Browser\BrowserName;
use DBrekelmans\BrowserDriverInstaller\Driver\DownloaderFactory;
use DBrekelmans\BrowserDriverInstaller\Driver\DriverFactory;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\Family;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

use function implode;
use function Safe\sprintf;

use const PHP_OS_FAMILY;

final class DriverCommand extends Command
{
    public const SUCCESS = 0;
    public const FAILURE = 1;

    private const BROWSER_NAME = 'browser';
    private const OPERATING_SYSTEM = 'os';
    private const BROWSER_PATH = 'browser-path';
    private const DRIVER_LOCATION = 'location';
    private const DEFAULT_DRIVER_LOCATION = __DIR__ . '/../../bin';

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

        $this->addOption(
            self::BROWSER_NAME,
            null,
            InputOption::VALUE_REQUIRED,
            sprintf(
                'The browser for which to install the driver (%s)',
                implode('|', BrowserName::toArray())
            ),
            BrowserName::GOOGLE_CHROME
        );

        $this->addOption(
            self::OPERATING_SYSTEM,
            null,
            InputOption::VALUE_REQUIRED,
            sprintf(
                'The operating system for which to install the driver (%s)',
                implode('|', OperatingSystem::toArray())
            ),
            OperatingSystem::fromFamily(new Family(PHP_OS_FAMILY))->getValue()
        );

        $this->addOption(
            self::DRIVER_LOCATION,
            null,
            InputOption::VALUE_REQUIRED,
            sprintf(
                'The location to download the driver to',
            ),
            $this->filesystem->readlink(self::DEFAULT_DRIVER_LOCATION, true)
        );

        $this->addOption(
            self::BROWSER_PATH,
            null,
            InputOption::VALUE_OPTIONAL,
            sprintf(
                'The path to the browser if not installed in default location',
            ),
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('This command is experimental.');

        $browserName = new BrowserName($input->getOption(self::BROWSER_NAME));
        $driverLocation = $input->getOption(self::DRIVER_LOCATION);
        $operatingSystem = new OperatingSystem($input->getOption(self::OPERATING_SYSTEM));
        $browserPath = $input->getOption(self::BROWSER_PATH);

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
        $filePath = $driverDownloader->download($driver, $driverLocation);

        $io->success(
            sprintf('Chrome driver %s successfully installed at %s.', $driver->version()->toBuildString(), $filePath)
        );

        return self::SUCCESS;
    }
}
