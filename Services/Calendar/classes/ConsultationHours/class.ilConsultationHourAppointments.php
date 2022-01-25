<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Consultation hour appointments
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilConsultationHourAppointments
{

    /**
     * @return int[]
     */
    public static function getAppointmentIds(
        int $a_user_id,
        int $a_context_id = null,
        ?ilDateTime $a_start = null,
        ?int $a_type = null,
        bool $a_check_owner = true
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$a_type) {
            $a_type = ilCalendarCategory::TYPE_CH;
        }
        $owner = ' ';
        if ($a_check_owner) {
            $owner = " AND be.obj_id = " . $ilDB->quote($a_user_id, 'integer');
        }

        $query = "SELECT ce.cal_id FROM cal_entries ce" .
            " JOIN cal_cat_assignments cca ON ce.cal_id = cca.cal_id" .
            " JOIN cal_categories cc ON cca.cat_id = cc.cat_id" .
            " JOIN booking_entry be ON ce.context_id  = be.booking_id" .
            " WHERE cc.obj_id = " . $ilDB->quote($a_user_id, 'integer') .
            $owner .
            " AND cc.type = " . $ilDB->quote($a_type, 'integer');

        if ($a_context_id) {
            $query .= " AND ce.context_id = " . $ilDB->quote($a_context_id, 'integer');
        }
        if ($a_start) {
            $query .= " AND ce.starta = " . $ilDB->quote($a_start->get(IL_CAL_DATETIME, '', 'UTC'), 'text');
        }
        $query .= (' ORDER BY ce.starta ASC');
        $res = $ilDB->query($query);
        $entries = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $entries[] = (int) $row->cal_id;
        }
        return $entries;
    }

    /**
     * Get appointment ids by consultation hour group
     * @return int[]
     * @todo check start time
     */
    public static function getAppointmentIdsByGroup(
        int $a_user_id,
        int $a_ch_group_id,
        ?ilDateTime $start = null
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        $type = ilCalendarCategory::TYPE_CH;
        $start_limit = '';
        if ($start instanceof ilDateTime) {
            $start_limit = 'AND ce.starta >= ' . $ilDB->quote($start->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp');
        }
        $query = 'SELECT ce.cal_id FROM cal_entries ce ' .
            'JOIN cal_cat_assignments ca ON ce.cal_id = ca.cal_id ' .
            'JOIN cal_categories cc ON ca.cat_id = cc.cat_id ' .
            'JOIN booking_entry be ON ce.context_id = be.booking_id ' .
            'WHERE cc.obj_id = ' . $ilDB->quote($a_user_id, 'integer') . ' ' .
            'AND cc.type = ' . $ilDB->quote($type, 'integer') . ' ' .
            'AND be.booking_group = ' . $ilDB->quote($a_ch_group_id, 'integer') . ' ' .
            $start_limit . ' ' .
            'ORDER BY ce.starta ';
        $res = $ilDB->query($query);
        $app_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $app_ids[] = (int) $row->cal_id;
        }
        return $app_ids;
    }

    /**
     * Get all appointments
     * @return ilCalendarEntry[]
     */
    public static function getAppointments(int $a_user_id) : array
    {
        $entries = [];
        foreach (self::getAppointmentIds($a_user_id) as $app_id) {
            $entries[] = new ilCalendarEntry($app_id);
        }
        return $entries;
    }

    /**
     * Get consultation hour manager for current user or specific user.
     * @return    int | string
     */
    public static function getManager(bool $a_as_name = false, bool $a_full_name = false, int $a_user_id = null)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        if (!$a_user_id) {
            $user_id = $ilUser->getId();
        } else {
            $user_id = $a_user_id;
        }

        $set = $ilDB->query('SELECT admin_id FROM cal_ch_settings' .
            ' WHERE user_id = ' . $ilDB->quote($user_id, 'integer'));
        $row = $ilDB->fetchAssoc($set);
        if ($row && $row['admin_id']) {
            if ($a_as_name && $a_full_name) {
                return ilObjUser::_lookupFullname($row['admin_id']);
            } elseif ($a_as_name) {
                return ilObjUser::_lookupLogin($row['admin_id']);
            }
            return (int) $row['admin_id'];
        }
        return 0;
    }

    /**
     * Set consultation hour manager for current user
     * @param string $a_user_name
     * @return bool
     */
    public static function setManager(string $a_user_name) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        $user_id = false;
        if ($a_user_name) {
            $user_id = ilObjUser::_loginExists($a_user_name);
            if (!$user_id) {
                return false;
            }
        }

        $ilDB->manipulate('DELETE FROM cal_ch_settings' .
            ' WHERE user_id = ' . $ilDB->quote($ilUser->getId(), 'integer'));

        if ($user_id && $user_id != $ilUser->getId()) {
            $ilDB->manipulate('INSERT INTO cal_ch_settings (user_id, admin_id)' .
                ' VALUES (' . $ilDB->quote($ilUser->getId(), 'integer') . ',' .
                $ilDB->quote($user_id, 'integer') . ')');
        }
        return true;
    }

    /**
     * Get all managed consultation hours users for current users
     * @return array
     */
    public static function getManagedUsers() : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        $all = array();
        $set = $ilDB->query('SELECT user_id FROM cal_ch_settings' .
            ' WHERE admin_id = ' . $ilDB->quote($ilUser->getId(), 'integer'));
        while ($row = $ilDB->fetchAssoc($set)) {
            $all[(int) $row['user_id']] = ilObjUser::_lookupLogin((int) $row['user_id']);
        }
        return $all;
    }
}
