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

use ILIAS\EmployeeTalk\UI\ControlFlowCommandHandler;
use ILIAS\EmployeeTalk\UI\ControlFlowCommand;
use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\components\EmployeeTalk\Talk\Repository\EmployeeTalkRepository;
use ILIAS\components\EmployeeTalk\Talk\Repository\IliasDBEmployeeTalkRepository;
use ILIAS\components\EmployeeTalk\Talk\DAO\EmployeeTalk;
use ILIAS\components\EmployeeTalk\Talk\EmployeeTalkPositionAccessLevel;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\DI\UIServices;

/**
 * Class ilEmployeeTalkMyStaffListGUI
 *
 * @ilCtrl_IsCalledBy ilEmployeeTalkMyStaffListGUI: ilMyStaffGUI
 * @ilCtrl_IsCalledBy ilEmployeeTalkMyStaffListGUI: ilFormPropertyDispatchGUI
 * @ilCtrl_Calls ilEmployeeTalkMyStaffListGUI: ilObjEmployeeTalkGUI
 * @ilCtrl_Calls ilEmployeeTalkMyStaffListGUI: ilObjEmployeeTalkSeriesGUI
 */
final class ilEmployeeTalkMyStaffListGUI implements ControlFlowCommandHandler
{
    private UIServices $ui;
    private ilLanguage $language;
    private ilTabsGUI $tabs;
    private ilMyStaffAccess $access;
    private ilCtrl $controlFlow;
    private ilObjUser $currentUser;
    private EmployeeTalkRepository $repository;
    private ilObjEmployeeTalkAccess $talkAccess;

    public function __construct()
    {
        global $DIC;

        $DIC->language()->loadLanguageModule('etal');
        $DIC->language()->loadLanguageModule('orgu');
        $this->language = $DIC->language();
        $this->talkAccess = new ilObjEmployeeTalkAccess();
        $this->access = ilMyStaffAccess::getInstance();

        $this->tabs = $DIC->tabs();
        $this->ui = $DIC->ui();
        $this->controlFlow = $DIC->ctrl();
        $this->currentUser = $DIC->user();
        $this->repository = new IliasDBEmployeeTalkRepository($DIC->database());
    }

    public function executeCommand(): void
    {
        $nextClass = $this->controlFlow->getNextClass();
        $command = $this->controlFlow->getCmd(ControlFlowCommand::DEFAULT);
        switch ($nextClass) {
            case strtolower(ilObjEmployeeTalkSeriesGUI::class):
                $gui = new ilObjEmployeeTalkSeriesGUI();
                $this->controlFlow->forwardCommand($gui);
                break;
            case strtolower(ilObjEmployeeTalkGUI::class):
                $gui = new ilObjEmployeeTalkGUI();
                if ($this->access->hasCurrentUserAccessToTalks()) {
                    $this->tabs->setBackTarget(
                        $this->language->txt('etal_talks'),
                        $this->controlFlow->getLinkTarget($this, ControlFlowCommand::INDEX)
                    );
                }
                $this->controlFlow->forwardCommand($gui);
                break;
            case strtolower(ilFormPropertyDispatchGUI::class):
                $this->controlFlow->setReturn($this, ControlFlowCommand::INDEX);
                $table = new ilEmployeeTalkTableGUI($this, ControlFlowCommand::INDEX);
                $table->executeCommand();
                break;
            default:
                switch ($command) {
                    case ControlFlowCommand::APPLY_FILTER:
                        $this->applyFilter();
                        break;
                    case ControlFlowCommand::RESET_FILTER:
                        $this->resetFilter();
                        break;
                    default:
                        $this->view();
                }
        }
    }

    private function applyFilter(): void
    {
        $table = new ilEmployeeTalkTableGUI($this, ControlFlowCommand::APPLY_FILTER);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->view();
    }

    private function resetFilter(): void
    {
        $table = new ilEmployeeTalkTableGUI($this, ControlFlowCommand::RESET_FILTER);
        $table->resetOffset();
        $table->resetFilter();
        $this->view();
    }

