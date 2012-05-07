<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilLPObjSettings
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesTracking
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
			// @FIXME clone this and not add it
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
		global $ilDB;

		$query = "SELECT * FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId(),'integer')." ".
			"AND item_id = ".$ilDB->quote($item_id,'integer');
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			return true;
		}

		$query = "INSERT INTO ut_lp_collections (obj_id, item_id) ".
			"VALUES( ".
			$ilDB->quote($this->obj_id ,'integer').", ".
			$ilDB->quote($item_id ,'integer').
			")";
		$res = $ilDB->manipulate($query);

		$this->__read();

		return true;
	}

	/**
	 * Deactivate assignments
	 */
	public static function deactivate($a_obj_id,array $a_item_ids)
	{
		global $ilDB;

		// Delete all non grouped items
		$query = "DELETE FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND ".$ilDB->in('item_id', $a_item_ids, false, 'integer')." ".
			"AND grouping_id = ".$ilDB->quote(0, 'integer');
		$ilDB->manipulate($query);

		// Select all grouping ids and deactivate them
		$query = "SELECT grouping_id FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND ".$ilDB->in('item_id', $a_item_ids, false, 'integer');
		$res = $ilDB->query($query);

		$grouping_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$grouping_ids[] = $row->grouping_id;
		}

		$query = "UPDATE ut_lp_collections ".
			"SET active = ".$ilDB->quote(0,'integer')." ".
			"WHERE ".$ilDB->in('grouping_id', $grouping_ids, false, 'integer');
		$ilDB->manipulate($query);
		return;
	}

	/**
	 * Activate assignment
	 * @param int $a_obj_id
	 * @param array $a_item_ids
	 */
	public static function activate($a_obj_id,array $a_item_ids)
	{
		global $ilDB;

		// Add missing entries
		$sql = "SELECT item_id FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND ".$ilDB->in('item_id', $a_item_ids, false, 'integer');
		$res = $ilDB->query($sql);

		$items_existing = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$items_existing[] = $row->item_id;
		}

		$items_not_existing = array_diff($a_item_ids, $items_existing);
		foreach($items_not_existing as $item)
		{
			$query = "INSERT INTO ut_lp_collections (obj_id,item_id,grouping_id,num_obligatory,active ) ".
				"VALUES( ".
				$ilDB->quote($a_obj_id,'integer').", ".
				$ilDB->quote($item,'integer').", ".
				$ilDB->quote(0,'integer').", ".
				$ilDB->quote(0,'integer').", ".
				$ilDB->quote(1,'integer')." ".
				")";
			$ilDB->manipulate($query);
		}
		// Select all grouping ids and activate them
		$query = "SELECT grouping_id FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND ".$ilDB->in('item_id', $a_item_ids, false, 'integer')." ".
			"AND grouping_id > ".$ilDB->quote(0,'integer')." ";
		$res = $ilDB->query($query);

		$grouping_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$grouping_ids[] = $row->grouping_id;
		}

		$query = "UPDATE ut_lp_collections ".
			"SET active = ".$ilDB->quote(1,'integer')." ".
			"WHERE ".$ilDB->in('grouping_id', $grouping_ids, false, 'integer');
		$ilDB->manipulate($query);
		return;
	}

	/**
	 * Create new grouping
	 * @global ilDB
	 */
	public static function createNewGrouping($a_obj_id, array $a_ids)
	{
		global $ilDB;

		// Activate each of this items
		self::activate($a_obj_id, $a_ids);

		// read all grouping ids and their item_ids
		$query = "SELECT DISTINCT(grouping_id) grp_id FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND ".$ilDB->in('item_id', $a_ids, false, 'integer')." ".
			"AND grouping_id != 0 ";
		$res = $ilDB->query($query);

		$grp_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$grp_ids[] = $row->grp_id;
		}

		$query = "SELECT item_id FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND ".$ilDB->in('grouping_id', $grp_ids, false, 'integer');
		$res = $ilDB->query($query);

		$all_item_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$all_item_ids[] = $row->item_id;
		}

		$all_item_ids = array_unique(array_merge($all_item_ids,$a_ids));

		// release grouping
		self::releaseGrouping($a_obj_id,$a_ids);

		// Create new grouping
		$query = "SELECT MAX(grouping_id) grp FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"GROUP BY obj_id ";
		$res = $ilDB->query($query);

		$grp_id = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$grp_id = $row->grp;
		}
		++$grp_id;

		$query = "UPDATE ut_lp_collections SET ".
			"grouping_id = ".$ilDB->quote($grp_id,'integer').", ".
			"num_obligatory = 1, ".
			"active = ".$ilDB->quote(1,'integer')." ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND ".$ilDB->in('item_id',$all_item_ids,false,'integer');
		$ilDB->manipulate($query);

		return;
	}

	/** 
	 * 
	 * @param <type> $a_obj_id
	 * @param array $a_obl 
	 * throws UnexpectedValueException
	 */
	public static function saveObligatoryMaterials($a_obj_id, array $a_obl)
	{
		global $ilDB;

		foreach($a_obl as $grouping_id => $num)
		{
			$query = "SELECT count(obj_id) num FROM ut_lp_collections ".
				'WHERE obj_id = '.$ilDB->quote($a_obj_id,'integer').' '.
				'AND grouping_id = '.$ilDB->quote($grouping_id,'integer').' '.
				'GROUP BY obj_id';
			$res = $ilDB->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				if($num <= 0 or $num >= $row->num)
				{
					throw new UnexpectedValueException();
				}
			}
		}
		foreach($a_obl as $grouping_id => $num)
		{
			$query = 'UPDATE ut_lp_collections '.
				'SET num_obligatory = '.$ilDB->quote($num, 'integer').' '.
				'WHERE obj_id = '.$ilDB->quote($a_obj_id,'integer').' '.
				'AND grouping_id = '.$ilDB->quote($grouping_id,'integer');
			$ilDB->manipulate($query);
		}
	}

	/**
	 * Release grouping of materials
	 * @param int obj_id
	 * @param array $a_ids
	 */
	public static function releaseGrouping($a_obj_id, array $a_ids)
	{
		global $ilDB;

		$query = "SELECT grouping_id FROM ut_lp_collections ".
			"WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
			"AND ".$ilDB->in('item_id', $a_ids, false, 'integer')." ".
			"AND grouping_id > 0 ";
		$res = $ilDB->query($query);

		$grp_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$grp_ids[] = $row->grouping_id;
		}

		$query = "UPDATE ut_lp_collections ".
			"SET grouping_id = ".$ilDB->quote(0,'integer').", ".
			"num_obligatory = 0 ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND " . $ilDB->in('grouping_id', $grp_ids, false, 'integer');
		$ilDB->manipulate($query);
		return true;
	}




	/**
	 * Check if there is any grouped material assigned.
	 * @global ilDB $ilDB
	 * @param int $a_obj_id
	 * @return bool
	 */
	public static function hasGroupedItems($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT item_id FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND grouping_id > 0 ";
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}

	/**
	 * Lookup grouped items
	 * @global ilDB $ilDB
	 * @param int $a_obj_id
	 * @param int $item_id
	 * @return array item ids grouped by grouping id
	 */
	public static function lookupGroupedItems($a_obj_id,$item_id)
	{
		global $ilDB;

		$query = "SELECT grouping_id FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND item_id = ".$ilDB->quote($item_id,'integer');
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		$grouping_id = $row->grouping_id;

		if($grouping_id == 0)
		{
			return array();
		}

		$query = "SELECT item_id, num_obligatory FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND grouping_id = ".$ilDB->quote($grouping_id,'integer');
		$res = $ilDB->query($query);

		$items = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$items['items'][] = $row->item_id;
			$items['num_obligatory'] = $row->num_obligatory;
			$items['grouping_id'] = $grouping_id;
		}
		return $items;
	}

	function delete($item_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM ut_lp_collections ".
			"WHERE item_id = ".$ilDB->quote($item_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ";
		$res = $ilDB->manipulate($query);

		$this->__read();

		return true;
	}


	// Static
	public static function _getPossibleItems($a_target_id)
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

	public static function _getCountPossibleItems($a_target_id)
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


	public static function _deleteAll($a_obj_id)
	{
		global $ilDB;

		$query = "DELETE FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')."";
		$res = $ilDB->manipulate($query);

		return true;
	}

	/**
	 * Get groped items
	 * @param int $a_obj_id
	 * @return array
	 */
	public static function getGroupedItems($a_obj_id, $a_use_subtree_by_id = false)
	{
		global $ilDB;

		$items = self::_getItems($a_obj_id, $a_use_subtree_by_id);

		$query = "SELECT * FROM ut_lp_collections ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND active = 1";
		$res = $ilDB->query($query);

		$grouped = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(in_array($row->item_id,$items))
			{
				$grouped[$row->grouping_id]['items'][] = $row->item_id;
				$grouped[$row->grouping_id]['num_obligatory'] = $row->num_obligatory;
			}
		}
		return $grouped;
	}

	function &_getItems($a_obj_id, $a_use_subtree_by_id = false)
	{
		global $ilObjDataCache;
		global $ilDB, $tree;

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
			if (!$a_use_subtree_by_id)
			{
				$possible_items = ilLPCollections::_getPossibleItems($course_ref_id);
			}
			else
			{
				$possible_items = $tree->getSubTreeIds($course_ref_id);
			}

			$query = "SELECT * FROM ut_lp_collections utc ".
				"JOIN object_reference obr ON item_id = ref_id ".
				"JOIN object_data obd ON obr.obj_id = obd.obj_id ".
				"WHERE utc.obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
				"AND active = ".$ilDB->quote(1,'integer')." ".
				"ORDER BY title";
		}
		else
		{
			// SAHS
			$query = "SELECT * FROM ut_lp_collections WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
				"AND active = ".$ilDB->quote(1,'integer');
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
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
			"AND item_id = ".$ilDB->quote($a_item_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		return true;
	}


	function __read()
	{
		global $ilObjDataCache, $ilDB;

		$this->items = array();

		if($ilObjDataCache->lookupType($this->getObjId()) != 'sahs')
		{
			$course_ref_ids = ilObject::_getAllReferences($this->getObjId());
			$course_ref_id = end($course_ref_ids);
			$query = "SELECT * FROM ut_lp_collections utc ".
				"JOIN object_reference obr ON item_id = ref_id ".
				"JOIN object_data obd ON obr.obj_id = obd.obj_id ".
				"WHERE utc.obj_id = ".$this->db->quote($this->obj_id,'integer')." ".
				"AND active = ".$ilDB->quote(1,'integer')." ".
				"ORDER BY title";
		}
		else
		{
			$query = "SELECT * FROM ut_lp_collections WHERE obj_id = ".$ilDB->quote($this->getObjId() ,'integer')." ".
				"AND active = ".$ilDB->quote(1,'integer');
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

	function _getScoresForUserAndCP_Node_Id ($target_id, $item_id, $user_id)
	{
		include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';

		switch(ilObjSAHSLearningModule::_lookupSubType($target_id))
		{
			case 'hacp':
			case 'aicc':
				include_once './Modules/ScormAicc/classes/class.ilObjAICCLearningModule.php';
				return ilObjAICCLearningModule::_getScoresForUser($item_id, $user_id);

			case 'scorm':
				include_once './Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php';
				//include_once './Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php';
				return ilObjSCORMLearningModule::_getScoresForUser($item_id, $user_id);

			case 'scorm2004':
				include_once './Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php';
				return ilObjSCORM2004LearningModule::_getScores2004ForUser($item_id, $user_id);
		}
		return array("raw" => null, "max" => null, "scaled" => null);
	}

}
?>