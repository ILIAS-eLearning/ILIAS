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
* class ilobjcourse
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/

class ilCourseItems
{
	var $course_obj;
	var $ilErr;
	var $ilDB;
	var $tree;
	var $lng;

	var $items;
	var $parent;		// ID OF PARENT CONTAINER e.g course_id, folder_id, group_id

	var $activation_unlimited;
	var $activation_start;
	var $activation_end;

	function ilCourseItems(&$course_obj,$a_parent = 0)
	{
		global $ilErr,$ilDB,$lng,$tree;

		$this->ilErr =& $ilErr;
		$this->ilDB  =& $ilDB;
		$this->lng   =& $lng;
		$this->tree  =& $tree;

		$this->course_obj =& $course_obj;
		$this->setParentId($a_parent);

		$this->__read();
	}

	function setParentId($a_parent = 0)
	{
		$this->parent = $a_parent ? $a_parent : $this->course_obj->getRefId();

		$this->__read();
		
		return true;
	}
	function getParentId()
	{
		return $this->parent;
	}
	function setActivationUnlimitedStatus($a_value)
	{
		$this->activation_unlimited = $a_value ? 1 : 0;
	}
	function getActivationUnlimitedStatus()
	{
		return (bool) $this->activation_unlimited;
	}
	function setActivationStart($a_start)
	{
		$this->activation_start = $a_start;
	}
	function getActivationStart()
	{
		return $this->activation_start;
	}
	function setActivationEnd($a_end)
	{
		$this->activation_end = $a_end;
	}
	function getActivationEnd()
	{
		return $this->activation_end;
	}
	function getAllItems()
	{
		return $this->items ? $this->items : array();
	}
	function getItems()
	{
		foreach($this->items as $item)
		{
			if($item["type"] != "rolf" and
			   ($item["activation_unlimited"] or
				($item["activation_start"] <= time() and $item["activation_end"] >= time())))
			{ 
				$filtered[] = $item;
			}
		}
		return $filtered ? $filtered : array();
	}
	function getItem($a_item_id)
	{
		foreach($this->items as $item)
		{
			if($item["child"] == $a_item_id)
			{
				return $item;
			}
		}
		return array();
	}

	function validateActivation()
	{
		$this->course_obj->setMessage('');

		if(!$this->getActivationUnlimitedStatus())
		{
			if($this->getActivationStart() > $this->getActivationEnd())
			{
				$this->course_obj->appendMessage($this->lng->txt("crs_activation_start_invalid"));
			}
		}

		if($this->course_obj->getMessage())
		{
			return false;
		}
		return true;
	}

	function update($a_item_id)
	{
		$query = "UPDATE crs_items SET ".
			"activation_unlimited = '".(int) $this->getActivationUnlimitedStatus()."', ".
			"activation_start = '".(int) $this->getActivationStart()."', ".
			"activation_end = '".(int) $this->getActivationEnd()."' ".
			"WHERE parent_id = '".$this->getParentId()."' ".
			"AND obj_id = '".$a_item_id."'";

		$res = $this->ilDB->query($query);

		$this->__read();

		return true;
	}


	function moveUp($item_id)
	{
		$this->__updateTop($item_id);
		$this->__read();
		
		return true;
	}
	function moveDown($item_id)
	{
		$this->__updateBottom($item_id);
		$this->__read();

		return true;
	}

	function deleteAllEntries()
	{
		$all_items = $this->tree->getChilds($this->parent);

		foreach($all_items as $item)
		{
			$query = "DELETE FROM crs_items ".
				"WHERE parent_id = '".$item["child"]."'";

			$this->ilDB->query($query);
		}
		$query = "DELETE FROM crs_items ".
			"WHERE parent_id = '".$this->course_obj->getRefId()."'";
		
		$this->ilDB->query($query);

		return true;
	}

	// PRIVATE
	function __read()
	{
		$this->items = array();
		$all_items = $this->tree->getChilds($this->parent);

		foreach($all_items as $item)
		{
			if($item["type"] != 'rolf')
			{
				$this->items[] = $item;
			}
		}

		for($i = 0;$i < count($this->items); ++$i)
		{
			if($this->items[$i]["type"] == 'rolf')
			{
				unset($this->items[$i]);
				continue;
			}
			$this->items[$i] = $this->__getItemData($this->items[$i]);
		}
		$this->__purgeDeleted();
		$this->__sort();

		return true;
	}

