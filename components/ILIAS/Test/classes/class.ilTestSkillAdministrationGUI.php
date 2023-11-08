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

use ILIAS\TestQuestionPool\QuestionInfoService;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilTestSkillAdministrationGUI: ilAssQuestionSkillAssignmentsGUI
 * @ilCtrl_Calls ilTestSkillAdministrationGUI: ilTestSkillLevelThresholdsGUI
 */
class ilTestSkillAdministrationGUI
{
    public function __construct(
        private ilCtrl $ctrl,
        private ilAccessHandler $access,
        private ilTabsGUI $tabs,
        private ilGlobalTemplateInterface $tpl,
        private ilLanguage $lng,
        private ilDBInterface $db,
        private ilLogger $log,
        private ilTree $tree,
        private ilComponentRepository $component_repository,
        private ilObjTest $test_obj,
        private QuestionInfoService $questioninfo,
        private int $ref_id
    ) {
    }

    public function executeCommand()
    {
        if ($this->isAccessDenied()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_permission"), true);
            $this->ctrl->setParameterByClass(ilObjTestGUI::class, 'ref_id', $this->ref_id);
            $this->ctrl->redirectByClass(ilObjTestGUI::class);
        }

        $nextClass = $this->ctrl->getNextClass();

        $this->manageTabs($nextClass);

        switch ($nextClass) {
            case 'ilassquestionskillassignmentsgui':

                $questionContainerId = $this->test_obj->getId();

                $questionList = new ilAssQuestionList($this->db, $this->lng, $this->component_repository);
                $questionList->setParentObjId($questionContainerId);
                $questionList->setQuestionInstanceTypeFilter($this->getRequiredQuestionInstanceTypeFilter());
                $questionList->load();

                $gui = new ilAssQuestionSkillAssignmentsGUI($this->ctrl, $this->access, $this->tpl, $this->lng, $this->db);
                $gui->setAssignmentEditingEnabled($this->isAssignmentEditingRequired());
                $gui->setQuestionContainerId($questionContainerId);
                $gui->setQuestionList($questionList);

                if ($this->test_obj->isFixedTest()) {
                    $gui->setQuestionOrderSequence($this->test_obj->getQuestions());
                } else {
                    $gui->setAssignmentConfigurationHintMessage($this->buildAssignmentConfigurationInPoolHintMessage());
                }

                $this->ctrl->forwardCommand($gui);

                break;

            case 'iltestskilllevelthresholdsgui':

                $gui = new ilTestSkillLevelThresholdsGUI($this->ctrl, $this->tpl, $this->lng, $this->db, $this->test_obj->getTestId());
                $gui->setQuestionAssignmentColumnsEnabled(!$this->test_obj->isRandomTest());
                $gui->setQuestionContainerId($this->test_obj->getId());
                $this->ctrl->forwardCommand($gui);
                break;
        }
    }

    private function isAssignmentEditingRequired(): bool
    {
        if (!$this->test_obj->isFixedTest()) {
            return false;
        }

        if ($this->test_obj->participantDataExist()) {
            return false;
        }

        return true;
    }

    public function manageTabs($activeSubTabId)
    {
        $link = $this->ctrl->getLinkTargetByClass(
            'ilAssQuestionSkillAssignmentsGUI',
            ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
        );
        $this->tabs->addSubTab(
            'ilassquestionskillassignmentsgui',
            $this->lng->txt('qpl_skl_sub_tab_quest_assign'),
            $link
        );

        $link = $this->ctrl->getLinkTargetByClass(
            'ilTestSkillLevelThresholdsGUI',
            ilTestSkillLevelThresholdsGUI::CMD_SHOW_SKILL_THRESHOLDS
        );
        $this->tabs->addSubTab(
            'iltestskilllevelthresholdsgui',
            $this->lng->txt('tst_skl_sub_tab_thresholds'),
            $link
        );

        $this->tabs->activateTab('tst_tab_competences');
        $this->tabs->activateSubTab($activeSubTabId);
    }

    private function isAccessDenied(): bool
    {
        if (!$this->test_obj->isSkillServiceEnabled()) {
            return true;
        }

        if (!ilObjTest::isSkillManagementGloballyActivated()) {
            return true;
        }

        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            return true;
        }

        return false;
    }

    private function getRequiredQuestionInstanceTypeFilter(): ?string
    {
        if ($this->test_obj->isRandomTest()) {
            return ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES;
        }

        return null;
    }

    private function buildAssignmentConfigurationInPoolHintMessage(): string
    {
        $question_set_config_factory = new ilTestQuestionSetConfigFactory(
            $this->tree,
            $this->db,
            $this->lng,
            $this->log,
            $this->component_repository,
            $this->test_obj,
            $this->questioninfo
        );

        $question_set_config = $question_set_config_factory->getQuestionSetConfig();

        if ($this->test_obj->isRandomTest()) {
            $testMode = $this->lng->txt('tst_question_set_type_random');
            $poolLinks = $question_set_config->getCommaSeparatedSourceQuestionPoolLinks();

            return sprintf($this->lng->txt('tst_qst_skl_cfg_in_pool_hint_rndquestset'), $testMode, $poolLinks);
        }

        return '';
    }
}
