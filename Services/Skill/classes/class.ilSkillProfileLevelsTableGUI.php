<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for skill profile levels
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services/Skill
 */	
class ilSkillProfileLevelsTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_profile)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		include_once("./Services/Skill/classes/class.ilBasicSkill.php");
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->tree = new ilSkillTree();
		
		$this->profile = $a_profile;
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setData($this->profile->getSkillLevels());
		$this->setTitle($lng->txt("skmg_skill_levels"));
		
		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("skmg_skill"));
		$this->addColumn($this->lng->txt("skmg_level"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.skill_profile_level_row.html", "Services/Skill");

		$this->addMultiCommand("confirmLevelAssignmentRemoval", $lng->txt("skmg_remove_levels"));
		//$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		$path = $this->tree->getSkillTreePath($a_set["base_skill_id"],
			$a_set["tref_id"]);
		$path_items = array();
		foreach ($path as $p)
		{
			if ($p["type"] != "skrt")
			{
				$path_items[] = $p["title"];
			}
		}
		$this->tpl->setVariable("SKILL_TITLE",
			implode($path_items, " > "));
		
		$this->tpl->setVariable("LEVEL_TITLE", ilBasicSkill::lookupLevelTitle($a_set["level_id"]));
		
		$this->tpl->setVariable("ID",
			((int) $a_set["base_skill_id"]).":".((int) $a_set["tref_id"]).":".((int) $a_set["level_id"]));
	}

}
?>
