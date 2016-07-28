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
		
		parent::__construct($a_node_id);
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
	function executeCommand()
	{
		global $ilCtrl, $tpl, $ilTabs;
		
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

	/**
	 * Show import form
	 */
	function showImportForm()
	{
		global $tpl, $ilTabs;

		$ilTabs->activateTab("skills");
		$tpl->setContent($this->initInputForm()->getHTML());
	}

	/**
	 * Init input form.
	 */
	public function initInputForm()
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($lng->txt("skmg_input_file"), "import_file");
		$fi->setSuffixes(array("zip"));
		$fi->setRequired(true);
		$form->addItem($fi);

		// save and cancel commands
		$form->addCommandButton("importSkills", $lng->txt("import"));
		$form->addCommandButton("listSkills", $lng->txt("cancel"));

		$form->setTitle($lng->txt("skmg_import_skills"));
		$form->setFormAction($ilCtrl->getFormAction($this));

		return $form;
	}

	/**
	 * Import skills
	 */
	public function importSkills()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;

		$form = $this->initInputForm();
		if ($form->checkInput())
		{
			include_once("./Services/Export/classes/class.ilImport.php");
			$imp = new ilImport();
			$imp->importEntity($_FILES["import_file"]["tmp_name"], $_FILES["import_file"]["name"], "skmg", "Services/Skill");

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "listSkills");
		}
		else
		{
			$ilTabs->activateTab("skills");
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}

}

?>