<?php

declare(strict_types=1);
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
 * TableGUI class for role administration
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesAccessControl
 */
class ilAssignedUsersTableGUI extends ilTable2GUI
{
    protected int $role_id;
    protected bool $roleAssignmentEditable = true;
    protected bool $isAdministrationContext = false;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_role_id,
        bool $a_editable = true,
        bool $isAdministrationContext = false
    ) {
        $this->setId("rbac_ua_" . $a_role_id);
        $this->role_id = $a_role_id;
        $this->roleAssignmentEditable = $a_editable;
        $this->isAdministrationContext = $isAdministrationContext;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        //		$this->setTitle($this->lng->txt("users"));

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("login"), "login", "29%");
        $this->addColumn($this->lng->txt("firstname"), "firstname", "29%");
        $this->addColumn($this->lng->txt("lastname"), "lastname", "29%");
        $this->addColumn($this->lng->txt('actions'), '', '13%');

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.user_assignment_row.html", "Services/AccessControl");

        $this->setEnableTitle(true);
        $this->setDefaultOrderField("login");
        $this->setDefaultOrderDirection("asc");

        $this->setShowRowsSelector(true);

        if ($this->roleAssignmentEditable) {
            $this->addMultiCommand("deassignUser", $this->lng->txt("remove"));
        }

        $this->setSelectAllCheckbox("user_id[]");
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
        return $this->roleAssignmentEditable;
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
        $this->setMaxCount((int) $usr_data["cnt"]);
        $this->setData((array) $usr_data["set"]);
    }

    /**
     * Fill table row
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("VAL_FIRSTNAME", $a_set["firstname"]);
        $this->tpl->setVariable("VAL_LASTNAME", $a_set["lastname"]);

        if (
            $a_set['usr_id'] != SYSTEM_USER_ID and
            ($a_set['usr_id'] != ANONYMOUS_USER_ID or $this->getRoleId() != ANONYMOUS_ROLE_ID) and
            $this->isRoleAssignmentEditable()) {
            $this->tpl->setVariable("ID", $a_set["usr_id"]);
        }

        $actions = new ilAdvancedSelectionListGUI();
        $actions->setSelectionHeaderClass("small");
        $actions->setItemLinkClass("small");

        $actions->setListTitle($this->lng->txt('actions'));
        $actions->setId($a_set['usr_id']);

        $link_contact = ilMailFormCall::getLinkTarget(
            $this->getParentObject(),
            $this->getParentCmd(),
            array('fr' => rawurlencode(base64_encode($this->ctrl->getLinkTarget(
                $this->getParentObject(),
                'userassignment',
                '',
                false,
                false
            )))
            ),
            array('type' => 'new', 'rcp_to' => $a_set['login'])
        );
        $actions->addItem(
            $this->lng->txt('message'),
            '',
            $link_contact
        );

        if ($this->isAdministrationContext) {
            $this->ctrl->setParameterByClass("ilobjusergui", "ref_id", 7);
            $this->ctrl->setParameterByClass("ilobjusergui", "obj_id", $a_set["usr_id"]);

            $link_change = $this->ctrl->getLinkTargetByClass(array("iladministrationgui", "ilobjusergui"), "view");

            $this->tpl->setVariable('VAL_LOGIN', $a_set['login']);
            $this->tpl->setVariable('HREF_LOGIN', $link_change);
            $actions->addItem(
                $this->lng->txt("edit"),
                '',
                $link_change
            );
        } else {
            $this->tpl->setVariable('VAL_PLAIN_LOGIN', $a_set['login']);
        }

        if (
            ($this->getRoleId() != SYSTEM_ROLE_ID or $a_set['usr_id'] != SYSTEM_USER_ID) and
            ($this->getRoleId() != ANONYMOUS_ROLE_ID or $a_set['usr_id'] != ANONYMOUS_USER_ID) and
            $this->isRoleAssignmentEditable()) {
            $this->ctrl->setParameter($this->getParentObject(), "user_id", $a_set["usr_id"]);
            $link_leave = $this->ctrl->getLinkTarget($this->getParentObject(), "deassignUser");

            $actions->addItem(
                $this->lng->txt('remove'),
                '',
                $link_leave
            );
        }

        $this->tpl->setVariable('VAL_ACTIONS', $actions->getHTML());
    }
}
