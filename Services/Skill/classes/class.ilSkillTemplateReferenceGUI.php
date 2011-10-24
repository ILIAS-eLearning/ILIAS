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
	function setTabs($a_tab)
	{
		global $ilTabs, $ilCtrl, $tpl, $lng;

		$ilTabs->clearTargets();
		
		if (is_object($this->node_object))
		{		
			// content
			$ilTabs->addTab("content", $lng->txt("content"),
				$ilCtrl->getLinkTarget($this, 'listItems'));
	
			// properties
			$ilTabs->addTab("properties", $lng->txt("settings"),
				$ilCtrl->getLinkTarget($this, 'editProperties'));
			
			// back link
			$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
				$this->node_object->skill_tree->getRootId());
			$ilTabs->setBackTarget($lng->txt("obj_skmg"),
				$ilCtrl->getLinkTargetByClass("ilskillrootgui", "listSkills"));
			$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
				$_GET["obj_id"]);
			
			$tid = ilSkillTemplateReference::_lookupTemplateId($this->node_object->getId());
			$add = " (".ilSkillTreeNode::_lookupTitle($tid).")";
	
			parent::setTitleIcon();
			$tpl->setTitle(
				$lng->txt("skmg_sktr").": ".$this->node_object->getTitle().$add);
			$this->setSkillNodeDescription();
			
			$ilTabs->activateTab($a_tab);
		}
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
		$this->initForm("create");
		$tpl->setContent($this->form->getHTML());
	}
	
	
	/**
	 * Edit properties
	 */
	function editProperties()
	{
		global $tpl;

		$this->setTabs("properties");
		
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
		
		// order nr
		$ni = new ilNumberInputGUI($lng->txt("skmg_order_nr"), "order_nr");
		$ni->setMaxLength(6);
		$ni->setSize(6);
		$ni->setRequired(true);
		$this->form->addItem($ni);

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
		
		// draft
		$cb = new ilCheckboxInputGUI($lng->txt("skmg_draft"), "draft");
		$cb->setInfo($lng->txt("skmg_draft_info"));
		$this->form->addItem($cb);

		// selectable
		$cb = new ilCheckboxInputGUI($lng->txt("skmg_selectable"), "selectable");
		$cb->setInfo($lng->txt("skmg_selectable_info"));
		$this->form->addItem($cb);

		if ($a_mode == "create")
		{
			$this->form->addCommandButton("save", $lng->txt("save"));
			$this->form->addCommandButton("cancel", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("skmg_new_sktr"));
		}
		else
		{
			$this->form->addCommandButton("updateSkillTemplateReference", $lng->txt("save"));
			$this->form->setTitle($lng->txt("skmg_edit_sktr"));
		}
		
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
		$values["selectable"] = $this->node_object->getSelfEvaluation();
		$values["draft"] = $this->node_object->getDraft();
		$values["order_nr"] = $this->node_object->getOrderNr();
		$this->form->setValuesByArray($values);
	}

	/**
	 * Save item
	 */
	function saveItem()
	{
		$sktr = new ilSkillTemplateReference();
		$sktr->setTitle($_POST["title"]);
		$sktr->setSkillTemplateId($_POST["skill_template_id"]);
		$sktr->setSelfEvaluation($_POST["selectable"]);
		$sktr->setOrderNr($_POST["order_nr"]);
		$sktr->setDraft($_POST["draft"]);
		$sktr->create();
		ilSkillTreeNode::putInTree($sktr, (int) $_GET["obj_id"], IL_LAST_NODE);
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
			$this->node_object->setSelfEvaluation($_POST["selectable"]);
			$this->node_object->setOrderNr($_POST["order_nr"]);
			$this->node_object->setDraft($_POST["draft"]);
			$this->node_object->update();

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
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
	
	/**
	 * List items
	 */
	function listItems()
	{
		global $tpl;

		$this->setTabs("content");
		
		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$bs = ilSkillTreeNode::getSkillTreeNodes((int) $_GET["obj_id"], false);
		include_once("./Services/UIComponent/NestedList/classes/class.ilNestedList.php");
		$ns = new ilNestedList();
		$ns->setListClass("il_Explorer");
		foreach ($bs as $b)
		{
			$par = ($b["id"] == (int) $_GET["obj_id"])
				? 0
				: $b["parent"];
				$ns->addListNode(ilSkillTreeNode::_lookupTitle($b["id"]), $b["id"], $par);
		}
		
		$tpl->setContent($ns->getHTML());
	}

}

?>