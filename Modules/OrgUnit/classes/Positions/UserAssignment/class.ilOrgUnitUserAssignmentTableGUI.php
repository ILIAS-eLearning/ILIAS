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

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;
use ILIAS\Modules\OrgUnit\ARHelper\DropdownBuilder;

/**
 * Class ilOrgUnitUserAssignmentTableGUI
 */
class ilOrgUnitUserAssignmentTableGUI extends ilTable2GUI
{
    protected ilOrgUnitPosition $ilOrgUnitPosition;
    protected DropdownBuilder $dropdownbuilder;
    protected \ilAccess $access;
    protected int $ref_id;

    public function __construct(BaseCommands $parent_obj, string $parent_cmd, ilOrgUnitPosition $position)
    {
        $dic = ilOrgUnitLocalDIC::dic();
        $this->dropdownbuilder = $dic['dropdownbuilder'];
        $this->parent_obj = $parent_obj;
        $this->ilOrgUnitPosition = $position;
        $this->ctrl = $dic['ctrl'];
        $this->access = $dic['access'];
        $to_int = $dic['refinery']->kindlyTo()->int();
        $this->ref_id = $dic['query']->retrieve('ref_id', $to_int);

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
            ->getAssignedUsers([(int) $_GET["ref_id"]], $this->ilOrgUnitPosition->getId()));
        $this->setData($data);
    }

    /**
     * @param int[] $user_ids
     */
    private function parseRows(array $user_ids): array
    {
        $data = [];
        foreach ($user_ids as $user_id) {
            $data[] = $this->getRowForUser($user_id);
        }
        return $data;
    }

    private function getRowForUser(int $user_id): array
    {
        $user = new ilObjUser($user_id);
        return [
            'login' => $user->getLogin(),
            'first_name' => $user->getFirstname(),
            'last_name' => $user->getLastname(),
            'user_object' => $user,
            'user_id' => $user_id,
            'active' => $user->getActive()
        ];
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("LOGIN", $a_set["login"]);
        $this->tpl->setVariable("FIRST_NAME", $a_set["first_name"]);
        $this->tpl->setVariable("LAST_NAME", $a_set["last_name"]);

        if($a_set["active"] === false) {
            $this->tpl->setVariable("INACTIVE", $this->lng->txt('usr_account_inactive'));
        }

        $this->ctrl->setParameterByClass(ilOrgUnitUserAssignmentGUI::class, 'usr_id', $a_set["user_id"]);
        $this->ctrl->setParameterByClass(ilOrgUnitUserAssignmentGUI::class, 'position_id', $this->ilOrgUnitPosition->getId());
        $this->ctrl->setParameterByClass(ilLearningProgressGUI::class, 'obj_id', $a_set["user_id"]);
        $dropdownbuilder = $this->dropdownbuilder
            ->withItem(
                'show_learning_progress',
                $this->ctrl->getLinkTargetByClass([
                    ilAdministrationGUI::class,
                    ilObjOrgUnitGUI::class,
                    ilLearningProgressGUI::class,
                    ], ""),
                $this->access->checkAccess("view_learning_progress", "", $this->ref_id)
                    && ilObjUserTracking::_enabledLearningProgress()
                    && ilObjUserTracking::_enabledUserRelatedData()
            )
            ->withItem(
                'remove',
                $this->ctrl->getLinkTargetByClass(
                    ilOrgUnitUserAssignmentGUI::class,
                    ilOrgUnitUserAssignmentGUI::CMD_CONFIRM
                ),
                $this->access->checkAccess("write", "", $this->ref_id)
            )
            ->get();

        $this->tpl->setVariable("ACTIONS", $dropdownbuilder);
    }
}
