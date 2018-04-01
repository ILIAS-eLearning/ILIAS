<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilParticipantsTestResultsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 * 
 * @ilCtrl_Calls ilParticipantsTestResultsGUI: ilTestEvaluationGUI
 */
class ilParticipantsTestResultsGUI
{
	const CMD_SHOW_PARTICIPANTS = 'showParticipants';
	
	/**
	 * @var ilObjTest
	 */
	protected $testObj;
	
	/**
	 * @var ilTestQuestionSetConfig
	 */
	protected $questionSetConfig;
	
	/**
	 * @var ilTestAccess
	 */
	protected $testAccess;
	
	/**
	 * @return ilObjTest
	 */
	public function getTestObj()
	{
		return $this->testObj;
	}
	
	/**
	 * @param ilObjTest $testObj
	 */
	public function setTestObj($testObj)
	{
		$this->testObj = $testObj;
	}
	
	/**
	 * @return ilTestQuestionSetConfig
	 */
	public function getQuestionSetConfig()
	{
		return $this->questionSetConfig;
	}
	
	/**
	 * @param ilTestQuestionSetConfig $questionSetConfig
	 */
	public function setQuestionSetConfig($questionSetConfig)
	{
		$this->questionSetConfig = $questionSetConfig;
	}
	
	/**
	 * @return ilTestAccess
	 */
	public function getTestAccess()
	{
		return $this->testAccess;
	}
	
	/**
	 * @param ilTestAccess $testAccess
	 */
	public function setTestAccess($testAccess)
	{
		$this->testAccess = $testAccess;
	}
	
	/**
	 * Execute Command
	 */
	public function	executeCommand()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		if( !$this->getTestAccess()->checkManageParticipantsAccess() && !$this->getTestAccess()->checkParticipantsResultsAccess() )
		{
			ilObjTestGUI::accessViolationRedirect();
		}
		
