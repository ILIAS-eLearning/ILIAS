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
*/

define('IL_CRS_TIMINGS_ACTIVATION',0);
define('IL_CRS_TIMINGS_DEACTIVATED',1);
define('IL_CRS_TIMINGS_PRESETTING',2);
define('IL_CRS_TIMINGS_FIXED',3);

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


	function ilCourseItems(&$course_obj,$a_parent = 0,$user_id = 0)
	{
		global $ilErr,$ilDB,$lng,$tree;

		$this->ilErr =& $ilErr;
		$this->ilDB  =& $ilDB;
		$this->lng   =& $lng;
		$this->tree  =& $tree;

		$this->course_obj =& $course_obj;
		$this->setParentId($a_parent);
		$this->user_id = $user_id;

		$this->__read();
	}

	/**
	 * Clone dependencies
	 *
	 * @access public
	 * @param int target ref_id
	 * @param int copy id
	 * 
	 */
	public function cloneDependencies($a_target_id,$a_copy_id)
	{
	 	global $ilObjDataCache,$ilLog;
	 	
		$ilLog->write(__METHOD__.': Begin course items...');
 	
	 	$target_obj_id = $ilObjDataCache->lookupObjId($a_target_id);
	 	
	 	include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
	 	$cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);
	 	$mappings = $cp_options->getMappings();
	 	
	 	$query = "SELECT * FROM crs_items WHERE ".
	 		"parent_id = ".$this->ilDB->quote($this->getParentId())." ".
	 		"ORDER BY position DESC";
	 	$res = $this->ilDB->query($query);
	 	
	 	if(!$res->numRows())
	 	{
			$ilLog->write(__METHOD__.': No course items found.');
	 		return true;
	 	}
	 	
	 	// new course item object
	 	if(!is_object($new_container = ilObjectFactory::getInstanceByRefId($a_target_id,false)))
	 	{
			$ilLog->write(__METHOD__.': Cannot create target object.');
	 		return false;
	 	}
	 	$new_items = new ilCourseItems($this->course_obj,$a_target_id);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		if(!isset($mappings[$row->parent_id]) or !$mappings[$row->parent_id])
	 		{
				$ilLog->write(__METHOD__.': No mapping for parent nr. '.$row->parent_id);
	 			continue;
	 		}
	 		if(!isset($mappings[$row->obj_id]) or !$mappings[$row->obj_id])
	 		{
				$ilLog->write(__METHOD__.': No mapping for item nr. '.$row->obj_id);
	 			continue;
	 		}
	 		$new_item_id = $mappings[$row->obj_id];
	 		$new_parent = $mappings[$row->parent_id];
	 		
	 		$new_items->setItemId($new_item_id);
	 		$new_items->setParentId($new_parent);
	 		$new_items->setTimingType($row->timing_type);
	 		$new_items->setTimingStart($row->timing_start);
	 		$new_items->setTimingEnd($row->timing_end);
	 		$new_items->setSuggestionStart($row->suggestion_start);
	 		$new_items->setSuggestionEnd($row->suggestion_end);
	 		$new_items->toggleChangeable($row->changeable);
	 		$new_items->setEarliestStart($row->earliest_start);
	 		$new_items->setLatestEnd($row->latest_end);
	 		$new_items->toggleVisible($row->visible);
	 		$new_items->setPosition($row->position);
	 		$new_items->update($new_item_id);
			$ilLog->write(__METHOD__.': Added new entry for item nr. '.$row->obj_id);
	 	}
		$ilLog->write(__METHOD__.': Finished course items.');
	}
	
	public function setItemId($a_item_id)
	{
		$this->item_id = $a_item_id;
	}
	
	public function getItemId()
	{
		return $this->item_id;
	}
	function getUserId()
	{
		global $ilUser;

		return $this->user_id ? $this->user_id : $ilUser->getId();
	}

	function _hasCollectionTimings($a_ref_id)
	{
		global $tree,$ilDB,$ilObjDataCache;

		// get all collections
		include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';

		$obj_id = $ilObjDataCache->lookupObjId($a_ref_id);
		switch(ilLPObjSettings::_lookupMode($obj_id))
		{
			case LP_MODE_MANUAL_BY_TUTOR:
			case LP_MODE_COLLECTION:
				include_once 'Services/Tracking/classes/class.ilLPCollectionCache.php';
				$ids = ilLPCollectionCache::_getItems($obj_id);
				break;
			default:
				$ids = array($a_ref_id);
				break;
		}
		if(!$ids)
		{
			return false;
		}
		
		$query = "SELECT * FROM crs_items ".
			"WHERE timing_type = ".$ilDB->quote(IL_CRS_TIMINGS_PRESETTING)." ".
			"AND obj_id IN(".implode(",",ilUtil::quoteArray($ids)).")";

		$res = $ilDB->query($query);
		return $res->numRows() ? true :false;
	}
	
	/**
	 * check if there is any active timing
	 *
	 * @access public
	 * @param int ref_id
	 * @return
	 */
	public function _hasTimings($a_ref_id)
	{
		global $tree,$ilDB;

		$subtree = $tree->getSubTree($tree->getNodeData($a_ref_id));
		
		foreach($subtree as $node)
		{
			$ref_ids[] = $node['ref_id'];
		}

		$query = "SELECT * FROM crs_items ".
			"WHERE timing_type = ".$ilDB->quote(IL_CRS_TIMINGS_PRESETTING)." ".
			"AND obj_id IN(".implode(",",ilUtil::quoteArray($ref_ids)).") ".
			"AND parent_id IN(".implode(",",ilUtil::quoteArray($ref_ids)).")";

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
			"WHERE timing_type = ".$ilDB->quote(IL_CRS_TIMINGS_PRESETTING)." ".
			"AND changeable = '1' ".
			"AND obj_id IN(".implode(",",ilUtil::quoteArray($ref_ids)).") ".
			"AND parent_id IN(".implode(",",ilUtil::quoteArray($ref_ids)).")";

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
	
	function setPosition($a_pos)
	{
		$this->position = $a_pos;
	}

	function getAllItems()
	{
		return $this->items ? $this->items : array();
	}

	/**
	* Get all items. (No events, no side blocks)
	*/
	function getFilteredItems($a_container_ref_id)
	{
		global $objDefinition;
		
		include_once 'Modules/Session/classes/class.ilEventItems.php';

		$event_items = ilEventItems::_getItemsOfContainer($a_container_ref_id);
		foreach($this->items as $item)
		{
			if(!in_array($item['ref_id'],$event_items) &&
				!$objDefinition->isSideBlock($item['type']))
			{
				$filtered[] = $item;
			}
		}
		return $filtered ? $filtered : array();
	} 

	function getItemsByEvent($a_event_id)
	{
		include_once 'Modules/Session/classes/class.ilEventItems.php';

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
				$ilErr->appendMessage($this->lng->txt('crs_latest_end_not_valid'));
			}
		}
		// Disabled
		#if($this->getTimingType() == IL_CRS_TIMINGS_PRESETTING and 
		#   $this->enabledChangeable())
		#{
		#	if($this->getSuggestionStart() < $this->getEarliestStart() or
		#	   $this->getSuggestionEnd() > $this->getLatestEnd() or
		#	   $this->getSuggestionStart() > $this->getLatestEnd() or
		#	   $this->getSuggestionEnd() < $this->getEarliestStart())
		#	{
		#		$ilErr->appendMessage($this->lng->txt("crs_suggestion_not_within_activation"));
		#	}
		#}

		if($ilErr->getMessage())
		{
			return false;
		}
		return true;
	}
	
	/**
	 * Save
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
		global $ilLog;
		
	 	$query = "INSERT INTO crs_items SET ".
			"timing_type = ".$ilDB->quote($this->getTimingType()).", ".
			"timing_start = ".$ilDB->quote($this->getTimingStart()).", ".
			"timing_end = ".$ilDB->quote($this->getTimingEnd()).", ".
			"suggestion_start = ".$ilDB->quote($this->getSuggestionStart()).", ".
			"suggestion_end = ".$ilDB->quote($this->getSuggestionEnd()).", ".
			"changeable = ".$ilDB->quote($this->enabledChangeable()).", ".
			"earliest_start = ".$ilDB->quote($this->getEarliestStart()).", ".
			"latest_end = ".$ilDB->quote($this->getLatestEnd()).", ".
			"visible = ".$ilDB->quote($this->enabledVisible()).", ".
			"parent_id = ".$ilDB->quote($this->getParentId()).", ".
			"obj_id = ".$ilDB->quote($this->getItemId()).", ".
			"position = ".$this->ilDB->quote($this->position);
		$ilLog->write(__METHOD__.': '.$query);	

		$res = $this->ilDB->query($query);
	}
	

	function update($a_item_id)
	{
		global $ilDB;
		
		$query = "UPDATE crs_items SET ".
			"timing_type = ".$ilDB->quote($this->getTimingType()).", ".
			"timing_start = ".$ilDB->quote($this->getTimingStart()).", ".
			"timing_end = ".$ilDB->quote($this->getTimingEnd()).", ".
			"suggestion_start = ".$ilDB->quote($this->getSuggestionStart()).", ".
			"suggestion_end = ".$ilDB->quote($this->getSuggestionEnd()).", ".
			"changeable = ".$ilDB->quote($this->enabledChangeable()).", ".
			"earliest_start = ".$ilDB->quote($this->getEarliestStart()).", ".
			"latest_end = ".$ilDB->quote($this->getLatestEnd()).", ".
			"visible = ".$ilDB->quote($this->enabledVisible())." ".
			"WHERE parent_id = ".$ilDB->quote($this->getParentId())." ".
			"AND obj_id = ".$ilDB->quote($a_item_id)."";

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
		global $ilDB;
		
		$all_items = $this->tree->getChilds($this->parent);

		foreach($all_items as $item)
		{
			$query = "DELETE FROM crs_items ".
				"WHERE parent_id = ".$ilDB->quote($item["child"])."";

			$this->ilDB->query($query);
		}
		$query = "DELETE FROM crs_items ".
			"WHERE parent_id = ".$ilDB->quote($this->course_obj->getRefId())." ";
		
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
		
		// one array for items per child id
		$this->items_per_child = array();
		foreach($this->items as $item)
		{
			$this->items_per_child[$item["child"]] = $item;
		}

		return true;
	}

	function __purgeDeleted()
	{
		global $tree,$ilDB;

		$all = array();

		$query = "SELECT obj_id FROM crs_items ".
			"WHERE parent_id = ".$ilDB->quote($this->getParentId())." ";

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
		global $ilDB;
		
		// READ POSITION
		$query = "SELECT position FROM crs_items ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ".
			"AND parent_id = ".$ilDB->quote($this->getParentId())." ";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$position = $row->position;
		}

		// UPDATE positions
		$query = "UPDATE crs_items SET ".
			"position = CASE ".
			"WHEN position > ".$ilDB->quote($position)." ".
			"THEN position - 1 ".
			"ELSE position ".
			"END ".
			"WHERE parent_id = ".$ilDB->quote($this->getParentId())." ";

		$res = $this->ilDB->query($query);

		// DELETE ENTRY
		$query = "DELETE FROM crs_items ".
			"WHERE parent_id = ".$ilDB->quote($this->getParentId())." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id)." ";

		$res = $this->ilDB->query($query);

		return true;
	}
			
	function __getItemData($a_item)
	{
		global $ilDB,$ilUser,$ilObjDataCache;

		$query = "SELECT * FROM crs_items  ".
			"WHERE obj_id = ".$ilDB->quote($a_item['child'])." ".
			"AND parent_id = ".$ilDB->quote($a_item['parent'])." ";

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
			
			include_once 'Modules/Course/classes/Timings/class.ilTimingPlaned.php';
			$data = ilTimingPlaned::_getPlanedTimings($this->getUserId(),$a_item['child']);

			if($ilObjDataCache->lookupType($obj_id = $ilObjDataCache->lookupObjId($row->obj_id)) == 'sess')
			{
				include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
				$info = ilSessionAppointment::_lookupAppointment($obj_id);
				$a_item['timing_type'] = IL_CRS_TIMINGS_FIXED;
				$a_item['start'] = $info['start'];
				$a_item['end'] = $info['end'];
				$a_item['fullday'] = $info['fullday'];
				$a_item['activation_info'] = 'crs_timings_suggested_info';
				continue;
			}


			// Check for user entry
			if($a_item['changeable'] and 
			   $a_item['timing_type'] == IL_CRS_TIMINGS_PRESETTING)
			{
				if($data['planed_start'])
				{
					$a_item['start'] = $data['planed_start'];
					$a_item['end'] = $data['planed_end'];
					$a_item['activation_info'] = 'crs_timings_planed_info';
				}
				else
				{
					$a_item['start'] = $row->suggestion_start;
					$a_item['end'] = $row->suggestion_end;
					$a_item['activation_info'] = 'crs_timings_suggested_info';
				}
			}
			elseif($a_item['timing_type'] == IL_CRS_TIMINGS_PRESETTING)
			{
				$a_item['start'] = $row->suggestion_start;
				$a_item['end'] = $row->suggestion_end;
				$a_item['activation_info'] = 'crs_timings_suggested_info';
			}
			elseif($a_item['timing_type'] == IL_CRS_TIMINGS_ACTIVATION)
			{
				$a_item['start'] = $row->timing_start;
				$a_item['end'] = $row->timing_end;
				$a_item['activation_info'] = 'activation';
			}
			else
			{
				$a_item['start'] = 999999999;
			}
		}

		if(!isset($a_item["position"]))
		{
			$a_item = $this->createDefaultEntry($a_item);
		}
		return $a_item;
	}

	function createDefaultEntry($a_item)
	{
		global $ilDB, $objDefinition;
		
		$a_item["timing_type"] = IL_CRS_TIMINGS_DEACTIVATED;
		$a_item["timing_start"]		= time();
		$a_item["timing_end"]		= time();
		$a_item["suggestion_start"]		= time();
		$a_item["suggestion_end"]		= time();
		if ($objDefinition->isSideBlock($a_item["type"]))
		{
			$a_item["position"]				= 0;
		}
		else
		{
			$a_item["position"]				= $this->__getLastPosition() + 1;
		}
		$a_item['visible']				= 0;
		$a_item['changeable']			= 0;
		$a_item['earliest_start']		= time();
		$a_item['latest_end']	    	= mktime(23,55,00,date('n',time()),date('j',time()),date('Y',time()));
		$a_item['visible']				= 0;
		$a_item['changeable']			= 0;
		

		$query = "INSERT INTO crs_items ".
			"VALUES(".$ilDB->quote($a_item['parent']).",".
			$ilDB->quote($a_item["child"]).",".
			$ilDB->quote($a_item["timing_type"]).",".
			$ilDB->quote($a_item["timing_start"]).",".
			$ilDB->quote($a_item["timing_end"]).",".
			$ilDB->quote($a_item["suggestion_start"]).",".
			$ilDB->quote($a_item["suggestion_end"]).",".
			$ilDB->quote($a_item["changeable"]).",".
			$ilDB->quote($a_item['earliest_start']).", ".
			$ilDB->quote($a_item['latest_end']).", ".
			$ilDB->quote($a_item["visible"]).",".
			$ilDB->quote($a_item["position"]).")";

		$res = $this->ilDB->query($query);

		return $a_item;
	}

	// methods for manual sortation
	function __getLastPosition()
	{
		global $ilDB;
		
		$query = "SELECT MAX(position) as last_position FROM crs_items ".
			"WHERE parent_id = ".$ilDB->quote($this->getParentId())." ";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$max = $row->last_position;
		}
		return $max ? $max : 0;
	}

	function __updateTop($item_id)
	{
		global $ilDB;
		
		$query = "SELECT position,obj_id FROM crs_items ".
			"WHERE obj_id = ".$ilDB->quote($item_id)." ".
			"AND parent_id = ".$ilDB->quote($this->getParentId())."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$node_a["position"] = $row->position;
			$node_a["obj_id"]	  = $row->obj_id;
		}

		$query = "SELECT position, obj_id FROM crs_items ".
			"WHERE position < ".$ilDB->quote($node_a["position"])." ".
			"AND parent_id = ".$ilDB->quote($this->getParentId())." ".
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
		global $ilDB;
		
		$query = "SELECT position,obj_id FROM crs_items ".
			"WHERE obj_id = ".$ilDB->quote($item_id)." ".
			"AND parent_id = ".$ilDB->quote($this->getParentId())."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$node_a["position"] = $row->position;
			$node_a["obj_id"]	  = $row->obj_id;
			break;
		}
		$query = "SELECT position ,obj_id FROM crs_items ".
			"WHERE position > ".$ilDB->quote($node_a["position"])." ".
			"AND parent_id = ".$ilDB->quote($this->getParentId())." ".
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
		include_once 'Modules/Session/classes/class.ilEventItems.php';

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
		global $ilDB;
		
		if(!$node_b["obj_id"])
		{
			return false;
		}

		$query = "UPDATE crs_items SET ".
			"position = ".$ilDB->quote($node_a["position"])." ".
			"WHERE obj_id = ".$ilDB->quote($node_b["obj_id"])." ".
			"AND parent_id = ".$ilDB->quote($this->getParentId())."";

		$res = $this->ilDB->query($query);

		$query = "UPDATE crs_items SET ".
			"position = ".$ilDB->quote($node_b["position"])." ".
			"WHERE obj_id = ".$ilDB->quote($node_a["obj_id"])." ".
			"AND parent_id = ".$ilDB->quote($this->getParentId())."";

		$res = $this->ilDB->query($query);

		return true;
	}
	
	/**
	 * sort items
	 *
	 * @access public
	 * @param
	 * @return
	 * @static
	 */
	public static function _sort($a_sort_mode,$a_items)
	{
		switch($a_sort_mode)
		{
			case IL_CRS_SORT_MANUAL:
				return ilUtil::sortArray($a_items,"position","asc",true);
				break;

			case IL_CRS_SORT_TITLE:
				return ilUtil::sortArray($a_items,"title","asc");
				break;

			case IL_CRS_SORT_ACTIVATION:
				// Sort by starting time. If mode is IL_CRS_TIMINGS_DEACTIVATED then sort these items by title and append
				// them to the array.
				$inactive = $active = array();
				foreach($a_items as $item)
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
				$sorted_active = ilUtil::sortArray($active,"start","asc");
				$sorted_inactive = ilUtil::sortArray($inactive,'title','asc');
				
				return array_merge($sorted_active,$sorted_inactive);
				break;
		}
		return true;
		
	}


	function __sort()
	{
		switch($this->course_obj->getOrderType())
		{
			case IL_CRS_SORT_MANUAL:
				$this->items = ilUtil::sortArray($this->items,"position","asc",true);
				break;

			case IL_CRS_SORT_TITLE:
				$this->items = ilUtil::sortArray($this->items,"title","asc");
				break;

			case IL_CRS_SORT_ACTIVATION:
				// Sort by starting time. If mode is IL_CRS_TIMINGS_DEACTIVATED then sort these items by title and append
				// them to the array.
				list($active,$inactive) = $this->__splitByActivation();
				
				$sorted_active = ilUtil::sortArray($active,"start","asc");
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
		include_once 'Modules/Course/classes/class.ilObjCourse.php';

		global $ilDB,$ilUser;

		$query = "SELECT * FROM crs_items ".
			"WHERE obj_id = '".$a_item_id."'";
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


			include_once 'Modules/Course/classes/Timings/class.ilTimingPlaned.php';
			$user_data = ilTimingPlaned::_getPlanedTimings($ilUser->getId(),$data['child']);

			// Check for user entry
			if($data['changeable'] and 
			   $data['timing_type'] == IL_CRS_TIMINGS_PRESETTING)
			{
				if($user_data['planed_start'])
				{
					$data['start'] = $user_data['planed_start'];
					$data['end'] = $user_data['planed_end'];
					$data['activation_info'] = 'crs_timings_planed_info';
				}
				else
				{
					$data['start'] = $row->suggestion_start;
					$data['end'] = $row->suggestion_end;
					$data['activation_info'] = 'crs_timings_suggested_info';
				}
			}
			elseif($data['timing_type'] == IL_CRS_TIMINGS_PRESETTING)
			{
				$data['start'] = $row->suggestion_start;
				$data['end'] = $row->suggestion_end;
				$data['activation_info'] = 'crs_timings_suggested_info';
			}
			elseif($data['timing_type'] == IL_CRS_TIMINGS_ACTIVATION)
			{
				$data['start'] = $row->timing_start;
				$data['end'] = $row->timing_end;
				$data['activation_info'] = 'activation';
			}
			else
			{
				$data['start'] = 999999999;
			}
		}
		return $data ? $data : array();
	}

	function _isActivated($a_item_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_items ".
			"WHERE obj_id = ".$ilDB->quote($a_item_id)." ";

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
	
	/**
	 * read activation times
	 *
	 * @access public
	 * @param array array(int) ref_ids
	 * 
	 */
	public static function _readActivationTimes($a_ref_ids)
	{
	 	global $ilDB;
	 	
	 	if(!is_array($a_ref_ids) or !$a_ref_ids)
	 	{
	 		return array();
	 	}
	 	
	 	$query = "SELECT obj_id,timing_type,timing_start,timing_end,visible FROM crs_items ".
	 		"WHERE obj_id IN (".implode(',',$a_ref_ids).")";
	 	$res = $ilDB->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$ac_times[(string) $row->obj_id]['obj_id'] = $row->obj_id;
	 		$ac_times[(string) $row->obj_id]['timing_type'] = $row->timing_type;
	 		$ac_times[(string) $row->obj_id]['timing_start'] = $row->timing_start;
	 		$ac_times[(string) $row->obj_id]['timing_end'] = $row->timing_end;
	 		$ac_times[(string) $row->obj_id]['visible'] = $row->visible;
	 	}

		return $ac_times ? $ac_times : array();
	}
	
	/**
	* Adds information to object/item array needed to be displayed in repository
	*/
	function addAdditionalSubItemInformation(&$a_item)
	{
		if (is_array($this->items_per_child[$a_item["child"]]))
		{
			$a_item = array_merge($a_item, $this->items_per_child[$a_item["child"]]);
		}
	}
}
?>