<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Modules/Test/classes/class.ilTestSkillQuestionAssignmentsGUI.php';
include_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdsGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilTestSkillAdministrationGUI: ilTestSkillQuestionAssignmentsGUI
 * @ilCtrl_Calls ilTestSkillAdministrationGUI: ilTestSkillLevelThresholdsGUI
 */
class ilTestSkillAdministrationGUI
{
	/**
	 * @var ILIAS
	 */
	private $ilias;

	/**
	 * @var ilCtrl
	 */
	private $ctrl;

	/**
	 * @var ilAccessHandler
	 */
	private $access;

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

	public function __construct(ILIAS $ilias, ilCtrl $ctrl, ilAccessHandler $access, ilTabsGUI $tabs, ilTemplate $tpl, ilLanguage $lng, ilDB $db, ilObjTest $testOBJ, $refId)
	{
		$this->ilias = $ilias;
		$this->ctrl = $ctrl;
		$this->access = $access;
		$this->tabs = $tabs;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->db = $db;
		$this->testOBJ = $testOBJ;
		$this->refId = $refId;
	}

	public function executeCommand()
	{
		if( $this->isAccessDenied() )
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
		}

		$nextClass = $this->ctrl->getNextClass();

		$this->manageTabs($nextClass);

		switch($nextClass)
		{
			case 'iltestskillquestionassignmentsgui':

				$gui = new ilTestSkillQuestionAssignmentsGUI($this->ctrl, $this->tpl, $this->lng, $this->db, $this->testOBJ);
				$this->ctrl->forwardCommand($gui);
				break;

			case 'iltestskilllevelthresholdsgui':

				$gui = new ilTestSkillLevelThresholdsGUI($this->ctrl, $this->tpl, $this->lng, $this->db, $this->testOBJ);
				$this->ctrl->forwardCommand($gui);
				break;
		}
	}

	public function manageTabs($activeSubTabId)
	{
		$link = $this->ctrl->getLinkTargetByClass(
			'iltestskillquestionassignmentsgui', ilTestSkillQuestionAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
		);
		$this->tabs->addSubTab(
			'iltestskillquestionassignmentsgui', $this->lng->txt('tst_skl_sub_tab_quest_assign'), $link

		);

		$link = $this->ctrl->getLinkTargetByClass(
			'iltestskilllevelthresholdsgui', ilTestSkillLevelThresholdsGUI::CMD_SHOW_SKILL_THRESHOLDS
		);
		$this->tabs->addSubTab(
			'iltestskilllevelthresholdsgui', $this->lng->txt('tst_skl_sub_tab_thresholds'), $link
		);

		$this->tabs->activateTab('tst_tab_competences');
		$this->tabs->activateSubTab($activeSubTabId);
	}

	private function isAccessDenied()
	{
		if( !$this->testOBJ->isSkillServiceEnabled() )
		{
			return true;
		}

		if( !ilObjTest::isSkillManagementGloballyActivated() )
		{
			return true;
		}

		if( ! $this->access->checkAccess('write', '', $this->refId) )
		{
			return true;
		}

		return false;
	}
} 