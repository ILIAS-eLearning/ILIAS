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

namespace ILIAS\Logging\Logger\Monolog;

use Monolog\LogRecord;
use ILIAS\Logging\ILIASLogLevel;

class ILIASTraceProcessor
{
    protected const array SKIP_CLASS_NAMES_START_WITH = [
        "Monolog",
        \ILIAS\Logging\Logger\Logger::class,
        \Psr\Log\AbstractLogger::class,
        "ilLogger"
    ];

    public function __construct(
        protected ILIASLogLevel $level
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if ($record['level'] < $this->level->value) {
            return $record;
        }

        $trace = debug_backtrace();

        // shift current method and first internal monolog call
        array_shift($trace);
        array_shift($trace);

        $previous_line = $trace[0]['line'] ?? '';
        while (($class = $trace[0]['class'] ?? '') !== '') {
            foreach (self::SKIP_CLASS_NAMES_START_WITH as $start) {
                if (str_starts_with($class, $start)) {
                    /*
                     * To find the line where the logger is called, we need to stay one frame "behind",
                     * otherwise we'll get the line where the function that calls the logger is
                     * called from.
                     */
                    $previous_line = $trace[0]['line'] ?? '';
                    array_shift($trace);
                    continue 2;
                }
            }
            break;
        }

        if (is_array($trace) && count($trace)) {
            $trace_info =
                ($trace[0]['class'] ?? '') . '::' .
                ($trace[0]['function'] ?? '') . ':' .
                $previous_line;
            $record['extra'] = array_merge(
                $record['extra'],
                array('trace' => $trace_info)
            );
        }
        return $record;
    }
}
