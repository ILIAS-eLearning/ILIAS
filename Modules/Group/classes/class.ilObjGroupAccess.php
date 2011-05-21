<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./classes/class.ilObjectAccess.php");

/**
* Class ilObjGroupAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilObjGroupAccess extends ilObjectAccess
{
	/**
	* checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* @param	string		$a_cmd		command (not permission!)
	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id	reference id
	* @param	int			$a_obj_id	object id
	* @param	int			$a_user_id	user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{

		global $ilUser, $lng, $rbacsystem, $ilAccess;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		switch ($a_cmd)
		{
			case "info":
			
				include_once './Modules/Group/classes/class.ilGroupParticipants.php';
				if(ilGroupParticipants::_isParticipant($a_ref_id,$a_user_id))
				{
					$ilAccess->addInfoItem(IL_STATUS_MESSAGE, $lng->txt("info_is_member"));
				}
				else
				{
					$ilAccess->addInfoItem(IL_STATUS_MESSAGE, $lng->txt("info_is_not_member"));
				}						
				break;
				
			case "join":
			
				include_once './Modules/Group/classes/class.ilGroupWaitingList.php';
				if(ilGroupWaitingList::_isOnList($ilUser->getId(), $a_obj_id))
				{
					return false;
				}

				include_once './Modules/Group/classes/class.ilGroupParticipants.php';
				if(ilGroupParticipants::_isParticipant($a_ref_id,$a_user_id))
				{
					return false;
				}
				break;
				
			case 'leave':

				// Regular member
				if($a_permission == 'leave')
				{
					include_once './Modules/Group/classes/class.ilGroupParticipants.php';
					if(!ilGroupParticipants::_isParticipant($a_ref_id, $a_user_id))
					{
						return false;
					}
				}
				// Waiting list
				if($a_permission == 'join')
				{
					include_once './Modules/Group/classes/class.ilGroupWaitingList.php';
					if(!ilGroupWaitingList::_isOnList($ilUser->getId(), $a_obj_id))
					{
						return false;
					}
				}
				break;
				
		}

		switch ($a_permission)
		{

		}
		return true;
	}

	/**
	 * get commands
	 * 
	 * this method returns an array of all possible commands/permission combinations
	 * 
	 * example:	
	 * $commands = array
	 *	(
	 *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
	 *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
	 *	);
	 */
	function _getCommands()
	{
		$commands = array();
		$commands[] = array("permission" => "read", "cmd" => "view", "lang_var" => "show", "default" => true);
		$commands[] = array("permission" => "join", "cmd" => "join", "lang_var" => "join");

		// on waiting list
		$commands[]	= array('permission' => "join", "cmd" => "leave", "lang_var" => "leave_waiting_list");
		
		// regualar users
		$commands[]	= array('permission' => "leave", "cmd" => "leave", "lang_var" => "grp_btn_unsubscribe");
		
		include_once ('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
		if (ilDAVActivationChecker::_isActive())
		{
			include_once './Services/WebDAV/classes/class.ilDAVUtils.php';
			if(ilDAVUtils::getInstance()->isLocalPasswordInstructionRequired())
			{
				$commands[] = array('permission' => 'read', 'cmd' => 'showPasswordInstruction', 'lang_var' => 'mount_webfolder', 'enable_anonymous' => 'false');
			}
			else
			{
				$commands[] = array("permission" => "read", "cmd" => "mount_webfolder", "lang_var" => "mount_webfolder", "enable_anonymous" => "false");
			}
		}

		$commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "edit");
		
		return $commands;
	}
	
	/**
	* check whether goto script will succeed
	*/
	function _checkGoto($a_target)
	{
		global $ilAccess,$ilUser;
		
		// registration codes
		if(substr($t_arr[2],0,5) == 'rcode' and $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			return true;
		}
		
		
		$t_arr = explode("_", $a_target);

		if ($t_arr[0] != "grp" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
			$ilAccess->checkAccess("visible", "", $t_arr[1]))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * 
	 * @return 
	 * @param object $a_obj_id
	 */
	public static function _registrationEnabled($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM grp_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";

		$res = $ilDB->query($query);
		
		$enabled = $unlimited = false;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$enabled = $row->registration_enabled;
			$unlimited = $row->registration_unlimited;
			$start = $row->registration_start;
			$end = $row->registration_end;
		}

		if(!$enabled)
		{
			return false;
		}
		if($unlimited)
		{
			return true;
		}
		
		if(!$unlimited)
		{
			$start = new ilDateTime($start,IL_CAL_DATETIME,'UTC');
			$end = new ilDateTime($end,IL_CAL_DATETIME,'UTC');
			$time = new ilDateTime(time(),IL_CAL_UNIX);
			
			return ilDateTime::_after($time, $start) and ilDateTime::_before($time,$end); 
		}
		return false;
	}
	

	/**
	 * Preload data
	 *
	 * @param array $a_obj_ids array of object ids
	 */
	function _preloadData($a_obj_ids, $a_ref_ids)
	{
		global $ilDB, $ilUser;
		
		include_once("./Modules/Group/classes/class.ilGroupWaitingList.php");
		ilGroupWaitingList::_preloadOnListInfo($ilUser->getId(), $a_obj_ids);
	}

}
?>
