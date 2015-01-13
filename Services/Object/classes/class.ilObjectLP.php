<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Tracking/classes/class.ilLPObjSettings.php";

/**
 * Base class for object lp connectors 
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ServicesTracking
 */
class ilObjectLP
{	
	protected $obj_id; // [int]
	protected $collection_instance; // [ilLPCollection]
	protected $mode; // [int]
	
	protected function __construct($a_obj_id)
	{		
		$this->obj_id = (int)$a_obj_id;
	}
	
	public static function getInstance($a_obj_id)
	{
		global $objDefinition;
		
		static $instances = array();
		
		if(!isset($instances[$a_obj_id]))
		{		
			$type = ilObject::_lookupType($a_obj_id);
			
			// see self::isSupportedObjectType()
			
			switch($type)
			{
				// container

				case "crs":
					include_once "Modules/Course/classes/class.ilCourseLP.php";
					$instance = new ilCourseLP($a_obj_id);	
					break;

				case "grp":
					include_once "Modules/Group/classes/class.ilGroupLP.php";
					$instance = new ilGroupLP($a_obj_id);
					break;

				case "fold":
					include_once "Modules/Folder/classes/class.ilFolderLP.php";
					$instance = new ilFolderLP($a_obj_id);
					break;


				// learning resources

				case "lm":
					include_once "Modules/LearningModule/classes/class.ilLearningModuleLP.php";
					$instance = new ilLearningModuleLP($a_obj_id);
					break;

				case "htlm":
					include_once "Modules/HTMLLearningModule/classes/class.ilHTMLLearningModuleLP.php";
					$instance = new ilHTMLLearningModuleLP($a_obj_id);
					break;

				case "sahs":
					include_once "Modules/ScormAicc/classes/class.ilScormLP.php";
					$instance = new ilScormLP($a_obj_id);
					break;


				// misc

				case "tst":
					include_once "Modules/Test/classes/class.ilTestLP.php";
					$instance = new ilTestLP($a_obj_id);
					break;

				case "exc":
					include_once "Modules/Exercise/classes/class.ilExerciseLP.php";
					$instance = new ilExerciseLP($a_obj_id);
					break;

				case "sess":
					include_once "Modules/Session/classes/class.ilSessionLP.php";
					$instance = new ilSessionLP($a_obj_id);
					break;

				// plugin
				case $objDefinition->isPluginTypeName($type):
					include_once "Services/Component/classes/class.ilPluginLP.php";
					$instance = new ilPluginLP($a_obj_id);
					break;

				default:
					// :TODO: should we return anything?
					$instance = new self($a_obj_id);			
					break;
			}
			
			$instances[$a_obj_id] = $instance;					
		}
	
		return $instances[$a_obj_id];
	}
		
	public static function isSupportedObjectType($a_type)
	{
		global $objDefinition;
		
		$valid = array("crs", "grp", "fold", "lm", "htlm", "sahs", "tst", "exc", "sess");		
		if(in_array($a_type, $valid))
		{
			return true;			
		}
		
		return $objDefinition->isPluginTypeName($a_type);		
	}	
		
	public function resetCaches()
	{
		$this->mode = null;
		$this->collection_instance = null;
	}
	
	public function isAnonymized()
	{
		// see ilLPCollectionOfRepositoryObjects::validateEntry()
		return false;
	}
	
	
	//
	// MODE
	// 
	
	public function getDefaultMode()
	{
		return ilLPObjSettings::LP_MODE_UNDEFINED;
	}
	
	public function getValidModes()
	{
		return array();
	}	
	
	public function getCurrentMode()
	{		
		if($this->mode === null)
		{				
			$mode = ilLPObjSettings::_lookupDBMode($this->obj_id);		
			if($mode === null)
			{
				$mode = $this->getDefaultMode();
			}		
			$this->mode = (int)$mode;
		}
		
		return $this->mode;
	}
	
	public function isActive()
	{
		// :TODO: check LP activation?
		
		$mode = $this->getCurrentMode();
		if($mode == ilLPObjSettings::LP_MODE_DEACTIVATED || 
			$mode == ilLPObjSettings::LP_MODE_UNDEFINED)
		{
			return false;
		}
		return true;
	}
	
