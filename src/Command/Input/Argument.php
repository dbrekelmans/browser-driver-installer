<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

interface Argument
{
    public static function name() : string;

    public function description() : string;

    public function mode() : ArgumentMode;

    public function default() : ?string;
}