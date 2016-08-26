<?php
include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");

/**
 * Access/Condition checking for career goal
 *
 * Please do not create instances of large application classes (like ilObjExample)
 * Write small methods within this class to determine the status.
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de> 
 */
class ilObjTalentAssessmentAccess extends ilObjectPluginAccess {

	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* @param        string        $a_cmd               command (not permission!)
	* @param        string        $a_permission        permission
	* @param        int           $a_ref_id            reference id
	* @param        int           $a_obj_id            object id
	* @param        int           $a_user_id           user id (default is current user)
	*
	* @return       boolean       true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "") {
	global $ilUser, $ilAccess;

		if ($a_user_id == "") {
			$a_user_id = $ilUser->getId();
		}

		switch ($a_permission) {
		case "read":
			if (!ilObjTalentAssessmentAccess::checkOnline($a_obj_id)
				 && !$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id))
			{
				return false;
			}
			break;
		}

		return true;
	}

	/**
	* Check online status of example object
	*/
	static function checkOnline($a_id) {
		global $ilDB;

		return true;
	}
}