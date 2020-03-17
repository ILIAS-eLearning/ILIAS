<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 *
 * @ilCtrl_Calls ilTestSkillLevelThresholdsGUI: ilTestSkillLevelThresholdsTableGUI
 */
class ilTestSkillLevelThresholdsGUI
{
    const CMD_SHOW_SKILL_THRESHOLDS = 'showSkillThresholds';
    const CMD_SAVE_SKILL_THRESHOLDS = 'saveSkillThresholds';
    /**
     * @var ilCtrl
     */
    private $ctrl;

    /**
     * @var ilTemplate
     */
    private $tpl;

    /**
     * @var ilLanguage
     */
    private $lng;

    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * @var int
     */
    private $testId;

    /**
     * @var integer
     */
    private $questionContainerId;

    private $questionAssignmentColumnsEnabled;

    public function __construct(ilCtrl $ctrl, ilTemplate $tpl, ilLanguage $lng, ilDBInterface $db, $testId)
    {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->db = $db;
        $this->testId = $testId;
        $this->questionAssignmentColumnsEnabled = false;
    }

    /**
     * @return int
     */
    public function getQuestionContainerId()
    {
        return $this->questionContainerId;
    }

    /**
     * @param int $questionContainerId
     */
    public function setQuestionContainerId($questionContainerId)
    {
        $this->questionContainerId = $questionContainerId;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd('show') . 'Cmd';

        $this->$cmd();
    }

    /**
     * @param boolean $questionAssignmentColumnsEnabled
     */
    public function setQuestionAssignmentColumnsEnabled($questionAssignmentColumnsEnabled)
    {
        $this->questionAssignmentColumnsEnabled = $questionAssignmentColumnsEnabled;
    }

    /**
     * @return bool
     */
    public function areQuestionAssignmentColumnsEnabled()
    {
        return $this->questionAssignmentColumnsEnabled;
    }

    /**
     * @return int
     */
    public function getTestId()
    {
        return $this->testId;
    }

    private function saveSkillThresholdsCmd()
    {
        require_once 'Modules/Test/classes/class.ilTestSkillLevelThreshold.php';

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $assignmentList = $this->buildSkillQuestionAssignmentList();
            $assignmentList->loadFromDb();

            $valid = true;

            $table = $this->getPopulatedTable();
            $renderedElements = [];
            if (isset($_POST['rendered']) && is_array($_POST['rendered'])) {
                $renderedElements = $_POST['rendered'];
            }
            $elements = $table->getInputElements($renderedElements);
            foreach ($elements as $elm) {
                if (!$elm->checkInput()) {
                    $valid = false;
                }

                $elm->setValueByArray($_POST);
            }

            if (!$valid) {
                ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
                return $this->showSkillThresholdsCmd($table);
            }

            $threshold = array();
            foreach ($_POST as $key => $value) {
                $matches = null;
                if (preg_match('/^threshold_(\d+?):(\d+?)_(\d+?)$/', $key, $matches) && is_array($matches)) {
                    $threshold[$matches[1] . ':' . $matches[2]][$matches[3]] = $value;
                }
            }

            /** @var $skillLevelThresholds ilTestSkillLevelThreshold[] */
            $skillLevelThresholds = array();

            foreach ($assignmentList->getUniqueAssignedSkills() as $data) {
                $skill = $data['skill'];
                $skillKey = $data['skill_base_id'] . ':' . $data['skill_tref_id'];
                $levels = $skill->getLevelData();

                $thresholds_by_level = array();

                foreach ($levels as $level) {
                    if (isset($threshold[$skillKey]) && isset($threshold[$skillKey][$level['id']])) {
                        $skillLevelThreshold = new ilTestSkillLevelThreshold($this->db);

                        $skillLevelThreshold->setTestId($this->getTestId());
                        $skillLevelThreshold->setSkillBaseId($data['skill_base_id']);
                        $skillLevelThreshold->setSkillTrefId($data['skill_tref_id']);
                        $skillLevelThreshold->setSkillLevelId($level['id']);

                        $skillLevelThreshold->setThreshold($threshold[$skillKey][$level['id']]);
                        $skillLevelThresholds[] = $skillLevelThreshold;
                        $thresholds_by_level[] = $threshold[$skillKey][$level['id']];
                    }
                }

                $sorted_thresholds_by_level = $thresholds_by_level = array_values($thresholds_by_level);
                sort($sorted_thresholds_by_level);
                if (
                    $sorted_thresholds_by_level != $thresholds_by_level ||
                    count($thresholds_by_level) != count(array_unique($thresholds_by_level))
                ) {
                    ilUtil::sendFailure($this->lng->txt('ass_competence_respect_level_ordering'));
                    return $this->showSkillThresholdsCmd($table);
                }
            }

            foreach ($skillLevelThresholds as $skillLevelThreshold) {
                $skillLevelThreshold->saveToDb();
            }

            ilUtil::sendSuccess($this->lng->txt('tst_msg_skl_lvl_thresholds_saved'), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_SKILL_THRESHOLDS);
    }

    /**
     * @param ilTestSkillLevelThresholdsTableGUI|null $table
     */
    private function showSkillThresholdsCmd(ilTestSkillLevelThresholdsTableGUI $table = null)
    {
        if (null === $table) {
            $table = $this->getPopulatedTable();
        }

        $this->tpl->setContent($this->ctrl->getHTML($table));
    }

    /**
     * @return ilTestSkillLevelThresholdsTableGUI
     */
    protected function getPopulatedTable()
    {
        $table = $this->buildTableGUI();

        $skillLevelThresholdList = $this->buildSkillLevelThresholdList();
        $skillLevelThresholdList->loadFromDb();
        $table->setSkillLevelThresholdList($skillLevelThresholdList);

        $assignmentList = $this->buildSkillQuestionAssignmentList();
        $assignmentList->loadFromDb();

        $table->setData($table->completeCompetenceTitles(
            $assignmentList->getUniqueAssignedSkills()
        ));
        return $table;
    }

    private function buildTableGUI()
    {
        require_once 'Modules/Test/classes/tables/class.ilTestSkillLevelThresholdsTableGUI.php';
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

    private function buildSkillQuestionAssignmentList()
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($this->getQuestionContainerId());

        return $assignmentList;
    }

    private function buildSkillLevelThresholdList()
    {
        require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdList.php';
        $thresholdList = new ilTestSkillLevelThresholdList($this->db);
        $thresholdList->setTestId($this->getTestId());

        return $thresholdList;
    }
}
