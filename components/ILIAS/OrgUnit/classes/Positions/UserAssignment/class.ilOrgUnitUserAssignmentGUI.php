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

use ILIAS\components\OrgUnit\ARHelper\BaseCommands;

use ILIAS\UI\Component\Table;

/**
 * Class ilOrgUnitUserAssignmentGUI
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @author       dkloepfer
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @ilCtrl_Calls ilOrgUnitUserAssignmentGUI: ilRepositorySearchGUI
 */
class ilOrgUnitUserAssignmentGUI extends BaseCommands
{
    public const SUBTAB_ASSIGNMENTS = 'user_assignments';
    public const SUBTAB_ASSIGNMENTS_RECURSIVE = 'user_assignments_recursive';
    public const CMD_ASSIGNMENTS_RECURSIVE = 'assignmentsRecursive';
    public const CMD_REMOVE_CONFIRM = 'confirmRemove';
    public const CMD_REMOVE = 'remove';
    public const CMD_REMOVE_RECURSIVELY_CONFIRM = 'confirmRemoveRecursively';
    public const CMD_REMOVE_RECURSIVE = "removeRecursive";
    public const CMD_SHOW_LP = 'showLearningProgress';

    private ilToolbarGUI $toolbar;
    private \ilOrgUnitPositionDBRepository $positionRepo;
    private \ilOrgUnitUserAssignmentDBRepository $assignmentRepo;

    public function __construct()
    {
        parent::__construct(['orgu', 'staff']);

        global $DIC;
        $this->toolbar = $DIC->toolbar();

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
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
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

        $types = $this->positionRepo->getArray('id', 'title');
        $this->ctrl->setParameterByClass(ilRepositorySearchGUI::class, 'addusertype', 'staff');
        ilRepositorySearchGUI::fillAutoCompleteToolbar($this, $this->toolbar, array(
            'auto_complete_name' => $this->lng->txt('user'),
            'user_type' => $types,
            'submit_name' => $this->lng->txt('add'),
        ));

        $tables = [];
        foreach ($this->positionRepo->getPositionsForOrgUnit($this->getParentRefId()) as $ilOrgUnitPosition) {
            $tables[] = $this->getStaffTable($ilOrgUnitPosition, [$this->getParentRefId()], false);
        }
        $this->setContent($this->ui_renderer->render($tables));
    }

    protected function assignmentsRecursive(): void
    {
        $this->addSubTabs();
        $this->activeSubTab(self::SUBTAB_ASSIGNMENTS_RECURSIVE);

        $orgu_ref_id = $this->getParentRefId();
        $orgu_tree = ilObjOrgUnitTree::_getInstance();
        $permission_access_staff_recursive = [];
        // maybe any parent gives us recursive permission
        (int) $root = (int) ilObjOrgUnit::getRootOrgRefId();
        $parent = (int) $orgu_tree->getParent($orgu_ref_id);

        while ($parent !== $root) {
            if (ilObjOrgUnitAccess::_checkAccessStaffRec($parent)) {
                array_merge(
                    $permission_access_staff_recursive = $permission_access_staff_recursive,
                    $orgu_tree->getAllChildren($parent)
                );
            }
            $parent = (int) $orgu_tree->getParent($parent);
        }

        foreach ($orgu_tree->getAllChildren($orgu_ref_id) as $ref_id) {
            $recursive = in_array($ref_id, $permission_access_staff_recursive);
            if (!$recursive) {
                // ok, so no permission from above, lets check local permissions
                if (true || ilObjOrgUnitAccess::_checkAccessStaffRec($ref_id)) {
                    // update recursive permissions
                    $permission_access_staff_recursive = array_merge(
                        $permission_access_staff_recursive,
                        $orgu_tree->getAllChildren($ref_id)
                    );
                } elseif (!ilObjOrgUnitAccess::_checkAccessStaff($ref_id)) {
                    // skip orgus in which one may not view the staff
                    continue;
                }
            }
        }

        $tables = [];
        foreach ($this->positionRepo->getPositionsForOrgUnit($this->getParentRefId()) as $ilOrgUnitPosition) {
            $tables[] = $this->getStaffTable($ilOrgUnitPosition, $permission_access_staff_recursive, true);
        }
        $this->setContent($this->ui_renderer->render($tables));
    }

    protected function confirmRemove(bool $recursive = false): void
    {
        list($position_id, $usr_id) = $this->getPositionAndUserIdFromTableQuery();

        $id = implode('_', [(string) $position_id, (string) $usr_id]);
        $usr_name = ilObjUser::_lookupLogin($usr_id);
        $pos_name = $this->positionRepo->getSingle($position_id, 'id')->getTitle();

        $item = $this->ui_factory->modal()->interruptiveItem()
            ->keyValue($id, $usr_name, $pos_name);

        $del_command = $recursive ? self::CMD_REMOVE_RECURSIVE : self::CMD_REMOVE;
        $action = $this->url_builder
            ->withParameter($this->row_id_token, $id)
            ->withParameter($this->action_token, $del_command)
            ->buildURI()->__toString();

        echo($this->ui_renderer->renderAsync([
            $this->ui_factory->modal()->interruptive(
                $this->lng->txt('remove_user'),
                sprintf($this->lng->txt('msg_confirm_remove_user'), $pos_name),
                $action
            )->withAffectedItems([$item])
        ]));
        exit();
    }

