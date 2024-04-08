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
declare(strict_types=1);

use ILIAS\components\OrgUnit\ARHelper\BaseCommands;

use ILIAS\UI\Component\Table;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

/**
 * Class ilOrgUnitUserAssignmentGUI
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @author       dkloepfer
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @ilCtrl_Calls ilOrgUnitUserAssignmentGUI: ilRepositorySearchGUI
 */
class ilOrgUnitUserAssignmentGUI extends BaseCommands
{
    public const CMD_ASSIGNMENTS_RECURSIVE = 'assignmentsRecursive';
    public const SUBTAB_ASSIGNMENTS = 'user_assignments';
    public const SUBTAB_ASSIGNMENTS_RECURSIVE = 'user_assignments_recursive';
    private \ilGlobalTemplateInterface $main_tpl;
    private ilToolbarGUI $toolbar;
    private ilAccessHandler $access;
    private \ilOrgUnitPositionDBRepository $positionRepo;
    private \ilOrgUnitUserAssignmentDBRepository $assignmentRepo;

    public function __construct()
    {
        global $DIC;

        parent::__construct(['orgu', 'staff']);

        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->access = $DIC->access();


        $dic = \ilOrgUnitLocalDIC::dic();
        $this->positionRepo = $dic["repo.Positions"];
        $this->assignmentRepo = $dic["repo.UserAssignments"];
    }

