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

    public const CMD_SHOW = 'show';
    /**
     * @var ilCtrl
     */
    private $ctrl;

    /**
     * @var ilGlobalTemplateInterface
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
     * @var ilObjTest
     */
    protected $testOBJ;

    /**
     * @var ilTestSession
     */
    private $testSession;

    /**
     * @var ilTestObjectiveOrientedContainer
     */
    private $objectiveOrientedContainer;

    /**
     * @var ilAssQuestionList
     */
    private $questionList;

    protected $noSkillProfileOptionEnabled = false;
    protected $availableSkillProfiles = array();
    protected $availableSkills = array();

    /**
     * @var ilTestPassesSelector
     */
    protected $testPassesSelector = null;

    public function __construct(ilCtrl $ctrl, ilTabsGUI $tabs, ilGlobalTemplateInterface $tpl, ilLanguage $lng, ilDBInterface $db, ilObjTest $testOBJ)
    {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->db = $db;
        $this->testOBJ = $testOBJ;
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

        $this->manageTabs($cmd);

        $this->$cmd();
    }

    private function isAccessDenied(): bool
    {
        return false;
    }

    private function manageTabs($cmd)
    {
        #$this->tabs->clearTargets();
#
#		$this->tabs->setBackTarget(
#			$this->lng->txt('tst_results_back_introduction'),
#			$this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'infoScreen')
#		);

#		if( $this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired() )
#		{
#			require_once 'Services/Link/classes/class.ilLink.php';
#			$courseLink = ilLink::_getLink($this->getObjectiveOrientedContainer()->getRefId());
#			$this->tabs->setBack2Target($this->lng->txt('back_to_objective_container'), $courseLink);
#		}
    }

    protected function init($skillProfileEnabled)
    {
        require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
        $this->testPassesSelector = new ilTestPassesSelector($this->db, $this->testOBJ);
        $this->testPassesSelector->setActiveId($this->testSession->getActiveId());
        $this->testPassesSelector->setLastFinishedPass($this->testSession->getLastFinishedPass());

        $assSettings = new ilSetting('assessment');
        require_once 'Modules/Test/classes/class.ilTestSkillEvaluation.php';
        $skillEvaluation = new ilTestSkillEvaluation(
            $this->db,
            $this->testOBJ->getTestId(),
            $this->testOBJ->getRefId()
        );

        $skillEvaluation->setUserId($this->getTestSession()->getUserId());
        $skillEvaluation->setActiveId($this->getTestSession()->getActiveId());

        $skillEvaluation->setNumRequiredBookingsForSkillTriggering($assSettings->get(
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

        $availableSkills = array();

        foreach ($evaluationPasses as $evalPass) {
            $testResults = $this->testOBJ->getTestResult($this->getTestSession()->getActiveId(), $evalPass, true);

            $skillEvaluation->setPass($evalPass);
            $skillEvaluation->evaluate($testResults);

            if ($skillProfileEnabled && self::INVOLVE_SKILLS_BELOW_NUM_ANSWERS_BARRIER_FOR_GAP_ANALASYS) {
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
        //ilUtil::sendInfo($this->lng->txt('tst_skl_res_interpretation_hint_msg'));

        $selectedSkillProfile = ilTestSkillEvaluationToolbarGUI::fetchSkillProfileParam($_POST);

        $this->init($selectedSkillProfile);

        $evaluationToolbarGUI = $this->buildEvaluationToolbarGUI($selectedSkillProfile);

        $personalSkillsGUI = $this->buildPersonalSkillsGUI(
            $this->getTestSession()->getUserId(),
            $evaluationToolbarGUI->getSelectedEvaluationMode(),
            $this->getAvailableSkills()
        );

        $this->tpl->setContent(
            $this->ctrl->getHTML($evaluationToolbarGUI) . $this->ctrl->getHTML($personalSkillsGUI)
        );
    }

    private function buildEvaluationToolbarGUI($selectedSkillProfileId): ilTestSkillEvaluationToolbarGUI
    {
        if (!$this->isNoSkillProfileOptionEnabled() && !$selectedSkillProfileId) {
            $selectedSkillProfileId = key($this->getAvailableSkillProfiles());
        }

        $gui = new ilTestSkillEvaluationToolbarGUI($this->ctrl, $this->lng, $this, self::CMD_SHOW);

        $gui->setAvailableSkillProfiles($this->getAvailableSkillProfiles());
        $gui->setNoSkillProfileOptionEnabled($this->isNoSkillProfileOptionEnabled());
        $gui->setSelectedEvaluationMode($selectedSkillProfileId);

        $gui->build();

        return $gui;
    }

    private function buildPersonalSkillsGUI($usrId, $selectedSkillProfileId, $availableSkills): ilTestPersonalSkillsGUI
    {
        $gui = new ilTestPersonalSkillsGUI($this->lng, $this->testOBJ->getId());

        $gui->setAvailableSkills($availableSkills);
        $gui->setSelectedSkillProfile($selectedSkillProfileId);

        //$gui->setReachedSkillLevels($reachedSkillLevels);
        $gui->setUsrId($usrId);

        return $gui;
    }

    /**
     * @param \ilTestSession $testSession
     */
    public function setTestSession($testSession)
    {
        $this->testSession = $testSession;
    }

    /**
     * @return \ilTestSession
     */
    public function getTestSession(): ilTestSession
    {
        return $this->testSession;
    }

    /**
     * @return boolean
     */
    public function isNoSkillProfileOptionEnabled(): bool
    {
        return $this->noSkillProfileOptionEnabled;
    }

    /**
     * @param boolean $noSkillProfileOptionEnabled
     */
    public function setNoSkillProfileOptionEnabled($noSkillProfileOptionEnabled)
    {
        $this->noSkillProfileOptionEnabled = $noSkillProfileOptionEnabled;
    }

    /**
     * @return array
     */
    public function getAvailableSkillProfiles(): array
    {
        return $this->availableSkillProfiles;
    }

    /**
     * @param array $availableSkillProfiles
     */
    public function setAvailableSkillProfiles($availableSkillProfiles)
    {
        $this->availableSkillProfiles = $availableSkillProfiles;
    }

    /**
     * @return array
     */
    public function getAvailableSkills(): array
    {
        return $this->availableSkills;
    }

    /**
     * @param array $availableSkills
     */
    public function setAvailableSkills($availableSkills)
    {
        $this->availableSkills = $availableSkills;
    }
}
