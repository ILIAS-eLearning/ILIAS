<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for skill profiles
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSkillProfileTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($this->getProfiles());
		$this->setTitle($lng->txt("skmg_skill_profiles"));
		
		$this->addColumn("", "", "1px", true);
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("users"));
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.skill_profile_row.html", "Services/Skill");

		$this->addMultiCommand("confirmDeleteProfiles", $lng->txt("delete"));
		//$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Get profiles
	 *
	 * @return array array of skill profiles
	 */
	function getProfiles()
	{
		include_once("./Services/Skill/classes/class.ilSkillProfile.php");
		return ilSkillProfile::getProfiles();
	}
	
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$this->tpl->setCurrentBlock("cmd");
		$this->tpl->setVariable("CMD", $lng->txt("edit"));
		$ilCtrl->setParameter($this->parent_obj, "sprof_id", $a_set["id"]);
		$this->tpl->setVariable("CMD_HREF", $ilCtrl->getLinkTarget($this->parent_obj, "showUsers"));
		$ilCtrl->setParameter($this->parent_obj, "sprof_id", $_GET["sprof_id"]);
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setVariable("ID", $a_set["id"]);
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("NUM_USERS", ilSkillProfile::countUsers($a_set["id"]));
	}

}
?>
