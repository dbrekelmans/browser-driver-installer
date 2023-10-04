<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use DBrekelmans\BrowserDriverInstaller\Exception\UnexpectedType;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use function is_string;

/**
 * @implements Option<string>
 */
final class VersionOption extends InputOption implements Option
{
    public const LATEST = 'latest';

    public function __construct()
    {
        parent::__construct(
            self::name(),
            $this->shortcut(),
            $this->mode()->value,
            $this->description(),
            $this->default()
        );
    }

    public static function name(): string
    {
        return 'driver-version';
    }

    /**
     * @inheritDoc
     */
    public static function value(InputInterface $input)
    {
        $value = $input->getOption(self::name());

        if (! is_string($value)) {
            throw UnexpectedType::expected('string', $value);
        }

        return $value;
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
        return 'Driver version to install';
    }

    public function default(): ?string
    {
        return self::LATEST;
    }
}
