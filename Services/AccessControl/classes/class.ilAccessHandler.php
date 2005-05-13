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
		$this->current_info = new ilAccessInfo();
	}

	/**
	* store access result
	*
	* @access	private
	* @param	string		$a_permission			permission
	* @param	string		$a_cmd					command string
	* @param	int			$a_ref_id				reference id
	* @param	boolean		$a_access_granted		true if access is granted
	* @param	int			$a_user_id				user id (if no id passed, current user id)
	*/
	function storeAccessResult($a_permission, $a_cmd, $a_ref_id, $a_access_granted, $a_user_id = "")
	{
		global $ilUser;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		$this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id] =
			array("granted" => $a_access_granted, "info" => $this->current_info);

		// get new info object
		$this->current_info = new ilAccessInfo();
	}


	/**
	* get stored access result
	*
	* @access	private
	* @param	string		$a_permission			permission
	* @param	string		$a_cmd					command string
	* @param	int			$a_ref_id				reference id
	* @param	int			$a_user_id				user id (if no id passed, current user id)
	* @return	array		result array:
	*						"granted" (boolean) => true if access is granted
	*						"info" (object) 	=> info object
	*/
	function getStoredAccessResult($a_permission, $a_cmd, $a_ref_id, $a_user_id = "")
	{
		global $ilUser;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		return $this->results[$a_ref_id][$a_permission][$a_cmd][$a_user_id];
	}


	/**
	* add an info item to current info object
	*/
	function addInfoItem($a_type, $a_text, $a_data = "")
	{
		$this->current_info->addInfoItem($a_type, $a_text, $a_data);
	}


	/**
	* check access for an object
	* (provide $a_type and $a_obj_id if available for better performance)
	*
	* @param	string		$a_permission
	* @param	string		$a_cmd
	* @param	int			$a_ref_id
	* @param	string		$a_type (optional)
	* @param	int			$a_obj_id (optional)
	*
	*/
	function checkAccess($a_permission, $a_cmd, $a_ref_id, $a_type = "", $a_obj_id = "")
	{
		global $tree, $objDefinition, $lng, $ilBench;

		$this->current_info->clear();

		if ($a_type == "")
		{
			$a_type = ilObject::_lookupType($a_ref_id, true);
		}

		// get cache result
		$ilBench->start("AccessControl", "1000_checkAccess_get_cache_result");
		$stored_access = $this->getStoredAccessResult($a_permission, $a_cmd, $a_ref_id);
		if (is_array($stored_access))
		{
			$this->last_info = $stored_access["info"];
			$ilBench->stop("AccessControl", "1000_checkAccess_get_cache_result");
			return $stored_access["granted"];
		}
		$ilBench->stop("AccessControl", "1000_checkAccess_get_cache_result");

		// to do: payment handling

		// rbac check for current object
		$ilBench->start("AccessControl", "2000_checkAccess_rbac_check");
		if ($a_permission != "")
		{
			if (!$this->rbacsystem->checkAccess($a_permission, $a_ref_id))
			{
				$this->current_info->addInfoItem(IL_NO_PERMISSION, $lng->txt("no_permission"));
				$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false);
				$ilBench->stop("AccessControl", "2000_checkAccess_rbac_check");
				return false;
			}
		}
		$ilBench->stop("AccessControl", "2000_checkAccess_rbac_check");

		// check read permission for all parents
		$ilBench->start("AccessControl", "3100_checkAccess_check_parents_get_path");
		$path = $tree->getPathId($a_ref_id);
		$ilBench->stop("AccessControl", "3100_checkAccess_check_parents_get_path");
		foreach ($path as $id)
		{
			if ($a_ref_id == $id)
			{
				continue;
			}
			if (!$this->checkAccess("read", "", $id))
			{
				$ilBench->start("AccessControl", "3200_checkAccess_check_parents_store_result");
				$this->current_info->addInfoItem(IL_NO_PARENT_ACCESS, $lng->txt("no_parent_access"));
				$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false);
				$ilBench->stop("AccessControl", "3200_checkAccess_check_parents_store_result");
				return false;
			}
		}

		// condition check (currently only implemented for read permission)
		$ilBench->start("AccessControl", "4000_checkAccess_condition_check");
		if ($a_obj_id == "")
		{
			$a_obj_id = ilObject::_lookupObjId($a_ref_id);
		}
		if ($a_permission == "read")
		{
			if(!ilConditionHandler::_checkAllConditionsOfTarget($obj_id))
			{
				$conditions = ilConditionHandler::_getConditionsOfTarget($obj_id, $a_type);
				foreach ($conditions as $condition)
				{
					$this->current_info->addInfoItem(IL_MISSING_PRECONDITION,
						$lng->txt("missing_precondition").": ".
						ilObject::_lookupTitle($condition["trigger_obj_id"])." ".
						$lng->txt("condition_".$condition["operator"])." ".
						$condition["value"], $condition);
				}
				$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false);
				$ilBench->stop("AccessControl", "4000_checkAccess_condition_check");
				return false;
			}
		}
		$ilBench->stop("AccessControl", "4000_checkAccess_condition_check");

		// object type specific check
		$ilBench->start("AccessControl", "5000_checkAccess_object_check");
		$class = $objDefinition->getClassName($a_type);
		$location = $objDefinition->getLocation($a_type);
		$full_class = "ilObj".$class."Access";
		include_once($location."/class.".$full_class.".php");
		// static call to ilObj..::_checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id)
		$obj_access = call_user_func(array($full_class, "_checkAccess"),
			$a_cmd, $a_permission, $a_ref_id, $a_obj_id);
		if (!($obj_access === true))
		{
			//$this->last_info->addInfoItem(IL_NO_OBJECT_ACCESS, $obj_acess);
			$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, false);
			$ilBench->stop("AccessControl", "5000_checkAccess_object_check");
			return false;
		}
		$ilBench->stop("AccessControl", "5000_checkAccess_object_check");

		$ilBench->start("AccessControl", "6000_checkAccess_store_access");
		unset($this->last_info);
		$this->storeAccessResult($a_permission, $a_cmd, $a_ref_id, true, $this->last_info);
		$ilBench->stop("AccessControl", "6000_checkAccess_store_access");
		return true;
	}

	/**
	* get last info object
	*/
	function getInfo()
	{
		return $this->current_info;
	}
}
