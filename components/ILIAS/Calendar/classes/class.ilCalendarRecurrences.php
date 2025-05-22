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
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarRecurrences
{
    /**
     * get all recurrences of an appointment
     */
    public static function _getRecurrences(int $a_cal_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT rule_id FROM cal_recurrence_rules " .
            "WHERE cal_id = " . $ilDB->quote($a_cal_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $recurrences = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $recurrences[] = new ilCalendarRecurrence((int) $row->rule_id);
        }
        return $recurrences;
    }

    public static function _getFirstRecurrence($a_cal_id): ilCalendarRecurrence
    {
        $recs = self::_getRecurrences($a_cal_id);
        if (count($recs)) {
            return $recs[0];
        }
        $new_rec = new ilCalendarRecurrence();
        $new_rec->setEntryId($a_cal_id);
        return $new_rec;
    }
}
