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
    public function __construct(
        protected ILIASLogLevel $level
    ) {
    }

    /**
     * @todo fix shifting calls
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        if ($record['level'] < $this->level->value) {
            return $record;
        }

        $trace = debug_backtrace();

        // shift current method
        array_shift($trace);

        // shift internal monolog calls
        array_shift($trace);
        array_shift($trace);
        array_shift($trace);
        array_shift($trace);

        if (is_array($trace) && count($trace)) {
            $trace_info =
                ($trace[0]['class'] ?? '') . '::' .
                ($trace[0]['function'] ?? '') . ':' .
                ($trace[0]['line'] ?? '');
            $record['extra'] = array_merge(
                $record['extra'],
                array('trace' => $trace_info)
            );
        }
        return $record;
    }
}
