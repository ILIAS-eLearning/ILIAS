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
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetSourcePoolsToolbarGUI
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetSourcePoolsTableGUI
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetPoolConfigFormGUI
 */
class ilTestRandomQuestionSetConfigGUI
{
	/**
	 * command constants
	 */
	const CMD_SHOW_GENERAL_CONFIG = 'showGeneralConfig';
	const CMD_SAVE_GENERAL_CONFIG = 'saveGeneralConfig';
	const CMD_SHOW_POOL_CONFIG_LIST = 'showPoolConfigList';
	const CMD_SAVE_POOL_CONFIG_LIST = 'savePoolConfigList';
	const CMD_SHOW_POOL_CONFIG = 'showPoolConfig';
	const CMD_SAVE_POOL_CONFIG = 'savePoolConfig';
	
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
		
		// manage sub tabs and tab activation
		
		$this->handleTabs();
		
		// process command
		
		$nextClass = $this->ctrl->getNextClass();
		
		switch($nextClass)
		{
			case 'ilTestRandomQuestionSetPoolConfigFormGUI':
				
				$formGUI = new ilTestRandomQuestionSetPoolConfigFormGUI(
						$this->ctrl, $this->lng, $this->testOBJ, $this, $this->questionSetConfig
				);
				
				$this->ctrl->forwardCommand($formGUI);
				
				break;
				
			default:
				
				$cmd = $this->ctrl->getCmd(self::CMD_SHOW_GENERAL_CONFIG).'Cmd';
				
				$this->$cmd();
		}
	}
	
	private function handleTabs()
	{
		$this->tabs->activateTab('assQuestions');
		
		$this->tabs->addSubTab(
				'tstRandQuestSetGeneralConfig',
				$this->lng->txt('tst_rnd_quest_cfg_tab_general'),
				$this->ctrl->getLinkTarget($this, self::CMD_SHOW_GENERAL_CONFIG)
		);
		
		$this->tabs->addSubTab(
				'tstRandQuestSetPoolConfig',
				$this->lng->txt('tst_rnd_quest_cfg_tab_pool'),
				$this->ctrl->getLinkTarget($this, self::CMD_SHOW_POOL_CONFIG_LIST)
		);
		
		switch( $this->ctrl->getCmd(self::CMD_SHOW_GENERAL_CONFIG) )
		{
			case self::CMD_SHOW_GENERAL_CONFIG:
			case self::CMD_SAVE_GENERAL_CONFIG:
				
				$this->tabs->activateSubTab('tstRandQuestSetGeneralConfig');
				break;

			case self::CMD_SHOW_POOL_CONFIG_LIST:
			case self::CMD_SAVE_POOL_CONFIG_LIST:
			case self::CMD_SHOW_POOL_CONFIG:
			case self::CMD_SAVE_POOL_CONFIG:
				
				$this->tabs->activateSubTab('tstRandQuestSetPoolConfig');
				break;
		}
	}
	
	private function buildGeneralConfigForm()
	{
		require_once 'Modules/Test/classes/forms/class.ilTestRandomQuestionSetGeneralConfigFormGUI.php';
		
		$form = new ilTestRandomQuestionSetGeneralConfigFormGUI(
				$this->ctrl, $this->lng, $this->testOBJ, $this, $this->questionSetConfig
		);
		
		$form->build();
		
		return $form;
	}
	
	private function showGeneralConfigCmd(ilTestRandomQuestionSetGeneralConfigFormGUI $form = null)
	{
		if($form === null)
		{
			$this->questionSetConfig->loadFromDb();
			$form = $this->buildGeneralConfigForm();
		}
		
		$this->tpl->setContent( $this->ctrl->getHTML($form) );
	}
	
	private function saveGeneralConfigCmd()
	{
		$this->questionSetConfig->loadFromDb();
		$form = $this->buildGeneralConfigForm();

		if( $this->testOBJ->participantDataExist() )
		{
			ilUtil::sendFailure($this->lng->txt("tst_msg_cannot_modify_random_question_set_conf_due_to_part"), true);
			return $this->showGeneralConfigCmd($form);
		}
		
		$errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
		$form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

		if($errors)
		{
			return $this->showGeneralConfigCmd($form);
		}
		
		$saved = $form->save();
		
		if( !$saved )
		{
			return $this->showGeneralConfigCmd($form);
		}
		
		$this->testOBJ->saveCompleteStatus( $this->questionSetConfig );

		ilUtil::sendSuccess($this->lng->txt("tst_msg_random_question_set_config_modified"));
		$this->ctrl->redirect($this, self::CMD_SHOW_GENERAL_CONFIG);
	}
	
	private function buildPoolConfigListToolbar()
	{
		require_once 'Modules/Test/classes/toolbars/class.ilTestRandomQuestionSetSourcePoolsToolbarGUI.php';
		
		$toolbar = new ilTestRandomQuestionSetSourcePoolsToolbarGUI(
				$this->ctrl, $this->lng, $this->testOBJ, $this, $this->questionSetConfig
		);
		
		$toolbar->build();
		
		return $toolbar;
	}
	
	private function buildPoolConfigListTable()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestRandomQuestionSetSourcePoolsTableGUI.php';
		
		$table = new ilTestRandomQuestionSetSourcePoolsTableGUI(
				$this, self::CMD_SHOW_POOL_CONFIG_LIST
		);
		
		$table->build();
		
		return $table;
	}
	
	private function showPoolConfigListCmd()
	{
		$toolbar = $this->buildPoolConfigListToolbar();
		$table = $this->buildPoolConfigListTable();
		
		$this->tpl->setContent(
				$this->ctrl->getHTML($toolbar) . $this->ctrl->getHTML($table)
		);
	}
	
	private function savePoolConfigListCmd()
	{
		
	}
	
	private function buildPoolConfigForm(ilTestRandomQuestionSetSourcePool $sourcePool, $availableTaxonomyIds)
	{
		require_once 'Modules/Test/classes/forms/class.ilTestRandomQuestionSetPoolConfigFormGUI.php';
		
		$form = new ilTestRandomQuestionSetPoolConfigFormGUI(
				$this->ctrl, $this->lng, $this->testOBJ, $this, $this->questionSetConfig
		);
		
		$form->build( $sourcePool, $availableTaxonomyIds );
		
		return $form;
	}
	
	private function showPoolConfigCmd(ilTestRandomQuestionSetPoolConfigFormGUI $form = null)
	{
		$this->questionSetConfig->loadFromDb();
			
		$poolId = $this->fetchPoolConfigParameter();
		
		$sourcePool = $this->buildSourcePoolInstance($poolId);
		
		if($form === null)
		{
			$form = $this->buildPoolConfigForm( $sourcePool, $this->getAvailableTaxonomyIds($sourcePool->getPoolId()) );
		}
		
		$this->tpl->setContent( $this->ctrl->getHTML($form) );
	}
	
	private function savePoolConfigCmd()
	{
		$this->questionSetConfig->loadFromDb();
		
		$poolId = $this->fetchPoolConfigParameter();
		$sourcePool = $this->buildSourcePoolInstance($poolId);
		
		$availableTaxonomyIds = $this->getAvailableTaxonomyIds( $sourcePool->getPoolId() );
		
		$form = $this->buildPoolConfigForm( $sourcePool, $availableTaxonomyIds );

		if( $this->testOBJ->participantDataExist() )
		{
			ilUtil::sendFailure($this->lng->txt("tst_msg_cannot_modify_random_question_set_conf_due_to_part"));
			return $this->showPoolConfigCmd($form);
		}
		
		$errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
		$form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

		if($errors)
		{
			return $this->showPoolConfigCmd($form);
		}
		
		$form->applySubmit( $sourcePool, $availableTaxonomyIds );
		
		if( !$sourcePool->saveToDb() )
		{
			return $this->showPoolConfigCmd($form);
		}
		
		$this->questionSetConfig->fetchRandomQuestionSet();
		
		$this->testOBJ->saveCompleteStatus( $this->questionSetConfig );

		ilUtil::sendSuccess($this->lng->txt("tst_msg_random_question_set_config_modified"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_POOL_CONFIG_LIST);
	}
	
	private function fetchPoolConfigParameter()
	{
		if( isset($_POST['source_pool_id']) && (int)$_POST['source_pool_id'] )
		{
			return (int)$_POST['source_pool_id'];
		}
		
		if( isset($_GET['source_pool_id']) && (int)$_GET['source_pool_id'] )
		{
			return (int)$_GET['source_pool_id'];
		}
		
		require_once 'Modules/Test/exceptions/class.ilTestInvalidParameterException.php';
		throw new ilTestInvalidParameterException('no source question pool id given');
	}
	
	private function buildSourcePoolInstance($poolId)
	{
		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolFactory.php';
		$sourcePoolFactory = new ilTestRandomQuestionSetSourcePoolFactory($this->db, $this->testOBJ);
		
		if( $this->questionSetConfig->isSourceQuestionPool($poolId) )
		{
			return $sourcePoolFactory->getSourcePoolByMirroredPoolData($poolId);
		}
		
		$availablePools = $this->testOBJ->getAvailableQuestionpools(
			true, $this->questionSetConfig->arePoolsWithHomogeneousScoredQuestionsRequired(), false, true, true
		);
		
		if( isset($availablePools[$poolId]) )
		{
			$originalPoolData = $availablePools[$poolId];
			
			$originalPoolData['qpl_path'] = $this->questionSetConfig->getQuestionPoolPathString($poolId);
			
			return $sourcePoolFactory->getSourcePoolByOriginalPoolData($originalPoolData);
		}
		
		require_once 'Modules/Test/exceptions/class.ilTestInvalidParameterException.php';
		throw new ilTestInvalidParameterException('invalid source question pool id given');
	}
	
	private function getAvailableTaxonomyIds($objId)
	{
		require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
		return ilObjTaxonomy::getUsageOfObject($objId);
	}
}
