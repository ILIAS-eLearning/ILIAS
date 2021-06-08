<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Taxonomy GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjTaxonomyGUI: ilObjTaxonomyGUI
 */
class ilObjTaxonomyGUI extends ilObject2GUI
{
    protected \ilTabsGUI $tabs;
    protected \ilHelpGUI $help;
    protected bool $multiple = false;
    protected bool $assigned_item_sorting = false;
    protected int $assigned_object_id;
    protected string $list_info;
    protected int $current_tax_node;
    protected int $requested_tax_id;
    protected string $requested_move_ids;

    /**
     * @inheritDoc
     */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC["tpl"];
        $this->help = $DIC["ilHelp"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_id, ilObject2GUI::OBJECT_ID);
        
        $ilCtrl->saveParameter($this, "tax_node");
        $ilCtrl->saveParameter($this, "tax_id");
        
        $lng->loadLanguageModule("tax");

        $params = $DIC->http()->request()->getQueryParams();
        $this->current_tax_node = (int) ($params["tax_node"] ?? null);
        $this->requested_tax_id = (int) ($params["tax_id"] ?? null);
        $this->requested_move_ids = (string) ($params["move_ids"] ?? "");
    }
    
    /**
     * Get type
     *
     * @return string type
     */
    public function getType()
    {
        return "tax";
    }

    /**
     * @param int $a_val object id
     */
    public function setAssignedObject(int $a_val)
    {
        $this->assigned_object_id = $a_val;
    }
    
    public function getAssignedObject() : int
    {
        return $this->assigned_object_id;
    }
    
    /**
     * Set multiple
     *
     * @param bool $a_val multiple
     */
    public function setMultiple(bool $a_val)
    {
        $this->multiple = $a_val;
    }
    
    public function getMultiple() : bool
    {
        return $this->multiple;
    }
    
    public function setListInfo(string $a_val)
    {
        $this->list_info = trim($a_val);
    }
    
    public function getListInfo() : string
    {
        return $this->list_info;
    }
    
    /**
     * Activate sorting mode of assigned objects
     * @param ilTaxAssignedItemInfo $a_item_info_obj
     * @param string                $a_component_id
     * @param int                   $a_obj_id
     * @param string                $a_item_type
     */
    public function activateAssignedItemSorting(
        ilTaxAssignedItemInfo $a_item_info_obj,
        string $a_component_id,
        int $a_obj_id,
        string $a_item_type
    ) {
        $this->assigned_item_sorting = true;
        $this->assigned_item_info_obj = $a_item_info_obj;
        $this->assigned_item_comp_id = $a_component_id;
        $this->assigned_item_obj_id = $a_obj_id;
        $this->assigned_item_type = $a_item_type;
    }
    
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $ilTabs = $this->tabs;
        
        $next_class = $ilCtrl->getNextClass();

        switch ($next_class) {
            default:
                $cmd = $ilCtrl->getCmd("listTaxonomies");
                $this->$cmd();
                break;
        }
    }
    
    /**
     * Init creation forms
     */
    protected function initCreationForms($a_new_type)
    {
        $forms = array(
            self::CFORM_NEW => $this->initCreateForm("tax")
            );
        
        return $forms;
    }

    
    ////
    //// Features that work on the base of an assigned object (AO)
    ////
    
    public function editAOTaxonomySettings() : void
    {
        $this->listTaxonomies();
    }
    
    public function getCurrentTaxonomyId() : ?int
    {
        $tax_ids = ilObjTaxonomy::getUsageOfObject($this->getAssignedObject());
        $tax_id = $this->requested_tax_id;
        if (in_array($tax_id, $tax_ids)) {
            return $tax_id;
        }
        return null;
    }
    
    
    /**
     * Get current taxonomy
     *
     * @param
     * @return
     */
    public function getCurrentTaxonomy()
    {
        $tax_id = $this->getCurrentTaxonomyId();
        if ($tax_id > 0) {
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
    public function listNodes()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $tax = $this->getCurrentTaxonomy();
        
        $this->setTabs("list_items");
        
        // show toolbar
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
        $ilToolbar->addFormButton($lng->txt("tax_create_node"), "createTaxNode");
        
        $ilToolbar->setCloseFormTag(false);
        
        
        // show tree
        $this->showTree();
        
        // show subitems
        $table = new ilTaxonomyTableGUI(
            $this,
            "listNodes",
            $tax->getTree(),
            $this->current_tax_node,
            $this->getCurrentTaxonomy()
        );
        $table->setOpenFormTag(false);

        $tpl->setContent($table->getHTML());
    }
    
    
    /**
     * Create assigned taxonomy
     *
     * @param
     * @return
     */
    public function createAssignedTaxonomy()
    {
        $this->create();
    }
    
    
    /**
     * If we run under an assigned object, the permission should be checked on
     * the upper level
     */
    protected function checkPermissionBool($a_perm, $a_cmd = "", $a_type = "", $a_node_id = null)
    {
        if ($this->getAssignedObject() > 0) {
            return true;
        } else {
            return parent::checkPermissionBool($a_perm, $a_cmd, $a_type, $a_node_id);
        }
    }
    
    /**
     * Cancel creation
     *
     * @param
     * @return
     */
    public function cancel()
    {
        $ilCtrl = $this->ctrl;
        
        if ($this->getAssignedObject() > 0) {
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
    public function save()
    {
        if ($this->getAssignedObject() > 0) {
            $this->requested_new_type = "tax";
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
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->getAssignedObject() > 0) {
            ilObjTaxonomy::saveUsage(
                $a_new_object->getId(),
                $this->getAssignedObject()
            );
            $ilCtrl->setParameter($this, "tax_id", $a_new_object->getId());
            ilUtil::sendSuccess($lng->txt("tax_added"), true);
            $ilCtrl->redirect($this, "editSettings");
        }
    }

    /**
     * Show Editing Tree
     * @param bool $a_ass_items
     * @throws ilCtrlException
     */
    public function showTree($a_ass_items = false)
    {
        global $DIC;

        $tax = $this->getCurrentTaxonomy();
        $ctrl = $this->ctrl;

        $cmd = $a_ass_items
            ? "listAssignedItems"
            : "listNodes";

        $DIC->globalScreen()->tool()->context()->current()
            ->addAdditionalData(
                ilTaxonomyGSToolProvider::SHOW_TAX_TREE,
                true
            );
        $DIC->globalScreen()->tool()->context()->current()
            ->addAdditionalData(
                ilTaxonomyGSToolProvider::TAX_TREE_GUI_PATH,
                $ctrl->getCurrentClassPath()
            );
        $DIC->globalScreen()->tool()->context()->current()
            ->addAdditionalData(
                ilTaxonomyGSToolProvider::TAX_ID,
                $tax->getId()
            );
        $DIC->globalScreen()->tool()->context()->current()
            ->addAdditionalData(
                ilTaxonomyGSToolProvider::TAX_TREE_CMD,
                $cmd
            );
        $DIC->globalScreen()->tool()->context()->current()
            ->addAdditionalData(
                ilTaxonomyGSToolProvider::TAX_TREE_PARENT_CMD,
                "showTree"
            );

        $tax_exp = new ilTaxonomyExplorerGUI(
            $this,
            "showTree",
            $tax->getId(),
            "ilobjtaxonomygui",
            $cmd
        );
        $tax_exp->handleCommand();
        return;
    }
    
    /**
     * Create tax node
     *
     * @param
     * @return
     */
    public function createTaxNode()
    {
        $tpl = $this->tpl;
        $ilHelp = $this->help;

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
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $this->form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $this->form->addItem($ti);
        
        // order nr
        $tax = $this->getCurrentTaxonomy();
        if ($tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL) {
            $or = new ilTextInputGUI($this->lng->txt("tax_order_nr"), "order_nr");
            $or->setMaxLength(5);
            $or->setSize(5);
            $this->form->addItem($or);
        }
        
        if ($a_mode == "edit") {
            $node = new ilTaxonomyNode($this->current_tax_node);
            $ti->setValue($node->getTitle());
            $or->setValue($node->getOrderNr());
        }
        
        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("saveTaxNode", $lng->txt("save"));
            $this->form->addCommandButton("listNodes", $lng->txt("cancel"));
            $this->form->setTitle($lng->txt("tax_new_tax_node"));
        } else {
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
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $this->initTaxNodeForm("create");
        if ($this->form->checkInput()) {
            $tax = $this->getCurrentTaxonomy();
            
            // create node
            $node = new ilTaxonomyNode();
            $node->setTitle($this->form->getInput("title"));
            
            $tax = $this->getCurrentTaxonomy();
            $order_nr = "";
            if ($tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL) {
                $order_nr = $this->form->getInput("order_nr");
            }
            if ($order_nr === "") {
                $order_nr = ilTaxonomyNode::getNextOrderNr($tax->getId(), $this->current_tax_node);
            }
            //echo $order_nr; exit;
            $node->setOrderNr($order_nr);
            $node->setTaxonomyId($tax->getId());
            $node->create();
            
            // put in tree
            ilTaxonomyNode::putInTree($tax->getId(), $node, $this->current_tax_node);
            
            ilTaxonomyNode::fixOrderNumbers($tax->getId(), $this->current_tax_node);
            
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listNodes");
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }
    
    
    /**
     * Update tax node
     */
    public function updateTaxNode()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        
        $this->initTaxNodeForm("edit");
        if ($this->form->checkInput()) {
            // create node
            $node = new ilTaxonomyNode($this->current_tax_node);
            $node->setTitle($this->form->getInput("title"));

            $tax = $this->getCurrentTaxonomy();
            if ($tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL) {
                $node->setOrderNr($this->form->getInput("order_nr"));
            }

            $node->update();

            ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "");
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }
    
    /**
     * Confirm deletion screen for items
     */
    public function deleteItems()
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilHelp = $this->help;
        $body = $this->request->getParsedBody();

        if (!isset($body["id"])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listNodes");
        }

        $this->setTabs("list_items");
        $ilHelp->setSubScreenId("del_items");

        //		$ilTabs->clearTargets();
        
        $confirmation_gui = new ilConfirmationGUI();

        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText($this->lng->txt("info_delete_sure"));

        // Add items to delete
        foreach ($body["id"] as $id) {
            $confirmation_gui->addItem(
                "id[]",
                $id,
                ilTaxonomyNode::_lookupTitle($id)
            );
        }

        $confirmation_gui->setCancel($lng->txt("cancel"), "listNodes");
        $confirmation_gui->setConfirm($lng->txt("confirm"), "confirmedDelete");

        $tpl->setContent($confirmation_gui->getHTML());
    }

    /**
     * Delete taxonomy nodes
     */
    public function confirmedDelete()
    {
        $ilCtrl = $this->ctrl;
        $body = $this->request->getParsedBody();
        
        // delete all selected objects
        foreach ($body["id"] as $id) {
            $node = new ilTaxonomyNode($id);
            $tax = new ilObjTaxonomy($node->getTaxonomyId());
            $tax_tree = $tax->getTree();
            $node_data = $tax_tree->getNodeData($id);
            if (is_object($node)) {
                $node->delete();
            }
            if ($tax_tree->isInTree($id)) {
                $tax_tree->deleteTree($node_data);
            }
            ilTaxonomyNode::fixOrderNumbers($node->getTaxonomyId(), $node_data["parent"]);
        }

        // feedback
        ilUtil::sendInfo($this->lng->txt("info_deleted"), true);
        
        $ilCtrl->redirect($this, "listNodes");
    }

    /**
     * Save settings and sorting
     *
     * @param
     * @return
     */
    public function saveSorting()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $body = $this->request->getParsedBody();
        
        // save sorting
        if (is_array($body["order"])) {
            foreach ($body["order"] as $k => $v) {
                ilTaxonomyNode::writeOrderNr(ilUtil::stripSlashes($k), $v);
            }
            ilTaxonomyNode::fixOrderNumbers($this->getCurrentTaxonomyId(), $this->current_tax_node);
        }
        
        // save titles
        if (is_array($body["title"])) {
            foreach ($body["title"] as $k => $v) {
                ilTaxonomyNode::writeTitle(
                    (int) $k,
                    ilUtil::stripSlashes($v)
                );
            }
        }

        
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"));
        $ilCtrl->redirect($this, "listNodes");
    }
    
    /**
     * Move items
     */
    public function moveItems()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilToolbar = $this->toolbar;
        $ilHelp = $this->help;
        $body = $this->request->getParsedBody();

        if (!isset($body["id"])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listNodes");
        }

        $this->setTabs("list_items");
        $ilHelp->setSubScreenId("move_items");

        $ilToolbar->addButton(
            $lng->txt("cancel"),
            $ilCtrl->getLinkTarget($this, "listNodes")
        );
        
        ilUtil::sendInfo($lng->txt("tax_please_select_target"));
        
        if (is_array($body["id"])) {
            $ilCtrl->setParameter($this, "move_ids", implode(",", $body["id"]));
            
            $tpl = $this->tpl;

            $tax_exp = new ilTaxonomyExplorerGUI(
                $this,
                "moveItems",
                $this->getCurrentTaxonomy()->getId(),
                "ilobjtaxonomygui",
                "pasteItems"
            );
            if (!$tax_exp->handleCommand()) {
                //$tpl->setLeftNavContent($tax_exp->getHTML());
                $tpl->setContent($tax_exp->getHTML() . "&nbsp;");
            }
        }
    }
    
    /**
     * Paste items (move operation)
     */
    public function pasteItems()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        if ($this->requested_move_ids != "") {
            $move_ids = explode(",", $this->requested_move_ids);
            $tax = $this->getCurrentTaxonomy();
            $tree = $tax->getTree();
            
            $target_node = new ilTaxonomyNode((int) $this->current_tax_node);
            foreach ($move_ids as $m_id) {
                // cross check taxonomy
                $node = new ilTaxonomyNode((int) $m_id);
                if ($node->getTaxonomyId() == $tax->getId() &&
                    ($target_node->getTaxonomyId() == $tax->getId() ||
                    $target_node->getId() == $tree->readRootId())) {
                    // check if target is not within the selected nodes
                    if ($tree->isGrandChild((int) $m_id, $target_node->getId())) {
                        ilUtil::sendFailure($lng->txt("tax_target_within_nodes"), true);
                        $this->ctrl->redirect($this, "listNodes");
                    }
                    
                    // if target is not current place, move
                    $parent_id = $tree->getParentId((int) $m_id);
                    if ($parent_id != $target_node->getId()) {
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
    public function confirmDeleteTaxonomy()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $tax = $this->getCurrentTaxonomy();
        
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
    public function deleteTaxonomy()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
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
    public function listTaxonomies()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $tax_ids = ilObjTaxonomy::getUsageOfObject($this->getAssignedObject());
        if (count($tax_ids) == 0 || $this->getMultiple()) {
            $ilToolbar->addButton(
                $lng->txt("tax_add_taxonomy"),
                $ilCtrl->getLinkTarget($this, "createAssignedTaxonomy")
            );
        } else {
            ilUtil::sendInfo($lng->txt("tax_max_one_tax"));
        }
        
        $tab = new ilTaxonomyListTableGUI(
            $this,
            "listTaxonomies",
            $this->getAssignedObject(),
            $this->getListInfo()
        );
        
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Set tabs
     *
     * @param $a_id string tab id to be activated
     */
    public function setTabs($a_id = "")
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilHelp = $this->help;
        
        $ilTabs->clearTargets();

        $ilHelp->setScreenIdComponent("tax");

        $tpl->setTitle(ilObject::_lookupTitle($this->getCurrentTaxonomyId()));
        $tpl->setDescription(ilObject::_lookupDescription($this->getCurrentTaxonomyId()));
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_tax.svg"));
        
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listTaxonomies")
        );
        
        $ilTabs->addTab(
            "list_items",
            $lng->txt("tax_nodes"),
            $ilCtrl->getLinkTarget($this, "listNodes")
        );
        if ($this->assigned_item_sorting) {
            $ilTabs->addTab(
                "ass_items",
                $lng->txt("tax_assigned_items"),
                $ilCtrl->getLinkTarget($this, "listAssignedItems")
            );
        }
        $ilTabs->addTab(
            "settings",
            $lng->txt("settings"),
            $ilCtrl->getLinkTarget($this, "editSettings")
        );
        
        $ilTabs->activateTab($a_id);
    }
    
    /**
     * Edit settings
     *
     * @param
     * @return
     */
    public function editSettings()
    {
        $tpl = $this->tpl;
        
        $this->setTabs("settings");
        
        $form = $this->initSettingsForm();
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * Init  form.
     */
    public function initSettingsForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $tax = $this->getCurrentTaxonomy();
        
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
        if ($this->assigned_item_sorting) {
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
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $tax = $this->getCurrentTaxonomy();
            $tax->setTitle($form->getInput("title"));
            $tax->setDescription($form->getInput("description"));
            $tax->setSortingMode($form->getInput("sorting"));
            $tax->setItemSorting($form->getInput("item_sorting"));
            $tax->update();

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editSettings");
        } else {
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
    public function listAssignedItems()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $tax = $this->getCurrentTaxonomy();
        
        $this->setTabs("ass_items");
                
        // show tree
        $this->showTree(true);
        
        // list assigned items
        $table = new ilTaxAssignedItemsTableGUI(
            $this,
            "listAssignedItems",
            $this->current_tax_node,
            $this->getCurrentTaxonomy(),
            $this->assigned_item_comp_id,
            $this->assigned_item_obj_id,
            $this->assigned_item_type,
            $this->assigned_item_info_obj
        );

        $tpl->setContent($table->getHTML());
    }

    /**
     * Save assigned items sorting
     *
     * @param
     * @return
     */
    public function saveAssignedItemsSorting()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $body = $this->request->getParsedBody();

        if (is_array($body["order"])) {
            $order = $body["order"];
            $tax_node = $this->current_tax_node;
            foreach ($order as $a_item_id => $ord_nr) {
                $tax_ass = new ilTaxNodeAssignment(
                    $this->assigned_item_comp_id,
                    $this->assigned_item_obj_id,
                    $this->assigned_item_type,
                    $this->getCurrentTaxonomyId()
                );
                $tax_ass->setOrderNr($tax_node, $a_item_id, $ord_nr);
            }
            $tax_ass = new ilTaxNodeAssignment(
                $this->assigned_item_comp_id,
                $this->assigned_item_obj_id,
                $this->assigned_item_type,
                $this->getCurrentTaxonomyId()
            );
            $tax_ass->fixOrderNr($tax_node);
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "listAssignedItems");
    }
}
