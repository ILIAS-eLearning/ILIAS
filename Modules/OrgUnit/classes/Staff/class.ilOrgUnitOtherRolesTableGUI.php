<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOrgUnitOtherRolesTableGUI
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 */
class ilOrgUnitOtherRolesTableGUI extends ilTable2GUI
{
    public function __construct(
        ilObjectGUI $parent_obj,
        string $parent_cmd,
        string $role_id,
        string $template_context = ""
    ) {
        parent::__construct($parent_obj, $parent_cmd, $template_context);

        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        /**
         * @var $ilCtrl ilCtrl
         * @var $ilTabs ilTabsGUI
         */
        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->lng = $lng;

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

    private function setTableHeaders() : void
    {
        $this->addColumn($this->lng->txt("firstname"), "first_name");
        $this->addColumn($this->lng->txt("lastname"), "last_name");
        $this->addColumn($this->lng->txt("action"));
    }

    final public function readData() : void
    {
        $this->parseData();
    }

    private function parseData() : void
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
    private function parseRows(array $user_ids) : array
    {
        $data = array();
        foreach ($user_ids as $user_id) {
            $set = array();
            $data[] = $this->getRowForUser($user_id);
        }

        return $data;
    }

    final public function setRoleId(int $role_id) : void
    {
        $this->role_id = $role_id;
    }

    final public function getRoleId() : int
    {
        return $this->role_id;
    }

    private function getRowForUser(int $user_id) : array
    {
        $user = new ilObjUser($user_id);
        $set = [];
        $set["first_name"] = $user->getFirstname();
        $set["last_name"] = $user->getLastname();
        $set["user_object"] = $user;
        $set["user_id"] = $user_id;
        return $set;
    }

    final public function fillRow(array $a_set) : void
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $this->tpl->setVariable("FIRST_NAME", $a_set["first_name"]);
        $this->tpl->setVariable("LAST_NAME", $a_set["last_name"]);

        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]) && !$this->recursive) {
            $this->ctrl->setParameterByClass("ilobjorgunitgui", "obj_id", $a_set["user_id"]);
            $this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "role_id", $this->role_id);

            $selection = new ilAdvancedSelectionListGUI();
            $selection->setListTitle($lng->txt("Actions"));
            $selection->setId("selection_list_user_other_roles_" . $a_set["user_id"]);
            $selection->addItem($this->lng->txt("remove"), "delete_from_role",
                $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "confirmRemoveFromRole"));
        }
        $this->tpl->setVariable("ACTIONS", $selection->getHTML());
    }
}
