<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");
include_once './Modules/Course/classes/class.ilCourseConstants.php';
include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
include_once 'Modules/Course/classes/class.ilCourseParticipant.php';
include_once './Services/Conditions/interfaces/interface.ilConditionHandling.php';

/**
* Class ilObjCourseAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilObjCourseAccess extends ilObjectAccess implements ilConditionHandling
{
    protected static $using_code = false;

    /**
     * Get operators
     */
    public static function getConditionOperators()
    {
        include_once './Services/Conditions/classes/class.ilConditionHandler.php';
        return array(
            ilConditionHandler::OPERATOR_PASSED
        );
    }

    /**
     *
     * @global ilObjUser $ilUser
     * @param type $a_obj_id
     * @param type $a_operator
     * @param type $a_value
     * @param type $a_usr_id
     * @return boolean
     */
    public static function checkCondition($a_obj_id, $a_operator, $a_value, $a_usr_id)
    {
        include_once "./Modules/Course/classes/class.ilCourseParticipants.php";
        include_once './Services/Conditions/classes/class.ilConditionHandler.php';
        
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_PASSED:
                return ilCourseParticipants::_hasPassed($a_obj_id, $a_usr_id);
        }
        return false;
    }
    
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
        $ilias = $DIC['ilias'];
        
        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }
        
        if ($ilUser->getId() == $a_user_id) {
            $participants = ilCourseParticipant::_getInstanceByObjId($a_obj_id, $a_user_id);
        } else {
            $participants = ilCourseParticipants::_getInstanceByObjId($a_obj_id);
        }


        switch ($a_cmd) {
            case "view":
                if ($participants->isBlocked($a_user_id) and $participants->isAssigned($a_user_id)) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("crs_status_blocked"));
                    return false;
                }
                break;

            case 'leave':

                // Regular member
                if ($a_permission == 'leave') {
                    include_once './Modules/Course/classes/class.ilObjCourse.php';
                    $limit = null;
                    if (!ilObjCourse::mayLeave($a_obj_id, $a_user_id, $limit)) {
                        $ilAccess->addInfoItem(
                            ilAccessInfo::IL_STATUS_INFO,
                            sprintf($lng->txt("crs_cancellation_end_rbac_info"), ilDatePresentation::formatDate($limit))
                        );
                        return false;
                    }
                    
                    include_once './Modules/Course/classes/class.ilCourseParticipants.php';
                    if (!$participants->isAssigned($a_user_id)) {
                        return false;
                    }
                }
                // Waiting list
                if ($a_permission == 'join') {
                    include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
                    if (!ilCourseWaitingList::_isOnList($a_user_id, $a_obj_id)) {
                        return false;
                    }
                    return true;
                }
                break;

            case 'join':

                include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
                if (ilCourseWaitingList::_isOnList($a_user_id, $a_obj_id)) {
                    return false;
                }
                break;
        }
        
        switch ($a_permission) {
            case 'visible':
                $visible = null;
                $active = self::_isActivated($a_obj_id, $visible);
                $tutor = $rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id);
                if (!$active) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                }
                if (!$tutor && !$active && !$visible) {
                    return false;
                }
                break;
            
            case 'read':
                $tutor = $rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id);
                if ($tutor) {
                    return true;
                }
                $active = self::_isActivated($a_obj_id);
                if (!$active) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                if ($participants->isBlocked($a_user_id) and $participants->isAssigned($a_user_id)) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("crs_status_blocked"));
                    return false;
                }
                break;
                
            case 'join':
                if (!self::_registrationEnabled($a_obj_id)) {
                    return false;
                }

                if ($participants->isAssigned($a_user_id)) {
                    return false;
                }
                break;
                
            case 'leave':
                include_once './Modules/Course/classes/class.ilObjCourse.php';
                return ilObjCourse::mayLeave($a_obj_id, $a_user_id);
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
        $commands[] = array("permission" => "crs_linked", "cmd" => "", "lang_var" => "view", "default" => true);

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
        $commands[] = array('permission' => "leave", "cmd" => "leave", "lang_var" => "crs_unsubscribe");

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
        

        if ($t_arr[0] != "crs" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        // checking for read results in endless loop, if read is given
        // but visible is not given (-> see bug 5323)
        if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            //if ($ilAccess->checkAccess("visible", "", $t_arr[1]))
            return true;
        }
        return false;
    }
    
    /**
     * Lookup view mode. This is placed here to the need that ilObjFolder must
     * always instantiate a Course object.
     * @return
     * @param object $a_id
     */
    public static function _lookupViewMode($a_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT view_mode FROM crs_settings WHERE obj_id = " . $ilDB->quote($a_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->view_mode;
        }
        return false;
    }
    
    /**
     * Is activated?
     *
     * @see ilStartupGUI
     * @param int $a_obj_id
     * @param bool &$a_visible_flag
     * @param bool $a_mind_member_view
     * @return boolean
     */
    public static function _isActivated($a_obj_id, &$a_visible_flag = null, $a_mind_member_view = true)
    {
        // #7669
        if ($a_mind_member_view) {
            include_once './Services/Container/classes/class.ilMemberViewSettings.php';
            if (ilMemberViewSettings::getInstance()->isActive()) {
                $a_visible_flag = true;
                return true;
            }
        }
        
        $ref_id = ilObject::_getAllReferences($a_obj_id);
        $ref_id = array_pop($ref_id);
        
        $a_visible_flag = true;
        
        include_once './Services/Object/classes/class.ilObjectActivation.php';
        $item = ilObjectActivation::getItem($ref_id);
        switch ($item['timing_type']) {
            case ilObjectActivation::TIMINGS_ACTIVATION:
                if (time() < $item['timing_start'] or
                   time() > $item['timing_end']) {
                    $a_visible_flag = $item['visible'];
                    return false;
                }
                // fallthrough
                
                // no break
            default:
                return true;
        }
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

        $query = "SELECT * FROM crs_settings " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $type = $row->sub_limitation_type;
            $reg_start = $row->sub_start;
            $reg_end = $row->sub_end;
        }

        switch ($type) {
            case IL_CRS_SUBSCRIPTION_UNLIMITED:
                return true;

            case IL_CRS_SUBSCRIPTION_DEACTIVATED:
                return false;

            case IL_CRS_SUBSCRIPTION_LIMITED:
                if (time() > $reg_start and
                   time() < $reg_end) {
                    return true;
                }
                // no break
            default:
                return false;
        }
        return false;
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
        
        $query = 'SELECT sub_limitation_type, sub_start, sub_end, sub_mem_limit, sub_max_members FROM crs_settings ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id);
        $res = $ilDB->query($query);
        
        $info = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $info['reg_info_start'] = new ilDateTime($row->sub_start, IL_CAL_UNIX);
            $info['reg_info_end'] = new ilDateTime($row->sub_end, IL_CAL_UNIX);
            $info['reg_info_type'] = $row->sub_limitation_type;
            $info['reg_info_max_members'] = $row->sub_max_members;
            $info['reg_info_mem_limit'] = $row->sub_mem_limit;
        }
        
        $registration_possible = true;

        // Limited registration
        if ($info['reg_info_type'] == ilCourseConstants::SUBSCRIPTION_LIMITED) {
            $dt = new ilDateTime(time(), IL_CAL_UNIX);
            if (ilDateTime::_before($dt, $info['reg_info_start'])) {
                $info['reg_info_list_prop']['property'] = $lng->txt('crs_list_reg_start');
                $info['reg_info_list_prop']['value'] = ilDatePresentation::formatDate($info['reg_info_start']);
            } elseif (ilDateTime::_before($dt, $info['reg_info_end'])) {
                $info['reg_info_list_prop']['property'] = $lng->txt('crs_list_reg_end');
                $info['reg_info_list_prop']['value'] = ilDatePresentation::formatDate($info['reg_info_end']);
            } else {
                $registration_possible = false;
                $info['reg_info_list_prop']['property'] = $lng->txt('crs_list_reg_period');
                $info['reg_info_list_prop']['value'] = $lng->txt('crs_list_reg_noreg');
            }
        } elseif ($info['reg_info_type'] == ilCourseConstants::SUBSCRIPTION_UNLIMITED) {
            $registration_possible = true;
        } else {
            $registration_possible = false;
            $info['reg_info_list_prop']['property'] = $lng->txt('crs_list_reg');
            $info['reg_info_list_prop']['value'] = $lng->txt('crs_list_reg_noreg');
        }
        
        if ($info['reg_info_mem_limit'] && $info['reg_info_max_members'] && $registration_possible) {
            // Check for free places
            include_once './Modules/Course/classes/class.ilCourseParticipant.php';
            $part = ilCourseParticipant::_getInstanceByObjId($a_obj_id, $ilUser->getId());

            include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
            $info['reg_info_list_size'] = ilCourseWaitingList::lookupListSize($a_obj_id);
            if ($info['reg_info_list_size']) {
                $info['reg_info_free_places'] = 0;
            } else {
                $info['reg_info_free_places'] = max(0, $info['reg_info_max_members'] - $part->getNumberOfMembers());
            }
            
            if ($info['reg_info_free_places']) {
                $info['reg_info_list_prop_limit']['property'] = $lng->txt('crs_list_reg_limit_places');
                $info['reg_info_list_prop_limit']['value'] = $info['reg_info_free_places'];
            } else {
                $info['reg_info_list_prop_limit']['property'] = '';
                $info['reg_info_list_prop_limit']['value'] = $lng->txt('crs_list_reg_limit_full');
            }
        }
        
        return $info;
    }

    /**
     * Type-specific implementation of general status
     *
     * Used in ListGUI and Learning Progress
     *
     * @param int $a_obj_id
     * @return bool
     */
    public static function _isOffline($a_obj_id)
    {
        $dummy = null;
        return !self::_isActivated($a_obj_id, $dummy, false);
    }
    
    /**
     * Preload data
     *
     * @param array $a_obj_ids array of object ids
     */
    public static function _preloadData($a_obj_ids, $a_ref_ids)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];

        $lng->loadLanguageModule("crs");
        
        ilCourseWaitingList::_preloadOnListInfo($ilUser->getId(), $a_obj_ids);
        
        $repository = new ilUserCertificateRepository();
        $coursePreload = new ilCertificateObjectsForUserPreloader($repository);
        $coursePreload->preLoad($ilUser->getId(), $a_obj_ids);
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
        
        $query = 'SELECT crs_start, crs_end FROM crs_settings' .
            ' WHERE obj_id = ' . $ilDB->quote($a_obj_id);
        $set = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($set)) {
            $start = $row['crs_start']
                ? new ilDate($row['crs_start'], IL_CAL_UNIX)
                : null;
            $end = $row['crs_end']
                ? new ilDate($row['crs_end'], IL_CAL_UNIX)
                : null;
        }
        
        if ($start && $end) {
            $lng->loadLanguageModule('crs');
            
            return array(
                'property' => $lng->txt('crs_period'),
                'value' => ilDatePresentation::formatPeriod($start, $end)
            );
        }
    }
}
