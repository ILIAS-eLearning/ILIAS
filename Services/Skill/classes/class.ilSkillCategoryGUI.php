<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNodeGUI.php");

/**
 * Skill category GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_isCalledBy ilSkillCategoryGUI: ilObjSkillManagementGUI
 * @ingroup ServicesSkill
 */
class ilSkillCategoryGUI extends ilSkillTreeNodeGUI
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
		return "scat";
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
	 * output tabs
	 */
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $tpl, $lng;

		// properties
		$ilTabs->addTarget("properties",
			 $ilCtrl->getLinkTarget($this,'showProperties'),
			 "showProperties", get_class($this));
			 
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_skmg_b.gif"));
		$tpl->setTitle(
			$lng->txt("skmg_basic_skill").": ".$this->node_object->getTitle());
	}

	/**
	 * Show Sequencing
	 */
	function showProperties()
	{
		global $tpl;
		
		$this->setTabs();
		$this->setLocator();

		$tpl->setContent("Properties");
	}

	/**
	 * Perform drag and drop action
	 */
	function proceedDragDrop()
	{
		global $ilCtrl;

//		$this->slm_object->executeDragDrop($_POST["il_hform_source_id"], $_POST["il_hform_target_id"],
//			$_POST["il_hform_fc"], $_POST["il_hform_as_subitem"]);
//		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	 * Edit
	 */
	function edit()
	{
		global $tpl;

		$this->initForm();
		$this->getValues();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * Init form.
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	public function initForm()
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// selectable
		$cb = new ilCheckboxInputGUI($lng->txt("skmg_selectable"), "self_eval");
		$cb->setInfo($lng->txt("skmg_selectable_info"));
		$this->form->addItem($cb);

		$this->form->addCommandButton("updateSkillCategory", $lng->txt("save"));
		$this->form->addCommandButton("cancel", $lng->txt("cancel"));

		$this->form->setTitle($lng->txt("skmg_edit_scat"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Get current values for from
	 */
	public function getValues()
	{
		$values = array();
		$values["self_eval"] = $this->node_object->getSelfEvaluation();
		$this->form->setValuesByArray($values);
	}

	/**
	 * Update form
	 */
	function updateSkillCategory()
	{
		global $lng, $ilCtrl, $tpl;

		$this->initForm("edit");
		if ($this->form->checkInput())
		{
			// perform update
			$this->node_object->setSelfEvaluation($_POST["self_eval"]);
			$this->node_object->update();

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "edit");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	/**
	 * Cancel
	 *
	 * @param
	 * @return
	 */
	function cancel()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjskillmanagementgui", "editSkills");
	}
}

?>