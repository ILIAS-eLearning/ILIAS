<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestTabsManager
{
	/**
	 * @var ilObjTestCtrl
	 */
	protected $testCtrl;
	
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	
	/**
	 * @var ilAccessHandler
	 */
	protected $access;
	
	/**
	 * @var ilTestAccess
	 */
	protected $testAccess;
	
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	
	/**
	 * @var ilObjTest
	 */
	protected $testOBJ;
	
	/**
	 * @var ilTestQuestionSetConfig
	 */
	protected $testQuestionSetConfig;
	
	/**
	 * @var string|null
	 */
	protected $parentBackHref;
	
	/**
	 * @var string|null
	 */
	protected $parentBackLabel;
	
	/**
	 * @var array[string]
	 */
	protected $hiddenTabs;
	
	/**
	 * ilTestTabsManager constructor.
	 */
	public function __construct(ilObjTestCtrl $testCtrl, ilTestAccess $testAccess)
	{
		$this->testCtrl = $testCtrl;
		$this->testAccess = $testAccess;

		$this->tabs = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['ilTabs'] : $GLOBALS['ilTabs'];
		$this->access = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['ilAccess'] : $GLOBALS['ilAccess'];
		$this->lng = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['lng'] : $GLOBALS['lng'];
	}
	
	/**
	 * @return ilObjTest
	 */
	public function getTestOBJ()
	{
		return $this->testOBJ;
	}
	
	/**
	 * @param ilObjTest $testOBJ
	 */
	public function setTestOBJ(ilObjTest $testOBJ)
	{
		$this->testOBJ = $testOBJ;
	}
	
	/**
	 * @return ilTestQuestionSetConfig
	 */
	public function getTestQuestionSetConfig()
	{
		return $this->testQuestionSetConfig;
	}
	
	/**
	 * @param ilTestQuestionSetConfig $testQuestionSetConfig
	 */
	public function setTestQuestionSetConfig(ilTestQuestionSetConfig $testQuestionSetConfig)
	{
		$this->testQuestionSetConfig = $testQuestionSetConfig;
	}
	
	/**
	 * @return array
	 */
	public function getHiddenTabs()
	{
		return $this->hiddenTabs;
	}
	
	/**
	 * @param array $hiddenTabs
	 */
	public function setHiddenTabs($hiddenTabs)
	{
		$this->hiddenTabs = $hiddenTabs;
	}
	
	/**
	 * @param array $hiddenTabs
	 */
	public function resetHiddenTabs()
	{
		$this->hiddenTabs = array();
	}
	
	/**
	 * @return null|string
	 */
	public function getParentBackLabel()
	{
		return $this->parentBackLabel;
	}
	
	/**
	 * @param null|string $parentBackLabel
	 */
	public function setParentBackLabel($parentBackLabel)
	{
		$this->parentBackLabel = $parentBackLabel;
	}
	
	/**
	 * @return null|string
	 */
	public function getParentBackHref()
	{
		return $this->parentBackHref;
	}
	
	/**
	 * @param null|string $parentBackHref
	 */
	public function setParentBackHref($parentBackHref)
	{
		$this->parentBackHref = $parentBackHref;
	}
	
	/**
	 * @return null|string
	 */
	public function hasParentBackLink()
	{
		if( !is_string($this->getParentBackHref()) || !strlen($this->getParentBackHref()) )
		{
			return false;
		}
		
		if( !is_string($this->getParentBackLabel()) || !strlen($this->getParentBackLabel()) )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 */
	protected function initSettingsTemplate()
	{
		$this->resetHiddenTabs();
		
		if( $this->getTestOBJ()->getTemplate() )
		{
			require_once 'Services/Administration/classes/class.ilSettingsTemplate.php';
			
			$template = new ilSettingsTemplate(
				$this->getTestOBJ()->getTemplate(), ilObjAssessmentFolderGUI::getSettingsTemplateConfig()
			);
			
			$this->setHiddenTabs($template->getHiddenTabs());
		}
	}
	
	/**
	 * @param string $tabId
	 * @return bool
	 */
	protected function isHiddenTab($tabId)
	{
		return in_array($tabId, $this->getHiddenTabs());
	}
	
	/**
	 * @return bool
	 */
	protected function isReadAccessGranted()
	{
		return $this->access->checkAccess('read', '', $this->getTestOBJ()->getRefId());
	}
	
	/**
	 * @return bool
	 */
	protected function isWriteAccessGranted()
	{
		return $this->access->checkAccess('write', '', $this->getTestOBJ()->getRefId());
	}
	
	/**
	 * @return bool
	 */
	protected function isStatisticsAccessGranted()
	{
		return $this->access->checkAccess('tst_statistics', '', $this->getTestOBJ()->getRefId());
	}
	
	/**
	 * @return bool
	 */
	protected function isPermissionsAccessGranted()
	{
		return $this->access->checkAccess('edit_permission', '', $this->getTestOBJ()->getRefId());
	}
	
	/**
	 * @return bool
	 */
	protected function isLpAccessGranted()
	{
		include_once 'Services/Tracking/classes/class.ilLearningProgressAccess.php';
		return ilLearningProgressAccess::checkAccess($this->getTestOBJ()->getRefId());
	}
	
	/**
	 * @return bool
	 */
	protected function checkParticipantTabAccess()
	{
		if( $this->testAccess->checkManageParticipantsAccess() )
		{
			return true;
		}
		
		if( $this->testAccess->checkParticipantsResultsAccess() )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * @return bool
	 */
	protected function checkScoreParticipantsTabAccess()
	{
		return $this->testAccess->checkScoreParticipantsAccess();
	}
	
	/**
	 * @return bool
	 */
	protected function checkStatisticsTabAccess()
	{
		return $this->testAccess->checkStatisticsAccess();
	}
	
	/**
	 */
	public function perform()
	{
		if( $this->isTabsConfigSetupRequired() )
		{
			$this->initSettingsTemplate();
			$this->setupTabsGuiConfig();
		}
	}
	
	protected function isTabsConfigSetupRequired()
	{
		if (preg_match('/^ass(.*?)gui$/i', $this->testCtrl->getCtrl()->getNextClass($this)))
		{
			return false;
		}
		
		if ($this->testCtrl->getCtrl()->getNextClass($this) == 'ilassquestionpagegui')
		{
			return false;
		}
		
		if ($this->testCtrl->getCtrl()->getCmdClass() == 'iltestoutputgui')
		{
			return false;
		}
		
		if ($this->testCtrl->getCtrl()->getCmdClass() == 'iltestevalobjectiveorientedgui')
		{
			return false;
		}
		
		if ($this->testCtrl->getCtrl()->getCmdClass() == 'iltestevaluationgui')
		{
			return in_array($this->testCtrl->getCtrl()->getCmd(), array(
				'outParticipantsResultsOverview', 'outEvaluation',
				'eval_a', 'singleResults', 'detailedEvaluation'
			));
		}
		
		return true;
	}
	
	protected function setupTabsGuiConfig()
	{
		if( $this->hasParentBackLink() )
		{
			$this->tabs->setBack2Target($this->getParentBackLabel(), $this->getParentBackHref());
		}
		
		switch($this->testCtrl->getCtrl()->getCmdClass())
		{
			case 'ilmarkschemagui':
			case 'ilobjtestsettingsgeneralgui':
			case 'ilobjtestsettingsscoringresultsgui':
				
				if( $this->isWriteAccessGranted() )
				{
					$this->getSettingsSubTabs();
				}
				
				break;
		}
		
		switch($this->testCtrl->getCtrl()->getCmd())
		{
			case "resume":
			case "previous":
			case "next":
			case "summary":
			case "directfeedback":
			case "finishTest":
			case "outCorrectSolution":
			case "passDetails":
			case "showAnswersOfUser":
			case "outUserResultsOverview":
			case "backFromSummary":
			case "show_answers":
			case "setsolved":
			case "resetsolved":
			case "confirmFinish":
			case "outTestSummary":
			case "outQuestionSummary":
			case "gotoQuestion":
			case "selectImagemapRegion":
			case "confirmSubmitAnswers":
			case "finalSubmission":
			case "postpone":
			case "outUserPassDetails":
			case "checkPassword":
			case "exportCertificate":
			case "finishListOfAnswers":
			case "backConfirmFinish":
			case "showFinalStatement":
				return;
				break;
			case "browseForQuestions":
			case "filter":
			case "resetFilter":
			case "resetTextFilter":
			case "insertQuestions":
				// #8497: resetfilter is also used in lp
				if($this->testCtrl->getCtrl()->getNextClass($this) != "illearningprogressgui")
				{
					$this->getBrowseForQuestionsTab();
					return;
				}
				break;
			case "scoring":
			case "certificate":
			case "certificateservice":
			case "certificateImport":
			case "certificateUpload":
			case "certificateEditor":
			case "certificateDelete":
			case "certificateSave":
			case "defaults":
			case "deleteDefaults":
			case "addDefaults":
			case "applyDefaults":
			case "inviteParticipants":
			case "searchParticipants":
				if( $this->isWriteAccessGranted() && in_array($this->testCtrl->getCtrl()->getCmdClass(), array('ilobjtestgui', 'ilcertificategui')) )
				{
					$this->getSettingsSubTabs();
				}
				break;
			case "export":
			case "print":
				break;
			case "statistics":
			case "eval_a":
			case "detailedEvaluation":
			case "outEvaluation":
			case "singleResults":
			case "exportEvaluation":
			case "evalUserDetail":
			case "outStatisticsResultsOverview":
			case "statisticsPassDetails":
				$this->getStatisticsSubTabs();
				break;
		}
		
		// questions tab
		if ($this->isWriteAccessGranted() && !$this->isHiddenTab('assQuestions'))
		{
			$force_active = ($_GET["up"] != "" || $_GET["down"] != "")
				? true
				: false;
			if (!$force_active)
			{
				if ($_GET["browse"] == 1) $force_active = true;
			}
			
			switch( $this->getTestOBJ()->getQuestionSetType() )
			{
				case ilObjTest::QUESTION_SET_TYPE_FIXED:
					$target = $this->testCtrl->getCtrl()->getLinkTargetByClass('iltestexpresspageobjectgui','showPage');
					break;
				
				case ilObjTest::QUESTION_SET_TYPE_RANDOM:
					$target = $this->testCtrl->getCtrl()->getLinkTargetByClass('ilTestRandomQuestionSetConfigGUI');
					break;
				
				case ilObjTest::QUESTION_SET_TYPE_DYNAMIC:
					$target = $this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestDynamicQuestionSetConfigGUI');
					break;
					
				default: $target = '';
			} 
			
			$this->tabs->addTarget("assQuestions",
				//$this->testCtrl->getCtrl()->getLinkTarget($this,'questions'),
				$target,
				array("questions", "browseForQuestions", "questionBrowser", "createQuestion",
					"randomselect", "filter", "resetFilter", "insertQuestions",
					"back", "createRandomSelection", "cancelRandomSelect",
					"insertRandomSelection", "removeQuestions", "moveQuestions",
					"insertQuestionsBefore", "insertQuestionsAfter", "confirmRemoveQuestions",
					"cancelRemoveQuestions", "executeCreateQuestion", "cancelCreateQuestion",
					"addQuestionpool", "saveRandomQuestions", "saveQuestionSelectionMode", "print",
					"addsource", "removesource", "randomQuestions"),
				"", "", $force_active);
		}
		
		// info tab
		if ($this->isReadAccessGranted() && !$this->isHiddenTab('info_short'))
		{
			$this->tabs->addTarget("info_short",
				$this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestGUI','infoScreen'),
				array("infoScreen", "outIntroductionPage", "showSummary",
					"setAnonymousId", "outUserListOfAnswerPasses", "redirectToInfoScreen"));
		}
		
		// settings tab
		if( $this->isWriteAccessGranted() )
		{
			if (!$this->isHiddenTab('settings'))
			{
				$settingsCommands = array(
					"marks", "showMarkSchema","addMarkStep", "deleteMarkSteps", "addSimpleMarkSchema", "saveMarks",
					"certificate", "certificateEditor", "certificateRemoveBackground", "certificateSave",
					"certificatePreview", "certificateDelete", "certificateUpload", "certificateImport",
					"scoring", "defaults", "addDefaults", "deleteDefaults", "applyDefaults",
					"inviteParticipants", "saveFixedParticipantsStatus", "searchParticipants", "addParticipants" // ARE THEY RIGHT HERE
				);
				
				require_once 'Modules/Test/classes/class.ilObjTestSettingsGeneralGUI.php';
				$reflection = new ReflectionClass('ilObjTestSettingsGeneralGUI');
				foreach($reflection->getConstants() as $name => $value)
					if(substr($name, 0, 4) == 'CMD_') $settingsCommands[] = $value;
				
				require_once 'Modules/Test/classes/class.ilObjTestSettingsScoringResultsGUI.php';
				$reflection = new ReflectionClass('ilObjTestSettingsScoringResultsGUI');
				foreach($reflection->getConstants() as $name => $value)
					if(substr($name, 0, 4) == 'CMD_') $settingsCommands[] = $value;
				
				$settingsCommands[] = ""; // DO NOT KNOW WHAT THIS IS DOING, BUT IT'S REQUIRED
				
				$this->tabs->addTarget("settings",
					$this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestSettingsGeneralGUI'),
					$settingsCommands,
					array("ilmarkschemagui", "ilobjtestsettingsgeneralgui", "ilobjtestsettingsscoringresultsgui", "ilobjtestgui", "ilcertificategui")
				);
			}
			
			// skill service
			if( $this->getTestOBJ()->isSkillServiceEnabled() && ilObjTest::isSkillManagementGloballyActivated() )
			{
				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentsGUI.php';
				
				$link = $this->testCtrl->getCtrl()->getLinkTargetByClass(
					array('ilTestSkillAdministrationGUI', 'ilAssQuestionSkillAssignmentsGUI'),
					ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
				);
				
				$this->tabs->addTarget('tst_tab_competences', $link, array(), array());
			}
			
			if( $this->checkParticipantTabAccess() && !$this->isHiddenTab('participants') )
			{
				// participants
				$this->tabs->addTarget("participants",
					$this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestGUI','participants'),
					array(
						"participants", "saveClientIP",
						"removeParticipant",
						"showParticipantAnswersForAuthor",
						"deleteAllUserResults",
						"cancelDeleteAllUserData", "deleteSingleUserResults",
						"outParticipantsResultsOverview", "outParticipantsPassDetails",
						"showPassOverview", "showUserAnswers", "participantsAction",
						"showDetailedResults",
						'timing', 'timingOverview', 'npResetFilter', 'npSetFilter', 'showTimingForm'
					),
					""
				);
			}
		}
		
		if($this->isLpAccessGranted() && !$this->isHiddenTab('learning_progress'))
		{
			$this->tabs->addTarget('learning_progress',
				$this->testCtrl->getCtrl()->getLinkTargetByClass(array('illearningprogressgui'),''),
				'',
				array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}
		
		if( $this->checkScoreParticipantsTabAccess()  && !$this->isHiddenTab('manscoring') )
		{
			include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
			$scoring = ilObjAssessmentFolder::_getManualScoring();
			if (count($scoring))
			{
				// scoring tab
				$this->tabs->addTarget(
					"manscoring", $this->testCtrl->getCtrl()->getLinkTargetByClass('ilTestScoringByQuestionsGUI', 'showManScoringByQuestionParticipantsTable'),
					array(
						'showManScoringParticipantsTable', 'applyManScoringParticipantsFilter', 'resetManScoringParticipantsFilter', 'showManScoringParticipantScreen',
						'showManScoringByQuestionParticipantsTable', 'applyManScoringByQuestionFilter', 'resetManScoringByQuestionFilter', 'saveManScoringByQuestion'
					
					), ''
				);
			}
		}
		
		// Scoring Adjustment
		$setting = new ilSetting('assessment');
		$scoring_adjust_active = (bool) $setting->get('assessment_adjustments_enabled', false);
		if ($this->isWriteAccessGranted() && $scoring_adjust_active && !$this->isHiddenTab('scoringadjust'))
		{
			// scoring tab
			$this->tabs->addTarget(
				"scoringadjust", $this->testCtrl->getCtrl()->getLinkTargetByClass('ilScoringAdjustmentGUI', 'showquestionlist'),
				array(
					'showquestionlist',
					'savescoringfortest',
					'adjustscoringfortest'
				), ''
			);
		}
		
		if ($this->checkStatisticsTabAccess()  && !$this->isHiddenTab('statistics'))
		{
			// statistics tab
			$this->tabs->addTarget(
				"statistics",
				$this->testCtrl->getCtrl()->getLinkTargetByClass("iltestevaluationgui", "outEvaluation"),
				array(
					"statistics", "outEvaluation", "exportEvaluation", "detailedEvaluation", "eval_a", "evalUserDetail",
					"passDetails", "outStatisticsResultsOverview", "statisticsPassDetails", "singleResults"
				),
				""
			);
		}
		
		if ($this->isWriteAccessGranted())
		{
			if (!$this->isHiddenTab('history')) {
				
				// history
				$this->tabs->addTarget("history",
					$this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestGUI','history'),
					"history", "");
			}
			
			if (!$this->isHiddenTab('meta_data')) {
				// meta data
				include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
				$mdgui = new ilObjectMetaDataGUI($this->getTestOBJ());
				$mdtab = $mdgui->getTab();
				if($mdtab)
				{
					$this->tabs->addTarget("meta_data",
						$mdtab,
						"", "ilmdeditorgui");
				}
			}
			
			if(!$this->isHiddenTab('export'))
			{
				// export tab
				$this->tabs->addTarget(
					"export",
					$this->testCtrl->getCtrl()->getLinkTargetByClass('iltestexportgui' ,''),
					'',
					array('iltestexportgui')
				);
			}
		}
		
		if ($this->isPermissionsAccessGranted() && !$this->isHiddenTab('permissions'))
		{
			$this->tabs->addTarget("perm_settings",
				$this->testCtrl->getCtrl()->getLinkTargetByClass(array('ilObjTestGUI','ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
		
		if( $this->getTestQuestionSetConfig()->areDepenciesBroken() )
		{
			$hideTabs = $this->getTestQuestionSetConfig()->getHiddenTabsOnBrokenDepencies();
			
			foreach($hideTabs as $tabId)
			{
				$this->tabs->removeTab($tabId);
			}
		}
	}
	
	protected function getBrowseForQuestionsTab()
	{
		if ($this->isWriteAccessGranted())
		{
			$this->testCtrl->getCtrl()->saveParameterByClass($this->testCtrl->getCtrl()->getCmdClass(), 'q_id');
			// edit page
			$this->tabs->setBackTarget($this->lng->txt("backtocallingtest"), $this->testCtrl->getCtrl()->getLinkTargetByClass($this->testCtrl->getCtrl()->getCmdClass(), "questions"));
			$this->tabs->addTarget("tst_browse_for_questions",
				$this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestGUI', "browseForQuestions"),
				array("browseForQuestions", "filter", "resetFilter", "resetTextFilter", "insertQuestions"),
				"", "", TRUE
			);
		}
	}
	
	protected function getRandomQuestionsTab()
	{
		if ($this->isWriteAccessGranted())
		{
			// edit page
			$this->tabs->setBackTarget($this->lng->txt("backtocallingtest"), $this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestGUI', "questions"));
			$this->tabs->addTarget("random_selection",
				$this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestGUI', "randomQuestions"),
				array("randomQuestions"),
				"", ""
			);
		}
	}
	
	protected function getQuestionsSubTabs()
	{
		$this->tabs->activateTab('assQuestions');
		$a_cmd = $this->testCtrl->getCtrl()->getCmd();
		
		if (!$this->getTestOBJ()->isRandomTest())
		{
			$questions_per_page = ($a_cmd == 'questions_per_page' || ($a_cmd == 'removeQuestions' && $_REQUEST['test_express_mode'])) ? true : false;
			
			$this->tabs->addSubTabTarget(
				"questions_per_page_view",
				$this->testCtrl->getCtrl()->getLinkTargetByClass('iltestexpresspageobjectgui', 'showPage'),
				"", "", "", $questions_per_page);
		}
		include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
		$template = new ilSettingsTemplate($this->getTestOBJ()->getTemplate(), ilObjAssessmentFolderGUI::getSettingsTemplateConfig());
		
		if (!$this->isHiddenTab('questions')) {
			// questions subtab
			$this->tabs->addSubTabTarget("edit_test_questions",
				$this->testCtrl->getCtrl()->getLinkTarget($this,'questions'),
				array("questions", "browseForQuestions", "questionxBrowser", "createQuestion",
					"randomselect", "filter", "resetFilter", "insertQuestions",
					"back", "createRandomSelection", "cancelRandomSelect",
					"insertRandomSelection", "removeQuestions", "moveQuestions",
					"insertQuestionsBefore", "insertQuestionsAfter", "confirmRemoveQuestions",
					"cancelRemoveQuestions", "executeCreateQuestion", "cancelCreateQuestion",
					"addQuestionpool", "saveRandomQuestions", "saveQuestionSelectionMode"),
				"");
			
			if (in_array($a_cmd, array('questions', 'createQuestion')) || ($a_cmd == 'removeQuestions' && !$_REQUEST['test_express_mode']))
				$this->tabs->activateSubTab('edit_test_questions');
		}
		
		// print view subtab
		if (!$this->getTestOBJ()->isRandomTest())
		{
			$this->tabs->addSubTabTarget("print_view",
				$this->testCtrl->getCtrl()->getLinkTarget($this,'print'),
				"print", "", "", $this->testCtrl->getCtrl()->getCmd() == 'print');
			$this->tabs->addSubTabTarget('review_view',
				$this->testCtrl->getCtrl()->getLinkTarget($this, 'review'),
				'review', '', '', $this->testCtrl->getCtrl()->getCmd() == 'review');
		}
	}
	
	protected function getStatisticsSubTabs()
	{
		// user results subtab
		$this->tabs->addSubTabTarget("eval_all_users",
			$this->testCtrl->getCtrl()->getLinkTargetByClass("iltestevaluationgui", "outEvaluation"),
			array("outEvaluation", "detailedEvaluation", "exportEvaluation", "evalUserDetail", "passDetails",
				"outStatisticsResultsOverview", "statisticsPassDetails")
			, "");
		
		// aggregated results subtab
		$this->tabs->addSubTabTarget("tst_results_aggregated",
			$this->testCtrl->getCtrl()->getLinkTargetByClass("iltestevaluationgui", "eval_a"),
			array("eval_a"),
			"", "");
		
		// question export
		$this->tabs->addSubTabTarget("tst_single_results",
			$this->testCtrl->getCtrl()->getLinkTargetByClass("iltestevaluationgui", "singleResults"),
			array("singleResults"),
			"", "");
	}
	
	protected function getSettingsSubTabs()
	{
		// general subtab
		$this->tabs->addSubTabTarget('general', $this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestSettingsGeneralGUI'),
			'',											// auto activation regardless from cmd
			array('ilobjtestsettingsgeneralgui')			// auto activation for ilObjTestSettingsGeneralGUI
		);
		
		if(!$this->isHiddenTab('mark_schema'))
		{
			$this->tabs->addSubTabTarget(
				'mark_schema',
				$this->testCtrl->getCtrl()->getLinkTargetByClass('ilmarkschemagui', 'showMarkSchema'),
				'',
				array('ilmarkschemagui')
			);
		}
		
		// scoring subtab
		$this->tabs->addSubTabTarget('scoring', $this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestSettingsScoringResultsGUI'),
			'',                                             // auto activation regardless from cmd
			array('ilobjtestsettingsscoringresultsgui')     // auto activation for ilObjTestSettingsScoringResultsGUI
		);
		
		// certificate subtab
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		if( !$this->isHiddenTab('certificate') && ilCertificate::isActive() )
		{
			$this->tabs->addSubTabTarget(
				"certificate",
				$this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestGUI','certificate'),
				array("certificate", "certificateEditor", "certificateRemoveBackground", "certificateSave",
					"certificatePreview", "certificateDelete", "certificateUpload", "certificateImport"),
				array("", "ilobjtestgui", "ilcertificategui")
			);
		}
		
		if (!$this->isHiddenTab('defaults')) {
			// defaults subtab
			$this->tabs->addSubTabTarget(
				"tst_default_settings",
				$this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestGUI', "defaults"),
				array("defaults", "deleteDefaults", "addDefaults", "applyDefaults"),
				array("", "ilobjtestgui", "ilcertificategui")
			);
		}
	}
	
	protected function getParticipantsSubTabs()
	{
		// participants subtab
		$this->tabs->addSubTabTarget( "participants",
			$this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestGUI','participants'),
			array(
				"participants", "saveClientIP",
				"removeParticipant",
				"showParticipantAnswersForAuthor",
				"deleteAllUserResults",
				"cancelDeleteAllUserData", "deleteSingleUserResults",
				"outParticipantsResultsOverview", "outParticipantsPassDetails",
				"showPassOverview", "showUserAnswers", "participantsAction",
				"showDetailedResults",
				'npResetFilter', 'npSetFilter'
			),
			""
		);
		
		if( !$this->testAccess->checkManageParticipantsAccess() )
		{
			return;
		}
		
		if( !$this->getTestQuestionSetConfig()->areDepenciesBroken() )
		{
			if($this->getTestOBJ()->getProcessingTimeInSeconds() > 0 && $this->getTestOBJ()->getNrOfTries() == 1)
			{
				// extratime subtab
				$this->tabs->addSubTabTarget( "timing",
					$this->testCtrl->getCtrl()->getLinkTargetByClass('ilObjTestGUI','timingOverview'),
					array("timing", "timingOverview"), "", ""
				);
			}
		}
	}
}