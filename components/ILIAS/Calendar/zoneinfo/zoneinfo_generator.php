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

exit;

const ZONEINFO = '/usr/share/zoneinfo';
const TZ_CONVERT = 'tz_convert';
const READLINK = 'readlink';

chdir('../../..');


foreach (ilCalendarUtil::_getShortTimeZoneList() as $tz_name => $tmp) {
    $name_underscore = str_replace('/', '_', $tz_name);

    if (is_link(ZONEINFO . '/' . $tz_name)) {
        $name = exec(READLINK . ' -f ' . ZONEINFO . '/' . $tz_name);
    } else {
        $name = ZONEINFO . '/' . $tz_name;
    }

    exec(TZ_CONVERT . ' -o Services/Calendar/zoneinfo/' . $name_underscore . '.tmp' . ' ' . $name);

    $reader = fopen('components/ILIAS/Calendar/zoneinfo/' . $name_underscore . '.tmp', 'r');
    $writer = fopen('components/ILIAS/Calendar/zoneinfo/' . $name_underscore . '.ics', 'w');

    $counter = 0;
    while ($line = fgets($reader)) {
        if (++$counter < 4) {
            continue;
        }
        if ($counter == 5) {
            fputs($writer, 'TZID:' . $tz_name . "\n");
        } else {
            if (substr($line, 0, 13) === 'END:VCALENDAR') {
                break;
            }
            fputs($writer, $line);
        }
    }

    fclose($reader);
    fclose($writer);
    unlink('components/ILIAS/Calendar/zoneinfo/' . $name_underscore . '.tmp');

    #echo $name_underscore.' <br />';
}
