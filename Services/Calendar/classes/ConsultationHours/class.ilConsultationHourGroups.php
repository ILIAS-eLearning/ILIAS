<?php

declare(strict_types=1);

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


/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourGroups
{
    /**
     * Get a all groups of an user
     * @return ilConsultationHourGroup[]
     */
    public static function getGroupsOfUser(int $a_user_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT grp_id FROM cal_ch_group ' .
            'WHERE usr_id = ' . $ilDB->quote($a_user_id, 'integer');
        $res = $ilDB->query($query);
        $groups = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $groups[] = new ilConsultationHourGroup((int) $row->grp_id);
        }
        return $groups;
    }

    /**
     * Get number of consultation hour groups
     */
    public static function getCountGroupsOfUser(int $a_user_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT COUNT(grp_id) num FROM cal_ch_group ' .
            'WHERE usr_id = ' . $ilDB->quote($a_user_id, 'integer') . ' ' .
            'GROUP BY grp_id';

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->num;
        }
        return 0;
    }

    /**
     * Lookup group title
     */
    public static function lookupTitle(int $a_group_id): string
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = 'SELECT title from cal_ch_group ' .
            'WHERE grp_id = ' . $ilDB->quote($a_group_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->title;
        }
        return '';
    }

    /**
     * Lookup max number of bookings for group
     */
    public static function lookupMaxBookings(int $a_group_id): int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = 'SELECT multiple_assignments from cal_ch_group ' .
            'WHERE grp_id = ' . $ilDB->quote($a_group_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->multiple_assignments;
        }
        return 0;
    }

    /**
     * Get group selection options
     */
    public static function getGroupSelectOptions(int $a_user_id): array
    {
        global $DIC;

        $lng = $DIC->language();
        $groups = self::getGroupsOfUser($a_user_id);
        if (!count($groups)) {
            return array();
        }
        $options = array();
        foreach ($groups as $group) {
            $options[(string) $group->getGroupId()] = $group->getTitle();
        }
        asort($options, SORT_STRING);
        $sorted_options = array();
        $sorted_options[0] = $lng->txt('cal_ch_grp_no_assignment');
        foreach ($options as $key => $opt) {
            $sorted_options[$key] = $opt;
        }
        return $sorted_options;
    }
}
