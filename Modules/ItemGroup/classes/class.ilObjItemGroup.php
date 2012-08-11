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
}

?>