<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use Symfony\Component\Console\Input\InputInterface;
use UnexpectedValueException;

/** @template T */
interface Option
{
    public static function name(): string;

    /**
     * @return T
     *
     * @throws UnexpectedValueException
     */
    public static function value(InputInterface $input): mixed;

    public function shortcut(): string|null;

    public function description(): string;

    public function mode(): OptionMode;

    public function default(): string|null;
}
