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

require_once("Services/AccessControl/classes/class.ilAccessInfo.php");

/**
* Class ilAccessHandler
*
* Checks access for ILIAS objects
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package AccessControl
*/
class ilAccessHandler
{
	/**
	* constructor
	*/
	function ilAccessHandler()
	{
		global $rbacsystem;

		$this->rbacsystem =& $rbacsystem;
		$this->results = array();
	}

	/**
	* store access result
	*
	* @access	private
	* @param	string		$a_cmd					command string
	* @param	int			$a_ref_id				reference id
	* @param	boolean		$a_access_granted		true if access is granted
	* @param	object		$a_info_obj				info object
	* @param	int			$a_user_id				user id (if no id passed, current user id)
	*/
	function storeAccessResult($a_cmd, $a_ref_id, $a_access_granted, &$a_info_obj, $a_user_id = "")
	{
		global $ilUser;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		$this->results[$a_ref_id][$a_cmd][$a_user_id] =
			array("granted" => $a_access_granted, "info" => $a_info_obj);
	}


	/**
	* get stored access result
	*
	* @access	private
	* @param	string		$a_cmd					command string
	* @param	int			$a_ref_id				reference id
	* @param	int			$a_user_id				user id (if no id passed, current user id)
	* @return	array		result array:
	*						"granted" (boolean) => true if access is granted
	*						"info" (object) 	=> info object
	*/
	function getStoredAccessResult($a_cmd, $a_ref_id, $a_user_id = "")
	{
		global $ilUser;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		return $this->results[$a_ref_id][$a_cmd][$a_user_id];
	}

	/**
	* pass $a_type and $a_obj_id if available for better performance
	*/
	function checkAccess($a_cmd, $a_ref_id, $a_type = "", $a_obj_id = "")
	{
		global $tree, $objDefinition, $lng;

		if ($a_type == "")
		{
			$a_type = ilObject::_lookupType($a_ref_id, true);
		}

		// get cache result
		$stored_access = $this->getStoredAccessResult($a_cmd, $a_ref_id);
		if (is_array($stored_access))
		{
			$this->last_info = $stored_access["info"];
			return $stored_access["granted"];
		}

		// to do: payment handling

		// rbac check for current object
		if (!$this->rbacsystem->checkAccess($a_cmd, $a_ref_id))
		{
			$this->last_info = new ilAccessInfo();
			$this->last_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("no_permission"));
			$this->storeAccessResult($a_cmd, $a_ref_id, false, $this->last_info);
			return false;
		}

		// check read permission for all parents
		$path = $tree->getPathFull($a_ref_id);
		foreach ($path as $node)
		{
			if ($a_ref_id == $node["child"])
			{
				continue;
			}
			if (!$this->checkAccess("read", $node["child"]))
			{
				$this->last_info = new ilAccessInfo();
				$this->last_info->addInfoItem(IL_NO_PARENT_ACCESS, $lng->txt("no_parent_access"));
				$this->storeAccessResult($a_cmd, $a_ref_id, false, $this->last_info);
				return false;
			}
		}

		// condition check (currently only implemented for read access)
		if ($a_obj_id == "")
		{
			$obj_id = ilObject::_lookupObjId($a_ref_id);
		}
		if ($a_cmd == "read")
		{
			if(!ilConditionHandler::_checkAllConditionsOfTarget($obj_id))
			{
				$this->last_info = new ilAccessInfo();
				$conditions = ilConditionHandler::_getConditionsOfTarget($obj_id, $a_type);
				foreach ($conditions as $condition)
				{
					$this->last_info->addInfoItem(IL_MISSING_PRECONDITION,
						$lng->txt("missing_precondition").": ".
						ilObject::_lookupTitle($condition["trigger_obj_id"])." ".
						$lng->txt("condition_".$condition["operator"])." ".
						$condition["value"], $condition);
				}
				$this->storeAccessResult($a_cmd, $a_ref_id, false, $this->last_info);
				return false;
			}
		}

		// object type specific check
		$class = $objDefinition->getClassName($a_type);
		$location = $objDefinition->getLocation($a_type);
		$full_class = "ilObj".$class;
		include_once($location."/class.".$full_class.".php");
		// static call to ilObj..::_checkAccess($a_cmd, $a_ref_id, $a_obj_id)
		$obj_access = call_user_func(array($full_class, "_checkAccess"),
			$a_cmd, $a_ref_id, $a_obj_id);
		if ($obj_access != true)
		{
			$this->last_info->addInfoItem(IL_NO_OBJECT_ACCESS,
				$obj_acess);
			$this->storeAccessResult($a_cmd, $a_ref_id, false, $this->last_info);
			return false;
		}

		unset($this->last_info);
		$this->storeAccessResult($a_cmd, $a_ref_id, true, $this->last_info);
		return true;
	}

	/**
	* get last info object
	*/
	function getInfo()
	{
		return $this->last_info;
	}
}
