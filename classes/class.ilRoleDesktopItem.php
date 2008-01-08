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


/**
* Class ilObjRoleGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
*
*
* 
* @extends ilObjectGUI
*/


class ilRoleDesktopItem
{
	var $db;
	var $role_id;
 
	/**
	* Constructor
	* @access public
	*/
	function ilRoleDesktopItem($a_role_id)
	{
		global $ilDB;

		$this->db =& $ilDB;
		$this->role_id = $a_role_id;
	}

	function getRoleId()
	{
		return $this->role_id;
	}
	function setRoleId($a_role_id)
	{
		$this->role_id = $a_role_id;
	}

	function add($a_item_id,$a_item_type)
	{
		global $ilDB;
		
		if($a_item_type and $a_item_id)
		{
			$query = "INSERT INTO role_desktop_items ".
				"SET role_id = ".$ilDB->quote($this->getRoleId()).", ".
				"item_id = ".$ilDB->quote($a_item_id).", ".
				"item_type = ".$ilDB->quote($a_item_type);

			$this->db->query($query);

			$this->__assign($a_item_id,$a_item_type);
			
			return true;
		}
		return false;
	}
	function delete($a_role_item_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM role_desktop_items ".
			"WHERE role_item_id = ".$ilDB->quote($a_role_item_id);

		$this->db->query($query);

		return true;
	}

	function deleteAll()
	{
		global $ilDB;
		
		$query = "DELETE FROM role_desktop_items ".
			"WHERE role_id = ".$ilDB->quote($this->getRoleId());

		$this->db->query($query);

		return true;
	}

	function isAssigned($a_item_ref_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM role_desktop_items ".
			"WHERE role_id = ".$ilDB->quote($this->getRoleId())." ".
			"AND item_id = ".$ilDB->quote($a_item_ref_id)." ";

		$res = $this->db->query($query);

		return $res->numRows() ? true : false;
	}

	function getItem($a_role_item_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM role_desktop_items ".
			"WHERE role_id = ".$ilDB->quote($this->getRoleId())." ".
			"AND role_item_id = ".$ilDB->quote($a_role_item_id)." ";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$item['item_id'] = $row->item_id;
			$item['item_type'] = $row->item_type;
		}

		return $item ? $item : array();
	}



	function getAll()
	{
		global $tree;

		$query = "SELECT * FROM role_desktop_items ".
			"WHERE role_id = ".$this->db->quote($this->getRoleId())." ";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			// TODO this check must be modified for non tree objects
			if(!$tree->isInTree($row->item_id))
			{
				$this->delete($row->role_item_id);
				continue;
			}
			$items[$row->role_item_id]['item_id'] = $row->item_id;
			$items[$row->role_item_id]['item_type'] = $row->item_type;
		}

		return $items ? $items : array();
	}

	// PRIVATE
	function __assign($a_item_id,$a_item_type)
	{
		global $rbacreview;

		foreach($rbacreview->assignedUsers($this->getRoleId()) as $user_id)
		{
			if(is_object($tmp_user = ilObjectFactory::getInstanceByObjId($user_id,false)))
			{
				if(!$tmp_user->isDesktopItem($a_item_id,$a_item_type))
				{
					$tmp_user->addDesktopItem($a_item_id,$a_item_type);
				}
			}
		}
		return true;
	}
}
?>