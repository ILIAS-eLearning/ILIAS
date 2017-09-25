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
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilHelpGUI
	 */
	protected $help;


	/**
	 * Constructor
	 */
	function __construct($a_node_id = 0)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC["tpl"];
		$this->tabs = $DIC->tabs();
		$this->lng = $DIC->language();
		$this->help = $DIC["ilHelp"];
		$ilCtrl = $DIC->ctrl();
		
		$ilCtrl->saveParameter($this, "obj_id");
		
		parent::__construct($a_node_id);
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
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;
		$ilTabs = $this->tabs;
		
		//$tpl->getStandardTemplate();
		
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
		$ilTabs = $this->tabs;
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ilHelp = $this->help;

		$ilTabs->clearTargets();
		$ilHelp->setScreenIdComponent("skmg_scat");
		
		// content
		$ilTabs->addTab("content", $lng->txt("content"),
			$ilCtrl->getLinkTarget($this, 'listItems'));

		// properties
		$ilTabs->addTab("properties", $lng->txt("settings"),
			$ilCtrl->getLinkTarget($this, 'editProperties'));

		// usage
		$this->addUsageTab($ilTabs);

		// back link
		$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
			$this->node_object->skill_tree->getRootId());
		$ilTabs->setBackTarget($lng->txt("obj_skmg"),
			$ilCtrl->getLinkTargetByClass("ilskillrootgui", "listSkills"));
		$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
			$_GET["obj_id"]);

			 
		parent::setTitleIcon();
		$tpl->setTitle(
			$lng->txt("scat").": ".$this->node_object->getTitle());
		$this->setSkillNodeDescription();
		
		$ilTabs->activateTab($a_tab);
	}

	/**
	 * Edit properties
	 */
	function editProperties()
	{
		$tpl = $this->tpl;
		
		$this->setTabs("properties");
		parent::editProperties();
	}

	/**
	 * Perform drag and drop action
	 */
	function proceedDragDrop()
	{
		$ilCtrl = $this->ctrl;

//		$this->slm_object->executeDragDrop($_POST["il_hform_source_id"], $_POST["il_hform_target_id"],
//			$_POST["il_hform_fc"], $_POST["il_hform_as_subitem"]);
//		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	 * Edit
	 */
	function edit()
	{
		$tpl = $this->tpl;

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
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(200);
		$ti->setSize(50);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// order nr
		$ni = new ilNumberInputGUI($lng->txt("skmg_order_nr"), "order_nr");
		$ni->setInfo($lng->txt("skmg_order_nr_info"));
		$ni->setMaxLength(6);
		$ni->setSize(6);
		$ni->setRequired(true);
		if ($a_mode == "create")
		{
			include_once("./Services/Skill/classes/class.ilSkillTree.php");
			$tree = new ilSkillTree();
			$max = $tree->getMaxOrderNr((int)$_GET["obj_id"]);
			$ni->setValue($max + 10);
		}
		$this->form->addItem($ni);

		// status
		$this->addStatusInput($this->form);

		// selectable
		$cb = new ilCheckboxInputGUI($lng->txt("skmg_selectable"), "self_eval");
		$cb->setInfo($lng->txt("skmg_selectable_info"));
		$this->form->addItem($cb);

		// save and cancel commands
		if ($this->checkPermissionBool("write"))
		{
			if ($a_mode == "create")
			{
				$this->form->addCommandButton("save", $lng->txt("save"));
				$this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
				$this->form->setTitle($lng->txt("skmg_create_skill_category"));
			} else
			{
				$this->form->addCommandButton("update", $lng->txt("save"));
				$this->form->setTitle($lng->txt("skmg_edit_scat"));
			}
		}
		
		$ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Save item
	 */
	function saveItem()
	{
		if (!$this->checkPermissionBool("write"))
		{
			return;
		}
		include_once "Services/Skill/classes/class.ilSkillCategory.php";
		$it = new ilSkillCategory();
		$it->setTitle($this->form->getInput("title"));
		$it->setOrderNr($this->form->getInput("order_nr"));
		$it->setSelfEvaluation($_POST["self_eval"]);
		$it->setStatus($_POST["status"]);
		$it->create();
		ilSkillTreeNode::putInTree($it, (int) $_GET["obj_id"], IL_LAST_NODE);
	}

	/**
	 * Get current values for from
	 */
	public function getValues()
	{
		$values = array();
		$values["title"] = $this->node_object->getTitle();
		$values["order_nr"] = $this->node_object->getOrderNr();
		$values["self_eval"] = $this->node_object->getSelfEvaluation();
		$values["status"] = $this->node_object->getStatus();
		$this->form->setValuesByArray($values);
	}

	/**
	 * Update item
	 */
	function updateItem()
	{
		if (!$this->checkPermissionBool("write"))
		{
			return;
		}

		$this->node_object->setTitle($this->form->getInput("title"));
		$this->node_object->setOrderNr($this->form->getInput("order_nr"));
		$this->node_object->setSelfEvaluation($_POST["self_eval"]);
		$this->node_object->setStatus($_POST["status"]);
		$this->node_object->update();
	}

	/**
	 * Update form
	 */
/*	function updateSkillCategory()
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
	}*/

	/**
	 * List items
	 *
	 * @param
	 * @return
	 */
	function listItems()
	{
		$tpl = $this->tpl;
		$lng = $this->lng;

		if ($this->isInUse())
		{
			ilUtil::sendInfo($lng->txt("skmg_skill_in_use"));
		}

		if ($this->checkPermissionBool("write"))
		{
			self::addCreationButtons();
		}
		$this->setTabs("content");
		
		include_once("./Services/Skill/classes/class.ilSkillCatTableGUI.php");
		$table = new ilSkillCatTableGUI($this, "listItems", (int) $_GET["obj_id"],
			ilSkillCatTableGUI::MODE_SCAT);
		
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
		global $DIC;

		$ilCtrl = $DIC->ctrl();
		$lng = $DIC->language();
		$ilToolbar = $DIC->toolbar();
		$ilUser = $DIC->user();

		// skill
		$ilCtrl->setParameterByClass("ilbasicskillgui",
			"obj_id", (int) $_GET["obj_id"]);
		$ilToolbar->addButton($lng->txt("skmg_create_skll"),
			$ilCtrl->getLinkTargetByClass("ilbasicskillgui", "create"));

		// skill category
		$ilCtrl->setParameterByClass("ilskillcategorygui",
			"obj_id", (int) $_GET["obj_id"]);
		$ilToolbar->addButton($lng->txt("skmg_create_skill_category"),
			$ilCtrl->getLinkTargetByClass("ilskillcategorygui", "create"));
		
		// skill template reference
		$ilCtrl->setParameterByClass("ilskilltemplatereferencegui",
			"obj_id", (int) $_GET["obj_id"]);
		$ilToolbar->addButton($lng->txt("skmg_create_skill_template_reference"),
			$ilCtrl->getLinkTargetByClass("ilskilltemplatereferencegui", "create"));
		
		// skills from clipboard
		$sep = false;
		if ($ilUser->clipboardHasObjectsOfType("skll"))
		{
			$ilToolbar->addSeparator();
			$sep = true;
			$ilToolbar->addButton($lng->txt("skmg_insert_basic_skill_from_clip"),
				$ilCtrl->getLinkTargetByClass("ilskillcategorygui", "insertBasicSkillClip"));
		}

		// skills from clipboard
		if ($ilUser->clipboardHasObjectsOfType("scat"))
		{
			if (!$sep)
			{
				$ilToolbar->addSeparator();
				$sep = true;
			}
			$ilToolbar->addButton($lng->txt("skmg_insert_skill_category_from_clip"),
				$ilCtrl->getLinkTargetByClass("ilskillcategorygui", "insertSkillCategoryClip"));
		}

		// skills from clipboard
		if ($ilUser->clipboardHasObjectsOfType("sktr"))
		{
			if (!$sep)
			{
				$ilToolbar->addSeparator();
				$sep = true;
			}
			$ilToolbar->addButton($lng->txt("skmg_insert_skill_template_reference_from_clip"),
				$ilCtrl->getLinkTargetByClass("ilskillcategorygui", "insertTemplateReferenceClip"));
		}

		// skill template reference
		$ilToolbar->addButton($lng->txt("skmg_import_skills"),
				$ilCtrl->getLinkTargetByClass("ilskillrootgui", "showImportForm"));

	}

	/**
	 * Cancel
	 *
	 * @param
	 * @return
	 */
	function cancel()
	{
		$ilCtrl = $this->ctrl;

		$ilCtrl->redirectByClass("ilobjskillmanagementgui", "editSkills");
	}
	
	/**
	 * Redirect to parent (identified by current obj_id)
	 *
	 * @param
	 * @return
	 */
	function redirectToParent($a_tmp_mode = false)
	{
		$ilCtrl = $this->ctrl;
		
		$t = ilSkillTreeNode::_lookupType((int) $_GET["obj_id"]);

		switch ($t)
		{
			case "skrt":
				$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id", (int) $_GET["obj_id"]);
				$ilCtrl->redirectByClass("ilskillrootgui", "listSkills");
				break;
		}
		
		parent::redirectToParent();
	}


}

?>