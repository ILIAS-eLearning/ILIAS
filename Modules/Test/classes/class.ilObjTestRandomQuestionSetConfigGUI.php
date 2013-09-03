<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilObjTestRandomQuestionSetConfig.php';

/**
 * GUI class that manages the question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 * 
 * @ilCtrl_Calls ilObjTestRandomQuestionSetConfigGUI: ilPropertyFormGUI
 */
class ilObjTestRandomQuestionSetConfigGUI
{
	const CMD_SHOW_GENERAL_CONFIG_FORM = 'showGeneralConfigForm';
	const CMD_SHOW_POOL_CONFIG_TABLE = 'showPoolConfigTable';
	
	/**
	 * global $ilCtrl object
	 * 
	 * @var ilCtrl
	 */
	protected $ctrl = null;
	
	/**
	 * global $ilAccess object
	 * 
	 * @var ilAccess
	 */
	protected $access = null;
	
	/**
	 * global $ilTabs object
	 *
	 * @var ilTabsGUI
	 */
	protected $tabs = null;
	
	/**
	 * global $lng object
	 * 
	 * @var ilLanguage
	 */
	protected $lng = null;
	
	/**
	 * global $tpl object
	 * 
	 * @var ilTemplate
	 */
	protected $tpl = null;
	
	/**
	 * global $ilDB object
	 * 
	 * @var ilDB
	 */
	protected $db = null;
	
	/**
	 * global $tree object
	 * 
	 * @var ilTree
	 */
	protected $tree = null;
	
	/**
	 * object instance for current test
	 *
	 * @var ilObjTest
	 */
	protected $testOBJ = null;
	
	/**
	 * object instance managing the dynamic question set config
	 *
	 * @var ilObjTestRandomQuestionSetConfig 
	 */
	protected $questionSetConfig = null;
	
	/**
	 * Constructor
	 */
	public function __construct(ilCtrl $ctrl, ilAccessHandler $access, ilTabsGUI $tabs, ilLanguage $lng, ilTemplate $tpl, ilDB $db, ilTree $tree, ilObjTest $testOBJ)
	{
		$this->ctrl = $ctrl;
		$this->access = $access;
		$this->tabs = $tabs;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->db = $db;
		$this->tree = $tree;
		
		$this->testOBJ = $testOBJ;
		
		$this->questionSetConfig = new ilObjTestRandomQuestionSetConfig($this->tree, $this->db, $this->testOBJ);
	}
	
	/**
	 * Command Execution
	 */
	public function executeCommand()
	{
		// allow only write access
		
		if (!$this->access->checkAccess("write", "", $this->testOBJ->getRefId())) 
		{
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirectByClass('ilObjTestGUI', "infoScreen");
		}
		
		// activate corresponding tab (auto activation does not work in ilObjTestGUI-Tabs-Salad)
		
		$this->tabs->activateTab('assQuestions');
		$this->handleTabs();
		
		// process command
		
		$nextClass = $this->ctrl->getNextClass();
		
		switch($nextClass)
		{
			default:
				$cmd = $this->ctrl->getCmd(self::CMD_SHOW_GENERAL_CONFIG_FORM).'Cmd';
				$this->$cmd();
		}
	}
	
	private function handleTabs()
	{
		$this->tabs->activateTab('assQuestions');
		
		$this->tabs->addSubTab(
				'tstRandQuestSetGeneralConfig',
				$this->lng->txt('tst_rnd_quest_cfg_tab_general'),
				$this->ctrl->getLinkTarget($this, self::CMD_SHOW_GENERAL_CONFIG_FORM)
		);
		
		$this->tabs->addSubTab(
				'tstRandQuestSetPoolConfig',
				$this->lng->txt('tst_rnd_quest_cfg_tab_pool'),
				$this->ctrl->getLinkTarget($this, self::CMD_SHOW_POOL_CONFIG_TABLE)
		);
		
		switch( $this->ctrl->getCmd(self::CMD_SHOW_GENERAL_CONFIG_FORM) )
		{
			case self::CMD_SHOW_GENERAL_CONFIG_FORM:
				
				$this->tabs->activateSubTab('tstRandQuestSetGeneralConfig');
				break;

			case self::CMD_SHOW_POOL_CONFIG_TABLE:
				
				$this->tabs->activateSubTab('tstRandQuestSetPoolConfig');
				break;
		}
	}
	
	private function showGeneralConfigFormCmd()
	{
		
	}
	
	private function showPoolConfigTableCmd()
	{
		
	}
}
