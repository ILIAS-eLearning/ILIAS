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

/**
 * Component logger with individual log levels by component id
 * @author Stefan Meyer
 */
abstract class ilLogger
{
    /**
     * Check whether current logger is handling a log level
     */
    public function isHandling(int $level): bool
    {
        return false;
    }

    public function log(string $message, int $level = ilLogLevel::INFO, array $context = []): void
    {
    }

    public function dump($value, int $level = ilLogLevel::INFO): void
    {
        $this->log('{dump}', $level, [
            'dump' => print_r($value, true),
        ]);
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

    /**
     * write log message
     * @deprecated since version 5.1
     * @see ilLogger->info(), ilLogger()->debug(), ...
     */
    public function write(string $message, $level = ilLogLevel::INFO, array $context = []): void
    {
        if (!in_array($level, ilLogLevel::getLevels())) {
            $level = ilLogLevel::INFO;
        }
    }

    /**
     * Write language log
     * @deprecated since version 5.1
     */
    public function writeLanguageLog(string $topic, string $lang_key): void
    {
    }

    public function logStack(?int $level = null, string $message = '', array $context = []): void
    {
        if (is_null($level)) {
            $level = ilLogLevel::INFO;
        }

        if (!in_array($level, ilLogLevel::getLevels())) {
            $level = ilLogLevel::INFO;
        }


        try {
            throw new Exception($message);
        } catch (Exception $ex) {
        }
    }

    /**
     * Write memory peak usage
     * Automatically called at end of script
     */
    public function writeMemoryPeakUsage(int $level): void
    {
    }
}
