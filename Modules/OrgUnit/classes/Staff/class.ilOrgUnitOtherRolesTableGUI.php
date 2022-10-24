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
 ********************************************************************
 */
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOrgUnitOtherRolesTableGUI
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 */
class ilOrgUnitOtherRolesTableGUI extends ilTable2GUI
{
    protected ilTabsGUI $tabs;
    protected ilAccessHandler $ilAccess;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\Refinery\Factory $refinery;

    public function __construct(
        ilObjectGUI $parent_obj,
        string $parent_cmd,
        string $role_id,
        string $template_context = ""
    ) {
        parent::__construct($parent_obj, $parent_cmd, $template_context);

        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->ilAccess = $DIC->access();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->setPrefix("sr_other_role_" . $role_id);
        $this->setFormName('sr_other_role_' . $role_id);
        $this->setId("sr_other_role_" . $role_id);
        $this->setRoleId($role_id);

        $this->setTableHeaders();
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(true);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setDefaultOrderField("role");
        $this->setEnableTitle(true);
        $this->setTitle(ilObjRole::_lookupTitle($role_id));
        $this->setRowTemplate("tpl.staff_row.html", "Modules/OrgUnit");
    }

    private function setTableHeaders(): void
    {
        $this->addColumn($this->lng->txt("firstname"), "first_name");
        $this->addColumn($this->lng->txt("lastname"), "last_name");
        $this->addColumn($this->lng->txt("action"));
    }

    public function readData(): void
    {
        $this->parseData();
    }

    private function parseData(): void
    {
        global $DIC;
        $rbacreview = $DIC['rbacreview'];

        $data = $this->parseRows($rbacreview->assignedUsers($this->getRoleId()));

        $this->setData($data);
    }

    /**
     * @param int[] $user_ids
     * @return array
     */
    private function parseRows(array $user_ids): array
    {
        $data = array();
        foreach ($user_ids as $user_id) {
            $set = array();
            $data[] = $this->getRowForUser($user_id);
        }

        return $data;
    }

    public function setRoleId(int $role_id): void
    {
        $this->role_id = $role_id;
    }

    public function getRoleId(): int
    {
        return $this->role_id;
    }

    private function getRowForUser(int $user_id): array
    {
        $user = new ilObjUser($user_id);
        $set = [];
        $set["first_name"] = $user->getFirstname();
        $set["last_name"] = $user->getLastname();
        $set["user_object"] = $user;
        $set["user_id"] = $user_id;
        return $set;
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("FIRST_NAME", $a_set["first_name"]);
        $this->tpl->setVariable("LAST_NAME", $a_set["last_name"]);

        if ($this->ilAccess->checkAccess("write", "", $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->to()->int()))) {
            $this->ctrl->setParameterByClass("ilobjorgunitgui", "obj_id", $a_set["user_id"]);
            $this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "role_id", $this->role_id);

            $selection = new ilAdvancedSelectionListGUI();
            $selection->setListTitle($this->lng->txt("Actions"));
            $selection->setId("selection_list_user_other_roles_" . $a_set["user_id"]);
            $selection->addItem(
                $this->lng->txt("remove"),
                "delete_from_role",
                $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "confirmRemoveFromRole")
            );
        }
        $this->tpl->setVariable("ACTIONS", $selection->getHTML());
    }
}
