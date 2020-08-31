<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

interface Option
{
    public static function name() : string;

    public function shortcut() : ?string;

    public function description() : string;

    public function mode() : OptionMode;

    public function default(): ?string;
}