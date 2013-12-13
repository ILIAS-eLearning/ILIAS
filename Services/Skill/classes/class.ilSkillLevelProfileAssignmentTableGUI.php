<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for skill profile skill level assignment
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSkillLevelProfileAssignmentTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_cskill_id)
	{
		global $ilCtrl, $lng;
		
		include_once("./Services/Skill/classes/class.ilBasicSkill.php");

		$parts = explode(":", $a_cskill_id);
		$this->skill_id = (int) $parts[0];
		$this->tref_id = (int) $parts[1];

		$this->skill = new ilBasicSkill($this->skill_id);
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($this->skill->getLevelData());
		$this->setTitle($this->skill->getTitle().", ".
			$lng->txt("skmg_skill_levels"));
		
		$this->addColumn($this->lng->txt("title"));
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.skill_level_profile_assignment_row.html", "Services/Skill");
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$this->tpl->setCurrentBlock("cmd");
		$this->tpl->setVariable("CMD", $lng->txt("skmg_assign_level"));
		$ilCtrl->setParameter($this->parent_obj, "level_id", (int) $a_set["id"]);
		$this->tpl->setVariable("CMD_HREF", $ilCtrl->getLinkTarget($this->parent_obj,
			"assignLevelToProfile"));
		$ilCtrl->setParameter($this->parent_obj, "level_id", $_GET["level_id"]);
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setVariable("TITLE", $a_set["title"]);
	}

}
?>
