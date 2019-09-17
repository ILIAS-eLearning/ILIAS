<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilContainerStartObjects
 * 
 * @author Stefan Meyer <meyer@leifos.com> 
 * @version $Id: class.ilCourseStart.php 44362 2013-08-22 08:36:03Z jluetzen $
 *
 * @ingroup ServicesContainer
 */
class ilContainerStartObjects
{	
	/**
	 * @var ilTree
	 */
	protected $tree;

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var ilObjectDataCache
	 */
	protected $obj_data_cache;

	/**
	 * @var Logger
	 */
	protected $log;

	protected $ref_id;
	protected $obj_id;
	protected $start_objs = array();

	public function __construct($a_object_ref_id, $a_object_id)
	{		
		global $DIC;

		$this->tree = $DIC->repositoryTree();
		$this->db = $DIC->database();
		$this->obj_data_cache = $DIC["ilObjDataCache"];
		$this->log = $DIC["ilLog"];
		$this->setRefId($a_object_ref_id);
		$this->setObjId($a_object_id);

		$this->__read();
	}
	
	protected function setObjId($a_id)
	{
		$this->obj_id = $a_id;
	}
	
	public function getObjId()
	{
		return $this->obj_id;
	}
	
	protected function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	
	public function getRefId()
	{
		return $this->ref_id;
	}
	
	public function getStartObjects()
	{
		return $this->start_objs ? $this->start_objs : array();
	}
		
	protected function __read()
	{
		$tree = $this->tree;
		$ilDB = $this->db;

		$this->start_objs = array();

		$query = "SELECT * FROM crs_start".
			" WHERE crs_id = ".$ilDB->quote($this->getObjId(), 'integer').
			" ORDER BY pos, crs_start_id";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{			
			if($tree->isInTree($row->item_ref_id))
			{
				$this->start_objs[$row->crs_start_id]['item_ref_id'] = $row->item_ref_id;
			}
			else
			{
				$this->delete($row->item_ref_id);
			}
		}
		return true;
	}
	
