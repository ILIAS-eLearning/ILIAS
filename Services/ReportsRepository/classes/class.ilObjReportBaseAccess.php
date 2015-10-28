<?php
require_once 'Services/Repository/classes/class.ilObjectPluginAccess.php';

abstract class ilObjReportBaseAccess extends ilObjectPluginAccess {

	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here. Also don't do usual RBAC checks.
	*
	* @param        string        $a_cmd                command (not permission!)
	* @param        string        $a_permission        permission
	* @param        int                $a_ref_id                reference id
	* @param        int                $a_obj_id                object id
	* @param        int                $a_user_id                user id (default is current user)
	*
	* @return        boolean                true, if everything is ok
	*/

	/**
	* Check online status of example object
	*/
	abstract static public function checkOnline($a_id);
}