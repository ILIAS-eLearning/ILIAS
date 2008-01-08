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
* class for checking external links in page objects. All user who want to get messages about invalid links of a page_object 
* are stored here 
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*/
class ilLinkCheckNotify
{
	var $db = null;


	function ilLinkCheckNotify(&$db)
	{
		$this->db =& $db;
	}
	
	function setUserId($a_usr_id)
	{
		$this->usr_id = $a_usr_id;
	}
	function getUserId()
	{
		return $this->usr_id;
	}
	function setObjId($a_obj_id)
	{
		$this->obj_id = $a_obj_id;
	}
	function getObjId()
	{
		return $this->obj_id;
	}

	function addNotifier()
	{
		global $ilDB;
		
		$this->deleteNotifier();

		$query = "INSERT INTO link_check_report ".
			"SET obj_id = ".$ilDB->quote($this->getObjId()).", ".
			"usr_id = ".$ilDB->quote($this->getUserId())."";

		$this->db->query($query);

		return true;
	}

	function deleteNotifier()
	{
		global $ilDB;

		$query = "DELETE FROM link_check_report ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId())." ".
			"AND usr_id = ".$ilDB->quote($this->getUserId())." ";

		$this->db->query($query);

		return true;
	}

	/* Static */
	function _getNotifyStatus($a_usr_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM link_check_report ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ";

		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}

	function _deleteUser($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM link_check_report ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ";

		$ilDB->query($query);

		return true;
	}

	function _deleteObject($a_obj_id)
	{
		global $ilDB;

		$query = "DELETE FROM link_check_report ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";

		$ilDB->query($query);

		return true;
	}

	function _getNotifiers($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM link_check_report ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[] = $row->usr_id;
		}

		return $usr_ids ? $usr_ids : array();
	}

	function _getAllNotifiers(&$db)
	{
		global $ilDB;

		$query = "SELECT * FROM link_check_report ";

		$res = $db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[$row->usr_id][] = $row->obj_id;
		}

		return $usr_ids ? $usr_ids : array();
	}			
}
?>