	function __purgeDeleted()
	{
		$all = array();

		$query = "SELECT obj_id FROM crs_items ".
			"WHERE parent_id = '".$this->getParentId()."'";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!$this->tree->isInTree($row->obj_id))
			{
				$this->__delete($row->obj_id);
			}
		}
	}

	function __delete($a_obj_id)
	{
		// READ POSITION
		$query = "SELECT position FROM crs_items ".
			"WHERE obj_id = '".$a_obj_id."' ".
			"AND parent_id = '".$this->getParentId()."'";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$position = $row->position;
		}

		// UPDATE positions
		$query = "UPDATE crs_items SET ".
			"position = CASE ".
			"WHEN position > '".$position."' ".
			"THEN position - 1 ".
			"ELSE position ".
			"END ".
			"WHERE parent_id = '".$this->getParentId()."'";

		$res = $this->ilDB->query($query);

		// DELETE ENTRY
		$query = "DELETE FROM crs_items ".
			"WHERE parent_id = '".$this->getParentId()."' ".
			"AND obj_id = '".$a_obj_id."'";

		$res = $this->ilDB->query($query);

		return true;
	}
			
	function __getItemData($a_item)
	{
		$query = "SELECT * FROM crs_items ".
			"WHERE parent_id = '".$this->getParentId()."' ".
			"AND obj_id = '".$a_item["child"]."'";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$a_item["activation_unlimited"] = $row->activation_unlimited;
			$a_item["activation_start"]		= $row->activation_start;
			$a_item["activation_end"]		= $row->activation_end;
			$a_item["position"]				= $row->position;
		}

		if(!isset($a_item["position"]))
		{
			$a_item = $this->createDefaultEntry($a_item);
		}
		return $a_item;
	}

	function createDefaultEntry($a_item)
	{
		$a_item["activation_unlimited"] = 1;
		$a_item["activation_start"]		= time();
		$a_item["activation_end"]		= time();
		$a_item["position"]				= $this->__getLastPosition() + 1;

		$query = "INSERT INTO crs_items ".
			"VALUES('".$this->getParentId()."','".
			$a_item["child"]."','".
			$a_item["activation_unlimited"]."','".
			$a_item["activation_start"]."','".
			$a_item["activation_end"]."','".
			$a_item["position"]."')";

		$res = $this->ilDB->query($query);

		return $a_item;
	}

	// methods for manual sortation
	function __getLastPosition()
	{
		$query = "SELECT MAX(position) as last_position FROM crs_items ".
			"WHERE parent_id = '".$this->getParentId()."'";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$max = $row->last_position;
		}
		return $max ? $max : 0;
	}

	function __updateTop($item_id)
	{
		$query = "SELECT position,obj_id FROM crs_items ".
			"WHERE obj_id = '".$item_id."' ".
			"AND parent_id = '".$this->getParentId()."'";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$node_a["position"] = $row->position;
			$node_a["obj_id"]	  = $row->obj_id;
		}

		$query = "SELECT position, obj_id FROM crs_items ".
			"WHERE position < '".$node_a["position"]."' ".
			"AND parent_id = '".$this->getParentId()."' ".
			"ORDER BY position DESC";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$node_b["position"] = $row->position;
			$node_b["obj_id"]	  = $row->obj_id;
			break;
		}
		$this->__switchNodes($node_a,$node_b);
		
	}

	function __updateBottom($item_id)
	{
		$query = "SELECT position,obj_id FROM crs_items ".
			"WHERE obj_id = '".$item_id."' ".
			"AND parent_id = '".$this->getParentId()."'";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$node_a["position"] = $row->position;
			$node_a["obj_id"]	  = $row->obj_id;
			break;
		}
		$query = "SELECT position ,obj_id FROM crs_items ".
			"WHERE position > '".$node_a["position"]."' ".
			"AND parent_id = '".$this->getParentId()."' ".
			"ORDER BY position ASC";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$node_b["position"] = $row->position;
			$node_b["obj_id"]	  = $row->obj_id;
			break;
		}
		$this->__switchNodes($node_a,$node_b);

		return true;
	}

	function __switchNodes($node_a,$node_b)
	{
		if(!$node_b["obj_id"])
		{
			return false;
		}

		$query = "UPDATE crs_items SET ".
			"position = '".$node_a["position"]."' ".
			"WHERE obj_id = '".$node_b["obj_id"]."' ".
			"AND parent_id = '".$this->getParentId()."'";

		$res = $this->ilDB->query($query);

		$query = "UPDATE crs_items SET ".
			"position = '".$node_b["position"]."' ".
			"WHERE obj_id = '".$node_a["obj_id"]."' ".
			"AND parent_id = '".$this->getParentId()."'";

		$res = $this->ilDB->query($query);

		return true;
	}


	function __sort()
	{
		switch($this->course_obj->getOrderType())
		{
			case $this->course_obj->SORT_MANUAL:
				$this->items = ilUtil::sortArray($this->items,"position","asc",true);
				break;

			case $this->course_obj->SORT_TITLE:
				$this->items = ilUtil::sortArray($this->items,"title","asc");
				break;

			case $this->course_obj->SORT_ACTIVATION:
				$this->items = ilUtil::sortArray($this->items,"activation_end","asc");
				break;
		}
		return true;
	}
	// STATIC
	function _isActivated($a_item_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_items ".
			"WHERE obj_id = '".$a_item_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($row->activation_unlimited)
			{
				return true;
			}
			if(time() > $row->activation_start and time() < $row->activation_end)
			{
				return true;
			}
		}
		return false;
	}
		

}
?>