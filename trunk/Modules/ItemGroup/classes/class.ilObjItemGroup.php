<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";
require_once "Services/Object/classes/class.ilObjectActivation.php";

/**
 * Class ilObjItemGroup
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @extends ilObject2
 */
class ilObjItemGroup extends ilObject2
{
	protected $access_type; // [int]
	protected $access_begin; // [timestamp]
	protected $access_end; // [timestamp]
	protected $access_visibility; // [bool]
	
	/**
	 * Constructor
	 *
	 * @param int $a_id id
	 * @param bool $a_reference ref id?
	 * @return
	 */
	function __construct($a_id = 0, $a_reference = true) 
	{
		global $tree, $objDefinition;
		
		$this->tree = $tree;
		$this->obj_def = $objDefinition;
		
		parent::__construct($a_id, $a_reference);			
	}
	
	/**
	 * Init type
	 */
	function initType()
	{
		$this->type = "itgr";
	}
	
	/**
	 * Read
	 */
	protected function doRead()
	{
		global $ilDB;

		/*$set = $ilDB->query("SELECT * FROM il_poll".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));
		$row = $ilDB->fetchAssoc($set);*/
		
		if ($this->ref_id)
		{
/*			$activation = ilObjectActivation::getItem($this->ref_id);			
			$this->setAccessType($activation["timing_type"]);
			$this->setAccessBegin($activation["timing_start"]);
			$this->setAccessEnd($activation["timing_end"]);							
			$this->setAccessVisibility($activation["visible"]);*/							
		}
	}
	
	/**
	 * Get properties array
	 */
	protected function propertiesToDB()
	{
		$fields = array(
		);
		
		return $fields;
	}

	/**
	 * Creation
	 */
	protected function doCreate()
	{
		global $ilDB;
		
		if($this->getId())
		{
			$fields = $this->propertiesToDB();
//			$fields["id"] = array("integer", $this->getId());

//			$ilDB->insert("il_poll", $fields);
			
			
			// object activation default entry will be created on demand
			
			
		}
	}
		
	/**
	 * Update
	 */
	protected function doUpdate()
	{
		global $ilDB;
	
		if($this->getId())
		{
			$fields = $this->propertiesToDB();
			
//			$ilDB->update("il_poll", $fields,
//				array("id"=>array("integer", $this->getId())));
			
			
			if($this->ref_id)
			{
/*				$activation = new ilObjectActivation();
				$activation->setTimingType($this->getAccessType());
				$activation->setTimingStart($this->getAccessBegin());
				$activation->setTimingEnd($this->getAccessEnd());
				$activation->toggleVisible($this->getAccessVisibility());
				$activation->update($this->ref_id);*/
			}
			
		}
	}

	/**
	 * Deletion
	 */
	protected function doDelete()
	{
		global $ilDB;
		
		if($this->getId())
		{		
			if($this->ref_id)
			{
//				ilObjectActivation::deleteAllEntries($this->ref_id);
			}
			
//			$ilDB->manipulate("DELETE FROM il_poll".
//				" WHERE id = ".$ilDB->quote($this->id, "integer"));
		}
	}
	
	/**
	 * Clone obj item group
	 *
	 * @param
	 * @return
	 */
	protected function doCloneObject($new_obj, $a_target_id, $a_copy_id)
	{
		
	}

	/**
	 * Clone dependencies
	 *
	 * @param
	 * @return
	 */
	function cloneDependencies($a_target_id,$a_copy_id)
	{
		global $ilLog;
		
		$ilLog->write(__METHOD__.': Cloning item group dependencies -'.$a_source_id.'-');
		
		parent::cloneDependencies($a_target_id,$a_copy_id);

		include_once('./Modules/ItemGroup/classes/class.ilItemGroupItems.php');
		$ig_items = new ilItemGroupItems($a_target_id);
		$ig_items->cloneItems($this->getRefId(), $a_copy_id);

		return true;
	}

	/**
	 * Fix container item group references after a container has been cloned
	 *
	 * @param
	 * @return
	 */
	static function fixContainerItemGroupRefsAfterCloning($a_source_container, $a_copy_id)
	{
		global $ilLog;
		
		$ilLog->write(__METHOD__.': Fix item group references in '.$a_source_container->getType());
		
	 	include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
	 	$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
	 	$mappings = $cwo->getMappings();
	 		 	
	 	$new_container_ref_id = $mappings[$a_source_container->getRefId()];
	 	$ilLog->write(__METHOD__.': 2-'.$new_container_ref_id.'-');
	 	$new_container_obj_id = ilObject::_lookupObjId($new_container_ref_id);
	 	
		include_once("./Services/COPage/classes/class.ilPageObject.php");
		include_once("./Services/Container/classes/class.ilContainerPage.php");
		$ilLog->write(__METHOD__.': 3'.$new_container_obj_id.'-');
	 	if (ilPageObject::_exists("cont", $new_container_obj_id))
	 	{
			$ilLog->write(__METHOD__.': 4');
	 		$new_page = new ilContainerPage($new_container_obj_id);
			$new_page->buildDom();
			include_once("./Services/COPage/classes/class.ilPCResources.php");
			ilPCResources::modifyItemGroupRefIdsByMapping($new_page, $mappings);
			$new_page->update();
		}
		$ilLog->write(__METHOD__.': 5');
	}
}

?>