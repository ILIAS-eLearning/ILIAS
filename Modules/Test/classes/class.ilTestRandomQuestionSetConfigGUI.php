<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetConfig.php';

/**
 * GUI class that manages the question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 * 
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetGeneralConfigFormGUI
 */
class ilTestRandomQuestionSetConfigGUI
{
	const CMD_SHOW_GENERAL_CONFIG_FORM = 'showGeneralConfigForm';
	const CMD_SAVE_GENERAL_CONFIG_FORM = 'saveGeneralConfigForm';
	const CMD_SHOW_POOL_CONFIG_TABLE = 'showPoolConfigTable';
	
	/**
	 * global $ilCtrl object
	 * 
	 * @var ilCtrl
	 */
	public $ctrl = null;
	
	/**
	 * global $ilAccess object
	 * 
	 * @var ilAccess
	 */
	public $access = null;
	
	/**
	 * global $ilTabs object
	 *
	 * @var ilTabsGUI
	 */
	public $tabs = null;
	
	/**
	 * global $lng object
	 * 
	 * @var ilLanguage
	 */
	public $lng = null;
	
	/**
	 * global $tpl object
	 * 
	 * @var ilTemplate
	 */
	public $tpl = null;
	
	/**
	 * global $ilDB object
	 * 
	 * @var ilDB
	 */
	public $db = null;
	
	/**
	 * global $tree object
	 * 
	 * @var ilTree
	 */
	public $tree = null;
	
	/**
	 * object instance for current test
	 *
	 * @var ilObjTest
	 */
	public $testOBJ = null;
	
	/**
	 * object instance managing the dynamic question set config
	 *
	 * @var ilTestRandomQuestionSetConfig 
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
		
		$this->questionSetConfig = new ilTestRandomQuestionSetConfig($this->tree, $this->db, $this->testOBJ);
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
	
	private function buildGeneralConfigForm()
	{
		require_once 'Modules/Test/classes/forms/class.ilTestRandomQuestionSetGeneralConfigFormGUI.php';
		
		$form = new ilTestRandomQuestionSetGeneralConfigFormGUI(
				$this->ctrl, $this->lng, $this, $this->questionSetConfig
		);
		
		$form->build();
		
		return $form;
	}
	
	private function showGeneralConfigFormCmd()
	{
		$form = $this->buildGeneralConfigForm();
		
		$this->tpl->setContent( $this->ctrl->getHTML($form) );
	}
	
	private function saveGeneralConfigFormCmd()
	{
		$form = $this->buildGeneralConfigForm();

		if( $this->testOBJ->participantDataExist() )
		{
			ilUtil::sendFailure($this->lng->txt("tst_msg_cannot_modify_dynamic_question_set_conf_due_to_part"), true);
			return $this->showFormCmd($form);
		}
		
		$errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
		$form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

		if($errors)
		{
			ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
			return $this->showFormCmd($form);
		}
		
		$saved = $this->performSaveForm($form);
		
		if( !$saved )
		{
			return $this->showFormCmd($form);
		}
		
		$this->testOBJ->saveCompleteStatus( $this->questionSetConfig );

		ilUtil::sendSuccess($this->lng->txt("tst_msg_dynamic_question_set_config_modified"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_FORM);
	}
	
	private function buildPoolConfigTable()
	{
		
	}
	
	private function showPoolConfigTableCmd()
	{
		
	}
}
