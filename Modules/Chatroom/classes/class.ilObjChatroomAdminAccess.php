<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
 * Class ilObjChatroomAdminAccess
 *
 * Access class for chatroom objects.
 *
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilObjChatroomAdminAccess extends ilObjectAccess
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
		$commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "edit");
		$commands[] = array("permission" => "write", "cmd" => "versions", "lang_var" => "versions");

		return $commands;
	}

	/**
	 * Check whether goto script will succeed.
	 *
	 * @global ilAccessHandler $ilAccess
	 * @param string $a_target
	 * @return bool
	 * @todo: $a_target muss eig. immer ein string sein, da sonst das explode
	 * nicht funktioniert, also typehinten (string $a_target) oder?
	 */
	public function _checkGoto($a_target)
	{
		global $ilAccess;

		$t_arr = explode("_", $a_target);

		if ($t_arr[0] != "chtr" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		if ($ilAccess->checkAccess("visible", "", $t_arr[1]))
		{
			return true;
		}

		return false;
	}

}

?>
