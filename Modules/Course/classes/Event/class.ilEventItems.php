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
* class ilEvent
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
*/


class ilEventItems
{
	var $ilErr;
	var $ilDB;
	var $tree;
	var $lng;

	var $event_id = null;
	var $items = array();


	function ilEventItems($a_event_id)
	{
		global $ilErr,$ilDB,$lng,$tree;

		$this->ilErr =& $ilErr;
		$this->db  =& $ilDB;
		$this->lng =& $lng;

		$this->event_id = $a_event_id;
		$this->__read();
	}

	function getEventId()
	{
		return $this->event_id;
	}
	function setEventId($a_event_id)
	{
		$this->event_id = $a_event_id;
	}
	function getItems()
	{
		return $this->items ? $this->items : array();
	}
	function setItems($a_items)
	{
		$this->items = $a_items;
	}
	function delete()
	{
		return ilEventItems::_delete($this->getEventId());
	}
	function _delete($a_event_id)
	{
		global $ilDB;

		$query = "DELETE FROM event_items ".
			"WHERE event_id = ".$ilDB->quote($a_event_id)." ";
		$ilDB->query($query);
		return true;
	}
	function update()
	{
		global $ilDB;
		
		$this->delete();
		
		foreach($this->items as $item)
		{
			$query = "INSERT INTO event_items ".
				"SET event_id = ".$ilDB->quote($this->getEventId()).", ".
				"item_id = ".$ilDB->quote($item)." ";
			$this->db->query($query);
		}
		return true;
	}
	
	function _getItemsOfContainer($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event AS e ".
			"JOIN event_items AS ei ON e.event_id = ei.event_id ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$items[] = $row->item_id;
		}
		return $items ? $items : array();
	}

	function _isAssigned($a_item_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_items ".
			"WHERE item_id = ".$ilDB->quote($a_item_id)." ";
		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}


	// PRIVATE
	function __read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM event_items ".
			"WHERE event_id = ".$ilDB->quote($this->getEventId())." ";

		$res = $this->db->query($query);
		$this->items = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->items[] = $row->item_id;
		}
		return true;
	}
		
}
?>