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

class ilLPCollections
{
	var $db = null;

	var $obj_id = null;
	var $items = array();

	function ilLPCollections($a_obj_id)
	{
		global $ilObjDataCache,$ilDB;

		$this->db =& $ilDB;

		$this->obj_id = $a_obj_id;

		$this->__read();
	}
	
	/**
	 * Clone collections
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function cloneCollections($a_target_id,$a_copy_id)
	{
		global $ilObjDataCache,$ilLog;
		
		$target_obj_id = $ilObjDataCache->lookupObjId($a_target_id);
		
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
		$mappings = $cwo->getMappings();
		
		$new_collections = new ilLPCollections($target_obj_id);
	 	foreach($this->items as $item)
	 	{
	 		if(!isset($mappings[$item]) or !$mappings[$item])
	 		{
	 			continue;
	 		}
	 		$new_collections->add($mappings[$item]);
	 		$ilLog->write(__METHOD__.': Added learning progress collection.');
	 	}
	}

	function getObjId()
	{
		return (int) $this->obj_id;
	}

	function getItems()
	{
		return $this->items;
	}

	function isAssigned($a_ref_id)
	{
		return (bool) in_array($a_ref_id,$this->items);
	}

	function add($item_id)
	{
		$query = "DELETE FROM ut_lp_collections ".
			"WHERE obj_id = '".$this->obj_id."' ".
			"AND item_id = '".(int) $item_id."'";
		$this->db->query($query);
		
		$query = "REPLACE INTO ut_lp_collections ".
			"SET obj_id = '".$this->obj_id."', ".
			"item_id = '".(int) $item_id."'";
		$this->db->query($query);
		$this->__read();

		return true;
	}

	function delete($item_id)
	{
		$query = "DELETE FROM ut_lp_collections ".
			"WHERE item_id = '".$item_id."' ".
			"AND obj_id = '".$this->obj_id."'";
		$this->db->query($query);

		$this->__read();

		return true;
	}


	// Static
	function _getPossibleItems($a_target_id)
	{
		global $tree;

		if($tree->isDeleted($a_target_id))
		{
			return array();
		}

		$node_data = $tree->getNodeData($a_target_id);
		foreach($tree->getSubTree($node_data) as $node)
		{
			// avoid recursion
			if($node['ref_id'] == $a_target_id)
			{
				continue;
			}

			switch($node['type'])
			{
				case 'sess':
				case 'exc':
				case 'fold':
				case 'grp':
				case 'sahs':
				case 'lm':
				case 'tst':
				case 'htlm':
					$all_possible[] = $node['ref_id'];
					break;
			}
		}

		return $all_possible ? $all_possible : array();
	}

	function _getCountPossibleItems($a_target_id)
	{
		return count(ilLPCollections::_getPossibleItems($a_target_id));
	}

	function _getCountPossibleSAHSItems($a_target_id)
	{
		return count(ilLPCollections::_getPossibleSAHSItems($a_target_id));
	}


	/**
	* get all tracking items of scorm or aicc object
	*/

	function _getPossibleSAHSItems($target_id)
	{
		include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';

		switch(ilObjSAHSLearningModule::_lookupSubType($target_id))
		{
			case 'hacp':
			case 'aicc':
				include_once './Modules/ScormAicc/classes/class.ilObjAICCLearningModule.php';

				foreach(ilObjAICCLearningModule::_getTrackingItems($target_id) as $item)
				{
					$items["$item[obj_id]"]['title'] = $item['title'];
					#$items[$item->getId()]['title'] = $item->getTitle();
				}
				return $items ? $items : array();

			case 'scorm':
				include_once './Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php';
				include_once './Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php';

				foreach(ilObjSCORMLearningModule::_getTrackingItems($target_id) as $item)
				{
					$items[$item->getId()]['title'] = $item->getTitle();
				}
				return $items ? $items : array();

			case 'scorm2004':
				include_once './Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php';

				foreach(ilObjSCORM2004LearningModule::_getTrackingItems($target_id) as $item)
				{
					$items[$item["id"]]['title'] = $item["title"];
				}
				return $items ? $items : array();
		}
		return array();
	}

