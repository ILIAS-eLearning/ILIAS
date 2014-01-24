<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Asset.php");

/**
* Class ilSCORM2004Sco
*
* SCO class for SCORM 2004 Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004Sco extends ilSCORM2004Asset
{
	protected $hide_obj_page = false;

	/**
	 * Constructor
	 *
	 * @param object SCORM LM object
	 */
	function __construct($a_slm_object, $a_id = 0)
	{
		parent::__construct($a_slm_object, $a_id);
		$this->setType("sco");
	}

	/**
	 * Set hide objective page
	 */
	public function setHideObjectivePage($a_val)
	{
		$this->hide_obj_page = $a_val;
	}

	/**
	 * Get hide objective page
	 */
	public function getHideObjectivePage()
	{
		return $this->hide_obj_page;
	}

	/**
	 * Create sco
	 */
	function create($a_upload = false, $a_template = false)
	{
		global $ilDB;
		
		parent::create($a_upload, $a_template);
		if (!$a_template)
		{
			$obj = new ilSCORM2004Objective($this->getId());
//			$obj->setObjectiveID("Objective SCO ".$this->getId());
			$obj->setId("local_obj_".$this->getID()."_0");
			$obj->update();
		}
		$ilDB->manipulate("INSERT INTO sahs_sc13_sco ".
			"(id, hide_obj_page) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote($this->getHideObjectivePage(), "integer").
			")");
	}

	/**
	 * Read data from database
	 */
	function read()
	{
		global $ilDB;

		parent::read();
		$set = $ilDB->query("SELECT * FROM sahs_sc13_sco WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		$this->setHideObjectivePage($rec["hide_obj_page"]);
	}

		/**
	 * Update
	 */
	function update()
	{
		global $ilDB;

		parent::update();
		$ilDB->manipulate("UPDATE sahs_sc13_sco SET ".
			" hide_obj_page = ".$ilDB->quote($this->getHideObjectivePage(), "integer").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
	}

	/**
	 * Delete
	 */
	function delete()
	{
		global $ilDB;

		parent::delete();
		$ilDB->manipulate("DELETE FROM sahs_sc13_sco WHERE "
			. " id = " . $ilDB->quote($this->getId(), "integer")
			);
	}

	/**
	 * Copy sco
	 */
	function copy($a_target_slm)
	{
		$sco = new ilSCORM2004Sco($a_target_slm);
		$sco->setTitle($this->getTitle());
		if ($this->getSLMId() != $a_target_slm->getId())
		{
			$sco->setImportId("il__sco_".$this->getId());
		}
		$sco->setSLMId($a_target_slm->getId());
		$sco->setType($this->getType());
		$sco->setDescription($this->getDescription());
		$sco->create(true);
		$a_copied_nodes[$this->getId()] = $sco->getId();

		// copy meta data
		include_once("Services/MetaData/classes/class.ilMD.php");
		$md = new ilMD($this->getSLMId(), $this->getId(), $this->getType());
		$new_md =& $md->cloneMD($a_target_slm->getId(), $sco->getId(), $this->getType());

		return $sco;
	}

	/**
	 * Get main objective
	 *
	 * @todo: This should be saved in a better way in the future
	 */
	function getMainObjectiveText()
	{
		$objectives = $this->getObjectives();

		foreach ($objectives as $ob)
		{
			// map info
			$mappings = $ob->getMappings();
			$mapinfo = null;
			foreach($mappings as $map)
			{
				$mapinfo .= $map->getTargetObjectiveID();
			}

			if ($mapinfo == null)
			{
				$mapinfo = "local";
			}
			else
			{
				$mapinfo = "global to ".$mapinfo;
			}

			if ($mapinfo == "local")
			{
				return $ob->getObjectiveID();
			}
		}
	}
	

}
?>