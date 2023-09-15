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

use ILIAS\DI\LoggingServices;
use ILIAS\Skill\Service\SkillService;
use ILIAS\Test\InternalRequestService;

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

    private ilTestSession $testSession;
    private ilTestObjectiveOrientedContainer $objectiveOrientedContainer;
    private ilAssQuestionList $questionList;

    protected bool $noSkillProfileOptionEnabled = false;
    protected array $availableSkillProfiles = [];
    protected array $availableSkills = [];
    protected ?ilTestPassesSelector $testPassesSelector = null;

    public function __construct(
        private ilObjTest $test_obj,
        private ilCtrl $ctrl,
        private ilGlobalTemplateInterface $tpl,
        private ilLanguage $lng,
        private ilDBInterface $db,
        private LoggingServices $logging_services,
        private SkillService $skills_service,
        private InternalRequestService $testrequest
    ) {
    }

    /**
     * @return ilAssQuestionList
     */
    public function getQuestionList(): ilAssQuestionList
    {
        return $this->questionList;
    }

    /**
     * @param ilAssQuestionList $questionList
     */
    public function setQuestionList($questionList)
    {
        $this->questionList = $questionList;
    }

    /**
     * @return ilTestObjectiveOrientedContainer
     */
    public function getObjectiveOrientedContainer(): ilTestObjectiveOrientedContainer
    {
        return $this->objectiveOrientedContainer;
    }

    /**
     * @param ilTestObjectiveOrientedContainer $objectiveOrientedContainer
     */
    public function setObjectiveOrientedContainer($objectiveOrientedContainer)
    {
        $this->objectiveOrientedContainer = $objectiveOrientedContainer;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_SHOW) . 'Cmd';

        $this->$cmd();
    }

    private function isAccessDenied(): bool
    {
        return false;
    }

    protected function init(bool $skill_profile_enabled): void
    {
        $this->testPassesSelector = new ilTestPassesSelector($this->db, $this->test_obj);
        $this->testPassesSelector->setActiveId($this->testSession->getActiveId());
        $this->testPassesSelector->setLastFinishedPass($this->testSession->getLastFinishedPass());

        $assSettings = new ilSetting('assessment');
        $skillEvaluation = new ilTestSkillEvaluation(
            $this->db,
            $this->logging_services,
            $this->test_obj->getTestId(),
            $this->test_obj->getRefId(),
            $this->skills_service->profile(),
            $this->skills_service->personal()
        );

        $skillEvaluation->setUserId($this->getTestSession()->getUserId());
        $skillEvaluation->setActiveId($this->getTestSession()->getActiveId());

        $skillEvaluation->setNumRequiredBookingsForSkillTriggering((int) $assSettings->get(
            'ass_skl_trig_num_answ_barrier',
            ilObjAssessmentFolder::DEFAULT_SKL_TRIG_NUM_ANSWERS_BARRIER
        ));

        $skillEvaluation->init($this->getQuestionList());

        $availableSkillProfiles = $skillEvaluation->getAssignedSkillMatchingSkillProfiles();
        $this->setNoSkillProfileOptionEnabled(
            $skillEvaluation->noProfileMatchingAssignedSkillExists($availableSkillProfiles)
        );
        $this->setAvailableSkillProfiles($availableSkillProfiles);

        // should be reportedPasses - yes - indeed, skill level status will not respect - avoid confuse here
        $evaluationPasses = $this->testPassesSelector->getExistingPasses();

        $availableSkills = [];

        foreach ($evaluationPasses as $evalPass) {
            $testResults = $this->test_obj->getTestResult($this->getTestSession()->getActiveId(), $evalPass, true);

            $skillEvaluation->setPass($evalPass);
            $skillEvaluation->evaluate($testResults);

            if ($skill_profile_enabled && self::INVOLVE_SKILLS_BELOW_NUM_ANSWERS_BARRIER_FOR_GAP_ANALASYS) {
                $skills = $skillEvaluation->getSkillsInvolvedByAssignment();
            } else {
                $skills = $skillEvaluation->getSkillsMatchingNumAnswersBarrier();
            }

            $availableSkills = array_merge($availableSkills, $skills);
        }

        $this->setAvailableSkills(array_values($availableSkills));
    }

    private function showCmd()
    {
        $selected_skill_profile = $this->testrequest->int(self::SKILL_PROFILE_PARAM);
        $skill_profile_enabled = $selected_skill_profile !== null;

        $this->init($skill_profile_enabled);

        $evaluation_toolbar_gui = $this->buildEvaluationToolbarGUI($selected_skill_profile);

        $personal_skills_gui = $this->buildPersonalSkillsGUI(
            $this->getTestSession()->getUserId(),
            $evaluation_toolbar_gui->getSelectedEvaluationMode(),
            $this->getAvailableSkills()
        );

        $this->tpl->setContent(
            $this->ctrl->getHTML($evaluation_toolbar_gui) . $this->ctrl->getHTML($personal_skills_gui)
        );
    }

    private function buildEvaluationToolbarGUI(int $selectedSkillProfileId): ilTestSkillEvaluationToolbarGUI
    {
        if (!$this->isNoSkillProfileOptionEnabled() && !$selectedSkillProfileId) {
            $selectedSkillProfileId = key($this->getAvailableSkillProfiles()) ?? 0;
        }

        $gui = new ilTestSkillEvaluationToolbarGUI($this->ctrl, $this->lng);

        $gui->setAvailableSkillProfiles($this->getAvailableSkillProfiles());
        $gui->setNoSkillProfileOptionEnabled($this->isNoSkillProfileOptionEnabled());
        $gui->setSelectedEvaluationMode($selectedSkillProfileId);

        $gui->build();

        return $gui;
    }

    private function buildPersonalSkillsGUI(
        int $usrId,
        ?int $selectedSkillProfileId,
        array $availableSkills
    ): ilTestPersonalSkillsGUI {
        $gui = new ilTestPersonalSkillsGUI($this->lng, $this->test_obj->getId());

        $gui->setAvailableSkills($availableSkills);
        $gui->setSelectedSkillProfile($selectedSkillProfileId);
        $gui->setUsrId($usrId);

        return $gui;
    }

    public function setTestSession(ilTestSession $testSession): void
    {
        $this->testSession = $testSession;
    }

    public function getTestSession(): ilTestSession
    {
        return $this->testSession;
    }

    public function isNoSkillProfileOptionEnabled(): bool
    {
        return $this->noSkillProfileOptionEnabled;
    }

    public function setNoSkillProfileOptionEnabled(bool $noSkillProfileOptionEnabled): void
    {
        $this->noSkillProfileOptionEnabled = $noSkillProfileOptionEnabled;
    }

    public function getAvailableSkillProfiles(): array
    {
        return $this->availableSkillProfiles;
    }

    public function setAvailableSkillProfiles(array $availableSkillProfiles): void
    {
        $this->availableSkillProfiles = $availableSkillProfiles;
    }

    public function getAvailableSkills(): array
    {
        return $this->availableSkills;
    }

    public function setAvailableSkills(array $availableSkills): void
    {
        $this->availableSkills = $availableSkills;
    }
}