	function deleteAll()
	{
		return ilLPCollections::_deleteAll($this->getObjId());
	}


	function _deleteAll($a_obj_id)
	{
		global $ilDB;

		$query = "DELETE FROM ut_lp_collections ".
			"WHERE obj_id = '".$a_obj_id."'";
		$ilDB->query($query);

		return true;
	}

	function &_getItems($a_obj_id)
	{
		global $ilObjDataCache;
		global $ilDB;

		include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';

		$mode = ilLPObjSettings::_lookupMode($a_obj_id);
		if($mode == LP_MODE_OBJECTIVES)
		{
			include_once 'Modules/Course/classes/class.ilCourseObjective.php';
			return ilCourseObjective::_getObjectiveIds($a_obj_id);
		}
		if($mode != LP_MODE_SCORM and $mode != LP_MODE_COLLECTION and $mode != LP_MODE_MANUAL_BY_TUTOR)
		{
			return array();
		}

		if($ilObjDataCache->lookupType($a_obj_id) != 'sahs')
		{
			$course_ref_ids = ilObject::_getAllReferences($a_obj_id);
			$course_ref_id = end($course_ref_ids);
			$possible_items = ilLPCollections::_getPossibleItems($course_ref_id);

			$query = "SELECT * FROM ut_lp_collections utc ".
				"JOIN object_reference obr ON item_id = ref_id ".
				"JOIN object_data obd ON obr.obj_id = obd.obj_id ".
				"WHERE utc.obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
				"ORDER BY title";
		}
		else
		{
			// SAHS
			$query = "SELECT * FROM ut_lp_collections WHERE obj_id = '".$a_obj_id."'";
		}

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($ilObjDataCache->lookupType($a_obj_id) != 'sahs')
			{
				if(!in_array($row->item_id,$possible_items))
				{
					ilLPCollections::__deleteEntry($a_obj_id,$row->item_id);
					continue;
				}
			}
			// Check anonymized
			if($ilObjDataCache->lookupType($item_obj_id = $ilObjDataCache->lookupObjId($row->item_id)) == 'tst')
			{
				include_once './Modules/Test/classes/class.ilObjTest.php';
				if(ilObjTest::_lookupAnonymity($item_obj_id))
				{
					ilLPCollections::__deleteEntry($a_obj_id,$row->item_id);
					continue;
				}
			}
			$items[] = $row->item_id;
		}
		return $items ? $items : array();
	}

	// Private
	function __deleteEntry($a_obj_id,$a_item_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM ut_lp_collections ".
			"WHERE obj_id = '".$a_obj_id."' ".
			"AND item_id = '".$a_item_id."'";
		$ilDB->query($query);
		return true;
	}


	function __read()
	{
		global $ilObjDataCache;

		if($ilObjDataCache->lookupType($this->getObjId()) != 'sahs')
		{
			$course_ref_ids = ilObject::_getAllReferences($this->getObjId());
			$course_ref_id = end($course_ref_ids);
			$query = "SELECT * FROM ut_lp_collections utc ".
				"JOIN object_reference obr ON item_id = ref_id ".
				"JOIN object_data obd ON obr.obj_id = obd.obj_id ".
				"WHERE utc.obj_id = ".$this->db->quote($this->obj_id,'integer')." ".
				"ORDER BY title";
		}
		else
		{
			$query = "SELECT * FROM ut_lp_collections WHERE obj_id = '".$this->getObjId()."'";
		}
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($ilObjDataCache->lookupType($this->getObjId()) != 'sahs')
			{
				if(!in_array($row->item_id,ilLPCollections::_getPossibleItems($course_ref_id)))
				{
					$this->__deleteEntry($this->getObjId(),$row->item_id);
					continue;
				}
			}
			// Check anonymized
			if($ilObjDataCache->lookupType($item_obj_id = $ilObjDataCache->lookupObjId($row->item_id)) == 'tst')
			{
				include_once './Modules/Test/classes/class.ilObjTest.php';
				if(ilObjTest::_lookupAnonymity($item_obj_id))
				{
					$this->__deleteEntry($this->getObjId(),$row->item_id);
					continue;
				}
			}
			$this->items[] = $row->item_id;
		}
		
	}
}
?>