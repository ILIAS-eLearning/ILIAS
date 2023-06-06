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

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitPositionGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionGUI extends BaseCommands
{
    use ILIAS\Repository\BaseGUIRequest;

    public const SUBTAB_SETTINGS = 'settings';
    public const SUBTAB_PERMISSIONS = 'obj_orgunit_positions';
    public const CMD_CONFIRM_DELETION = 'confirmDeletion';
    public const CMD_ASSIGN = 'assign';
    protected ilToolbarGUI $toolbar;
    protected \ILIAS\UI\Component\Link\Factory $link_factory;
    private ilCtrl $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilLanguage $lng;
    protected \ilOrgUnitPositionDBRepository $positionRepo;
    protected \ilOrgUnitUserAssignmentDBRepository $assignmentRepo;

    public function __construct()
    {
        $dic = ilOrgUnitLocalDIC::dic();
        $this->positionRepo = $dic["repo.Positions"];
        $this->assignmentRepo = $dic["repo.UserAssignments"];
        $this->ctrl = $dic['ctrl'];
        $this->lng = $dic['lng'];

        $to_int = $dic['refinery']->kindlyTo()->int();
        $ref_id = $dic['query']->retrieve('ref_id', $to_int);
        $this->link_factory = $dic['ui.factory']->link();

        parent::__construct();

        global $DIC;
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC->ui()->mainTemplate();

        $this->initRequest(
            $DIC->http(),
            $dic['refinery']
        );

        if (!ilObjOrgUnitAccess::_checkAccessPositions($ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilObjOrgUnitGUI::class);
        }
    }

    protected function getPossibleNextClasses(): array
    {
        return array(
            ilOrgUnitDefaultPermissionGUI::class,
            ilOrgUnitUserAssignmentGUI::class,
        );
    }

    protected function getActiveTabId(): string
    {
        return ilObjOrgUnitGUI::TAB_POSITIONS;
    }

    protected function index(): void
    {
        $url = $this->ctrl->getLinkTarget($this, self::CMD_ADD);
        $link = $this->link_factory->standard(
            $this->lng->txt('add_position'),
            $url
        );
        $this->toolbar->addComponent($link);

        $table = new ilOrgUnitPositionTableGUI($this, self::CMD_INDEX);
        $this->setContent($table->getHTML());
    }

    protected function add(): void
    {
        $form = new ilOrgUnitPositionFormGUI($this, $this->positionRepo->create());
        $this->tpl->setContent($form->getHTML());
    }

    protected function create(): void
    {
        $form = new ilOrgUnitPositionFormGUI($this, $this->positionRepo->create());
        if ($form->saveObject() === true) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_position_created'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function edit(): void
    {
        $this->addSubTabs();
        $this->activeSubTab(self::SUBTAB_SETTINGS);
        $position = $this->getPositionFromRequest();
        $form = new ilOrgUnitPositionFormGUI($this, $position);
        $form->fillForm();
        $this->tpl->setContent($form->getHTML());
    }

    protected function update(): void
    {
        $position = $this->getPositionFromRequest();
        $form = new ilOrgUnitPositionFormGUI($this, $position);
        $form->setValuesByPost();
        if ($form->saveObject() === true) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_position_updated'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function assign(): void
    {
        $position = $this->getPositionFromRequest();
        if ($position->isCorePosition()) {
            $this->cancel();
        }

        $employee_position = $this->positionRepo->getSingle(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE, 'core_identifier');
        $assignments = $this->assignmentRepo->getByPosition($position->getId());
        foreach ($assignments as $assignment) {
            $this->assignmentRepo->store($assignment->withPositionId($employee_position->getId()));
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_assignment_to_employee_done'), true);
    }

    protected function confirmDeletion(): void
    {
        $position = $this->getPositionFromRequest();
        if ($position->isCorePosition()) {
            $this->cancel();
        }
        $this->lng->loadLanguageModule('orgu');
        $position_string = $this->lng->txt("position") . ": ";
        $authority_string = $this->lng->txt("authorities") . ": ";
        $user_string = $this->lng->txt("user_assignments") . ": ";

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setCancel($this->lng->txt(self::CMD_CANCEL), self::CMD_CANCEL);
        $confirmation->setConfirm($this->lng->txt(self::CMD_DELETE), self::CMD_DELETE);
        $confirmation->setHeaderText($this->lng->txt('msg_confirm_deletion'));
        $confirmation->addItem(self::AR_ID, (string) $position->getId(), $position_string
            . $position->getTitle());
        // Authorities
        $authority_string .= implode(", ", $this->getAuthorityDescription($position->getAuthorities()));
        $confirmation->addItem('authorities', true, $authority_string);

        // Amount uf user-assignments
        $userIdsOfPosition = $this->assignmentRepo->getUsersByPosition($position->getId());
        $ilOrgUnitUserQueries = new ilOrgUnitUserQueries();
        $usersOfPosition = $ilOrgUnitUserQueries->findAllUsersByUserIds($userIdsOfPosition);
        $userNames = $ilOrgUnitUserQueries->getAllUserNames($usersOfPosition);

        $confirmation->addItem('users', true, $user_string . implode(', ', $userNames));

        $checkbox_assign_users = new ilCheckboxInputGUI('', 'assign_users');
        $checkbox_assign_users->setChecked(true);
        $checkbox_assign_users->setValue(1);
        $checkbox_assign_users->setOptionTitle('Assign affected users to employee role');
        $confirmation->addItem('assign_users', '', $checkbox_assign_users->render());

        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function delete(): void
    {
        if ($_POST['assign_users']) {
            $this->assign();
        }
        $position = $this->getPositionFromRequest();
        $this->positionRepo->delete($position->getId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_deleted'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function cancel(): void
    {
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function getPositionFromRequest(): ?ilOrgUnitPosition
    {
        return $this->positionRepo->getSingle($this->int(self::AR_ID), 'id');
    }


    public function addSubTabs(): void
    {
        $this->ctrl->saveParameter($this, 'arid');
        $this->ctrl->saveParameterByClass(ilOrgUnitDefaultPermissionGUI::class, 'arid');
        $this->pushSubTab(self::SUBTAB_SETTINGS, $this->ctrl
                                                      ->getLinkTarget($this, self::CMD_EDIT));
        $this->pushSubTab(self::SUBTAB_PERMISSIONS, $this->ctrl
                                                         ->getLinkTargetByClass(
                                                             ilOrgUnitDefaultPermissionGUI::class,
                                                             self::CMD_INDEX
                                                         ));
    }

    /**
     * Returns descriptions for authorities as an array of strings
     *
     * @param ilOrgUnitAuthority[] $authorities
     */
    private function getAuthorityDescription(array $authorities): array
    {
        $lang = $this->lng;
        $lang->loadLanguageModule('orgu');
        $lang_keys = array(
            'in',
            'over',
            'scope_' . ilOrgUnitAuthority::SCOPE_SAME_ORGU,
            'scope_' . ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS,
            'over_' . ilOrgUnitAuthority::OVER_EVERYONE,
        );
        $t = [];
        foreach ($lang_keys as $key) {
            $t[$key] = $lang->txt($key);
        }

        $authority_description = [];
        foreach ($authorities as $authority) {
            switch ($authority->getOver()) {
                case ilOrgUnitAuthority::OVER_EVERYONE:
                    $over_txt = $t["over_" . $authority->getOver()];
                    break;
                default:
                    $over_txt = $this->positionRepo
                        ->getSingle($authority->getOver(), 'id')
                        ->getTitle();
                    break;
            }

            $authority_description[] = " " . $t["over"] . " " . $over_txt . " " . $t["in"] . " " . $t["scope_" . $authority->getScope()];
        }

        return $authority_description;
    }
}
