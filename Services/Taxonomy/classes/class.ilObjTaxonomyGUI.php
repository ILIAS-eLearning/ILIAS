<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";
require_once "./Services/Taxonomy/classes/class.ilObjTaxonomy.php";

/**
 * Taxonomy GUI class
 *
 * @author Alex Killing alex.killing@gmx.de 
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjTaxonomyGUI:
 *
 * @ingroup ServicesTaxonomy
 */
class ilObjTaxonomyGUI extends ilObject2GUI
{
	
	/**
	 * Execute command
	 */
	function __construct($a_id = 0)
	{
		global $ilCtrl;
		
		parent::__construct($a_id, ilObject2GUI::OBJECT_ID);
		
		$ilCtrl->saveParameter($this, "tax_node");
	}
	
	/**
	 * Get type
	 *
	 * @return string type
	 */
	function getType()
	{
		return "tax";
	}

	/**
	 * Set assigned object
	 *
	 * @param int $a_val object id	
	 */
	function setAssignedObject($a_val)
	{
		$this->assigned_object_id = $a_val;
	}
	
	/**
	 * Get assigned object
	 *
	 * @return int object id
	 */
	function getAssignedObject()
	{
		return $this->assigned_object_id;
	}
	
	
	/**
	 * Execute command
	 */
	function executeCommand()
	{
		global $ilCtrl, $ilUser, $ilTabs;
		
		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd();

		switch ($next_class)
		{
			default:
				$this->$cmd();
				break;
		}
	}
	
	/**
	 * Init creation forms
	 */
	protected function initCreationForms()
	{
		$forms = array();
		
		$forms = array(
			self::CFORM_NEW => $this->initCreateForm("tax")
			);
		
		return $forms;
	}

	
	////
	//// Features that work on the base of an assigend object (AO)
	////
	
	/**
	 * 
	 *
	 * @param
	 * @return
	 */
	function editAOTaxonomySettings()
	{
		global $ilToolbar, $ilCtrl, $lng;
		
		$tax_ids = ilObjTaxonomy::getUsageOfObject($this->getAssignedObject());
		if (count($tax_ids) == 0)
		{
			$ilToolbar->addButton($lng->txt("tax_add_taxonomy"),
				$ilCtrl->getLinkTarget($this, "createAssignedTaxonomy"));
		}
		else
		{
			$this->listItems();
		}
		
		// currently we support only one taxonomy, otherwise we may need to provide
		// a list here
		
	}
	
	/**
	 * Determine current taxonomy (of assigned object)
	 *
	 * @param
	 * @return
	 */
	function determineAOCurrentTaxonomy()
	{
		// get taxonomy
		$tax_ids = ilObjTaxonomy::getUsageOfObject($this->getAssignedObject());
		$tax = new ilObjTaxonomy(current($tax_ids));
		return $tax;
	}
	
	
	/**
	 * List items
	 *
	 * @param
	 * @return
	 */
	function listItems()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;
		
		$tax = $this->determineAOCurrentTaxonomy();
		
		// show toolbar
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		$ilToolbar->addFormButton($lng->txt("tax_create_node"), "createTaxNode");
		
		// settings
		$ilToolbar->addSeparator();
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$options = array(
			ilObjTaxonomy::SORT_ALPHABETICAL => $lng->txt("tax_alphabetical"),
			ilObjTaxonomy::SORT_MANUAL => $lng->txt("tax_manual")
			);
		$si = new ilSelectInputGUI($lng->txt("tax_sorting"), "sorting");
		$si->setValue($this->determineAOCurrentTaxonomy()->getSortingMode());
		$si->setOptions($options);
		$ilToolbar->addInputItem($si, true);
		
		$ilToolbar->addFormButton($lng->txt("save"), "saveSettingsAndSorting");
		
		$ilToolbar->addSeparator();
		$ilToolbar->addFormButton($lng->txt("tax_delete_taxonomy"), "confirmDeleteTaxonomy");
		
		$ilToolbar->setCloseFormTag(false);
		
		
		// show tree
		$this->showTree($tax->getTree());
		
		// show subitems
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTableGUI.php");
		$table = new ilTaxonomyTableGUI($this, "listItems", $tax->getTree(),
			(int) $_GET["tax_node"], $this->determineAOCurrentTaxonomy());
		$table->setOpenFormTag(false);

