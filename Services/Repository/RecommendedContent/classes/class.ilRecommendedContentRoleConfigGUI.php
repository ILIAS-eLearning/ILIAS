<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Recommended content configuration for roles
 *
 * @author killing@leifos.de
 */
class ilRecommendedContentRoleConfigGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var int
     */
    protected $role_id;

    /**
     * @var int
     */
    protected $node_ref_id;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var \ilTemplate
     */
    protected $main_tpl;

    /**
     * @var ilRecommendedContentManager
     */
    protected $manager;

    /**
     * @var int
     */
    protected $requested_item_ref_id;

    /**
     * Constructor
     */
    public function __construct(int $role_id, int $node_ref_id)
    {
        global $DIC;

        $this->role_id = $role_id;
        $this->node_ref_id = $node_ref_id;
        $this->rbacsystem = $DIC->rbac()->system();
        $this->rbacreview = $DIC->rbac()->review();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->manager = new ilRecommendedContentManager();
        $this->requested_item_ref_id = (int) $_GET['item_ref_id'];
        $this->requested_item_ref_ids = is_array($_POST["item_ref_id"])
            ? array_map(function ($i) {
                return (int) $i;
            }, $_POST["item_ref_id"])
            : [];
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("listItems");

        switch ($next_class) {
            default:
                if (in_array($cmd, ["listItems", "selectItem", "assignItem", "confirmRemoveItems", "removeItems"])) {
                    $this->$cmd();
                }
                break;
        }
    }

    /**
     * List items
     */
    public function listItems()
    {
        $rbacreview = $this->rbacreview;
        $rbacsystem = $this->rbacsystem;
        $toolbar = $this->toolbar;
        $lng = $this->lng;
        $main_tpl = $this->main_tpl;
        $ctrl = $this->ctrl;

        if (!$rbacreview->isAssignable($this->role_id, $this->node_ref_id) &&
            $this->node_ref_id != ROLE_FOLDER_ID) {
            ilUtil::sendInfo($this->lng->txt('rep_no_assign_rec_content_to_role'));
        } else {
            if ($rbacsystem->checkAccess('push_desktop_items', USER_FOLDER_ID)) {
                $toolbar->addButton($lng->txt('add'), $ctrl->getLinkTarget(
                    $this,
                    'selectItem'
                ));
            }
            $tbl = new ilRecommendedContentRoleTableGUI(
                $this,
                'listItems',
                $this->role_id,
                $this->manager
            );
            $main_tpl->setContent($tbl->getHTML());
        }
    }

    /**
     * Remove items confirmation
     */
    public function confirmRemoveItems()
    {
        $this->checkPushPermission();

        $main_tpl = $this->main_tpl;

        if (count($this->requested_item_ref_ids) == 0) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->listItems();
            return;
        }

        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $confirmation_gui->setHeaderText($this->lng->txt('rep_remove_rec_content'));
        $confirmation_gui->setCancel($this->lng->txt("cancel"), "listItems");
        $confirmation_gui->setConfirm($this->lng->txt("remove"), "removeItems");

        foreach ($this->requested_item_ref_ids as $item_ref_id) {
            $confirmation_gui->addItem(
                "item_ref_id[]",
                $item_ref_id,
                ilObject::_lookupTitle(ilObject::_lookupObjectId($item_ref_id))
            );
        }

        $main_tpl->setContent($confirmation_gui->getHTML());
    }

    /**
     * Remove items
     */
    public function removeItems()
    {
        $this->checkPushPermission();

        if (count($this->requested_item_ref_ids) > 0) {
            foreach ($this->requested_item_ref_ids as $item_ref_id) {
                $this->manager->removeRoleRecommendation($this->role_id, $item_ref_id);
            }
            ilUtil::sendSuccess($this->lng->txt('rep_rec_content_removed'));
        }
        $this->listItems();
    }

    /**
     * Check permission to push recommended content
     */
    protected function checkPushPermission()
    {
        $ctrl = $this->ctrl;
        $rbacsystem = $this->rbacsystem;

        if (!$rbacsystem->checkAccess('write', $this->node_ref_id) ||
            !$rbacsystem->checkAccess('push_desktop_items', USER_FOLDER_ID)) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $ctrl->redirect($this, "listItems");
        }
    }

    /**
     * Select recommended content
     */
    protected function selectItem()
    {
        $this->checkPushPermission();

        $main_tpl = $this->main_tpl;
        $exp = new ilRepositorySelectorExplorerGUI(
            $this,
            "selectItem",
            $this,
            "assignItem",
            "item_ref_id"
        );
        $exp->setSkipRootNode(true);
        if (!$exp->handleCommand()) {
            $main_tpl->setContent($exp->getHTML());
        }
    }

    /**
     * Assign item
     */
    protected function assignItem()
    {
        $ctrl = $this->ctrl;
        $this->checkPushPermission();
        if ($this->requested_item_ref_id > 0) {
            $this->manager->addRoleRecommendation($this->role_id, $this->requested_item_ref_id);
            ilUtil::sendSuccess($this->lng->txt('rep_added_rec_content'), true);
        }
        $ctrl->redirect($this, 'listItems');
    }
}
