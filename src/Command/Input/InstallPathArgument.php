<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use Symfony\Component\Console\Input\InputArgument;

class InstallPathArgument extends InputArgument implements Argument
{
    public function __construct()
    {
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
        return ArgumentMode::REQUIRED();
    }

    public function description() : string
    {
        return 'Location where the driver will be installed';
    }

    public function default() : ?string
    {
        return null;
    }

    public function shortcut() : ?string
    {
        return null;
    }
}