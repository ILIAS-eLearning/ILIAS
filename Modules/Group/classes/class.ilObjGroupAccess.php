<?php

declare(strict_types=1);

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjGroupAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilObjGroupAccess extends ilObjectAccess
{
    protected static bool $using_code = false;

    /**
     * @inheritDoc
     */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];

        if (is_null($user_id)) {
            $user_id = $ilUser->getId();
        }

        switch ($cmd) {
            case "info":

                if (ilGroupParticipants::_isParticipant($ref_id, $user_id)) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_STATUS_INFO, $lng->txt("info_is_member"));
                } else {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_STATUS_INFO, $lng->txt("info_is_not_member"));
                }
                break;

            case "join":

                if (!self::_registrationEnabled($obj_id)) {
                    return false;
                }

                if (ilGroupWaitingList::_isOnList($ilUser->getId(), $obj_id)) {
                    return false;
                }

                if (ilGroupParticipants::_isParticipant($ref_id, $user_id)) {
                    return false;
                }
                break;

            case 'leave':

                // Regular member
                if ($permission == 'leave') {
                    $limit = null;
                    if (!ilObjGroup::mayLeave($obj_id, $user_id, $limit)) {
                        $ilAccess->addInfoItem(
                            ilAccessInfo::IL_STATUS_INFO,
                            sprintf($lng->txt("grp_cancellation_end_rbac_info"), ilDatePresentation::formatDate($limit))
                        );
                        return false;
                    }

                    if (!ilGroupParticipants::_isParticipant($ref_id, $user_id)) {
                        return false;
                    }
                }
                // Waiting list
                if ($permission == 'join') {
                    if (!ilGroupWaitingList::_isOnList($ilUser->getId(), $obj_id)) {
                        return false;
                    }
                }
                break;

        }

        switch ($permission) {
            case 'leave':
                return ilObjGroup::mayLeave($obj_id, $user_id);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function _getCommands(): array
    {
        $commands = array();
        $commands[] = array("permission" => "grp_linked", "cmd" => "", "lang_var" => "show", "default" => true);

        $commands[] = array("permission" => "join", "cmd" => "join", "lang_var" => "join");

        // on waiting list
        $commands[] = array('permission' => "join", "cmd" => "leave", "lang_var" => "leave_waiting_list");

        // regualar users
        $commands[] = array('permission' => "leave", "cmd" => "leave", "lang_var" => "grp_btn_unsubscribe");

        if (ilDAVActivationChecker::_isActive()) {
            $webdav_obj = new ilObjWebDAV();
            $commands[] = $webdav_obj->retrieveWebDAVCommandArrayForActionMenu();
        }

        $commands[] = array("permission" => "write", "cmd" => "enableAdministrationPanel", "lang_var" => "edit_content");
        $commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "settings");

        return $commands;
    }

    /**
     * @inheritDoc
    */
    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilUser = $DIC->user();

        $t_arr = explode("_", $target);
        // registration codes
        if (substr((string) ($t_arr[2] ?? ""), 0, 5) === 'rcode' and $ilUser->getId() != ANONYMOUS_USER_ID) {
            self::$using_code = true;
            return true;
        }

        if ($t_arr[0] != "grp" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", (int) $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", (int) $t_arr[1])) {
            return true;
        }
        return false;
    }

    public static function _registrationEnabled(int $a_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM grp_settings " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";

        $res = $ilDB->query($query);

        $enabled = $unlimited = false;
        $start = $end = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $enabled = $row->registration_enabled;
            $unlimited = $row->registration_unlimited;
            $start = $row->registration_start;
            $end = $row->registration_end;
        }

        if (!$enabled) {
            return false;
        }
        if ($unlimited) {
            return true;
        }
        $start = new ilDateTime($start, IL_CAL_DATETIME);
        $end = new ilDateTime($end, IL_CAL_DATETIME);
        $time = new ilDateTime(time(), IL_CAL_UNIX);
        return ilDateTime::_after($time, $start) and ilDateTime::_before($time, $end);
    }


    /**
     * @inheritDoc
     */
    public static function _preloadData(array $obj_ids, array $ref_ids): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        ilGroupWaitingList::_preloadOnListInfo([$ilUser->getId()], $obj_ids);
    }

    public static function lookupRegistrationInfo(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $query = 'SELECT registration_type, registration_enabled, registration_unlimited,  registration_start, ' .
            'registration_end, registration_mem_limit, registration_max_members FROM grp_settings ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);

        $info = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $info['reg_info_start'] = new ilDateTime($row->registration_start, IL_CAL_DATETIME);
            $info['reg_info_end'] = new ilDateTime($row->registration_end, IL_CAL_DATETIME);
            $info['reg_info_type'] = $row->registration_type;
            $info['reg_info_mem_limit'] = $row->registration_mem_limit;
            $info['reg_info_unlimited'] = $row->registration_unlimited;

            $info['reg_info_max_members'] = 0;
            if ($info['reg_info_mem_limit']) {
                $info['reg_info_max_members'] = $row->registration_max_members;
            }

            $info['reg_info_enabled'] = $row->registration_enabled;
        }

        $registration_possible = $info['reg_info_enabled'];

        // Limited registration (added $registration_possible, see bug 0010157)
        if (!$info['reg_info_unlimited'] && $registration_possible) {
            $dt = new ilDateTime(time(), IL_CAL_UNIX);
            if (ilDateTime::_before($dt, $info['reg_info_start'])) {
                $info['reg_info_list_prop']['property'] = $lng->txt('grp_list_reg_start');
                $info['reg_info_list_prop']['value'] = ilDatePresentation::formatDate($info['reg_info_start']);
            } elseif (ilDateTime::_before($dt, $info['reg_info_end'])) {
                $info['reg_info_list_prop']['property'] = $lng->txt('grp_list_reg_end');
                $info['reg_info_list_prop']['value'] = ilDatePresentation::formatDate($info['reg_info_end']);
            } else {
                $registration_possible = false;
                $info['reg_info_list_prop']['property'] = $lng->txt('grp_list_reg_period');
                $info['reg_info_list_prop']['value'] = $lng->txt('grp_list_reg_noreg');
            }
        } else {
            // added !$registration_possible, see bug 0010157
            if (!$registration_possible) {
                $registration_possible = false;
                $info['reg_info_list_prop']['property'] = $lng->txt('grp_list_reg');
                $info['reg_info_list_prop']['value'] = $lng->txt('grp_list_reg_noreg');
            }
        }

        if ($info['reg_info_mem_limit'] && $info['reg_info_max_members'] && $registration_possible) {
            // Check for free places
            $part = ilGroupParticipants::_getInstanceByObjId($a_obj_id);

            $info['reg_info_list_size'] = ilCourseWaitingList::lookupListSize($a_obj_id);
            if ($info['reg_info_list_size']) {
                $info['reg_info_free_places'] = 0;
            } else {
                $info['reg_info_free_places'] = max(0, $info['reg_info_max_members'] - $part->getCountMembers());
            }

            if ($info['reg_info_free_places']) {
                $info['reg_info_list_prop_limit']['property'] = $lng->txt('grp_list_reg_limit_places');
                $info['reg_info_list_prop_limit']['value'] = $info['reg_info_free_places'];
            } else {
                $info['reg_info_list_prop_limit']['property'] = '';
                $info['reg_info_list_prop_limit']['value'] = $lng->txt('grp_list_reg_limit_full');
            }
        }

        return $info;
    }

    /**
     * @param int $a_obj_id
     * @return array<{property: string, value: string}> | null
     * @throws ilDateTimeException
     */
    public static function lookupPeriodInfo(int $a_obj_id): ?array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $start = $end = null;
        $query = 'SELECT period_start, period_end, period_time_indication FROM grp_settings ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, ilDBConstants::T_INTEGER);

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            if (!$row->period_time_indication) {
                $start = ($row->period_start
                    ? new \ilDate($row->period_start, IL_CAL_DATETIME)
                    : null);
                $end = ($row->period_end
                    ? new \ilDate($row->period_end, IL_CAL_DATETIME)
                    : null);
            } else {
                $start = ($row->period_start
                    ? new \ilDateTime($row->period_start, IL_CAL_DATETIME, \ilTimeZone::UTC)
                    : null);
                $end = ($row->period_end
                    ? new \ilDateTime($row->period_end, IL_CAL_DATETIME, \ilTimeZone::UTC)
                    : null);
            }
        }
        if ($start && $end) {
            $lng->loadLanguageModule('grp');

            return
                [
                    'property' => $lng->txt('grp_period'),
                    'value' => ilDatePresentation::formatPeriod($start, $end)
                ];
        }
        return null;
    }

    public static function _usingRegistrationCode(): bool
    {
        return self::$using_code;
    }
}
