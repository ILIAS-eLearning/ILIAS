<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Logging\Logger;

use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use ILIAS\Logging\ILIASLogLevel;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Exception;
use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger implements LoggerInterface
{
    public function __construct(
        protected MonologLogger $logger
    ) {
    }

    public function isHandlingLogLevel(mixed $level): bool
    {
        return $this->logger->isHandling($level);
    }

    public function dump(mixed $value, mixed $level = ILIASLogLevel::INFO): void
    {
        $this->log($level, '{dump}', ['dump' => print_r($value, true)]);
    }

    public function logStack(mixed $level = ILIASLogLevel::INFO, string|Stringable $message = '', array $context = []): void
    {
        try {
            throw new Exception($message);
        } catch (Exception $ex) {
            $this->log($level, $message . "\n" . $ex->getTraceAsString(), $context);
        }
    }

    public function log(mixed $level, Stringable|string $message, array $context = []): void
    {
        if ($level instanceof ILIASLogLevel) {
            $level = $level->value;
        }
        $this->logger->log($level, $message, $context);
    }
}
