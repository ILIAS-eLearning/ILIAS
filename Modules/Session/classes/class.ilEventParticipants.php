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
* @version $Id: class.ilEventParticipants.php 15697 2008-01-08 20:04:33Z hschottm $
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
			"WHERE event_id = ".$ilDB->quote($this->getEventId() ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($this->getUserId() ,'integer')." ";
		$res = $ilDB->manipulate($query);

		$query = "INSERT INTO event_participants (event_id,usr_id,registered,participated,mark,comment) ".
			"VALUES( ".
			$ilDB->quote($this->getEventId() ,'integer').", ".
			$ilDB->quote($this->getUserId() ,'integer').", ".
			$ilDB->quote($this->getRegistered() ,'integer').", ".
			$ilDB->quote($this->getParticipated() ,'integer').", ".
			$ilDB->quote($this->getMark() ,'text').", ".
			$ilDB->quote($this->getComment() ,'text')." ".
			")";
		$res = $ilDB->manipulate($query);
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
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE event_participants ".
				"SET participated = ".$ilDB->quote($a_status ,'integer')." ".
				"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
			$res = $ilDB->manipulate($query);
		}
		else
		{
			$query = "INSERT INTO event_participants (registered,participated,event_id,usr_id) ".
				"VALUES( ".
				$ilDB->quote(0 ,'integer').", ".
				$ilDB->quote($a_status ,'integer').", ".
				$ilDB->quote($a_event_id ,'integer').", ".
				$ilDB->quote($a_usr_id ,'integer')." ".
				")";
			$res = $ilDB->manipulate($query);
		}
		return true;
	}

	function _getRegistered($a_event_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
			"AND registered = ".$ilDB->quote(1 ,'integer');
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
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
			"AND participated = 1";
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
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
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
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE event_participants ".
				"SET registered = '1' ".
				"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
			$res = $ilDB->manipulate($query);
		}
		else
		{
			$query = "INSERT INTO event_participants (registered,participated,event_id,usr_id) ".
				"VALUES( ".
				"1, ".
				"0, ".
				$ilDB->quote($a_event_id ,'integer').", ".
				$ilDB->quote($a_usr_id ,'integer')." ".
				")";
			$res = $ilDB->manipulate($query);
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
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE event_participants ".
				"SET registered = 0 ".
				"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
			$res = $ilDB->manipulate($query);
		}
		else
		{
			$query = "INSERT INTO event_participants (registered,participated,event_id,usr_id) ".
				"VALUES( ".
				"0, ".
				"0, ".
				$ilDB->quote($a_event_id ,'integer').", ".
				$ilDB->quote($a_usr_id ,'integer')." ".
				")";
			$res = $ilDB->manipulate($query);
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
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
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
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
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
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		return true;
	}
	function _deleteByUser($a_usr_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM event_participants ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
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