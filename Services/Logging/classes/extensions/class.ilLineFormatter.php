<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Custom line formatter
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesLogging
 */
class ilLineFormatter extends \Monolog\Formatter\LineFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        if (isset($record["extra"]["trace"])) {
            $record["message"] = $record["extra"]["trace"] . " " . $record["message"];
            $record["extra"] = array();
        }

        return parent::format($record);
    }
}
