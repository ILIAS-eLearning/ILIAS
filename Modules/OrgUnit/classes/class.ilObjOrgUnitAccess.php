<?php

require_once('./Services/Object/classes/class.ilObjectAccess.php');
class ilObjOrgUnitAccess extends ilObjectAccess {

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
	function _getCommands()
	{
		$commands = array();
		$commands[] = array( 'permission' => 'read', 'cmd' => 'render', 'lang_var' => 'show', 'default' => true );
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