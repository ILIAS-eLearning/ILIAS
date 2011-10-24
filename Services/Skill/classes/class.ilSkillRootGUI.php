<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNodeGUI.php");

/**
 * Skill root GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_isCalledBy ilSkillRootGUI: ilObjSkillManagementGUI
 * @ingroup ServicesSkill
 */
class ilSkillRootGUI extends ilSkillTreeNodeGUI
{

	/**
	 * Constructor
	 */
	function __construct($a_node_id = 0)
	{
		global $ilCtrl;
		
		$ilCtrl->saveParameter($this, "obj_id");
		
		parent::ilSkillTreeNodeGUI($a_node_id);
	}

	/**
	 * Get Node Type
	 */
	function getType()
	{
		return "skrt";
	}

	/**
	 * Execute command
	 */
	function &executeCommand()
	{
		global $ilCtrl, $tpl, $ilTabs;
		
		$tpl->getStandardTemplate();
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			default:
				$ret = $this->$cmd();
				break;
		}
	}
	
	/**
	 * List templates
	 */
	function listTemplates()
	{
		global $tpl, $ilToolbar, $ilCtrl, $lng, $ilTabs;
		
		$skmg_set = new ilSetting("skmg");
		$enable_skmg = $skmg_set->get("enable_skmg");
		if (!$enable_skmg)
		{
			ilUtil::sendInfo($lng->txt("skmg_skill_management_deactivated"));
		}

		$this->getParentGUI()->showTree(true, $this, "listTemplates");
		$ilTabs->activateTab("skill_templates");

		include_once("./Services/Skill/classes/class.ilSkillTemplateCategoryGUI.php");
		ilSkillTemplateCategoryGUI::addCreationButtons();
		
		include_once("./Services/Skill/classes/class.ilSkillCatTableGUI.php");
		$table = new ilSkillCatTableGUI($this, "listTemplates", (int) $_GET["obj_id"],
			ilSkillCatTableGUI::MODE_SCTP);
		
		$tpl->setContent($table->getHTML());
	}

	/**
	 * List skills
	 */
	function listSkills()
	{
		global $tpl, $ilToolbar, $ilCtrl, $lng, $ilTabs;

		$skmg_set = new ilSetting("skmg");
		$enable_skmg = $skmg_set->get("enable_skmg");
		if (!$enable_skmg)
		{
			ilUtil::sendInfo($lng->txt("skmg_skill_management_deactivated"));
		}

		$this->getParentGUI()->showTree(false, $this, "listSkills");
		$ilTabs->activateTab("skills");
		
		include_once("./Services/Skill/classes/class.ilSkillCategoryGUI.php");
		ilSkillCategoryGUI::addCreationButtons();
		
		include_once("./Services/Skill/classes/class.ilSkillCatTableGUI.php");
		$table = new ilSkillCatTableGUI($this, "listSkills", (int) $_GET["obj_id"],
			ilSkillCatTableGUI::MODE_SCAT);
		
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * cancel delete
	 */
	function cancelDelete()
	{
		global $ilCtrl;
		if ($_GET["tmpmode"])
		{
			$ilCtrl->redirect($this, "listTemplates");
		}
		else
		{
			$ilCtrl->redirect($this, "listSkills");
		}
	}

}

?>