<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilOrgUnitOtherRolesTableGUI
 *
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 */
class ilOrgUnitOtherRolesTableGUI extends ilTable2GUI
{
    public function __construct($parent_obj, $parent_cmd, $role_id, $template_context = "")
    {
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
//
    //	public function getHTML(){
    //		$this->parseData();
    //		return parent::getHTML();
    //	}

    protected function setTableHeaders()
    {
        $this->addColumn($this->lng->txt("firstname"), "first_name");
        $this->addColumn($this->lng->txt("lastname"), "last_name");
        $this->addColumn($this->lng->txt("action"));
    }

    public function readData()
    {
        $this->parseData();
    }

    public function parseData()
    {
        global $DIC;
        $rbacreview = $DIC['rbacreview'];

        $data = $this->parseRows($rbacreview->assignedUsers($this->getRoleId()));

        $this->setData($data);
    }

    protected function parseRows($user_ids)
    {
        $data = array();
        foreach ($user_ids as $user_id) {
            $set = array();
            $this->setRowForUser($set, $user_id);
            $data[] = $set;
        }
        return $data;
    }

    /**
     * @param $role_id integer
     */
    public function setRoleId($role_id)
    {
        $this->role_id = $role_id;
    }

    /**
     * @return integer
     */
    public function getRoleId()
    {
        return $this->role_id;
    }

    protected function setRowForUser(&$set, $user_id)
    {
        $user = new ilObjUser($user_id);
        $set["first_name"] = $user->getFirstname();
        $set["last_name"] = $user->getLastname();
        $set["user_object"] = $user;
        $set["user_id"] = $user_id;
    }

    public function fillRow($set)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $this->tpl->setVariable("FIRST_NAME", $set["first_name"]);
        $this->tpl->setVariable("LAST_NAME", $set["last_name"]);


        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]) && !$this->recursive) {
            $this->ctrl->setParameterByClass("ilobjorgunitgui", "obj_id", $set["user_id"]);
            $this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "role_id", $this->role_id);

            $selection = new ilAdvancedSelectionListGUI();
            $selection->setListTitle($lng->txt("Actions"));
            $selection->setId("selection_list_user_other_roles_" . $set["user_id"]);
            $selection->addItem($this->lng->txt("remove"), "delete_from_role", $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "confirmRemoveFromRole"));
        }
        $this->tpl->setVariable("ACTIONS", $selection->getHTML());
    }
}
