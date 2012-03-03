<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
* Class ilObjBookingPoolAccess
*
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjCategoryAccess.php 20139 2009-06-08 09:45:39Z akill $
*
* @ingroup ModulesBookingManager
*/
class ilObjBookingPoolAccess extends ilObjectAccess
{

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
		$commands[] = array("permission" => "read", "cmd" => "render", "lang_var" => "show", "default" => true);
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

		if ($t_arr[0] != "book" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		if ($ilAccess->checkAccess("read", "", $t_arr[1]))
		{
			return true;
		}
		return false;
	}

	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $rbacsystem;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		// add no access info item and return false if access is not granted
		// $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $a_text, $a_data = "");
		//
		// for all RBAC checks use checkAccessOfUser instead the normal checkAccess-method:
		// $rbacsystem->checkAccessOfUser($a_user_id, $a_permission, $a_ref_id)

		if($a_permission == "visible" && !$rbacsystem->checkAccessOfUser($a_user_id,'write',$a_ref_id))
		{
			include_once "Modules/BookingManager/classes/class.ilObjBookingPool.php";
			$pool = new ilObjBookingPool($a_ref_id);
			if($pool->isOffline())
			{
				return false;
			}
		}

		return true;
	}
}

?>
