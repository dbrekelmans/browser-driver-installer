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
    public const PREFIX  = 'browser';
    public const SUCCESS = 0;

    /** @var Filesystem */
    protected $filesystem;

    /** @var BrowserFactory */
    protected $browserFactory;

    /** @var DriverFactory */
    protected $driverFactory;

    /** @var DownloaderFactory */
    protected $driverDownloaderFactory;

    public function __construct(
        Filesystem $filesystem,
        BrowserFactory $browserFactory,
        DriverFactory $driverFactory,
        DownloaderFactory $driverDownloaderFactory
    ) {
        $this->filesystem              = $filesystem;
        $this->browserFactory          = $browserFactory;
        $this->driverFactory           = $driverFactory;
        $this->driverDownloaderFactory = $driverDownloaderFactory;

        parent::__construct(sprintf('%s:%s', self::PREFIX, static::browserName()->getValue()));
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

        $browserName = static::browserName();

        $installPath     = Input\InstallPathArgument::value($input);
        $operatingSystem = Input\OperatingSystemOption::value($input);
        $browserPath     = Input\BrowserPathOption::value($input);

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
        $filePath         = $driverDownloader->download($driver, $installPath);

        $io->success(
            sprintf('%s %s installed to %s', $driver->name()->getValue(), $driver->version()->toBuildString(), $filePath)
        );

        return self::SUCCESS;
    }
}