		switch( $DIC->ctrl()->getNextClass($this) )
		{
			case "iltestevaluationgui":
				require_once 'Modules/Test/classes/class.ilTestEvaluationGUI.php';
				$gui = new ilTestEvaluationGUI($this->getTestObj());
				$gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
				$gui->setTestAccess($this->getTestAccess());
				break;
			
			default:
				
				$command = $DIC->ctrl()->getCmd(self::CMD_SHOW_PARTICIPANTS).'Cmd';
				$this->{$command}();
		}
	}
	
	/**
	 * @return ilTestParticipantsTableGUI
	 */
	protected function buildTableGUI()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestParticipantsTableGUI.php';
		$tableGUI = new ilTestParticipantsTableGUI($this, self::CMD_SHOW_PARTICIPANTS);
		return $tableGUI;
	}
	
	/**
	 * set table filter command
	 */
	protected function setFilterCmd()
	{
		$tableGUI = $this->buildTableGUI();
		$tableGUI->writeFilterToSession();
		$tableGUI->resetOffset();
		$this->showParticipantsCmd();
		
	}
	
	/**
	 * reset table filter command
	 */
	protected function resetFilterCmd()
	{
		$tableGUI = $this->buildTableGUI();
		$tableGUI->resetFilter();
		$tableGUI->resetOffset();
		$this->showParticipantsCmd();
		
	}
	
	/**
	 * show participants command
	 */
	protected function showParticipantsCmd()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		if( $this->getQuestionSetConfig()->areDepenciesBroken() )
		{
			ilUtil::sendFailure(
				$this->getQuestionSetConfig()->getDepenciesBrokenMessage($DIC->language())
			);
		}
		elseif( $this->getQuestionSetConfig()->areDepenciesInVulnerableState() )
		{
			ilUtil::sendInfo(
				$this->questionSetConfig->getDepenciesInVulnerableStateMessage($DIC->language())
			);
		}
		
		$manageParticipantFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());
		$accessResultsFilter = ilTestParticipantAccessFilter::getAccessResultsUserFilter($this->getTestObj()->getRefId());
		
		$participantList = $this->getTestObj()->getParticipantList();
		$participantList = $participantList->getAccessFilteredList($manageParticipantFilter);
		$participantList = $participantList->getAccessFilteredList($accessResultsFilter);
		
		require_once 'Modules/Test/classes/tables/class.ilTestParticipantsTableGUI.php';
		$tableGUI = new ilTestParticipantsTableGUI($this, 'participants');
		$tableGUI->setRowKeyDataField('active_id');

		if( !$this->getQuestionSetConfig()->areDepenciesBroken() )
		{
			$tableGUI->setManageResultsCommandsEnabled(
				$this->getTestAccess()->checkManageParticipantsAccess()
			);
			
			$tableGUI->setAccessResultsCommandsEnabled(
				$this->getTestAccess()->checkParticipantsResultsAccess()
			);

			if( $participantList->hasUnfinishedPasses() )
			{
				$this->addFinishAllPassesButton($DIC->toolbar());
			}
			
			if( $participantList->hasTestResults() )
			{
				$this->addDeleteAllTestResultsButton($DIC->toolbar());
			}
		}
		
		$tableGUI->setAnonymity($this->getTestObj()->getAnonymity());
		
		$tableGUI->initColumns();
		$tableGUI->initCommands();
		
		$tableGUI->initFilter();
		$tableGUI->setFilterCommand('participantsSetFilter');
		$tableGUI->setResetCommand('participantsResetFiler');
		
		$tableGUI->setData($this->applyFilterCriteria($participantList->getTableRows()));
		
		$DIC->ui()->mainTemplate()->setContent($tableGUI->getHTML());
	}
	
	/**
	 * @param ilToolbarGUI $toolbar
	 */
	protected function addDeleteAllTestResultsButton(ilToolbarGUI $toolbar)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		require_once  'Services/UIComponent/Button/classes/class.ilLinkButton.php';
		$delete_all_results_btn = ilLinkButton::getInstance();
		$delete_all_results_btn->setCaption('delete_all_user_data');
		$delete_all_results_btn->setUrl($DIC->ctrl()->getLinkTarget($this, 'deleteAllUserResults'));
		$toolbar->addButtonInstance($delete_all_results_btn);
	}
	
	/**
	 * @param ilToolbarGUI $toolbar
	 */
	protected function addFinishAllPassesButton(ilToolbarGUI $toolbar)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$finish_all_user_passes_btn = ilLinkButton::getInstance();
		$finish_all_user_passes_btn->setCaption('finish_all_user_passes');
		$finish_all_user_passes_btn->setUrl($DIC->ctrl()->getLinkTargetByClass('iltestevaluationgui', 'finishAllUserPasses'));
		$toolbar->addButtonInstance($finish_all_user_passes_btn);
	}
	
	/**
	 * @param array $in_rows
	 * @return array
	 */
	protected function applyFilterCriteria($in_rows)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$sess_filter = $_SESSION['form_tst_participants_' . $this->getTestObj()->getRefId()]['selection'];
		$sess_filter = str_replace('"','',$sess_filter);
		$sess_filter = explode(':', $sess_filter);
		$filter = substr($sess_filter[2],0, strlen($sess_filter[2])-1);
		
		if ($filter == 'all' || $filter == false)
		{
			return $in_rows; #unchanged - no filter.
		}
		
		$with_result = array();
		$without_result = array();
		foreach ($in_rows as $row)
		{
			$result = $DIC->database()->query(
				'SELECT count(solution_id) count
				FROM tst_solutions
				WHERE active_fi = ' . $DIC->database()->quote($row['active_id'])
			);
			$count = $DIC->database()->fetchAssoc($result);
			$count = $count['count'];
			
			if ($count == 0)
			{
				$without_result[] = $row;
			}
			else
			{
				$with_result[] = $row;
			}
		}
		
		if ($filter == 'withSolutions')
		{
			return $with_result;
		}
		return $without_result;
	}
}