    protected function confirmRemoveRecursively(): void
    {
        $this->confirmRemove(true);
    }

    protected function remove(): void
    {
        list($position_id, $usr_id) = $this->getPositionAndUserIdFromTableQuery();

        $assignment = $this->assignmentRepo->find(
            $usr_id,
            $position_id,
            $this->getParentRefId()
        );
        if (!$assignment) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("user_not_found_to_delete"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }
        $this->assignmentRepo->delete($assignment);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('remove_successful'), true);
        $this->cancel();
    }

    protected function removeRecursive(): void
    {
        list($position_id, $usr_id) = $this->getPositionAndUserIdFromTableQuery();
        $assignments = $this->assignmentRepo->getByUserAndPosition($usr_id, $position_id);
        foreach ($assignments as $assignment) {
            $this->assignmentRepo->delete($assignment);
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('remove_successful'), true);
        $this->assignmentsRecursive();
    }

    protected function showLearningProgress(): void
    {
        list($position_id, $usr_id) = $this->getPositionAndUserIdFromTableQuery();
        $this->ctrl->setParameterByClass(ilLearningProgressGUI::class, 'obj_id', $usr_id);
        $target = $this->ctrl->getLinkTargetByClass(ilLearningProgressGUI::class, "");
        $this->ctrl->redirectToURL($target);
    }

    protected function getStaffTable(
        ilOrgUnitPosition $position,
        array $orgu_ids,
        bool $recursive = false
    ): Table\Data {
        $columns = [
            'login' => $this->ui_factory->table()->column()->text($this->lng->txt("login")),
            'firstname' => $this->ui_factory->table()->column()->text($this->lng->txt("firstname")),
            'lastname' => $this->ui_factory->table()->column()->text($this->lng->txt("lastname")),
            'active' => $this->ui_factory->table()->column()->boolean(
                $this->lng->txt("active"),
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_ok.svg', '', 'small'),
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_not_ok.svg', '', 'small')
            )->withIsOptional(true, false),
        ];

        $remove_cmd = self::CMD_REMOVE_CONFIRM;
        if($recursive) {
            $remove_cmd = self::CMD_REMOVE_RECURSIVELY_CONFIRM;
            $columns['orgu_title'] = $this->ui_factory->table()->column()->text($this->lng->txt("obj_orgu"));
        }

        $actions = [
            'remove' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('remove'),
                $this->url_builder->withParameter($this->action_token, $remove_cmd),
                $this->row_id_token
            )->withAsync(),

            'show_learning_progress' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('show_learning_progress'),
                $this->url_builder->withParameter($this->action_token, self::CMD_SHOW_LP),
                $this->row_id_token
            ),
        ];

        $lp_visible = array_filter(
            $orgu_ids,
            fn($id) => $this->access->checkAccess("view_learning_progress", "", $id)
        );

        return $this->ui_factory->table()
            ->data($position->getTitle(), $columns, $this->assignmentRepo)
            ->withId(implode('.', ['orgustaff',$this->getParentRefId(),$position->getId()]))
            ->withActions($actions)
            ->withAdditionalParameters([
                'position_id' => $position->getId(),
                'orgu_ids' => $orgu_ids,
                'lp_visible_ref_ids' => $lp_visible
            ])
            ->withRequest($this->request);
    }

    /**
     * @return array<int, int> position_id, user_id
     */
    protected function getPositionAndUserIdFromTableQuery(): array
    {
        if($this->query->has($this->row_id_token->getName())) {
            return $this->query->retrieve(
                $this->row_id_token->getName(),
                $this->refinery->custom()->transformation(
                    function ($v) {
                        $id = is_array($v) ? array_shift($v) : $v;
                        return array_map('intval', explode('_', $id));
                    }
                )
            );
        }
        throw new \Exception('no position/user id in query');
    }

    protected function cancel(): void
    {
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    public function addStaff(): void
    {
        if (!$this->access->checkAccess("write", "", $this->getParentRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
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
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("user_not_found"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $position_id = (int) ($_POST['user_type'] ?? ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

        if ($position_id === 0 || !$this->positionRepo->getSingle($position_id, 'id')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("user_not_found"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }
        foreach ($user_ids as $user_id) {
            $assignment = $this->assignmentRepo->get($user_id, $position_id, $this->getParentRefId());
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("users_successfuly_added"), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    /**
     * @param array<int> $user_ids
     */
    public function addStaffFromSearch(array $user_ids, ?string $user_type = null): void
    {
        if (!$this->access->checkAccess("write", "", $this->getParentRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        if (!count($user_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("user_not_found"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $position_id = (int) ($user_type ?? ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

        if ($position_id === 0 || !$this->positionRepo->getSingle($position_id, 'id')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("user_not_found"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }
        foreach ($user_ids as $user_id) {
            $assignment = $this->assignmentRepo->get($user_id, $position_id, $this->getParentRefId());
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("users_successfuly_added"), true);
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
