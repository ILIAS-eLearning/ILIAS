<?php

declare(strict_types=1);

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

use ILIAS\EmployeeTalk\UI\ControlFlowCommandHandler;
use ILIAS\EmployeeTalk\UI\ControlFlowCommand;
use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\Modules\EmployeeTalk\Talk\Repository\EmployeeTalkRepository;
use ILIAS\Modules\EmployeeTalk\Talk\Repository\IliasDBEmployeeTalkRepository;
use ILIAS\Modules\EmployeeTalk\Talk\DAO\EmployeeTalk;
use ILIAS\Modules\EmployeeTalk\Talk\EmployeeTalkPositionAccessLevel;
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
    private ilCtrl $controlFlow;
    private ilObjUser $currentUser;
    private EmployeeTalkRepository $repository;
    private HTTPServices $http;
    private ilObjEmployeeTalkAccess $talkAccess;
    private ILIAS\Refinery\Factory $refinery;

    public function __construct()
    {
        /**
         * @var \ILIAS\DI\Container $container
         */
        $container = $GLOBALS['DIC'];

        $container->language()->loadLanguageModule('etal');
        $container->language()->loadLanguageModule('orgu');
        $this->language = $container->language();
        $this->http = $container->http();
        $this->talkAccess = new ilObjEmployeeTalkAccess();

        $this->tabs = $container->tabs();
        $this->ui = $container->ui();
        $this->refinery = $container->refinery();
        $this->controlFlow = $container->ctrl();
        $this->ui->mainTemplate()->setTitle($container->language()->txt('mm_org_etal'));
        $this->currentUser = $container->user();
        $this->repository = new IliasDBEmployeeTalkRepository($container->database());
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
                    case ControlFlowCommand::TABLE_ACTIONS:
                        $this->getActions();
                        break;
                    default:
                        $this->view();
                }
        }
    }

    private function getActions(): void
    {
        $listGUI = new ilAdvancedSelectionListGUI();

        $class = strtolower(ilObjEmployeeTalkGUI::class);
        $classPath = [
            strtolower(ilDashboardGUI::class),
            strtolower(ilMyStaffGUI::class),
            strtolower(ilEmployeeTalkMyStaffListGUI::class),
            $class
        ];

        $queryParams = $this->http->request()->getQueryParams();
        if (!key_exists('ref_id', $queryParams)) {
            echo $listGUI->getHTML(true);
            exit;
        }

        $refId = $this->http
            ->wrapper()
            ->query()
            ->retrieve('ref_id', $this->refinery->kindlyTo()->int());
        $this->controlFlow->setParameterByClass($class, "ref_id", $refId);
        if ($this->talkAccess->canEdit($refId)) {
            $listGUI->addItem($this->language->txt('edit'), '', $this->controlFlow->getLinkTargetByClass($classPath, ControlFlowCommand::UPDATE));
        } else {
            $listGUI->addItem($this->language->txt('view'), '', $this->controlFlow->getLinkTargetByClass($classPath, ControlFlowCommand::INDEX));
        }

        if ($this->talkAccess->canDelete($refId)) {
            $this->controlFlow->setParameterByClass($class, "item_ref_id", $refId);
            $listGUI->addItem($this->language->txt('delete'), '', $this->controlFlow->getLinkTargetByClass($classPath, ControlFlowCommand::DELETE_INDEX));
        }

        echo $listGUI->getHTML(true);
        exit;
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

    private function view(): bool
    {
        $this->loadActionBar();
        $this->loadTabs();
        $this->ui->mainTemplate()->setContent($this->loadTable()->getHTML());
        return true;
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
        $talkAccess = new ilObjEmployeeTalkAccess();
        if (!$talkAccess->canCreate()) {
            return;
        }

        $gl = new ilGroupedListGUI();
        $gl->setAsDropDown(true, false);

        $templates = new CallbackFilterIterator(
            new ArrayIterator(ilObject::_getObjectsByType("talt")),
            function (array $item) {
                return
                    (
                        $item['offline'] === "0" ||
                        $item['offline'] === null
                    ) && ilObjTalkTemplate::_hasUntrashedReference(intval($item['obj_id']));
            }
        );

        foreach ($templates as $item) {
            $type = $item["type"];

            $objId = intval($item['obj_id']);
            $path = ilObject::_getIcon($objId, 'tiny', $type);
            $icon = ($path != "")
                ? ilUtil::img($path, "") . " "
                : "";

            $url = $this->controlFlow->getLinkTargetByClass(strtolower(ilObjEmployeeTalkSeriesGUI::class), ControlFlowCommand::CREATE);
            $refId = ilObject::_getAllReferences($objId);

            // Templates only have one ref id
            $url .= "&new_type=" . ilObjEmployeeTalkSeries::TYPE;
            $url .= "&template=" . array_pop($refId);
            $url .= "&ref_id=" . ilObjTalkTemplateAdministration::getRootRefId();

            $ttip = ilHelp::getObjCreationTooltipText("tals");

            $gl->addEntry(
                $icon . $item["title"],
                $url,
                "_top",
                "",
                "",
                $type,
                $ttip,
                "bottom center",
                "top center",
                false
            );
        }

        $adv = new ilAdvancedSelectionListGUI();
        $adv->setListTitle($this->language->txt("etal_add_new_item"));
        //$gl->getHTML();
        $adv->setGroupedList($gl);
        $adv->setStyle(ilAdvancedSelectionListGUI::STYLE_EMPH);
        //$this->toolbar->addDropDown($this->language->txt("cntr_add_new_item"), $adv->getHTML());
        $this->ui->mainTemplate()->setVariable("SELECT_OBJTYPE_REPOS", $adv->getHTML());
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
            $talks = $this->repository->findByEmployeesAndOwner($users, $this->currentUser->getId());
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
