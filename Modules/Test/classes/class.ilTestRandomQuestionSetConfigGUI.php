<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetConfig.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolBuilder.php';

require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';

/**
 * GUI class that manages the question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 * 
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetGeneralConfigFormGUI
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetPoolDefinitionFormGUI
 */
class ilTestRandomQuestionSetConfigGUI
{
	const CMD_SHOW_GENERAL_CONFIG_FORM              = 'showGeneralConfigForm';
	const CMD_SAVE_GENERAL_CONFIG_FORM              = 'saveGeneralConfigForm';
	const CMD_SHOW_SRC_POOL_DEF_LIST                = 'showSourcePoolDefinitionList';
	const CMD_SAVE_SRC_POOL_DEF_LIST                = 'saveSourcePoolDefinitionList';
	const CMD_DELETE_SINGLE_SRC_POOL_DEF            = 'deleteSingleSourcePoolDefinition';
	const CMD_DELETE_MULTI_SRC_POOL_DEFS            = 'deleteMultipleSourcePoolDefinitions';
	const CMD_SHOW_CREATE_SRC_POOL_DEF_FORM         = 'showCreateSourcePoolDefinitionForm';
	const CMD_SAVE_CREATE_SRC_POOL_DEF_FORM         = 'saveCreateSourcePoolDefinitionForm';
	const CMD_SAVE_AND_NEW_CREATE_SRC_POOL_DEF_FORM = 'saveCreateAndNewSourcePoolDefinitionForm';
	const CMD_SHOW_EDIT_SRC_POOL_DEF_FORM           = 'showEditSourcePoolDefinitionForm';
	const CMD_SAVE_EDIT_SRC_POOL_DEF_FORM           = 'saveEditSourcePoolDefinitionForm';
	const CMD_BUILD_QUESTION_STAGE					= 'buildQuestionStage';
	
	const HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD = 'afterRebuildQuestionStageCmd';
	/**
	 * @var ilCtrl
	 */
	public $ctrl = null;
	
	/**
	 * @var ilAccess
	 */
	public $access = null;
	
	/**
	 * @var ilTabsGUI
	 */
	public $tabs = null;
	
	/**
	 * @var ilLanguage
	 */
	public $lng = null;
	
	/**
	 * @var ilTemplate
	 */
	public $tpl = null;
	
	/**
	 * @var ilDB
	 */
	public $db = null;
	
	/**
	 * @var ilTree
	 */
	public $tree = null;

	/**
	 * @var ilPluginAdmin
	 */
	public $pluginAdmin = null;

	/**
	 * @var ilObjTest
	 */
	public $testOBJ = null;
	
	/**
	 * @var ilTestRandomQuestionSetConfig
	 */
	protected $questionSetConfig = null;

	/**
	 * @var ilTestRandomQuestionSetSourcePoolDefinitionFactory
	 */
	protected $sourcePoolDefinitionFactory = null;

	/**
	 * @var ilTestRandomQuestionSetSourcePoolDefinitionList
	 */
	protected $sourcePoolDefinitionList= null;

	/**
	 * @var ilTestRandomQuestionSetStagingPoolBuilder
	 */
	protected $stagingPool = null;
	
	public function __construct(
		ilCtrl $ctrl, ilAccessHandler $access, ilTabsGUI $tabs, ilLanguage $lng,
		ilTemplate $tpl, ilDB $db, ilTree $tree, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ
	)
	{
		$this->ctrl = $ctrl;
		$this->access = $access;
		$this->tabs = $tabs;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->db = $db;
		$this->tree = $tree;
		$this->pluginAdmin = $pluginAdmin;

		$this->testOBJ = $testOBJ;

		$this->questionSetConfig = new ilTestRandomQuestionSetConfig(
			$this->tree, $this->db, $this->pluginAdmin, $this->testOBJ
		);

		$this->sourcePoolDefinitionFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
			$this->db, $this->testOBJ
		);

		$this->sourcePoolDefinitionList = new ilTestRandomQuestionSetSourcePoolDefinitionList(
			$this->db, $this->testOBJ, $this->sourcePoolDefinitionFactory
		);

