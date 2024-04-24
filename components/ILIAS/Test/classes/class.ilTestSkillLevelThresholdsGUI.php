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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 *
 * @ilCtrl_Calls ilTestSkillLevelThresholdsGUI: ilTestSkillLevelThresholdsTableGUI
 */
class ilTestSkillLevelThresholdsGUI
{
    public const CMD_SHOW_SKILL_THRESHOLDS = 'showSkillThresholds';
    public const CMD_SAVE_SKILL_THRESHOLDS = 'saveSkillThresholds';

    private int $question_container_id;
    private bool $questionAssignmentColumnsEnabled = false;

    public function __construct(
        private readonly ilCtrlInterface $ctrl,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilLanguage $lng,
        private readonly ilDBInterface $db,
        private readonly int $test_id
    ) {
    }

    /**
     * @return int
     */
    public function getQuestionContainerId(): int
    {
        return $this->question_container_id;
    }

    public function setQuestionContainerId(int $question_container_id): void
    {
        $this->question_container_id = $question_container_id;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd('show') . 'Cmd';

        $this->$cmd();
    }

    public function setQuestionAssignmentColumnsEnabled(bool $question_assignment_columns_enabled): void
    {
        $this->question_assignment_columns_enabled = $question_assignment_columns_enabled;
    }

    /**
     * @return bool
     */
    public function areQuestionAssignmentColumnsEnabled(): bool
    {
        return $this->question_assignment_columns_enabled;
    }

    /**
     * @return int
     */
    public function getTestId(): int
    {
        return $this->test_id;
    }

    private function saveSkillThresholdsCmd(): void
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $assignment_list = $this->buildSkillQuestionAssignmentList();
            $assignment_list->loadFromDb();

            $valid = true;

            $table = $this->getPopulatedTable();
            $elements = $table->getInputElements((array) ($_POST['rendered'] ?? []));
            foreach ($elements as $elm) {
                if (!$elm->checkInput()) {
                    $valid = false;
                }

                $elm->setValue($_POST[$elm->getPostVar()]);
            }

            if (!$valid) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
                $this->showSkillThresholdsCmd($table);
                return;
            }

            $threshold = [];
            foreach ($elements as $elm) {
                $key = $elm->getPostVar();
                $value = $_POST[$key];
                $matches = null;
                if (preg_match('/^threshold_(\d+?):(\d+?)_(\d+?)$/', $key, $matches) && is_array($matches)) {
                    $threshold[$matches[1] . ':' . $matches[2]][$matches[3]] = $value;
                }
            }

            /** @var $skill_level_thresholds ilTestSkillLevelThreshold[] */
            $skill_level_thresholds = [];

            foreach ($assignment_list->getUniqueAssignedSkills() as $data) {
                $skill = $data['skill'];
                $skillKey = $data['skill_base_id'] . ':' . $data['skill_tref_id'];
                $levels = $skill->getLevelData();

                $thresholds_by_level = [];

                foreach ($levels as $level) {
                    if (isset($threshold[$skillKey]) && isset($threshold[$skillKey][$level['id']])) {
                        $skill_level_threshold = new ilTestSkillLevelThreshold($this->db);

                        $skill_level_threshold->setTestId($this->getTestId());
                        $skill_level_threshold->setSkillBaseId($data['skill_base_id']);
                        $skill_level_threshold->setSkillTrefId($data['skill_tref_id']);
                        $skill_level_threshold->setSkillLevelId($level['id']);

                        $skill_level_threshold->setThreshold($threshold[$skillKey][$level['id']]);
                        $skill_level_thresholds[] = $skill_level_threshold;
                        $thresholds_by_level[] = $threshold[$skillKey][$level['id']];
                    }
                }

                $sorted_thresholds_by_level = $thresholds_by_level = array_values($thresholds_by_level);
                sort($sorted_thresholds_by_level);
                if (
                    $sorted_thresholds_by_level != $thresholds_by_level ||
                    count($thresholds_by_level) != count(array_unique($thresholds_by_level))
                ) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('ass_competence_respect_level_ordering'));
                    $this->showSkillThresholdsCmd($table);
                    return;
                }
            }

            foreach ($skill_level_thresholds as $skill_level_threshold) {
                $skill_level_threshold->saveToDb();
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_msg_skl_lvl_thresholds_saved'), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_SKILL_THRESHOLDS);
    }

    private function showSkillThresholdsCmd(ilTestSkillLevelThresholdsTableGUI $table = null): void
    {
        if (null === $table) {
            $table = $this->getPopulatedTable();
        }

        $this->tpl->setContent($this->ctrl->getHTML($table));
    }

    protected function getPopulatedTable(): ilTestSkillLevelThresholdsTableGUI
    {
        $table = $this->buildTableGUI();

        $skill_level_threshold_list = $this->buildSkillLevelThresholdList();
        $skill_level_threshold_list->loadFromDb();
        $table->setSkillLevelThresholdList($skill_level_threshold_list);

        $assignment_list = $this->buildSkillQuestionAssignmentList();
        $assignment_list->loadFromDb();

        $table->setData($table->completeCompetenceTitles(
            $assignment_list->getUniqueAssignedSkills()
        ));
        return $table;
    }

    private function buildTableGUI(): ilTestSkillLevelThresholdsTableGUI
    {
        $table = new ilTestSkillLevelThresholdsTableGUI(
            $this,
            $this->getTestId(),
            self::CMD_SHOW_SKILL_THRESHOLDS,
            $this->ctrl,
            $this->lng
        );
        $table->setQuestionAssignmentColumnsEnabled($this->areQuestionAssignmentColumnsEnabled());
        $table->initColumns();

        return $table;
    }

    private function buildSkillQuestionAssignmentList(): ilAssQuestionSkillAssignmentList
    {
        $assignment_list = new ilAssQuestionSkillAssignmentList($this->db);
        $assignment_list->setParentObjId($this->getQuestionContainerId());

        return $assignment_list;
    }

    private function buildSkillLevelThresholdList(): ilTestSkillLevelThresholdList
    {
        $threshold_list = new ilTestSkillLevelThresholdList($this->db);
        $threshold_list->setTestId($this->getTestId());

        return $threshold_list;
    }
}