	public function getModeText($a_mode)
	{		
		return ilLPObjSettings::_mode2Text($a_mode);		
	}
	
	public function getModeInfoText($a_mode)
	{
		return ilLPObjSettings::_mode2InfoText($a_mode);		
	}
	
	
	//
	// COLLECTION
	// 
		
	public function getCollectionInstance()
	{				
		// :TODO: factory if not plugin ?!
		// => move to ilLPCollection::getInstance() ?!
		
		if($this->collection_instance === null)
		{		
			$path = "Services/Tracking/classes/collection/";
			
			$mode = $this->getCurrentMode();		
			switch($mode)
			{
				case ilLPObjSettings::LP_MODE_COLLECTION:
				case ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR:		
					include_once $path."class.ilLPCollectionOfRepositoryObjects.php";
					$this->collection_instance = new ilLPCollectionOfRepositoryObjects($this->obj_id, $mode);		
					break;

				case ilLPObjSettings::LP_MODE_OBJECTIVES:
					include_once $path."class.ilLPCollectionOfObjectives.php";
					$this->collection_instance = new ilLPCollectionOfObjectives($this->obj_id, $mode);		
					break;

				case ilLPObjSettings::LP_MODE_SCORM:	
					include_once $path."class.ilLPCollectionOfSCOs.php";
					$this->collection_instance = new ilLPCollectionOfSCOs($this->obj_id, $mode);		
					break;

				case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:	
				case ilLPObjSettings::LP_MODE_COLLECTION_TLT:	
					include_once $path."class.ilLPCollectionOfLMChapters.php";
					$this->collection_instance = new ilLPCollectionOfLMChapters($this->obj_id, $mode);	
					break;
				
				default:
					$this->collection_instance = false;
					break;
			}
		}
		
		return $this->collection_instance;		
	}
			
	
	//
	// MEMBERS
	// 
	
	public function getMembers($a_search = true)
	{		
		global $tree;
		
		if(!$a_search)
		{
			return;
		}
		
		$ref_ids = ilObject::_getAllReferences($this->obj_id);
		$ref_id = current($ref_ids);
		
		// walk path to find parent with specific members 
		$path = $tree->getPathId($ref_id);
		array_pop($path);
		foreach(array_reverse($path) as $path_ref_id)
		{
			$olp = self::getInstance(ilObject::_lookupObjId($path_ref_id));
			$all = $olp->getMembers(false);
			if(is_array($all))
			{
				return $all;
			}
		}		
	}
	
	
	//
	// RESET
	//
	
	final public function resetLPDataForCompleteObject($a_recursive = true)
	{				
		$user_ids = $this->gatherLPUsers();
		if(sizeof($user_ids))
		{
			$this->resetLPDataForUserIds(array_unique($user_ids), $a_recursive);
		}		
	}
	
	final public function resetLPDataForUserIds(array $a_user_ids, $a_recursive = true)
	{				
		if((bool)$a_recursive && 
			method_exists($this, "getPossibleCollectionItems")) // #15203
		{
			$subitems = $this->getPossibleCollectionItems();
			if(is_array($subitems))
			{
				foreach($subitems as $sub_ref_id)
				{
					$olp = self::getInstance(ilObject::_lookupObjId($sub_ref_id));
					$olp->resetLPDataForUserIds($a_user_ids, false);
				}
			}
		}
		
		$this->resetCustomLPDataForUserIds($a_user_ids, (bool)$a_recursive);
						
		include_once "Services/Tracking/classes/class.ilLPMarks.php";
		ilLPMarks::_deleteForUsers($this->obj_id, $a_user_ids);

		include_once "Services/Tracking/classes/class.ilChangeEvent.php";
		ilChangeEvent::_deleteReadEventsForUsers($this->obj_id, $a_user_ids);		
				
		// update LP status to get collections up-to-date
		include_once "Services/Tracking/classes/class.ilLPStatusWrapper.php";	
		foreach($a_user_ids as $user_id)
		{
			ilLPStatusWrapper::_updateStatus($this->obj_id, $user_id);
		}
	}
		
