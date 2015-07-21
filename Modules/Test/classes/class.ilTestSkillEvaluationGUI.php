<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/toolbars/class.ilTestSkillEvaluationToolbarGUI.php';
require_once 'Modules/Test/classes/class.ilTestPersonalSkillsGUI.php';
require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';

/**
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
	const CMD_SHOW = 'show';
	/**
	 * @var ilCtrl
	 */
	private $ctrl;

	/**
	 * @var ilTabsGUI
	 */
	private $tabs;

	/**
	 * @var ilTemplate
	 */
	private $tpl;

	/**
	 * @var ilLanguage
	 */
	private $lng;

	/**
	 * @var ilDB
	 */
	private $db;

	/**
	 * @var int
	 */
	private $testId;

	/**
	 * @var ilTestSkillEvaluation
	 */
	private $skillEvaluation;

	/**
	 * @var ilTestSession
	 */
	private $testSession;

	/**
	 * @var array
	 */
	private $testResults;

	/**
	 * @var ilAssQuestionList
	 */
	private $questionList;

	/**
	 * @var int
	 */
	private $objectId;

	public function __construct(ilCtrl $ctrl, ilTabsGUI $tabs, ilTemplate $tpl, ilLanguage $lng, ilDB $db, $testId, $refId, $objectId)
	{
		$this->ctrl = $ctrl;
		$this->tabs = $tabs;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->db = $db;
		$this->testId = $testId;
		$this->objectId = $objectId;

		require_once 'Modules/Test/classes/class.ilTestSkillEvaluation.php';
		$this->skillEvaluation = new ilTestSkillEvaluation($this->db, $this->getTestId(), $refId);
	}

	/**
	 * @return ilAssQuestionList
	 */
	public function getQuestionList()
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

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd(self::CMD_SHOW) . 'Cmd';

		$this->manageTabs($cmd);

		$this->$cmd();
	}

	private function isAccessDenied()
	{
		return false;
	}

	private function manageTabs($cmd)
	{
		$this->tabs->clearTargets();

		$this->tabs->setBackTarget(
			$this->lng->txt('tst_results_back_introduction'),
			$this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'infoScreen')
		);
	}

	private function showCmd()
	{
		ilUtil::sendInfo($this->lng->txt('tst_skl_res_interpretation_hint_msg'));
		
		$selectedSkillProfile = ilTestSkillEvaluationToolbarGUI::fetchSkillProfileParam($_POST);

		$testSession = $this->getTestSession();

		$this->skillEvaluation->setUserId($testSession->getUserId());
		$this->skillEvaluation->setActiveId($testSession->getActiveId());
		$this->skillEvaluation->setPass($testSession->getPass());

		$settings = new ilSetting('assessment');

		$this->skillEvaluation->setNumRequiredBookingsForSkillTriggering($settings->get(
			'ass_skl_trig_num_answ_barrier', ilObjAssessmentFolder::DEFAULT_SKL_TRIG_NUM_ANSWERS_BARRIER
		));

		$testResults = $this->getTestResults();

		$this->skillEvaluation->init($this->getQuestionList());
		$this->skillEvaluation->evaluate($testResults);

		$evaluationToolbarGUI = $this->buildEvaluationToolbarGUI($selectedSkillProfile);
		$personalSkillsGUI = $this->buildPersonalSkillsGUI($testSession->getUserId(), $selectedSkillProfile);

		$this->tpl->setContent(
			$this->ctrl->getHTML($evaluationToolbarGUI) . $this->ctrl->getHTML($personalSkillsGUI)
		);
	}

	private function buildEvaluationToolbarGUI($selectedSkillProfileId)
	{
		$availableSkillProfiles = $this->skillEvaluation->getAssignedSkillMatchingSkillProfiles();

		$noSkillProfileOptionEnabled = $this->skillEvaluation->noProfileMatchingAssignedSkillExists(
			$availableSkillProfiles
		);

		$gui = new ilTestSkillEvaluationToolbarGUI($this->ctrl, $this->lng, $this, self::CMD_SHOW);

		$gui->setAvailableSkillProfiles($availableSkillProfiles);
		$gui->setNoSkillProfileOptionEnabled($noSkillProfileOptionEnabled);
		$gui->setSelectedEvaluationMode($selectedSkillProfileId);

		$gui->build();

		return $gui;
	}
	
	private function isTestResultButtonRequired()
	{
		$testOBJ = ilObjectFactory::getInstanceByObjId($this->objectId);
		
		if( !$testOBJ->canShowTestResults($this->testSession) )
		{
			return false;
		}

		require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
		$testPassesSelector = new ilTestPassesSelector($this->db, $testOBJ);
		$testPassesSelector->setActiveId($this->testSession->getActiveId());
		$testPassesSelector->setLastFinishedPass($this->testSession->getLastFinishedPass());

		if( !count($testPassesSelector->getReportablePasses()) )
		{
			return false;
		}
		
		return true;
	}

	private function buildPersonalSkillsGUI($usrId, $selectedSkillProfileId)
	{
		$availableSkills = $this->skillEvaluation->getUniqueAssignedSkillsForPersonalSkillGUI();
		$reachedSkillLevels = $this->skillEvaluation->getReachedSkillLevelsForPersonalSkillGUI();

		$gui = new ilTestPersonalSkillsGUI($this->lng, $this->getObjectId());

		$gui->setAvailableSkills($availableSkills);
		$gui->setSelectedSkillProfile($selectedSkillProfileId);

		$gui->setReachedSkillLevels($reachedSkillLevels);
		$gui->setUsrId($usrId);

		return $gui;
	}

	/**
	 * @return int
	 */
	public function getTestId()
	{
		return $this->testId;
	}

	/**
	 * @param array $testResults
	 */
	public function setTestResults($testResults)
	{
		$this->testResults = $testResults;
	}

	/**
	 * @return array
	 */
	public function getTestResults()
	{
		return $this->testResults;
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
	public function getTestSession()
	{
		return $this->testSession;
	}

	/**
	 * @return int
	 */
	public function getObjectId()
	{
		return $this->objectId;
	}
}