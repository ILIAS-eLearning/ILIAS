<?php declare(strict_types=1);

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

use Monolog\Formatter\LineFormatter as LineFormatter;

/**
 * Custom line formatter
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup ServicesLogging
 */
class ilLineFormatter extends LineFormatter
{
    /**
     * @inheritDoc
     */
    public function format(array $record) : string
    {
        if (isset($record["extra"]["trace"])) {
            $record["message"] = $record["extra"]["trace"] . " " . $record["message"];
            $record["extra"] = array();
        }
        return parent::format($record);
    }
}
