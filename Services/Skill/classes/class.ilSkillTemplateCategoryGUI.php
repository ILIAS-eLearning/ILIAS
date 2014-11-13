<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNodeGUI.php");
include_once("./Services/Skill/classes/class.ilSkillTemplateCategory.php");

/**
 * Skill template category GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_isCalledBy ilSkillTemplateCategoryGUI: ilObjSkillManagementGUI
 * @ingroup ServicesSkill
 */
class ilSkillTemplateCategoryGUI extends ilSkillTreeNodeGUI
{

	/**
	 * Constructor
	 */
	function __construct($a_node_id = 0, $a_tref_id)
	{
		global $ilCtrl;
		
		$ilCtrl->saveParameter($this, "obj_id");
		$this->tref_id = $a_tref_id;
		
		parent::ilSkillTreeNodeGUI($a_node_id);
	}

	/**
	 * Get Node Type
	 */
	function getType()
	{
		return "sctp";
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
	function setTabs($a_tab)
	{
		global $ilTabs, $ilCtrl, $tpl, $lng, $ilHelp;

		$ilTabs->clearTargets();
		$ilHelp->setScreenIdComponent("skmg_sctp");
		
		// content
		$ilTabs->addTab("content", $lng->txt("content"),
			$ilCtrl->getLinkTarget($this, 'listItems'));

		if ($this->tref_id > 0)
		{
			// usage
			$this->addUsageTab($ilTabs);
		}

		// properties
		if ($this->tref_id == 0)
		{
			$ilTabs->addTab("properties", $lng->txt("settings"),
				$ilCtrl->getLinkTarget($this, 'editProperties'));
		}
		
		// back link
		if ($this->tref_id == 0)
		{
			$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
				$this->node_object->skill_tree->getRootId());
			$ilTabs->setBackTarget($lng->txt("skmg_skill_templates"),
				$ilCtrl->getLinkTargetByClass("ilskillrootgui", "listTemplates"));
			$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
				$_GET["obj_id"]);
		}
 
		parent::setTitleIcon();
		$tpl->setTitle(
			$lng->txt("skmg_sctp").": ".$this->node_object->getTitle());
		$this->setSkillNodeDescription();
		
		$ilTabs->activateTab($a_tab);

	}

	/**
	 * List items
	 *
	 * @param
	 * @return
	 */
	function listItems()
	{
		global $tpl;
		
		if ($this->tref_id == 0)
		{
			self::addCreationButtons();
		}

		$this->setTabs("content");
		
		include_once("./Services/Skill/classes/class.ilSkillCatTableGUI.php");
		$table = new ilSkillCatTableGUI($this, "listItems", (int) $_GET["obj_id"],
			ilSkillCatTableGUI::MODE_SCTP, $this->tref_id);
		
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * Add creation buttons
	 *
	 * @param
	 * @return
	 */
	static function addCreationButtons()
	{
		global $ilCtrl, $lng, $ilToolbar, $ilUser;
		
		$ilCtrl->setParameterByClass("ilobjskillmanagementgui", "tmpmode", 1);
		
		$ilCtrl->setParameterByClass("ilbasicskilltemplategui",
			"obj_id", (int) $_GET["obj_id"]);
		$ilToolbar->addButton($lng->txt("skmg_create_skill_template"),
			$ilCtrl->getLinkTargetByClass("ilbasicskilltemplategui", "create"));
		$ilCtrl->setParameterByClass("ilskilltemplatecategorygui",
			"obj_id", (int) $_GET["obj_id"]);
		$ilToolbar->addButton($lng->txt("skmg_create_skill_template_category"),
			$ilCtrl->getLinkTargetByClass("ilskilltemplatecategorygui", "create"));
		
		// skill templates from clipboard
		$sep = false;
		if ($ilUser->clipboardHasObjectsOfType("sktp"))
		{
			$ilToolbar->addSeparator();
			$sep = true;
			$ilToolbar->addButton($lng->txt("skmg_insert_skill_template_from_clip"),
				$ilCtrl->getLinkTargetByClass("ilskilltemplatecategorygui", "insertSkillTemplateClip"));
		}

		// template categories from clipboard
		if ($ilUser->clipboardHasObjectsOfType("sctp"))
		{
			if (!$sep)
			{
				$ilToolbar->addSeparator();
				$sep = true;
			}
			$ilToolbar->addButton($lng->txt("skmg_insert_template_category_from_clip"),
				$ilCtrl->getLinkTargetByClass("ilskilltemplatecategorygui", "insertTemplateCategoryClip"));
		}

	}
	
	/**
	 * Edit properties
	 */
	function editProperties()
	{
		$this->setTabs("properties");
		parent::editProperties();
	}
	
	
	/**
	 * Save item
	 */
	function saveItem()
	{
		$it = new ilSkillTemplateCategory();
		$it->setTitle($this->form->getInput("title"));
		$it->setOrderNr($this->form->getInput("order_nr"));
		$it->create();
		ilSkillTreeNode::putInTree($it, (int) $_GET["obj_id"], IL_LAST_NODE);
	}

	/**
	 * Update item
	 */
	function updateItem()
	{
		$this->node_object->setTitle($this->form->getInput("title"));
		$this->node_object->setOrderNr($this->form->getInput("order_nr"));
		$this->node_object->setSelfEvaluation($_POST["self_eval"]);
		$this->node_object->update();
	}

	/**
	 * After saving
	 */
	function afterSave()
	{
		$this->redirectToParent(true);
	}

}

?>