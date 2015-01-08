<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";
require_once "./Services/Taxonomy/classes/class.ilObjTaxonomy.php";
include_once("./Services/Taxonomy/interfaces/interface.ilTaxAssignedItemInfo.php");

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
	protected $multiple = false;
	protected $assigned_item_sorting = false;
	
	/**
	 * Execute command
	 */
	function __construct($a_id = 0)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_id, ilObject2GUI::OBJECT_ID);
		
		$ilCtrl->saveParameter($this, "tax_node");
		$ilCtrl->saveParameter($this, "tax_id");
		
		$lng->loadLanguageModule("tax");
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
	 * Set multiple
	 *
	 * @param bool $a_val multiple	
	 */
	function setMultiple($a_val)
	{
		$this->multiple = $a_val;
	}
	
	/**
	 * Get multiple
	 *
	 * @return bool multiple
	 */
	function getMultiple()
	{
		return $this->multiple;
	}
	
	/**
	 * Set list info
	 *
	 * @param string $a_val
	 */
	function setListInfo($a_val)
	{
		$this->list_info = trim($a_val);
	}
	
	/**
	 * Get list info
	 *
	 * @return string
	 */
	function getListInfo()
	{
		return $this->list_info;
	}		
	
	/**
	 * Activate sorting mode of assigned objects
	 *
	 * @param object $a_item_info_obj information object of assigned items
	 */
	function activateAssignedItemSorting(ilTaxAssignedItemInfo $a_item_info_obj, $a_component_id, $a_obj_id, $a_item_type)
	{
		$this->assigned_item_sorting = true;
		$this->assigned_item_info_obj = $a_item_info_obj;
		$this->assigned_item_comp_id = $a_component_id;
		$this->assigned_item_obj_id = $a_obj_id;
		$this->assigned_item_type = $a_item_type;
	}
	
	
	/**
	 * Execute command
	 */
	function executeCommand()
	{
		global $ilCtrl, $ilUser, $ilTabs;
		
		$next_class = $ilCtrl->getNextClass();

		switch ($next_class)
		{
			default:
				$cmd = $ilCtrl->getCmd("listTaxonomies");
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
		
		
//		if (count($tax_ids) != 0 && !$this->getMultiple())
//		{
//			$this->listNodes();
//		}
//		else if ($this->getMultiple())
//		{
			$this->listTaxonomies();
//		}
		
		// currently we support only one taxonomy, otherwise we may need to provide
		// a list here
		
	}
	
	/**
	 * Get current taxonomy id
	 *
	 * @param
	 * @return
	 */
	function getCurrentTaxonomyId()
	{
		$tax_ids = ilObjTaxonomy::getUsageOfObject($this->getAssignedObject());
		$tax_id = (int) $_GET["tax_id"]; 
		if (in_array($tax_id, $tax_ids))
		{
			return $tax_id;
		}
		return false;
	}
	
	
	/**
	 * Get current taxonomy
	 *
	 * @param
	 * @return
	 */
	function getCurrentTaxonomy()
	{
		$tax_id = $this->getCurrentTaxonomyId();
		if ($tax_id > 0)
		{
			$tax = new ilObjTaxonomy($tax_id);
			return $tax;
		}
		
		return false;
	}
	
	
	/**
	 * List items
	 *
	 * @param
	 * @return
	 */
	function listNodes()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;
		
		$tax = $this->getCurrentTaxonomy();
		
		$this->setTabs("list_items");
		
		// show toolbar
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		$ilToolbar->addFormButton($lng->txt("tax_create_node"), "createTaxNode");
		
		$ilToolbar->setCloseFormTag(false);
		
		
		// show tree
		$this->showTree();
		
		// show subitems
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTableGUI.php");
		$table = new ilTaxonomyTableGUI($this, "listNodes", $tax->getTree(),
			(int) $_GET["tax_node"], $this->getCurrentTaxonomy());
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
			$ilCtrl->redirect($this, "listTaxonomies");
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
		global $ilCtrl, $lng;

		if ($this->getAssignedObject() > 0)
		{
			ilObjTaxonomy::saveUsage($a_new_object->getId(),
				$this->getAssignedObject());
			$ilCtrl->setParameter($this, "tax_id", $a_new_object->getId());
			ilUtil::sendSuccess($lng->txt("tax_added"), true);
			$ilCtrl->redirect($this, "editSettings");
		}
	}

	/**
	 * Show Editing Tree
	 */
	function showTree($a_ass_items = false)
	{
		global $ilUser, $tpl, $ilCtrl, $lng;

		$tax = $this->getCurrentTaxonomy();
		
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyExplorerGUI.php");
		$cmd = $a_ass_items
			? "listAssignedItems"
			: "listNodes";
		$tax_exp = new ilTaxonomyExplorerGUI($this, "showTree", $tax->getId(),
			"ilobjtaxonomygui", $cmd);
		if (!$tax_exp->handleCommand())
		{
			//$tpl->setLeftNavContent($tax_exp->getHTML());
			$tpl->setLeftContent($tax_exp->getHTML()."&nbsp;");
		}
		return;
	}
	
	/**
	 * Get tree html
	 *
	 * @param
	 * @return
	 */
	static function getTreeHTML($a_tax_id, $a_class, $a_cmd,
		$a_target_class, $a_target_cmd, $a_root_node_title = "")
	{
die("ilObjTaxonomyGUI::getTreeHTML is deprecated.");
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyExplorerGUI.php");
		$tax_exp = new ilTaxonomyExplorerGUI($a_class, $a_cmd, $a_tax_id,
			$a_target_class, $a_target_cmd);
		if (!$tax_exp->handleCommand())
		{
			return $tax_exp->getHTML()."&nbsp;";
		}
		return;
	}
	

	/**
	 * Create tax node
	 *
	 * @param
	 * @return
	 */
	function createTaxNode()
	{
		global $tpl, $ilHelp;

		$this->setTabs("list_items");
		$ilHelp->setSubScreenId("create_node");
		
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
		$tax = $this->getCurrentTaxonomy();
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
			$this->form->addCommandButton("listNodes", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("tax_new_tax_node"));
		}
		else
		{
			$this->form->addCommandButton("updateTaxNode", $lng->txt("save"));
			$this->form->addCommandButton("listNodes", $lng->txt("cancel"));
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
			$tax = $this->getCurrentTaxonomy();
			
			// create node
			include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
			$node = new ilTaxonomyNode();
			$node->setTitle($this->form->getInput("title"));
			
			$tax = $this->getCurrentTaxonomy();
			if ($tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL)
			{
				$order_nr = $this->form->getInput("order_nr");
			}
			if ($order_nr === "")
			{
				$order_nr = ilTaxonomyNode::getNextOrderNr($tax->getId(), (int) $_GET["tax_node"]);
			}
	//echo $order_nr; exit;
			$node->setOrderNr($order_nr);
			$node->setTaxonomyId($tax->getId());
			$node->create();
			
			// put in tree
			ilTaxonomyNode::putInTree($tax->getId(), $node, (int) $_GET["tax_node"]);
			
			ilTaxonomyNode::fixOrderNumbers($tax->getId(), (int) $_GET["tax_node"]);
			
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "listNodes");
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

			$tax = $this->getCurrentTaxonomy();
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
		global $lng, $tpl, $ilCtrl, $ilTabs, $ilHelp;

		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		$this->setTabs("list_items");
		$ilHelp->setSubScreenId("del_items");

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

		$confirmation_gui->setCancel($lng->txt("cancel"), "listNodes");
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
		
		$ilCtrl->redirect($this, "listNodes");
	}

	/**
	 * Save settings and sorting
	 *
	 * @param
	 * @return
	 */
	function saveSorting()
	{
		global $ilCtrl, $lng;
		
		// save sorting
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
		if (is_array($_POST["order"]))
		{
//			asort($_POST["order"]);
//			$cnt = 10;
			foreach ($_POST["order"] as $k => $v)
			{
				ilTaxonomyNode::writeOrderNr(ilUtil::stripSlashes($k), $v);
				ilTaxonomyNode::fixOrderNumbers($this->getCurrentTaxonomyId(), (int) $_GET["tax_node"]);
//				$cnt+= 10;
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
		$ilCtrl->redirect($this, "listNodes");
	}
	
	/**
	 * Move items
	 */
	function moveItems()
	{
		global $tpl, $ilCtrl, $lng, $ilToolbar, $ilHelp;

		$this->setTabs("list_items");
		$ilHelp->setSubScreenId("move_items");

		$ilToolbar->addButton($lng->txt("cancel"),
			$ilCtrl->getLinkTarget($this, "listNodes"));
		
		ilUtil::sendInfo($lng->txt("tax_please_select_target"));
		
		if (is_array($_POST["id"]))
		{
			$ilCtrl->setParameter($this, "move_ids", implode($_POST["id"], ","));
			
			global $ilUser, $tpl, $ilCtrl, $lng;

			include_once("./Services/Taxonomy/classes/class.ilTaxonomyExplorerGUI.php");
			$tax_exp = new ilTaxonomyExplorerGUI($this, "moveItems", $this->getCurrentTaxonomy()->getId(),
				"ilobjtaxonomygui", "pasteItems");
			if (!$tax_exp->handleCommand())
			{
				//$tpl->setLeftNavContent($tax_exp->getHTML());
				$tpl->setContent($tax_exp->getHTML()."&nbsp;");
			}
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
			$tax = $this->getCurrentTaxonomy();
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
						$this->ctrl->redirect($this, "listNodes");
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
		$ilCtrl->redirect($this, "listNodes");
	}
	
	/**
	 * Confirm taxonomy deletion
	 */
	function confirmDeleteTaxonomy()
	{
		global $ilCtrl, $tpl, $lng;

		$tax = $this->getCurrentTaxonomy();
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($ilCtrl->getFormAction($this));
		$cgui->setHeaderText($lng->txt("tax_confirm_deletion"));
		$cgui->setCancel($lng->txt("cancel"), "listTaxonomies");
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
		
		$tax = $this->getCurrentTaxonomy();
		$tax->delete();
		
		ilUtil::sendSuccess($lng->txt("tax_tax_deleted"), true);
		$ilCtrl->redirect($this, "listTaxonomies");
	}

	/**
	 * List taxonomies
	 *
	 * @param
	 * @return
	 */
	function listTaxonomies()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;
		
		$tax_ids = ilObjTaxonomy::getUsageOfObject($this->getAssignedObject());
		if (count($tax_ids) == 0 || $this->getMultiple())
		{
			$ilToolbar->addButton($lng->txt("tax_add_taxonomy"),
				$ilCtrl->getLinkTarget($this, "createAssignedTaxonomy"));
		}
		else
		{
			ilUtil::sendInfo($lng->txt("tax_max_one_tax"));
		}
		
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyListTableGUI.php");
		
		$tab = new ilTaxonomyListTableGUI($this, "listTaxonomies", $this->getAssignedObject(),
			$this->getListInfo());
		
		$tpl->setContent($tab->getHTML());
	}
	
	/**
	 * Set tabs
	 *
	 * @param $a_id string tab id to be activated
	 */
	function setTabs($a_id)
	{
		global $ilTabs, $ilCtrl, $tpl, $lng, $ilHelp;
		
		$ilTabs->clearTargets();

		$ilHelp->setScreenIdComponent("tax");

		$tpl->clearHeader();
		$tpl->setTitle(ilObject::_lookupTitle($this->getCurrentTaxonomyId()));
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_tax.svg"));
		
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "listTaxonomies"));
		
		$ilTabs->addTab("list_items", $lng->txt("tax_nodes"),
			$ilCtrl->getLinkTarget($this, "listNodes"));
		if ($this->assigned_item_sorting)
		{
			$ilTabs->addTab("ass_items", $lng->txt("tax_assigned_items"),
				$ilCtrl->getLinkTarget($this, "listAssignedItems"));
		}
		$ilTabs->addTab("settings", $lng->txt("settings"),
			$ilCtrl->getLinkTarget($this, "editSettings"));
		
		$ilTabs->activateTab($a_id);
	}
	
	/**
	 * Edit settings
	 *
	 * @param
	 * @return
	 */
	function editSettings()
	{
		global $tpl;
		
		$this->setTabs("settings");
		
		$form = $this->initSettingsForm();
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Init  form.
	 */
	public function initSettingsForm()
	{
		global $lng, $ilCtrl;
	
		$tax = $this->getCurrentTaxonomy();
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
	
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(200);
		$form->addItem($ti);
		$ti->setValue($tax->getTitle());
		
		// description
		$ta = new ilTextAreaInputGUI($lng->txt("description"), "description");
		//$ta->setCols();
		//$ta->setRows();
		$form->addItem($ta);
		$ta->setValue($tax->getDescription());

		// sorting
		$options = array(
			ilObjTaxonomy::SORT_ALPHABETICAL => $lng->txt("tax_alphabetical"),
			ilObjTaxonomy::SORT_MANUAL => $lng->txt("tax_manual")
			);
		$si = new ilSelectInputGUI($lng->txt("tax_node_sorting"), "sorting");
		$si->setOptions($options);
		$form->addItem($si);
		$si->setValue($tax->getSortingMode());
		
		// assigned item sorting
		if ($this->assigned_item_sorting)
		{
			$cb = new ilCheckboxInputGUI($lng->txt("tax_item_sorting"), "item_sorting");
			$cb->setChecked($tax->getItemSorting());
			$form->addItem($cb);
		}
	
		$form->addCommandButton("updateSettings", $lng->txt("save"));
	                
		$form->setTitle($lng->txt("settings"));
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		return $form;
	}
	
	/**
	 * Update taxonomy settings
	 */
	public function updateSettings()
	{
		global $tpl, $lng, $ilCtrl;
	
		$form = $this->initSettingsForm();
		if ($form->checkInput())
		{
			$tax = $this->getCurrentTaxonomy();
			$tax->setTitle($form->getInput("title"));
			$tax->setDescription($form->getInput("description"));
			$tax->setSortingMode($form->getInput("sorting"));
			$tax->setItemSorting($form->getInput("item_sorting"));
			$tax->update();

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editSettings");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}
	
	/**
	 * List assigned items
	 *
	 * @param
	 * @return
	 */
	function listAssignedItems()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;
		
		$tax = $this->getCurrentTaxonomy();
		
		$this->setTabs("ass_items");
				
		// show tree
		$this->showTree(true);
		
		// list assigned items
		include_once("./Services/Taxonomy/classes/class.ilTaxAssignedItemsTableGUI.php");
		$table = new ilTaxAssignedItemsTableGUI($this, "listAssignedItems",
			(int) $_GET["tax_node"], $this->getCurrentTaxonomy(), $this->assigned_item_comp_id,
			$this->assigned_item_obj_id, $this->assigned_item_type, $this->assigned_item_info_obj);

		$tpl->setContent($table->getHTML());
	}

	/**
	 * Save assigned items sorting
	 *
	 * @param
	 * @return
	 */
	function saveAssignedItemsSorting()
	{
		global $lng, $ilCtrl;
		
		include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
		if (is_array($_POST["order"]))
		{
			$order = $_POST["order"];
			//asort($order, SORT_NUMERIC);
			//$cnt = 10;
			$tax_node = (int) $_GET["tax_node"];
			foreach ($order as $a_item_id => $ord_nr)
			{
				$tax_ass = new ilTaxNodeAssignment($this->assigned_item_comp_id,
					$this->assigned_item_obj_id,
					$this->assigned_item_type, $this->getCurrentTaxonomyId());
				$tax_ass->setOrderNr($tax_node, $a_item_id, $ord_nr);
				//$cnt+= 10;
			}
			$tax_ass = new ilTaxNodeAssignment($this->assigned_item_comp_id,
				$this->assigned_item_obj_id,
				$this->assigned_item_type, $this->getCurrentTaxonomyId());
			$tax_ass->fixOrderNr($tax_node);
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
		$ilCtrl->redirect($this, "listAssignedItems");
	}
	
}
?>