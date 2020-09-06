<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use Symfony\Component\Console\Input\InputOption;

final class BrowserPathOption extends InputOption implements Option
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
        return 'browser-path';
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
        return 'Path to the browser if it\'s not installed in the default location';
    }

    public function default() : ?string
    {
        return null;
    }
}