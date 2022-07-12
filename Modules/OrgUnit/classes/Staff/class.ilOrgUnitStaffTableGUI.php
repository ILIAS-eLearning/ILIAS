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
 * Class ilOrgUnitStaffTableGUI
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 */
class ilOrgUnitStaffTableGUI extends ilTable2GUI
{
    protected \ilTabsGUI $tabs;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\Refinery\Factory $refinery;
    private bool $recursive = false;
    /** @var string "employee" | "superior" */
    private string $staff = "employee";

    public function __construct(
        ilObjectGUI $parent_obj,
        string $parent_cmd,
        string $staff = "employee",
        bool $recursive = false,
        string $template_context = ""
    ) {
        global $DIC;
        $this->ctrl =  $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->setPrefix("il_orgu_" . $staff);
        $this->setFormName('il_orgu_' . $staff);
        $this->setId("il_orgu_" . $staff);

        parent::__construct($parent_obj, $parent_cmd, $template_context);

        $this->setFormAction($this->ctrl->getFormAction($parent_obj));
        $this->setStaff($staff);
        $this->recursive = $recursive;
        $this->setTableHeaders();
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(true);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setDefaultOrderField("role");
        $this->setEnableTitle(true);
        $this->setTitle($this->lng->txt("Staff"));
        $this->setRowTemplate("tpl.staff_row.html", "Modules/OrgUnit");
    }

    private function setTableHeaders() : void
    {
        $this->addColumn($this->lng->txt("firstname"), "first_name");
        $this->addColumn($this->lng->txt("lastname"), "last_name");
        if ($this->recursive) {
            $this->addColumn($this->lng->txt('obj_orgu'), 'org_units');
        }
        $this->addColumn($this->lng->txt("action"));
    }

    public function parseData() : void
    {
        if ($this->staff === "employee") {
            $data = $this->parseRows(ilObjOrgUnitTree::_getInstance()->getEmployees($this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->to()->int()), $this->recursive));
        } elseif ($this->staff === "superior") {
            $data = $this->parseRows(ilObjOrgUnitTree::_getInstance()->getSuperiors($this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->to()->int()), $this->recursive));
        } else {
            throw new \RuntimeException("The ilOrgUnitStaffTableGUI's staff variable has to be either 'employee' or 'superior'");
        }

        $this->setData($data);
    }

    private function parseRows(array $user_ids) : array
    {
        $data = array();
        foreach ($user_ids as $user_id) {
            $data[] = $this->getRowForUser($user_id);
        }

        return $data;
    }

    /**
     * @param string $staff Set this variable either to "employee" or "superior". It's employee by default.
     */
    public function setStaff(string $staff) : void
    {
        $this->staff = $staff;
    }

    /**
     * @return string
     */
    public function getStaff() : string
    {
        return $this->staff;
    }

    protected function getRowForUser(int $user_id) : array
    {
        $user = new ilObjUser($user_id);
        $set = [];
        $set["first_name"] = $user->getFirstname();
        $set["last_name"] = $user->getLastname();
        $set["user_object"] = $user;
        $set["user_id"] = $user_id;
        if ($this->recursive) {
            $set["org_units"] = ilObjOrgUnitTree::_getInstance()->getOrgUnitOfUser($user_id);
        }
        return $set;
    }

    public function fillRow(array $a_set) : void
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $this->tpl->setVariable("FIRST_NAME", $a_set["first_name"]);
        $this->tpl->setVariable("LAST_NAME", $a_set["last_name"]);
        if ($this->recursive) {
            $orgUnitsTitles = array_values(ilObjOrgUnitTree::_getInstance()->getTitles($a_set['org_units']));
            $this->tpl->setVariable("ORG_UNITS", implode(', ', $orgUnitsTitles));
        }
        $this->ctrl->setParameterByClass("illearningprogressgui", "obj_id", $a_set["user_id"]);
        $this->ctrl->setParameterByClass("ilobjorgunitgui", "obj_id", $a_set["user_id"]);
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setListTitle($lng->txt("Actions"));
        $selection->setId("selection_list_user_lp_" . $a_set["user_id"]);

        if ($ilAccess->checkAccess("view_learning_progress", "",
                $_GET["ref_id"]) and ilObjUserTracking::_enabledLearningProgress() and
            ilObjUserTracking::_enabledUserRelatedData()
        ) {
            $selection->addItem(
                $lng->txt("show_learning_progress"),
                "show_learning_progress",
                $this->ctrl->getLinkTargetByClass(array("ilAdministrationGUI",
                                                        "ilObjOrgUnitGUI",
                                                        "ilLearningProgressGUI"
                ), "")
            );
        }
        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]) && !$this->recursive) {
            if ($this->staff === "employee") {
                $selection->addItem($this->lng->txt("remove"), "delete_from_employees",
                    $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "confirmRemoveFromEmployees"));
                $selection->addItem($this->lng->txt("change_to_superior"), "change_to_superior",
                    $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "fromEmployeeToSuperior"));

            }
            if ($this->staff === "superior") {
                $selection->addItem($this->lng->txt("remove"), "delete_from_superiors",
                    $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "confirmRemoveFromSuperiors"));
                $selection->addItem($this->lng->txt("change_to_employee"), "change_to_employee",
                    $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "fromSuperiorToEmployee"));

            }
        }
        $this->tpl->setVariable("ACTIONS", $selection->getHTML());
    }

    /**
     * @param bool $recursive show direct members of this org unit or the sub-units as well?
     */
    public function setRecursive(bool $recursive) : void
    {
        $this->recursive = $recursive;
    }
}
