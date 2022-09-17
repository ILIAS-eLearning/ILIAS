<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Taxonomy GUI class
 * @author       Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjTaxonomyGUI: ilObjTaxonomyGUI
 */
class ilObjTaxonomyGUI extends ilObject2GUI
{
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected bool $multiple = false;
    protected bool $assigned_item_sorting = false;
    protected int $assigned_object_id;
    protected ilTaxAssignedItemInfo $assigned_item_info_obj;
    protected string $assigned_item_comp_id;
    protected int $assigned_item_obj_id;
    protected string $assigned_item_type;
    protected string $list_info = '';
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
        $this->help = $DIC[ilHelp::class];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_id, ilObject2GUI::OBJECT_ID);

        $ilCtrl->saveParameter($this, "tax_node");
        $ilCtrl->saveParameter($this, "tax_id");

        $lng->loadLanguageModule("tax");

        // @todo introduce request wrapper
        $this->request = $DIC->http()->request();
        $params = $DIC->http()->request()->getQueryParams();
        $this->current_tax_node = (int) ($params["tax_node"] ?? null);
        $this->requested_tax_id = (int) ($params["tax_id"] ?? null);
        $this->requested_move_ids = (string) ($params["move_ids"] ?? "");
    }

    public function getType(): string
    {
        return "tax";
    }

    /**
     * @param int $a_val object id
     */
    public function setAssignedObject(int $a_val): void
    {
        $this->assigned_object_id = $a_val;
    }

    public function getAssignedObject(): int
    {
        return $this->assigned_object_id;
    }

    /**
     * Set multiple
     * @param bool $a_val multiple
     */
    public function setMultiple(bool $a_val): void
    {
        $this->multiple = $a_val;
    }

    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    public function setListInfo(string $a_val): void
    {
        $this->list_info = trim($a_val);
    }

    public function getListInfo(): string
    {
        return $this->list_info;
    }

    /**
     * Activate sorting mode of assigned objects
     */
    public function activateAssignedItemSorting(
        ilTaxAssignedItemInfo $a_item_info_obj,
        string $a_component_id,
        int $a_obj_id,
        string $a_item_type
    ): void {
        $this->assigned_item_sorting = true;
        $this->assigned_item_info_obj = $a_item_info_obj;
        $this->assigned_item_comp_id = $a_component_id;
        $this->assigned_item_obj_id = $a_obj_id;
        $this->assigned_item_type = $a_item_type;
    }

    /**
     * Execute command
     */
    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $cmd = $ilCtrl->getCmd("listTaxonomies");
        $this->$cmd();
    }

    /**
     * Init creation forms
     */
    protected function initCreationForms(string $a_new_type): array
    {
        return array(
            self::CFORM_NEW => $this->initCreateForm("tax")
        );
    }


    ////
    //// Features that work on the base of an assigned object (AO)
    ////

    public function editAOTaxonomySettings(): void
    {
        $this->listTaxonomies();
    }

    public function getCurrentTaxonomyId(): ?int
    {
        $tax_ids = ilObjTaxonomy::getUsageOfObject($this->getAssignedObject());
        $tax_id = $this->requested_tax_id;
        if (in_array($tax_id, $tax_ids)) {
            return $tax_id;
        }
        return null;
    }

    public function getCurrentTaxonomy(): ?ilObjTaxonomy
    {
        $tax_id = $this->getCurrentTaxonomyId();
        if ($tax_id > 0) {
            return new ilObjTaxonomy($tax_id);
        }
        return null;
    }

    /**
     * List items
     */
    public function listNodes(): void
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

        $tax_node = $this->current_tax_node;
        if ($tax_node === 0) {
            $tax = $this->getCurrentTaxonomy();
            if ($tax) {
                $tree = $tax->getTree();
                $tax_node = $tree->readRootId();
            }
        }

        // show subitems
        $table = new ilTaxonomyTableGUI(
            $this,
            "listNodes",
            $tax->getTree(),
            $tax_node,
            $this->getCurrentTaxonomy()
        );
        $table->setOpenFormTag(false);

        $tpl->setContent($table->getHTML());
    }

    /**
     * Create assigned taxonomy
     */
    public function createAssignedTaxonomy(): void
    {
        $this->create();
    }

    protected function checkPermissionBool(
        string $perm,
        string $cmd = "",
        string $type = "",
        ?int $node_id = null
    ): bool {
        if ($this->getAssignedObject() > 0) {
            return true;
        } else {
            return parent::checkPermissionBool($perm, $cmd, $type, $node_id);
        }
    }

    /**
     * Cancel creation
     */
    public function cancel(): void
    {
        $ilCtrl = $this->ctrl;
        if ($this->getAssignedObject() > 0) {
            $ilCtrl->redirect($this, "listTaxonomies");
        }
        parent::cancel();
    }

    public function save(): void
    {
        if ($this->getAssignedObject() > 0) {
            $this->requested_new_type = "tax";
        }
        parent::saveObject();
    }

    /**
     * After saving,
     */
    protected function afterSave(ilObject $a_new_object): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->getAssignedObject() > 0) {
            ilObjTaxonomy::saveUsage(
                $a_new_object->getId(),
                $this->getAssignedObject()
            );
            $ilCtrl->setParameter($this, "tax_id", $a_new_object->getId());
            $this->tpl->setOnScreenMessage('success', $lng->txt("tax_added"), true);
            $ilCtrl->redirect($this, "editSettings");
        }
    }

    public function showTree(bool $a_ass_items = false): void
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
    }

    /**
     * Create tax node
     */
    public function createTaxNode(): void
    {
        $tpl = $this->tpl;
        $ilHelp = $this->help;

        $this->setTabs("list_items");
        $ilHelp->setSubScreenId("create_node");

        $form = $this->initTaxNodeForm("create");
        $tpl->setContent($form->getHTML());
    }

    // Init tax node form
    public function initTaxNodeForm(string $a_mode = "edit"): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $form->addItem($ti);

        // order nr
        $tax = $this->getCurrentTaxonomy();
        $or = null;
        if ($tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL) {
            $or = new ilTextInputGUI($this->lng->txt("tax_order_nr"), "order_nr");
            $or->setMaxLength(5);
            $or->setSize(5);
            $form->addItem($or);
        }

        if ($a_mode == "edit") {
            $node = new ilTaxonomyNode($this->current_tax_node);
            $ti->setValue($node->getTitle());
            if (is_object($or)) {
                $or->setValue($node->getOrderNr());
            }
        }

        // save and cancel commands
        if ($a_mode == "create") {
            $form->addCommandButton("saveTaxNode", $lng->txt("save"));
            $form->addCommandButton("listNodes", $lng->txt("cancel"));
            $form->setTitle($lng->txt("tax_new_tax_node"));
        } else {
            $form->addCommandButton("updateTaxNode", $lng->txt("save"));
            $form->addCommandButton("listNodes", $lng->txt("cancel"));
            $form->setTitle($lng->txt("tax_edit_tax_node"));
        }

        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * Save tax node form
     */
    public function saveTaxNode(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = $this->initTaxNodeForm("create");
        if ($form->checkInput()) {
            // create node
            $node = new ilTaxonomyNode();
            $node->setTitle($form->getInput("title"));

            $tax = $this->getCurrentTaxonomy();
            $order_nr = "";
            if ($tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL) {
                $order_nr = $form->getInput("order_nr");
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

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listNodes");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }

    /**
     * Update tax node
     */
    public function updateTaxNode(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $form = $this->initTaxNodeForm("edit");
        if ($form->checkInput()) {
            // create node
            $node = new ilTaxonomyNode($this->current_tax_node);
            $node->setTitle($form->getInput("title"));

            $tax = $this->getCurrentTaxonomy();
            if ($tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL) {
                $node->setOrderNr($form->getInput("order_nr"));
            }

            $node->update();

            $this->tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }

    /**
     * Confirm deletion screen for items
     */
    public function deleteItems(): void
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilHelp = $this->help;
        $body = $this->request->getParsedBody();

        if (!isset($body["id"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
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
    public function confirmedDelete(): void
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
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("info_deleted"), true);

        $ilCtrl->redirect($this, "listNodes");
    }

    /**
     * Save settings and sorting
     */
    public function saveSorting(): void
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
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"));
        $ilCtrl->redirect($this, "listNodes");
    }

    /**
     * Move items
     */
    public function moveItems(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilToolbar = $this->toolbar;
        $ilHelp = $this->help;
        $body = $this->request->getParsedBody();

        if (!isset($body["id"])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listNodes");
        }

        $this->setTabs("list_items");
        $ilHelp->setSubScreenId("move_items");

        $ilToolbar->addButton(
            $lng->txt("cancel"),
            $ilCtrl->getLinkTarget($this, "listNodes")
        );

        $this->tpl->setOnScreenMessage('info', $lng->txt("tax_please_select_target"));

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
    public function pasteItems(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        if ($this->requested_move_ids != "") {
            $move_ids = explode(",", $this->requested_move_ids);
            $tax = $this->getCurrentTaxonomy();
            $tree = $tax->getTree();

            $target_node = new ilTaxonomyNode($this->current_tax_node);
            foreach ($move_ids as $m_id) {
                // cross check taxonomy
                $node = new ilTaxonomyNode((int) $m_id);
                if ($node->getTaxonomyId() == $tax->getId() &&
                    ($target_node->getTaxonomyId() == $tax->getId() ||
                        $target_node->getId() == $tree->readRootId())) {
                    // check if target is not within the selected nodes
                    if ($tree->isGrandChild((int) $m_id, $target_node->getId())) {
                        $this->tpl->setOnScreenMessage('failure', $lng->txt("tax_target_within_nodes"), true);
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

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "listNodes");
    }

    /**
     * Confirm taxonomy deletion
     */
    public function confirmDeleteTaxonomy(): void
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

        $cgui->addItem("id[]", 0, $tax->getTitle());

        $tpl->setContent($cgui->getHTML());
    }

    /**
     * Delete taxonomy
     */
    public function deleteTaxonomy(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $tax = $this->getCurrentTaxonomy();
        $tax->delete();

        $this->tpl->setOnScreenMessage('success', $lng->txt("tax_tax_deleted"), true);
        $ilCtrl->redirect($this, "listTaxonomies");
    }

    /**
     * List taxonomies
     */
    public function listTaxonomies(): void
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
            $this->tpl->setOnScreenMessage('info', $lng->txt("tax_max_one_tax"));
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
     * @inheritDoc
     */
    protected function setTabs($a_id = ""): void
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
     */
    public function editSettings(): void
    {
        $tpl = $this->tpl;

        $this->setTabs("settings");

        $form = $this->initSettingsForm();
        $tpl->setContent($form->getHTML());
    }

    public function initSettingsForm(): ilPropertyFormGUI
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
    public function updateSettings(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $tax = $this->getCurrentTaxonomy();
            $tax->setTitle($form->getInput("title"));
            $tax->setDescription($form->getInput("description"));
            $tax->setSortingMode((int) $form->getInput("sorting"));
            $tax->setItemSorting((bool) $form->getInput("item_sorting"));
            $tax->update();

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editSettings");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }

    /**
     * List assigned items
     */
    public function listAssignedItems(): void
    {
        $tpl = $this->tpl;

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
     */
    public function saveAssignedItemsSorting(): void
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
                    (int) $this->getCurrentTaxonomyId()
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
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "listAssignedItems");
    }
}
