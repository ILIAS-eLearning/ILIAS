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

	function _hasTimings($a_ref_id)
	{
		global $tree,$ilDB;

		$subtree = $tree->getSubTree($tree->getNodeData($a_ref_id));
		foreach($subtree as $node)
		{
			$ref_ids[] = $node['ref_id'];
		}

		$query = "SELECT * FROM crs_items ".
			"WHERE timing_type = '".IL_CRS_TIMINGS_PRESETTING."' ".
			"AND obj_id IN('".implode("','",$ref_ids)."')";

		$res = $ilDB->query($query);
		return $res->numRows() ? true :false;
	}

	function _hasChangeableTimings($a_ref_id)
	{
		global $tree,$ilDB;

		$subtree = $tree->getSubTree($tree->getNodeData($a_ref_id));
		foreach($subtree as $node)
		{
			$ref_ids[] = $node['ref_id'];
		}

		$query = "SELECT * FROM crs_items ".
			"WHERE timing_type = '".IL_CRS_TIMINGS_PRESETTING."' ".
			"AND changeable = '1' ".
			"AND obj_id IN('".implode("','",$ref_ids)."') ".
			"AND parent_id IN('".implode("','",$ref_ids)."')";

		$res = $ilDB->query($query);
		return $res->numRows() ? true :false;
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
	function setSuggestionStart($a_start)
	{
		$this->suggestion_start = $a_start;
	}
	function getSuggestionStart()
	{
		return $this->suggestion_start ? $this->suggestion_start : mktime(0,0,1,date('n',time()),date('j',time()),date('Y',time()));
	}
	function setSuggestionEnd($a_end)
	{
		$this->suggestion_end = $a_end;
	}
	function getSuggestionEnd()
	{
		return $this->suggestion_end ? $this->suggestion_end : mktime(23,55,00,date('n',time()),date('j',time()),date('Y',time()));
	}
	function setEarliestStart($a_start)
	{
		$this->earliest_start = $a_start;
	}
	function getEarliestStart()
	{
		return $this->earliest_start ? $this->earliest_start : mktime(0,0,1,date('n',time()),date('j',time()),date('Y',time()));
	}
	function setLatestEnd($a_end)
	{
		$this->latest_end = $a_end;
	}
	function getLatestEnd()
	{
		return $this->latest_end ? $this->latest_end : mktime(23,55,00,date('n',time()),date('j',time()),date('Y',time()));
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

	function getFilteredItems($a_container_id)
	{
		include_once 'course/classes/Event/class.ilEventItems.php';

		$event_items = ilEventItems::_getItemsOfContainer($a_container_id);

		foreach($this->items as $item)
		{
			if(!in_array($item['ref_id'],$event_items))
			{
				$filtered[] = $item;
			}
		}
		return $filtered ? $filtered : array();
	} 

	function getItemsByEvent($a_event_id)
	{
		include_once 'course/classes/Event/class.ilEventItems.php';

		$event_items_obj = new ilEventItems($a_event_id);
		$event_items = $event_items_obj->getItems();
		foreach($event_items as $item)
		{
			if($this->tree->isDeleted($item))
			{
				continue;
			}
			$node = $this->tree->getNodeData($item);
			$items[] = $this->__getItemData($node);
		}
		return $items ? $items : array();
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

		if($this->getTimingType() == IL_CRS_TIMINGS_ACTIVATION)
		{
			if($this->getTimingStart() > $this->getTimingEnd())
			{
				$ilErr->appendMessage($this->lng->txt("crs_activation_start_invalid"));
			}
		}
		if($this->getTimingType() == IL_CRS_TIMINGS_PRESETTING)
		{
			if($this->getSuggestionStart() > $this->getSuggestionEnd())
			{
				$ilErr->appendMessage($this->lng->txt("crs_suggestion_not_valid"));
			}
		}			
		if($this->getTimingType() == IL_CRS_TIMINGS_PRESETTING and 
		   $this->enabledChangeable())
		{
			if($this->getSuggestionStart() < $this->getEarliestStart() or
			   $this->getSuggestionEnd() > $this->getLatestEnd() or
			   $this->getSuggestionStart() > $this->getLatestEnd() or
			   $this->getSuggestionEnd() < $this->getEarliestStart())
			{
				$ilErr->appendMessage($this->lng->txt("crs_suggestion_not_within_activation"));
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
			"timing_type = '".(int) $this->getTimingType()."', ".
			"timing_start = '".(int) $this->getTimingStart()."', ".
			"timing_end = '".(int) $this->getTimingEnd()."', ".
			"suggestion_start = '".(int) $this->getSuggestionStart()."', ".
			"suggestion_end = '".(int) $this->getSuggestionEnd()."', ".
			"changeable = '".(int) $this->enabledChangeable()."', ".
			"earliest_start = '".(int) $this->getEarliestStart()."', ".
			"latest_end = '".(int) $this->getLatestEnd()."', ".
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
		global $tree;

		$all = array();

		$query = "SELECT obj_id FROM crs_items ".
			"WHERE parent_id = '".$this->getParentId()."'";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($tree->getParentId($row->obj_id) != $this->getParentId())
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
			"WHERE parent_id = '".$a_item['parent']."' ".
			"AND obj_id = '".$a_item["child"]."'";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$a_item["timing_type"] = $row->timing_type;
			$a_item["timing_start"]		= $row->timing_start;
			$a_item["timing_end"]		= $row->timing_end;
			$a_item["suggestion_start"]		= $row->suggestion_start ? $row->suggestion_start :
				mktime(0,0,1,date('n',time()),date('j',time()),date('Y',time()));
			$a_item["suggestion_end"]		= $row->suggestion_end ? $row->suggestion_end :
				mktime(23,55,00,date('n',time()),date('j',time()),date('Y',time()));
			$a_item['changeable']			= $row->changeable;
			$a_item['earliest_start']		= $row->earliest_start ? $row->earliest_start :
				mktime(0,0,1,date('n',time()),date('j',time()),date('Y',time()));
			$a_item['latest_end']			= $row->latest_end ? $row->latest_end : 
				mktime(23,55,00,date('n',time()),date('j',time()),date('Y',time()));
			$a_item['visible']				= $row->visible;
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
		$a_item["suggestion_start"]		= time();
		$a_item["suggestion_end"]		= time();
		$a_item["position"]				= $this->__getLastPosition() + 1;
		$a_item['visible']				= 0;
		$a_item['changeable']			= 0;
		$a_item['earliest_start']		= time();
		$a_item['latest_end']	    	= mktime(23,55,00,date('n',time()),date('j',time()),date('Y',time()));
		$a_item['visible']				= 0;
		$a_item['changeable']			= 0;
		

		$query = "INSERT INTO crs_items ".
			"VALUES('".$a_item['parent']."','".
			$a_item["child"]."','".
			$a_item["timing_type"]."','".
			$a_item["timing_start"]."','".
			$a_item["timing_end"]."','".
			$a_item["suggestion_start"]."','".
			$a_item["suggestion_end"]."','".
			$a_item["changeable"]."','".
			$a_item['earliest_start']."',' ".
			$a_item['latest_end']."',' ".
			$a_item["visible"]."','".
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
			if(!$this->__isMovable($row->obj_id))
			{
				continue;
			}
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
			if(!$this->__isMovable($row->obj_id))
			{
				continue;
			}
			$node_b["position"] = $row->position;
			$node_b["obj_id"]	  = $row->obj_id;
			break;
		}
		$this->__switchNodes($node_a,$node_b);

		return true;
	}

	function __isMovable($a_ref_id)
	{
		include_once 'course/classes/Event/class.ilEventItems.php';

		global $ilObjDataCache;

		if($ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($a_ref_id)) != 'crs')
		{
			return true;
		}
		if(ilEventItems::_isAssigned($a_ref_id))
		{
			return false;
		}
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
				// Sort by starting time. If mode is IL_CRS_TIMINGS_DEACTIVATED then sort these items by title and append
				// them to the array.
				list($active,$inactive) = $this->__splitByActivation();
				
				$sorted_active = ilUtil::sortArray($active,"timing_start","asc");
				$sorted_inactive = ilUtil::sortArray($inactive,'title','asc');
				
				$this->items = array_merge($sorted_active,$sorted_inactive);
				break;
		}
		return true;
	}

	function __splitByActivation()
	{
		$inactive = $active = array();
		foreach($this->items as $item)
		{
			if($item['timing_type'] == IL_CRS_TIMINGS_DEACTIVATED)
			{
				$inactive[] = $item;
			}
			else
			{
				$active[] = $item;
			}
		}
		return array($active,$inactive);
	}
	// STATIC
	function _getItem($a_item_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_items WHERE obj_id = '".$a_item_id."'";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data['parent_id'] = $row->parent_id;
			$data['obj_id'] = $row->obj_id;
			$data['timing_type'] = $row->timing_type;
			$data['timing_start'] = $row->timing_start;
			$data['timing_end'] = $row->timing_end;
			$data["suggestion_start"] = $row->suggestion_start ? $row->suggestion_start :
				mktime(0,0,1,date('n',time()),date('j',time()),date('Y',time()));
			$data["suggestion_end"]	= $row->suggestion_end ? $row->suggestion_end :
				mktime(23,55,00,date('n',time()),date('j',time()),date('Y',time()));
			$data['changeable'] = $row->changeable;
			$data['earliest_start']	= $row->earliest_start ? $row->earliest_start :
				mktime(0,0,1,date('n',time()),date('j',time()),date('Y',time()));
			$data['latest_end'] = $row->latest_end ? $row->latest_end : 
				mktime(23,55,00,date('n',time()),date('j',time()),date('Y',time()));
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