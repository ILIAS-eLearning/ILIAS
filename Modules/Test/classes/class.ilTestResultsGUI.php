<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestResultsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 * 
 * @ilCtrl_Calls ilTestResultsGUI: ilParticipantsTestResultsGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilMyTestResultsGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilMyTestSolutionsGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilTestToplistGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilTestSkillEvaluationGUI
 */
class ilTestResultsGUI
{
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
	 * @var ilTestSession
	 */
	protected $testSession;
	
	/**
	 * @var ilTestTabsManager
	 */
	protected $testTabs;
	
	/**
	 * @var ilTestObjectiveOrientedContainer
	 */
	protected $objectiveParent;
	
	/**
	 * ilTestParticipantsGUI constructor.
	 * @param ilObjTest $testObj
	 */
	public function __construct(ilObjTest $testObj, ilTestQuestionSetConfig $questionSetConfig)
	{
		$this->testObj = $testObj;
		$this->questionSetConfig = $questionSetConfig;
	}
	
	/**
	 * @return ilTestObjectiveOrientedContainer
	 */
	public function getObjectiveParent()
	{
		return $this->objectiveParent;
	}
	
	/**
	 * @param ilTestObjectiveOrientedContainer $objectiveParent
	 */
	public function setObjectiveParent($objectiveParent)
	{
		$this->objectiveParent = $objectiveParent;
	}
	
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
	 * @return ilTestSession
	 */
	public function getTestSession()
	{
		return $this->testSession;
	}
	
	/**
	 * @param ilTestSession $testSession
	 */
	public function setTestSession($testSession)
	{
		$this->testSession = $testSession;
	}
	
	/**
	 * @return ilTestTabsManager
	 */
	public function getTestTabs()
	{
		return $this->testTabs;
	}
	
	/**
	 * @param ilTestTabsManager $testTabs
	 */
	public function setTestTabs($testTabs)
	{
		$this->testTabs = $testTabs;
	}
	
	/**
	 * Execute Command
	 */
	public function	executeCommand()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		if( !$this->getTestAccess()->checkManageParticipantsAccess() )
		{
			ilObjTestGUI::accessViolationRedirect();
		}
		
		$this->getTestTabs()->activateTab(ilTestTabsManager::TAB_ID_RESULTS);
		$this->getTestTabs()->getResultsSubTabs();
		
		switch( $DIC->ctrl()->getNextClass() )
		{
			case 'ilparticipantstestresultsgui':
				
				$this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_PARTICIPANTS_RESULTS);
				
				require_once 'Modules/Test/classes/class.ilParticipantsTestResultsGUI.php';
				$gui = new ilParticipantsTestResultsGUI();
				$gui->setTestObj($this->getTestObj());
				$gui->setQuestionSetConfig($this->getQuestionSetConfig());
				$gui->setTestAccess($this->getTestAccess());
				$gui->setObjectiveParent($this->getObjectiveParent());
				$DIC->ctrl()->forwardCommand($gui);
				break;
			
			case 'ilmytestresultsgui':
				
				$this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_MY_RESULTS);
				
				require_once 'Modules/Test/classes/class.ilMyTestResultsGUI.php';
				$gui = new ilMyTestResultsGUI();
				$gui->setTestObj($this->getTestObj());
				$gui->setTestAccess($this->getTestAccess());
				$gui->setTestSession($this->getTestSession());
				$gui->setObjectiveParent($this->getObjectiveParent());
				$DIC->ctrl()->forwardCommand($gui);
				break;
			
			case 'ilmytestsolutionsgui':
				
				$this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_MY_SOLUTIONS);
				
				require_once 'Modules/Test/classes/class.ilMyTestSolutionsGUI.php';
				$gui = new ilMyTestSolutionsGUI();
				$gui->setTestObj($this->getTestObj());
				$gui->setTestAccess($this->getTestAccess());
				$gui->setObjectiveParent($this->getObjectiveParent());
				$DIC->ctrl()->forwardCommand($gui);
				break;
			
			case 'iltesttoplistgui':
				
				$this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_HIGHSCORE);
				
				require_once './Modules/Test/classes/class.ilTestToplistGUI.php';
				$gui = new ilTestToplistGUI($this->getTestObj());
				$DIC->ctrl()->forwardCommand($gui);
				break;

			case 'iltestskillevaluationgui':
				
				$this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_SKILL_RESULTS);
				
				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
				if( $this->object->isDynamicTest() )
				{
					require_once 'Modules/Test/classes/class.ilObjTestDynamicQuestionSetConfig.php';
					$dynamicQuestionSetConfig = new ilObjTestDynamicQuestionSetConfig(
						$DIC->repositoryTree(), $DIC->database(), $GLOBALS['ilPluginAdmin'], $this->getTestObj()
					);
					$dynamicQuestionSetConfig->loadFromDb();
					$questionList = new ilAssQuestionList($DIC->database(), $DIC->language(), $GLOBALS['ilPluginAdmin']);
					$questionList->setParentObjId($dynamicQuestionSetConfig->getSourceQuestionPoolId());
					$questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_ORIGINALS);
				}
				else
				{
					$questionList = new ilAssQuestionList($DIC->database(), $DIC->language(), $GLOBALS['ilPluginAdmin']);
					$questionList->setParentObjId($this->getTestObj()->getId());
					$questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);
				}
				$questionList->load();
				
				require_once 'Modules/Test/classes/class.ilTestSessionFactory.php';
				$testSessionFactory = new ilTestSessionFactory($this->getTestObj());
				$testSession = $testSessionFactory->getSession();
				
				require_once 'Modules/Test/classes/class.ilTestSkillEvaluationGUI.php';
				$gui = new ilTestSkillEvaluationGUI(
					$DIC->ctrl(), $DIC->tabs(), $DIC->ui()->mainTemplate(), $DIC->language(), $DIC->database(), $this->getTestObj()
				);
				$gui->setQuestionList($questionList);
				$gui->setTestSession($testSession);
				$gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
				
				$DIC->ctrl()->forwardCommand($gui);
				break;
		}
	}
}