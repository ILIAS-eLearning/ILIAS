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
* @extends Object
* @package ilias-core
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

	function getParticipants()
	{
		return $this->participants ? $this->participants : array();
	}

	function _isRegistered($a_usr_id,$a_event_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = '".$a_event_id."' ".
			"AND usr_id = '".$a_usr_id."'";
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
			"WHERE event_id = '".$a_event_id."' ".
			"AND usr_id = '".$a_usr_id."'";
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE event_participants ".
				"SET registered = '1' ".
				"WHERE event_id = '".$a_event_id."' ".
				"AND usr_id = '".$a_usr_id."'";
			$ilDB->query($query);
		}
		else
		{
			$query = "INSERT INTO event_participants ".
				"SET registered = '1', ".
				"participated = '0', ".
				"event_id = '".$a_event_id."', ".
				"usr_id = '".$a_usr_id."'";
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
			"WHERE event_id = '".$a_event_id."' ".
			"AND usr_id = '".$a_usr_id."'";
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE event_participants ".
				"SET registered = '0' ".
				"WHERE event_id = '".$a_event_id."' ".
				"AND usr_id = '".$a_usr_id."'";
			$ilDB->query($query);
		}
		else
		{
			$query = "INSERT INTO event_participants ".
				"SET registered = '0', ".
				"participated = '0' ".
				"event_id = '".$a_event_id."', ".
				"usr_id = '".$a_usr_id."'";
			$ilDB->query($query);
		}
		return true;
	}
	function unregister($a_usr_id)
	{
		return ilEventParticipants::_unregister($a_usr_id,$this->getEventId());
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
			"WHERE event_id = '".$a_event_id."'";
		$ilDB->query($query);
		return true;
	}
	function _deleteByUser($a_usr_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM event_participants ".
			"WHERE usr_id = '".$a_usr_id."'";
		$ilDB->query($query);
		return true;
	}


	// Private
	function __read()
	{
		$query = "SELECT * FROM event_participants ".
			"WHERE event_id = '".$this->getEventId()."'";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->participants[$row->usr_id]['usr_id'] = $row->usr_id;
			$this->participants[$row->usr_id]['registered'] = $row->registered;
			$this->participants[$row->usr_id]['participated'] = $row->participated;
		}
	}
}
?>