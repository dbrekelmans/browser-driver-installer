<?php

declare(strict_types=1);

namespace BrowserDriverInstaller\BrowserVersionResolver;

use BrowserDriverInstaller\BrowserVersionResolverInterface;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Throwable;
use UnexpectedValueException;
use function preg_last_error_msg;
use function preg_match;
use function reset;
use function sprintf;

/**
 * @internal
 */
final class UnixGoogleChromeBrowserVersionResolver implements BrowserVersionResolverInterface
{
    /**
     * @var string
     */
    private $binary;

    public function __construct(string $binary)
    {
        $this->binary = $binary;
    }

    /**
     * @inheritDoc
     */
    public function resolveVersion() : string
    {
        $process = Process::fromShellCommandline(sprintf('%s --version', $this->binary));
        $process->run();

        if (!$process->isSuccessful()) {
            $this->fail(new ProcessFailedException($process));
        }

        return $this->parseVersionProcessOutput($process->getOutput());
    }

    /**
     * @throws RuntimeException
     */
    private function parseVersionProcessOutput(string $version) : string
    {
        $success = preg_match('/\d+\.\d+\.\d+/', $version, $output);

        if ($success === false) {
            $this->fail(new UnexpectedValueException(preg_last_error_msg()));
        }

        if ($success === 0) {
            $this->fail();
        }

        return reset($output);
    }

    /**
     * @throws RuntimeException
     */
    private function fail(?Throwable $exception = null) : void
    {
        throw new RuntimeException('Chrome version could not be resolved.', 0, $exception);
    }
}