    private function view(): void
    {
        $this->loadActionBar();
        $this->loadTabs();
        $this->ui->mainTemplate()->setTitle($this->language->txt('mm_org_etal'));
        $this->ui->mainTemplate()->setTitleIcon(ilUtil::getImagePath('standard/icon_etal.svg'));
        $this->ui->mainTemplate()->setContent($this->loadTable()->getHTML());
    }

    private function loadTabs(): void
    {
        $this->tabs->addTab("view_content", "Content", "#");
        $this->tabs->activateTab("view_content");
        //$this->tabs->addTab("placeholder", "", "#");
        $this->tabs->setForcePresentationOfSingleTab(true);
    }

    private function loadActionBar(): void
    {
        if (!$this->talkAccess->canCreate()) {
            return;
        }

        $templates = new CallbackFilterIterator(
            new ArrayIterator(ilObject::_getObjectsByType("talt")),
            function (array $item) {
                return
                    (
                        $item['offline'] === "0" ||
                        $item['offline'] === 0 ||
                        $item['offline'] === null
                    ) && ilObjTalkTemplate::_hasUntrashedReference(intval($item['obj_id']));
            }
        );

        $buttons = [];
        $talk_class = strtolower(ilObjEmployeeTalkSeriesGUI::class);
        foreach ($templates as $item) {
            $objId = intval($item['obj_id']);
            $refId = ilObject::_getAllReferences($objId);

            // Templates only have one ref id
            $this->controlFlow->setParameterByClass($talk_class, 'new_type', ilObjEmployeeTalkSeries::TYPE);
            $this->controlFlow->setParameterByClass($talk_class, 'template', array_pop($refId));
            $this->controlFlow->setParameterByClass($talk_class, 'ref_id', ilObjTalkTemplateAdministration::getRootRefId());
            $url = $this->controlFlow->getLinkTargetByClass($talk_class, ControlFlowCommand::CREATE);
            $this->controlFlow->clearParametersByClass($talk_class);

            $buttons[] = $this->ui->factory()->link()->standard(
                (string) $item["title"],
                $url
            );
        }

        $dropdown = $this->ui->factory()->dropdown()->standard($buttons)->withLabel(
            $this->language->txt('etal_add_new_item')
        );
        $this->ui->mainTemplate()->setVariable(
            'SELECT_OBJTYPE_REPOS',
            $this->ui->renderer()->render($dropdown)
        );
    }

    private function loadTable(): ilEmployeeTalkTableGUI
    {
        $table = new ilEmployeeTalkTableGUI($this, ControlFlowCommand::DEFAULT);

        /**
         * @var EmployeeTalk[] $talks
         */
        $talks = [];
        if ($this->currentUser->getId() === 6) {
            $talks = $this->repository->findAll();
        } else {
            $users = $this->getEmployeeIdsWithValidPermissionRights($this->currentUser->getId());
            $talks = $this->repository->findByUserOrTheirEmployees($this->currentUser->getId(), $users);
        }
        $table->setTalkData($talks);

        return $table;
    }

    private function getEmployeeIdsWithValidPermissionRights(int $userId): array
    {
        $myStaffAccess = ilMyStaffAccess::getInstance();
        //The user has always access to his own talks
        $managedUsers = [$userId];

        /**
         * @var Array<int, Array<string>> $managedOrgUnitUsersOfUserByPosition
         */
        $managedOrgUnitUsersOfUserByPosition = $myStaffAccess->getUsersForUserPerPosition($userId);

        foreach ($managedOrgUnitUsersOfUserByPosition as $position => $managedOrgUnitUserByPosition) {
            // Check if the position has any relevant position rights
            $permissionSet = ilOrgUnitPermissionQueries::getTemplateSetForContextName(ilObjEmployeeTalk::TYPE, strval($position));
            $isAbleToExecuteOperation = array_reduce($permissionSet->getOperations(), function (bool $prev, ilOrgUnitOperation $it) {
                return $prev || $it->getOperationString() === EmployeeTalkPositionAccessLevel::VIEW;
            }, false);

            if (!$isAbleToExecuteOperation) {
                continue;
            }

            foreach ($managedOrgUnitUserByPosition as $managedOrgUnitUser) {
                $managedUsers[] = intval($managedOrgUnitUser);
            }
        }

        $managedUsers = array_unique($managedUsers, SORT_NUMERIC);

        return $managedUsers;
    }
}
