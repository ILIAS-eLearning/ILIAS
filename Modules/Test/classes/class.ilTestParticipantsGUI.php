<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 * 
 * @ilCtrl_Calls ilTestParticipantsGUI: ilTestFixedParticipantsGUI
 * @ilCtrl_Calls ilTestParticipantsGUI: ilTestParticipantsTimeExtensionGUI
 */
class ilTestParticipantsGUI
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
	 * @var ilTestTabsManager
	 */
	protected $testTabs;
	
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
		
		$this->getTestTabs()->activateTab(ilTestTabsManager::TAB_ID_EXAM_DASHBOARD);
		$this->getTestTabs()->getParticipantsSubTabs();
		
		switch( $DIC->ctrl()->getNextClass() )
		{
			case 'iltestfixedparticipantsgui':
				
				$this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_FIXED_PARTICIPANTS);
				
				require_once 'Modules/Test/classes/class.ilTestFixedParticipantsGUI.php';
				$gui = new ilTestFixedParticipantsGUI($this->getTestObj(), $this->getQuestionSetConfig());
				$DIC->ctrl()->forwardCommand($gui);
				break;
				
			case 'iltestparticipantstimeextensiongui':
				
				$this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_TIME_EXTENSION);
				
				require_once 'Modules/Test/classes/class.ilTestParticipantsTimeExtensionGUI.php';
				$gui = new ilTestParticipantsTimeExtensionGUI($this->getTestObj());
				$DIC->ctrl()->forwardCommand($gui);
				break;
		}
	}
}