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
	 * Insert Chapter
	 */
/*	function insertChapter()
	{
		global $ilCtrl;
		
		$res = $this->getParentGUI()->insertChapter(false);
		$ilCtrl->setParameter($this, "highlight", $res["items"]);
		$ilCtrl->redirect($this, "showOrganization", "node_".$res["node_id"]);
	}
*/
	/**
	* Insert Sco
	*/
/*	function insertSco()
	{
		global $ilCtrl;
		
		$res = $this->getParentGUI()->insertSco(false);
		$ilCtrl->setParameter($this, "highlight", $res["items"]);
		$ilCtrl->redirect($this, "showOrganization", "node_".$res["node_id"]);
	}
*/

	/**
	 * Collapse all
	 */
	function collapseAll()
	{
		global $ilCtrl;
		
		$this->getParentGUI()->collapseAll(false);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	 * Expand all
	 */
	function ExpandAll()
	{
		global $ilCtrl;
		
		$this->getParentGUI()->expandAll(false);
		$ilCtrl->redirect($this, "showOrganization");
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
	 * Cut items
	 */
	function cutItems()
	{
		global $ilCtrl;

		$this->getParentGUI()->cutItems();
	}

	/**
	 * Copy items
	 */
	function copyItems()
	{
		global $ilCtrl;

		$this->getParentGUI()->copyItems();
	}

	/**
	 * cancel delete
	 */
	function cancelDelete()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "listItems");
	}

	/**
	 * confirmed delete
	 */
	function confirmedDelete()
	{
		global $ilCtrl;
		
		$this->getParentGUI()->confirmedDelete(false);
		$ilCtrl->redirect($this, "listItems");
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
							ilUtil::getImagePath("icon_skmg_s.gif"));
						break;

					case "skll":
						$ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id",
							$path[$i]["child"]);
						$ilLocator->addItem($path[$i]["title"],
							$ilCtrl->getLinkTargetByClass("ilskillmanagementgui",
							"ilbasicskillgui"), "", 0, $path[$i]["type"],
							ilUtil::getImagePath("icon_skmg_s.gif"));
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
			$path = $this->node_object->skill_tree->getPathFull($this->node_object->getId());
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
	function redirectToParent()
	{
		global $ilCtrl;
		
		$t = ilSkillTreeNode::_lookupType((int) $_GET["obj_id"]);

		switch ($t)
		{
			case "skrt":
				$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id", (int) $_GET["obj_id"]);
				$ilCtrl->redirectByClass("ilskillrootgui", "listTemplates");
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
	
}
?>
