<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNodeGUI.php");
include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
/**
 * Skill template reference GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_isCalledBy ilSkillTemplateReferenceGUI: ilObjSkillManagementGUI
 * @ingroup ServicesSkill
 */
class ilSkillTemplateReferenceGUI extends ilSkillTreeNodeGUI
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
		return "sktr";
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
			 
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_sktr_b.gif"));
		$tpl->setTitle(
			$lng->txt("skmg_skill_template_reference").": ".$this->node_object->getTitle());
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
	 * Insert
	 *
	 * @param
	 * @return
	 */
	function insert()
	{
		global $ilCtrl, $tpl;
		
		$ilCtrl->saveParameter($this, "parent_id");
		$ilCtrl->saveParameter($this, "target");
		$this->initForm("insert");
		$tpl->setContent($this->form->getHTML());
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
	public function initForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// select skill template
		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$tmplts = ilSkillTreeNode::getTopTemplates();
		
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$this->form->addItem($ti);

		// template
		$options = array(
			"" => $lng->txt("please_select"),
			);
		foreach ($tmplts as $tmplt)
		{
			$options[$tmplt["child"]] = $tmplt["title"];
		}
		$si = new ilSelectInputGUI($lng->txt("skmg_skill_template"), "skill_template_id");
		$si->setOptions($options);
		$si->setRequired(true);
		$this->form->addItem($si);

		if ($a_mode == "insert")
		{
			$this->form->addCommandButton("saveSkillTemplateReference", $lng->txt("save"));
			$this->form->setTitle($lng->txt("skmg_new_sktr"));
		}
		else
		{
			$this->form->addCommandButton("updateSkillTemplateReference", $lng->txt("save"));
			$this->form->setTitle($lng->txt("skmg_edit_sktr"));
		}
		$this->form->addCommandButton("cancel", $lng->txt("cancel"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Get current values for from
	 */
	public function getValues()
	{
		$values = array();
		$values["skill_template_id"] = $this->node_object->getSkillTemplateId();
		$values["title"] = $this->node_object->getTitle();
		$this->form->setValuesByArray($values);
	}

	/**
	 * Save form
	 */
	function saveSkillTemplateReference()
	{
		global $lng, $ilCtrl, $tpl;

		$this->initForm("insert");
		if ($this->form->checkInput())
		{
			$sktr = new ilSkillTemplateReference();
			$sktr->setTitle($_POST["title"]);
			$sktr->setSkillTemplateId($_POST["skill_template_id"]);
			$sktr->create();
			ilSkillTreeNode::putInTree($sktr,
				(int)$_GET["parent_id"], (int)$_GET["target"]);

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirectByClass("ilobjskillmanagementgui", "editSkills");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	/**
	 * Update form
	 */
	function updateSkillTemplateReference()
	{
		global $lng, $ilCtrl, $tpl;

		$this->initForm("edit");
		if ($this->form->checkInput())
		{
			// perform update
			$this->node_object->setSkillTemplateId($_POST["skill_template_id"]);
			$this->node_object->setTitle($_POST["title"]);
			$this->node_object->update();

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirectByClass("ilobjskillmanagementgui", "editSkills");
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