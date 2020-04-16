<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");
include_once 'Services/Conditions/interfaces/interface.ilConditionHandling.php';

/**
* Class ilObjContentObjectAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilObjSAHSLearningModuleAccess extends ilObjectAccess implements ilConditionHandling
{
    
    /**
     * Get possible conditions operaditors
     */
    public static function getConditionOperators()
    {
        include_once './Services/Conditions/classes/class.ilConditionHandler.php';
        return array(
            ilConditionHandler::OPERATOR_FINISHED,
            ilConditionHandler::OPERATOR_FAILED
        );
    }
    
    
    /**
     * check condition
     * @param type $a_svy_id
     * @param type $a_operator
     * @param type $a_value
     * @param type $a_usr_id
     * @return boolean
     */
    public static function checkCondition($a_trigger_obj_id, $a_operator, $a_value, $a_usr_id)
    {
        switch ($a_operator) {

            case ilConditionHandler::OPERATOR_FAILED:
                include_once './Services/Tracking/classes/class.ilLPStatus.php';
                return ilLPStatus::_lookupStatus($a_trigger_obj_id, $a_usr_id) == ilLPStatus::LP_STATUS_FAILED_NUM;
                break;
            
            case ilConditionHandler::OPERATOR_FINISHED:
            default:
                include_once './Services/Tracking/classes/class.ilLPStatus.php';
                return ilLPStatus::_hasUserCompleted($a_trigger_obj_id, $a_usr_id);

        }
        return true;
    }
    
    
    
    /**
    * checks wether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * @param    string        $a_cmd        command (not permission!)
    * @param    string        $a_permission    permission
    * @param    int            $a_ref_id    reference id
    * @param    int            $a_obj_id    object id
    * @param    int            $a_user_id    user id (if not provided, current user is taken)
    *
    * @return    boolean        true, if everything is ok
    */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "") //UK weg?
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }

        // switch ($a_cmd)
        // {
        // case "view":

        // if(!ilObjSAHSLearningModuleAccess::_lookupOnline($a_obj_id)
        // && !$rbacsystem->checkAccessOfUser($a_user_id,'write',$a_ref_id))
        // {
        // $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
        // return false;
        // }
        // break;
        // }

        // switch ($a_permission)
        // {
        // case "visible":
        // if (!ilObjSAHSLearningModuleAccess::_lookupOnline($a_obj_id) &&
        // (!$rbacsystem->checkAccessOfUser($a_user_id,'write', $a_ref_id)))
        // {
        // $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
        // return false;
        // }
        // break;
        // }


        return true;
    }
    
    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *    (
     *        array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *        array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *    );
     */
    public static function _getCommands($a_obj_id = null)
    {
        $commands = array(
            array("permission" => "read", "cmd" => "view", "lang_var" => "show","default" => true),
            array("permission" => "write", "cmd" => "editContent", "lang_var" => "edit_content"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings")
        );
        // #14866
        if ($a_obj_id && ilObjSAHSLearningModuleAccess::_lookupOfflineModeAvailable($a_obj_id)) {
            $offlineMode = ilObjSAHSLearningModuleAccess::_lookupUserIsOfflineMode($a_obj_id);
            if ($offlineMode == false) {
                $commands[] = array("permission" => "read", "cmd" => "offlineModeStart", "lang_var" => "offline_mode");
            } else {
                $commands[] = array("permission" => "read", "cmd" => "offlineModeStop", "lang_var" => "offline_mode");
                $commands[0] = array("permission" => "read", "cmd" => "offlineModeView", "lang_var" => "show","default" => true);
            }
        }
        
        return $commands;
    }

    //
    // access relevant methods
    //

    
    /**
    * Lookup editable
    */
    public static function _lookupEditable($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->queryF(
            'SELECT * FROM sahs_lm WHERE id = %s',
            array('integer'),
            array($a_obj_id)
        );
        $rec = $ilDB->fetchAssoc($set);

        return $rec["editable"];
    }
    

    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        
        $t_arr = explode("_", $a_target);

        if ($t_arr[0] != "sahs" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("visible", "", $t_arr[1]) || $ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }
        return false;
    }

    /**
     * Returns the number of bytes used on the harddisk by the learning module
     * with the specified object id.
     * @param int object id of a file object.
     */
    public static function _lookupDiskUsage($a_id)
    {
        $lm_data_dir = ilUtil::getWebspaceDir('filesystem') . "/lm_data";
        $lm_dir = $lm_data_dir . DIRECTORY_SEPARATOR . "lm_" . $a_id;
        
        return file_exists($lm_dir) ? ilUtil::dirsize($lm_dir) : 0;
    }


    /**
        * Checks offlineMode and returns false if
        */
    public static function _lookupUserIsOfflineMode($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $user_id = $ilUser->getId();

        $set = $ilDB->queryF(
            'SELECT offline_mode FROM sahs_user WHERE obj_id = %s AND user_id = %s',
            array('integer','integer'),
            array($a_obj_id, $user_id)
        );
        $rec = $ilDB->fetchAssoc($set);
        if ($rec["offline_mode"] == "offline") {
            return true;
        }
        return false;
    }
    
    /**
    * check wether learning module is online
    */
    public static function _lookupOfflineModeAvailable($a_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $set = $ilDB->queryF(
            'SELECT offline_mode FROM sahs_lm WHERE id = %s',
            array('integer'),
            array($a_id)
        );
        $rec = $ilDB->fetchAssoc($set);

        return ilUtil::yn2tf($rec["offline_mode"]);
    }
}
