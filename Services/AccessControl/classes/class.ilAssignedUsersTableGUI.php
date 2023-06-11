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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Link\Standard as StandardLink;

/**
 * TableGUI class for role administration
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesAccessControl
 */
class ilAssignedUsersTableGUI extends ilTable2GUI
{
    public function __construct(
        object $parent_obj,
        string $parent_cmd,
        private UIFactory $ui_factory,
        private Renderer $ui_renderer,
        private int $role_id,
        private bool $role_assignment_editable = true,
        private bool $is_administration_context = false
    ) {
        $this->setId('rbac_ua_' . $role_id);

        parent::__construct($parent_obj, $parent_cmd);

        $this->addColumn('', '', '1', true);
        $this->addColumn($this->lng->txt('login'), 'login', '29%');
        $this->addColumn($this->lng->txt('firstname'), 'firstname', '29%');
        $this->addColumn($this->lng->txt('lastname'), 'lastname', '29%');
        $this->addColumn($this->lng->txt('actions'), '', '13%');

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($parent_obj, $parent_cmd));
        $this->setRowTemplate('tpl.user_assignment_row.html', 'Services/AccessControl');

        $this->setEnableTitle(true);
        $this->setDefaultOrderField('login');
        $this->setDefaultOrderDirection('asc');

        $this->setShowRowsSelector(true);

        if ($this->role_assignment_editable) {
            $this->addMultiCommand('deassignUser', $this->lng->txt('remove'));
        }

        $this->setSelectAllCheckbox('user_id[]');
        $this->lng->loadLanguageModule('user');
        $this->addMultiCommand(
            'addToClipboard',
            $this->lng->txt('clipboard_add_btn')
        );
        $this->getItems();
    }

    /**
     * get current role id
     */
    public function getRoleId(): int
    {
        return $this->role_id;
    }

    /**
     * Check if role assignment is editable
     */
    public function isRoleAssignmentEditable(): bool
    {
        return $this->role_assignment_editable;
    }

    /**
     * Get user items
     */
    public function getItems(): void
    {
        $this->determineOffsetAndOrder();
        $usr_data = ilUserQuery::getUserListData(
            ilUtil::stripSlashes($this->getOrderField()),
            ilUtil::stripSlashes($this->getOrderDirection()),
            $this->getOffset(),
            $this->getLimit(),
            '',
            '',
            null,
            false,
            false,
            0,
            $this->getRoleId()
        );
        $this->setMaxCount((int) $usr_data['cnt']);
        $this->setData((array) $usr_data['set']);
    }

    /**
     * Fill table row
     */
    protected function fillRow(array $user_row): void
    {
        $this->tpl->setVariable('VAL_FIRSTNAME', $user_row['firstname']);
        $this->tpl->setVariable('VAL_LASTNAME', $user_row['lastname']);

        if ($user_row['usr_id'] !== SYSTEM_USER_ID
            && ($user_row['usr_id'] !== ANONYMOUS_USER_ID || $this->getRoleId() !== ANONYMOUS_ROLE_ID)
            && $this->isRoleAssignmentEditable()) {
            $this->tpl->setVariable('ID', $user_row['usr_id']);
        }

        $actions = $this->getActions($user_row['login'], $user_row['usr_id']);

        $login_entry = $user_row['login'];

        if ($this->is_administration_context) {
            $login_entry = $this->ui_renderer->render(
                $this->getChangeLink($user_row['usr_id'], $user_row['login'])
            );
        }

        $this->tpl->setVariable('VAL_LOGIN', $login_entry);

        $actions_dropdown = $this->ui_factory->dropdown()->standard($actions)
            ->withLabel($this->lng->txt('actions'));

        $this->tpl->setVariable(
            'VAL_ACTIONS',
            $this->ui_renderer->render($actions_dropdown)
        );
    }

    /**
     *
     * @return array<ILIAS\UI\Component\Link\Standard>
     */
    private function getActions(string $login, int $usr_id): array
    {
        $actions = [];

        $actions[] = $this->getContactLink($login);

        if ($this->is_administration_context) {
            $actions[] = $this->getChangeLink($usr_id, $this->lng->txt('edit'));
        }

        if (($this->getRoleId() !== SYSTEM_ROLE_ID || $usr_id !== SYSTEM_USER_ID)
            && ($this->getRoleId() !== ANONYMOUS_ROLE_ID || $usr_id !== ANONYMOUS_USER_ID)
            && $this->isRoleAssignmentEditable()) {
            $actions[] = $this->getLeaveLink($usr_id);
        }

        return $actions;
    }

    private function getContactLink(string $login): StandardLink
    {
        $contact_link = ilMailFormCall::getLinkTarget(
            $this->getParentObject(),
            $this->getParentCmd(),
            ['fr' => rawurlencode(base64_encode($this->ctrl->getLinkTarget(
                $this->getParentObject(),
                'userassignment',
                '',
                false,
                false
            )))
            ],
            ['type' => 'new', 'rcp_to' => $login]
        );

        return $this->ui_factory->link()->standard(
            $this->lng->txt('message'),
            $contact_link
        );
    }

    private function getChangeLink(int $usr_id, string $label): StandardLink
    {
        $this->ctrl->setParameterByClass('ilobjusergui', 'ref_id', 7);
        $this->ctrl->setParameterByClass('ilobjusergui', 'obj_id', $usr_id);
        $change_link = $this->ctrl->getLinkTargetByClass(['iladministrationgui', 'ilobjusergui'], 'view');
        return $this->ui_factory->link()->standard(
            $label,
            $change_link
        );
    }

    private function getLeaveLink(int $usr_id): StandardLink
    {
        $this->ctrl->setParameter($this->getParentObject(), 'user_id', $usr_id);
        $link_leave = $this->ctrl->getLinkTarget($this->getParentObject(), 'deassignUser');

        return $this->ui_factory->link()->standard(
            $this->lng->txt('remove'),
            $link_leave
        );
    }
}
