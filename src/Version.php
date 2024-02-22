<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller;

use InvalidArgumentException;
use Safe\Exceptions\PcreException;

use function implode;
use function Safe\preg_match;
use function sprintf;

final class Version
{
    private const DELIMITER = '.';

    private function __construct(
        private readonly string $major,
        private readonly string $minor,
        private readonly string|null $patch = null,
        private readonly string|null $build = null,
    ) {
    }

    /** @throws InvalidArgumentException */
    public static function fromString(string $versionString): self
    {
        try {
            if (
                preg_match(
                    "/(?'major'\d+)\.(?'minor'\d+)(\.(?'patch'\d+))?(\.(?'build'\d+))?/",
                    $versionString,
                    $matches,
                ) === 0
            ) {
                throw new InvalidArgumentException(
                    sprintf('Could not parse version string "%s".', $versionString),
                );
            }

            if (! isset($matches['major'], $matches['minor'])) {
                throw new InvalidArgumentException(
                    sprintf('Could not parse version string "%s".', $versionString),
                );
            }

            return new self(
                (string) $matches['major'],
                (string) $matches['minor'],
                isset($matches['patch']) ? (string) $matches['patch'] : null,
                isset($matches['build']) ? (string) $matches['build'] : null,
            );
        } catch (PcreException $exception) {
            throw new InvalidArgumentException(
                sprintf('Could not parse version string "%s".', $versionString),
                0,
                $exception,
            );
        }
    }

    public function major(): string
    {
        return $this->major;
    }

    public function minor(): string
    {
        return $this->minor;
    }

    public function patch(): string|null
    {
        return $this->patch;
    }

    public function build(): string|null
    {
        return $this->build;
    }

    public function toBuildString(): string
    {
        $versionString = $this->toString();

        if ($this->build !== null) {
            $versionString .= self::DELIMITER . $this->build;
        }

        return $versionString;
    }

    public function toString(): string
    {
        $versionString = implode(self::DELIMITER, [$this->major, $this->minor]);

        if ($this->patch !== null) {
            $versionString .= self::DELIMITER . $this->patch;
        }

        return $versionString;
    }
}
