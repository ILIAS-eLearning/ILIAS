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

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Presentation\TabsManager;
use ILIAS\Refinery\Factory as Refinery;

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
        private ilCtrlInterface $ctrl,
        private ilAccessHandler $access,
        private TabsManager $tabs_manager,
        private ilGlobalTemplateInterface $tpl,
        private ilLanguage $lng,
        private Refinery $refinery,
        private ilDBInterface $db,
        private TestLogger $logger,
        private ilTree $tree,
        private ilComponentRepository $component_repository,
        private ilObjTest $test_obj,
        private GeneralQuestionPropertiesRepository $questionrepository,
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

        switch ($nextClass) {
            case 'ilassquestionskillassignmentsgui':
                $this->tabs_manager->getQuestionsSubTabs();
                $this->tabs_manager->activateTab(TabsManager::TAB_ID_QUESTIONS);
                $this->tabs_manager->activateSubTab(TabsManager::SETTINGS_SUBTAB_ID_ASSIGN_SKILLS_TO_QUESTIONS);

                $questionContainerId = $this->test_obj->getId();

                $questionList = new ilAssQuestionList($this->db, $this->lng, $this->refinery, $this->component_repository);
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
                $this->tabs_manager->getSettingsSubTabs();
                $this->tabs_manager->activateTab(TabsManager::TAB_ID_SETTINGS);
                $this->tabs_manager->activateSubTab(TabsManager::SETTINGS_SUBTAB_ID_ASSIGN_SKILL_TRESHOLDS);

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
            $this->logger,
            $this->component_repository,
            $this->test_obj,
            $this->questionrepository
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
