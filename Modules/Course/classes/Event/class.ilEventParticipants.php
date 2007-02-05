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
* class ilEventMembers
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
*/

class ilEventParticipants
{
	var $ilErr;
	var $ilDB;
	var $tree;
	var $lng;

	var $event_id = null;

	function ilEventParticipants($a_event_id)
	{
		global $ilErr,$ilDB,$lng,$tree;

		$this->ilErr =& $ilErr;
		$this->db  =& $ilDB;
		$this->lng =& $lng;

		$this->event_id = $a_event_id;
		$this->__read();
	}

	function setUserId($a_usr_id)
	{
		$this->user_id = $a_usr_id;
	}
	function getUserId()
	{
		return $this->user_id;
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
	function setParticipated($a_status)
	{
		$this->participated = $a_status;
	}
	function getParticipated()
	{
		return $this->participated;
	}
	function setRegistered($a_status)
	{
		$this->registered = $a_status;
	}
	function getRegistered()
	{
		return $this->registered;
	}
	function updateUser()
	{
		global $ilDB;
		
		$query = "DELETE FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($this->getEventId())." ".
			"AND usr_id = ".$ilDB->quote($this->getUserId())." ";
		$this->db->query($query);

		$query = "INSERT INTO event_participants ".
			"SET event_id = ".$ilDB->quote($this->getEventId()).", ".
			"usr_id = ".$ilDB->quote($this->getUserId()).", ".
			"registered = ".$ilDB->quote($this->getRegistered()).", ".
			"participated = ".$ilDB->quote($this->getParticipated()).", ".
			"mark = ".$ilDB->quote($this->getMark()).", ".
			"comment = ".$ilDB->quote($this->getComment())."";
		$this->db->query($query);
		return true;
	}

	function getUser($a_usr_id)
	{
		return $this->participants[$a_usr_id] ? $this->participants[$a_usr_id] : array();
	}

	function getParticipants()
	{
		return $this->participants ? $this->participants : array();
	}

	function isRegistered($a_usr_id)
	{
		return $this->participants[$a_usr_id]['registered'] ? true : false;
	}

	function hasParticipated($a_usr_id)
	{
		return $this->participants[$a_usr_id]['participated'] ? true : false;
	}

	function updateParticipation($a_usr_id,$a_status)
	{
		ilEventParticipants::_updateParticipation($a_usr_id,$this->getEventId(),$a_status);
	}

	function _updateParticipation($a_usr_id,$a_event_id,$a_status)
	{
		global $ilDB;

		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($a_event_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE event_participants ".
				"SET participated = ".$ilDB->quote($a_status)." ".
				"WHERE event_id = ".$ilDB->quote($a_event_id)." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
			$ilDB->query($query);
		}
		else
		{
			$query = "INSERT INTO event_participants ".
				"SET registered = '0', ".
				"participated = ".$ilDB->quote($a_status).", ".
				"event_id = ".$ilDB->quote($a_event_id).", ".
				"usr_id = ".$ilDB->quote($a_usr_id)." ";
			$ilDB->query($query);
		}
		return true;
	}

	function _getRegistered($a_event_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($a_event_id)." ".
			"AND registered = '1'";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->usr_id;
		}
		return $user_ids ? $user_ids : array();
	}

	function _getParticipated($a_event_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($a_event_id)." ".
			"AND participated = '1'";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->usr_id;
		}
		return $user_ids ? $user_ids : array();
	}

	function _isRegistered($a_usr_id,$a_event_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($a_event_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return (bool) $row->registered;
		}
		return false;
	}

	function _register($a_usr_id,$a_event_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($a_event_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE event_participants ".
				"SET registered = '1' ".
				"WHERE event_id = ".$ilDB->quote($a_event_id)." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
			$ilDB->query($query);
		}
		else
		{
			$query = "INSERT INTO event_participants ".
				"SET registered = '1', ".
				"participated = '0', ".
				"event_id = ".$ilDB->quote($a_event_id).", ".
				"usr_id = ".$ilDB->quote($a_usr_id)." ";
			$ilDB->query($query);
		}
		return true;
	}
	function register($a_usr_id)
	{
		return ilEventParticipants::_register($a_usr_id,$this->getEventId());
	}
			
	function _unregister($a_usr_id,$a_event_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($a_event_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE event_participants ".
				"SET registered = '0' ".
				"WHERE event_id = ".$ilDB->quote($a_event_id)." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
			$ilDB->query($query);
		}
		else
		{
			$query = "INSERT INTO event_participants ".
				"SET registered = '0', ".
				"participated = '0' ".
				"event_id = ".$ilDB->quote($a_event_id).", ".
				"usr_id = ".$ilDB->quote($a_usr_id)." ";
			$ilDB->query($query);
		}
		return true;
	}
	function unregister($a_usr_id)
	{
		return ilEventParticipants::_unregister($a_usr_id,$this->getEventId());
	}

	function _lookupMark($a_event_id,$a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($a_event_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->mark;
		}
		return '';
	}
	
	function _lookupComment($a_event_id,$a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($a_event_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->comment;
		}
		return '';
	}


	function getEventId()
	{
		return $this->event_id;
	}
	function setEventId($a_event_id)
	{
		$this->event_id = $a_event_id;
	}

	function _deleteByEvent($a_event_id)
	{
		global $ilDB;

		$query = "DELETE FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($a_event_id)." ";
		$ilDB->query($query);
		return true;
	}
	function _deleteByUser($a_usr_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM event_participants ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ";
		$ilDB->query($query);
		return true;
	}


	// Private
	function __read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($this->getEventId())." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->participants[$row->usr_id]['usr_id'] = $row->usr_id;
			$this->participants[$row->usr_id]['registered'] = $row->registered;
			$this->participants[$row->usr_id]['participated'] = $row->participated;
			$this->participants[$row->usr_id]['mark'] = $row->mark;
			$this->participants[$row->usr_id]['comment'] = $row->comment;
		}
	}
}
?>