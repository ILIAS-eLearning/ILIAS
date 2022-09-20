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

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitRecursiveUserAssignmentTableGUI
 * @author dkloepfer
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilOrgUnitRecursiveUserAssignmentTableGUI extends ilTable2GUI
{
    private static array $permission_access_staff_recursive = [];
    private static array $permission_view_lp_recursive = [];
    protected ilOrgUnitPosition $ilOrgUnitPosition;

    public function __construct(BaseCommands $parent_obj, string $parent_cmd, ilOrgUnitPosition $position)
    {
        global $DIC;

        $this->parent_obj = $parent_obj;
        $this->ilOrgUnitPosition = $position;
        $this->ctrl = $DIC->ctrl();
        $this->setPrefix("il_orgu_" . $position->getId());
        $this->setFormName('il_orgu_' . $position->getId());
        $this->setId("il_orgu_" . $position->getId());
        $this->orgu_ref_id = filter_input(INPUT_GET, "ref_id", FILTER_SANITIZE_NUMBER_INT);
        parent::__construct($parent_obj, $parent_cmd);

        $this->setFormAction($this->ctrl->getFormAction($parent_obj));
        $this->setTableHeaders();
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(true);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setTitle($position->getTitle());
        $this->setRowTemplate("tpl.staff_row.html", "Modules/OrgUnit");
        $this->setData($this->loadData());
    }

    protected function setTableHeaders(): void
    {
        $this->addColumn($this->lng->txt("login"), "login");
        $this->addColumn($this->lng->txt("firstname"), "first_name");
        $this->addColumn($this->lng->txt("lastname"), "last_name");
        $this->addColumn($this->lng->txt("obj_orgu"), "orgus");
        $this->addColumn($this->lng->txt("action"));
    }


    public function loadData(): array
    {
        global $DIC;
        $access = $DIC['ilAccess'];
        $orgu_tree = ilObjOrgUnitTree::_getInstance();
        $data = [];
        // maybe any parent gives us recursive permission
        (int) $root = (int) ilObjOrgUnit::getRootOrgRefId();
        $parent = (int) $orgu_tree->getParent($this->orgu_ref_id);

        while ($parent !== $root) {
            if (ilObjOrgUnitAccess::_checkAccessStaffRec($parent)) {
                array_merge(
                    self::$permission_access_staff_recursive,
                    $orgu_tree->getAllChildren($parent)
                );
            }
            $parent = (int) $orgu_tree->getParent($parent);
        }

        foreach ($orgu_tree->getAllChildren($this->orgu_ref_id) as $ref_id) {
            $recursive = in_array($ref_id, self::$permission_access_staff_recursive);
            if (!$recursive) {
                // ok, so no permission from above, lets check local permissions
                if (ilObjOrgUnitAccess::_checkAccessStaffRec($ref_id)) {
                    // update recursive permissions
                    array_merge(
                        self::$permission_access_staff_recursive,
                        $orgu_tree->getAllChildren($ref_id)
                    );
                } elseif (!ilObjOrgUnitAccess::_checkAccessStaff($ref_id)) {
                    // skip orgus in which one may not view the staff
                    continue;
                }
            }
            $permission_view_lp = $this->mayViewLPIn($ref_id, $access, $orgu_tree);
            foreach ($orgu_tree->getAssignements($ref_id, $this->ilOrgUnitPosition) as $usr_id) {
                $usr_id = (int) $usr_id;
                if (!array_key_exists($usr_id, $data)) {
                    $user = new ilObjUser($usr_id);
                    $set["login"] = $user->getLogin();
                    $set["first_name"] = $user->getFirstname();
                    $set["last_name"] = $user->getLastname();
                    $set["user_id"] = $usr_id;
                    $set["orgu_assignments"] = [];
                    $set['view_lp'] = false;
                    $data[$usr_id] = $set;
                }
                $data[$usr_id]['orgu_assignments'][] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
                $data[$usr_id]['view_lp'] = $permission_view_lp || $data[$usr_id]['view_lp'];
            }
        }

        return $data;
    }

    private function mayViewLPIn(int $ref_id, ilAccess $access, ilObjOrgUnitTree $orgu_tree): bool
    {
        if ($access->checkAccess("view_learning_progress", "", $ref_id)) { // admission by local
            return true;
        }
        $current = $ref_id;
        $root = ilObjOrgUnit::getRootOrgRefId();
        $checked_children = [];
        while ($current !== $root) {
            if (!array_key_exists($current, self::$permission_view_lp_recursive)) {
                self::$permission_view_lp_recursive[$current]
                    = $access->checkAccess("view_learning_progress_rec", "", $current);
            }
            if (self::$permission_view_lp_recursive[$current]) {
                // if an orgu may be viewed recursively, same holds for all of its children. lets cache this.
                foreach ($checked_children as $child) {
                    self::$permission_view_lp_recursive[$child] = true;
                }

                return true;
            }
            $checked_children[] = $current;
            $current = (int) $orgu_tree->getParent($current);
        }

        return false;
    }

    public function fillRow(array $a_set): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $this->tpl->setVariable("LOGIN", $a_set["login"]);
        $this->tpl->setVariable("FIRST_NAME", $a_set["first_name"]);
        $this->tpl->setVariable("LAST_NAME", $a_set["last_name"]);
        $orgus = $a_set['orgu_assignments'];
        sort($orgus);
        $this->tpl->setVariable("ORG_UNITS", implode(',', $orgus));
        $this->ctrl->setParameterByClass(ilOrgUnitUserAssignmentGUI::class, 'usr_id', $a_set["user_id"]);
        $this->ctrl->setParameterByClass(
            ilOrgUnitUserAssignmentGUI::class,
            'position_id',
            $this->ilOrgUnitPosition->getId()
        );
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setListTitle($lng->txt("Actions"));
        $selection->setId("selection_list_user_lp_" . $a_set["user_id"]);
        if ($a_set['view_lp']
            && ilObjUserTracking::_enabledLearningProgress()
            && ilObjUserTracking::_enabledUserRelatedData()
        ) {
            $selection->addItem(
                $lng->txt("show_learning_progress"),
                "show_learning_progress",
                $this->ctrl->getLinkTargetByClass(array(
                    ilAdministrationGUI::class,
                    ilObjOrgUnitGUI::class,
                    ilLearningProgressGUI::class,
                ), "")
            );
        }
        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
            $this->addActions($selection);
        }
        $this->tpl->setVariable("ACTIONS", $selection->getHTML());
    }

    protected function addActions(ilAdvancedSelectionListGUI $selection): void
    {
        $selection->addItem($this->lng->txt("remove"), "delete_from_employees", $this->ctrl->getLinkTargetByClass(ilOrgUnitUserAssignmentGUI::class, ilOrgUnitUserAssignmentGUI::CMD_CONFIRM_RECURSIVE));
    }
}
