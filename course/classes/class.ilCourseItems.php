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

define('IL_CRS_TIMINGS_ACTIVATION',0);
define('IL_CRS_TIMINGS_DEACTIVATED',1);
define('IL_CRS_TIMINGS_PRESETTING',2);


class ilCourseItems
{
	var $course_obj;
	var $ilErr;
	var $ilDB;
	var $tree;
	var $lng;

	var $items;
	var $parent;		// ID OF PARENT CONTAINER e.g course_id, folder_id, group_id

	var $timing_type;
	var $timing_start;
	var $timing_end;

	var $timing_min;
	var $timing_max;


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

	function setEarliestTime($a_timing)
	{
		$this->timing_min = $a_timing;
	}
	function getEarliestTime()
	{
		return $this->timing_min;
	}
	function setLastTime($a_timing)
	{
		$this->timing_max = $a_timing;
	}
	function getLastTime()
	{
		return $this->timing_max;
	}

	function setTimingType($a_type)
	{
		$this->timing_type = $a_type;
	}
	function getTimingType()
	{
		return $this->timing_type;
	}
	
	function setTimingStart($a_start)
	{
		$this->timing_start = $a_start;
	}
	function getTimingStart()
	{
		return $this->timing_start;
	}
	function setTimingEnd($a_end)
	{
		$this->timing_end = $a_end;
	}
	function getTimingEnd()
	{
		return $this->timing_end;
	}
	function toggleVisible($a_status)
	{
		$this->visible = (int) $a_status;
	}
	function enabledVisible()
	{
		return (bool) $this->visible;
	}
	function toggleChangeable($a_status)
	{
		$this->changeable = (int) $a_status;
	}
	function enabledChangeable()
	{
		return (bool) $this->changeable;
	}

	function getAllItems()
	{
		return $this->items ? $this->items : array();
	}
	function getItems()
	{
		global $rbacsystem;

		foreach($this->items as $item)
		{
			if($item["type"] != "rolf" and
			   ($item["timing_type"] or
				($item["timing_start"] <= time() and $item["timing_end"] >= time())))
			{
				if($rbacsystem->checkAccess('visible',$item['ref_id']))
				{
					$filtered[] = $item;
				}
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
		global $ilErr;
		
		$ilErr->setMessage('');

		if($this->getTimingType())
		{
			if($this->getTimingStart() > $this->getTimingEnd())
			{
				$ilErr->appendMessage($this->lng->txt("crs_activation_start_invalid"));
			}
		}

		if($ilErr->getMessage())
		{
			return false;
		}
		return true;
	}

	function update($a_item_id)
	{
		$query = "UPDATE crs_items SET ".
			"timing_min = '".(int) $this->getEarliestTime()."', ".
			"timing_max = '".(int) $this->getLastTime()."', ".
			"activation_unlimited = '".(int) $this->getTimingType()."', ".
			"activation_start = '".(int) $this->getTimingStart()."', ".
			"activation_end = '".(int) $this->getTimingEnd()."', ".
			"changeable = '".(int) $this->enabledChangeable()."', ".
			"visible = '".(int) $this->enabledVisible()."' ".
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
			$a_item["timing_type"] = $row->activation_unlimited;
			$a_item["timing_start"]		= $row->activation_start;
			$a_item["timing_end"]		= $row->activation_end;
			$a_item['changeable']			= $row->changeable;
			$a_item['visible']				= $row->visible;
			$a_item['timing_min']			= $row->timing_min;
			$a_item['timing_max']			= $row->timing_max;
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
		$a_item["timing_type"] = IL_CRS_TIMINGS_DEACTIVATED;
		$a_item["timing_start"]		= time();
		$a_item["timing_end"]		= time();
		$a_item["position"]				= $this->__getLastPosition() + 1;
		$a_item['visible']				= 0;
		$a_item['changeable']			= 0;
		$a_item['visible']				= 0;
		$a_item['changeable']			= 0;
		$a_item['timing_min']			= time();
		$a_item['timing_max']			= time();
		

		$query = "INSERT INTO crs_items ".
			"VALUES('".$this->getParentId()."','".
			$a_item["child"]."','".
			$a_item["timing_min"]."','".
			$a_item["timing_max"]."','".
			$a_item["timing_type"]."','".
			$a_item["timing_start"]."','".
			$a_item["timing_end"]."','".
			$a_item["visible"]."','".
			$a_item["changeable"]."','".
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
				$this->items = ilUtil::sortArray($this->items,"timing_end","asc");
				break;
		}
		return true;
	}
	// STATIC
	function _getItem($a_item_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_items WHERE obj_id = '".$a_item_id."'";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data['timing_min'] = $row->timing_min;
			$data['timing_max'] = $row->timing_max;
			$data['parent_id'] = $row->parent_id;
			$data['obj_id'] = $row->obj_id;
			$data['timing_type'] = $row->activation_unlimited;
			$data['timing_start'] = $row->activation_start;
			$data['timing_end'] = $row->activation_end;
			$data['changeable'] = $row->changeable;
			$data['visible'] = $row->visible;
			$data['position'] = $row->position;
		}
		return $data ? $data : array();
	}
			

		


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