    public function executeCommand(): void
    {
        if (
            !ilObjOrgUnitAccess::_checkAccessPositions(
                (int) filter_input(INPUT_GET, "ref_id", FILTER_SANITIZE_NUMBER_INT)
            )
            &&
            !ilObjOrgUnitAccess::_checkAccessStaff(
                (int) filter_input(INPUT_GET, "ref_id", FILTER_SANITIZE_NUMBER_INT)
            )
        ) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilObjOrgUnitGUI::class);
        }

        $r = $this->http->request();
        switch ($this->ctrl->getNextClass()) {
            case strtolower(ilRepositorySearchGUI::class):
                switch ($this->ctrl->getCmd()) {
                    case 'addUserFromAutoComplete':
                        if ($r->getQueryParams()['addusertype'] == "staff") {
                            $this->addStaff();
                        }
                        break;
                    default:
                        $repo = new ilRepositorySearchGUI();
                        $repo->setCallback($this, 'addStaffFromSearch');
                        $this->ctrl->forwardCommand($repo);
                        break;
                }
                break;

            default:
                parent::executeCommand();
                break;
        }
    }

    protected function index(): void
    {
        $this->addSubTabs();
        $this->activeSubTab(self::SUBTAB_ASSIGNMENTS);

        // Header
        $types = $this->positionRepo->getArray('id', 'title');

        $this->ctrl->setParameterByClass(ilRepositorySearchGUI::class, 'addusertype', 'staff');
        ilRepositorySearchGUI::fillAutoCompleteToolbar($this, $this->toolbar, array(
            'auto_complete_name' => $this->lng->txt('user'),
            'user_type' => $types,
            'submit_name' => $this->lng->txt('add'),
        ));

        // Tables
        $html = '';
        $tables = [];

        foreach ($this->positionRepo->getPositionsForOrgUnit($this->getParentRefId()) as $ilOrgUnitPosition) {
            $tables[] = $this->getStaffTable($ilOrgUnitPosition);

            $ilOrgUnitUserAssignmentTableGUI = new ilOrgUnitUserAssignmentTableGUI(
                $this,
                self::CMD_INDEX,
                $ilOrgUnitPosition
            );
            $html .= $ilOrgUnitUserAssignmentTableGUI->getHTML();
        }
        $this->setContent(
            $this->ui_renderer->render($tables)
            . '<hr><hr>'
            . $html
        );
    }

    protected function assignmentsRecursive(): void
    {
        $this->addSubTabs();
        $this->activeSubTab(self::SUBTAB_ASSIGNMENTS_RECURSIVE);
        // Tables
        $html = '';
        foreach ($this->positionRepo->getPositionsForOrgUnit($this->getParentRefId()) as $ilOrgUnitPosition) {
            $ilOrgUnitRecursiveUserAssignmentTableGUI =
                new ilOrgUnitRecursiveUserAssignmentTableGUI(
                    $this,
                    self::CMD_ASSIGNMENTS_RECURSIVE,
                    $ilOrgUnitPosition
                );
            $html .= $ilOrgUnitRecursiveUserAssignmentTableGUI->getHTML();
        }
        $this->setContent($html);
    }


    protected function getStaffTable(ilOrgUnitPosition $position): Table\Data
    {
        $columns = [
            'login' => $this->ui_factory->table()->column()->text($this->lng->txt("login")),
            'firstname' => $this->ui_factory->table()->column()->text($this->lng->txt("firstname")),
            'lastname' => $this->ui_factory->table()->column()->text($this->lng->txt("lastname")),
        ];

        $actions = [
            'remove' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('remove'),
                $this->url_builder->withParameter($this->action_token, "remove"),
                $this->row_id_token
            ),
        ];

        return $this->ui_factory->table()
            ->data($position->getTitle(), $columns, $this->assignmentRepo)
            ->withId('orgu_positions')
            ->withActions($actions)
            ->withAdditionalParameters([
                'position_id' => $position->getId(),
                'orgu_ids' => [$this->getParentRefId()]
            ])
            ->withRequest($this->request);
    }





    protected function confirm(): void
    {
        $confirmation = $this->getConfirmationGUI();
        $confirmation->setConfirm($this->lng->txt('remove_user'), self::CMD_DELETE);

        $this->setContent($confirmation->getHTML());
    }

    protected function confirmRecursive(): void
    {
        $confirmation = $this->getConfirmationGUI();
        $confirmation->setConfirm($this->lng->txt('remove_user'), self::CMD_DELETE_RECURSIVE);

        $this->setContent($confirmation->getHTML());
    }

    protected function getConfirmationGUI(): ilConfirmationGUI
    {
        $this->ctrl->saveParameter($this, 'position_id');
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setCancel($this->lng->txt(self::CMD_CANCEL), self::CMD_CANCEL);

        $params = $this->http->request()->getQueryParams();
        $usr_id = $params['usr_id'];
        $position_id = $params['position_id'];

        $types = $this->positionRepo->getArray('id', 'title');
        $position_title = $types[$position_id];

        $confirmation->setHeaderText(sprintf($this->lng->txt('msg_confirm_remove_user'), $position_title));
        $confirmation->addItem('usr_id', $usr_id, ilObjUser::_lookupLogin((int) $usr_id));

        return $confirmation;
    }

    protected function delete(): void
    {
        $params = $this->http->request()->getQueryParams();
        $usr_id = (int) $_POST['usr_id'];
        $position_id = (int) $params['position_id'];

        $assignment = $this->assignmentRepo->find(
            $usr_id,
            $position_id,
            $this->getParentRefId()
        );
        if (!$assignment) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("user_not_found_to_delete"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }
        $this->assignmentRepo->delete($assignment);

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('remove_successful'), true);
        $this->cancel();
    }

    protected function deleteRecursive(): void
    {
        $r = $this->http->request();
        $assignments = $this->assignmentRepo
            ->getByUserAndPosition((int) $_POST['usr_id'], (int) $r->getQueryParams()['position_id']);

        foreach ($assignments as $assignment) {
            $this->assignmentRepo->delete($assignment);
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('remove_successful'), true);
        $this->cancel();
    }

    protected function cancel(): void
    {
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    public function addStaff(): void
    {
        if (!$this->access->checkAccess("write", "", $this->getParentRefId())) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $users = explode(',', $_POST['user_login']);
        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);
            if ($user_id) {
                $user_ids[] = $user_id;
            }
        }

        if (!count($user_ids)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("user_not_found"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $position_id = (int) ($_POST['user_type'] ?? ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

        if ($position_id === 0 || !$this->positionRepo->getSingle($position_id, 'id')) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("user_not_found"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }
        foreach ($user_ids as $user_id) {
            $assignment = $this->assignmentRepo->get($user_id, $position_id, $this->getParentRefId());
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("users_successfuly_added"), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    /**
     * @param array<int> $user_ids
     */
    public function addStaffFromSearch(array $user_ids, ?string $user_type = null): void
    {
        if (!$this->access->checkAccess("write", "", $this->getParentRefId())) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        if (!count($user_ids)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("user_not_found"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $position_id = (int) ($user_type ?? ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

        if ($position_id === 0 || !$this->positionRepo->getSingle($position_id, 'id')) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("user_not_found"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }
        foreach ($user_ids as $user_id) {
            $assignment = $this->assignmentRepo->get($user_id, $position_id, $this->getParentRefId());
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("users_successfuly_added"), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    public function addSubTabs(): void
    {
        $this->pushSubTab(self::SUBTAB_ASSIGNMENTS, $this->ctrl
                                                         ->getLinkTarget($this, self::CMD_INDEX));
        $this->pushSubTab(self::SUBTAB_ASSIGNMENTS_RECURSIVE, $this->ctrl
                                                                   ->getLinkTarget(
                                                                       $this,
                                                                       self::CMD_ASSIGNMENTS_RECURSIVE
                                                                   ));
    }
}
