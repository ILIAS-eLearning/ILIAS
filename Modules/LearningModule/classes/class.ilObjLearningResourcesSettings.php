<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjLearningResourcesSettings
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
* @ingroup ModuleLearningModule
*/
class ilObjLearningResourcesSettings extends ilObject
{
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function __construct($a_id = 0,$a_call_by_reference = true)
	{
		global $DIC;

		$this->db = $DIC->database();
		$this->type = "lrss";
		parent::__construct($a_id,$a_call_by_reference);
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		$ilDB = $this->db;
		
		if (!parent::update())
		{			
			return false;
		}

		return true;
	}
	
	/**
	* read style folder data
	*/
	function read()
	{
		$ilDB = $this->db;

		parent::read();

	}
	

	
	

	/**
	* delete object and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{		
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		
		//put here your module specific stuff
		
		return true;
	}

} // END class.ilObjLearningResourcesSettings
?>
