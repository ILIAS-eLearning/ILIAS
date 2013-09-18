<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
 * Access class for chatroom objects.
 *
 * @author  Jan Posselt <jposselt at databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilObjChatroomAccess extends ilObjectAccess
{

	/**
	 * This method returns an array of all possible commands/permission combinations
	 *
	 * Example:
	 * $commands = array
	 * 	(
	 * 		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
	 * 		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
	 * 	);
	 *
	 * @return string
	 */
	public function _getCommands()
	{
		$commands	= array();
		$commands[] = array("permission" => "read", "cmd" => "view", "lang_var" => "enter", "default" => true);
		$commands[] = array("permission" => "write", "cmd" => "settings-general", "lang_var" => "settings");
		
		// alex 3 Oct 2012: this leads to a blank screen, i guess it is a copy/paste bug from files
		//$commands[] = array("permission" => "write", "cmd" => "versions", "lang_var" => "versions");

		return $commands;
	}

	/**
	 * Check whether goto script will succeed.
	 *
	 * @param string $a_target
	 * @return bool
	 * @todo: $a_target muss eig. immer ein string sein, da sonst das explode
	 * nicht funktionieren würde. Also (string $a_target) 
	 */
	public function _checkGoto($a_target)
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;

		$t_arr = explode("_", $a_target);

		if ($t_arr[0] != "chtr" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		if ($rbacsystem->checkAccess("visible", $t_arr[1]))
		{
			return true;
		}

		return false;
	}

	private static $chat_enabled = null;

	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $rbacsystem;

		if (self::$chat_enabled === null) {
			$chatSetting = new ilSetting('chatroom');
			self::$chat_enabled = (boolean) $chatSetting->get('chat_enabled');
		}

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		if ($rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)) {
			return true;
		}
		return self::$chat_enabled;
	}

}

?>