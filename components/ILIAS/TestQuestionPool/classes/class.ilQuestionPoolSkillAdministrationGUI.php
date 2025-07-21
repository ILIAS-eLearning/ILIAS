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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/TestQuestionPool
 *
 * @ilCtrl_Calls ilQuestionPoolSkillAdministrationGUI: ilAssQuestionSkillAssignmentsGUI, ilAssQuestionSkillUsagesGUI
 */
class ilQuestionPoolSkillAdministrationGUI
{
    public function __construct(
        private readonly ILIAS $ilias,
        private readonly ilCtrl $ctrl,
        private readonly Factory $ui_factory,
        private readonly Renderer $ui_renderer,
        private readonly GlobalHttpState $http_state,
        private readonly Refinery $refinery,
        private readonly ilAccessHandler $access,
        private readonly ilTabsGUI $tabs,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilLanguage $lng,
        private readonly ilDBInterface $db,
        private readonly ilComponentRepository $component_repository,
        private readonly ilObjQuestionPool $pool_obj,
        private readonly int $ref_id
    ) {
    }

    private function isAccessDenied(): bool
    {
        return
            !$this->pool_obj->isSkillServiceEnabled()
            || !ilObjQuestionPool::isSkillManagementGloballyActivated()
            || !$this->access->checkAccess('write', '', $this->ref_id);
    }

    public function manageTabs($activeSubTabId): void
    {
        $link = $this->ctrl->getLinkTargetByClass(
            ilAssQuestionSkillAssignmentsGUI::class,
            ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
        );
        $this->tabs->addSubTab(
            strtolower(ilAssQuestionSkillAssignmentsGUI::class),
            $this->lng->txt('qpl_skl_sub_tab_quest_assign'),
            $link
        );

        $link = $this->ctrl->getLinkTargetByClass(
            ilAssQuestionSkillUsagesGUI::class,
            ilAssQuestionSkillUsagesGUI::CMD_SHOW
        );
        $this->tabs->addSubTab(
            strtolower(ilAssQuestionSkillUsagesGUI::class),
            $this->lng->txt('qpl_skl_sub_tab_usages'),
            $link
        );

        $this->tabs->activateTab('qpl_tab_competences');
        $this->tabs->activateSubTab($activeSubTabId);
    }

    public function executeCommand(): void
    {
        if ($this->isAccessDenied()) {
            $this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
        }

        $nextClass = $this->ctrl->getNextClass();

        $this->manageTabs($nextClass);

        switch (strtolower($nextClass)) {
            case strtolower(ilAssQuestionSkillAssignmentsGUI::class):
                $questionList = new ilAssQuestionList($this->db, $this->lng, $this->refinery, $this->component_repository);
                $questionList->setParentObjId($this->pool_obj->getId());
                $questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_ORIGINALS);
                $questionList->load();

                $gui = new ilAssQuestionSkillAssignmentsGUI($this->ctrl, $this->access, $this->tpl, $this->lng, $this->db);
                $gui->setAssignmentEditingEnabled(true);
                $gui->setQuestionContainerId($this->pool_obj->getId());
                $gui->setQuestionList($questionList);

                $this->ctrl->forwardCommand($gui);

                break;

            case strtolower(ilAssQuestionSkillUsagesGUI::class):
                $gui = new ilAssQuestionSkillUsagesGUI(
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->http_state,
                    $this->lng,
                    $this->tpl,
                    $this->db,
                    $this->pool_obj->getId()
                );

                $this->ctrl->forwardCommand($gui);

                break;
        }
    }
}
