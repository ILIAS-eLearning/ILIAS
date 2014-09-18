<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
 * Class ilObjBibliographicAccess
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Gabriel Comte <gc@studer-raimann.ch>
 *
 */
class ilObjBibliographicAccess extends ilObjectAccess {

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
	public function _getCommands() {
		$commands = array
		(
			array( "permission" => "read", "cmd" => "render", "lang_var" => "show", "default" => true ),
			array( "permission" => "write", "cmd" => "view", "lang_var" => "edit_content" ),
			array( "permission" => "write", "cmd" => "edit", "lang_var" => "settings" )
		);

		return $commands;
	}


	/**
	 * check whether goto script will succeed
	 */
	public function _checkGoto($a_target) {
		global $ilAccess, $lng;
		$t_arr = explode("_", $a_target);
		if ($t_arr[0] != "bibl" || ((int)$t_arr[1]) <= 0) {
			return false;
		}
		if ($ilAccess->checkAccess("visible", "", $t_arr[1])) {
			return true;
		}
		$object_title = ilObject::_lookupTitle(ilObject::_lookupObjId($t_arr[1]));
		ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"), $object_title), true);

		return false;
	}


	/**
	 * checks wether a user may invoke a command or not
	 * (this method is called by ilAccessHandler::checkAccess)
	 *
	 * @param    string  $a_cmd        command (not permission!)
	 * @param    string  $a_permission permission
	 * @param    int     $a_ref_id     reference id
	 * @param    int     $a_obj_id     object id
	 * @param int|string $a_user_id    user id (if not provided, current user is taken)
	 *
	 * @return    boolean        true, if everything is ok
	 */
	public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "") {
		global $ilUser, $lng, $rbacsystem, $ilAccess;
		if ($a_user_id == "") {
			$a_user_id = $ilUser->getId();
		}
		switch ($a_cmd) {
			case "view":
				if (! self::_lookupOnline($a_obj_id)
					&& ! $rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)
				) {
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));

					return false;
				}
				break;
			// for permission query feature
			case "infoScreen":
				if (! self::_lookupOnline($a_obj_id)) {
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
				} else {
					$ilAccess->addInfoItem(IL_STATUS_MESSAGE, $lng->txt("online"));
				}
				break;
		}
		switch ($a_permission) {
			case "read":
			case "visible":
				if (! self::_lookupOnline($a_obj_id)
					&& (! $rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id))
				) {
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));

					return false;
				}
				break;
		}

		return true;
	}


	/**
	 * Check wether bibliographic is online or not
	 *
	 * @param    int $a_id bibl id
	 */
	public static function _lookupOnline($a_id) {
		global $ilDB;
		$q = "SELECT is_online FROM il_bibl_data WHERE id = " .
			$ilDB->quote($a_id, "integer");
		$bibl_set = $ilDB->query($q);
		$bibl_rec = $ilDB->fetchAssoc($bibl_set);

		return $bibl_rec["is_online"];
	}
}

?>
