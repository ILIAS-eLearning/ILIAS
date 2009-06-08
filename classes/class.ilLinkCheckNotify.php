<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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

		$query = "INSERT INTO link_check_report (obj_id,usr_id) ".
			"VALUES ( ".
			$ilDB->quote($this->getObjId(),'integer').", ".
			$ilDB->quote($this->getUserId(),'integer').
			")";
		$res = $ilDB->manipulate($query);

		return true;
	}

	function deleteNotifier()
	{
		global $ilDB;

		$query = "DELETE FROM link_check_report ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId(),'integer')." ".
			"AND usr_id = ".$ilDB->quote($this->getUserId(),'integer')." ";
		$res = $ilDB->manipulate($query);

		return true;
	}

	/* Static */
	function _getNotifyStatus($a_usr_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM link_check_report ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id,'integer');
		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}

	function _deleteUser($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM link_check_report ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id,'integer');
		$res = $ilDB->manipulate($query);
		return true;
	}

	function _deleteObject($a_obj_id)
	{
		global $ilDB;

		$query = "DELETE FROM link_check_report ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ";
		$res = $ilDB->manipulate($query);
				
		return true;
	}

	function _getNotifiers($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM link_check_report ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ";

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