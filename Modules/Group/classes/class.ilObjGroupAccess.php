<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

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
    protected static $using_code = false;
    /**
    * checks wether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * @param	string		$a_cmd		command (not permission!)
    * @param	string		$a_permission	permission
    * @param	int			$a_ref_id	reference id
    * @param	int			$a_obj_id	object id
    * @param	int			$a_user_id	user id (if not provided, current user is taken)
    *
    * @return	boolean		true, if everything is ok
    */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }
        
        switch ($a_cmd) {
            case "info":
            
                include_once './Modules/Group/classes/class.ilGroupParticipants.php';
                if (ilGroupParticipants::_isParticipant($a_ref_id, $a_user_id)) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_STATUS_INFO, $lng->txt("info_is_member"));
                } else {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_STATUS_INFO, $lng->txt("info_is_not_member"));
                }
                break;
                
            case "join":
            
                if (!self::_registrationEnabled($a_obj_id)) {
                    return false;
                }

                include_once './Modules/Group/classes/class.ilGroupWaitingList.php';
                if (ilGroupWaitingList::_isOnList($ilUser->getId(), $a_obj_id)) {
                    return false;
                }

                include_once './Modules/Group/classes/class.ilGroupParticipants.php';
                if (ilGroupParticipants::_isParticipant($a_ref_id, $a_user_id)) {
                    return false;
                }
                break;
                
            case 'leave':

                // Regular member
                if ($a_permission == 'leave') {
                    include_once './Modules/Group/classes/class.ilObjGroup.php';
                    $limit = null;
                    if (!ilObjGroup::mayLeave($a_obj_id, $a_user_id, $limit)) {
                        $ilAccess->addInfoItem(
                            ilAccessInfo::IL_STATUS_INFO,
                            sprintf($lng->txt("grp_cancellation_end_rbac_info"), ilDatePresentation::formatDate($limit))
                        );
                        return false;
                    }
                    
                    include_once './Modules/Group/classes/class.ilGroupParticipants.php';
                    if (!ilGroupParticipants::_isParticipant($a_ref_id, $a_user_id)) {
                        return false;
                    }
                }
                // Waiting list
                if ($a_permission == 'join') {
                    include_once './Modules/Group/classes/class.ilGroupWaitingList.php';
                    if (!ilGroupWaitingList::_isOnList($ilUser->getId(), $a_obj_id)) {
                        return false;
                    }
                }
                break;
                
        }

        switch ($a_permission) {
            case 'leave':
                include_once './Modules/Group/classes/class.ilObjGroup.php';
                return ilObjGroup::mayLeave($a_obj_id, $a_user_id);
        }
        return true;
    }

    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *	(
     *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *	);
     */
    public static function _getCommands()
    {
        $commands = array();
        $commands[] = array("permission" => "grp_linked", "cmd" => "", "lang_var" => "show", "default" => true);

        include_once './Services/WebServices/FileManager/classes/class.ilFMSettings.php';
        if (ilFMSettings::getInstance()->isEnabled()) {
            $commands[] = array(
                'permission' => 'read',
                'cmd' => 'fileManagerLaunch',
                'lang_var' => 'fm_start',
                'enable_anonymous' => false
            );
        }

        $commands[] = array("permission" => "join", "cmd" => "join", "lang_var" => "join");

        // on waiting list
        $commands[] = array('permission' => "join", "cmd" => "leave", "lang_var" => "leave_waiting_list");
        
        // regualar users
        $commands[] = array('permission' => "leave", "cmd" => "leave", "lang_var" => "grp_btn_unsubscribe");
        
        include_once('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
        if (ilDAVActivationChecker::_isActive()) {
            include_once './Services/WebDAV/classes/class.ilWebDAVUtil.php';
            if (ilWebDAVUtil::getInstance()->isLocalPasswordInstructionRequired()) {
                $commands[] = array('permission' => 'read', 'cmd' => 'showPasswordInstruction', 'lang_var' => 'mount_webfolder', 'enable_anonymous' => 'false');
            } else {
                $commands[] = array("permission" => "read", "cmd" => "mount_webfolder", "lang_var" => "mount_webfolder", "enable_anonymous" => "false");
            }
        }

        $commands[] = array("permission" => "write", "cmd" => "enableAdministrationPanel", "lang_var" => "edit_content");
        $commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "settings");
        
        return $commands;
    }
    
    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];

        $t_arr = explode("_", $a_target);
        // registration codes
        if (substr($t_arr[2], 0, 5) == 'rcode' and $ilUser->getId() != ANONYMOUS_USER_ID) {
            self::$using_code = true;
            return true;
        }

        if ($t_arr[0] != "grp" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            return true;
        }
        return false;
    }
    
    /**
     *
     * @return
     * @param object $a_obj_id
     */
    public static function _registrationEnabled($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM grp_settings " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";

        $res = $ilDB->query($query);
        
        $enabled = $unlimited = false;
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
        
        if (!$unlimited) {
            $start = new ilDateTime($start, IL_CAL_DATETIME);
            $end = new ilDateTime($end, IL_CAL_DATETIME);
            $time = new ilDateTime(time(), IL_CAL_UNIX);
            
            return ilDateTime::_after($time, $start) and ilDateTime::_before($time, $end);
        }
        return false;
    }
    

    /**
     * Preload data
     *
     * @param array $a_obj_ids array of object ids
     */
    public static function _preloadData($a_obj_ids, $a_ref_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        
        include_once("./Modules/Group/classes/class.ilGroupWaitingList.php");
        ilGroupWaitingList::_preloadOnListInfo($ilUser->getId(), $a_obj_ids);
    }
    
    /**
     * Lookup registration info
     * @global ilDB $ilDB
     * @global ilObjUser $ilUser
     * @global ilLanguage $lng
     * @param int $a_obj_id
     * @return array
     */
    public static function lookupRegistrationInfo($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        
        $query = 'SELECT registration_type, registration_enabled, registration_unlimited,  registration_start, ' .
            'registration_end, registration_mem_limit, registration_max_members FROM grp_settings ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id);
        $res = $ilDB->query($query);
        
        $info = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $info['reg_info_start'] = new ilDateTime($row->registration_start, IL_CAL_DATETIME);
            $info['reg_info_end'] = new ilDateTime($row->registration_end, IL_CAL_DATETIME);
            $info['reg_info_type'] = $row->registration_type;
            $info['reg_info_max_members'] = $row->registration_max_members;
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
            include_once './Modules/Group/classes/class.ilGroupParticipants.php';
            $part = ilGroupParticipants::_getInstanceByObjId($a_obj_id);

            include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
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
     * Lookup course period info
     *
     * @param int $a_obj_id
     * @return array
     */
    public static function lookupPeriodInfo($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        
        $start = $end = null;
        
        $query = 'SELECT grp_start, grp_end FROM grp_settings' .
            ' WHERE obj_id = ' . $ilDB->quote($a_obj_id);
        $set = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($set)) {
            $start = $row['grp_start']
                ? new ilDate($row['grp_start'], IL_CAL_UNIX)
                : null;
            $end = $row['grp_end']
                ? new ilDate($row['grp_end'], IL_CAL_UNIX)
                : null;
        }
        
        if ($start && $end) {
            $lng->loadLanguageModule('grp');
            
            return array(
                'property' => $lng->txt('grp_period'),
                'value' => ilDatePresentation::formatPeriod($start, $end)
            );
        }
    }
    

    /**
     * Using Registration code
     *
     * @return bool
     */
    public static function _usingRegistrationCode()
    {
        return self::$using_code;
    }
}
