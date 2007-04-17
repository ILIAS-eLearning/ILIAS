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
* Class ilLPEventCollections
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

class ilLPEventCollections
{
	var $db = null;

	var $obj_id = null;
	var $items = array();

	function ilLPEventCollections($a_obj_id)
	{
		global $ilObjDataCache,$ilDB;

		$this->db =& $ilDB;

		$this->obj_id = $a_obj_id;

		$this->__read();
	}

	function getObjId()
	{
		return (int) $this->obj_id;
	}

	function getItems()
	{
		return $this->items;
	}

	function isAssigned($a_obj_id)
	{
		return (bool) in_array($a_obj_id,$this->items);
	}

	function add($item_id)
	{
		if($this->isAssigned($item_id))
		{
			return false;
		}

		$query = "INSERT INTO ut_lp_event_collections ".
			"SET obj_id = '".$this->obj_id."', ".
			"item_id = '".(int) $item_id."'";
		$this->db->query($query);
		
		$this->__read();

		return true;
	}

	function delete($item_id)
	{
		$query = "DELETE FROM ut_lp_event_collections ".
			"WHERE item_id = '".$item_id."' ".
			"AND obj_id = '".$this->obj_id."'";
		$this->db->query($query);

		$this->__read();

		return true;
	}


	function _deleteAll($a_obj_id)
	{
		global $ilDB;

		$query = "DELETE FROM ut_lp_collections ".
			"WHERE obj_id = '".$a_obj_id."'";
		$ilDB->query($query);

		return true;
	}

	function _getItems($a_obj_id)
	{
		include_once 'Modules/Course/classes/Event/class.ilEvent.php';

		global $ilObjDataCache;
		global $ilDB;

		$query = "SELECT * FROM ut_lp_event_collections WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!ilEvent::_exists($row->item_id))
			{
				$query = "DELETE FROM ut_lp_event_collections ".
					"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ".
					"AND item_id = ".$ilDB->quote($row->item_id)."";
				$ilDB->query($query);
				continue;
			}
			$items[] = $row->item_id;
		}
		return $items ? $items : array();
	}

	// Private
	function __read()
	{
		include_once 'Modules/Course/classes/Event/class.ilEvent.php';

		global $ilObjDataCache;

		$query = "SELECT * FROM ut_lp_event_collections WHERE obj_id = ".$this->db->quote($this->obj_id)."";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!ilEvent::_exists($row->item_id))
			{
				$query = "DELETE FROM ut_lp_event_collections ".
					"WHERE obj_id = '".$this->getObjId()."' ".
					"AND item_id = '".$row->item_id."'";
				$this->db->query($query);
				continue;
			}
			$this->items[] = $row->item_id;
		}
		
	}
}
?>