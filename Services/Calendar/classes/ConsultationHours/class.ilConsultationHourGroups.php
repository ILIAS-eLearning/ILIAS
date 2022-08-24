<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return (int) $row->num;
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