		$this->stagingPool = new ilTestRandomQuestionSetStagingPoolBuilder(
			$this->db, $this->testOBJ
		);
	}
	
	public function executeCommand()
	{
		if (!$this->access->checkAccess("write", "", $this->testOBJ->getRefId()))
		{
			ilUtil::sendFailure($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirectByClass('ilObjTestGUI', "infoScreen");
		}

		if( $this->isAvoidManipulationRedirectRequired() )
		{
			ilUtil::sendFailure($this->lng->txt("tst_msg_cannot_modify_random_question_set_conf_due_to_part"), true);
			$this->ctrl->redirect($this);
		}

		$this->handleTabs();

		$nextClass = $this->ctrl->getNextClass();
		
		switch($nextClass)
		{
			case 'ilTestRandomQuestionSetPoolDefinitionFormGUI':
				
				$formGUI = new ilTestRandomQuestionSetPoolDefinitionFormGUI(
						$this->ctrl, $this->lng, $this->testOBJ, $this, $this->questionSetConfig
				);
				
				$this->ctrl->forwardCommand($formGUI);
				
				break;
				
			default:
				
				$cmd = $this->ctrl->getCmd(self::CMD_SHOW_GENERAL_CONFIG_FORM).'Cmd';
				
				$this->$cmd();
		}
	}

	private function isAvoidManipulationRedirectRequired()
	{
		if( !$this->testOBJ->participantDataExist() )
		{
			return false;
		}

		if( !$this->isManipulationCommand() )
		{
			return false;
		}

		return true;
	}

	private function isManipulationCommand()
	{
		switch( $this->ctrl->getCmd(self::CMD_SHOW_GENERAL_CONFIG_FORM) )
		{
			case self::CMD_SAVE_GENERAL_CONFIG_FORM:
			case self::CMD_SAVE_SRC_POOL_DEF_LIST:
			case self::CMD_DELETE_SINGLE_SRC_POOL_DEF:
			case self::CMD_DELETE_MULTI_SRC_POOL_DEFS:
			case self::CMD_SAVE_CREATE_SRC_POOL_DEF_FORM:
			case self::CMD_SAVE_EDIT_SRC_POOL_DEF_FORM:
			case self::CMD_SAVE_AND_NEW_CREATE_SRC_POOL_DEF_FORM:
			case self::CMD_BUILD_QUESTION_STAGE:

				return true;
		}

		return false;
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
				$this->ctrl->getLinkTarget($this, self::CMD_SHOW_SRC_POOL_DEF_LIST)
		);
		
		switch( $this->ctrl->getCmd(self::CMD_SHOW_GENERAL_CONFIG_FORM) )
		{
			case self::CMD_SHOW_GENERAL_CONFIG_FORM:
			case self::CMD_SAVE_GENERAL_CONFIG_FORM:
				
				$this->tabs->activateSubTab('tstRandQuestSetGeneralConfig');
				break;

			case self::CMD_SHOW_SRC_POOL_DEF_LIST:
			case self::CMD_SAVE_SRC_POOL_DEF_LIST:
			case self::CMD_DELETE_SINGLE_SRC_POOL_DEF:
			case self::CMD_DELETE_MULTI_SRC_POOL_DEFS:
			case self::CMD_SHOW_CREATE_SRC_POOL_DEF_FORM:
			case self::CMD_SAVE_CREATE_SRC_POOL_DEF_FORM:
			case self::CMD_SHOW_EDIT_SRC_POOL_DEF_FORM:
			case self::CMD_SAVE_EDIT_SRC_POOL_DEF_FORM:

				$this->tabs->activateSubTab('tstRandQuestSetPoolConfig');
				break;
		}
	}
	
	private function buildQuestionStageCmd()
	{
		$this->sourcePoolDefinitionList->loadDefinitions();
		$this->stagingPool->rebuild( $this->sourcePoolDefinitionList );
		$this->sourcePoolDefinitionList->saveDefinitions();

		$this->questionSetConfig->loadFromDb();
		$this->questionSetConfig->setLastQuestionSyncTimestamp(time());
		$this->questionSetConfig->saveToDb();

		$this->testOBJ->saveCompleteStatus( $this->questionSetConfig );
		
		ilUtil::sendSuccess($this->lng->txt("tst_msg_random_question_set_synced"), true);
		$this->ctrl->redirect($this, $this->fetchAfterRebuildQuestionStageCmdParameter());
	}
	
	private function fetchAfterRebuildQuestionStageCmdParameter()
	{
		if( !isset($_GET[self::HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD]) )
		{
			return self::CMD_SHOW_GENERAL_CONFIG_FORM;
		}
		
		if( !strlen($_GET[self::HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD]) )
		{
			return self::CMD_SHOW_GENERAL_CONFIG_FORM;
		}

		return $_GET[self::HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD];
	}

	private function showGeneralConfigFormCmd(ilTestRandomQuestionSetGeneralConfigFormGUI $form = null)
	{
		if($form === null)
		{
			$this->questionSetConfig->loadFromDb();
			$form = $this->buildGeneralConfigFormGUI();
		}
		
		$this->tpl->setContent( $this->ctrl->getHTML($form) );

		$this->handleConfigurationStateMessages(self::CMD_SHOW_GENERAL_CONFIG_FORM);
	}

	private function saveGeneralConfigFormCmd()
	{
		$this->questionSetConfig->loadFromDb();

		$form = $this->buildGeneralConfigFormGUI();

		$errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
		$form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

		if($errors)
		{
			return $this->showGeneralConfigFormCmd($form);
		}
		
		$form->save();

		$this->sourcePoolDefinitionList->loadDefinitions();
		$this->stagingPool->rebuild( $this->sourcePoolDefinitionList );
		$this->sourcePoolDefinitionList->saveDefinitions();

		$this->questionSetConfig->setLastQuestionSyncTimestamp(time());
		$this->questionSetConfig->saveToDb();

		$this->testOBJ->saveCompleteStatus( $this->questionSetConfig );

		ilUtil::sendSuccess($this->lng->txt("tst_msg_random_question_set_config_modified"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_GENERAL_CONFIG_FORM);
	}

	private function buildGeneralConfigFormGUI()
	{
		require_once 'Modules/Test/classes/forms/class.ilTestRandomQuestionSetGeneralConfigFormGUI.php';

		$form = new ilTestRandomQuestionSetGeneralConfigFormGUI(
			$this->ctrl, $this->lng, $this->testOBJ, $this, $this->questionSetConfig
		);

		$form->build();

		return $form;
	}

	private function showSourcePoolDefinitionListCmd()
	{
		$this->questionSetConfig->loadFromDb();
		$this->sourcePoolDefinitionList->loadDefinitions();

		$content = '';

		if( !$this->testOBJ->participantDataExist() )
		{
			$toolbar = $this->buildSourcePoolDefinitionListToolbarGUI();
			$content .= $this->ctrl->getHTML($toolbar);
		}

		$table = $this->buildSourcePoolDefinitionListTableGUI();
		$table->init( $this->sourcePoolDefinitionList);
		$content .= $this->ctrl->getHTML($table);

		$this->tpl->setContent($content);

		$this->handleConfigurationStateMessages(self::CMD_SHOW_SRC_POOL_DEF_LIST);
	}

	private function saveSourcePoolDefinitionListCmd()
	{
		$this->questionSetConfig->loadFromDb();

		$table = $this->buildSourcePoolDefinitionListTableGUI();

		$this->sourcePoolDefinitionList->loadDefinitions();

		$table->applySubmit($this->sourcePoolDefinitionList);

		$this->sourcePoolDefinitionList->reindexPositions();
		$this->sourcePoolDefinitionList->saveDefinitions();

		$this->stagingPool->rebuild( $this->sourcePoolDefinitionList );
		$this->sourcePoolDefinitionList->saveDefinitions();

		$this->questionSetConfig->setLastQuestionSyncTimestamp(time());
		$this->questionSetConfig->saveToDb();

		$this->testOBJ->saveCompleteStatus( $this->questionSetConfig );

		ilUtil::sendSuccess($this->lng->txt("tst_msg_random_question_set_config_modified"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
	}

	private function buildSourcePoolDefinitionListToolbarGUI()
	{
		require_once 'Modules/Test/classes/toolbars/class.ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI.php';

		$toolbar = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
			$this->ctrl, $this->lng, $this, $this->questionSetConfig
		);

		$toolbar->build();

		return $toolbar;
	}

	private function buildSourcePoolDefinitionListTableGUI()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI.php';

		$table = new ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI(
			$this->ctrl, $this->lng, $this, self::CMD_SHOW_SRC_POOL_DEF_LIST
		);

		if( !$this->testOBJ->participantDataExist() )
		{
			$table->setDefinitionEditModeEnabled(true);
		}

		$table->setQuestionAmountColumnEnabled(
			$this->questionSetConfig->isQuestionAmountConfigurationModePerPool()
		);

		require_once 'Modules/Test/classes/class.ilTestTaxonomyFilterLabelTranslater.php';
		$translater = new ilTestTaxonomyFilterLabelTranslater($this->db);
		$translater->loadLabels($this->sourcePoolDefinitionList);
		$table->setTaxonomyFilterLabelTranslater($translater);

		$table->build();

		return $table;
	}

	private function deleteSingleSourcePoolDefinitionCmd()
	{
		$definitionId = $this->fetchSingleSourcePoolDefinitionIdParameter();
		$this->deleteSourcePoolDefinitions( array($definitionId) );

		ilUtil::sendSuccess($this->lng->txt("tst_msg_source_pool_definitions_deleted"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
	}

	private function deleteMultipleSourcePoolDefinitionsCmd()
	{
		$definitionIds = $this->fetchMultiSourcePoolDefinitionIdsParameter();
		$this->deleteSourcePoolDefinitions( $definitionIds );

		ilUtil::sendSuccess($this->lng->txt("tst_msg_source_pool_definitions_deleted"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
	}

	private function deleteSourcePoolDefinitions($definitionIds)
	{
		foreach($definitionIds as $definitionId)
		{
			$definition = $this->sourcePoolDefinitionFactory->getSourcePoolDefinitionByDefinitionId($definitionId);
			$definition->deleteFromDb();
		}

		$this->sourcePoolDefinitionList->loadDefinitions();
		$this->sourcePoolDefinitionList->reindexPositions();
		$this->sourcePoolDefinitionList->saveDefinitions();

		$this->sourcePoolDefinitionList->loadDefinitions();
		$this->stagingPool->rebuild( $this->sourcePoolDefinitionList );
		$this->sourcePoolDefinitionList->saveDefinitions();
		
		// Bugfix for mantis: 0015082
		$this->questionSetConfig->loadFromDb();
		$this->questionSetConfig->setLastQuestionSyncTimestamp(time());
		$this->questionSetConfig->saveToDb();

		$this->testOBJ->saveCompleteStatus( $this->questionSetConfig );
	}

	private function showCreateSourcePoolDefinitionFormCmd(ilTestRandomQuestionSetPoolDefinitionFormGUI $form = null)
	{
		$this->questionSetConfig->loadFromDb();

		$poolId = $this->fetchQuestionPoolIdParameter();

		$sourcePoolDefinition = $this->getSourcePoolDefinitionByAvailableQuestionPoolId($poolId);
		$availableTaxonomyIds = ilObjTaxonomy::getUsageOfObject($sourcePoolDefinition->getPoolId());

		if($form === null)
		{
			$form = $this->buildCreateSourcePoolDefinitionFormGUI();
			$form->build($sourcePoolDefinition, $availableTaxonomyIds);
		}

		$this->tpl->setContent( $this->ctrl->getHTML($form) );
	}
	
	private function saveCreateAndNewSourcePoolDefinitionFormCmd()
	{
		$this->saveCreateSourcePoolDefinitionFormCmd(true);
	}

	/**
	 * @param bool $redirect_back_to_form
	 */
	private function saveCreateSourcePoolDefinitionFormCmd($redirect_back_to_form = false)
	{
		$this->questionSetConfig->loadFromDb();

		$poolId = $this->fetchQuestionPoolIdParameter();
		$sourcePoolDefinition = $this->getSourcePoolDefinitionByAvailableQuestionPoolId($poolId);
		$availableTaxonomyIds = ilObjTaxonomy::getUsageOfObject($sourcePoolDefinition->getPoolId());

		$form = $this->buildCreateSourcePoolDefinitionFormGUI();
		$form->build($sourcePoolDefinition, $availableTaxonomyIds);

		$errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
		$form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

		if($errors)
		{
			return $this->showCreateSourcePoolDefinitionFormCmd($form);
		}

		$form->applySubmit( $sourcePoolDefinition, $availableTaxonomyIds );

		$this->sourcePoolDefinitionList->loadDefinitions();
		$sourcePoolDefinition->setSequencePosition( $this->sourcePoolDefinitionList->getNextPosition() );
		$sourcePoolDefinition->saveToDb();
		$this->sourcePoolDefinitionList->addDefinition($sourcePoolDefinition);

		$this->stagingPool->rebuild( $this->sourcePoolDefinitionList );
		$this->sourcePoolDefinitionList->saveDefinitions();

		$this->questionSetConfig->setLastQuestionSyncTimestamp(time());
		$this->questionSetConfig->saveToDb();

		$this->testOBJ->saveCompleteStatus( $this->questionSetConfig );

		if($redirect_back_to_form)
		{
			ilUtil::sendSuccess($this->lng->txt("tst_msg_random_qsc_modified_add_new_rule"), true);
			$this->ctrl->setParameter($this, 'src_pool_def_id', $sourcePoolDefinition->getId());
			$this->ctrl->setParameter($this, 'quest_pool_id', $sourcePoolDefinition->getPoolId());
			$this->ctrl->redirect($this, self::CMD_SHOW_CREATE_SRC_POOL_DEF_FORM);
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt("tst_msg_random_question_set_config_modified"), true);
			$this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
		}
	}

	private function buildCreateSourcePoolDefinitionFormGUI()
	{
		require_once 'Modules/Test/classes/forms/class.ilTestRandomQuestionSetPoolDefinitionFormGUI.php';

		$form = new ilTestRandomQuestionSetPoolDefinitionFormGUI(
			$this->ctrl, $this->lng, $this->testOBJ, $this, $this->questionSetConfig
		);

		$form->setSaveCommand(self::CMD_SAVE_CREATE_SRC_POOL_DEF_FORM);
		$form->setSaveAndNewCommand(self::CMD_SAVE_AND_NEW_CREATE_SRC_POOL_DEF_FORM);

		return $form;
	}

	private function showEditSourcePoolDefinitionFormCmd(ilTestRandomQuestionSetPoolDefinitionFormGUI $form = null)
	{
		$this->questionSetConfig->loadFromDb();

		$defId = $this->fetchSingleSourcePoolDefinitionIdParameter();
		$sourcePoolDefinition = $this->sourcePoolDefinitionFactory->getSourcePoolDefinitionByDefinitionId($defId);
		$availableTaxonomyIds = ilObjTaxonomy::getUsageOfObject($sourcePoolDefinition->getPoolId());

		if($form === null)
		{
			$form = $this->buildEditSourcePoolDefinitionFormGUI();
			$form->build($sourcePoolDefinition, $availableTaxonomyIds);
		}

		$this->tpl->setContent( $this->ctrl->getHTML($form) );
	}

	private function saveEditSourcePoolDefinitionFormCmd()
	{
		$this->questionSetConfig->loadFromDb();

		$defId = $this->fetchSingleSourcePoolDefinitionIdParameter();
		$sourcePoolDefinition = $this->sourcePoolDefinitionFactory->getSourcePoolDefinitionByDefinitionId($defId);
		$availableTaxonomyIds = ilObjTaxonomy::getUsageOfObject($sourcePoolDefinition->getPoolId());

		$form = $this->buildEditSourcePoolDefinitionFormGUI();
		$form->build($sourcePoolDefinition, $availableTaxonomyIds);

		$errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
		$form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

		if($errors)
		{
			return $this->showSourcePoolDefinitionListCmd($form);
		}

		$form->applySubmit($sourcePoolDefinition, $availableTaxonomyIds);

		$sourcePoolDefinition->saveToDb();

		$this->sourcePoolDefinitionList->loadDefinitions();
		$this->stagingPool->rebuild( $this->sourcePoolDefinitionList );

		$this->questionSetConfig->setLastQuestionSyncTimestamp(time());
		$this->questionSetConfig->saveToDb();

		$this->sourcePoolDefinitionList->saveDefinitions();

		$this->testOBJ->saveCompleteStatus( $this->questionSetConfig );

		ilUtil::sendSuccess($this->lng->txt("tst_msg_random_question_set_config_modified"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
	}

	private function buildEditSourcePoolDefinitionFormGUI()
	{
		require_once 'Modules/Test/classes/forms/class.ilTestRandomQuestionSetPoolDefinitionFormGUI.php';

		$form = new ilTestRandomQuestionSetPoolDefinitionFormGUI(
			$this->ctrl, $this->lng, $this->testOBJ, $this, $this->questionSetConfig
		);

		$form->setSaveCommand(self::CMD_SAVE_EDIT_SRC_POOL_DEF_FORM);

		return $form;
	}

	private function fetchQuestionPoolIdParameter()
	{
		if( isset($_POST['quest_pool_id']) && (int)$_POST['quest_pool_id'] )
		{
			return (int)$_POST['quest_pool_id'];
		}

		if( isset($_GET['quest_pool_id']) && (int)$_GET['quest_pool_id'] )
		{
			return (int)$_GET['quest_pool_id'];
		}

		require_once 'Modules/Test/exceptions/class.ilTestMissingQuestionPoolIdParameterException.php';
		throw new ilTestMissingQuestionPoolIdParameterException();
	}

	private function fetchSingleSourcePoolDefinitionIdParameter()
	{
		if( isset($_POST['src_pool_def_id']) && (int)$_POST['src_pool_def_id'] )
		{
			return (int)$_POST['src_pool_def_id'];
		}

		if( isset($_GET['src_pool_def_id']) && (int)$_GET['src_pool_def_id'] )
		{
			return (int)$_GET['src_pool_def_id'];
		}

		require_once 'Modules/Test/exceptions/class.ilTestMissingSourcePoolDefinitionParameterException.php';
		throw new ilTestMissingSourcePoolDefinitionParameterException();
	}

	private function fetchMultiSourcePoolDefinitionIdsParameter()
	{
		if( !isset($_POST['src_pool_def_ids']) || !is_array($_POST['src_pool_def_ids']) )
		{
			require_once 'Modules/Test/exceptions/class.ilTestMissingSourcePoolDefinitionParameterException.php';
			throw new ilTestMissingSourcePoolDefinitionParameterException();
		}

		$definitionIds = array();

		foreach($_POST['src_pool_def_ids'] as $definitionId)
		{
			$definitionId = (int)$definitionId;

			if( !$definitionId )
			{
				require_once 'Modules/Test/exceptions/class.ilTestMissingSourcePoolDefinitionParameterException.php';
				throw new ilTestMissingSourcePoolDefinitionParameterException();
			}

			$definitionIds[] = $definitionId;
		}

		return $definitionIds;
	}

	private function getSourcePoolDefinitionByAvailableQuestionPoolId($poolId)
	{
		$availablePools = $this->testOBJ->getAvailableQuestionpools(
			true, $this->questionSetConfig->arePoolsWithHomogeneousScoredQuestionsRequired(), false, true, true
		);

		if( isset($availablePools[$poolId]) )
		{
			$originalPoolData = $availablePools[$poolId];

			$originalPoolData['qpl_path'] = $this->questionSetConfig->getQuestionPoolPathString($poolId);

			return $this->sourcePoolDefinitionFactory->getSourcePoolDefinitionByOriginalPoolData($originalPoolData);
		}

		require_once 'Modules/Test/exceptions/class.ilTestQuestionPoolNotAvailableAsSourcePoolException.php';
		throw new ilTestQuestionPoolNotAvailableAsSourcePoolException();
	}

	private function handleConfigurationStateMessages($currentRequestCmd)
	{
		if( !$this->questionSetConfig->isQuestionAmountConfigComplete() )
		{
			$infoMessage = $this->lng->txt('tst_msg_rand_quest_set_incomplete_quest_amount_cfg');
			
			if( $this->isQuestionAmountConfigPerTestHintRequired($currentRequestCmd) )
			{
				$infoMessage .= '<br />'.sprintf(
					$this->lng->txt('tst_msg_rand_quest_set_change_quest_amount_here'),
					$this->buildGeneralConfigSubTabLink()
				);
			}
			elseif( $this->isQuestionAmountConfigPerPoolHintRequired($currentRequestCmd) )
			{
				$infoMessage .= '<br />'.sprintf(
					$this->lng->txt('tst_msg_rand_quest_set_change_quest_amount_here'),
					$this->buildQuestionSelectionSubTabLink()
				);
			}
		}
		elseif( !$this->questionSetConfig->hasSourcePoolDefinitions() )
		{
			$infoMessage = $this->lng->txt('tst_msg_rand_quest_set_no_src_pool_defs');
		}
		elseif( !$this->questionSetConfig->isQuestionSetBuildable() )
		{
			$infoMessage = $this->lng->txt('tst_msg_rand_quest_set_pass_not_buildable');
		}
		else
		{
			$syncDate = new ilDateTime(
				$this->questionSetConfig->getLastQuestionSyncTimestamp(), IL_CAL_UNIX
			);

			$infoMessage = sprintf(
				$this->lng->txt('tst_msg_rand_quest_set_stage_pool_last_sync'), ilDatePresentation::formatDate($syncDate)
			);

			if( !$this->testOBJ->participantDataExist() )
			{
				$infoMessage .= "<br />{$this->buildQuestionStageRebuildLink($currentRequestCmd)}";
			}
		}
		
		if( $this->isNoAvailableQuestionPoolsHintRequired($currentRequestCmd) )
		{
			$infoMessage .= '<br />'.$this->lng->txt('tst_msg_rand_quest_set_no_pools_available');
		}

		ilUtil::sendInfo($infoMessage);
	}

	/**
	 * @param $afterRebuildQuestionStageCmd
	 * @return string
	 */
	private function buildQuestionStageRebuildLink($afterRebuildQuestionStageCmd)
	{
		$this->ctrl->setParameter(
			$this, self::HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD, $afterRebuildQuestionStageCmd
		);
		
		$href = $this->ctrl->getLinkTarget($this, self::CMD_BUILD_QUESTION_STAGE);
		$label = $this->lng->txt('tst_btn_rebuild_random_question_stage');
		
		return "<a href=\"{$href}\">{$label}</a>";
	}

	private function buildGeneralConfigSubTabLink()
	{
		$href = $this->ctrl->getLinkTarget($this, self::CMD_SHOW_GENERAL_CONFIG_FORM);
		$label = $this->lng->txt('tst_rnd_quest_cfg_tab_general');
		
		return "<a href=\"{$href}\">{$label}</a>";
	}

	private function buildQuestionSelectionSubTabLink()
	{
		$href = $this->ctrl->getLinkTarget($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
		$label = $this->lng->txt('tst_rnd_quest_cfg_tab_pool');

		return "<a href=\"{$href}\">{$label}</a>";
	}

	/**
	 * @param $currentRequestCmd
	 * @return bool
	 */
	private function isNoAvailableQuestionPoolsHintRequired($currentRequestCmd)
	{
		if( $currentRequestCmd != self::CMD_SHOW_SRC_POOL_DEF_LIST )
		{
			return false;
		}
		
		if( $this->questionSetConfig->doesSelectableQuestionPoolsExist() )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param $currentRequestCmd
	 * @return bool
	 */
	private function isQuestionAmountConfigPerPoolHintRequired($currentRequestCmd)
	{
		if( $currentRequestCmd != self::CMD_SHOW_GENERAL_CONFIG_FORM )
		{
			return false;
		}

		if( !$this->questionSetConfig->isQuestionAmountConfigurationModePerPool() )
		{
			return false;
		}

		return true;
	}

	/**
	 * @param $currentRequestCmd
	 * @return bool
	 */
	private function isQuestionAmountConfigPerTestHintRequired($currentRequestCmd)
	{
		if( $currentRequestCmd != self::CMD_SHOW_SRC_POOL_DEF_LIST )
		{
			return false;
		}

		if( !$this->questionSetConfig->isQuestionAmountConfigurationModePerTest() )
		{
			return false;
		}

		return true;
	}
}
