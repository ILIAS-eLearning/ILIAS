<?php

namespace ILIAS\Tree\Logging;

use ILIAS\Logging\Logger\LoggerInterface;
use ILIAS\Logging\ILIASLogLevel;
use Stringable;
use Psr\Log\LoggerTrait;

class NoLogger implements LoggerInterface
{
    use LoggerTrait;

    public function log($level, \Stringable|string $message, array $context = []): void
    {
    }

    public function isHandlingLogLevel(mixed $level): bool
    {
        return true;
    }

    public function dump(mixed $value, mixed $level = ILIASLogLevel::INFO): void
    {
    }

    public function logStack(
        mixed $level = ILIASLogLevel::INFO,
        Stringable|string $message = '',
        array $context = []
    ): void {
    }
}
