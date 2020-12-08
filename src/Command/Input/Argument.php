<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use Symfony\Component\Console\Input\InputInterface;
use UnexpectedValueException;

/**
 * @template T
 */
interface Argument
{
    public static function name(): string;

    /**
     * @return mixed
     *
     * @throws UnexpectedValueException
     *
     * @psalm-return T
     */
    public static function value(InputInterface $input);

    public function description(): string;

    public function mode(): ArgumentMode;

    public function default(): ?string;
}
