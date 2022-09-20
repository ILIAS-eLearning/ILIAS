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
 * Recommended content configuration for roles
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRecommendedContentRoleConfigGUI
{
    /** @var int[] */
    protected array $requested_item_ref_ids;
    protected ilRbacSystem $rbacsystem;
    protected ilRbacReview $rbacreview;
    protected ilLanguage $lng;
    protected int $role_id;
    protected int $node_ref_id;
    protected ilCtrl $ctrl;
    protected ilToolbarGUI $toolbar;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilRecommendedContentManager $manager;
    protected int $requested_item_ref_id;

    public function __construct(int $role_id, int $node_ref_id)
    {
        /** @var \ILIAS\DI\Container $DIC */
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
        $request = $DIC->repository()->internal()->gui()->standardRequest();
        $this->requested_item_ref_id = $request->getItemRefId();
        $this->requested_item_ref_ids = $request->getItemRefIds();
    }

    public function executeCommand(): void
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

    public function listItems(): void
    {
        $rbacreview = $this->rbacreview;
        $rbacsystem = $this->rbacsystem;
        $toolbar = $this->toolbar;
        $lng = $this->lng;
        $main_tpl = $this->main_tpl;
        $ctrl = $this->ctrl;

        if ($this->node_ref_id !== ROLE_FOLDER_ID && !$rbacreview->isAssignable($this->role_id, $this->node_ref_id)) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt('rep_no_assign_rec_content_to_role'));
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

    public function confirmRemoveItems(): void
    {
        $this->checkPushPermission();

        $main_tpl = $this->main_tpl;

        if (count($this->requested_item_ref_ids) === 0) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
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
                "item_ref_ids[]",
                (string) $item_ref_id,
                ilObject::_lookupTitle(ilObject::_lookupObjectId($item_ref_id))
            );
        }

        $main_tpl->setContent($confirmation_gui->getHTML());
    }

    public function removeItems(): void
    {
        $this->checkPushPermission();
        if (count($this->requested_item_ref_ids) > 0) {
            foreach ($this->requested_item_ref_ids as $item_ref_id) {
                $this->manager->removeRoleRecommendation($this->role_id, $item_ref_id);
            }
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('rep_rec_content_removed'));
        }
        $this->listItems();
    }

    protected function checkPushPermission(): void
    {
        $ctrl = $this->ctrl;
        $rbacsystem = $this->rbacsystem;

        if (!$rbacsystem->checkAccess('write', $this->node_ref_id) ||
            !$rbacsystem->checkAccess('push_desktop_items', USER_FOLDER_ID)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $ctrl->redirect($this, "listItems");
        }
    }

    protected function selectItem(): void
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

    protected function assignItem(): void
    {
        $ctrl = $this->ctrl;
        $this->checkPushPermission();
        if ($this->requested_item_ref_id > 0) {
            $this->manager->addRoleRecommendation($this->role_id, $this->requested_item_ref_id);
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('rep_added_rec_content'), true);
        }
        $ctrl->redirect($this, 'listItems');
    }
}
