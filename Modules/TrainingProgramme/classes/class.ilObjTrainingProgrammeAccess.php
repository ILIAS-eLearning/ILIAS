<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once('./Services/Object/classes/class.ilObjectAccess.php');
require_once('./Services/User/classes/class.ilUserAccountSettings.php');

/**
 * Class ilObjTrainingProgrammeAccess
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */
class ilObjTrainingProgrammeAccess extends ilObjectAccess {
	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here. Also don't do any RBAC checks.
	*
	* @param	string		$a_cmd			command (not permission!)
 	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	int			$a_user_id		user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $ilAccess;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		// add no access info item and return false if access is not granted
		// $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $a_text, $a_data = "");
		//
		// for all RBAC checks use checkAccessOfUser instead the normal checkAccess-method:
		// $rbacsystem->checkAccessOfUser($a_user_id, $a_permission, $a_ref_id)

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
	 *        array('permission' => 'read', 'cmd' => 'view', 'lang_var' => 'show'),
	 *        array('permission' => 'write', 'cmd' => 'edit', 'lang_var' => 'edit'),
	 *    );
	 */
	public function _getCommands()
	{
		$commands = array();
		$commands[] = array( 'permission' => 'read', 'cmd' => 'view', 'lang_var' => 'show', 'default' => true );
//		$commands[] = array('permission' => 'read', 'cmd' => 'render', 'lang_var' => 'show', 'default' => true);
//		$commands[] = array('permission' => 'write', 'cmd' => 'enableAdministrationPanel', 'lang_var' => 'edit_content');
//		$commands[] = array( 'permission' => 'write', 'cmd' => 'edit', 'lang_var' => 'settings' );

		return $commands;
	}

	/**
	 * check whether goto script will succeed
	 */
	function _checkGoto($a_target)
	{
		die("here");
		global $ilAccess;
		$t_arr = explode('_', $a_target);
		if ($t_arr[0] != 'orgu' || ((int)$t_arr[1]) <= 0) {
			return false;
		}
		if ($ilAccess->checkAccess('read', '', $t_arr[1])) {
			return true;
		}

		return false;
	}
}

?>