	/**
	 * Delete item by sequence id
	 * @param type $a_crs_start_id
	 * @return boolean
	 */
	public function delete($a_crs_start_id)
	{
		$ilDB = $this->db;
		
		$query = "DELETE FROM crs_start".
			" WHERE crs_start_id = ".$ilDB->quote($a_crs_start_id, 'integer').
			" AND crs_id = ".$ilDB->quote($this->getObjId(), 'integer');
		$ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * Delete item by ref_id
	 * @param type $a_item_ref_id
	 */
	public function deleteItem($a_item_ref_id)
	{
		$ilDB = $this->db;
		
		$query = "DELETE FROM crs_start".
			" WHERE crs_id = ".$ilDB->quote($this->getObjId(), 'integer').
			" AND item_ref_id = ".$ilDB->quote($a_item_ref_id, 'integer');
		$ilDB->manipulate($query);
		return true;
	}

	public function exists($a_item_ref_id)
	{
		$ilDB = $this->db;
		
		$query = "SELECT * FROM crs_start".
			" WHERE crs_id = ".$ilDB->quote($this->getObjId(), 'integer').
			" AND item_ref_id = ".$ilDB->quote($a_item_ref_id, 'integer');
		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}

	public function add($a_item_ref_id)
	{
		$ilDB = $this->db;
		
		if($a_item_ref_id)
		{
			$max_pos = $ilDB->query("SELECT max(pos) pos FROM crs_start".
				" WHERE crs_id = ".$ilDB->quote($this->getObjId(), "integer"));
			$max_pos = $ilDB->fetchAssoc($max_pos);
			$max_pos = ((int)$max_pos["pos"])+10;
			
			$next_id = $ilDB->nextId('crs_start');
			$query = "INSERT INTO crs_start".
				" (crs_start_id,crs_id,item_ref_id,pos)".
				" VALUES".
				" (".$ilDB->quote($next_id, 'integer').
				", ".$ilDB->quote($this->getObjId(), 'integer').
				", ".$ilDB->quote($a_item_ref_id, 'integer').
				", ".$ilDB->quote($max_pos, 'integer').
				")";
			$ilDB->manipulate($query);
			return true;
		}
		return false;
	}

	public function __deleteAll()
	{
		$ilDB = $this->db;
		
		$query = "DELETE FROM crs_start".
			" WHERE crs_id = ".$ilDB->quote($this->getObjId(), 'integer');
		$ilDB->manipulate($query);
		return true;
	}
		
	public function setObjectPos($a_start_id, $a_pos)
	{
		$ilDB = $this->db;
		
		if(!(int)$a_start_id || !(int)$a_pos)
		{
			return;
		}
		
		$ilDB->manipulate("UPDATE crs_start".
			" SET pos = ".$ilDB->quote($a_pos, "integer").
			" WHERE crs_id = ".$ilDB->quote($this->getObjId(), 'integer').
			" AND crs_start_id = ".$ilDB->quote($a_start_id, 'integer'));
	}

	public function getPossibleStarters()
	{
		include_once "Services/Object/classes/class.ilObjectActivation.php";
		foreach(ilObjectActivation::getItems($this->getRefId(), false) as $node)
		{
			switch($node['type'])
			{
				case 'lm':
				case 'sahs':
				case 'copa':
				case 'svy':
				case 'tst':
					$poss_items[] = $node['ref_id'];
					break;
			}
		}
		return $poss_items ? $poss_items : array();
	}	

	public function allFullfilled($a_user_id)
	{
		foreach($this->getStartObjects() as $item)
		{
			if(!$this->isFullfilled($a_user_id, $item['item_ref_id']))
			{
				return false;
			}
		}
		return true;
	}

	public function isFullfilled($a_user_id, $a_item_id)
	{
		$ilObjDataCache = $this->obj_data_cache;

		$obj_id = $ilObjDataCache->lookupObjId($a_item_id);
		$type = $ilObjDataCache->lookupType($obj_id);
		
		switch($type)
		{
			case 'tst':
				include_once './Modules/Test/classes/class.ilObjTestAccess.php';				
				if(!ilObjTestAccess::checkCondition($obj_id,'finished','',$a_user_id)) // #14000
				{
					return false;
				}
				break;
				
			case 'svy':
				
				include_once './Modules/Survey/classes/class.ilObjSurveyAccess.php';
				if(!ilObjSurveyAccess::_lookupFinished($obj_id, $a_user_id))
				{
					return false;
				}
				break;
				
			case 'sahs':
				include_once 'Services/Tracking/classes/class.ilLPStatus.php';
				if(!ilLPStatus::_hasUserCompleted($obj_id, $a_user_id))
				{
					return false;
				}
				break;

			case 'copa':
				if (!ilLPStatus::_hasUserCompleted($obj_id, $a_user_id)) {
					return false;
				}
				break;

			default:				
				include_once './Modules/Course/classes/class.ilCourseLMHistory.php';
				$lm_continue = new ilCourseLMHistory($this->getRefId(), $a_user_id);
				$continue_data = $lm_continue->getLMHistory();
				if(!isset($continue_data[$a_item_id]))
				{
					return false;
				}
				break;
		}
		
		return true;
	}
		
	public function cloneDependencies($a_target_id, $a_copy_id)
	{
		$ilObjDataCache = $this->obj_data_cache;
		$ilLog = $this->log;
		
		$ilLog->write(__METHOD__.': Begin course start objects...');
		
		$new_obj_id = $ilObjDataCache->lookupObjId($a_target_id);
		$start = new self($a_target_id, $new_obj_id);
		
	 	include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
	 	$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
	 	$mappings = $cwo->getMappings();
	 	foreach($this->getStartObjects() as $data)
	 	{
	 		$item_ref_id = $data['item_ref_id'];
	 		if(isset($mappings[$item_ref_id]) and $mappings[$item_ref_id])
	 		{
				$ilLog->write(__METHOD__.': Clone start object nr. '.$item_ref_id);
	 			$start->add($mappings[$item_ref_id]);
	 		}
	 		else
	 		{
				$ilLog->write(__METHOD__.': No mapping found for start object nr. '.$item_ref_id);
	 		}
	 	}
		$ilLog->write(__METHOD__.': ... end course start objects');
	 	return true;
	}
	
	/**
	 * Check if object is start object
	 * @param type $a_container_id
	 * @param type $a_item_ref_id
	 * @return boolean
	 */
	public static function isStartObject($a_container_id, $a_item_ref_id)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$query = 'SELECT crs_start_id FROM crs_start '.
			'WHERE crs_id = '.$ilDB->quote($a_container_id,'integer').' '.
			'AND item_ref_id = '.$ilDB->quote($a_item_ref_id,'integer');
		$res = $ilDB->query($query);
		if($res->numRows() >= 1)
		{
			return true;
		}
		return false;
	}
	
} 

?>