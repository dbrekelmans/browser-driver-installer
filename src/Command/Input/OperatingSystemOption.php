<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller\Command\Input;

use DBrekelmans\BrowserDriverInstaller\Exception\UnexpectedType;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\Family;
use DBrekelmans\BrowserDriverInstaller\OperatingSystem\OperatingSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use UnexpectedValueException;
use const PHP_OS_FAMILY;
use function implode;
use function is_string;
use function Safe\sprintf;

/**
 * @implements Option<OperatingSystem>
 */
final class OperatingSystemOption extends InputOption implements Option
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
        return 'os';
    }

    public function shortcut() : ?string
    {
        return null;
    }

    public function mode() : OptionMode
    {
        return OptionMode::REQUIRED();
    }

    public function description() : string
    {
        return sprintf(
            'Operating system for which to install the driver (%s)',
            implode('|', OperatingSystem::toArray())
        );
    }

    public function default() : ?string
    {
        /**
         * @psalm-suppress MixedArgument
         */
        return OperatingSystem::fromFamily(new Family(PHP_OS_FAMILY))->getValue();
    }

    /**
     * @return OperatingSystem
     *
     * @inheritDoc
     */
    public static function value(InputInterface $input)
    {
        $value = $input->getOption(self::name());

        if (! is_string($value)) {
            throw UnexpectedType::expected('string', $value);
        }

        if (! OperatingSystem::isValid($value)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Unexpected value %s. Expected one of: %s',
                    $value,
                    implode(', ', OperatingSystem::toArray())
                )
            );
        }

        return new OperatingSystem($value);
    }
}
