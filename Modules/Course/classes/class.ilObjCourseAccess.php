<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("classes/class.ilObjectAccess.php");
include_once './Modules/Course/classes/class.ilCourseConstants.php';
include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
include_once 'Modules/Course/classes/class.ilCourseParticipant.php';

/**
* Class ilObjCourseAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilObjCourseAccess extends ilObjectAccess
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
		global $ilUser, $lng, $rbacsystem, $ilAccess, $ilias;


		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}
		
		if($ilUser->getId() == $a_user_id)
		{
			$participants = ilCourseParticipant::_getInstanceByObjId($a_obj_id,$a_user_id);
		}
		else
		{
			$participants = ilCourseParticipants::_getInstanceByObjId($a_obj_id);
		}


		switch ($a_cmd)
		{
			case "view":
				if($participants->isBlocked($a_user_id) and $participants->isAssigned($a_user_id))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("crs_status_blocked"));
					return false;
				} 
				break;

			case 'leave':

				// Regular member
				if($a_permission == 'leave')
				{
					include_once './Modules/Course/classes/class.ilCourseParticipants.php';
					if(!$participants->isAssigned($a_user_id))
					{
						return false;
					}
				}
				// Waiting list
				if($a_permission == 'join')
				{
					include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
					if(!ilCourseWaitingList::_isOnList($ilUser->getId(), $a_obj_id))
					{
						return false;
					}
					return true;
				}
				break;
		}

		switch ($a_permission)
		{
			case "visible":
				$active = ilObjCourseAccess::_isActivated($a_obj_id);
				$registration = ilObjCourseAccess::_registrationEnabled($a_obj_id);
				$tutor = $rbacsystem->checkAccessOfUser($a_user_id,'write',$a_ref_id);
				
				if(!$active)
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
				}
				if(!$tutor and !$active)
				{
					return false;
				}
				break;

			case 'read':
				$tutor = $rbacsystem->checkAccessOfUser($a_user_id,'write',$a_ref_id);
				if($tutor)
				{
					return true;
				}				
				$active = ilObjCourseAccess::_isActivated($a_obj_id);

				if(!$active)
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
					return false;
				}
				
				if($participants->isBlocked($a_user_id) and $participants->isAssigned($a_user_id))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("crs_status_blocked"));
					return false;
				} 
				break;
				
			case 'join':
				
				if(!self::_registrationEnabled($a_obj_id))
				{
					return false;
				}
				
				include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
				if(ilCourseWaitingList::_isOnList($ilUser->getId(), $a_obj_id))
				{
					return false;
				}

				if($participants->isAssigned($a_user_id))
				{
					return false;
				}
				break;
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
		$commands[] = array("permission" => "read", "cmd" => "", "lang_var" => "view", "default" => true);
		$commands[] = array("permission" => "join", "cmd" => "join", "lang_var" => "join");

		// on waiting list
		$commands[]	= array('permission' => "join", "cmd" => "leave", "lang_var" => "leave_waiting_list");
		
		// regualar users
		$commands[]	= array('permission' => "leave", "cmd" => "leave", "lang_var" => "crs_unsubscribe");

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
		
		$t_arr = explode("_", $a_target);
		
		// registration codes
		if(substr($t_arr[2],0,5) == 'rcode' and $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			return true;
		}
		

		if ($t_arr[0] != "crs" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		// checking for read results in endless loop, if read is given
		// but visible is not given (-> see bug 5323)
		//if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
		//	$ilAccess->checkAccess("visible", "", $t_arr[1]))
		if ($ilAccess->checkAccess("visible", "", $t_arr[1]))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Lookup view mode. This is placed here to the need that ilObjFolder must
	 * always instantiate a Course object.
	 * @return 
	 * @param object $a_id
	 */
	function _lookupViewMode($a_id)
	{
		global $ilDB;

		$query = "SELECT view_mode FROM crs_settings WHERE obj_id = ".$ilDB->quote($a_id ,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->view_mode;
		}
		return false;
	}

	/**
	 * Is activated?
	 *
	 * @param int id of user
	 * @return boolean
	 */
	public static function _isActivated($a_obj_id)
	{
		global $ilDB;
		
		include_once './Services/Container/classes/class.ilMemberViewSettings.php';
		if(ilMemberViewSettings::getInstance()->isActive())
		{
			return true;
		}

		$query = "SELECT * FROM crs_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";

		$res = $ilDB->query($query);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$type = $row->activation_type;
			$start = $row->activation_start;
			$end = $row->activation_end;
		}
		switch($type)
		{
			case IL_CRS_ACTIVATION_OFFLINE:
				return false;

			case IL_CRS_ACTIVATION_UNLIMITED:
				return true;

			case IL_CRS_ACTIVATION_LIMITED:
				if(time() < $start or
				   time() > $end)
				{
					return false;
				}
				return true;
				
			default:
				return false;
		}
	}

	/**
	 * 
	 * @return 
	 * @param object $a_obj_id
	 */
	public static function _registrationEnabled($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$type = $row->sub_limitation_type;
			$reg_start = $row->sub_start;
			$reg_end = $row->sub_end;
		}

		switch($type)
		{
			case IL_CRS_SUBSCRIPTION_UNLIMITED:
				return true;

			case IL_CRS_SUBSCRIPTION_DEACTIVATED:
				return false;

			case IL_CRS_SUBSCRIPTION_LIMITED:
				if(time() > $reg_start and
				   time() < $reg_end)
				{
					return true;
				}
			default:
				return false;
		}
		return false;
	}

	/**
	 * Type-specific implementation of general status
	 *
	 * Used in ListGUI and Learning Progress
	 *
	 * @param int $a_obj_id
	 * @return bool
	 */
	static function _isOffline($a_obj_id)
	{
		return !self::_isActivated($a_obj_id);
	}
	
	/**
	 * Preload data
	 *
	 * @param array $a_obj_ids array of object ids
	 */
	function _preloadData($a_obj_ids, $a_ref_ids)
	{
		global $ilDB, $ilUser;
		
		include_once("./Modules/Course/classes/class.ilCourseWaitingList.php");
		ilCourseWaitingList::_preloadOnListInfo($ilUser->getId(), $a_obj_ids);
	}

}

?>
