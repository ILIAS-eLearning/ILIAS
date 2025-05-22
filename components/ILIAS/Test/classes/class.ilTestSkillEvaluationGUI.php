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

use ILIAS\Skill\Service\SkillService;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\Logging\TestLogger;

/**
 * User interface which displays the competences which a learner has shown in a
 * test.
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id: class.ilTestSkillGUI.php 46688 2013-12-09 15:23:17Z bheyser $
 *
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilTestSkillEvaluationGUI: ilTestSkillEvaluationToolbarGUI
 * @ilCtrl_Calls ilTestSkillEvaluationGUI: ilTestPersonalSkillsGUI
 */
class ilTestSkillEvaluationGUI
{
    public const INVOLVE_SKILLS_BELOW_NUM_ANSWERS_BARRIER_FOR_GAP_ANALASYS = false;

    public const SKILL_PROFILE_PARAM = 'skill_profile';
    public const CMD_SHOW = 'show';

    private ilTestSession $test_session;
    private ilTestObjectiveOrientedContainer $objective_oriented_container;
    private ilAssQuestionList $question_list;

    protected bool $no_skill_profile_option_enabled = false;
    protected array $available_skill_profiles = [];
    protected array $available_skills = [];
    protected ?ilTestPassesSelector $test_passes_selector = null;

    public function __construct(
        private readonly ilObjTest $test_obj,
        private readonly ilCtrlInterface $ctrl,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilLanguage $lng,
        private readonly ilDBInterface $db,
        private readonly TestLogger $logger,
        private readonly SkillService $skills_service,
        private readonly RequestDataCollector $testrequest
    ) {
    }

    public function getQuestionList(): ilAssQuestionList
    {
        return $this->question_list;
    }

    public function setQuestionList(ilAssQuestionList $question_list): void
    {
        $this->question_list = $question_list;
    }

    public function getObjectiveOrientedContainer(): ilTestObjectiveOrientedContainer
    {
        return $this->objective_oriented_container;
    }

    public function setObjectiveOrientedContainer(
        ilTestObjectiveOrientedContainer $objective_oriented_container
    ): void {
        $this->objective_oriented_container = $objective_oriented_container;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd(self::CMD_SHOW) . 'Cmd';
        $this->$cmd();
    }

    protected function init(bool $skill_profile_enabled): void
    {
        $this->test_passes_selector = new ilTestPassesSelector($this->db, $this->test_obj);
        $this->test_passes_selector->setActiveId($this->test_session->getActiveId());
        $this->test_passes_selector->setLastFinishedPass($this->test_session->getLastFinishedPass());

        $skill_evaluation = new ilTestSkillEvaluation(
            $this->db,
            $this->logger,
            $this->test_obj->getTestId(),
            $this->test_obj->getRefId(),
            $this->skills_service->profile(),
            $this->skills_service->personal()
        );

        $skill_evaluation->setUserId($this->getTestSession()->getUserId());
        $skill_evaluation->setActiveId($this->getTestSession()->getActiveId());

        $skill_evaluation->setNumRequiredBookingsForSkillTriggering(
            $this->test_obj->getGlobalSettings()->getSkillTriggeringNumberOfAnswers()
        );

        $skill_evaluation->init($this->getQuestionList());

        $available_skill_profiles = $skill_evaluation->getAssignedSkillMatchingSkillProfiles();
        $this->setNoSkillProfileOptionEnabled(
            $skill_evaluation->noProfileMatchingAssignedSkillExists($available_skill_profiles)
        );
        $this->setAvailableSkillProfiles($available_skill_profiles);

        // should be reportedPasses - yes - indeed, skill level status will not respect - avoid confuse here
        $evaluation_passes = $this->test_passes_selector->getExistingPasses();

        $available_skills = [];

        foreach ($evaluation_passes as $eval_pass) {
            $test_results = $this->test_obj->getTestResult($this->getTestSession()->getActiveId(), $eval_pass, true);

            $skill_evaluation->setPass($eval_pass);
            $skill_evaluation->evaluate($test_results);

            if ($skill_profile_enabled && self::INVOLVE_SKILLS_BELOW_NUM_ANSWERS_BARRIER_FOR_GAP_ANALASYS) {
                $skills = $skill_evaluation->getSkillsInvolvedByAssignment();
            } else {
                $skills = $skill_evaluation->getSkillsMatchingNumAnswersBarrier();
            }

            $available_skills = array_merge($available_skills, $skills);
        }

        $this->setAvailableSkills(array_values($available_skills));
    }

    private function showCmd(): void
    {
        $skill_profile_selected = $this->testrequest->isset(self::SKILL_PROFILE_PARAM);
        $selected_skill_profile = $this->testrequest->int(self::SKILL_PROFILE_PARAM);

        $this->init($skill_profile_selected);

        $personal_skills_gui = new ilPersonalSkillsGUI();
        $personal_skills_gui->setGapAnalysisActualStatusModePerObject(
            $this->test_obj->getId(),
            $this->lng->txt('tst_test_result')
        );
        $personal_skills_gui->setTriggerObjectsFilter([$this->test_obj->getId()]);
        $personal_skills_gui->setHistoryView(true);
        $personal_skills_gui->setProfileId($selected_skill_profile);

        $this->tpl->setContent(
            $this->buildEvaluationToolbarGUI($selected_skill_profile)->getHTML()
            . $personal_skills_gui->getGapAnalysisHTML(
                $this->getTestSession()->getUserId(),
                $this->available_skills
            )
        );
    }

    private function buildEvaluationToolbarGUI(
        int $selected_skill_profile_id
    ): ilTestSkillEvaluationToolbarGUI {
        if (!$this->no_skill_profile_option_enabled && $selected_skill_profile_id === null) {
            $selected_skill_profile_id = key($this->available_skill_profiles) ?? 0;
        }

        $gui = new ilTestSkillEvaluationToolbarGUI($this->ctrl, $this->lng);
        $gui->setAvailableSkillProfiles($this->available_skill_profiles);
        $gui->setNoSkillProfileOptionEnabled($this->no_skill_profile_option_enabled);
        $gui->setSelectedEvaluationMode($selected_skill_profile_id);
        $gui->build();
        return $gui;
    }

    public function setTestSession(ilTestSession $test_session): void
    {
        $this->test_session = $test_session;
    }

    public function getTestSession(): ilTestSession
    {
        return $this->test_session;
    }

    public function setNoSkillProfileOptionEnabled(bool $no_skill_profile_option_enabled): void
    {
        $this->no_skill_profile_option_enabled = $no_skill_profile_option_enabled;
    }

    public function setAvailableSkillProfiles(array $available_skill_profiles): void
    {
        $this->available_skill_profiles = $available_skill_profiles;
    }

    public function setAvailableSkills(array $available_skills): void
    {
        $this->available_skills = $available_skills;
    }
}
