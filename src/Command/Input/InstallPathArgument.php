<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;

use function Safe\sprintf;

use const DIRECTORY_SEPARATOR;

class InstallPathArgument extends InputArgument implements Argument
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        parent::__construct(
            self::name(),
            $this->mode()->getValue(),
            $this->description(),
            $this->default()
        );
    }

    public static function name() : string
    {
        return 'install-path';
    }

    public function mode() : ArgumentMode
    {
        return ArgumentMode::OPTIONAL();
    }

    public function description() : string
    {
        return 'Location where the driver will be installed';
    }

    public function default() : ?string
    {
        return $this->filesystem->readlink(
            sprintf(
                '%s%s..%s..%s..%sbin',
                __DIR__,
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR
            ),
            true
        );
    }

    public function shortcut() : ?string
    {
        return null;
    }
}