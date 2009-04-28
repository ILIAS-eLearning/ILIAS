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
* Class ilLPObjSettings
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/


class ilLPMarks
{
	var $db = null;

	var $obj_id = null;
	var $usr_id = null;
	var $obj_type = null;

	var $completed = false;
	var $comment = '';
	var $mark = '';

	var $has_entry = false;



	function ilLPMarks($a_obj_id,$a_usr_id)
	{
		global $ilObjDataCache,$ilDB;

		$this->db =& $ilDB;

		$this->obj_id = $a_obj_id;
		$this->usr_id = $a_usr_id;
		$this->obj_type = $ilObjDataCache->lookupType($this->obj_id);

		$this->__read();
	}
	
	/**
	 * Delete object
	 *
	 * @static
	 */
	 public static function deleteObject($a_obj_id)
	 {
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM ut_lp_marks ".
	 		"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer');
	 	$res = $ilDB->manipulate($query);
	 	return true;
	 }

	function getUserId()
	{
		return $this->usr_id;
	}

	function setMark($a_mark)
	{
		$this->mark = $a_mark;
	}
	function getMark()
	{
		return $this->mark;
	}
	function setComment($a_comment)
	{
		$this->comment = $a_comment;
	}
	function getComment()
	{
		return $this->comment;
	}
	function setCompleted($a_status)
	{
		$this->completed = (bool) $a_status;
	}
	function getCompleted()
	{
		return $this->completed;
	}

	function getObjId()
	{
		return (int) $this->obj_id;
	}
	
	function update()
	{
		global $ilDB;
		
		if(!$this->has_entry)
		{
			$this->__add();
		}
		$query = "UPDATE ut_lp_marks ".
			"SET mark = ".$ilDB->quote($this->getMark(), 'text').", ".
			"u_comment = ".$ilDB->quote($this->getComment() ,'text').", ".
			"completed = ".$ilDB->quote($this->getCompleted() ,'integer')." ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId() ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($this->getUserId(), 'integer');
		$res = $ilDB->manipulate($query);
		return true;
	}

	// Static
	function _hasCompleted($a_usr_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM ut_lp_marks ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return (bool) $row->completed;
		}
		return false;
	}

	function _lookupMark($a_usr_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM ut_lp_marks ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id, 'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->mark;
		}
		return '';
	}

		
	function _lookupComment($a_usr_id,$a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM ut_lp_marks ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->u_comment;
		}
		return '';
	}

	// Private
	function __read()
	{
		global $ilDB;
		
		$res = $this->db->query("SELECT * FROM ut_lp_marks ".
								"WHERE obj_id = ".$this->db->quote($this->obj_id ,'integer')." ".
								"AND usr_id = ".$ilDB->quote($this->usr_id ,'integer'));
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->has_entry = true;
			$this->completed = (int) $row->completed;
			$this->comment = $row->u_comment;
			$this->mark = $row->mark;

			return true;
		}

		return false;
	}

	function __add()
	{
		global $ilDB;
		
		$query = "INSERT INTO ut_lp_marks (mark,u_comment, completed,obj_id,usr_id) ".
			"VALUES( ".
			$ilDB->quote($this->getMark(),'text').", ".
			$ilDB->quote($this->getComment() ,'text').", ".
			$ilDB->quote($this->getCompleted() ,'integer').", ".
			$ilDB->quote($this->getObjId() ,'integer').", ".
			$ilDB->quote($this->getUserId() ,'integer')." ".
			")";
		$res = $ilDB->manipulate($query);
		$this->has_entry = true;

		return true;
	}
}
?>