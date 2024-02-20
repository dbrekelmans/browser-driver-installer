<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use DBrekelmans\BrowserDriverInstaller\Exception\UnexpectedType;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\Family;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use UnexpectedValueException;

use function array_map;
use function implode;
use function is_string;
use function sprintf;

use const PHP_OS_FAMILY;

/** @implements Option<OperatingSystem> */
final class OperatingSystemOption extends InputOption implements Option
{
    public function __construct()
    {
        parent::__construct(
            self::name(),
            $this->shortcut(),
            $this->mode()->value,
            $this->description(),
            $this->default(),
        );
    }

    public static function name(): string
    {
        return 'os';
    }

    public function shortcut(): string|null
    {
        return null;
    }

    public function mode(): OptionMode
    {
        return OptionMode::REQUIRED;
    }

    public function description(): string
    {
        return sprintf(
            'Operating system for which to install the driver (%s)',
            implode('|', array_map(static fn ($case) => $case->value, OperatingSystem::cases())),
        );
    }

    public function default(): string|null
    {
        return OperatingSystem::fromFamily(Family::from(PHP_OS_FAMILY))->value;
    }

    public static function value(InputInterface $input): OperatingSystem
    {
        $value = $input->getOption(self::name());

        if (! is_string($value)) {
            throw UnexpectedType::expected('string', $value);
        }

        if (OperatingSystem::tryFrom($value) === null) {
            throw new UnexpectedValueException(
                sprintf(
                    'Unexpected value %s. Expected one of: %s',
                    $value,
                    implode(', ', array_map(static fn ($case) => $case->value, OperatingSystem::cases())),
                ),
            );
        }

        return OperatingSystem::from($value);
    }
}
