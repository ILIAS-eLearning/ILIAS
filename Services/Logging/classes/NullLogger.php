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

namespace ILIAS\Services\Logging;

use ilLogger;
use ilLogLevel;
use Monolog\Logger;
use Exception;

class NullLogger extends ilLogger
{
    public function __construct()
    {
    }

    public function isHandling(int $level): bool
    {
        return true;
    }

    public function log(string $message, int $level = ilLogLevel::INFO, array $context = []): void
    {
    }

    public function dump($value, int $level = ilLogLevel::INFO): void
    {
    }

    public function debug(string $message, array $context = []): void
    {
    }

    public function info(string $message, array $context = []): void
    {
    }

    public function notice(string $message, array $context = []): void
    {
    }

    public function warning(string $message, array $context = []): void
    {
    }

    public function error(string $message, array $context = []): void
    {
    }

    public function critical(string $message, array $context = []): void
    {
    }

    public function alert(string $message, array $context = []): void
    {
    }

    public function emergency(string $message, array $context = []): void
    {
    }

    /** @noinspection \PhpInconsistentReturnPointsInspection */
    public function getLogger(): Logger
    {
        throw new Exception('Can not return monolog logger from a null logger.');
    }

    public function write(string $message, $level = ilLogLevel::INFO, array $context = []): void
    {
    }

    public function writeLanguageLog(string $topic, string $lang_key): void
    {
    }

    public function logStack(?int $level = null, string $message = '', array $context = []): void
    {
    }

    public function writeMemoryPeakUsage(int $level): void
    {
    }
}
