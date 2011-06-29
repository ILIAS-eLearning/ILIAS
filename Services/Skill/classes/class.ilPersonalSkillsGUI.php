<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Personal skills GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPersonalSkillsGUI:
 *
 * @ingroup ServicesSkill
 */
class ilPersonalSkillsGUI
{
	protected $skill_tree;
	
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		global $ilCtrl, $lng;

		$lng->loadLanguageModule('skmg');

		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->skill_tree = new ilSkillTree();
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $ilCtrl, $tpl, $lng;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listSkills");
		
		$tpl->setTitle($lng->txt("skills"));
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_skmg_b.gif"));

		switch($next_class)
		{
			default:
				$this->$cmd();
				break;
		}
		return true;
	}


	/**
	 * List skills
	 */
	function listSkills()
	{
		global $tpl, $ilTabs, $lng, $ilCtrl, $ilToolbar;

		$this->setTabs("list_skills");
		
		$ilToolbar->addButton($lng->txt("skmg_add_skill"),
			$ilCtrl->getLinkTarget($this, "addPersonalSkill"));
		
		include_once("./Services/Skill/classes/class.ilPersonalSkillTableGUI.php");
		$sktab = new ilPersonalSkillTableGUI($this, "listSkills");
		
		$tpl->setContent($sktab->getHTML());

	}

	/**
	 * Set tabs
	 */
	function setTabs($a_activate)
	{
		global $ilTabs, $lng, $ilCtrl;
		
		// list skills
		$ilTabs->addTab("list_skills",
			$lng->txt("skmg_list_skills"),
			$ilCtrl->getLinkTarget($this, "listSkills"));

		// assign materials
		$ilTabs->addTab("assign_materials",
			$lng->txt("skmg_assign_materials"),
			$ilCtrl->getLinkTarget($this, "assignMaterials"));

		$ilTabs->activateTab($a_activate);
	}
	
}
?>