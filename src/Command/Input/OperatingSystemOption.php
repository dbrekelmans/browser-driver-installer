<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use DBrekelmans\BrowserDriverInstaller\OperatingSystem\Family;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use Symfony\Component\Console\Input\InputOption;

use function implode;
use function Safe\sprintf;

use const PHP_OS_FAMILY;

class OperatingSystemOption extends InputOption implements Option
{
    public function __construct()
    {
        parent::__construct(
            self::name(),
            $this->shortcut(),
            $this->mode()->getValue(),
            $this->description(),
            $this->default()
        );
    }

    public static function name() : string
    {
        return 'os';
    }

    public function shortcut() : ?string
    {
        return null;
    }

    public function mode() : OptionMode
    {
        return OptionMode::OPTIONAL();
    }

    public function description() : string
    {
        return sprintf(
            'Operating system for which to install the driver (%s)',
            implode('|', OperatingSystem::toArray())
        );
    }

    public function default() : ?string
    {
        return OperatingSystem::fromFamily(new Family(PHP_OS_FAMILY))->getValue();
    }
}