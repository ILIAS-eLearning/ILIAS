<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");

/**
 * Basic GUI class for skill tree nodes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilSkillTreeNodeGUI
{
	var $node_object;

	/**
	* constructor
	*
	* @param	object		$a_content_obj		node object
	*/
	function ilSkillTreeNodeGUI($a_node_id = 0)
	{
		$this->node_object = null;

		if ($a_node_id > 0 &&
			$this->getType() == ilSkillTreeNode::_lookupType($a_node_id))
		{
			$this->readNodeObject((int) $a_node_id);
		}
	}

	/**
	* Set Parent GUI class
	*
	* @param	object	$a_parentgui	Parent GUI class
	*/
	function setParentGUI($a_parentgui)
	{
		$this->parentgui = $a_parentgui;
	}

	/**
	* Get Parent GUI class (ilObjSCORM2004LearningModuleGUI).
	*
	* @return	object	Parent GUI class
	*/
	function getParentGUI()
	{
		return $this->parentgui;
	}

	/**
	 * Get node object instance
	 */
	function readNodeObject($a_node_id)
	{
		include_once("./Services/Skill/classes/class.ilSkillTreeNodeFactory.php");
		$this->node_object = ilSkillTreeNodeFactory::getInstance($a_node_id);
	}
	
	/**
	 * Save Titles
	 */
	function saveAllTitles()
	{
		global $ilCtrl;
		
		$this->getParentGUI()->saveAllTitles(false);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	 * Delete nodes in the hierarchy
	 */
	function deleteNodes()
	{
		global $ilCtrl;

		$ilCtrl->setParameter($this, "backcmd", $_GET["backcmd"]);
		$this->getParentGUI()->deleteNodes($this);
	}

	/**
	 * Copy items to clipboard, then cut them from the current tree
	 */
	function cutItems()
	{
		global $ilCtrl, $lng;

		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");

		if (!is_array($_POST["id"]) || count($_POST["id"]) == 0)
		{
			$this->redirectToParent();
		}
		
		$items = ilUtil::stripSlashesArray($_POST["id"]);
		$todel = array();			// delete IDs < 0 (needed for non-js editing)
		foreach($items as $k => $item)
		{
			if ($item < 0)
			{
				$todel[] = $k;
			}
		}
		foreach($todel as $k)
		{
			unset($items[$k]);
		}

		if (!ilSkillTreeNode::uniqueTypesCheck($items))
		{
			ilUtil::sendInfo($lng->txt("skmg_insert_please_choose_one_type_only"), true);
			$this->redirectToParent();
		}

		ilSkillTreeNode::clipboardCut(1, $items);

		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		ilEditClipboard::setAction("cut");

		ilUtil::sendInfo($lng->txt("skmg_selected_items_have_been_cut"), true);
		
		ilSkillTreeNode::saveChildsOrder((int) $_GET["obj_id"], array(),
			$_GET["tmpmode"]);

		$this->redirectToParent();
	}

	/**
	 * Copy items to clipboard
	 */
	function copyItems()
	{
		global $ilCtrl, $lng;

		if (!is_array($_POST["id"]) || count($_POST["id"]) == 0)
		{
			$this->redirectToParent();
		}

		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");

		$items = ilUtil::stripSlashesArray($_POST["id"]);
		$todel = array();				// delete IDs < 0 (needed for non-js editing)
		foreach($items as $k => $item)
		{
			if ($item < 0)
			{
				$todel[] = $k;
			}
		}
		foreach($todel as $k)
		{
			unset($items[$k]);
		}
		if (!ilSkillTreeNode::uniqueTypesCheck($items))
		{
			ilUtil::sendInfo($lng->txt("skmg_insert_please_choose_one_type_only"), true);
			$this->redirectToParent();
		}
		ilSkillTreeNode::clipboardCopy(1, $items);

		// @todo: move this to a service since it can be used here, too
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		ilEditClipboard::setAction("copy");
		ilUtil::sendInfo($lng->txt("skmg_selected_items_have_been_copied"), true);

		$this->redirectToParent();
	}

	/**
	 * cancel delete
	 */
	function cancelDelete()
	{
		global $ilCtrl;
		
		$this->redirectToParent();
	}

	/**
	 * confirmed delete
	 */
	function confirmedDelete()
	{
		global $ilCtrl;
		
		$this->getParentGUI()->confirmedDelete(false);
		ilSkillTreeNode::saveChildsOrder((int) $_GET["obj_id"], array(),
			$_GET["tmpmode"]);

		$this->redirectToParent();
	}

	/**
	 * Set Locator Items
	 */
	function setLocator()
	{
		global $ilLocator, $tpl, $ilCtrl;
		
		$ilLocator->addRepositoryItems($_GET["ref_id"]);
		$this->getParentGUI()->addLocatorItems();
		
		if ($_GET["obj_id"] > 0)
		{
			include_once("./Services/Skill/classes/class.ilSkillTree.php");
			$tree = new ilSkillTree();
			$path = $tree->getPathFull($_GET["obj_id"]);
			for( $i =  1; $i < count($path); $i++)
			{
				switch($path[$i]["type"])
				{
					case "scat":
						$ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id",
							$path[$i]["child"]);
						$ilLocator->addItem($path[$i]["title"],
							$ilCtrl->getLinkTargetByClass("ilskillmanagementgui",
							"ilskillcategorygui"), "", 0, $path[$i]["type"],
							ilUtil::getImagePath("icon_skmg.svg"));
						break;

					case "skll":
						$ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id",
							$path[$i]["child"]);
						$ilLocator->addItem($path[$i]["title"],
							$ilCtrl->getLinkTargetByClass("ilskillmanagementgui",
							"ilbasicskillgui"), "", 0, $path[$i]["type"],
							ilUtil::getImagePath("icon_skmg.svg"));
						break;
						
				}
			}
		}
		$ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		
		$tpl->setLocator();
	}
	
	/**
	 * Set skill node description
	 */
	function setSkillNodeDescription()
	{
		global $tpl;
		
		if (is_object($this->node_object))
		{
			include_once("./Services/Skill/classes/class.ilSkillTree.php");
			$tree = new ilSkillTree();
			$path = $this->node_object->skill_tree->getSkillTreePath($this->node_object->getId(),
					$this->tref_id);
			$desc = "";
			foreach ($path as $p)
			{
				if (in_array($p["type"], array("scat", "skll", "sktr")))
				{
					$desc.= $sep.$p["title"];
					$sep = " > ";
				}
			}
		}
		$tpl->setDescription($desc);
	}

	/**
	 * Create skill tree node
	 */
	function create()
	{
		global $tpl;
		
		$this->initForm("create");
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Add status input
	 *
	 * @param ilPropertyFormGUI $a_form form
	 */
	function addStatusInput(ilPropertyFormGUI $a_form)
	{
		global $lng;

		// status
		$radg = new ilRadioGroupInputGUI($lng->txt("skmg_status"), "status");
		foreach (ilSkillTreeNode::getAllStatus() as $k => $op)
		{
			$op = new ilRadioOption($op, $k, ilSkillTreeNode::getStatusInfo($k));
			$radg->addOption($op);
		}
		$a_form->addItem($radg);
	}
	
	/**
	 * Edit properties form
	 */
	function editProperties()
	{
		global $tpl;
		
		$this->initForm("edit");
		$this->getPropertyValues();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Get property values for edit form
	 */
	function getPropertyValues()
	{
		$values = array();
		
		$values["title"] = $this->node_object->getTitle();
		$values["order_nr"] = $this->node_object->getOrderNr();
		$values["self_eval"] = $this->node_object->getSelfEvaluation();
		$values["status"] = $this->node_object->getStatus();
		
		$this->form->setValuesByArray($values); 
    }
    
	/**
	 * Save skill tree node
	 *
	 */
	public function save()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initForm("create");
		if ($this->form->checkInput())
		{
			$this->saveItem();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			ilSkillTreeNode::saveChildsOrder((int) $_GET["obj_id"], array(),
				in_array($this->getType(), array("sktp", "sctp")));
			$this->afterSave();
		}
		else
		{
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}
	
	/**
	 * After saving
	 */
	function afterSave()
	{
		$this->redirectToParent();
	}
	
	
	/**
	 * Update skill tree node
	 *
	 */
	public function update()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initForm("edit");
		if ($this->form->checkInput())
		{
			$this->updateItem();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$this->afterUpdate();
		}
		else
		{
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}
	
	/**
	 * After update
	 */
	function afterUpdate()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "editProperties");
	}
	
	/**
	 * Init  form.
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
			$this->form->setTitle($lng->txt("skmg_create_".$this->getType()));
		}
		else
		{
			$this->form->addCommandButton("update", $lng->txt("save"));
			$this->form->setTitle($lng->txt("skmg_edit_".$this->getType()));
		}

		
		$ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		$this->form->setFormAction($ilCtrl->getFormAction($this));

	}

	/**
	 * Cancel saving
	 *
	 * @param
	 * @return
	 */
	function cancelSave()
	{
		$this->redirectToParent();
	}

	/**
	 * Redirect to parent (identified by current obj_id)
	 *
	 * @param
	 * @return
	 */
	function redirectToParent($a_tmp_mode = false)
	{
		global $ilCtrl;
		
		if ($_GET["tmpmode"])
		{
			$a_tmp_mode = true;
		}
		
		$t = ilSkillTreeNode::_lookupType((int) $_GET["obj_id"]);

		switch ($t)
		{
			case "skrt":
				$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id", (int) $_GET["obj_id"]);
				if ($a_tmp_mode)
				{
					$ilCtrl->redirectByClass("ilskillrootgui", "listTemplates");
				}
				else
				{
					$ilCtrl->redirectByClass("ilskillrootgui", "listSkills");
				}
				break;

			case "sctp":
				$ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", (int) $_GET["obj_id"]);
				$ilCtrl->redirectByClass("ilskilltemplatecategorygui", "listItems");
				break;

			case "scat":
				$ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id", (int) $_GET["obj_id"]);
				$ilCtrl->redirectByClass("ilskillcategorygui", "listItems");
				break;
		} 
		
	}
	
	/**
	 * Save order
	 */
	function saveOrder()
	{
		global $ilCtrl, $lng;
		
		ilSkillTreeNode::saveChildsOrder((int) $_GET["obj_id"], $_POST["order"],
			(int) $_GET["tmpmode"]);
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$this->redirectToParent((int) $_GET["tmpmode"]);
	}

	/**
	 * Insert basic skills from clipboard
	 */
	function insertBasicSkillClip()
	{
		global $ilCtrl, $ilUser;

		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$nodes = ilSkillTreeNode::insertItemsFromClip("skll", (int) $_GET["obj_id"]);
		$this->redirectToParent();
	}

	/**
	 * Insert skill categories from clipboard
	 */
	function insertSkillCategoryClip()
	{
		global $ilCtrl, $ilUser;

		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$nodes = ilSkillTreeNode::insertItemsFromClip("scat", (int) $_GET["obj_id"]);
		$this->redirectToParent();
	}
	
	/**
	 * Insert skill template references from clipboard
	 */
	function insertTemplateReferenceClip()
	{
		global $ilCtrl, $ilUser;

		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$nodes = ilSkillTreeNode::insertItemsFromClip("sktr", (int) $_GET["obj_id"]);
		$this->redirectToParent();
	}
	
	/**
	 * Insert skill template from clipboard
	 */
	function insertSkillTemplateClip()
	{
		global $ilCtrl, $ilUser;

		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$nodes = ilSkillTreeNode::insertItemsFromClip("sktp", (int) $_GET["obj_id"]);
		$this->redirectToParent();
	}

	/**
	 * Insert skill template category from clipboard
	 */
	function insertTemplateCategoryClip()
	{
		global $ilCtrl, $ilUser;

		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$nodes = ilSkillTreeNode::insertItemsFromClip("sctp", (int) $_GET["obj_id"]);
		$this->redirectToParent();
	}
	
	/**
	 * Set title icon
	 */
	function setTitleIcon()
	{
		global $tpl;
		
		$obj_id = (is_object($this->node_object))
			? $this->node_object->getId()
			:0;
		$tpl->setTitleIcon(
			ilSkillTreeNode::getIconPath(
			$obj_id, $this->getType(), "",
			(ilSkillTreeNode::_lookupStatus($obj_id) == ilSkillTreeNode::STATUS_DRAFT)));
	}

	////
	//// Usage
	////

	/**
	 * Add usage tab
	 *
	 * @param
	 * @return
	 */
	function addUsageTab($a_tabs)
	{
		global $lng, $ilCtrl;

		$a_tabs->addTab("usage",
			$lng->txt("skmg_usage"),
			$ilCtrl->getLinkTarget($this, "showUsage"));
	}


	/**
	 * Show skill usage
	 */
	function showUsage()
	{
		global $tpl;

		$this->setTabs("usage");

		include_once("./Services/Skill/classes/class.ilSkillUsage.php");
		$usage_info = new ilSkillUsage();
		$base_skill_id = ($this->base_skill_id > 0)
			? $this->base_skill_id
			: $this->node_object->getId();
		$usages = $usage_info->getAllUsagesInfoOfSubtree($base_skill_id.":".$this->tref_id);

		$html = "";
		include_once("./Services/Skill/classes/class.ilSkillUsageTableGUI.php");
		foreach ($usages as $k => $usage)
		{
			$tab = new ilSkillUsageTableGUI($this, "showUsage", $k, $usage);
			$html.= $tab->getHTML()."<br/><br/>";
		}

		$tpl->setContent($html);
	}

}
?>
