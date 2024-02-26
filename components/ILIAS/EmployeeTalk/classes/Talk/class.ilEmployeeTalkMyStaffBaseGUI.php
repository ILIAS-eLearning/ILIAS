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
use ILIAS\EmployeeTalk\Talk\Repository\EmployeeTalkRepository;
use ILIAS\EmployeeTalk\Talk\Repository\IliasDBEmployeeTalkRepository;
use ILIAS\EmployeeTalk\Talk\DAO\EmployeeTalk;
use ILIAS\DI\UIServices;

abstract class ilEmployeeTalkMyStaffBaseGUI implements ControlFlowCommandHandler
{
    protected UIServices $ui;
    protected ilLanguage $language;
    protected ilTabsGUI $tabs;
    protected ilMyStaffAccess $access;
    protected ilCtrl $ctrl;
    protected ilObjUser $current_user;
    protected EmployeeTalkRepository $repository;
    protected ilObjEmployeeTalkAccess $talk_access;

    public function __construct()
    {
        global $DIC;

        $DIC->language()->loadLanguageModule('etal');
        $DIC->language()->loadLanguageModule('orgu');
        $this->language = $DIC->language();
        $this->talk_access = new ilObjEmployeeTalkAccess();
        $this->access = ilMyStaffAccess::getInstance();

        $this->tabs = $DIC->tabs();
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->current_user = $DIC->user();
        $this->repository = new IliasDBEmployeeTalkRepository($DIC->database());
    }

    final public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();
        $command = $this->ctrl->getCmd(ControlFlowCommand::DEFAULT);
        $link_to_this = $this->ctrl->getLinkTargetByClass($this->getClassPath());

        switch ($next_class) {
            case strtolower(ilObjEmployeeTalkSeriesGUI::class):
                $gui = new ilObjEmployeeTalkSeriesGUI();
                $gui->setLinkToParentGUI($link_to_this);
                $this->ctrl->forwardCommand($gui);
                break;
            case strtolower(ilObjEmployeeTalkGUI::class):
                $gui = new ilObjEmployeeTalkGUI();
                if ($this->access->hasCurrentUserAccessToTalks()) {
                    $this->tabs->setBackTarget(
                        $this->language->txt('etal_talks'),
                        $this->ctrl->getLinkTarget($this, ControlFlowCommand::INDEX)
                    );
                }
                $gui->setLinkToParentGUI($link_to_this);
                $this->ctrl->forwardCommand($gui);
                break;
            case strtolower(ilFormPropertyDispatchGUI::class):
                $this->ctrl->setReturn($this, ControlFlowCommand::INDEX);
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
                    case ControlFlowCommand::INDEX:
                    default:
                        $this->view();
                }
        }
    }

    abstract protected function hasCurrentUserAccess(): bool;

    private function checkAccessOrFail(): void
    {
        if (!$this->hasCurrentUserAccess()) {
            $this->ui->mainTemplate()->setOnScreenMessage(
                'failure',
                $this->language->txt("permission_denied"),
                true
            );
            $this->ctrl->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    /**
     * @return string[]
     */
    abstract public function getClassPath(): array;

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
        $this->loadHeader();
        $this->ui->mainTemplate()->setContent($this->loadTable()->getHTML());
    }

    abstract protected function loadHeader(): void;

    abstract protected function loadTabs(): void;

    private function loadActionBar(): void
    {
        if (!$this->talk_access->canCreate()) {
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
            $this->ctrl->setParameterByClass($talk_class, 'new_type', ilObjEmployeeTalkSeries::TYPE);
            $this->ctrl->setParameterByClass($talk_class, 'template', array_pop($refId));
            $this->ctrl->setParameterByClass($talk_class, 'ref_id', ilObjTalkTemplateAdministration::getRootRefId());
            $url = $this->ctrl->getLinkTargetByClass($talk_class, ControlFlowCommand::CREATE);
            $this->ctrl->clearParametersByClass($talk_class);

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

    /**
     * @return EmployeeTalk[]
     */
    abstract protected function loadTalkData(): array;

    private function loadTable(): ilEmployeeTalkTableGUI
    {
        $table = new ilEmployeeTalkTableGUI($this, ControlFlowCommand::DEFAULT);
        $table->setTalkData($this->loadTalkData());

        return $table;
    }
}
