<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("classes/class.ilObjectAccess.php");
include_once './Modules/Course/classes/class.ilCourseConstants.php';
include_once 'Modules/Course/classes/class.ilCourseParticipants.php';

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
		
		$participants = ilCourseParticipants::_getInstanceByObjId($a_obj_id);

		switch ($a_cmd)
		{
			case "view":
				if($participants->isBlocked($a_user_id) and $participants->isAssigned($a_user_id))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("crs_status_blocked"));
					return false;
				} 
				break;

			case 'join':
				
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
				
			case 'leave':

				// Regular member
				if($a_permission == 'leave')
				{
					include_once './Modules/Course/classes/class.ilCourseParticipants.php';
					if(!$participants->isAssigned($a_user_id) or $participants->isLastAdmin($a_user_id))
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

		// BEGIN WebDAV: Mount as webfolder.
		require_once ('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
		if (ilDAVActivationChecker::_isActive())
		{
			$commands[] = array("permission" => "read", "cmd" => "mount_webfolder", "lang_var" => "mount_webfolder", "enable_anonymous" => "false");
		}
		// END WebDAV: Mount as webfolder.
		$commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "edit");
		return $commands;
	}
	
	/**
	* check whether goto script will succeed
	*/
	function _checkGoto($a_target)
	{
		global $ilAccess;
		
		$t_arr = explode("_", $a_target);

		if ($t_arr[0] != "crs" || ((int) $t_arr[1]) <= 0)
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
}

?>
