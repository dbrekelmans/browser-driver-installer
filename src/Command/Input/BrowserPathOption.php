<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use DBrekelmans\BrowserDriverInstaller\Exception\UnexpectedType;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

use function is_string;

/**
 * @implements Option<string|null>
 */
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

    public static function name(): string
    {
        return 'browser-path';
    }

    public function shortcut(): ?string
    {
        return null;
    }

    public function mode(): OptionMode
    {
        return OptionMode::REQUIRED();
    }

    public function description(): string
    {
        return 'Path to the browser if it\'s not installed in the default location';
    }

    public function default(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public static function value(InputInterface $input)
    {
        $value = $input->getOption(self::name());

        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw UnexpectedType::expected('string', $value);
        }

        return $value;
    }
}
