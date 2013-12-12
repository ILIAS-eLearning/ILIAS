<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/interfaces/interface.ilSkillUsageInfo.php");

/**
 * Manages resources for skills. This is not about user assigned materials,
 * it is about resources that are assigned to skill levels in the
 * competence management administration of ILIAS.
 *
 * This can be either triggers (e.g. a course that triggers a competence level)
 * or resources that impart the knowledge of a competence level. Imparting
 * does not necessarily mean that it triggers a competence level.
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup Services/Skill
 */
class ilSkillResources implements ilSkillUsageInfo
{
	protected $base_skill_id;	// base skill id
	protected $tref_id;			// template reference id (if no template involved: 0)
	
	// The resources array has the following keys (int)
	// first dimension is "level_id" (int): the skill level id
	// second dimension is "rep_ref_id" (int): the ref id of the repository resource
	//
	// The values of the array are associatives arrays with the following key value pairs:
	// level_id (int): the skill level id
	// rep_ref_id (int): the ref id of the repository resource
	// trigger: 1, if the resource triggers the skill level (0 otherwise)
	// imparting: 1, if the resource imparts knowledge of the skill level (0 otherwise)
	protected $resources = array();
	
	/**
	 * Constructor
	 *
	 * @param int $a_skill_id base skill id
	 * @param int $a_tref_id template reference id (0, if no template is involved)
	 */
	function __construct($a_skill_id = 0, $a_tref_id = 0)
	{
		$this->setBaseSkillId($a_skill_id);
		$this->setTemplateRefId($a_tref_id);
		
		if ($a_skill_id > 0)
		{
			$this->readResources();
		}
	}
	
	/**
	 * Set base skill id
	 *
	 * @param int $a_val base skill id	
	 */
	function setBaseSkillId($a_val)
	{
		$this->base_skill_id = (int) $a_val;
	}
	
	/**
	 * Get base skill id
	 *
	 * @return int base skill id
	 */
	function getBaseSkillId()
	{
		return $this->base_skill_id;
	}
	
	/**
	 * Set template reference id
	 *
	 * @param int $a_val template reference id	
	 */
	function setTemplateRefId($a_val)
	{
		$this->tref_id = (int) $a_val;
	}
	
	/**
	 * Get template reference id
	 *
	 * @return int template reference id
	 */
	function getTemplateRefId()
	{
		return $this->tref_id;
	}
	
	/**
	 * Read resources
	 *
	 * @param
	 * @return
	 */
	function readResources()
	{
		global $ilDB, $tree;
		
		$set = $ilDB->query("SELECT * FROM skl_skill_resource ".
			" WHERE base_skill_id = ".$ilDB->quote($this->getBaseSkillId(), "integer").
			" AND tref_id = ".$ilDB->quote($this->getTemplateRefId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if ($tree->isInTree($rec["rep_ref_id"]))
			{
				$this->resources[$rec["level_id"]][$rec["rep_ref_id"]] = array(
					"level_id" => $rec["level_id"],
					"rep_ref_id" => $rec["rep_ref_id"],
					"trigger" => $rec["ltrigger"],
					"imparting" => $rec["imparting"]
					);
			}
		}
	}
	
	/**
	 * Save resources
	 */
	function save()
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM skl_skill_resource WHERE ".
			" base_skill_id = ".$ilDB->quote((int) $this->getBaseSkillId(), "integer").
			" AND tref_id = ".$ilDB->quote((int) $this->getTemplateRefId(), "integer")
			);
		foreach ($this->getResources() as $level_id => $l)
		{
			foreach ($l as $ref_id => $r)
			{
				if ($r["imparting"] || $r["trigger"])
				{
					$ilDB->manipulate("INSERT INTO skl_skill_resource ".
						"(base_skill_id, tref_id, level_id, rep_ref_id, imparting, ltrigger) VALUES (".
						$ilDB->quote((int) $this->getBaseSkillId(), "integer").",".
						$ilDB->quote((int) $this->getTemplateRefId(), "integer").",".
						$ilDB->quote((int) $level_id, "integer").",".
						$ilDB->quote((int) $ref_id, "integer").",".
						$ilDB->quote((int) $r["imparting"], "integer").",".
						$ilDB->quote((int) $r["trigger"], "integer").
						")");
				}
			}
		}
	}
	
	/**
	 * Get resources
	 *
	 * @return
	 */
	function getResources()
	{
		return $this->resources;
	}
	
	/**
	 * Get resoures for level id
	 *
	 * @param int $a_level_id level id
	 * @return array array of resources
	 */
	function getResourcesOfLevel($a_level_id)
	{
		$ret = (is_array($this->resources[$a_level_id]))
			? $this->resources[$a_level_id]
			: array();
			
		return $ret;
	}
	
	/**
	 * Set resource as trigger
	 *
	 * @param int $a_level_id level id
	 * @param int $a_rep_ref_id repository resource ref id
	 * @param bool $a_trigger trigger true/false
	 */
	function setResourceAsTrigger($a_level_id, $a_rep_ref_id, $a_trigger = true)
	{
		if (!is_array($this->resources[$a_level_id]))
		{
			$this->resources[$a_level_id] = array();
		}
		if (!is_array($this->resources[$a_level_id][$a_rep_ref_id]))
		{
			$this->resources[$a_level_id][$a_rep_ref_id] = array();
		}
		
		$this->resources[$a_level_id][$a_rep_ref_id]["trigger"] = $a_trigger;
	}

	/**
	 * Set resource as imparting resource
	 *
	 * @param int $a_level_id level id
	 * @param int $a_rep_ref_id repository resource ref id
	 * @param bool $a_imparting imparting knowledge true/false
	 */
	function setResourceAsImparting($a_level_id, $a_rep_ref_id, $a_imparting = true)
	{
		if (!is_array($this->resources[$a_level_id]))
		{
			$this->resources[$a_level_id] = array();
		}
		if (!is_array($this->resources[$a_level_id][$a_rep_ref_id]))
		{
			$this->resources[$a_level_id][$a_rep_ref_id] = array();
		}
		
		$this->resources[$a_level_id][$a_rep_ref_id]["imparting"] = $a_imparting;
	}

	/**
	 * Get usage info
	 *
	 * @param
	 * @return
	 */
	static public function getUsageInfo($a_cskill_ids, &$a_usages)
	{
		global $ilDB;
		
		include_once("./Services/Skill/classes/class.ilSkillUsage.php");
		ilSkillUsage::getUsageInfoGeneric($a_cskill_ids, $a_usages, ilSkillUsage::RESOURCE,
				"skl_skill_resource", "rep_ref_id", "base_skill_id");
	}

}

?>