		$tpl->setContent($table->getHTML());
	}
	
	
	/**
	 * Create assigned taxonomy
	 *
	 * @param
	 * @return
	 */
	function createAssignedTaxonomy()
	{
		$this->create();
	}
	
	
	/**
	 * If we run under an assigned object, the permission should be checked on
	 * the upper level
	 */
	protected function checkPermissionBool($a_perm, $a_cmd = "", $a_type = "", $a_node_id = null)
	{
		if ($this->getAssignedObject() > 0)
		{
			return true;
		}
		else
		{
			return parent::checkPermissionBool($a_perm, $a_cmd, $a_type, $a_node_id);
		}
	}
	
	/**
	 * Cancel creation
	 *
	 * @param
	 * @return
	 */
	function cancel()
	{
		global $ilCtrl;
		
		if ($this->getAssignedObject() > 0)
		{
			$ilCtrl->redirect($this, "editAOTaxonomySettings");
		}
		
		return parent::cancel();
	}
	
	/**
	 * Save taxonomy
	 *
	 * @param
	 * @return
	 */
	function save()
	{
		global $ilCtrl;
		
		if ($this->getAssignedObject() > 0)
		{
			$_REQUEST["new_type"] = "tax";
		}
		
		parent::saveObject();
	}
	
	/**
	 * After saving, 
	 *
	 * @param
	 * @return
	 */
	protected function afterSave(ilObject $a_new_object)
	{
		global $ilCtrl;

		if ($this->getAssignedObject() > 0)
		{
			ilObjTaxonomy::saveUsage($a_new_object->getId(),
				$this->getAssignedObject());
			$ilCtrl->redirect($this, "editAOTaxonomySettings");
		}
	}

	/**
	 * Show Editing Tree
	 */
	function showTree($a_tax_tree)
	{
		global $ilUser, $tpl, $ilCtrl, $lng;

		require_once ("./Services/Taxonomy/classes/class.ilTaxonomyExplorer.php");

		$exp = new ilTaxonomyExplorer($ilCtrl->getLinkTarget($this, "listItems"), $a_tax_tree);
		$exp->setTargetGet("tax_node");
		
		$exp->setExpandTarget($ilCtrl->getLinkTarget($this, "listItems"));
		
		if ($_GET["txexpand"] == "")
		{
			$expanded = $a_tax_tree->readRootId();
		}
		else
		{
			$expanded = $_GET["txexpand"];
		}

		if ($_GET["tax_node"] > 0)
		{
			$path = $a_tax_tree->getPathId($_GET["tax_node"]);
			$exp->setForceOpenPath($path);
			$exp->highlightNode($_GET["tax_node"]);
		}
		else
		{
			$exp->highlightNode($a_tax_tree->readRootId());
		}
		$exp->setExpand($expanded);
		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		// asynchronous output
		if ($ilCtrl->isAsynch())
		{
			echo $output; exit;
		}
		
		$tpl->setLeftContent($output);
	}
	
	/**
	 * Get tree html
	 *
	 * @param
	 * @return
	 */
	static function getTreeHTML($a_tax_id, $a_class, $a_cmd, $a_root_node_title = "")
	{
		global $ilUser, $tpl, $ilCtrl, $lng;

		$lng->loadLanguageModule("tax");
		
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
		require_once ("./Services/Taxonomy/classes/class.ilTaxonomyExplorer.php");
		$a_tax_tree = new ilTaxonomyTree($a_tax_id);

		$exp = new ilTaxonomyExplorer($ilCtrl->getLinkTargetByClass($a_class, $a_cmd), $a_tax_tree,
			$a_class, $a_cmd);
		$exp->setTargetGet("tax_node");
		
		if ($a_root_node_title != "")
		{
			$exp->setRootNodeTitle($a_root_node_title);
		}
		
		$exp->setExpandTarget($ilCtrl->getLinkTargetByClass($a_class, $a_cmd));
		
		if ($_GET["txexpand"] == "")
		{
			$expanded = $a_tax_tree->readRootId();
		}
		else
		{
			$expanded = $_GET["txexpand"];
		}

		if ($_GET["tax_node"] > 0)
		{
			$path = $a_tax_tree->getPathId($_GET["tax_node"]);
			$exp->setForceOpenPath($path);
			$exp->highlightNode($_GET["tax_node"]);
		}
		else
		{
			$exp->highlightNode($a_tax_tree->readRootId());
		}
		$exp->setExpand($expanded);
		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		return $output;
	}
	

	/**
	 * Create tax node
	 *
	 * @param
	 * @return
	 */
	function createTaxNode()
	{
		global $tpl;
		
		$this->initTaxNodeForm("create");
		$tpl->setContent($this->form->getHTML());
	}
	
	
	/**
	 * Init tax node form
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	public function initTaxNodeForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$this->form->addItem($ti);
		
		// order nr
		$tax = $this->determineAOCurrentTaxonomy();
		if ($tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL)
		{
			$or = new ilTextInputGUI($this->lng->txt("tax_order_nr"), "order_nr");
			$or->setMaxLength(5);
			$or->setSize(5);
			$this->form->addItem($or);
		}
		
		if ($a_mode == "edit")
		{
			$node = new ilTaxonomyNode((int) $_GET["tax_node"]);
			$ti->setValue($node->getTitle());
			$or->setValue($node->getOrderNr());
		}
		
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("saveTaxNode", $lng->txt("save"));
			$this->form->addCommandButton("listItems", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("tax_new_tax_node"));
		}
		else
		{
			$this->form->addCommandButton("updateTaxNode", $lng->txt("save"));
			$this->form->addCommandButton("listItems", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("tax_edit_tax_node"));
		}
	                
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	 
	}
	
	/**
	 * Save tax node form
	 *
	 */
	public function saveTaxNode()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initTaxNodeForm("create");
		if ($this->form->checkInput())
		{
			$tax = $this->determineAOCurrentTaxonomy();
			
			// create node
			include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
			$node = new ilTaxonomyNode();
			$node->setTitle($this->form->getInput("title"));
			
			$tax = $this->determineAOCurrentTaxonomy();
			if ($tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL)
			{
				$order_nr = $this->form->getInput("order_nr");
			}
			if ($order_nr === "")
			{
				$order_nr = ilTaxonomyNode::getNextOrderNr($tax->getId(), (int) $_GET["tax_node"]);
			}
			$node->setOrderNr($order_nr);
			$node->setTaxonomyId($tax->getId());
			$node->create();
			
			// put in tree
			ilTaxonomyNode::putInTree($tax->getId(), $node, (int) $_GET["tax_node"]);
			
			ilTaxonomyNode::fixOrderNumbers($tax->getId(), (int) $_GET["tax_node"]);
			
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "listItems");
		}
		else
		{
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}
	
	
	/**
	 * Update tax node
	 */
	function updateTaxNode()
	{
		global $lng, $ilCtrl, $tpl;
		
		$this->initTaxNodeForm("edit");
		if ($this->form->checkInput())
		{
			// create node
			$node = new ilTaxonomyNode($_GET["tax_node"]);
			$node->setTitle($this->form->getInput("title"));

			$tax = $this->determineAOCurrentTaxonomy();
			if ($tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL)
			{
				$node->setOrderNr($this->form->getInput("order_nr"));
			}

			$node->update();

			ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "");
		}
		else
		{
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}
	
	/**
	 * Confirm deletion screen for items
	 */
	function deleteItems()
	{
		global $lng, $tpl, $ilCtrl, $ilTabs;

		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

//		$ilTabs->clearTargets();
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$confirmation_gui = new ilConfirmationGUI();

		$confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
		$confirmation_gui->setHeaderText($this->lng->txt("info_delete_sure"));

		// Add items to delete
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
		foreach($_POST["id"] as $id)
		{
			$confirmation_gui->addItem("id[]", $id,
				ilTaxonomyNode::_lookupTitle($id));
		}

		$confirmation_gui->setCancel($lng->txt("cancel"), "listItems");
		$confirmation_gui->setConfirm($lng->txt("confirm"), "confirmedDelete");

		$tpl->setContent($confirmation_gui->getHTML());
	}

	/**
	 * Delete taxonomy nodes
	 */
	function confirmedDelete()
	{
		global $ilCtrl;
		
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");

		// delete all selected objects
		foreach ($_POST["id"] as $id)
		{
			$node = new ilTaxonomyNode($id);
			$tax = new ilObjTaxonomy($node->getTaxonomyId());
			$tax_tree = $tax->getTree();
			$node_data = $tax_tree->getNodeData($id);
			if (is_object($node))
			{
				$node->delete();
			}
			if($tax_tree->isInTree($id))
			{
				$tax_tree->deleteTree($node_data);
			}
			ilTaxonomyNode::fixOrderNumbers($node->getTaxonomyId(), $node_data["parent"]);
		}

		// feedback
		ilUtil::sendInfo($this->lng->txt("info_deleted"),true);
		
		$ilCtrl->redirect($this, "listItems");
	}

	/**
	 * Save settings and sorting
	 *
	 * @param
	 * @return
	 */
	function saveSettingsAndSorting()
	{
		global $ilCtrl, $lng;
		
		// save settings
		$tax = $this->determineAOCurrentTaxonomy();
		$tax->setSortingMode(ilUtil::stripSlashes($_POST["sorting"]));
		$tax->update();
		
		// save sorting
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
		if (is_array($_POST["order"]))
		{
			asort($_POST["order"]);
			$cnt = 10;
			foreach ($_POST["order"] as $k => $v)
			{
				ilTaxonomyNode::writeOrderNr(ilUtil::stripSlashes($k), $cnt);
				$cnt+= 10;
			}
		}
		
		// save titles
		if (is_array($_POST["title"]))
		{
			foreach ($_POST["title"] as $k => $v)
			{
				ilTaxonomyNode::writeTitle((int) $k,
					ilUtil::stripSlashes($v));
			}
		}

		
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"));
		$ilCtrl->redirect($this, "listItems");
	}
	
	/**
	 * Move items
	 */
	function moveItems()
	{
		global $tpl, $ilCtrl, $lng, $ilToolbar;
		
		$ilToolbar->addButton($lng->txt("cancel"),
			$ilCtrl->getLinkTarget($this, "listItems"));
		
		ilUtil::sendInfo($lng->txt("tax_please_select_target"));
		
		if (is_array($_POST["id"]))
		{
			$ilCtrl->setParameter($this, "move_ids", implode($_POST["id"], ","));
			
			global $ilUser, $tpl, $ilCtrl, $lng;

			require_once ("./Services/Taxonomy/classes/class.ilTaxonomyExplorer.php");

			$exp = new ilTaxonomyExplorer($ilCtrl->getLinkTarget($this, "pasteItems"),
				$this->determineAOCurrentTaxonomy()->getTree(),
				"ilobjtaxonomygui", "pasteItems");
			$exp->forceExpandAll(true, false);
			$exp->setTargetGet("tax_node");
		
			$exp->setExpandTarget($ilCtrl->getLinkTarget($this, "pasteItems"));
		
			// build html-output
			$exp->setOutput(0);
			$output = $exp->getOutput();
			
			$tpl->setContent($output);
		}
	}
	
	/**
	 * Paste items (move operation)
	 */
	function pasteItems()
	{
		global $lng, $ilCtrl;
//var_dump($_GET);
//var_dump($_POST);
		if ($_GET["move_ids"] != "")
		{
			$move_ids = explode(",", $_GET["move_ids"]);
			$tax = $this->determineAOCurrentTaxonomy();
			$tree = $tax->getTree();
			
			include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
			$target_node = new ilTaxonomyNode((int) $_GET["tax_node"]);
			foreach ($move_ids as $m_id)
			{
				// cross check taxonomy
				$node = new ilTaxonomyNode((int) $m_id);
				if ($node->getTaxonomyId() == $tax->getId() &&
					($target_node->getTaxonomyId() == $tax->getId() ||
					$target_node->getId() == $tree->readRootId()))
				{
					// check if target is not within the selected nodes
					if($tree->isGrandChild((int) $m_id, $target_node->getId()))
					{
						ilUtil::sendFailure($lng->txt("tax_target_within_nodes"), true);
						$this->ctrl->redirect($this, "listItems");
					}
					
					// if target is not current place, move
					$parent_id = $tree->getParentId((int) $m_id);
					if ($parent_id != $target_node->getId())
					{
						$tree->moveTree((int) $m_id, $target_node->getId());
						ilTaxonomyNode::fixOrderNumbers($tax->getId(), $target_node->getId());
						ilTaxonomyNode::fixOrderNumbers($tax->getId(), $parent_id);
					}
				}
			}
		}

		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "listItems");
	}
	
	/**
	 * Confirm taxonomy deletion
	 */
	function confirmDeleteTaxonomy()
	{
		global $ilCtrl, $tpl, $lng;

		$tax = $this->determineAOCurrentTaxonomy();
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($ilCtrl->getFormAction($this));
		$cgui->setHeaderText($lng->txt("tax_confirm_deletion"));
		$cgui->setCancel($lng->txt("cancel"), "listItems");
		$cgui->setConfirm($lng->txt("delete"), "deleteTaxonomy");
		
		$cgui->addItem("id[]", $i, $tax->getTitle());
		
		$tpl->setContent($cgui->getHTML());
	}
	
	/**
	 * Delete taxonomy
	 *
	 * @param
	 * @return
	 */
	function deleteTaxonomy()
	{
		global $ilCtrl, $lng;
		
		$tax = $this->determineAOCurrentTaxonomy();
		$tax->delete();
		
		ilUtil::sendSuccess($lng->txt("tax_tax_deleted"), true);
		$ilCtrl->redirect($this, "editAOTaxonomySettings");
	}
	
}
?>