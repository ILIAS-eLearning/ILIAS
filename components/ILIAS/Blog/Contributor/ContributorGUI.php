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

declare(strict_types=1);

namespace ILIAS\Blog\Contributor;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ilRepositorySearchGUI;
use ilObjUser;
use ilConfirmationGUI;

/**
 * @ilCtrl_Calls ILIAS\Blog\Contributor\ContributorGUI: ilRepositorySearchGUI
 */
class ContributorGUI
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $node_id,
        protected \ilObjBlog $blog
    ) {
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();
        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("contributors");

        switch ($next_class) {
            case strtolower(\ilRepositorySearchGUI::class):
                $rep_search = new \ilRepositorySearchGUI();
                $rep_search->setTitle($this->domain->lng()->txt("blog_add_contributor"));
                $rep_search->setCallback($this, 'addContributor', $this->blog->getAllLocalRoles($this->node_id));
                $ctrl->setReturn($this, 'contributors');
                $ctrl->forwardCommand($rep_search);
                break;

            default:
                if (method_exists($this, $cmd)) {
                    $this->$cmd();
                }
                break;
        }
    }

    public function contributors(): void
    {
        $ilTabs = $this->gui->tabs();
        $ilToolbar = $this->gui->toolbar();
        $ilCtrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $tpl = $this->gui->ui()->mainTemplate();

        $ilTabs->activateTab("contributors");

        $local_roles = $this->blog->getAllLocalRoles($this->node_id);

        // add member
        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $ilToolbar,
            array(
                'auto_complete_name' => $lng->txt('user'),
                'submit_name' => $lng->txt('add'),
                'add_search' => true,
                'add_from_container' => $this->node_id,
                'user_type' => $local_roles
            ),
            true
        );

        $other_roles = $this->blog->getRolesWithContributeOrRedact($this->node_id);
        if ($other_roles) {
            $tpl->setOnScreenMessage('info', sprintf($lng->txt("blog_contribute_other_roles"), implode(", ", $other_roles)));
        }

        $table = $this->gui->contributor()->contributorTableBuilder(
            $this->blog->getAllLocalRoles($this->node_id),
            $this,
            "contributors"
        )->getTable();

        if ($table->handleCommand()) {
            return;
        }

        $tpl->setContent($table->render());
    }

    /**
     * Autocomplete submit
     */
    public function addUserFromAutoComplete(): void
    {
        $lng = $this->domain->lng();
        $req = $this->gui->standardRequest();

        $user_login = $req->getUserLogin();
        $user_type = $req->getUserType();

        if (trim($user_login) === '') {
            $this->gui->ui()->mainTemplate()->setOnScreenMessage('failure', $lng->txt('msg_no_search_string'));
            $this->contributors();
            return;
        }
        $users = explode(',', $user_login);

        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);

            if (!$user_id) {
                $this->gui->ui()->mainTemplate()->setOnScreenMessage('failure', $lng->txt('user_not_known'));
                $this->contributors();
                return;
            }

            $user_ids[] = (int) $user_id;
        }

        $this->addContributor($user_ids, $user_type);
    }

    /**
     * Centralized method to add contributors
     */
    public function addContributor(
        array $a_user_ids = array(),
        ?string $a_user_type = null
    ): void {
        $ilCtrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $rbacreview = $this->domain->rbac()->review();
        $rbacadmin = $this->domain->rbac()->admin();
        $a_user_type = (int) $a_user_type;

        if (empty($a_user_ids)) {
            $a_user_ids = $this->gui->standardRequest()->getIds();
        }

        if (!count($a_user_ids) || !$a_user_type) {
            $this->gui->ui()->mainTemplate()->setOnScreenMessage('failure', $lng->txt("no_checkbox"));
            $this->contributors();
            return;
        }

        // get contributor role
        $local_roles = array_keys($this->blog->getAllLocalRoles($this->node_id));
        if (!in_array($a_user_type, $local_roles)) {
            $this->gui->ui()->mainTemplate()->setOnScreenMessage('failure', $lng->txt("missing_perm"));
            $this->contributors();
            return;
        }

        foreach ($a_user_ids as $user_id) {
            $user_id = (int) $user_id;
            $a_user_type = (int) $a_user_type;
            if (!$rbacreview->isAssigned($user_id, $a_user_type)) {
                $rbacadmin->assignUser($a_user_type, $user_id);
            }
        }

        $this->gui->ui()->mainTemplate()->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "contributors");
    }

    /**
     * Used in ContributorTableBuilder
     */
    public function confirmRemoveContributor(array $ids = []): void
    {
        if (empty($ids)) {
            $ids = $this->gui->standardRequest()->getIds();
        }
        if (count($ids) === 0) {
            $this->gui->ui()->mainTemplate()->setOnScreenMessage('failure', $this->domain->lng()->txt("select_one"), true);
            $this->gui->ctrl()->redirect($this, "contributors");
        }

        $confirm = new ilConfirmationGUI();
        $confirm->setHeaderText($this->domain->lng()->txt('blog_confirm_delete_contributors'));
        $confirm->setFormAction($this->gui->ctrl()->getFormAction($this, 'removeContributor'));
        $confirm->setConfirm($this->domain->lng()->txt('delete'), 'removeContributor');
        $confirm->setCancel($this->domain->lng()->txt('cancel'), 'contributors');

        foreach ($ids as $user_id) {
            $confirm->addItem(
                'id[]',
                (string) $user_id,
                \ilUserUtil::getNamePresentation($user_id, false, false, "", true)
            );
        }

        $this->gui->ui()->mainTemplate()->setContent($confirm->getHTML());
    }

    public function removeContributor(): void
    {
        $ilCtrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $rbacadmin = $this->domain->rbac()->admin();

        $ids = $this->gui->standardRequest()->getIds();

        if (count($ids) === 0) {
            $this->gui->ui()->mainTemplate()->setOnScreenMessage('failure', $lng->txt("select_one"), true);
            $ilCtrl->redirect($this, "contributors");
        }

        // get contributor role
        $local_roles = array_keys($this->blog->getAllLocalRoles($this->node_id));
        if (!$local_roles) {
            $this->gui->ui()->mainTemplate()->setOnScreenMessage('failure', $lng->txt("missing_perm"));
            $this->contributors();
            return;
        }

        foreach ($ids as $user_id) {
            foreach ($local_roles as $role_id) {
                $rbacadmin->deassignUser($role_id, $user_id);
            }
        }

        $this->gui->ui()->mainTemplate()->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        $this->gui->ctrl()->redirect($this, "contributors");
    }

    /**
     * Used in ContributorTableBuilder
     */
    public function addContributorContainerAction(array $ids = []): void
    {
        if (empty($ids)) {
            $ids = $this->gui->standardRequest()->getIds();
        }

        // This would typically add contributors from a container
        // For now, redirecting back to contributors as this seems to be a placeholder action
        $this->gui->ctrl()->redirect($this, "contributors");
    }
}
