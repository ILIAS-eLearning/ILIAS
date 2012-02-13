<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObject2.php";

/**
 * Taxonomy
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * $Id$
 *
 */
class ilObjTaxonomy extends ilObject2
{
	/**
	 * Constructor
	 *
	 * @param	integer	object id
	 */
	function ilObjTaxonomy($a_id = 0)
	{
		parent::ilObject($a_id, false);
	}

	/**
	 * Init type
	 *
	 * @param
	 * @return
	 */
	function initType()
	{
		$this->setType("tax");
	}
	
	/**
	 * Create a new taxonomy
	 */
	function doCreate()
	{
		global $ilDB;

	}
	
	/**
	 * clone taxonomy sheet (note: taxonomies have no ref ids and return an object id)
	 * 
 	 * @access	public
	 * @return	integer		new obj id
	 */
	function doCloneObject($a_new_obj, $a_target_id, $a_copy_id)
	{
		global $log, $lng;
		

	}


	/**
	 * Delete taxonomy object
	 */
	function doDelete()
	{
		global $ilDB;
		
		// delete object
		
	}


	/**
	 * Read taxonomy properties
	 */
	function doRead()
	{
		global $ilDB;
		
		//
	}

	/**
	 * Upate taxonomy properties
	 */
	function doUpdate()
	{
		global $ilDB;
		
		//
	}

	/**
	 * Save Usage
	 *
	 * @param
	 * @return
	 */
	static function saveUsage($a_tax_id, $a_obj_id)
	{
		global $ilDB;
		
		$ilDB->replace("tax_usage",
			array("tax_id" => array("integer" => $a_tax_id),
				"obj_id" => array("integer" => $a_obj_id)
				),
			array()
			);
	}
	
	/**
	 * Get usage of object
	 *
	 * @param int $a_obj_id object id
	 * @return array array of taxonomies
	 */
	static function getUsageOfObject($a_obj_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT obj_id FROM tax_usage ".
			" WHERE tax_id = ".$ilDB->quote($a_obj_id, "integer")
			);
		$obj = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$obj[] = $rec["obj_id"];
		}
		return $obj;
	}
	
}
?>
