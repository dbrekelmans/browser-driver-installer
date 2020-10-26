<?php

declare(strict_types=1);

namespace DBrekelmans\BrowserDriverInstaller;

use InvalidArgumentException;
use Safe\Exceptions\PcreException;

use function implode;
use function Safe\preg_match;
use function Safe\sprintf;

final class Version
{
    private const DELIMITER = '.';

    /** @var string */
    private $major;

    /** @var string */
    private $minor;

    /** @var string */
    private $patch;

    /** @var string|null */
    private $build;

    private function __construct(string $major, string $minor, string $patch, ?string $build)
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->build = $build;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromString(string $versionString): self
    {
        try {
            if (
                preg_match(
                    "/(?'major'\d+)\.(?'minor'\d+)\.(?'patch'\d+)(\.(?'build'\d+))?/",
                    $versionString,
                    $matches
                ) === 0
            ) {
                throw new InvalidArgumentException(
                    sprintf('Could not parse version string "%s".', $versionString)
                );
            }

            if (!isset($matches['major'], $matches['minor'], $matches['patch'])) {
                throw new InvalidArgumentException(
                    sprintf('Could not parse version string "%s".', $versionString)
                );
            }

            return new self(
                (string) $matches['major'],
                (string) $matches['minor'],
                (string) $matches['patch'],
                isset($matches['build']) ? (string) $matches['build'] : null
            );
        } catch (PcreException $exception) {
            throw new InvalidArgumentException(
                sprintf('Could not parse version string "%s".', $versionString),
                0,
                $exception
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

    public function patch(): string
    {
        return $this->patch;
    }

    public function build(): ?string
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
        return implode(self::DELIMITER, [$this->major, $this->minor, $this->patch]);
    }
}
