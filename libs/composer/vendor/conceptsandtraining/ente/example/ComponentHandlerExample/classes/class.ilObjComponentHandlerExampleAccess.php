<?php
require_once 'Services/Repository/classes/class.ilObjectPluginAccess.php';

/**
 * Access checker for each plugin object.
 */
class ilObjComponentHandlerExampleAccess extends ilObjectPluginAccess {
	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here. Also don't do usual RBAC checks.
	*
	* @param 	string 		$a_cmd 				command (not permission!)
	* @param 	string 		$a_permission 		permission
	* @param 	int 		$a_ref_id 			reference id
	* @param 	int 		$a_obj_id 			object id
	* @param 	int 		$a_user_id 			user id (default is current user)
	*
	* @return 	boolean 						true, if everything is ok
	*/
	public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "") {
		global $ilUser, $ilAccess;

		/*
		* This Routine is called within ilAccess::checkAccessOfUser::doStatusCheck.
		* We rely on standart ilAccess::checkAccessOfUser procedure, i.e. return true here, except when the object is offline,
		* then redirect to read-permission check.
		*/
		if ($a_user_id == "") {
			$a_user_id = $ilUser->getId();
		}

		switch ($a_permission) {
			case "read":
				if (!self::checkOnline($a_obj_id)
					&& !$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id))
				{
					return false;
				}
		}

		return true;
	}

	/**
	* Check online status of object
	*/
	static public function checkOnline($a_id) {
		return true;
	}
}
