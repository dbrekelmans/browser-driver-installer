<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use DBrekelmans\BrowserDriverInstaller\Cpu\CpuArchitecture;
use DBrekelmans\BrowserDriverInstaller\Exception\UnexpectedType;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use UnexpectedValueException;

use function array_map;
use function implode;
use function is_string;
use function sprintf;

/** @implements Option<CpuArchitecture> */
final class CpuArchitectureOption extends InputOption implements Option
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
        return 'arch';
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
            'CPU architecture for which to install the driver (%s)',
            implode('|', array_map(static fn ($case) => $case->value, CpuArchitecture::cases())),
        );
    }

    public function default(): string|null
    {
        return CpuArchitecture::X86_64->value;
    }

    public static function value(InputInterface $input): CpuArchitecture
    {
        $value = $input->getOption(self::name());

        if (! is_string($value)) {
            throw UnexpectedType::expected('string', $value);
        }

        if (CpuArchitecture::tryFrom($value) === null) {
            throw new UnexpectedValueException(
                sprintf(
                    'Unexpected value %s. Expected one of: %s',
                    $value,
                    implode(', ', array_map(static fn ($case) => $case->value, CpuArchitecture::cases())),
                ),
            );
        }

        return CpuArchitecture::from($value);
    }
}
