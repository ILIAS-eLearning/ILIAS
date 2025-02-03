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
 * calendar exclusions
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilCalendarRecurrenceExclusions
{
    /**
     * Read exclusion dates
     * @return ilCalendarRecurrenceExclusion[]
     */
    public static function getExclusionDates($a_cal_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT excl_id FROM cal_rec_exclusion " .
            "WHERE cal_id = " . $ilDB->quote($a_cal_id, 'integer');
        $res = $ilDB->query($query);
        $exclusions = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $exclusions[] = new ilCalendarRecurrenceExclusion((int) $row->excl_id);
        }
        return $exclusions;
    }

    /**
     * Delete exclusion dates of calendar entry
     */
    public static function delete(int $a_cal_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "DELETE FROM cal_rec_exclusion " .
            "WHERE cal_id = " . $ilDB->quote($a_cal_id, 'integer');
        $ilDB->manipulate($query);
    }
}
