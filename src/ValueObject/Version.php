<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\ValueObject;

use InvalidArgumentException;
use Safe\Exceptions\PcreException;
use function implode;
use function Safe\preg_match;
use function Safe\sprintf;

final class Version
{
    private string $major;
    private string $minor;
    private string $patch;
    private ?string $build;

    public function __construct(string $major, string $minor, string $patch, ?string $build)
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->build = $build;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromString(string $versionString) : self
    {
        try {
            if (0 === preg_match(
                    "/(?'major'\d+)\.(?'minor'\d+)\.(?'patch'\d+)(\.(?'build'\d+))?/",
                    $versionString,
                    $matches
                )) {
                throw new InvalidArgumentException(
                    sprintf('Could not parse version string "%s".', $versionString)
                );
            }

            return new self(
                $matches['major'],
                $matches['minor'],
                $matches['patch'],
                $matches['build'] ?? null
            );
        } catch (PcreException $e) {
            throw new InvalidArgumentException(
                sprintf('Could not parse version string "%s".', $versionString), 0, $e
            );
        }
    }

    public function major() : string
    {
        return $this->major;
    }

    public function minor() : string
    {
        return $this->minor;
    }

    public function patch() : string
    {
        return $this->patch;
    }

    public function build() : ?string
    {
        return $this->build;
    }

    public function toString() : string
    {
        return implode('.', [$this->major, $this->minor, $this->patch]);
    }

    public function toBuildString() : string
    {
        $versionString = $this->toString();

        if ($this->build !== null) {
            $versionString .= '.' . $this->build;
        }

        return $versionString;
    }
}
