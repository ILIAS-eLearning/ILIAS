<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/toolbars/class.ilTestSkillEvaluationToolbarGUI.php';
require_once 'Modules/Test/classes/class.ilTestPersonalSkillsGUI.php';

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
	 * @var ilObjTest
	 */
	private $testOBJ;

	/**
	 * @var ilTestSkillEvaluation
	 */
	private $skillEvaluation;

	/**
	 * @var ilTestSessionFactory
	 */
	private $testSessionFactory;

	public function __construct(ilCtrl $ctrl, ilTabsGUI $tabs, ilTemplate $tpl, ilLanguage $lng, ilDB $db, ilObjTest $testOBJ)
	{
		$this->ctrl = $ctrl;
		$this->tabs = $tabs;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->db = $db;
		$this->testOBJ = $testOBJ;

		require_once 'Modules/Test/classes/class.ilTestSkillEvaluation.php';
		$this->skillEvaluation = new ilTestSkillEvaluation($this->db, $this->testOBJ);

		require_once 'Modules/Test/classes/class.ilTestSessionFactory.php';
		$this->testSessionFactory = new ilTestSessionFactory($this->testOBJ);
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
		$selectedSkillProfile = ilTestSkillEvaluationToolbarGUI::fetchSkillProfileParam($_POST);

		$testSession = $this->testSessionFactory->getSession();

		$this->skillEvaluation->init()->evaluate(
			$testSession->getActiveId(), $testSession->getLastFinishedPass(), $testSession->getUserId()
		);

		$evaluationToolbarGUI = $this->buildEvaluationToolbarGUI($testSession->getUserId(), $selectedSkillProfile);
		$personalSkillsGUI = $this->buildPersonalSkillsGUI($testSession->getUserId(), $selectedSkillProfile);

		$this->tpl->setContent(
			$this->ctrl->getHTML($evaluationToolbarGUI) . $this->ctrl->getHTML($personalSkillsGUI)
		);
	}

	private function buildEvaluationToolbarGUI($usrId, $selectedSkillProfileId)
	{
		$availableSkillProfiles = $this->skillEvaluation->getAssignedSkillMatchingSkillProfiles(
			$usrId
		);

		$noSkillProfileOptionEnabled = $this->skillEvaluation->noProfileMatchingAssignedSkillExists(
			$usrId, $availableSkillProfiles
		);

		$gui = new ilTestSkillEvaluationToolbarGUI($this->ctrl, $this->lng, $this, self::CMD_SHOW);

		$gui->setAvailableSkillProfiles($availableSkillProfiles);
		$gui->setNoSkillProfileOptionEnabled($noSkillProfileOptionEnabled);
		$gui->setSelectedEvaluationMode($selectedSkillProfileId);

		$gui->build();

		return $gui;
	}

	private function buildPersonalSkillsGUI($usrId, $selectedSkillProfileId)
	{
		$availableSkills = $this->skillEvaluation->getUniqueAssignedSkillsForPersonalSkillGUI();
		$reachedSkillLevels = $this->skillEvaluation->getReachedSkillLevelsForPersonalSkillGUI();

		$gui = new ilTestPersonalSkillsGUI($this->lng, $this->testOBJ);

		$gui->setAvailableSkills($availableSkills);
		$gui->setSelectedSkillProfile($selectedSkillProfileId);

		$gui->setReachedSkillLevels($reachedSkillLevels);
		$gui->setUsrId($usrId);

		return $gui;
	}
}