	protected function resetCustomLPDataForUserIds(array $a_user_ids, $a_recursive = true)
	{
		// this should delete all data that is relevant for the supported LP modes
	}
	
	protected function gatherLPUsers()
	{
		include_once "Services/Tracking/classes/class.ilLPMarks.php";
		$user_ids = ilLPMarks::_getAllUserIds($this->obj_id);
		
		include_once "Services/Tracking/classes/class.ilChangeEvent.php";
		$user_ids = array_merge($user_ids, ilChangeEvent::_getAllUserIds($this->obj_id));		
		
		return $user_ids;
	}
	
	
	//
	// EVENTS
	// 
		
	final static public function handleMove($a_source_ref_id)
	{	
		global $tree, $ilDB;
		
		$ref_ids = $tree->getSubTreeIds($a_source_ref_id);
		$ref_ids[] = $a_source_ref_id;
		
		// get "parent" path to source node (not including source node)
		$new_path = $tree->getPathId($a_source_ref_id);
		array_pop($new_path);
		$new_path = implode("/", $new_path);
	
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		
		// find collections with ref_ids		
		$set = $ilDB->query("SELECT DISTINCT(ut_lp_collections.obj_id) obj_id".
			" FROM object_reference".
			" JOIN ut_lp_collections ON".
			" (".$ilDB->in("object_reference.ref_id", $ref_ids, "", "integer").
			" AND object_reference.ref_id = ut_lp_collections.item_id)");
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if (in_array(ilObject::_lookupType($rec["obj_id"]), array("crs", "grp", "fold")))
			{
				$coll_ref_id = ilObject::_getAllReferences($rec["obj_id"]);
				$coll_ref_id = array_pop($coll_ref_id);
				
				// #13402
				if($coll_ref_id == $a_source_ref_id)
				{
					continue;
				}
				
				// get path to collection (including collection "parent")
				$coll_path = $tree->getPathId($coll_ref_id);
				$coll_path = implode("/", $coll_path);
				
				// collection path is not inside new path
				if(!stristr($new_path, $coll_path))
				{
					// delete all items of moved (sub-)tree
					$query = "DELETE FROM ut_lp_collections".
						" WHERE obj_id = ".$ilDB->quote($rec["obj_id"], "integer").
						" AND ".$ilDB->in("item_id", $ref_ids, "", "integer");
					$ilDB->manipulate($query);
					
					ilLPStatusWrapper::_refreshStatus($rec["obj_id"]);			
				}
			}
		}		
	}
	
	final public function handleToTrash()
	{			
		$this->updateParentCollections();		
	}
	
	final public function handleDelete()
	{				
		include_once "Services/Tracking/classes/class.ilLPMarks.php";
		ilLPMarks::deleteObject($this->obj_id);

		include_once "Services/Tracking/classes/class.ilChangeEvent.php";
		ilChangeEvent::_delete($this->obj_id);		
		
		$collection = $this->getCollectionInstance();
		if($collection)
		{
			$collection->delete();
		}
		
		$this->updateParentCollections();
	}
	
	final protected function updateParentCollections()
	{
		global $ilDB;
		
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		
		// update parent collections?		
		$set = $ilDB->query("SELECT ut_lp_collections.obj_id obj_id FROM ".
				"object_reference JOIN ut_lp_collections ON ".
				"(object_reference.obj_id = ".$ilDB->quote($this->obj_id, "integer").
				" AND object_reference.ref_id = ut_lp_collections.item_id)");
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if (in_array(ilObject::_lookupType($rec["obj_id"]), array("crs", "grp", "fold")))
			{				
				// remove from parent collection
				$query = "DELETE FROM ut_lp_collections".
					" WHERE obj_id = ".$ilDB->quote($rec["obj_id"], "integer").
					" AND item_id = ".$ilDB->quote($this->obj_id, "integer");
				$ilDB->manipulate($query);
				
				ilLPStatusWrapper::_refreshStatus($rec["obj_id"]);				
			}
		}
	}
}

?>