<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilBasicSkillTemplate.php");
include_once("./Services/Skill/classes/class.ilBasicSkillGUI.php");

/**
* Basic skill template GUI class
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ilCtrl_isCalledBy ilBasicSkillTemplateGUI: ilObjSkillManagementGUI
*
* @ingroup ServicesSkill
*/
class ilBasicSkillTemplateGUI extends ilBasicSkillGUI
{

	/**
	 * Constructor
	 */
	function __construct($a_node_id = 0)
	{
		global $ilCtrl;
		
		$ilCtrl->saveParameter($this, array("obj_id", "level_id"));
		
		parent::ilSkillTreeNodeGUI($a_node_id);
	}

	/**
	 * Get Node Type
	 */
	function getType()
	{
		return "sktp";
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
			 
		parent::setTitleIcon();
		$tpl->setTitle(
			$lng->txt("skmg_basic_skill_template").": ".$this->node_object->getTitle());
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

		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(200);
		$ti->setSize(50);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// order nr
		$ni = new ilNumberInputGUI($lng->txt("skmg_order_nr"), "order_nr");
		$ni->setMaxLength(6);
		$ni->setSize(6);
		$ni->setRequired(true);
		$this->form->addItem($ni);

		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("save", $lng->txt("save"));
			$this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("skmg_create_skll"));
		}
		else
		{
			$this->form->addCommandButton("update", $lng->txt("save"));
			$this->form->setTitle($lng->txt("skmg_edit_skll"));
		}
		
		$ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Set header for level
	 *
	 * @param
	 * @return
	 */
	function setLevelHead()
	{
		global $ilTabs, $ilCtrl, $tpl, $lng;

		// tabs
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("skmg_skill_levels"),
			$ilCtrl->getLinkTarget($this, "edit"));

		if ($_GET["level_id"] > 0)
		{
			$ilTabs->addTab("level_settings",
				$lng->txt("settings"),
				$ilCtrl->getLinkTarget($this, "editLevel"));

/*			$ilTabs->addTab("level_trigger",
				$lng->txt("skmg_trigger"),
				$ilCtrl->getLinkTarget($this, "editLevelTrigger"));

			$ilTabs->addTab("level_certificate",
				$lng->txt("certificate"),
				$ilCtrl->getLinkTargetByClass("ilcertificategui", "certificateEditor"));*/
		}

		// title
		if ($_GET["level_id"] > 0)
		{
			$tpl->setTitle($lng->txt("skmg_skill_level").": ".
				ilBasicSkill::lookupLevelTitle((int) $_GET["level_id"]));
		}
		else
		{
			$tpl->setTitle($lng->txt("skmg_skill_level"));			
		}

		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$tree = new ilSkillTree();
		$path = $tree->getPathFull($this->node_object->getId());
		$desc = "";
		foreach ($path as $p)
		{
			if (in_array($p["type"], array("scat", "skll")))
			{
				$desc.= $sep.$p["title"];
				$sep = " > ";
			}
		}
		$tpl->setDescription($desc);
	}

	/**
	 * Set header for skill
	 *
	 * @param
	 * @return
	 */
	function setSkillHead($a_tab)
	{
		global $ilTabs, $ilCtrl, $tpl, $lng;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("skmg_skill_templates"),
			$ilCtrl->getLinkTargetByClass("ilobjskillmanagementgui", "editSkillTemplates"));

		if (is_object($this->node_object))
		{
			$tpl->setTitle($lng->txt("skmg_skill_template").": ".
				$this->node_object->getTitle());
			
			// levels
			$ilTabs->addTab("levels", $lng->txt("skmg_skill_levels"),
				$ilCtrl->getLinkTarget($this, 'edit'));
	
			// properties
			$ilTabs->addTab("properties", $lng->txt("settings"),
				$ilCtrl->getLinkTarget($this, 'editProperties'));
			
			$ilTabs->activateTab($a_tab);

			//$tpl->setTitleIcon(ilUtil::getImagePath("icon_sktp_b.png"), $lng->txt("skmg_skill_template"));
			parent::setTitleIcon();
		
			$this->setSkillNodeDescription();
		}
		else
		{
			$tpl->setTitle($lng->txt("skmg_skill"));
			$tpl->setDescription("");
		}
	}

	/**
	 * Save item
	 */
	function saveItem()
	{
		$it = new ilBasicSkillTemplate();
		$it->setTitle($this->form->getInput("title"));
		$it->setOrderNr($this->form->getInput("order_nr"));
		$it->create();
		ilSkillTreeNode::putInTree($it, (int) $_GET["obj_id"], IL_LAST_NODE);
		$this->node_object = $it;
	}
	
	/**
	 * After saving
	 */
	function afterSave()
	{
		global $ilCtrl;
		
		$ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id",
			$this->node_object->getId());
		$ilCtrl->redirectByClass("ilbasicskilltemplategui", "edit");
	}

}
?>
