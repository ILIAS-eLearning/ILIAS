<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2.php";

/**
 * Help settings application class
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 * @version $Id$
 *
 * @ingroup ServicesHelp
 */
class ilObjHelpSettings extends ilObject2
{
	
	/**
	 * Constructor
	 * 
	 * @param	integer	reference_id or object_id
	 * @param	boolean	treat the id as reference_id (true) or object_id (false)
	 */
	function ilObjHelpSettings($a_id = 0,$a_call_by_reference = true)
	{
		parent::__construct($a_id,$a_call_by_reference);
	}

	/**
	 * Init type
	 */
	function initType()
	{
		$this->type = "hlps";
	}

	/**
	 * Create help module
	 *
	 * @param
	 * @return
	 */
	static function createHelpModule()
	{
		global $ilDB;
		
		$id = $ilDB->nextId("help_module");
		
		$ilDB->manipulate("INSERT INTO help_module ".
			"(id) VALUES (".
			$ilDB->quote($id, "integer").
			")");
		
		return $id;
	}
	
	/**
	 * Write help module lm id
	 *
	 * @param
	 * @return
	 */
	static function writeHelpModuleLmId($a_id, $a_lm_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE help_module SET ".
			" lm_id = ".$ilDB->quote($a_lm_id, "integer").
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
	}
	
	
	/**
	 * Upload help file
	 *
	 * @param
	 * @return
	 */
	function uploadHelpModule($a_file)
	{
		$id = $this->createHelpModule();
		
		// create and insert object in objecttree
		include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
		$newObj = new ilObjContentObject();
		$newObj->setType("lm");
		$newObj->setTitle("Help Module");
		$newObj->create(true);
		$newObj->createLMTree();
		
		self::writeHelpModuleLmId($id, $newObj->getId());

		// import help learning module
		$mess = $newObj->importFromZipFile($a_file["tmp_name"], $a_file["name"],
			false, $id);
	}
	
	/**
	 * Get help modules
	 *
	 * @param
	 * @return
	 */
	function getHelpModules()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM help_module");
		
		$mods = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			if (ilObject::_lookupType($rec["lm_id"]) == "lm")
			{
				$rec["title"] = ilObject::_lookupTitle($rec["lm_id"]);
				$rec["create_date"] = ilObject::_lookupCreationDate($rec["lm_id"]);
			}
			
			$mods[] = $rec;
		}
		
		return $mods;
	}
	
	/**
	 * lookup module title
	 *
	 * @param
	 * @return
	 */
	function lookupModuleTitle($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM help_module ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
		$rec  = $ilDB->fetchAssoc($set);
		if (ilObject::_lookupType($rec["lm_id"]) == "lm")
		{
			return ilObject::_lookupTitle($rec["lm_id"]);
		}
		return "";
	}
	
	/**
	 * lookup module lm id
	 *
	 * @param
	 * @return
	 */
	function lookupModuleLmId($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT lm_id FROM help_module ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
		$rec  = $ilDB->fetchAssoc($set);
		return $rec["lm_id"];
	}
	
	/**
	 * Delete module
	 *
	 * @param
	 * @return
	 */
	function deleteModule($a_id)
	{
		global $ilDB, $ilSetting;
		
		// if this is the currently activated one, deactivate it first
		if ($a_id == (int) $ilSetting->get("help_module"))
		{
			$ilSetting->set("help_module", "");
		}
		
		$set = $ilDB->query("SELECT * FROM help_module ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
		$rec  = $ilDB->fetchAssoc($set);

		// delete learning module
		if (ilObject::_lookupType($rec["lm_id"]) == "lm")
		{
			include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
			$lm = new ilObjLearningModule($rec["lm_id"], false);
			$lm->delete();
		}
		
		// delete mappings
		include_once("./Services/Help/classes/class.ilHelpMapping.php");
		ilHelpMapping::deleteEntriesOfModule($a_id);
		
		// delete tooltips
		include_once("./Services/Help/classes/class.ilHelp.php");
		ilHelp::deleteTooltipsOfModule($a_id);
		
		// delete help module record
		$ilDB->manipulate("DELETE FROM help_module WHERE ".
			" id = ".$ilDB->quote($a_id, "integer"));
		
	}

	/**
	 * Check if LM is a help LM
	 *
	 * @param integer $a_lm_id lm id
	 * @return bool true/false
	 */
	static function isHelpLM($a_lm_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT id FROM help_module ".
			" WHERE lm_id = ".$ilDB->quote($a_lm_id, "integer")
		);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return true;
		}
		return false;
	}

}
?>
