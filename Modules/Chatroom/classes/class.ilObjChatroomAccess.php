<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectAccess.php';
require_once 'Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php';

/**
 * Access class for chatroom objects.
 * @author  Jan Posselt <jposselt at databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilObjChatroomAccess extends ilObjectAccess implements ilWACCheckingClass
{
	/**
	 * @var null|bool
	 */
	private static $chat_enabled = null;

	/**
	 * {@inheritdoc}
	 */
	public static function _getCommands()
	{
		$commands   = array();
		$commands[] = array("permission" => "read", "cmd" => "view", "lang_var" => "enter", "default" => true);
		$commands[] = array("permission" => "write", "cmd" => "settings-general", "lang_var" => "settings");

		// alex 3 Oct 2012: this leads to a blank screen, i guess it is a copy/paste bug from files
		//$commands[] = array("permission" => "write", "cmd" => "versions", "lang_var" => "versions");

		return $commands;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function _checkGoto($a_target)
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;

		if(is_string($a_target))
		{
			$t_arr = explode("_", $a_target);

			if(count($t_arr) < 2 || $t_arr[0] != "chtr" || ((int)$t_arr[1]) <= 0)
			{
				return false;
			}

			if($rbacsystem->checkAccess("read", $t_arr[1]))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		/**
		 * @var $ilUser     ilObjUser
		 * @var $rbacsystem ilRbacSystem
		 */
		global $ilUser, $rbacsystem;

		if(self::$chat_enabled === null)
		{
			$chatSetting        = new ilSetting('chatroom');
			self::$chat_enabled = (boolean)$chatSetting->get('chat_enabled');
		}

		if($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		if($rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id))
		{
			return true;
		}

		return self::$chat_enabled;
	}

	/**
	 * @inheritdoc
	 */
	public function canBeDelivered(ilWACPath $ilWACPath)
	{
		if(preg_match("/chatroom\\/smilies\\//ui", $ilWACPath->getPath()))
		{
			return true;
		}

		return false;
	}
}