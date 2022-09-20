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
 * Class ilOrgUnitUserAssignmentTableGUI
 */
class ilOrgUnitUserAssignmentTableGUI extends ilTable2GUI
{
    protected ilOrgUnitPosition $ilOrgUnitPosition;

    public function __construct(BaseCommands $parent_obj, string $parent_cmd, ilOrgUnitPosition $position)
    {
        $this->parent_obj = $parent_obj;
        $this->ilOrgUnitPosition = $position;
        $this->ctrl = $GLOBALS["DIC"]->ctrl();
        $this->setPrefix("il_orgu_" . $position->getId());
        $this->setFormName('il_orgu_' . $position->getId());
        $this->setId("il_orgu_" . $position->getId());

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
        $this->parseData();
    }

    private function setTableHeaders(): void
    {
        $this->addColumn($this->lng->txt("login"), "login");
        $this->addColumn($this->lng->txt("firstname"), "first_name");
        $this->addColumn($this->lng->txt("lastname"), "last_name");
        $this->addColumn($this->lng->txt("action"));
    }

    public function parseData(): void
    {
        $data = $this->parseRows(ilObjOrgUnitTree::_getInstance()
                                                 ->getAssignements($_GET["ref_id"], $this->ilOrgUnitPosition));
        $this->setData($data);
    }

    /**
     * @param int[] $user_ids
     */
    private function parseRows(array $user_ids): array
    {
        $data = array();
        foreach ($user_ids as $user_id) {
            $data[] = $this->getRowForUser($user_id);
        }
        return $data;
    }

    private function getRowForUser(int $user_id): array
    {
        $user = new ilObjUser($user_id);
        $set = [];
        $set["login"] = $user->getLogin();
        $set["first_name"] = $user->getFirstname();
        $set["last_name"] = $user->getLastname();
        $set["user_object"] = $user;
        $set["user_id"] = $user_id;
        return $set;
    }

    public function fillRow(array $a_set): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $this->tpl->setVariable("LOGIN", $a_set["login"]);
        $this->tpl->setVariable("FIRST_NAME", $a_set["first_name"]);
        $this->tpl->setVariable("LAST_NAME", $a_set["last_name"]);
        //		$this->ctrl->setParameterByClass(ilLearningProgressGUI::class, "obj_id", $set["user_id"]);
        //		$this->ctrl->setParameterByClass(ilObjOrgUnitGUI::class, "obj_id", $set["user_id"]);
        $this->ctrl->setParameterByClass(ilOrgUnitUserAssignmentGUI::class, 'usr_id', $a_set["user_id"]);
        $this->ctrl->setParameterByClass(
            ilOrgUnitUserAssignmentGUI::class,
            'position_id',
            $this->ilOrgUnitPosition->getId()
        );
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setListTitle($lng->txt("Actions"));
        $selection->setId("selection_list_user_lp_" . $a_set["user_id"]);

        if ($ilAccess->checkAccess("view_learning_progress", "", $_GET["ref_id"])
            && ilObjUserTracking::_enabledLearningProgress()
            && ilObjUserTracking::_enabledUserRelatedData()
        ) {
            $this->ctrl->setParameterByClass(ilLearningProgressGUI::class, 'obj_id', $a_set["user_id"]);
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
            $selection->addItem(
                $this->lng->txt("remove"),
                "delete_from_employees",
                $this->ctrl->getLinkTargetByClass(
                    ilOrgUnitUserAssignmentGUI::class,
                    ilOrgUnitUserAssignmentGUI::CMD_CONFIRM
                )
            );
        }
        $this->tpl->setVariable("ACTIONS", $selection->getHTML());
    }
}
