<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Access handler for personal workspace
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
 */
class ilWorkspaceAccessHandler
{
	/**
	 * check access for an object
	 *
	 * @param	string		$a_permission
	 * @param	string		$a_cmd
	 * @param	int			$a_node_id
	 * @param	string		$a_type (optional)
	 * @return	bool
	 */
	public function checkAccess($a_permission, $a_cmd, $a_node_id, $a_type = "")
	{
		global $ilUser;

		return $this->checkAccessOfUser($ilUser->getId(),$a_permission, $a_cmd, $a_node_id, $a_type);
	}

	/**
	 * check access for an object
	 *
	 * @param	integer		$a_user_id
	 * @param	string		$a_permission
	 * @param	string		$a_cmd
	 * @param	int			$a_node_id
	 * @param	string		$a_type (optional)
	 * @return	bool
	 */
	public function checkAccessOfUser($a_user_id,$a_permission, $a_cmd, $a_node_id, $a_type = "")
	{
		// tree root is read-only
		if($a_permission == "write")
		{
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			$tree = new ilWorkspaceTree($a_user_id);
			if($tree->readRootId() == $a_node_id)
			{
				return false;
			}
		}

		return true;
	}

	public function setPermissions($a_parent_node_id, $a_node_id)
	{
		
	}
}

?>