<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
				break;

				include_once './Modules/Group/classes/class.ilGroupParticipants.php';
				if(ilGroupParticipants::_isParticipant($a_ref_id,$a_user_id))
				{
					return false;
				}
				break;
				
			case 'leave':
				include_once './Modules/Group/classes/class.ilGroupWaitingList.php';
				if(!ilGroupWaitingList::_isOnList($ilUser->getId(), $a_obj_id))
				{
					return false;
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
		// only for users on the waiting list
		$commands[]	= array('permission' => "join", "cmd" => "leave", "lang_var" => "leave_waiting_list");
		
		// BEGIN WebDAV: Mount Webfolder.
		require_once ('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
		if (ilDAVActivationChecker::_isActive())
		{
			$commands[] = array("permission" => "read", "cmd" => "mount_webfolder", "lang_var" => "mount_webfolder", "enable_anonymous" => "false");
		}
		// END WebDAV: Mount Webfolder.
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

}
?>
