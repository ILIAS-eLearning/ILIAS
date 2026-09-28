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

use Monolog\Formatter\LineFormatter as LineFormatter;
use Monolog\LogRecord;

class ILIASLineFormatter extends LineFormatter
{
    protected const string DEFAULT_FORMAT = "[%extra.suid%] [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
    protected const string DEFAULT_DATE_FORMAT = 'Y-m-d H:i:s.u';

    public function __construct()
    {
        parent::__construct(
            self::DEFAULT_FORMAT,
            self::DEFAULT_DATE_FORMAT,
            true,
            true,
            false
        );
    }

    public function format(LogRecord $record): string
    {
        if (isset($record["extra"]["trace"])) {
            $trace = $record["extra"]["trace"];
            unset($record["extra"]["trace"]);
            $record = $record->with(
                message: $trace . " " . $record["message"],
                extra: $record["extra"]
            );
        }

        $record = $record->with(context: []);

        return parent::format($record);
    }
}
