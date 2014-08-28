<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Object/classes/class.ilObjectAccess.php');
require_once('./Services/User/classes/class.ilUserAccountSettings.php');
/**
 * Class ilObjOrgUnitAccess
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 *
 */
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
	 * @param integer $ref_id
	 *
	 * @return bool
	 */
	public static function _checkAccessStaff($ref_id) {
		global $ilAccess;

		if (($ilAccess->checkAccess("write", "", $ref_id)
			OR $ilAccess->checkAccess("view_learning_progress", "", $ref_id))
			AND $ilAccess->checkAccess("read", "", $ref_id)) {
			return true;
		}

		return false;
	}

	/**
	 * @param integer $ref_id
	 *
	 * @return bool
	 */
	public static function _checkAccessStaffRec($ref_id) {
		global $ilAccess;

		if (($ilAccess->checkAccess("write", "", $ref_id)
			OR $ilAccess->checkAccess("view_learning_progress_rec", "", $ref_id))
			AND $ilAccess->checkAccess("read", "", $ref_id)) {
			return true;
		}

		return false;
	}

	/**
	 * @param integer $ref_id
	 *
	 * @return bool
	 */
	public static function _checkAccessAdministrateUsers($ref_id) {
		global $ilAccess;

		if (ilUserAccountSettings::getInstance()->isLocalUserAdministrationEnabled() AND
			$ilAccess->checkAccess('cat_administrate_users', "", $ref_id)) {
			return true;
		}

		return false;
	}

	/**
	 * @param integer $ref_id
	 * @param integer $usr_id
	 *
	 * @return bool
	 */
	public static function _checkAccessToUserLearningProgress($ref_id,$usr_id) {
		global $ilAccess;

		//Permission to view the Learning Progress of an OrgUnit: Employees
		if ($ilAccess->checkAccess("view_learning_progress", "", $ref_id)
			AND in_array($usr_id, ilObjOrgUnitTree::_getInstance()->getEmployees($ref_id, false))) {
			return true;
		}
		//Permission to view the Learning Progress of an OrgUnit: Superiors
		if ($ilAccess->checkAccess("view_learning_progress", "", $ref_id)
			AND in_array($usr_id, ilObjOrgUnitTree::_getInstance()->getSuperiors($ref_id, false))) {
			return true;
		}

		//Permission to view the Learning Progress of an OrgUnit or SubOrgUnit!: Employees
		if ($ilAccess->checkAccess("view_learning_progress_rec", "", $ref_id)
		AND in_array($usr_id, ilObjOrgUnitTree::_getInstance()->getEmployees($ref_id, true))) {
			return true;
		}

		//Permission to view the Learning Progress of an OrgUnit or SubOrgUnit!: Superiors
		if ($ilAccess->checkAccess("view_learning_progress_rec", "", $ref_id)
			AND in_array($usr_id, ilObjOrgUnitTree::_getInstance()->getSuperiors($ref_id, true))) {
			return true;
		}

		return false;
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
?>