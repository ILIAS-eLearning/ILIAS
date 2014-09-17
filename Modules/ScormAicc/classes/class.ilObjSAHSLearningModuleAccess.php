<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");
include_once 'Services/AccessControl/interfaces/interface.ilConditionHandling.php';

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
		include_once './Services/AccessControl/classes/class.ilConditionHandler.php';
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
	public static function checkCondition($a_trigger_obj_id,$a_operator,$a_value,$a_usr_id)
	{
		switch($a_operator)
		{

			case ilConditionHandler::OPERATOR_FAILED:
				include_once './Services/Tracking/classes/class.ilLPStatus.php';
				ilLPStatus::_lookupStatus($a_trigger_obj_id, $a_usr_id) == ilLPStatus::LP_STATUS_FAILED_NUM;
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
    function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        global $ilUser, $lng, $rbacsystem, $ilAccess;

        if ($a_user_id == "")
        {
            $a_user_id = $ilUser->getId();
        }

        switch ($a_cmd)
        {
            case "view":

                if(!ilObjSAHSLearningModuleAccess::_lookupOnline($a_obj_id)
                    && !$rbacsystem->checkAccessOfUser($a_user_id,'write',$a_ref_id))
                {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                break;
        }

        switch ($a_permission)
        {
            case "visible":
                if (!ilObjSAHSLearningModuleAccess::_lookupOnline($a_obj_id) &&
                    (!$rbacsystem->checkAccessOfUser($a_user_id,'write', $a_ref_id)))
                {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                break;
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
     *    (
     *        array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *        array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *    );
     */
    function _getCommands($a_obj_id)
    {
        $commands = array
        (
            array("permission" => "read", "cmd" => "view", "lang_var" => "show","default" => true),
            array("permission" => "write", "cmd" => "editContent", "lang_var" => "edit_content"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings")
        );
		if (ilObjSAHSLearningModuleAccess::_lookupOfflineModeAvailable($a_obj_id)) {
			$offlineMode=ilObjSAHSLearningModuleAccess::_lookupUserIsOfflineMode($a_obj_id);
			if ($offlineMode == false) {
				$commands[]=array("permission" => "read", "cmd" => "offlineModeStart", "lang_var" => "offline_mode");
			}
			else {
				$commands[]=array("permission" => "read", "cmd" => "offlineModeStop", "lang_var" => "offline_mode");
				$commands[0]=array("permission" => "read", "cmd" => "offlineModeView", "lang_var" => "show","default" => true);
			}
		}
        
        return $commands;
    }

    //
    // access relevant methods
    //

    /**
    * check wether learning module is online
    */
    function _lookupOnline($a_id)
    {
        global $ilDB;

        $set = $ilDB->queryF('SELECT c_online FROM sahs_lm WHERE id = %s', 
        array('integer'), array($a_id));
        $rec = $ilDB->fetchAssoc($set);
        
        return ilUtil::yn2tf($rec["c_online"]);
    }
    
    /**
    * Lookup editable
    */
    static function _lookupEditable($a_obj_id)
    {
		global $ilDB;
		
		$set = $ilDB->queryF('SELECT * FROM sahs_lm WHERE id = %s', 
			array('integer'), array($a_obj_id));
		$rec = $ilDB->fetchAssoc($set);

		return $rec["editable"];
    }
    

    /**
    * check whether goto script will succeed
    */
    function _checkGoto($a_target)
    {
        global $ilAccess;
        
        $t_arr = explode("_", $a_target);

        if ($t_arr[0] != "sahs" || ((int) $t_arr[1]) <= 0)
        {
            return false;
        }

        if ($ilAccess->checkAccess("visible", "", $t_arr[1]))
        {
            return true;
        }
        return false;
    }

    /**
     * Returns the number of bytes used on the harddisk by the learning module
     * with the specified object id.
     * @param int object id of a file object.
     */
    function _lookupDiskUsage($a_id)
    {
        $lm_data_dir = ilUtil::getWebspaceDir('filesystem')."/lm_data";
        $lm_dir = $lm_data_dir.DIRECTORY_SEPARATOR."lm_".$a_id;
        
        return file_exists($lm_dir) ? ilUtil::dirsize($lm_dir) : 0;
        
    }

	/**
		* Checks whether a certificate exists for the active user or not
		* @param int obj_id Object ID of the SCORM Learning Module
		* @param int usr_id Object ID of the user. If not given, the active user will be taken
		* @return true/false
		*/
	public static function _lookupUserCertificate($obj_id, $usr_id = 0)
	{
		global $ilUser;
		$uid = ($usr_id) ? $usr_id : $ilUser->getId();
		
		$completed = false;
		// check for certificates
		include_once "./Services/Certificate/classes/class.ilCertificate.php";
		if (ilCertificate::isActive() && ilCertificate::isObjectActive($obj_id))
		{
			$lpdata = false;
			include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
			$type = ilObjSAHSLearningModule::_lookupSubType($obj_id);
			include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
			if (ilObjUserTracking::_enabledLearningProgress())
			{
				include_once "./Services/Tracking/classes/class.ilLPStatus.php";
				$completed = ilLPStatus::_hasUserCompleted($obj_id, $uid);
				$lpdata = true;
			}
			switch ($type)
			{
				case "scorm":
					if (!$lpdata)
					{
						include_once "./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php";
						$completed = ilObjSCORMLearningModule::_getCourseCompletionForUser($obj_id, $uid);
					}
					break;
				case "scorm2004":
					if (!$lpdata)
					{
						include_once "./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php";
						$completed = ilObjSCORM2004LearningModule::_getCourseCompletionForUser($obj_id, $uid);
					}
					break;
				default:
					break;
			}
		}
		return $completed;
	}

	/**
	 * Type-specific implementation of general status
	 *
	 * Used in ListGUI and Learning Progress
	 *
	 * @param int $a_obj_id
	 * @return bool
	 */
	static function _isOffline($a_obj_id)
	{
		return !self::_lookupOnline($a_obj_id);
	}
	
	/**
		* Checks offlineMode and returns false if 
		*/
	static function _lookupUserIsOfflineMode($a_obj_id)
	{
		global $ilDB,$ilUser;

		$user_id = $ilUser->getId();

		$set = $ilDB->queryF('SELECT offline_mode FROM sahs_user WHERE obj_id = %s AND user_id = %s', 
			array('integer','integer'),
			array($a_obj_id, $user_id)
		);
		$rec = $ilDB->fetchAssoc($set);
		if ($rec["offline_mode"] == "offline") return true;
		return false;
	}
	
	/**
	* check wether learning module is online
	*/
	function _lookupOfflineModeAvailable($a_id)
	{
		global $ilDB;

		$set = $ilDB->queryF('SELECT offline_mode FROM sahs_lm WHERE id = %s', 
		array('integer'), array($a_id));
		$rec = $ilDB->fetchAssoc($set);

		return ilUtil::yn2tf($rec["offline_mode"]);
	}
}

?>
