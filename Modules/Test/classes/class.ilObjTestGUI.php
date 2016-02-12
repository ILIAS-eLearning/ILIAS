<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/exceptions/class.ilTestException.php';
require_once './Services/Object/classes/class.ilObjectGUI.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/Test/classes/class.ilObjAssessmentFolderGUI.php';
require_once './Modules/Test/classes/class.ilObjAssessmentFolder.php';
require_once './Modules/Test/classes/class.ilTestExpressPage.php';

/**
 * Class ilObjTestGUI
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 * 
 * @version		$Id$
 *
 * @ilCtrl_Calls ilObjTestGUI: ilObjCourseGUI, ilObjectMetaDataGUI, ilCertificateGUI, ilPermissionGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestPlayerFixedQuestionSetGUI, ilTestPlayerRandomQuestionSetGUI, ilTestPlayerDynamicQuestionSetGUI
 * @ilCtrl_Calls ilObjTestGUI: ilLearningProgressGUI, ilMarkSchemaGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestEvaluationGUI, ilTestEvalObjectiveOrientedGUI
 * @ilCtrl_Calls ilObjTestGUI: ilAssGenFeedbackPageGUI, ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilObjTestGUI: ilInfoScreenGUI, ilShopPurchaseGUI, ilObjectCopyGUI, ilTestScoringGUI
 * @ilCtrl_Calls ilObjTestGUI: ilRepositorySearchGUI, ilScoringAdjustmentGUI, ilTestExportGUI
 * @ilCtrl_Calls ilObjTestGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: assOrderingQuestionGUI, assImagemapQuestionGUI, assJavaAppletGUI
 * @ilCtrl_Calls ilObjTestGUI: assNumericGUI, assErrorTextGUI, ilTestScoringByQuestionsGUI
 * @ilCtrl_Calls ilObjTestGUI: assTextSubsetGUI, assOrderingHorizontalGUI, ilTestToplistGUI
 * @ilCtrl_Calls ilObjTestGUI: assSingleChoiceGUI, assFileUploadGUI, assTextQuestionGUI, assFlashQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestExpressPageObjectGUI, ilPageEditorGUI, ilAssQuestionPageGUI
 * @ilCtrl_Calls ilObjTestGUI: ilObjQuestionPoolGUI, ilEditClipboardGUI
 * @ilCtrl_Calls ilObjTestGUI: ilObjTestSettingsGeneralGUI, ilObjTestSettingsScoringResultsGUI
 * @ilCtrl_Calls ilObjTestGUI: ilCommonActionDispatcherGUI, ilObjTestDynamicQuestionSetConfigGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestRandomQuestionSetConfigGUI
 * @ilCtrl_Calls ilObjTestGUI: ilAssQuestionHintsGUI, ilAssQuestionFeedbackEditingGUI, ilLocalUnitConfigurationGUI, assFormulaQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestPassDetailsOverviewTableGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestResultsToolbarGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestSettingsChangeConfirmationGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestSkillAdministrationGUI, ilTestSkillEvaluationGUI
 * @ilCtrl_Calls ilObjTestGUI: ilAssQuestionPreviewGUI
 * @ilCtrl_Calls ilObjTestGUI: assKprimChoiceGUI, assLongMenuGUI
 *
 * @ingroup ModulesTest
 */
class ilObjTestGUI extends ilObjectGUI
{
	private static $infoScreenChildClasses = array(
		'ilpublicuserprofilegui', 'ilobjportfoliogui'
	);
	
	/** @var ilObjTest $object */
	public $object = null;

	/** @var ilTestQuestionSetConfigFactory $testQuestionSetConfigFactory Factory for question set config. */
	private $testQuestionSetConfigFactory = null;
	
	/** @var ilTestPlayerFactory $testPlayerFactory Factory for test player. */
	private $testPlayerFactory = null;
	
	/** @var ilTestSessionFactory $testSessionFactory Factory for test session. */
	private $testSessionFactory = null;
	
	/** @var ilTestSequenceFactory $testSequenceFactory Factory for test sequence. */
	private $testSequenceFactory = null;
	
	/**
	 * @var ilTestObjectiveOrientedContainer
	 */
	private $objectiveOrientedContainer;

	/**
	 * Constructor
	 * @access public
	 */
	function ilObjTestGUI()
	{
		global $lng, $ilCtrl, $ilDB, $ilPluginAdmin, $tree;
		$lng->loadLanguageModule("assessment");
		$this->type = "tst";
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id", "test_ref_id", "calling_test", "test_express_mode", "q_id"));
		$this->ilObjectGUI("",$_GET["ref_id"], true, false);

		if( $this->object instanceof ilObjTest )
		{
			require_once 'Modules/Test/classes/class.ilTestQuestionSetConfigFactory.php';
			$this->testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $ilPluginAdmin, $this->object);
			
			require_once 'Modules/Test/classes/class.ilTestPlayerFactory.php';
			$this->testPlayerFactory = new ilTestPlayerFactory($this->object);

			require_once 'Modules/Test/classes/class.ilTestSessionFactory.php';
			$this->testSessionFactory = new ilTestSessionFactory($this->object);

			require_once 'Modules/Test/classes/class.ilTestSequenceFactory.php';
			$this->testSequenceFactory = new ilTestSequenceFactory($ilDB, $lng, $ilPluginAdmin, $this->object);
		}
		
		require_once 'Modules/Test/classes/class.ilTestObjectiveOrientedContainer.php';
		$this->objectiveOrientedContainer = new ilTestObjectiveOrientedContainer();
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		global $ilAccess, $ilNavigationHistory, $ilCtrl, $ilErr, $tpl, $lng, $ilTabs, $ilPluginAdmin, $ilDB, $tree, $ilias, $ilUser;

		if((!$ilAccess->checkAccess("read", "", $_GET["ref_id"])))
		{
			$ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
		}

		$cmd = $this->ctrl->getCmd("infoScreen");

		$cmdsDisabledDueToOfflineStatus = array(
			'resumePlayer', 'resumePlayer', 'outUserResultsOverview', 'outUserListOfAnswerPasses'
		);

		if(!$this->getCreationMode() && !$this->object->isOnline() && in_array($cmd, $cmdsDisabledDueToOfflineStatus))
		{
			$cmd = 'infoScreen';
		}

		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->setReturn($this, "infoScreen");

		if(method_exists($this->object, "getTestStyleLocation")) $this->tpl->addCss($this->object->getTestStyleLocation("output"), "screen");

		// add entry to navigation history
		if(!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"])
		)
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"ilias.php?baseClass=ilObjTestGUI&cmd=infoScreen&ref_id=" . $_GET["ref_id"], "tst");
		}

		if(!$this->getCreationMode())
		{
			if(IS_PAYMENT_ENABLED)
			{
				require_once 'Services/Payment/classes/class.ilPaymentObject.php';
				if(ilPaymentObject::_requiresPurchaseToAccess($this->object->getRefId(), $type = (isset($_GET['purchasetype']) ? $_GET['purchasetype'] : NULL)))
				{
					$this->setLocator();
					$this->tpl->getStandardTemplate();

					include_once 'Services/Payment/classes/class.ilShopPurchaseGUI.php';
					$pp  = new ilShopPurchaseGUI((int)$_GET['ref_id']);
					$ret = $this->ctrl->forwardCommand($pp);
					$this->tpl->show();
					exit();
				}
			}
		}

		// elba hack for storing question id for inserting new question after
		if($_REQUEST['prev_qid'])
		{
			global $___prev_question_id;
			$___prev_question_id = $_REQUEST['prev_qid'];
			$this->ctrl->setParameter($this, 'prev_qid', $_REQUEST['prev_qid']);
		}

		if( !$this->getCreationMode() && $this->testQuestionSetConfigFactory->getQuestionSetConfig()->areDepenciesBroken() )
		{
			if( !$this->testQuestionSetConfigFactory->getQuestionSetConfig()->isValidRequestOnBrokenQuestionSetDepencies($next_class, $cmd) )
			{
				$this->ctrl->redirectByClass('ilObjTestGUI', 'infoScreen');
			}
		}
		
		$this->determineObjectiveOrientedContainer();
		
		switch($next_class)
		{
			case 'iltestexportgui':
				if(!$ilAccess->checkAccess('write', '', $this->ref_id))
				{
					$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
				}

				$this->prepareOutput();
				$this->addHeaderAction();
				require_once 'Modules/Test/classes/class.ilTestExportGUI.php';
				$ilCtrl->forwardCommand(new ilTestExportGUI($this));
				break;

			case "ilinfoscreengui":
				$this->prepareOutput();
				$this->addHeaderAction();
				$this->infoScreen(); // forwards command
				break;
			case 'ilobjectmetadatagui':
				if(!$ilAccess->checkAccess('write', '', $this->object->getRefId()))
				{
					$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
				}

				$this->prepareOutput();
				$this->addHeaderAction();
				include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
				$md_gui = new ilObjectMetaDataGUI($this->object);	
				$this->ctrl->forwardCommand($md_gui);
				break;

			case "iltestplayerfixedquestionsetgui":
				require_once "./Modules/Test/classes/class.ilTestPlayerFixedQuestionSetGUI.php";
				if(!$this->object->getKioskMode()) $this->prepareOutput();
				$gui = new ilTestPlayerFixedQuestionSetGUI($this->object);
				$gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
				$this->ctrl->forwardCommand($gui);
				break;

			case "iltestplayerrandomquestionsetgui":
				require_once "./Modules/Test/classes/class.ilTestPlayerRandomQuestionSetGUI.php";
				if(!$this->object->getKioskMode()) $this->prepareOutput();
				$gui = new ilTestPlayerRandomQuestionSetGUI($this->object);
				$gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
				$this->ctrl->forwardCommand($gui);
				break;

			case "iltestplayerdynamicquestionsetgui":
				require_once "./Modules/Test/classes/class.ilTestPlayerDynamicQuestionSetGUI.php";
				if (!$this->object->getKioskMode()) $this->prepareOutput();
				$gui = new ilTestPlayerDynamicQuestionSetGUI($this->object);
				$gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
				$this->ctrl->forwardCommand($gui);
				break;

			case "iltestevaluationgui":
				$this->forwardToEvaluationGUI();
				break;

			case "iltestevalobjectiveorientedgui":
				$this->forwardToEvalObjectiveOrientedGUI();
				break;

			case "iltestservicegui":
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once "./Modules/Test/classes/class.ilTestServiceGUI.php";
				$serviceGUI =& new ilTestServiceGUI($this->object);
				$this->ctrl->forwardCommand($serviceGUI);
				break;

			case 'ilpermissiongui':
				$this->prepareOutput();
				$this->addHeaderAction();
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret      =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case "illearningprogressgui":
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				$new_gui =& new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $this->object->getRefId());
				$this->ctrl->forwardCommand($new_gui);

				break;

			case "ilcertificategui":
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
				require_once "./Modules/Test/classes/class.ilTestCertificateAdapter.php";
				$output_gui = new ilCertificateGUI(new ilTestCertificateAdapter($this->object));
				$this->ctrl->forwardCommand($output_gui);
				break;

			case "iltestscoringgui":
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once "./Modules/Test/classes/class.ilTestScoringGUI.php";
				$output_gui = new ilTestScoringGUI($this->object);
				$this->ctrl->forwardCommand($output_gui);
				break;

			case 'ilmarkschemagui':
				if(!$ilAccess->checkAccess('write', '', $this->object->getRefId()))
				{
					ilUtil::sendInfo($this->lng->txt('cannot_edit_test'), true);
					$this->ctrl->redirect($this, 'infoScreen');
				}
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once 'Modules/Test/classes/class.ilMarkSchemaGUI.php';
				$mark_schema_gui = new ilMarkSchemaGUI($this->object);
				$this->ctrl->forwardCommand($mark_schema_gui);
				break;

			case 'iltestscoringbyquestionsgui':
				$this->prepareOutput();
				$this->addHeaderAction();
				include_once 'Modules/Test/classes/class.ilTestScoringByQuestionsGUI.php';
				$output_gui = new ilTestScoringByQuestionsGUI($this->object);
				$this->ctrl->forwardCommand($output_gui);
				break;
			
			case 'ilobjtestsettingsgeneralgui':
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once 'Modules/Test/classes/class.ilObjTestSettingsGeneralGUI.php';
				$gui = new ilObjTestSettingsGeneralGUI(
						$this->ctrl, $ilAccess, $this->lng, $this->tpl, $this->tree, $ilDB, $ilPluginAdmin, $ilUser, $this
				);
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ilobjtestsettingsscoringresultsgui':
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once 'Modules/Test/classes/class.ilObjTestSettingsScoringResultsGUI.php';
				$gui = new ilObjTestSettingsScoringResultsGUI(
					$this->ctrl, $ilAccess, $this->lng, $this->tpl, $this->tree, $ilDB, $ilPluginAdmin, $this
				);
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ilobjtestdynamicquestionsetconfiggui':
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once 'Modules/Test/classes/class.ilObjTestDynamicQuestionSetConfigGUI.php';
				$gui = new ilObjTestDynamicQuestionSetConfigGUI($this->ctrl, $ilAccess, $ilTabs, $this->lng, $this->tpl, $ilDB, $tree, $ilPluginAdmin, $this->object);
				$this->ctrl->forwardCommand($gui);
				break;
			
			case 'iltestrandomquestionsetconfiggui':
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetConfigGUI.php';
				$gui = new ilTestRandomQuestionSetConfigGUI($this->ctrl, $ilAccess, $ilTabs, $this->lng, $this->tpl, $ilDB, $tree, $ilPluginAdmin, $this->object);
				$this->ctrl->forwardCommand($gui);
				break;

			case 'iltestskilladministrationgui':
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once 'Modules/Test/classes/class.ilTestSkillAdministrationGUI.php';
				$gui = new ilTestSkillAdministrationGUI($ilias, $this->ctrl, $ilAccess, $ilTabs, $this->tpl, $this->lng, $ilDB, $tree, $ilPluginAdmin, $this->object, $this->ref_id);
				$this->ctrl->forwardCommand($gui);
				break;

			case 'iltestskillevaluationgui':
				$this->prepareOutput();
				$this->addHeaderAction();
				
				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
				if( $this->object->isDynamicTest() )
				{
					require_once 'Modules/Test/classes/class.ilObjTestDynamicQuestionSetConfig.php';
					$dynamicQuestionSetConfig = new ilObjTestDynamicQuestionSetConfig($tree, $ilDB, $ilPluginAdmin, $this->object);
					$dynamicQuestionSetConfig->loadFromDb();
					$questionList = new ilAssQuestionList($ilDB, $this->lng, $ilPluginAdmin);
					$questionList->setParentObjId($dynamicQuestionSetConfig->getSourceQuestionPoolId());
					$questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_ORIGINALS);
				}
				else
				{
					$questionList = new ilAssQuestionList($ilDB, $this->lng, $ilPluginAdmin);
					$questionList->setParentObjId($this->object->getId());
					$questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);
				}
				$questionList->load();

				require_once 'Modules/Test/classes/class.ilTestSessionFactory.php';
				$testSessionFactory = new ilTestSessionFactory($this->object);
				$testSession = $testSessionFactory->getSession();
				$testResults = $this->object->getTestResult($testSession->getActiveId(), $testSession->getPass(), true);

				require_once 'Modules/Test/classes/class.ilTestSkillEvaluationGUI.php';
				$gui = new ilTestSkillEvaluationGUI($this->ctrl, $ilTabs, $this->tpl, $this->lng, $ilDB, $this->object->getTestId(),$this->object->getRefId(), $this->object->getId());
				$gui->setQuestionList($questionList);
				$gui->setTestSession($testSession);
				$gui->setTestResults($testResults);
				$gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ilobjectcopygui':
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('tst');
				$this->ctrl->forwardCommand($cp);
				break;

			case 'ilrepositorysearchgui':
				$this->prepareOutput();
				$this->addHeaderAction();
				require_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setCallback($this,
					'addParticipantsObject',
					array()
				);

				// Set tabs
				$this->ctrl->setReturn($this, 'participants');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				$this->tabs_gui->setTabActive('participants');
				break;

			case 'ilpageeditorgui':
			case 'iltestexpresspageobjectgui':

				require_once 'Modules/TestQuestionPool/classes/class.ilAssIncompleteQuestionPurger.php';
				$incompleteQuestionPurger = new ilAssIncompleteQuestionPurger($ilDB);
				$incompleteQuestionPurger->setOwnerId($ilUser->getId());
				$incompleteQuestionPurger->purge();
				
				$qid = $_REQUEST['q_id'];

				// :FIXME: does not work
				// $this->ctrl->saveParameterByClass(array('iltestexpresspageobjectgui', 'assorderingquestiongui', 'ilpageeditorgui', 'ilpcquestion', 'ilpcquestiongui'), 'test_express_mode');

				if(!$qid || $qid == 'Array')
				{
					$questions = $this->object->getQuestionTitlesAndIndexes();
					if(!is_array($questions))
						$questions = array();

					$keys = array_keys($questions);
					$qid  = $keys[0];

					$_REQUEST['q_id'] = $qid;
					$_GET['q_id']     = $qid;
					$_POST['q_id']    = $qid;
				}

				$this->prepareOutput();
				if(!in_array($cmd, array('addQuestion', 'browseForQuestions')))
				{
					$this->buildPageViewToolbar($qid);
				}

				if(!$qid || in_array($cmd, array('insertQuestions', 'browseForQuestions')))
				{
					require_once "./Modules/Test/classes/class.ilTestExpressPageObjectGUI.php";
					$pageObject              = new ilTestExpressPageObjectGUI (0);
					$pageObject->test_object = $this->object;
					$ret                     =& $this->ctrl->forwardCommand($pageObject);
					break;
				}
				require_once "./Services/Style/classes/class.ilObjStyleSheet.php";
				$this->tpl->setCurrentBlock("ContentStyle");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$this->tpl->parseCurrentBlock();

				// syntax style
				$this->tpl->setCurrentBlock("SyntaxStyle");
				$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
					ilObjStyleSheet::getSyntaxStylePath());
				$this->tpl->parseCurrentBlock();
				require_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";

				$q_gui = assQuestionGUI::_getQuestionGUI("", $qid);
				if(!($q_gui instanceof assQuestionGUI))
				{
					$this->ctrl->setParameterByClass('iltestexpresspageobjectgui', 'q_id', '');
					$this->ctrl->redirectByClass('iltestexpresspageobjectgui', $this->ctrl->getCmd());
				}

				$q_gui->outAdditionalOutput();
				$q_gui->object->setObjId($this->object->getId());
				
				$q_gui->setTargetGuiClass(null);
				$q_gui->setQuestionActionCmd(null);
				
				$question = $q_gui->object;
				$this->ctrl->saveParameter($this, "q_id");

				#$this->lng->loadLanguageModule("content");
				$this->ctrl->setReturnByClass("ilTestExpressPageObjectGUI", "view");
				$this->ctrl->setReturn($this, "questions");

				require_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPage.php";
				require_once "./Modules/Test/classes/class.ilTestExpressPageObjectGUI.php";

				$page_gui = new ilTestExpressPageObjectGUI($qid);
				$page_gui->test_object = $this->object;
				$page_gui->setEditPreview(true);
				$page_gui->setEnabledTabs(false);
				if(strlen($this->ctrl->getCmd()) == 0)
				{
					$this->ctrl->setCmdClass(get_class($page_gui));
					$this->ctrl->setCmd("preview");
				}

				$page_gui->setQuestionHTML(array($q_gui->object->getId() => $q_gui->getPreview(TRUE)));
				$page_gui->setTemplateTargetVar("ADM_CONTENT");

				$page_gui->setOutputMode($this->object->evalTotalPersons() == 0 ? "edit" : 'preview');

				$page_gui->setHeader($question->getTitle());
				$page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
				$page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "fullscreen"));
				$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this));
				$page_gui->setPresentationTitle($question->getTitle() . ' ['. $this->lng->txt('question_id_short') . ': ' . $question->getId()  . ']');
				$ret =& $this->ctrl->forwardCommand($page_gui);

				global $ilTabs;
				$ilTabs->activateTab('assQuestions');

				$this->tpl->setContent($ret);
				break;

			case 'ilassquestionpreviewgui':

				$this->prepareOutput();

				$this->ctrl->saveParameter($this, "q_id");

				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewGUI.php';
				$gui = new ilAssQuestionPreviewGUI($this->ctrl, $this->tabs_gui, $this->tpl, $this->lng, $ilDB, $ilUser);

				$gui->initQuestion((int)$_GET['q_id'], $this->object->getId());
				$gui->initPreviewSettings($this->object->getRefId());
				$gui->initPreviewSession($ilUser->getId(), (int)$_GET['q_id']);
				$gui->initHintTracking();
				$gui->initStyleSheets();

				$this->ctrl->forwardCommand($gui);

				break;

			case 'ilassquestionpagegui':
				require_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php";
				//echo $_REQUEST['prev_qid'];
				if($_REQUEST['prev_qid'])
				{
					$this->ctrl->setParameter($this, 'prev_qid', $_REQUEST['prev_qid']);
				}

				$this->prepareOutput();
				//global $___test_express_mode;
				//$___test_express_mode = true;
				$_GET['calling_test'] = $this->object->getRefId();
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$this->tpl->setCurrentBlock("ContentStyle");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$this->tpl->parseCurrentBlock();

				// syntax style
				$this->tpl->setCurrentBlock("SyntaxStyle");
				$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
					ilObjStyleSheet::getSyntaxStylePath());
				$this->tpl->parseCurrentBlock();
				require_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
				$q_gui = assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
				$q_gui->setQuestionTabs();
				$q_gui->outAdditionalOutput();
				$q_gui->object->setObjId($this->object->getId());
				$question =& $q_gui->object;
				$this->ctrl->saveParameter($this, "q_id");
				$this->lng->loadLanguageModule("content");
				$this->ctrl->setReturnByClass("ilAssQuestionPageGUI", "view");
				$this->ctrl->setReturn($this, "questions");
				$page_gui = new ilAssQuestionPageGUI($_GET["q_id"]);
				$page_gui->setEditPreview(true);
				if(strlen($this->ctrl->getCmd()) == 0)
				{
					$this->ctrl->setCmdClass(get_class($page_gui));
					$this->ctrl->setCmd("preview");
				}
				$page_gui->setQuestionHTML(array($q_gui->object->getId() => $q_gui->getPreview(TRUE)));
				$page_gui->setTemplateTargetVar("ADM_CONTENT");
				$page_gui->setOutputMode($this->object->evalTotalPersons() == 0 ? "edit" : 'preview');
				$page_gui->setHeader($question->getTitle());
				$page_gui->setPresentationTitle($question->getTitle() . ' ['. $this->lng->txt('question_id_short') . ': ' . $question->getId()  . ']');
				$ret =& $this->ctrl->forwardCommand($page_gui);
				$this->tpl->setContent($ret);
				break;
				
			case 'ilassspecfeedbackpagegui':
				require_once "./Modules/TestQuestionPool/classes/feedback/class.ilAssSpecFeedbackPageGUI.php";
				$pg_gui = new ilAssSpecFeedbackPageGUI((int) $_GET["feedback_id"]);
				$this->ctrl->forwardCommand($pg_gui);
				break;
				
			case 'ilassgenfeedbackpagegui':
				require_once "./Modules/TestQuestionPool/classes/feedback/class.ilAssGenFeedbackPageGUI.php";
				$pg_gui = new ilAssGenFeedbackPageGUI((int) $_GET["feedback_id"]);
				$this->ctrl->forwardCommand($pg_gui);
				break;

			case 'illocalunitconfigurationgui':
				$this->prepareSubGuiOutput();

				// set return target
				$this->ctrl->setReturn($this, "questions");

				// set context tabs
				require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
				$questionGUI = assQuestionGUI::_getQuestionGUI('', $_GET['q_id']);
				$questionGUI->object->setObjId($this->object->getId());
				$questionGUI->setQuestionTabs();

				require_once 'Modules/TestQuestionPool/classes/class.ilLocalUnitConfigurationGUI.php';
				require_once 'Modules/TestQuestionPool/classes/class.ilUnitConfigurationRepository.php';
				$gui = new ilLocalUnitConfigurationGUI(
					new ilUnitConfigurationRepository((int)$_GET['q_id'])
				);
				$this->ctrl->forwardCommand($gui);
				break;

			case "ilcommonactiondispatchergui":
				require_once "Services/Object/classes/class.ilCommonActionDispatcherGUI.php";
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ilassquestionhintsgui':

				$this->prepareSubGuiOutput();

				// set return target
				$this->ctrl->setReturn($this, "questions");

				// set context tabs
				require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
				$questionGUI =& assQuestionGUI::_getQuestionGUI($q_type, $_GET['q_id']);
				$questionGUI->object->setObjId($this->object->getId());
				$questionGUI->setQuestionTabs();

				// forward to ilAssQuestionHintsGUI
				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintsGUI.php';
				$gui = new ilAssQuestionHintsGUI($questionGUI);
				$ilCtrl->forwardCommand($gui);

				break;

			case 'ilassquestionfeedbackeditinggui':

				$this->prepareSubGuiOutput();

				// set return target
				$this->ctrl->setReturn($this, "questions");

				// set context tabs
				require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
				$questionGUI = assQuestionGUI::_getQuestionGUI('', $_GET['q_id']);
				$questionGUI->object->setObjId($this->object->getId());
				$questionGUI->setQuestionTabs();

				// forward to ilAssQuestionFeedbackGUI
				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionFeedbackEditingGUI.php';
				$gui = new ilAssQuestionFeedbackEditingGUI($questionGUI, $ilCtrl, $ilAccess, $tpl, $ilTabs, $lng);
				$ilCtrl->forwardCommand($gui);

				break;

			case 'iltesttoplistgui':
				$this->prepareOutput();
				require_once './Modules/Test/classes/class.ilTestToplistGUI.php';
				$gui = new ilTestToplistGUI($this);
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ilscoringadjustmentgui':
				$this->prepareOutput();
				require_once './Modules/Test/classes/class.ilScoringAdjustmentGUI.php';
				$gui = new ilScoringAdjustmentGUI($this->object);
				$this->ctrl->forwardCommand($gui);
				break;
			
			case '':
			case 'ilobjtestgui':
				$this->prepareOutput();
				$this->addHeaderAction();
				if((strcmp($cmd, "properties") == 0) && ($_GET["browse"]))
				{
					$this->questionBrowser();
					return;
				}
				if((strcmp($cmd, "properties") == 0) && ($_GET["up"] || $_GET["down"]))
				{
					$this->questionsObject();
					return;
				}
				$cmd .= "Object";
				$ret =& $this->$cmd();
				break;
			default:
				// elba hack for storing question id for inserting new question after
				if($_REQUEST['prev_qid'])
				{
					global $___prev_question_id;
					$___prev_question_id = $_REQUEST['prev_qid'];
					$this->ctrl->setParameterByClass('ilassquestionpagegui', 'prev_qid', $_REQUEST['prev_qid']);
					$this->ctrl->setParameterByClass($_GET['sel_question_types'] . 'gui', 'prev_qid', $_REQUEST['prev_qid']);
				}
				$this->create_question_mode = true;
				$this->prepareOutput();

				$this->ctrl->setReturn($this, "questions");
				require_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
				$q_gui =& assQuestionGUI::_getQuestionGUI($_GET['sel_question_types'], $_GET["q_id"]);
				$q_gui->object->setObjId($this->object->getId());
				if(!$_GET['sel_question_types'])
					$qType = assQuestion::getQuestionTypeFromDb($_GET['q_id']);
				else
				{
					$qType = $_GET['sel_question_types'];
				}
				$this->ctrl->setParameterByClass($qType . "GUI", 'prev_qid', $_REQUEST['prev_qid']);
				$this->ctrl->setParameterByClass($qType . "GUI", 'test_ref_id', $_REQUEST['ref_id']);
				$this->ctrl->setParameterByClass($qType . "GUI", 'q_id', $_REQUEST['q_id']);
				if($_REQUEST['test_express_mode'])
					$this->ctrl->setParameterByClass($qType . "GUI", 'test_express_mode', 1);

				#global $___test_express_mode;
				#$___test_express_mode = true;
				if(!$q_gui->isSaveCommand())
					$_GET['calling_test'] = $this->object->getRefId();

				$q_gui->setQuestionTabs();
				#unset($___test_express_mode);
				$ret =& $this->ctrl->forwardCommand($q_gui);
				break;
		}
		if ( !in_array(strtolower($_GET["baseClass"]), array('iladministrationgui', 'ilrepositorygui')) &&
			$this->getCreationMode() != true)
		{
			$this->tpl->show();
		}
	}

	private function questionsTabGatewayObject()
	{
		switch( $this->object->getQuestionSetType() )
		{
			case ilObjTest::QUESTION_SET_TYPE_FIXED:
				$this->ctrl->redirectByClass('ilTestExpressPageObjectGUI', 'showPage');

			case ilObjTest::QUESTION_SET_TYPE_RANDOM:
				$this->ctrl->redirectByClass('ilTestRandomQuestionSetConfigGUI');
				
			case ilObjTest::QUESTION_SET_TYPE_DYNAMIC:
				$this->ctrl->redirectByClass('ilObjTestDynamicQuestionSetConfigGUI');
		}
	}

	private function userResultsGatewayObject()
	{
		$this->ctrl->setCmdClass('ilTestEvaluationGUI');
		$this->ctrl->setCmd('outUserResultsOverview');
		$this->tabs_gui->clearTargets();
		
		$this->forwardToEvaluationGUI();
	}

	private function forwardToEvaluationGUI()
	{
		$this->prepareOutput();
		$this->addHeaderAction();

		require_once 'Modules/Test/classes/class.ilTestEvaluationGUI.php';
		$gui = new ilTestEvaluationGUI($this->object);
		$gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());

		$this->ctrl->forwardCommand($gui);
	}

	private function forwardToEvalObjectiveOrientedGUI()
	{
		$this->prepareOutput();
		$this->addHeaderAction();

		require_once 'Modules/Test/classes/class.ilTestEvalObjectiveOrientedGUI.php';
		$gui = new ilTestEvalObjectiveOrientedGUI($this->object);
		$gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());

		$this->ctrl->forwardCommand($gui);
	}

	/**
	 * @param $show_pass_details
	 * @param $show_answers
	 * @param $show_reached_points
	 * @param $show_user_results
	 *
	 * @return ilTemplate
	 */
	public function createUserResults($show_pass_details, $show_answers, $show_reached_points, $show_user_results)
	{
		global $ilTabs, $ilDB;

		$ilTabs->setBackTarget(
			$this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'participants')
		);

		if( $this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired() )
		{
			require_once 'Services/Link/classes/class.ilLink.php';
			$courseLink = ilLink::_getLink($this->getObjectiveOrientedContainer()->getRefId());
			$ilTabs->setBack2Target($this->lng->txt('back_to_objective_container'), $courseLink);
		}

		$template = new ilTemplate("tpl.il_as_tst_participants_result_output.html", TRUE, TRUE, "Modules/Test");
		
		require_once 'Modules/Test/classes/toolbars/class.ilTestResultsToolbarGUI.php';
		$toolbar = new ilTestResultsToolbarGUI($this->ctrl, $this->tpl, $this->lng);

		$this->ctrl->setParameter($this, 'pdf', '1');
		$toolbar->setPdfExportLinkTarget( $this->ctrl->getLinkTarget($this, $this->ctrl->getCmd()) );
		$this->ctrl->setParameter($this, 'pdf', '');

		if( $show_answers )
		{
			if( isset($_GET['show_best_solutions']) )
			{
				$_SESSION['tst_results_show_best_solutions'] = true;
			}
			elseif( isset($_GET['hide_best_solutions']) )
			{
				$_SESSION['tst_results_show_best_solutions'] = false;
			}
			elseif( !isset($_SESSION['tst_results_show_best_solutions']) )
			{
				$_SESSION['tst_results_show_best_solutions'] = false;
			}

			if( $_SESSION['tst_results_show_best_solutions'] )
			{
				$this->ctrl->setParameter($this, 'hide_best_solutions', '1');
				$toolbar->setHideBestSolutionsLinkTarget($this->ctrl->getLinkTarget($this, $this->ctrl->getCmd()));
				$this->ctrl->setParameter($this, 'hide_best_solutions', '');
			}
			else
			{
				$this->ctrl->setParameter($this, 'show_best_solutions', '1');
				$toolbar->setShowBestSolutionsLinkTarget($this->ctrl->getLinkTarget($this, $this->ctrl->getCmd()));
				$this->ctrl->setParameterByClass('', 'show_best_solutions', '');
			}
		}

		require_once 'Modules/Test/classes/class.ilTestParticipantData.php';
		$participantData = new ilTestParticipantData($ilDB, $this->lng);
		if( $this->object->getFixedParticipants() )
		{
			$participantData->setUserIds($show_user_results);
		}
		else
		{
			$participantData->setActiveIds($show_user_results);
		}
		$participantData->load($this->object->getTestId());
		$toolbar->setParticipantSelectorOptions($participantData->getOptionArray($show_user_results));

		$toolbar->build();
		$template->setVariable('RESULTS_TOOLBAR', $this->ctrl->getHTML($toolbar));

		include_once "./Modules/Test/classes/class.ilTestServiceGUI.php";
		$serviceGUI = new ilTestServiceGUI($this->object);
		$serviceGUI->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
		$serviceGUI->setParticipantData($participantData);

		$count      = 0;
		foreach ($show_user_results as $key => $active_id)
		{
			$count++;
			$results = "";
			if ($this->object->getFixedParticipants())
			{
				$active_id = $this->object->getActiveIdOfUser( $active_id );
			}
			if ($active_id > 0)
			{
				$results = $serviceGUI->getResultsOfUserOutput(
					$this->testSessionFactory->getSession( $active_id ),
					$active_id,
					$this->object->_getResultPass( $active_id ),
					$this,
					$show_pass_details,
					$show_answers,
					FALSE,
					$show_reached_points
				);
			}
			if ($count < count( $show_user_results ))
			{
				$template->touchBlock( "break" );
			}
			$template->setCurrentBlock( "user_result" );
			$template->setVariable( "USER_RESULT", $results );
			$template->parseCurrentBlock();
		}
		
		if( $this->isPdfDeliveryRequest() )
		{
			require_once 'class.ilTestPDFGenerator.php';

			ilTestPDFGenerator::generatePDF(
				$template->get(), ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD, $this->object->getTitle()
			);
			
			exit;
		}
		else
		{
			return $template;
		}
	}

	private function redirectTo_ilObjTestSettingsGeneralGUI_showForm_Object()
	{
		require_once 'Modules/Test/classes/class.ilObjTestSettingsGeneralGUI.php';
		$this->ctrl->redirectByClass('ilObjTestSettingsGeneralGUI', ilObjTestSettingsGeneralGUI::CMD_SHOW_FORM);
	}
	
	/**
	 * prepares ilias to get output rendered by sub gui class
	 * 
	 * @global ilLocator $ilLocator
	 * @global ilTemplate $tpl
	 * @global ilObjUser $ilUser
	 * @return boolean
	 */
	private function prepareSubGuiOutput()
	{
		global $ilUser;

		$this->tpl->getStandardTemplate();

		// set locator
		$this->setLocator();
		
		// catch feedback message
		ilUtil::infoPanel();

		// set title and description and title icon
		$this->setTitleAndDescription();

		// BEGIN WebDAV: Display Mount Webfolder icon.
		require_once 'Services/WebDAV/classes/class.ilDAVServer.php';
		if (ilDAVServer::_isActive() && $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			$this->showMountWebfolderIcon();
		}
		// END WebDAV: Display Mount Webfolder icon.
	}

	function runObject()
	{
		$this->ctrl->redirect($this, "infoScreen");
	}
	
	function outEvaluationObject()
	{
		$this->ctrl->redirectByClass("iltestevaluationgui", "outEvaluation");
	}

	/**
	* form for new test object import
	*/
	function importFileObject()
	{
		$form = $this->initImportForm($_REQUEST["new_type"]);
		if($form->checkInput())
		{
			$this->ctrl->setParameter($this, "new_type", $this->type);
			$this->uploadTstObject();
		}

		// display form to correct errors
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}
	
	function addDidacticTemplateOptions(array &$a_options) 
	{
		include_once("./Modules/Test/classes/class.ilObjTest.php");
		$tst = new ilObjTest();
		$defaults = $tst->getAvailableDefaults();
		if (count($defaults))
		{
			foreach ($defaults as $row)
			{
				$a_options["tstdef_".$row["test_defaults_id"]] = array($row["name"],
					$this->lng->txt("tst_default_settings"));
			}
		}

		// using template?
		include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
		$templates = ilSettingsTemplate::getAllSettingsTemplates("tst");
		if($templates)
		{
			foreach($templates as $item)
			{
				$a_options["tsttpl_".$item["id"]] = array($item["title"],
					nl2br(trim($item["description"])));
			}
		}		
	}
	
	/**
	* save object
	* @access	public
	*/
	function afterSave(ilObject $a_new_object)
	{
		$tstdef = $this->getDidacticTemplateVar("tstdef");
		if ($tstdef) 
		{
			$testDefaultsId = $tstdef;
			$testDefaults = ilObjTest::_getTestDefaults($testDefaultsId);
			$a_new_object->applyDefaults($testDefaults);
		}

		$template_id = $this->getDidacticTemplateVar("tsttpl");
		if($template_id)
		{			
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template = new ilSettingsTemplate($template_id, ilObjAssessmentFolderGUI::getSettingsTemplateConfig());

			$template_settings = $template->getSettings();
			if($template_settings)
			{
				$this->applyTemplate($template_settings, $a_new_object);
			}

			$a_new_object->setTemplate($template_id);
		}
		
		$a_new_object->saveToDb();

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);
		$this->ctrl->setParameter($this, 'ref_id', $a_new_object->getRefId());
		$this->ctrl->redirectByClass('ilObjTestSettingsGeneralGUI');
	}

	function backToRepositoryObject()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$path = $this->tree->getPathFull($this->object->getRefID());
		ilUtil::redirect($this->getReturnLocation("cancel","./ilias.php?baseClass=ilRepositoryGUI&cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
	}

	/**
	* imports test and question(s)
	*/
	function uploadTstObject()
	{
		if ($_FILES["xmldoc"]["error"] > UPLOAD_ERR_OK)
		{
			ilUtil::sendFailure($this->lng->txt("error_upload"));
			$this->createObject();
			return;
		}
		include_once("./Modules/Test/classes/class.ilObjTest.php");
		// create import directory
		$basedir = ilObjTest::_createImportDirectory();

		// copy uploaded file to import directory
		$file = pathinfo($_FILES["xmldoc"]["name"]);
		$full_path = $basedir."/".$_FILES["xmldoc"]["name"];
		ilUtil::moveUploadedFile($_FILES["xmldoc"]["tmp_name"], $_FILES["xmldoc"]["name"], $full_path);

		// unzip file
		ilUtil::unzip($full_path);

		// determine filenames of xml files
		$subdir = basename($file["basename"],".".$file["extension"]);
		ilObjTest::_setImportDirectory($basedir);
		$xml_file = ilObjTest::_getImportDirectory().'/'.$subdir.'/'.$subdir.".xml";
		$qti_file = ilObjTest::_getImportDirectory().'/'.$subdir.'/'. preg_replace("/test|tst/", "qti", $subdir).".xml";
		$results_file = ilObjTest::_getImportDirectory().'/'.$subdir.'/'. preg_replace("/test|tst/", "results", $subdir).".xml";

		if(!is_file($qti_file))
		{
			ilUtil::delDir($basedir);
			ilUtil::sendFailure($this->lng->txt("tst_import_non_ilias_zip"));
			$this->createObject();
			return;
		}

		// start verification of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($qti_file, IL_MO_VERIFY_QTI, 0, "");
		$result = $qtiParser->startParsing();
		$founditems =& $qtiParser->getFoundItems();
		
		if (count($founditems) == 0)
		{
			// nothing found

			// delete import directory
			ilUtil::delDir($basedir);

			ilUtil::sendInfo($this->lng->txt("tst_import_no_items"));
			$this->createObject();
			return;
		}
		
		$complete = 0;
		$incomplete = 0;
		foreach ($founditems as $item)
		{
			if (strlen($item["type"]))
			{
				$complete++;
			}
			else
			{
				$incomplete++;
			}
		}
		
		if ($complete == 0)
		{
			// delete import directory
			ilUtil::delDir($basedir);

			ilUtil::sendInfo($this->lng->txt("qpl_import_non_ilias_files"));
			$this->createObject();
			return;
		}
		
		$_SESSION["tst_import_results_file"] = $results_file;
		$_SESSION["tst_import_xml_file"] = $xml_file;
		$_SESSION["tst_import_qti_file"] = $qti_file;
		$_SESSION["tst_import_subdir"] = $subdir;
		// display of found questions
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.tst_import_verification.html", "Modules/Test");
		$row_class = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($founditems as $item)
		{
			$this->tpl->setCurrentBlock("verification_row");
			$this->tpl->setVariable("ROW_CLASS", $row_class[$counter++ % 2]);
			$this->tpl->setVariable("QUESTION_TITLE", $item["title"]);
			$this->tpl->setVariable("QUESTION_IDENT", $item["ident"]);
			include_once "./Services/QTI/classes/class.ilQTIItem.php";
			switch ($item["type"])
			{
				case MULTIPLE_CHOICE_QUESTION_IDENTIFIER:
				case QT_MULTIPLE_CHOICE_MR:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assMultipleChoice"));
					break;
				case SINGLE_CHOICE_QUESTION_IDENTIFIER:
				case QT_MULTIPLE_CHOICE_SR:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assSingleChoice"));
					break;
				case KPRIM_CHOICE_QUESTION_IDENTIFIER:
				case QT_KPRIM_CHOICE:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assKprimChoice"));
					break;
				case LONG_MENU_QUESTION_IDENTIFIER:
				case QT_LONG_MENU:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assLongMenu"));
					break;
				case NUMERIC_QUESTION_IDENTIFIER:
				case QT_NUMERIC:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assNumeric"));
					break;
				case FORMULA_QUESTION_IDENTIFIER:
				case QT_FORMULA:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assFormulaQuestion"));
					break;
				case TEXTSUBSET_QUESTION_IDENTIFIER:
				case QT_TEXTSUBSET:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assTextSubset"));
					break;
				case CLOZE_TEST_IDENTIFIER:
				case QT_CLOZE:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assClozeTest"));
					break;
				case ERROR_TEXT_IDENTIFIER:
				case QT_ERRORTEXT:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assErrorText"));
					break;
				case IMAGEMAP_QUESTION_IDENTIFIER:
				case QT_IMAGEMAP:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assImagemapQuestion"));
					break;
				case JAVAAPPLET_QUESTION_IDENTIFIER:
				case QT_JAVAAPPLET:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assJavaApplet"));
					break;
				case FLASHAPPLET_QUESTION_IDENTIFIER:
				case QT_FLASHAPPLET:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assFlashApplet"));
					break;
				case MATCHING_QUESTION_IDENTIFIER:
				case QT_MATCHING:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assMatchingQuestion"));
					break;
				case ORDERING_QUESTION_IDENTIFIER:
				case QT_ORDERING:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assOrderingQuestion"));
					break;
				case ORDERING_HORIZONTAL_IDENTIFIER:
				case QT_ORDERING_HORIZONTAL:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assOrderingHorizontal"));
					break;
				case TEXT_QUESTION_IDENTIFIER:
				case QT_TEXT:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assTextQuestion"));
					break;
				case FILE_UPLOAD_IDENTIFIER:
				case QT_FILEUPLOAD:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assFileUpload"));
					break;
			}
			$this->tpl->parseCurrentBlock();
		}

		// on import creation screen the pool was chosen (-1 for no pool)
		// BUT when no pool is available the input on creation screen is missing, so the field value -1 for no pool is not submitted.
		$QplOrTstID = isset($_POST["qpl"]) && (int)$_POST["qpl"] != 0 ? $_POST["qpl"] : -1;
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("question_title"));
		$this->tpl->setVariable("FOUND_QUESTIONS_INTRODUCTION", $this->lng->txt("tst_import_verify_found_questions"));
		$this->tpl->setVariable("VERIFICATION_HEADING", $this->lng->txt("import_tst"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("ARROW", ilUtil::getImagePath("arrow_downright.svg"));
		$this->tpl->setVariable("QUESTIONPOOL_ID", $QplOrTstID);
		$this->tpl->setVariable("VALUE_IMPORT", $this->lng->txt("import"));
		$this->tpl->setVariable("VALUE_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* imports question(s) into the questionpool (after verification)
	*/
	function importVerifiedFileObject()
	{
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		// create new questionpool object
		$newObj = new ilObjTest(0, true);
		// set type of questionpool object
		$newObj->setType($_GET["new_type"]);
		// set title of questionpool object to "dummy"
		$newObj->setTitle("dummy");
		// set description of questionpool object
		$newObj->setDescription("test import");
		// create the questionpool class in the ILIAS database (object_data table)
		$newObj->create(true);
		// create a reference for the questionpool object in the ILIAS database (object_reference table)
		$newObj->createReference();
		// put the questionpool object in the administration tree
		$newObj->putInTree($_GET["ref_id"]);
		// get default permissions and set the permissions for the questionpool object
		$newObj->setPermissions($_GET["ref_id"]);
		// notify the questionpool object and all its parent objects that a "new" object was created
		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());
		// empty mark schema
		$newObj->mark_schema->flush();

		// start parsing of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";

		// Handle selection of "no questionpool" as qpl_id = -1 -> use test object id instead.
		// TODO: chek if empty strings in $_POST["qpl_id"] relates to a bug or not
		if ($_POST["qpl_id"] == "-1")
		{
			$qpl_id = $newObj->id;
		} 
		else 
		{
			$qpl_id = $_POST["qpl_id"];
		}

		$qtiParser = new ilQTIParser($_SESSION["tst_import_qti_file"], IL_MO_PARSE_QTI, $qpl_id, $_POST["ident"]);
		if( !isset($_POST["ident"]) || !is_array($_POST["ident"]) || !count($_POST["ident"]) )
		{
			$qtiParser->setIgnoreItemsEnabled(true);
		}
		$qtiParser->setTestObject($newObj);
		$result = $qtiParser->startParsing();
		$newObj->saveToDb();

		// import page data
		include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
		$contParser = new ilContObjParser($newObj, $_SESSION["tst_import_xml_file"], $_SESSION["tst_import_subdir"]);
		$contParser->setQuestionMapping($qtiParser->getImportMapping());
		$contParser->startParsing();

		if( isset($_POST["ident"]) && is_array($_POST["ident"]) && count($_POST["ident"]) == $qtiParser->getFoundItems() )
		{
			// import test results
			if (@file_exists($_SESSION["tst_import_results_file"]))
			{
				include_once ("./Modules/Test/classes/class.ilTestResultsImportParser.php");
				$results = new ilTestResultsImportParser($_SESSION["tst_import_results_file"], $newObj);
				$results->startParsing();
			}
		}

		// delete import directory
		ilUtil::delDir(ilObjTest::_getImportDirectory());
		ilUtil::sendSuccess($this->lng->txt("object_imported"),true);

		$newObj->updateMetaData();
		
		ilUtil::redirect("ilias.php?ref_id=".$newObj->getRefId().
				"&baseClass=ilObjTestGUI");
	}
	
	/**
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function uploadObject($redirect = true)
	{
		$this->uploadTstObject();
	}

	/**
	* download file
	*/
	function downloadFileObject()
	{
		$file = explode("_", $_GET["file_id"]);
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj =& new ilObjFile($file[count($file) - 1], false);
		$fileObj->sendFile();
		exit;
	}
	
	/**
	* show fullscreen view
	*/
	function fullscreenObject()
	{
		include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php");
		$page_gui = new ilAssQuestionPageGUI($_GET["pg_id"]);
		$page_gui->showMediaFullscreen();
		
	}

	/**
	* download source code paragraph
	*/
	function download_paragraphObject()
	{
		include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPage.php");
		$pg_obj = new ilAssQuestionPage($_GET["pg_id"]);
		$pg_obj->send_paragraph ($_GET["par_id"], $_GET["downloadtitle"]);
		exit;
	}

	/**
	* Sets the filter for the question browser 
	*
	* Sets the filter for the question browser 
	*
	* @access	public
	*/
	function filterObject()
	{
		$this->questionBrowser();
	}

	/**
	* Resets the filter for the question browser 
	*
	* Resets the filter for the question browser 
	*
	* @access	public
	*/
	function resetFilterObject()
	{
		$this->questionBrowser();
	}

	/**
	* Called when the back button in the question browser was pressed 
	*
	* Called when the back button in the question browser was pressed 
	*
	* @access	public
	*/
	function backObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Creates a new questionpool and returns the reference id
	*
	* Creates a new questionpool and returns the reference id
	*
	* @return integer Reference id of the newly created questionpool
	* @access	public
	*/
	function createQuestionPool($name = "dummy", $description = "")
	{
		global $tree;
		$parent_ref = $tree->getParentId($this->object->getRefId());
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		$qpl = new ilObjQuestionPool();
		$qpl->setType("qpl");
		$qpl->setTitle($name);
		$qpl->setDescription($description);
		$qpl->create();
		$qpl->createReference();
		$qpl->putInTree($parent_ref);
		$qpl->setPermissions($parent_ref);
		$qpl->setOnline(1); // must be online to be available
		$qpl->saveToDb();
		return $qpl->getRefId();
	}

	/**
	* Creates a form for random selection of questions
	*/
	public function randomselectObject()
	{
		global $ilUser;
		$this->getQuestionsSubTabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_select.html", "Modules/Test");
		$questionpools =& $this->object->getAvailableQuestionpools(FALSE, FALSE, FALSE, TRUE);
		$this->tpl->setCurrentBlock("option");
		$this->tpl->setVariable("VALUE_OPTION", "0");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("all_available_question_pools"));
		$this->tpl->parseCurrentBlock();
		foreach ($questionpools as $key => $value)
		{
			$this->tpl->setCurrentBlock("option");
			$this->tpl->setVariable("VALUE_OPTION", $key);
			$this->tpl->setVariable("TEXT_OPTION", $value["title"]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("hidden");
		$this->tpl->setVariable("HIDDEN_NAME", "sel_question_types");
		$this->tpl->setVariable("HIDDEN_VALUE", $_POST["sel_question_types"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_random_select_questionpool"));
		$this->tpl->setVariable("TXT_NR_OF_QUESTIONS", $this->lng->txt("tst_random_nr_of_questions"));
		$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("submit"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Cancels the form for random selection of questions
	*
	* Cancels the form for random selection of questions
	*
	* @access	public
	*/
	function cancelRandomSelectObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Offers a random selection for insertion in the test
	*
	* Offers a random selection for insertion in the test
	*
	* @access	public
	*/
	function createRandomSelectionObject()
	{
		$this->getQuestionsSubTabs();
		$question_array = $this->object->randomSelectQuestions($_POST["nr_of_questions"], $_POST["sel_qpl"]);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_question_offer.html", "Modules/Test");
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$questionpools =& $this->object->getAvailableQuestionpools(true);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		foreach ($question_array as $question_id)
		{
			$dataset = $this->object->getQuestionDataset($question_id);
			$this->tpl->setCurrentBlock("QTab");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->setVariable("QUESTION_TITLE", $dataset->title);
			$this->tpl->setVariable("QUESTION_COMMENT", $dataset->description);
			$this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($dataset->type_tag));
			$this->tpl->setVariable("QUESTION_AUTHOR", $dataset->author);
			$this->tpl->setVariable("QUESTION_POOL", $questionpools[$dataset->obj_fi]["title"]);
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		if (count($question_array) == 0)
		{
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_NO_QUESTIONS_AVAILABLE", $this->lng->txt("no_questions_available"));
			$this->tpl->parseCurrentBlock();
		}
			else
		{
			$this->tpl->setCurrentBlock("Selectionbuttons");
			$this->tpl->setVariable("BTN_YES", $this->lng->txt("random_accept_sample"));
			$this->tpl->setVariable("BTN_NO", $this->lng->txt("random_another_sample"));
			$this->tpl->parseCurrentBlock();
		}
		$chosen_questions = join($question_array, ",");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("QUESTION_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("tst_question_type"));
		$this->tpl->setVariable("QUESTION_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("QUESTION_POOL", $this->lng->txt("qpl"));
		$this->tpl->setVariable("VALUE_CHOSEN_QUESTIONS", $chosen_questions);
		$this->tpl->setVariable("VALUE_QUESTIONPOOL_SELECTION", $_POST["sel_qpl"]);
		$this->tpl->setVariable("VALUE_NR_OF_QUESTIONS", $_POST["nr_of_questions"]);
		$this->tpl->setVariable("TEXT_QUESTION_OFFER", $this->lng->txt("tst_question_offer"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Inserts a random selection into the test
	*
	* Inserts a random selection into the test
	*
	* @access	public
	*/
	function insertRandomSelectionObject()
	{
		$selected_array = split(",", $_POST["chosen_questions"]);
		if (!count($selected_array))
		{
			ilUtil::sendInfo($this->lng->txt("tst_insert_missing_question"));
		}
		else
		{
			$total = $this->object->evalTotalPersons();
			if ($total)
			{
				// the test was executed previously
				ilUtil::sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("tst_insert_questions"));
			}
			foreach ($selected_array as $key => $value) 
			{
				$this->object->insertQuestion( $this->testQuestionSetConfigFactory->getQuestionSetConfig(), $value );
			}
			$this->object->saveCompleteStatus( $this->testQuestionSetConfigFactory->getQuestionSetConfig() );
			ilUtil::sendSuccess($this->lng->txt("tst_questions_inserted"), true);
			$this->ctrl->redirect($this, "questions");
			return;
		}
	}

	function browseForQuestionsObject()
	{
		$this->questionBrowser();
	}
	
	/**
	* Called when a new question should be created from a test after confirmation
	*
	* Called when a new question should be created from a test after confirmation
	*
	* @access	public
	*/
	function executeCreateQuestionObject()
	{
		$qpl_ref_id = $_REQUEST["sel_qpl"];

		$qpl_mode = $_REQUEST['usage'];
		
		if(isset($_REQUEST['qtype']))
		{
			include_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';
			$sel_question_types = ilObjQuestionPool::getQuestionTypeByTypeId($_REQUEST["qtype"]); 
		}
		else if(isset($_REQUEST['sel_question_types']))
		{
			$sel_question_types = $_REQUEST["sel_question_types"];
		}

		if (!$qpl_mode || ($qpl_mode == 2 && strcmp($_REQUEST["txt_qpl"], "") == 0) || ($qpl_mode == 3 && strcmp($qpl_ref_id, "") == 0))
		//if ((strcmp($_REQUEST["txt_qpl"], "") == 0) && (strcmp($qpl_ref_id, "") == 0))
		{
			// Mantis #14890
			$_REQUEST['sel_question_types'] = $sel_question_types;
			ilUtil::sendInfo($this->lng->txt("questionpool_not_entered"));
			$this->createQuestionObject();
			return;
		}
		else
		{
			$_SESSION["test_id"] = $this->object->getRefId();
			if ($qpl_mode == 2 && strcmp($_REQUEST["txt_qpl"], "") != 0)
			{
				// create a new question pool and return the reference id
				$qpl_ref_id = $this->createQuestionPool($_REQUEST["txt_qpl"]);
			}
			else if ($qpl_mode == 1)
			{
			    $qpl_ref_id = $_GET["ref_id"];
			}

			include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPoolGUI.php";
			$baselink = "ilias.php?baseClass=ilObjQuestionPoolGUI&ref_id=" . $qpl_ref_id . "&cmd=createQuestionForTest&test_ref_id=".$_GET["ref_id"]."&calling_test=".$_GET["ref_id"]."&sel_question_types=" . $sel_question_types;

			if (isset($_REQUEST['prev_qid'])) 
			{
			    $baselink .= '&prev_qid=' . $_REQUEST['prev_qid'];
			}
			else if(isset($_REQUEST['position']))
			{
				$baselink .= '&prev_qid=' . $_REQUEST['position'];
			}
			
			if ($_REQUEST['test_express_mode']) {
			    $baselink .= '&test_express_mode=1';
			}
			
			if( isset($_REQUEST['add_quest_cont_edit_mode']) )
			{
				$baselink = ilUtil::appendUrlParameterString(
						$baselink, "add_quest_cont_edit_mode={$_REQUEST['add_quest_cont_edit_mode']}", false
				);
			}
			
#var_dump($_REQUEST['prev_qid']);
			ilUtil::redirect($baselink);
			
			exit();
		}
	}

	/**
	* Called when the creation of a new question is cancelled
	*
	* Called when the creation of a new question is cancelled
	*
	* @access	public
	*/
	function cancelCreateQuestionObject()
	{
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* Called when a new question should be created from a test
	*
	* Called when a new question should be created from a test
	*
	* @access	public
	*/
	function createQuestionObject()
	{
		global $ilUser;
		$this->getQuestionsSubTabs();
		//$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_qpl_select.html", "Modules/Test");
		$questionpools =& $this->object->getAvailableQuestionpools(FALSE, FALSE, FALSE, TRUE, FALSE, "write");
		
		if ($this->object->getPoolUsage()) {
		    global $lng, $ilCtrl, $tpl;

		    include_once "Services/Form/classes/class.ilPropertyFormGUI.php";

		    $form = new ilPropertyFormGUI();
		    $form->setFormAction($ilCtrl->getFormAction($this, "executeCreateQuestion"));
		    $form->setTitle($lng->txt("ass_create_question"));
		    include_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';


		    $hidden = new ilHiddenInputGUI('sel_question_types');
		    $hidden->setValue($_REQUEST["sel_question_types"]);
		    $form->addItem($hidden);

			// content editing mode
			if( ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled() )
			{
				$ri = new ilRadioGroupInputGUI($lng->txt("tst_add_quest_cont_edit_mode"), "add_quest_cont_edit_mode");

				$ri->addOption(new ilRadioOption(
						$lng->txt('tst_add_quest_cont_edit_mode_default'),
						assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT
				));

				$ri->addOption(new ilRadioOption(
						$lng->txt('tst_add_quest_cont_edit_mode_page_object'),
						assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT
				));
				
				$ri->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT);

				$form->addItem($ri, true);
			}
			else
			{
				$hi = new ilHiddenInputGUI("question_content_editing_type");
				$hi->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT);
				$form->addItem($hi, true);
			}
			
		    // use pool
		    $usage = new ilRadioGroupInputGUI($this->lng->txt("assessment_pool_selection"), "usage");
		    $usage->setRequired(true);
		    $no_pool = new ilRadioOption($this->lng->txt("assessment_no_pool"), 1);
		    $usage->addOption($no_pool);
		    $existing_pool = new ilRadioOption($this->lng->txt("assessment_existing_pool"), 3);
		    $usage->addOption($existing_pool);
		    $new_pool = new ilRadioOption($this->lng->txt("assessment_new_pool"), 2);
		    $usage->addOption($new_pool);
		    $form->addItem($usage);

		    $usage->setValue(1);

		    $questionpools = ilObjQuestionPool::_getAvailableQuestionpools(FALSE, FALSE, TRUE, FALSE, FALSE, "write");
		    $pools_data = array();
		    foreach($questionpools as $key => $p) {
			$pools_data[$key] = $p['title'];
		    }
		    $pools = new ilSelectInputGUI($this->lng->txt("select_questionpool"), "sel_qpl");
		    $pools->setOptions($pools_data);
		    $existing_pool->addSubItem($pools);


			$this->lng->loadLanguageModule('rbac');
		    $name = new ilTextInputGUI($this->lng->txt("rbac_create_qpl"), "txt_qpl");
		    $name->setSize(50);
		    $name->setMaxLength(50);
		    $new_pool->addSubItem($name);

		    $form->addCommandButton("executeCreateQuestion", $lng->txt("submit"));
		    $form->addCommandButton("cancelCreateQuestion", $lng->txt("cancel"));

		    return $this->tpl->setVariable('ADM_CONTENT', $form->getHTML());

		}
		else {
		    global $ilCtrl;

		    $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'sel_question_types', $_REQUEST["sel_question_types"]);
		    $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'add_quest_cont_edit_mode', $_REQUEST["add_quest_cont_edit_mode"]);
		    $link = $ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'handleToolbarCommand','',false,false);
		    ilUtil::redirect($link);
		}
	}

	/**
	 * Remove questions from the test after confirmation
	 */
	public function confirmRemoveQuestionsObject()
	{
		$checked_questions = $_POST["q_id"];

		$questions = $this->object->getQuestionTitlesAndIndexes();
		$deleted   = array();
		foreach((array)$checked_questions as $value)
		{
			$this->object->removeQuestion($value);
			$deleted[] = $value;
		}

		$this->object->saveCompleteStatus( $this->testQuestionSetConfigFactory->getQuestionSetConfig() );
		
		ilUtil::sendSuccess($this->lng->txt("tst_questions_removed"));

		if($_REQUEST['test_express_mode'])
		{
			$prev        = null;
			$return_to   = null;
			$deleted_tmp = $deleted;
			$first       = array_shift($deleted_tmp);
			foreach((array)$questions as $key => $value)
			{
				if(!in_array($key, $deleted))
				{
					$prev = $key;
					if(!$first)
					{
						$return_to = $prev;
						break;
					}
					else continue;
				}
				else if($key == $first)
				{
					if($prev)
					{
						$return_to = $prev;
						break;
					}
					$first = array_shift($deleted_tmp);
				}
			}

			if(
				count($questions) == count($checked_questions) ||
				!$return_to
			)
			{
				$this->ctrl->setParameter($this, 'q_id', '');
				$this->ctrl->redirect($this, 'showPage');
			}

			$this->ctrl->setParameter($this, 'q_id', $return_to);
			$this->ctrl->redirect($this, "showPage");
		}
		else
		{
			$this->ctrl->setParameter($this, 'q_id', '');
			$this->ctrl->redirect($this, 'questions');
		}
	}
	
	/**
	* Cancels the removal of questions from the test
	*
	* Cancels the removal of questions from the test
	*
	* @access	public
	*/
	function cancelRemoveQuestionsObject()
	{
	    	if ($_REQUEST['test_express_mode']) {
		    $this->ctrl->setParameter($this, 'q_id', $_REQUEST['q_id']);
		    $this->ctrl->redirect($this, "showPage");
		}
		else {
		    $this->ctrl->redirect($this, "questions");
		}
	}
	
	/**
	* Displays a form to confirm the removal of questions from the test
	*
	* Displays a form to confirm the removal of questions from the test
	*
	* @access	public
	*/
	function removeQuestionsForm($checked_questions)
	{		
		$total = $this->object->evalTotalPersons();
		if ($total) 
		{
			// the test was executed previously
			$question = sprintf($this->lng->txt("tst_remove_questions_and_results"), $total);
		} 
		else 
		{
			if (count($checked_questions) == 1)
			{
				$question = $this->lng->txt("tst_remove_question");
			}
			else
			{
				$question = $this->lng->txt("tst_remove_questions");
			}
		}
				
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($question);

		$this->ctrl->saveParameter($this, 'test_express_mode');
		$this->ctrl->saveParameter($this, 'q_id');
		
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($this->lng->txt("cancel"), "cancelRemoveQuestions");
		$cgui->setConfirm($this->lng->txt("confirm"), "confirmRemoveQuestions");
								
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$removablequestions =& $this->object->getTestQuestions();				
		if (count($removablequestions))
		{
			foreach ($removablequestions as $data)
			{
				if (in_array($data["question_id"], $checked_questions))
				{
					$txt = $data["title"]." (".assQuestion::_getQuestionTypeName($data["type_tag"]).")";
					$txt .= ' ['. $this->lng->txt('question_id_short') . ': ' . $data['question_id']  . ']';
					
					if($data["description"])
					{
						$txt .= "<div class=\"small\">".$data["description"]."</div>";
					}
					
					$cgui->addItem("q_id[]", $data["question_id"], $txt);
				}
			}		
		}
		
		$this->tpl->setContent($cgui->getHTML());		
	}

	/**
	* Called when a selection of questions should be removed from the test
	*
	* Called when a selection of questions should be removed from the test
	*
	* @access	public
	*/
	function removeQuestionsObject()
	{
		$this->getQuestionsSubTabs();
		$checked_questions = $_REQUEST["q_id"];
		if (!is_array($checked_questions) && $checked_questions) {
		    $checked_questions = array($checked_questions);
		}
		if (count($checked_questions) > 0) 
		{			
			$this->removeQuestionsForm($checked_questions);
			return;
		} 
		elseif (count($checked_questions) == 0) 
		{
			ilUtil::sendFailure($this->lng->txt("tst_no_question_selected_for_removal"), true);
			$this->ctrl->redirect($this, "questions");
		}
	}
	
	/**
	* Marks selected questions for moving
	*/
	function moveQuestionsObject()
	{
		$selected_questions = NULL;
		$selected_questions = $_POST['q_id'];
		if (is_array($selected_questions))
		{
			$_SESSION['tst_qst_move_' . $this->object->getTestId()] = $_POST['q_id'];
			ilUtil::sendSuccess($this->lng->txt("msg_selected_for_move"), true);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('no_selection_for_move'), TRUE);
		}
		$this->ctrl->redirect($this, 'questions');
	}
	
	/**
	* Insert checked questions before the actual selection
	*/
	public function insertQuestionsBeforeObject()
	{
		// get all questions to move
		$move_questions = $_SESSION['tst_qst_move_' . $this->object->getTestId()];

		if (count($_POST['q_id']) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("no_target_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		if (count($_POST['q_id']) > 1)
		{
			ilUtil::sendFailure($this->lng->txt("too_many_targets_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		$insert_mode = 0;
		$this->object->moveQuestions($_SESSION['tst_qst_move_' . $this->object->getTestId()], $_POST['q_id'][0], $insert_mode);
		ilUtil::sendSuccess($this->lng->txt("msg_questions_moved"), true);
		unset($_SESSION['tst_qst_move_' . $this->object->getTestId()]);
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Insert checked questions after the actual selection
	*/
	public function insertQuestionsAfterObject()
	{
		// get all questions to move
		$move_questions = $_SESSION['tst_qst_move_' . $this->object->getTestId()];
		if (count($_POST['q_id']) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("no_target_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		if (count($_POST['q_id']) > 1)
		{
			ilUtil::sendFailure($this->lng->txt("too_many_targets_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		$insert_mode = 1;
		$this->object->moveQuestions($_SESSION['tst_qst_move_' . $this->object->getTestId()], $_POST['q_id'][0], $insert_mode);
		ilUtil::sendSuccess($this->lng->txt("msg_questions_moved"), true);
		unset($_SESSION['tst_qst_move_' . $this->object->getTestId()]);
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Insert questions from the questionbrowser into the test 
	*
	* @access	public
	*/
	function insertQuestionsObject()
	{
		$selected_array = (is_array($_POST['q_id'])) ? $_POST['q_id'] : array();
		if (!count($selected_array))
		{
			ilUtil::sendInfo($this->lng->txt("tst_insert_missing_question"), true);
			$this->ctrl->redirect($this, "browseForQuestions");
		}
		else
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$manscoring = FALSE;
			foreach ($selected_array as $key => $value) 
			{
				$this->object->insertQuestion( $this->testQuestionSetConfigFactory->getQuestionSetConfig(), $value );
				if (!$manscoring)
				{
					$manscoring = $manscoring | assQuestion::_needsManualScoring($value);
				}
			}
			$this->object->saveCompleteStatus( $this->testQuestionSetConfigFactory->getQuestionSetConfig() );
			if ($manscoring)
			{
				ilUtil::sendInfo($this->lng->txt("manscoring_hint"), TRUE);
			}
			else
			{
				ilUtil::sendSuccess($this->lng->txt("tst_questions_inserted"), TRUE);
			}
			$this->ctrl->redirect($this, "questions");
			return;
		}
	}

	public function filterAvailableQuestionsObject()
	{
		include_once "./Modules/Test/classes/tables/class.ilTestQuestionBrowserTableGUI.php";
		$table_gui = new ilTestQuestionBrowserTableGUI($this, 'browseForQuestions', $this->ref_id);
		$table_gui->resetOffset();
		$table_gui->writeFilterToSession();
		$this->ctrl->redirect($this, "browseForQuestions");
	}
	
	public function resetfilterAvailableQuestionsObject()
	{
		include_once "./Modules/Test/classes/tables/class.ilTestQuestionBrowserTableGUI.php";
		$table_gui = new ilTestQuestionBrowserTableGUI($this, 'browseForQuestions', $this->ref_id);
		$table_gui->resetOffset();
		$table_gui->resetFilter();
		$this->ctrl->redirect($this, "browseForQuestions");
	}
	
	/**
	* Creates a form to select questions from questionpools to insert the questions into the test 
	*
	* @access	public
	*/
	function questionBrowser()
	{
		global $ilAccess;

		$this->ctrl->setParameterByClass(get_class($this), "browse", "1");

		include_once "./Modules/Test/classes/tables/class.ilTestQuestionBrowserTableGUI.php";
		$table_gui = new ilTestQuestionBrowserTableGUI($this, 'browseForQuestions', $this->ref_id, (($ilAccess->checkAccess("write", "", $this->ref_id) ? true : false)));
		$arrFilter = array();
		foreach ($table_gui->getFilterItems() as $item)
		{
			if ($item->getValue() !== false)
			{
				$arrFilter[$item->getPostVar()] = $item->getValue();
			}
		}
		$data = $this->object->getAvailableQuestions($arrFilter, 1);
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	public function addQuestionObject()
	{
		global $lng, $ilCtrl, $tpl;

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";

		$ilCtrl->setParameter($this, 'qtype', $_REQUEST['qtype']);

		$form = new ilPropertyFormGUI();

		$form->setFormAction($ilCtrl->getFormAction($this, "executeCreateQuestion"));
		$form->setTitle($lng->txt("ass_create_question"));
		include_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';

		$pool = new ilObjQuestionPool();
		$questionTypes = $pool->getQuestionTypes(false, true);
		$options = array();

		// question type
		foreach($questionTypes as $label => $data)
		{
			$options[$data['question_type_id']] = $label; 
		}

		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($lng->txt("question_type"), "qtype");
		$si->setOptions($options);
		$form->addItem($si, true);

		// position
		$questions = $this->object->getQuestionTitlesAndIndexes();
		if($questions)
		{
			$si = new ilSelectInputGUI($lng->txt("position"), "position");
			$options = array('0' => $lng->txt('first'));
			foreach($questions as $key => $title)
			{
				$options[$key] = $lng->txt('behind') . ' '. $title . ' ['.$this->lng->txt('question_id_short') . ': '. $key .']';
			}
			$si->setOptions($options);
			$si->setValue($_REQUEST['q_id']);
			$form->addItem($si, true);
		}

		// content editing mode
		if( ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled() )
		{
			$ri = new ilRadioGroupInputGUI($lng->txt("tst_add_quest_cont_edit_mode"), "add_quest_cont_edit_mode");

			$ri->addOption(new ilRadioOption(
				$lng->txt('tst_add_quest_cont_edit_mode_default'),
				assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT
			));

			$ri->addOption(new ilRadioOption(
				$lng->txt('tst_add_quest_cont_edit_mode_page_object'),
				assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT
			));

			$ri->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT);

			$form->addItem($ri, true);
		}
		else
		{
			$hi = new ilHiddenInputGUI("question_content_editing_type");
			$hi->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT);
			$form->addItem($hi, true);
		}

		if($this->object->getPoolUsage())
		{
			// use pool
			$usage = new ilRadioGroupInputGUI($this->lng->txt("assessment_pool_selection"), "usage");
			$usage->setRequired(true);
			$no_pool = new ilRadioOption($this->lng->txt("assessment_no_pool"), 1);
			$usage->addOption($no_pool);
			$existing_pool = new ilRadioOption($this->lng->txt("assessment_existing_pool"), 3);
			$usage->addOption($existing_pool);
			$new_pool = new ilRadioOption($this->lng->txt("assessment_new_pool"), 2);
			$usage->addOption($new_pool);
			$form->addItem($usage);

			$usage->setValue(1);

			$questionpools = ilObjQuestionPool::_getAvailableQuestionpools(FALSE, FALSE, TRUE, FALSE, FALSE, "write");
			$pools_data = array();
			foreach($questionpools as $key => $p)
			{
				$pools_data[$key] = $p['title'];
			}
			$pools = new ilSelectInputGUI($this->lng->txt("select_questionpool"), "sel_qpl");
			$pools->setOptions($pools_data);
			$existing_pool->addSubItem($pools);

			$name = new ilTextInputGUI($this->lng->txt("name"), "txt_qpl");
			$name->setSize(50);
			$name->setMaxLength(50);
			$new_pool->addSubItem($name);
		}

		$form->addCommandButton("executeCreateQuestion", $lng->txt("submit"));
		$form->addCommandButton("questions", $lng->txt("cancel"));

		return $tpl->setContent($form->getHTML());
	}
	
	function questionsObject()
	{
		global $ilAccess, $ilTabs;

		$ilTabs->activateTab('assQuestions');
		
		// #12590
		$this->ctrl->setParameter($this, 'test_express_mode', '');

		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		if ($_GET['browse'])
		{
			return $this->questionbrowser();
		}

		$this->getQuestionsSubTabs();

		// #11631, #12994
		$this->ctrl->setParameter($this, 'q_id', '');

		if ($_GET["eqid"] && $_GET["eqpl"])
		{
			ilUtil::redirect("ilias.php?baseClass=ilObjQuestionPoolGUI&ref_id=" . $_GET["eqpl"] . "&cmd=editQuestionForTest&calling_test=".$_GET["ref_id"]."&q_id=" . $_GET["eqid"]);
		}
		
		if ($_GET["up"] > 0)
		{
			$this->object->questionMoveUp($_GET["up"]);
		}
		if ($_GET["down"] > 0)
		{
			$this->object->questionMoveDown($_GET["down"]);
		}

		if ($_GET["add"])
		{
			$selected_array = array();
			array_push($selected_array, $_GET["add"]);
			$total = $this->object->evalTotalPersons();
			if ($total)
			{
				// the test was executed previously
				ilUtil::sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("tst_insert_questions"));
			}
			$this->insertQuestions($selected_array);
			return;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questions.html", "Modules/Test");

		$total = $this->object->evalTotalPersons();
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			if($total != 0)
			{
				$link = $this->ctrl->getLinkTarget($this, "participants");
				$link = "<a href=\"".$link."\">".$this->lng->txt("test_has_datasets_warning_page_view_link")."</a>";
				ilUtil::sendInfo($this->lng->txt("test_has_datasets_warning_page_view")." ".$link);
			}
			else {
				global $ilToolbar;

				$ilToolbar->addButton($this->lng->txt("ass_create_question"), $this->ctrl->getLinkTarget($this, "addQuestion"));
				
				if ($this->object->getPoolUsage()) {
					$ilToolbar->addSeparator();
					$ilToolbar->addButton($this->lng->txt("tst_browse_for_questions"), $this->ctrl->getLinkTarget($this, 'browseForQuestions'));
				}

				$ilToolbar->addSeparator();
				$ilToolbar->addButton($this->lng->txt("random_selection"), $this->ctrl->getLinkTarget($this, "randomselect"));


				global $ilAccess, $ilUser, $lng, $ilCtrl;
				$online_access = false;
				if ($this->object->getFixedParticipants())
				{
					include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
					$online_access_result = ilObjTestAccess::_lookupOnlineTestAccess($this->object->getId(), $ilUser->getId());
					if ($online_access_result === true)
					{
						$online_access = true;
					}
				}

				if( $this->object->isOnline() && $this->object->isComplete( $this->testQuestionSetConfigFactory->getQuestionSetConfig() ) )
				{
					if ((!$this->object->getFixedParticipants() || $online_access) && $ilAccess->checkAccess("read", "", $this->ref_id))
					{
						$testSession = $this->testSessionFactory->getSession();
						$testSequence = $this->testSequenceFactory->getSequenceByTestSession($testSession);

						$testPlayerGUI = $this->testPlayerFactory->getPlayerGUI();

						$executable = $this->object->isExecutable($testSession, $ilUser->getId(), $allowPassIncrease = TRUE);
						
						if ($executable["executable"])
						{
							if ($testSession->getActiveId() > 0)
							{
								// resume test

								if ($testSequence->hasStarted($testSession))
								{
									$execTestLabel = $this->lng->txt("tst_resume_test");
									$execTestLink = $this->ctrl->getLinkTarget($testPlayerGUI, 'resumePlayer');
								}
								else
								{
									$execTestLabel = $this->object->getStartTestLabel($testSession->getActiveId());
									$execTestLink = $this->ctrl->getLinkTarget($testPlayerGUI, 'startPlayer');
								}
							}
							else
							{
								// start new test

								$execTestLabel = $this->object->getStartTestLabel($testSession->getActiveId());
								$execTestLink = $this->ctrl->getLinkTarget($testPlayerGUI, 'startPlayer');
							}

							$ilToolbar->addSeparator();
							$ilToolbar->addButton($execTestLabel, $execTestLink);
						}
					}
				}


			}
		}

		$this->tpl->setCurrentBlock("adm_content");
		include_once "./Modules/Test/classes/tables/class.ilTestQuestionsTableGUI.php";
		$checked_move = is_array($_SESSION['tst_qst_move_' . $this->object->getTestId()]) && (count($_SESSION['tst_qst_move_' . $this->object->getTestId()]));
		$table_gui = new ilTestQuestionsTableGUI($this, 'questions', (($ilAccess->checkAccess("write", "", $this->ref_id) ? true : false)), $checked_move, $total);
		$data = $this->object->getTestQuestions();
		$table_gui->setData($data);
		$this->tpl->setVariable('QUESTIONBROWSER', $table_gui->getHTML());	
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	function takenObject() {
	}
	
	/**
	* Deletes all user data for the test object
	*
	* Deletes all user data for the test object
	*
	* @access	public
	*/
	function confirmDeleteAllUserResultsObject()
	{
		global $ilDB, $lng;

		require_once 'Modules/Test/classes/class.ilTestParticipantData.php';
		
		$participantData = new ilTestParticipantData($ilDB, $lng);
		$participantData->load($this->object->getTestId());

		$this->object->removeTestResults($participantData);
		
		ilUtil::sendSuccess($this->lng->txt("tst_all_user_data_deleted"), true);
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Deletes the selected user data for the test object
	*
	* Deletes the selected user data for the test object
	*
	* @access	public
	*/
	function confirmDeleteSelectedUserDataObject()
	{
		global $ilDB, $lng;

		require_once 'Modules/Test/classes/class.ilTestParticipantData.php';
		$participantData = new ilTestParticipantData($ilDB, $lng);

		if( $this->object->getFixedParticipants() )
		{
			$participantData->setUserIds($_POST["chbUser"]);
		}
		else
		{
			$participantData->setActiveIds($_POST["chbUser"]);
		}

		$participantData->load($this->object->getTestId());

		$this->object->removeTestResults($participantData);

		ilUtil::sendSuccess($this->lng->txt("tst_selected_user_data_deleted"), true);
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Cancels the deletion of all user data for the test object
	*
	* Cancels the deletion of all user data for the test object
	*
	* @access	public
	*/
	function cancelDeleteSelectedUserDataObject()
	{
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Asks for a confirmation to delete all user data of the test object
	*
	* Asks for a confirmation to delete all user data of the test object
	* 
	* DEPRECATED?
	*
	* @access	public
	*/
	function deleteAllUserDataObject()
	{
		ilUtil::sendQuestion($this->lng->txt("confirm_delete_all_user_data"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_maintenance.html", "Modules/Test");

		$this->tpl->setCurrentBlock("confirm_delete");
		$this->tpl->setVariable("BTN_CONFIRM_DELETE_ALL", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_DELETE_ALL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Asks for a confirmation to delete all user data of the test object
	*/
	public function deleteAllUserResultsObject()
	{
		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this, "participants"));
		$cgui->setHeaderText($this->lng->txt("delete_all_user_data_confirmation"));
		$cgui->setCancel($this->lng->txt("cancel"), "participants");
		$cgui->setConfirm($this->lng->txt("proceed"), "confirmDeleteAllUserResults");
		
		$this->tpl->setContent($cgui->getHTML());
	}
	
	/**
	* Asks for a confirmation to delete selected user data of the test object
	*
	* Asks for a confirmation to delete selected user data of the test object
	*
	* @access	public
	*/
	function deleteSingleUserResultsObject()
	{
		if (count($_POST["chbUser"]) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("select_one_user"), TRUE);
			$this->ctrl->redirect($this, "participants");
		}
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($this->lng->txt("confirm_delete_single_user_data"));

		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($this->lng->txt("cancel"), "cancelDeleteSelectedUserData");
		$cgui->setConfirm($this->lng->txt("confirm"), "confirmDeleteSelectedUserData");
								
		include_once './Services/User/classes/class.ilObjUser.php';	
		foreach ($_POST["chbUser"] as $key => $active_id)
		{
			if ($this->object->getFixedParticipants())
			{
				$user_id = $active_id;
			}
			else
			{
				$user_id = $this->object->_getUserIdFromActiveId($active_id);
			}
			$user = ilObjUser::_lookupName($user_id);
		
			if ($this->object->getAnonymity())
			{
				$name = $this->lng->txt("anonymous");
			}
			else if($user["lastname"])
			{
				$name = $user["lastname"].", ".$user["firstname"]." (".
					$user["login"].")";
			}
			else
			{
				$name = $this->lng->txt("deleted_user");				
			}
		
			$cgui->addItem("chbUser[]", $active_id, $name,
				ilUtil::getImagePath("icon_usr.svg"), $this->lng->txt("usr"));
		}
		
		$this->tpl->setContent($cgui->getHTML());
	}
	
	/**
	* Creates the change history for a test
	*
	* Creates the change history for a test
	*
	* @access	public
	*/
	function historyObject()
	{
		include_once "./Modules/Test/classes/tables/class.ilTestHistoryTableGUI.php";
		$table_gui = new ilTestHistoryTableGUI($this, 'history');
		$table_gui->setTestObject($this->object);
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		$log =& ilObjAssessmentFolder::_getLog(0, time(), $this->object->getId(), TRUE);
		$table_gui->setData($log);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}
	
	function initImportForm($a_new_type)
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTarget("_top");
		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];
		$this->ctrl->setParameter($this, "new_type", $new_type);
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("import_tst"));

		// file
		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($this->lng->txt("import_file"), "xmldoc");
		$fi->setSuffixes(array("zip"));
		$fi->setRequired(true);
		$form->addItem($fi);

		// question pool
		include_once("./Modules/Test/classes/class.ilObjTest.php");
		$tst = new ilObjTest();
		$questionpools = $tst->getAvailableQuestionpools(TRUE, FALSE, TRUE, TRUE);
		if (count($questionpools))
		{
			$options = array("-1" => $this->lng->txt("dont_use_questionpool"));
			foreach ($questionpools as $key => $value)
			{
				$options[$key] = $value["title"];
			}

			$pool = new ilSelectInputGUI($this->lng->txt("select_questionpool"), "qpl");
			$pool->setOptions($options);
			$form->addItem($pool);
		}

		$form->addCommandButton("importFile", $this->lng->txt("import"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));

		return $form;
	}

 /**
	* Evaluates the actions on the participants page
	*
	* @access	public
	*/
	function participantsActionObject()
	{
		$command = $_POST["command"];
		if (strlen($command))
		{
			$method = $command . "Object";
			if (method_exists($this, $method))
			{
				$this->$method();
				return;
			}
		}
		$this->ctrl->redirect($this, "participants");
	}

 /**
	* Creates the output of the test participants
	*
	* @access	public
	*/
	function participantsObject()
	{
		global $ilAccess, $ilToolbar, $lng;
		
		$this->getParticipantsSubTabs();
		
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}
		
		if( $this->testQuestionSetConfigFactory->getQuestionSetConfig()->areDepenciesBroken($this->tree) )
		{
			ilUtil::sendFailure(
					$this->testQuestionSetConfigFactory->getQuestionSetConfig()->getDepenciesBrokenMessage($this->lng)
			);
		}
		elseif( $this->testQuestionSetConfigFactory->getQuestionSetConfig()->areDepenciesInVulnerableState($this->tree) )
		{
			ilUtil::sendInfo(
					$this->questionSetConfig->getDepenciesInVulnerableStateMessage($this->lng)
			);
		}

		if ($this->object->getFixedParticipants())
		{
			// search button
			include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$ilToolbar,
				array(
					'auto_complete_name'	=> $lng->txt('user'),
					'submit_name'			=> $lng->txt('add')
				)
			);

			$ilToolbar->addSeparator();
			$search_btn = ilLinkButton::getInstance();
			$search_btn->setCaption('tst_search_users');
			$search_btn->setUrl($this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
			$ilToolbar->addButtonInstance($search_btn);
			require_once  'Services/UIComponent/Button/classes/class.ilLinkButton.php';

			$participants =& $this->object->getInvitedUsers();
			$rows = array();
			foreach ($participants as $data)
			{
				$maxpass = $this->object->_getMaxPass($data["active_id"]);
				if (!is_null($maxpass))
				{
					$maxpass += 1;
				}
				$access = "";
				if (strlen($data["active_id"]))
				{
					$last_access = $this->object->_getLastAccess($data["active_id"]);
					$access = $last_access;
				}
				$this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $data['active_id']);
				
				if ($data['active_id'] == null) // if no active id is set, user is invitee not participant...
				{
					if ( strlen($data["firstname"].$data["lastname"]) == 0 )
					{
						$fullname = $lng->txt("deleted_user");
					}
					else if($this->object->getAnonymity())
					{
					 	$fullname = $lng->txt('anonymous');	
					}
					else
					{
						$fullname = trim($data["lastname"] . ", " . $data["firstname"] . " " . $data["title"]);
					}
				} else {
					include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
					$fullname = ilObjTestAccess::_getParticipantData($data['active_id']);					
				}
				
				array_push($rows, array(
					'usr_id' => $data["usr_id"],
					'active_id' => $data['active_id'],
					'login' => $data["login"],
					'clientip' => $data["clientip"],
					'firstname' => $data["firstname"],
					'lastname' => $data["lastname"],
					'name' => $fullname,
					'started' => ($data["active_id"] > 0) ? 1 : 0,
					'finished' => ($data["test_finished"] == 1) ? 1 : 0,
					'access' => $access,
					'maxpass' => $maxpass,
					'result' => $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'outParticipantsResultsOverview')
				));
			}
			include_once "./Modules/Test/classes/tables/class.ilTestFixedParticipantsTableGUI.php";
			$table_gui = new ilTestFixedParticipantsTableGUI( $this, 'participants',
					$this->testQuestionSetConfigFactory->getQuestionSetConfig()->areDepenciesBroken(),
					$this->object->getAnonymity(), count($rows)
			);
			$table_gui->setFilterCommand('fpSetFilter');
			$table_gui->setResetCommand('fpResetFiler');
			$rows = $this->applyFilterCriteria($rows);
			$table_gui->setData($rows);
			$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());

			if(count($rows) > 0)
			{
				$ilToolbar->addSeparator();
				$delete_all_results_btn = ilLinkButton::getInstance();
				$delete_all_results_btn->setCaption('delete_all_user_data');
				$delete_all_results_btn->setUrl($this->ctrl->getLinkTarget($this, 'deleteAllUserResults'));
				$ilToolbar->addButtonInstance($delete_all_results_btn);
			}
		}
		else
		{
			$participants =& $this->object->getTestParticipants();
			$rows = array();
			foreach ($participants as $data)
			{
				$maxpass = $this->object->_getMaxPass($data["active_id"]);
				if (!is_null($maxpass))
				{
					$maxpass += 1;
				}
				$access = "";
				if (strlen($data["active_id"]))
				{
					$last_access = $this->object->_getLastAccess($data["active_id"]);
					$access = $last_access;
				}
				$this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $data['active_id']);

				include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
				$fullname = ilObjTestAccess::_getParticipantData($data['active_id']);					
				array_push($rows, array(
					'usr_id' => $data["active_id"],
					'active_id' => $data['active_id'],
					'login' => $data["login"],
					'name' => $fullname,
					'firstname' => $data["firstname"],
					'lastname' => $data["lastname"],
					'started' => ($data["active_id"] > 0) ? 1 : 0,
					'finished' => ($data["test_finished"] == 1) ? 1 : 0,
					'access' => $access,
					'maxpass' => $maxpass,
					'result' => $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'outParticipantsResultsOverview')
				));
			}
			include_once "./Modules/Test/classes/tables/class.ilTestParticipantsTableGUI.php";
			$table_gui = new ilTestParticipantsTableGUI( $this, 'participants',
					$this->testQuestionSetConfigFactory->getQuestionSetConfig()->areDepenciesBroken(),
					$this->object->getAnonymity(), count($rows)
			);

			if(count($rows) > 0)
			{
				require_once  'Services/UIComponent/Button/classes/class.ilLinkButton.php';
				$delete_all_results_btn = ilLinkButton::getInstance();
				$delete_all_results_btn->setCaption('delete_all_user_data');
				$delete_all_results_btn->setUrl($this->ctrl->getLinkTarget($this, 'deleteAllUserResults'));
				$ilToolbar->addStickyItem($delete_all_results_btn);
			}

			$table_gui->setFilterCommand('npSetFilter');
			$table_gui->setResetCommand('npResetFilter');
			$rows = $this->applyFilterCriteria($rows);
			$table_gui->setData($rows);
			$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
		}
	}
	
	public function timingOverviewObject()
	{
		$this->getParticipantsSubTabs();

		include_once "./Modules/Test/classes/tables/class.ilTimingOverviewTableGUI.php";
		$table_gui = new ilTimingOverviewTableGUI($this, 'timingOverview');
		
		$participants =& $this->object->getTestParticipants();#
		$times = $this->object->getStartingTimeOfParticipants();
		$addons = $this->object->getTimeExtensionsOfParticipants();

		$tbl_data = array();
		foreach ($participants as $participant)
		{
			$tblRow = array();
				
			$started = "";
			if ($times[$participant['active_id']])
			{
				$started = $this->lng->txt('tst_started').': '.ilDatePresentation::formatDate(new ilDateTime($times[$participant['active_id']], IL_CAL_DATETIME));
				$tblRow['started'] = $started;
			}
			else
			{
				$tblRow['started'] = '';
			}
			
			if ($addons[$participant['active_id']] > 0) 
			{
				$tblRow['extratime'] = $addons[$participant['active_id']];
			}

			$tblRow['login'] = $participant['login'];

			if ($this->object->getAnonymity())
			{
				$tblRow['name'] = $this->lng->txt("anonymous");
			}
			else
			{
				$tblRow['name'] = $participant['lastname'] . ', ' . $participant['firstname'];
			}

			$tbl_data[] = $tblRow;
		}
		$table_gui->setData($tbl_data);
		
		$this->tpl->setContent($table_gui->getHTML());
	}
	
	public function timingObject()
	{
		$this->getParticipantsSubTabs();

		global $ilAccess;
		
		if (!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		if ($this->object->getProcessingTimeInSeconds() > 0 && $this->object->getNrOfTries() == 1)
		{
			$form = $this->formTimingObject();
			if (count($_POST) && $form->checkInput())
			{
				$res = $this->object->addExtraTime($form->getInput('participant'), $form->getInput('extratime'));
				ilUtil::sendSuccess(sprintf($this->lng->txt('tst_extratime_added'), $form->getInput('extratime')), true);
				$this->ctrl->redirect($this, 'timingOverview');
			}
			else
			{
				return $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("tst_extratime_notavailable"));
		}
	}

	private function formTimingObject()
	{
		global $ilAccess;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("tst_change_workingtime");
		$form->setTitle($this->lng->txt("tst_change_workingtime"));

		// test users
		$participantslist = new ilSelectInputGUI($this->lng->txt('participants'), "participant");
		$participants =& $this->object->getTestParticipants();
		$times = $this->object->getStartingTimeOfParticipants();
		$addons = $this->object->getTimeExtensionsOfParticipants();
		$options = array(
			'' => $this->lng->txt('please_select'),
			'0' => $this->lng->txt('all_participants')
		);
		foreach ($participants as $participant)
		{
			$started = "";

			if ($this->object->getAnonymity())
			{
				$name = $this->lng->txt("anonymous");
			}
			else
			{
				$name = $participant['lastname'] . ', ' . $participant['firstname']; 
			}
			
			
			if ($times[$participant['active_id']])
			{
				$started = ", ".$this->lng->txt('tst_started').': '.ilDatePresentation::formatDate(new ilDateTime($times[$participant['active_id']], IL_CAL_DATETIME));
			}
			if ($addons[$participant['active_id']] > 0) $started .= ", " . $this->lng->txt('extratime') . ': ' . $addons[$participant['active_id']] . ' ' . $this->lng->txt('minutes');
			$options[$participant['active_id']] = $participant['login'] . ' (' .$name. ')'.$started;
		}
		$participantslist->setRequired(true);
		$participantslist->setOptions($options);
		$form->addItem($participantslist);

		// extra time
		$extratime = new ilNumberInputGUI($this->lng->txt("extratime"), "extratime");
		$extratime->setInfo($this->lng->txt('tst_extratime_info'));
		$extratime->setRequired(true);
		$extratime->setMinValue(0);
		$extratime->setMinvalueShouldBeGreater(false);
		$extratime->setSuffix($this->lng->txt('minutes'));
		$extratime->setSize(5);
		$form->addItem($extratime);

		if (is_array($_POST) && strlen($_POST['cmd']['timing'])) $form->setValuesByArray($_POST);

		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form->addCommandButton("timing", $this->lng->txt("save"));
		$form->addCommandButton('timingOverview', $this->lng->txt("cancel"));
		return $form;
	}
	
	public function showTimingFormObject()
	{
		$form = $this->formTimingObject();
		$this->tpl->setContent($form->getHTML());
	}	
	
	function applyFilterCriteria($in_rows)
	{
		global $ilDB;
		$sess_filter = $_SESSION['form_tst_participants_' . $this->ref_id]['selection'];
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
			$result = $ilDB->query(
				'SELECT count(solution_id) count
				FROM tst_solutions
				WHERE active_fi = ' . $ilDB->quote($row['active_id'])
			);
			$count = $ilDB->fetchAssoc($result);
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
	
	function fpSetFilterObject()
	{
		include_once("./Modules/Test/classes/tables/class.ilTestFixedParticipantsTableGUI.php");
		$table_gui = new ilTestFixedParticipantsTableGUI($this, "participants", false, $this->object->getAnonymity(), 0);
		$table_gui->writeFilterToSession();        // writes filter to session
		$table_gui->resetOffset();                // sets record offest to 0 (first page)
		$this->participantsObject();
	}

	function fpResetFilterObject()
	{
		include_once("./Modules/Test/classes/tables/class.ilTestFixedParticipantsTableGUI.php");
		$table_gui = new ilTestFixedParticipantsTableGUI(
			$this, "participants", false, $this->object->getAnonymity(), 0
		);
		$table_gui->resetFilter();        // writes filter to session
		$table_gui->resetOffset();                // sets record offest to 0 (first page)
		$this->participantsObject();
	}

	function npSetFilterObject()
	{
		include_once("./Modules/Test/classes/tables/class.ilTestParticipantsTableGUI.php");
		$table_gui = new ilTestParticipantsTableGUI(
			$this, "participants", false, $this->object->getAnonymity(), 0
		);
		$table_gui->writeFilterToSession();        // writes filter to session
		$table_gui->resetOffset();                // sets record offest to 0 (first page)
		$this->participantsObject();
		
	}
	
	function npResetFilterObject()
	{
		include_once("./Modules/Test/classes/tables/class.ilTestParticipantsTableGUI.php");
		$table_gui = new ilTestParticipantsTableGUI(
			$this, "participants", false, $this->object->getAnonymity(), 0
		);
		$table_gui->resetFilter();        // writes filter to session
		$table_gui->resetOffset();                // sets record offest to 0 (first page)
		$this->participantsObject();
		
	}
	
 /**
	* Shows the pass overview and the answers of one ore more users for the scored pass
	*
	* @access	public
	*/
	function showDetailedResultsObject()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = TRUE, $show_answers = TRUE, $show_reached_points = TRUE);
	}

 /**
	* Shows the answers of one ore more users for the scored pass
	*
	* @access	public
	*/
	function showUserAnswersObject()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = FALSE, $show_answers = TRUE);
	}

 /**
	* Shows the pass overview of the scored pass for one ore more users
	*
	* @access	public
	*/
	function showPassOverviewObject()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = TRUE, $show_answers = FALSE);
	}
	
 /**
	* Shows the pass overview of the scored pass for one ore more users
	*
	* @access	public
	*/
	function showUserResults($show_pass_details, $show_answers, $show_reached_points = FALSE)
	{
		$show_user_results = $_SESSION["show_user_results"];
		
		if (count($show_user_results) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("select_one_user"), TRUE);
			$this->ctrl->redirect($this, "participants");
		}


		$template = $this->createUserResults( $show_pass_details, $show_answers, $show_reached_points, $show_user_results);

		if($template instanceof ilTemplate)
		{
			$this->tpl->setVariable("ADM_CONTENT", $template->get());
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
			if ($this->object->getShowSolutionAnswersOnly())
			{
				$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
			}
		}
	}

	function removeParticipantObject()
	{
		if (is_array($_POST["chbUser"])) 
		{
			foreach ($_POST["chbUser"] as $user_id)
			{
				$this->object->disinviteUser($user_id);
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("select_one_user"), true);
		}
		$this->ctrl->redirect($this, "participants");
	}
	
	function saveClientIPObject()
	{
		if (is_array($_POST["chbUser"])) 
		{
			foreach ($_POST["chbUser"] as $user_id)
			{
				$this->object->setClientIP($user_id, $_POST["clientip_".$user_id]);
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("select_one_user"), true);
		}
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Print tab to create a print of all questions with points and solutions
	*
	* Print tab to create a print of all questions with points and solutions
	*
	* @access	public
	*/
	function printobject() 
	{
		global $ilAccess, $ilias;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}
		
		$isPdfDeliveryRequest = isset($_GET['pdf']) && $_GET['pdf'];
		
		$this->getQuestionsSubTabs();
		$template = new ilTemplate("tpl.il_as_tst_print_test_confirm.html", TRUE, TRUE, "Modules/Test");

		if(!$isPdfDeliveryRequest) // #15243
		{
			$this->ctrl->setParameter($this, "pdf", "1");
			$template->setCurrentBlock("pdf_export");
			$template->setVariable("PDF_URL", $this->ctrl->getLinkTarget($this, "print"));
			$this->ctrl->setParameter($this, "pdf", "");
			$template->setVariable("PDF_TEXT", $this->lng->txt("pdf_export"));
			$template->parseCurrentBlock();

			$template->setCurrentBlock("navigation_buttons");
			$template->setVariable("BUTTON_PRINT", $this->lng->txt("print"));
			$template->parseCurrentBlock();
		}

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		
		global $ilUser;		
		$print_date = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
		$max_points= 0;
		$counter = 1;

		require_once 'Modules/Test/classes/class.ilTestQuestionHeaderBlockBuilder.php';
		$questionHeaderBlockBuilder = new ilTestQuestionHeaderBlockBuilder($this->lng);
		$questionHeaderBlockBuilder->setHeaderMode($this->object->getTitleOutput());

		foreach ($this->object->questions as $question) 
		{		
			$template->setCurrentBlock("question");
			$question_gui = $this->object->createQuestionGUI("", $question);
			
			if( $isPdfDeliveryRequest )
			{
				$question_gui->setOutputMode(assQuestionGUI::OUTPUT_MODE_PDF);
			}

			$questionHeaderBlockBuilder->setQuestionTitle($question_gui->object->getTitle());
			$questionHeaderBlockBuilder->setQuestionPoints($question_gui->object->getMaximumPoints());
			$questionHeaderBlockBuilder->setQuestionPosition($counter);
			$template->setVariable("QUESTION_HEADER", $questionHeaderBlockBuilder->getHTML());

			$template->setVariable("TXT_QUESTION_ID", $this->lng->txt('question_id_short'));
			$template->setVariable("QUESTION_ID", $question_gui->object->getId());
			$result_output = $question_gui->getSolutionOutput("", NULL, FALSE, TRUE, FALSE, $this->object->getShowSolutionFeedback());
			if (strlen($result_output) == 0) $result_output = $question_gui->getPreview(FALSE);
			$template->setVariable("SOLUTION_OUTPUT", $result_output);
			$template->parseCurrentBlock("question");
			$counter ++;
			$max_points += $question_gui->object->getMaximumPoints();
		}

		$template->setVariable("TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$template->setVariable("PRINT_TEST", ilUtil::prepareFormOutput($this->lng->txt("tst_print")));
		$template->setVariable("TXT_PRINT_DATE", ilUtil::prepareFormOutput($this->lng->txt("date")));
		$template->setVariable("VALUE_PRINT_DATE", ilUtil::prepareFormOutput(strftime("%c",$print_date)));
		$template->setVariable("TXT_MAXIMUM_POINTS", ilUtil::prepareFormOutput($this->lng->txt("tst_maximum_points")));
		$template->setVariable("VALUE_MAXIMUM_POINTS", ilUtil::prepareFormOutput($max_points));
		
		if( $isPdfDeliveryRequest )
		{
			//$this->object->deliverPDFfromHTML($template->get(), $this->object->getTitle());
			require_once 'class.ilTestPDFGenerator.php';
			ilTestPDFGenerator::generatePDF($template->get(), ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD, $this->object->getTitle());
		}
		else
		{
			$this->tpl->setVariable("PRINT_CONTENT", $template->get());
		}
	}

	/**
	 * Review tab to create a print of all questions without points and solutions
	 *
	 * Review tab to create a print of all questions without points and solutions
	 *
	 * @access	public
	 */
	function reviewobject()
	{
		global $ilAccess, $ilias;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}
		$this->getQuestionsSubTabs();
		$template = new ilTemplate("tpl.il_as_tst_print_test_confirm.html", TRUE, TRUE, "Modules/Test");

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");

		global $ilUser;
		$print_date = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
		$max_points= 0;
		$counter = 1;

		require_once 'Modules/Test/classes/class.ilTestQuestionHeaderBlockBuilder.php';
		$questionHeaderBlockBuilder = new ilTestQuestionHeaderBlockBuilder($this->lng);
		$questionHeaderBlockBuilder->setHeaderMode($this->object->getTitleOutput());
		
		foreach ($this->object->questions as $question)
		{
			$template->setCurrentBlock("question");
			$question_gui = $this->object->createQuestionGUI("", $question);
			
			$questionHeaderBlockBuilder->setQuestionTitle($question_gui->object->getTitle());
			$questionHeaderBlockBuilder->setQuestionPoints($question_gui->object->getMaximumPoints());
			$questionHeaderBlockBuilder->setQuestionPosition($counter);
			$template->setVariable("QUESTION_HEADER", $questionHeaderBlockBuilder->getHTML());
			
			/** @var $question_gui assQuestionGUI  */
			//$result_output = $question_gui->getTestOutput('', NULL, FALSE, FALSE, FALSE);
			$result_output = $question_gui->getPreview(false);

			if (strlen($result_output) == 0) $result_output = $question_gui->getPreview(FALSE);
			$template->setVariable("SOLUTION_OUTPUT", $result_output);
			$template->parseCurrentBlock("question");
			$counter ++;
			$max_points += $question_gui->object->getMaximumPoints();
		}



		$template->setVariable("TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$template->setVariable("PRINT_TEST", ilUtil::prepareFormOutput($this->lng->txt("review_view")));
		$template->setVariable("TXT_PRINT_DATE", ilUtil::prepareFormOutput($this->lng->txt("date")));
		$template->setVariable("VALUE_PRINT_DATE", ilUtil::prepareFormOutput(strftime("%c",$print_date)));
		$template->setVariable("TXT_MAXIMUM_POINTS", ilUtil::prepareFormOutput($this->lng->txt("tst_maximum_points")));
		$template->setVariable("VALUE_MAXIMUM_POINTS", ilUtil::prepareFormOutput($max_points));

		if (array_key_exists("pdf", $_GET) && ($_GET["pdf"] == 1))
		{
			//$this->object->deliverPDFfromHTML($template->get(), $this->object->getTitle());
			require_once 'class.ilTestPDFGenerator.php';
			$content = $template->get();
			ilTestPDFGenerator::generatePDF($template->get(), ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD, $this->object->getTitle());
		}
		else
		{
			$this->ctrl->setParameter($this, "pdf", "1");
			$template->setCurrentBlock("pdf_export");
			$template->setVariable("PDF_URL", $this->ctrl->getLinkTarget($this, "review"));
			$this->ctrl->setParameter($this, "pdf", "");
			$template->setVariable("PDF_TEXT", $this->lng->txt("pdf_export"));
			$template->parseCurrentBlock();

			$template->setCurrentBlock("navigation_buttons");
			$template->setVariable("BUTTON_PRINT", $this->lng->txt("print"));
			$template->parseCurrentBlock();
			
			
			$this->tpl->setVariable("PRINT_CONTENT", $template->get());
		}
	}	
	
	function addParticipantsObject($a_user_ids = array())
	{
		$countusers = 0;
		// add users 
		if (is_array($a_user_ids))
		{
			$i = 0;
			foreach ($a_user_ids as $user_id)
			{
				$client_ip = $_POST["client_ip"][$i];
				$this->object->inviteUser($user_id, $client_ip);
				$countusers++;
				$i++;
			}
		}
		$message = "";
		if ($countusers)
		{
			$message = $this->lng->txt("tst_invited_selected_users");
		}
		if (strlen($message))
		{
			ilUtil::sendInfo($message, TRUE);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("tst_invited_nobody"), TRUE);
			return false;
		}
		
		$this->ctrl->redirect($this, "participants");
	}

	/**
	 * Displays the settings page for test defaults
	 */
	public function defaultsObject()
	{
		/**
		 * @var $ilAccess  ilAccessHandler
		 * @var $ilToolbar ilToolbarGUI
		 * @var $tpl       ilTemplage
		 */
		global $ilAccess, $ilToolbar, $tpl;

		if(!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		$ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'addDefaults'));
		$ilToolbar->addFormButton($this->lng->txt('add'), 'addDefaults');
		require_once 'Services/Form/classes/class.ilTextInputGUI.php';
		$ilToolbar->addInputItem(new ilTextInputGUI($this->lng->txt('tst_defaults_defaults_of_test'), 'name'), true);

		require_once 'Modules/Test/classes/tables/class.ilTestPersonalDefaultSettingsTableGUI.php';
		$table    = new ilTestPersonalDefaultSettingsTableGUI($this, 'defaults');
		$defaults = $this->object->getAvailableDefaults();
		$table->setData((array)$defaults);
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * Deletes selected test defaults
	 */
	public function deleteDefaultsObject()
	{
		if(isset($_POST['chb_defaults']) && is_array($_POST['chb_defaults']) && count($_POST['chb_defaults']))
		{
			foreach($_POST['chb_defaults'] as $test_default_id)
			{
				$this->object->deleteDefaults($test_default_id);
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
		}
		$this->defaultsObject();
	}

	/**
	 * 
	 */
	public function confirmedApplyDefaultsObject()
	{
		$this->applyDefaultsObject(true);
		return;
	}

	/**
	 * Applies the selected test defaults
	 */
	public function applyDefaultsObject($confirmed = false)
	{
		if( count($_POST["chb_defaults"]) != 1 )
		{
			ilUtil::sendInfo(
				$this->lng->txt("tst_defaults_apply_select_one")
			);
			
			return $this->defaultsObject();
		}
			
		// do not apply if user datasets exist
		if($this->object->evalTotalPersons() > 0)
		{
			ilUtil::sendInfo(
				$this->lng->txt("tst_defaults_apply_not_possible")
			);

			return $this->defaultsObject();
		}

		$defaults =& $this->object->getTestDefaults($_POST["chb_defaults"][0]);
		$defaultSettings = unserialize($defaults["defaults"]);

		if( isset($defaultSettings['isRandomTest']) )
		{
			if( $defaultSettings['isRandomTest'] )
			{
				$newQuestionSetType = ilObjTest::QUESTION_SET_TYPE_RANDOM;
				$this->object->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_RANDOM);
			}
			else
			{
				$newQuestionSetType = ilObjTest::QUESTION_SET_TYPE_FIXED;
				$this->object->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_FIXED);
			}
		}
		elseif( isset($defaultSettings['questionSetType']) )
		{
			$newQuestionSetType = $defaultSettings['questionSetType'];
		}
		$oldQuestionSetType = $this->object->getQuestionSetType();
		$questionSetTypeSettingSwitched = ( $oldQuestionSetType != $newQuestionSetType );

		$oldQuestionSetConfig = $this->testQuestionSetConfigFactory->getQuestionSetConfigByType($oldQuestionSetType);

		switch( true )
		{
			case !$questionSetTypeSettingSwitched:
			case !$oldQuestionSetConfig->doesQuestionSetRelatedDataExist():
			case $confirmed:

				break;
			
			default:

				require_once 'Modules/Test/classes/confirmations/class.ilTestSettingsChangeConfirmationGUI.php';
				$confirmation = new ilTestSettingsChangeConfirmationGUI($this->lng, $this->object);

				$confirmation->setFormAction( $this->ctrl->getFormAction($this) );
				$confirmation->setCancel($this->lng->txt('cancel'), 'defaults');
				$confirmation->setConfirm($this->lng->txt('confirm'), 'confirmedApplyDefaults');

				$confirmation->setOldQuestionSetType($this->object->getQuestionSetType());
				$confirmation->setNewQuestionSetType($newQuestionSetType);
				$confirmation->setQuestionLossInfoEnabled(false);
				$confirmation->build();

				$confirmation->populateParametersFromPost();

				$this->tpl->setContent( $this->ctrl->getHTML($confirmation) );
				
				return;
		}

		if( $questionSetTypeSettingSwitched && $this->object->isOnline() )
		{
			$this->object->setOnline(false);

			$info = $this->lng->txt("tst_set_offline_due_to_switched_question_set_type_setting");

			ilUtil::sendInfo($info, true);
		}

		$this->object->applyDefaults($defaults);

		ilUtil::sendSuccess($this->lng->txt("tst_defaults_applied"), true);
		
		if( $questionSetTypeSettingSwitched && $oldQuestionSetConfig->doesQuestionSetRelatedDataExist() )
		{
			$oldQuestionSetConfig->removeQuestionSetRelatedData();
		}

		$this->ctrl->redirect($this, 'defaults');
	}
	
	/**
	* Adds the defaults of this test to the defaults
	*/
	function addDefaultsObject()
	{
		if (strlen($_POST["name"]) > 0)
		{
			$this->object->addDefaults($_POST['name']);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("tst_defaults_enter_name"));
		}
		$this->defaultsObject();
	}
	
	private function isCommandClassAnyInfoScreenChild()
	{
		if( in_array($this->ctrl->getCmdClass(), self::$infoScreenChildClasses) )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		#if( !include 'competenzenRocker.php' ) exit;

		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}
	
	function redirectToInfoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen($_GET['lock']);
	}
	
	/**
	* show information screen
	*/
	function infoScreen($session_lock = "")
	{
		/**
		 * @var $ilAccess  ilAccessHandler
		 * @var $ilUser    ilObjUser
		 * @var $ilToolbar ilToolbarGUI
		 */
		global $ilAccess, $ilUser, $ilToolbar;
		
		require_once 'Modules/Test/classes/class.ilTestDynamicQuestionSetFilterSelection.php';
		
		require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
		require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';

		$testQuestionSetConfig = $this->testQuestionSetConfigFactory->getQuestionSetConfig();
		$testSession = $this->testSessionFactory->getSession();
		$testSequence = $this->testSequenceFactory->getSequenceByTestSession($testSession);
		$testSequence->loadFromDb();
		$testSequence->loadQuestions($testQuestionSetConfig, new ilTestDynamicQuestionSetFilterSelection());
		
		$testPlayerGUI = $this->testPlayerFactory->getPlayerGUI();
		
		if ($_GET['createRandomSolutions'])
		{
			global $ilCtrl;
			
			$this->object->createRandomSolutions($_GET['createRandomSolutions']);
			
			$ilCtrl->redirect($this);
		}

		if (!$ilAccess->checkAccess("read", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		
		if( $this->isCommandClassAnyInfoScreenChild() )
		{
			return $this->ctrl->forwardCommand($info);
		}
		
		$this->ctrl->setParameter($testPlayerGUI, "sequence", $testSession->getLastSequence());
		
		$info->setFormAction($this->ctrl->getFormAction($testPlayerGUI));
		
		if (strlen($session_lock))
		{
			$info->addHiddenElement("lock", $session_lock);
		}
		else
		{
			$info->addHiddenElement("lock", md5($_COOKIE['PHPSESSID'] . time()));
		}
		$online_access = false;
		if ($this->object->getFixedParticipants())
		{
			include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
			$online_access_result = ilObjTestAccess::_lookupOnlineTestAccess($this->object->getId(), $ilUser->getId());
			if ($online_access_result === true)
			{
				$online_access = true;
			}
			else
			{
				ilUtil::sendInfo($online_access_result);
			}
		}

		$enter_anonymous_code = false;
		if( $this->object->isOnline() && $this->object->isComplete( $this->testQuestionSetConfigFactory->getQuestionSetConfig() ) )
		{
			if ((!$this->object->getFixedParticipants() || $online_access) && $ilAccess->checkAccess("read", "", $this->ref_id))
			{
				$executable = $this->object->isExecutable($testSession, $ilUser->getId(), $allowPassIncrease = TRUE
				);
				if ($executable["executable"])
				{
					if( $this->object->areObligationsEnabled() && $this->object->hasObligations($this->object->getTestId()) )
					{
						ilUtil::sendInfo($GLOBALS['lng']->txt('tst_test_contains_obligatory_questions'));
					}
					
					if ($testSession->getActiveId() > 0)
					{
						// resume test
						require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
						$testPassesSelector = new ilTestPassesSelector($GLOBALS['ilDB'], $this->object);
						$testPassesSelector->setActiveId($testSession->getActiveId());
						$testPassesSelector->setLastFinishedPass($testSession->getLastFinishedPass());
						
						$closedPasses = $testPassesSelector->getClosedPasses();
						$existingPasses = $testPassesSelector->getExistingPasses();
						
						if ($existingPasses > $closedPasses)
						{
							$btn = ilSubmitButton::getInstance();
							$btn->setCaption('tst_resume_test');
							$btn->setCommand('resumePlayer');
							$btn->setPrimary(true);
							$big_button[] = $btn;
						}
						else
						{
							$btn = ilSubmitButton::getInstance();
							$btn->setCaption($this->object->getStartTestLabel($testSession->getActiveId()), false);
							$btn->setCommand('startPlayer');
							$btn->setPrimary(true);
							$big_button[] = $btn;
						}
					}
					else
					{
						// start new test
						$btn = ilSubmitButton::getInstance();
						$btn->setCaption($this->object->getStartTestLabel($testSession->getActiveId()), false);
						$btn->setCommand('startPlayer');
						$btn->setPrimary(true);
						$big_button[] = $btn;
					}
				}
				else
				{
					ilUtil::sendInfo($executable["errormessage"]);
				}
				if ($testSession->getActiveId() > 0)
				{
					// test results button

					require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
					$testPassesSelector = new ilTestPassesSelector($GLOBALS['ilDB'], $this->object);
					$testPassesSelector->setActiveId($testSession->getActiveId());
					$testPassesSelector->setLastFinishedPass($testSession->getLastFinishedPass());
					
					if ($this->object->canShowTestResults($testSession, $ilUser->getId()) && count($testPassesSelector->getReportablePasses())) 
					{
						$btn = ilLinkButton::getInstance();
						$btn->setCaption('tst_show_results');
						$btn->setUrl($this->ctrl->getLinkTargetByClass('ilTestEvaluationGUI',  'outUserResultsOverview'));
						$btn->setPrimary(false);
						$big_button[] = $btn;

						if ($this->object->getHighscoreEnabled())
						{
							// Can also compare results then
							$btn = ilLinkButton::getInstance();
							$btn->setCaption('tst_show_toplist');
							$btn->setUrl($this->ctrl->getLinkTargetByClass('ilTestToplistGUI', 'outResultsToplist'));
							$btn->setPrimary(false);
							$big_button[] = $btn;
						}

						if( $this->object->isSkillServiceToBeConsidered() )
						{
							require_once 'Modules/Test/classes/class.ilTestSkillEvaluationGUI.php';

							$btn = ilLinkButton::getInstance();
							$btn->setCaption('tst_show_comp_results');
							$btn->setUrl($this->ctrl->getLinkTargetByClass('ilTestSkillEvaluationGUI', ilTestSkillEvaluationGUI::CMD_SHOW));
							$btn->setPrimary(false);
							$big_button[] = $btn;
						}
					}
					
				}
			}
			if ($testSession->getActiveId() > 0)
			{
				if ($this->object->canShowSolutionPrintview($ilUser->getId()))
				{
					$btn = ilLinkButton::getInstance();
					$btn->setCaption('tst_list_of_answers_show');
					$btn->setUrl($this->ctrl->getLinkTargetByClass('ilTestEvaluationGUI', 'outUserListOfAnswerPasses'));
					$btn->setPrimary(false);
					$big_button[] = $btn;
				}
			}
			
			if( $this->isDeleteDynamicTestResultsButtonRequired($testSession, $testSequence) )
			{
				$this->populateDeleteDynamicTestResultsButton($testSession, $big_button);
			}
			
			if($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
			{
				$enter_anonymous_code = true;
			}
		}

		if( !$this->object->isOnline() && !$testQuestionSetConfig->areDepenciesBroken() )
 		{
			$message = $this->lng->txt("test_is_offline");

			if($ilAccess->checkAccess("write", "", $this->ref_id))
			{
				$message .= "<br /><a href=\"".$this->ctrl->getLinkTargetByClass('ilobjtestsettingsgeneralgui')."\">".
					$this->lng->txt("test_edit_settings")."</a>";
			}

			ilUtil::sendInfo($message);
		}

		if( $this->object->isSkillServiceToBeConsidered() && $this->areSkillLevelThresholdsMissing() )
		{
			ilUtil::sendFailure($this->getSkillLevelThresholdsMissingInfo());
		}

		if($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$testQuestionSetConfig = $this->testQuestionSetConfigFactory->getQuestionSetConfig();
			
			if( $testQuestionSetConfig->areDepenciesBroken() )
			{
				ilUtil::sendFailure( $testQuestionSetConfig->getDepenciesBrokenMessage($this->lng) );
				
				$big_button = array();
				$enter_anonymous_code = false;
			}
			elseif( $testQuestionSetConfig->areDepenciesInVulnerableState() )
			{
				ilUtil::sendInfo( $testQuestionSetConfig->getDepenciesInVulnerableStateMessage($this->lng) );
			}
		}
		
		if ($this->object->getShowInfo())
		{
			$info->enablePrivateNotes();
		}

		if($big_button || $enter_anonymous_code)
		{
			$ilToolbar->setFormAction($this->ctrl->getFormAction($testPlayerGUI));

			foreach($big_button as $button)
			{
				$ilToolbar->addButtonInstance($button);
			}

			if($enter_anonymous_code)
			{
				if($big_button)
				{
					$ilToolbar->addSeparator();
				}

				require_once 'Services/Form/classes/class.ilTextInputGUI.php';
				$anonymous_id = new ilTextInputGUI($this->lng->txt('enter_anonymous_code'), 'anonymous_id');
				$anonymous_id->setSize(8);
				$ilToolbar->addInputItem($anonymous_id, true);
				$ilToolbar->addFormButton($this->lng->txt('submit'), 'setAnonymousId');
			}

			$ilToolbar->setCloseFormTag(false);
			$info->setOpenFormTag(false);
		}
		
		if (strlen($this->object->getIntroduction()))
		{
			$info->addSection($this->lng->txt("tst_introduction"));
			$info->addProperty("", $this->object->prepareTextareaOutput($this->object->getIntroduction(), true).
					$info->getHiddenToggleButton());
		}
		else
		{
			$info->addSection("");
			$info->addProperty("", $info->getHiddenToggleButton());
		}

		$info->addSection($this->lng->txt("tst_general_properties"));
		if ($this->object->getShowInfo())
		{
			$info->addProperty($this->lng->txt("author"), $this->object->getAuthor());
			$info->addProperty($this->lng->txt("title"), $this->object->getTitle());
		}
		if( $this->object->isOnline() && $this->object->isComplete( $this->testQuestionSetConfigFactory->getQuestionSetConfig() ) )
		{
			if ((!$this->object->getFixedParticipants() || $online_access) && $ilAccess->checkAccess("read", "", $this->ref_id))
			{
				if ($this->object->getShowInfo() || !$this->object->getForceJS())
				{
					// use javascript
					$checked_javascript = false;
					if ($this->object->getJavaScriptOutput())
					{
						$checked_javascript = true;
					}
				}
				// hide previous results
				if(!$this->object->isRandomTest() && !$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired())
				{
					if ($this->object->getNrOfTries() != 1)
					{
						if ($this->object->getUsePreviousAnswers() == 0)
						{
							if ($this->object->getShowInfo())
							{
								$info->addProperty($this->lng->txt("tst_use_previous_answers"), $this->lng->txt("tst_dont_use_previous_answers"));
							}
						}
						else
						{
							$use_previous_answers = FALSE;
							if ($ilUser->prefs["tst_use_previous_answers"])
							{
								$checked_previous_answers = TRUE;
							}
							$info->addPropertyCheckbox($this->lng->txt("tst_use_previous_answers"), "chb_use_previous_answers", 1, $this->lng->txt("tst_use_previous_answers_user"), $checked_previous_answers);
						}
					}
				}
			}
		}

		$info->hideFurtherSections(false);
		
		if ($this->object->getShowInfo())
		{
			$info->addSection($this->lng->txt("tst_sequence_properties"));
			$info->addProperty($this->lng->txt("tst_sequence"), $this->lng->txt(($this->object->getSequenceSettings() == TEST_FIXED_SEQUENCE)? "tst_sequence_fixed":"tst_sequence_postpone"));
		
			$info->addSection($this->lng->txt("tst_heading_scoring"));
			$info->addProperty($this->lng->txt("tst_text_count_system"), $this->lng->txt(($this->object->getCountSystem() == COUNT_PARTIAL_SOLUTIONS)? "tst_count_partial_solutions":"tst_count_correct_solutions"));
			$info->addProperty($this->lng->txt("tst_score_mcmr_questions"), $this->lng->txt(($this->object->getMCScoring() == SCORE_ZERO_POINTS_WHEN_UNANSWERED)? "tst_score_mcmr_zero_points_when_unanswered":"tst_score_mcmr_use_scoring_system"));
			if ($this->object->isRandomTest())
			{
				$info->addProperty($this->lng->txt("tst_pass_scoring"), $this->lng->txt(($this->object->getPassScoring() == SCORE_BEST_PASS)? "tst_pass_best_pass":"tst_pass_last_pass"));
			}

			$info->addSection($this->lng->txt("tst_score_reporting"));
			$score_reporting_text = "";
			switch ($this->object->getScoreReporting())
			{
				case REPORT_AFTER_TEST:
					$score_reporting_text = $this->lng->txt("tst_report_after_test");
					break;
				case REPORT_ALWAYS:
					$score_reporting_text = $this->lng->txt("tst_report_after_first_question");
					break;
				case REPORT_AFTER_DATE:
					$score_reporting_text = $this->lng->txt("tst_report_after_date");
					break;
				case 4:
					$score_reporting_text = $this->lng->txt("tst_report_never");
					break;
			}
			$info->addProperty($this->lng->txt("tst_score_reporting"), $score_reporting_text); 
			$reporting_date = $this->object->getReportingDate();
			if ($reporting_date)
			{
				#preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $reporting_date, $matches);
				#$txt_reporting_date = date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]));
				#$info->addProperty($this->lng->txt("tst_score_reporting_date"), $txt_reporting_date);
				$info->addProperty($this->lng->txt('tst_score_reporting_date'),
					ilDatePresentation::formatDate(new ilDateTime($reporting_date,IL_CAL_TIMESTAMP)));
			}
	
			$info->addSection($this->lng->txt("tst_session_settings"));
			$info->addProperty($this->lng->txt("tst_nr_of_tries"), ($this->object->getNrOfTries() == 0)?$this->lng->txt("unlimited"):$this->object->getNrOfTries());
			if ($this->object->getNrOfTries() != 1)
			{
				$info->addProperty($this->lng->txt("tst_nr_of_tries_of_user"), ($testSession->getPass() == false)?$this->lng->txt("tst_no_tries"):$testSession->getPass());
			}

			if ($this->object->getEnableProcessingTime())
			{
				$info->addProperty($this->lng->txt("tst_processing_time"), $this->object->getProcessingTime());
			}
			if (strlen($this->object->getAllowedUsers()) && ($this->object->getAllowedUsersTimeGap()))
			{
				$info->addProperty($this->lng->txt("tst_allowed_users"), $this->object->getAllowedUsers());
			}
		
			$starting_time = $this->object->getStartingTime();
			if ($starting_time && $this->object->isStartingTimeEnabled())
			{
				$info->addProperty($this->lng->txt("tst_starting_time"),
					ilDatePresentation::formatDate(new ilDateTime($starting_time,IL_CAL_TIMESTAMP)));
			}
			$ending_time = $this->object->getEndingTime();
			if ($ending_time && $this->object->isEndingTimeEnabled())
			{
				$info->addProperty($this->lng->txt("tst_ending_time"),
					ilDatePresentation::formatDate(new ilDateTime($ending_time,IL_CAL_TIMESTAMP)));
			}
			$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
			// forward the command
		}
		
		$this->ctrl->forwardCommand($info);
	}

	function addLocatorItems()
	{
		global $ilLocator;
		switch ($this->ctrl->getCmd())
		{
			case "run":
			case "infoScreen":
			case "redirectToInfoScreen":
			case "start":
			case "resume":
			case "previous":
			case "next":
			case "summary":
			case "finishTest":
			case "outCorrectSolution":
			case "passDetails":
			case "showAnswersOfUser":
			case "outUserResultsOverview":
			case "backFromSummary":
			case "show_answers":
			case "setsolved":
			case "resetsolved":
			case "outTestSummary":
			case "outQuestionSummary":
			case "gotoQuestion":
			case "selectImagemapRegion":
			case "confirmSubmitAnswers":
			case "finalSubmission":
			case "postpone":
			case "outUserPassDetails":
			case "checkPassword":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
				break;
			case "eval_stat":
			case "evalAllUsers":
			case "evalUserDetail":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "eval_stat"), "", $_GET["ref_id"]);
				break;
			case "create":
			case "save":
			case "cancel":
			case "importFile":
			case "cloneAll":
			case "importVerifiedFile":
			case "cancelImport":
				break;
		default:
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $_GET["ref_id"]);
				break;
		}
	}
	
	function getBrowseForQuestionsTab(&$tabs_gui)
	{
		global $ilAccess;
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$this->ctrl->saveParameterByClass($this->ctrl->getCmdClass(), 'q_id');
			// edit page
			$tabs_gui->setBackTarget($this->lng->txt("backtocallingtest"), $this->ctrl->getLinkTargetByClass($this->ctrl->getCmdClass(), "questions"));
			$tabs_gui->addTarget("tst_browse_for_questions",
				$this->ctrl->getLinkTarget($this, "browseForQuestions"),
				array("browseForQuestions", "filter", "resetFilter", "resetTextFilter", "insertQuestions"),
				"", "", TRUE
			);
		}
	}
	
	function getRandomQuestionsTab(&$tabs_gui)
	{
		global $ilAccess;
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// edit page
			$tabs_gui->setBackTarget($this->lng->txt("backtocallingtest"), $this->ctrl->getLinkTarget($this, "questions"));
			$tabs_gui->addTarget("random_selection",
				$this->ctrl->getLinkTarget($this, "randomQuestions"),
				array("randomQuestions"),
				"", ""
			);
		}
	}

	function statisticsObject()
	{
	}

	/**
	* Shows the certificate editor
	*/
	function certificateObject()
	{
		include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
		include_once "./Modules/Test/classes/class.ilTestCertificateAdapter.php";
		$output_gui = new ilCertificateGUI(new ilTestCertificateAdapter($this->object));
		$output_gui->certificateEditor();
	}

	function getQuestionsSubTabs()
	{
		global $ilTabs, $ilCtrl;
		$ilTabs->activateTab('assQuestions');
		$a_cmd = $ilCtrl->getCmd();

		if (!$this->object->isRandomTest())
		{
                #if (in_array($this->object->getEnabledViewMode(), array('both', 'express'))) {
                    $questions_per_page = ($a_cmd == 'questions_per_page' || ($a_cmd == 'removeQuestions' && $_REQUEST['test_express_mode'])) ? true : false;

                    $this->tabs_gui->addSubTabTarget(
                            "questions_per_page_view",
                            $this->ctrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'showPage'),
                            "", "", "", $questions_per_page);
                #}
		}
		include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
		$template = new ilSettingsTemplate($this->object->getTemplate(), ilObjAssessmentFolderGUI::getSettingsTemplateConfig());

                if (!in_array('questions', $template->getHiddenTabs())) {
                    // questions subtab
                    $ilTabs->addSubTabTarget("edit_test_questions",
                             $this->ctrl->getLinkTarget($this,'questions'),
                             array("questions", "browseForQuestions", "questionBrowser", "createQuestion",
                             "randomselect", "filter", "resetFilter", "insertQuestions",
                             "back", "createRandomSelection", "cancelRandomSelect",
                             "insertRandomSelection", "removeQuestions", "moveQuestions",
                             "insertQuestionsBefore", "insertQuestionsAfter", "confirmRemoveQuestions",
                             "cancelRemoveQuestions", "executeCreateQuestion", "cancelCreateQuestion",
                             "addQuestionpool", "saveRandomQuestions", "saveQuestionSelectionMode"),
                             "");

                    if (in_array($a_cmd, array('questions', 'createQuestion')) || ($a_cmd == 'removeQuestions' && !$_REQUEST['test_express_mode']))
                            $this->tabs_gui->activateSubTab('edit_test_questions');
		}
                #}

		// print view subtab
		if (!$this->object->isRandomTest())
		{
			$ilTabs->addSubTabTarget("print_view",
				 $this->ctrl->getLinkTarget($this,'print'),
				 "print", "", "", $this->ctrl->getCmd() == 'print');
			$ilTabs->addSubTabTarget('review_view', 
				 $this->ctrl->getLinkTarget($this, 'review'), 
				 'review', '', '', $this->ctrl->getCmd() == 'review');
		}
		
			
	}
	
	function getStatisticsSubTabs()
	{
		global $ilTabs;
		
		// user results subtab
		$ilTabs->addSubTabTarget("eval_all_users",
			 $this->ctrl->getLinkTargetByClass("iltestevaluationgui", "outEvaluation"),
			 array("outEvaluation", "detailedEvaluation", "exportEvaluation", "evalUserDetail", "passDetails",
			 	"outStatisticsResultsOverview", "statisticsPassDetails")
			 , "");
	
		// aggregated results subtab
		$ilTabs->addSubTabTarget("tst_results_aggregated",
			$this->ctrl->getLinkTargetByClass("iltestevaluationgui", "eval_a"),
			array("eval_a"),
			"", "");
	
		// question export
		$ilTabs->addSubTabTarget("tst_single_results",
			$this->ctrl->getLinkTargetByClass("iltestevaluationgui", "singleResults"),
			array("singleResults"),
			"", "");
	}
	
	function getSettingsSubTabs($hiddenTabs = array())
	{
		/**
		 * @var $ilTabs ilTabsGUI
		 */
		global $ilTabs, $ilias;
		
		// general subtab
		$ilTabs->addSubTabTarget('general', $this->ctrl->getLinkTargetByClass('ilObjTestSettingsGeneralGUI'),
			 '',											// auto activation regardless from cmd
			 array('ilobjtestsettingsgeneralgui')			// auto activation for ilObjTestSettingsGeneralGUI
		);

		if(!in_array('mark_schema', $hiddenTabs))
		{
			$ilTabs->addSubTabTarget(
				'mark_schema',
				$this->ctrl->getLinkTargetByClass('ilmarkschemagui', 'showMarkSchema'),
				'',
				array('ilmarkschemagui')
			);
		}

		// scoring subtab
		$ilTabs->addSubTabTarget('scoring', $this->ctrl->getLinkTargetByClass('ilObjTestSettingsScoringResultsGUI'),
			'',                                             // auto activation regardless from cmd
			array('ilobjtestsettingsscoringresultsgui')     // auto activation for ilObjTestSettingsScoringResultsGUI
		);
	
		// certificate subtab
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		if( !in_array('certificate', $hiddenTabs) && ilCertificate::isActive())
		{				
			$ilTabs->addSubTabTarget(
				"certificate",
				$this->ctrl->getLinkTarget($this,'certificate'),
				array("certificate", "certificateEditor", "certificateRemoveBackground", "certificateSave",
					"certificatePreview", "certificateDelete", "certificateUpload", "certificateImport"),
				array("", "ilobjtestgui", "ilcertificategui")
			);
		}

                if (!in_array('defaults', $hiddenTabs)) {
                    // defaults subtab
                    $ilTabs->addSubTabTarget(
                            "tst_default_settings",
                            $this->ctrl->getLinkTarget($this, "defaults"),
                            array("defaults", "deleteDefaults", "addDefaults", "applyDefaults"),
                            array("", "ilobjtestgui", "ilcertificategui")
                    );
                }
	}

	function getParticipantsSubTabs()
	{
		global $ilTabs;

		// participants subtab
		$ilTabs->addSubTabTarget( "participants",
			$this->ctrl->getLinkTarget($this,'participants'),
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
		
		if( !$this->testQuestionSetConfigFactory->getQuestionSetConfig()->areDepenciesBroken() )
		{
			if($this->object->getProcessingTimeInSeconds() > 0 && $this->object->getNrOfTries() == 1)
			{
				// extratime subtab
				$ilTabs->addSubTabTarget( "timing",
					$this->ctrl->getLinkTarget($this,'timingOverview'),
					array("timing", "timingOverview"), "", ""
				);
			}
		}
	}
	
	/**
	* adds tabs to tab gui object
	*
	* @param ilTabsGUI $tabs_gui
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilAccess, $ilUser, $ilHelp;

		if (preg_match('/^ass(.*?)gui$/i', $this->ctrl->getNextClass($this))) {
			return;
		}
		else if ($this->ctrl->getNextClass($this) == 'ilassquestionpagegui') {
			return;
		}
		
		$ilHelp->setScreenIdComponent("tst");
                
		$hidden_tabs = array();
		
		$template = $this->object->getTemplate();
		if($template)
		{
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template = new ilSettingsTemplate($template, ilObjAssessmentFolderGUI::getSettingsTemplateConfig());

			$hidden_tabs = $template->getHiddenTabs();
		}
		
		// for local use in this fucking sledge hammer method
		$curUserHasWriteAccess = $ilAccess->checkAccess("write", "", $this->ref_id);

		switch($this->ctrl->getCmdClass())
		{
			// no tabs .. no subtabs .. during test pass
			case 'iltestoutputgui':

			// tab handling happens within GUIs
			case 'iltestevaluationgui':
				$nonSelfTabbingCommands = array(
					'outParticipantsResultsOverview', 'outEvaluation',
					'eval_a', 'singleResults', 'detailedEvaluation'
				);
				if( in_array($this->ctrl->getCmd(), $nonSelfTabbingCommands) )
				{
					break;
				}
			case 'iltestevalobjectiveorientedgui':
				return;
				
			case 'ilmarkschemagui':
			case 'ilobjtestsettingsgeneralgui':
			case 'ilobjtestsettingsscoringresultsgui':
				
				if( $curUserHasWriteAccess )
				{
					$this->getSettingsSubTabs($hidden_tabs);
				}
				
				break;
		}

		if( $this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired() )
		{
			require_once 'Services/Link/classes/class.ilLink.php';
			$courseLink = ilLink::_getLink($this->getObjectiveOrientedContainer()->getRefId());
			$tabs_gui->setBack2Target($this->lng->txt('back_to_objective_container'), $courseLink);
		}

		switch($this->ctrl->getCmd())
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
				if($this->ctrl->getNextClass($this) != "illearningprogressgui")
				{
					return $this->getBrowseForQuestionsTab($tabs_gui);
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
				if( $curUserHasWriteAccess && in_array($this->ctrl->getCmdClass(), array('ilobjtestgui', 'ilcertificategui')) )
				{
					$this->getSettingsSubTabs($hidden_tabs);
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

		if (strcmp(strtolower(get_class($this->object)), "ilobjtest") == 0)
		{
			// questions tab
			if ($ilAccess->checkAccess("write", "", $this->ref_id) && !in_array('assQuestions', $hidden_tabs))
			{
				$force_active = ($_GET["up"] != "" || $_GET["down"] != "")
					? true
					: false;
				if (!$force_active)
				{
					if ($_GET["browse"] == 1) $force_active = true;
				}

				switch( $this->object->getQuestionSetType() )
				{
					case ilObjTest::QUESTION_SET_TYPE_FIXED:
						$target = $this->ctrl->getLinkTargetByClass('iltestexpresspageobjectgui','showPage');
						break;
					
					case ilObjTest::QUESTION_SET_TYPE_RANDOM:
						$target = $this->ctrl->getLinkTargetByClass('ilTestRandomQuestionSetConfigGUI');
						break;
						
					case ilObjTest::QUESTION_SET_TYPE_DYNAMIC:
						$target = $this->ctrl->getLinkTargetByClass('ilObjTestDynamicQuestionSetConfigGUI');
						break;
				}

				$tabs_gui->addTarget("assQuestions",
					 //$this->ctrl->getLinkTarget($this,'questions'),
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
			if ($ilAccess->checkAccess("read", "", $this->ref_id) && !in_array('info_short', $hidden_tabs))
			{
				$tabs_gui->addTarget("info_short",
					 $this->ctrl->getLinkTarget($this,'infoScreen'),
					 array("infoScreen", "outIntroductionPage", "showSummary", 
					 "setAnonymousId", "outUserListOfAnswerPasses", "redirectToInfoScreen"));
			}
			
			// settings tab
			if ($ilAccess->checkAccess("write", "", $this->ref_id))
			{
				if (!in_array('settings', $hidden_tabs))
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
					
					$tabs_gui->addTarget("settings",
						$this->ctrl->getLinkTargetByClass('ilObjTestSettingsGeneralGUI'),
						$settingsCommands,
						array("ilmarkschemagui", "ilobjtestsettingsgeneralgui", "ilobjtestsettingsscoringresultsgui", "ilobjtestgui", "ilcertificategui")
					);
				}

				// skill service
				if( $this->object->isSkillServiceEnabled() && ilObjTest::isSkillManagementGloballyActivated() )
				{
					require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentsGUI.php';

					$link = $this->ctrl->getLinkTargetByClass(
						array('ilTestSkillAdministrationGUI', 'ilAssQuestionSkillAssignmentsGUI'),
						ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
					);

					$tabs_gui->addTarget('tst_tab_competences', $link, array(), array());
				}

				if (!in_array('participants', $hidden_tabs))
				{
					// participants
					$tabs_gui->addTarget("participants",
						$this->ctrl->getLinkTarget($this,'participants'),
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

			include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
			if(ilLearningProgressAccess::checkAccess($this->object->getRefId()) && !in_array('learning_progress', $hidden_tabs))
			{
				$tabs_gui->addTarget('learning_progress',
									 $this->ctrl->getLinkTargetByClass(array('illearningprogressgui'),''),
									 '',
									 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
			}

			if ($ilAccess->checkAccess("write", "", $this->ref_id)  && !in_array('manscoring', $hidden_tabs))
			{
				include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
				$scoring = ilObjAssessmentFolder::_getManualScoring();
				if (count($scoring))
				{
					// scoring tab
					$tabs_gui->addTarget(
							"manscoring", $this->ctrl->getLinkTargetByClass('ilTestScoringGUI', 'showManScoringParticipantsTable'),
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
			if ($ilAccess->checkAccess("write", "", $this->ref_id) && $scoring_adjust_active && !in_array('scoringadjust', $hidden_tabs))
			{
				// scoring tab
				$tabs_gui->addTarget(
					"scoringadjust", $this->ctrl->getLinkTargetByClass('ilScoringAdjustmentGUI', 'showquestionlist'),
					array(
						'showquestionlist',
						'savescoringfortest',
						'adjustscoringfortest'
					), ''
				);
			}

			if ((($ilAccess->checkAccess("tst_statistics", "", $this->ref_id)) || ($ilAccess->checkAccess("write", "", $this->ref_id)))  && !in_array('statistics', $hidden_tabs))
			{
				// statistics tab
				$tabs_gui->addTarget(
					"statistics",
					$this->ctrl->getLinkTargetByClass("iltestevaluationgui", "outEvaluation"),
					array(
						"statistics", "outEvaluation", "exportEvaluation", "detailedEvaluation", "eval_a", "evalUserDetail",
						"passDetails", "outStatisticsResultsOverview", "statisticsPassDetails", "singleResults"
					),
					""
				);
			}

			if ($ilAccess->checkAccess("write", "", $this->ref_id))
			{
                             if (!in_array('history', $hidden_tabs)) {

				// history
				$tabs_gui->addTarget("history",
					 $this->ctrl->getLinkTarget($this,'history'),
					 "history", "");
                             }

                if (!in_array('meta_data', $hidden_tabs)) {
					// meta data
					include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
					$mdgui = new ilObjectMetaDataGUI($this->object);					
					$mdtab = $mdgui->getTab();
					if($mdtab)
					{
						$tabs_gui->addTarget("meta_data",
							 $mdtab,
							 "", "ilmdeditorgui");
					}
                }

				if(!in_array('export', $hidden_tabs))
				{
					// export tab
					$tabs_gui->addTarget(
						"export",
						 $this->ctrl->getLinkTargetByClass('iltestexportgui' ,''),
						 '',
						 array('iltestexportgui')
					);
				}
			}
			
			if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id)&& !in_array('permissions', $hidden_tabs))
			{
				$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
			}
		}
		
		if( $this->testQuestionSetConfigFactory->getQuestionSetConfig()->areDepenciesBroken() )
		{
			$hideTabs = $this->testQuestionSetConfigFactory->getQuestionSetConfig()->getHiddenTabsOnBrokenDepencies();
			
			foreach($hideTabs as $tabId)
			{
				$tabs_gui->removeTab($tabId);
			}
		}
	}
	
	/**
	* Redirect script to call a test with the test reference id
	* 
	* Redirect script to call a test with the test reference id
	*
	* @param integer $a_target The reference id of the test
	* @access	public
	*/
	public static function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			//include_once "./Services/Utilities/classes/class.ilUtil.php";
			$_GET["baseClass"] = "ilObjTestGUI";
			$_GET["cmd"] = "infoScreen";
			$_GET["ref_id"] = $a_target;
			include_once("ilias.php");
			exit;
			//ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=infoScreen&ref_id=$a_target");
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}

	/**
	 * Questions per page
	 *
	 * @param
	 * @return
	 */
	function buildPageViewToolbar($qid = 0)
	{
		if($this->create_question_mode)
			return;

		global $ilToolbar, $ilCtrl, $lng;
		
		require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';

		$this->getQuestionsSubTabs();

		$ilCtrl->saveParameter($this, 'q_mode');

		$ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'test_express_mode', 1);
		$ilCtrl->setParameter($this, 'test_express_mode', 1);
		$ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'q_id', $_REQUEST['q_id']);
		$ilCtrl->setParameter($this, 'q_id', $_REQUEST['q_id']);
		$ilToolbar->setFormAction($ilCtrl->getFormActionByClass('iltestexpresspageobjectgui', 'edit'));

		if($this->object->evalTotalPersons() == 0)
		{
			/*
			include_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';
			$pool = new ilObjQuestionPool();
			$questionTypes = $pool->getQuestionTypes();$options = array();
			foreach($questionTypes as $label => $data) {
			$options[$data['question_type_id']] = $label;
			}

					include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
					$si = new ilSelectInputGUI($lng->txt("test_add_new_question"), "qtype");
					$si->setOptions($options);
					$ilToolbar->addInputItem($si, true);
			/*
					// use pool
					if ($this->object->isExpressModeQuestionPoolAllowed()) {
						include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
						$cb = new ilCheckboxInputGUI($lng->txt("test_use_pool"), "use_pool");
						$ilToolbar->addInputItem($cb, true);
					}
			*/
			$ilToolbar->addFormButton($lng->txt("ass_create_question"), "addQuestion");

			$ilToolbar->addSeparator();

			if($this->object->getPoolUsage())
			{
				$ilToolbar->addFormButton($lng->txt("tst_browse_for_questions"), "browseForQuestions");

				$show_separator = true;
			}
		}

		$questions = $this->object->getQuestionTitlesAndIndexes();

		// desc
		$options = array();
		foreach($questions as $id => $label)
		{
			$options[$id] = $label . ' ['. $this->lng->txt('question_id_short') . ': ' . $id . ']';
		}

		$optionKeys = array_keys($options);

		if(!$options)
		{
			$options[] = $lng->txt('none');
		}
		//else if (count($options) > 1) {
//                    $addSeparator = false;
//                    if ($optionKeys[0] != $qid) {
//                        //$ilToolbar->addFormButton($lng->txt("test_prev_question"), "prevQuestion");
//                        $ilToolbar->addLink($lng->txt("test_prev_question"), $ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'prevQuestion'));
//                        $addSeparator = true;
//                    }
//		    else {
//			$ilToolbar->addSpacer(45);
//		    }
//
//                    if ($optionKeys[count($optionKeys)-1] != $qid) {
//                        //$ilToolbar->addFormButton($lng->txt("test_next_question"), "nextQuestion");
//                        $ilToolbar->addLink($lng->txt("test_next_question"), $ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'nextQuestion'));
//			$addSeparator = true;
//                    }
//		    else {
//			$ilToolbar->addSpacer(45);
//		    }
//
//                    //if ($addSeparator) {
//                        $ilToolbar->addSeparator();
//                    //}

		if(count($questions))
		{
			if(isset($show_separator) && $show_separator)
			{
				$ilToolbar->addSeparator();
			}

			$btn = ilLinkButton::getInstance();
			$btn->setCaption("test_prev_question");
			$btn->setUrl($ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'prevQuestion'));
			$ilToolbar->addButtonInstance($btn);
			
			if( count($options) <= 1 || $optionKeys[0] == $qid )
			{
				$btn->setDisabled(true);
			}

			$btn = ilLinkButton::getInstance();
			$btn->setCaption("test_next_question");
			$btn->setUrl($ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'nextQuestion'));
			$ilToolbar->addButtonInstance($btn);

			if( count($options) <= 1 || $optionKeys[count($optionKeys) - 1] == $qid )
			{
				$btn->setDisabled(true);
			}
		}

		if(count($questions) > 1)
		{

			$ilToolbar->addSeparator();

			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$si = new ilSelectInputGUI($lng->txt("test_jump_to"), "q_id");
			$si->addCustomAttribute("onChange=\"forms['ilToolbar'].submit();\"");
			$si->setOptions($options);

			if($qid)
			{
				$si->setValue($qid);
			}

			$ilToolbar->addInputItem($si, true);
		}

		$total = $this->object->evalTotalPersons();

		/*if (count($options)) {
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$si = new ilSelectInputGUI($lng->txt("test_jump_to"), "q_id");
			$si->addCustomAttribute("onChange=\"forms['ilToolbar'].submit();\"");
			$si->setOptions($options);

			if ($qid) {
				$si->setValue($qid);
			}

			$ilToolbar->addInputItem($si, true);
		}*/

		if(count($questions) && !$total)
		{
			$ilCtrl->setParameter($this, 'q_id', $_REQUEST['q_id']);
			$ilToolbar->addSeparator();
			$ilToolbar->addButton($lng->txt("test_delete_page"), $ilCtrl->getLinkTarget($this, "removeQuestions"));
		}

		if(count($questions) > 1 && !$total)
		{
			$ilToolbar->addSeparator();
			$ilToolbar->addButton($lng->txt("test_move_page"), $ilCtrl->getLinkTarget($this, "movePageForm"));
		}

		global $ilAccess, $ilUser;
		
		$online_access = false;
		if($this->object->getFixedParticipants())
		{
			include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
			$online_access_result = ilObjTestAccess::_lookupOnlineTestAccess($this->object->getId(), $ilUser->getId());
			if($online_access_result === true)
			{
				$online_access = true;
			}
		}

		if($this->object->isOnline() && $this->object->isComplete( $this->testQuestionSetConfigFactory->getQuestionSetConfig() ))
		{
			if((!$this->object->getFixedParticipants() || $online_access) && $ilAccess->checkAccess("read", "", $this->ref_id))
			{
				$testSession = $this->testSessionFactory->getSession();

				$executable = $this->object->isExecutable($testSession, $ilUser->getId(), $allowPassIncrease = TRUE);
				
				if($executable["executable"])
				{
					$player_factory = new ilTestPlayerFactory($this->object);
					$player_instance = $player_factory->getPlayerGUI();

					if ($testSession->getActiveId() > 0)
					{
						$ilToolbar->addSeparator();
						$ilToolbar->addButton($lng->txt('tst_resume_test'), $ilCtrl->getLinkTarget($player_instance, 'resumePlayer'));
					}
					else
					{
						$ilToolbar->addSeparator();
						$ilToolbar->addButton($lng->txt('tst_start_test'), $ilCtrl->getLinkTarget($player_instance, 'startTest'));
					}
				}
			}
		}
	}

	public function copyQuestionsToPoolObject()
	{
		$this->copyQuestionsToPool($_REQUEST['q_id'], $_REQUEST['sel_qpl']);
		$this->ctrl->redirect($this, 'questions');
	}

	public function copyQuestionsToPool($questionIds, $qplId)
	{
		$newIds = array();
		foreach($questionIds as $q_id)
		{
			$newId = $this->copyQuestionToPool($q_id, $qplId);
			$newIds[$q_id] = $newId;
		}

		$result = new stdClass();
		$result->ids = $newIds;
		$result->qpoolid = $qplId;

		return $result;
	}

	public function copyQuestionToPool($sourceQuestionId, $targetParentId)
	{
		require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
		$question_gui = assQuestion::instantiateQuestionGUI($sourceQuestionId);

		$newtitle = $question_gui->object->getTitle();
		if ($question_gui->object->questionTitleExists($targetParentId, $question_gui->object->getTitle()))
		{
			$counter = 2;
			while ($question_gui->object->questionTitleExists($targetParentId, $question_gui->object->getTitle() . " ($counter)"))
			{
				$counter++;
			}
			$newtitle = $question_gui->object->getTitle() . " ($counter)";
		}

		return $question_gui->object->createNewOriginalFromThisDuplicate($targetParentId, $newtitle);
	}

	/**
	 * @global ilObjectDataCache $ilObjDataCache
	 */
	public function copyAndLinkQuestionsToPoolObject()
	{
		global $ilObjDataCache;

		$qplId = $ilObjDataCache->lookupObjId($_REQUEST['sel_qpl']);
		$result = $this->copyQuestionsToPool($_REQUEST['q_id'], $qplId);

		foreach($result->ids as $oldId => $newId)
		{
			$questionInstance = assQuestion::_instanciateQuestion($oldId);

			if( assQuestion::originalQuestionExists($questionInstance->getOriginalId()) )
			{
				$oldOriginal = assQuestion::_instanciateQuestion($questionInstance->getOriginalId());
				$oldOriginal->delete($oldOriginal->getId());
			}

			$questionInstance->setNewOriginalId($newId);
		}

		$this->ctrl->redirect($this, 'questions');
	}

	private function getQuestionpoolCreationForm()
	{
		global $lng;
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();

		$title = new ilTextInputGUI($lng->txt('title'), 'title');
		$title->setRequired(true);
		$form->addItem($title);

		$description = new ilTextAreaInputGUI($lng->txt('description'), 'description');
		$form->addItem($description);

		$form->addCommandButton('createQuestionPoolAndCopy', $lng->txt('create'));

		if(isset($_REQUEST['q_id']) && is_array($_REQUEST['q_id']))
		{
			foreach($_REQUEST['q_id'] as $id)
			{
				$hidden = new ilHiddenInputGUI('q_id[]');
				$hidden->setValue($id);
				$form->addItem($hidden);
			}
		}

		return $form;
	}

	public function copyToQuestionpoolObject()
	{
		$this->createQuestionpoolTargetObject('copyQuestionsToPool');
	}

	public function copyAndLinkToQuestionpoolObject()
	{
		global $lng;

		require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
		
		// #13761; All methods use for this request should be revised, thx japo ;-) 
		if(
			'copyAndLinkToQuestionpool' == $this->ctrl->getCmd() &&
			(!isset($_REQUEST['q_id']) || !is_array($_REQUEST['q_id']))
		)
		{
			ilUtil::sendFailure($this->lng->txt('tst_no_question_selected_for_moving_to_qpl'), true);
			$this->ctrl->redirect($this, 'questions');
		}

		if(isset($_REQUEST['q_id']) && is_array($_REQUEST['q_id']))
		{
			foreach($_REQUEST['q_id'] as $q_id)
			{
				if(!assQuestion::originalQuestionExists($q_id))
				{
					continue;
				}

				$type = ilObject::_lookupType(assQuestion::lookupParentObjId(assQuestion::_getOriginalId($q_id)));

				if($type !== 'tst')
				{
					ilUtil::sendFailure($lng->txt('tst_link_only_unassigned'), true);
					$this->ctrl->redirect($this, 'questions');
					return;
				}
			}
		}

		$this->createQuestionpoolTargetObject('copyAndLinkQuestionsToPool');
	}

	public function createQuestionPoolAndCopyObject()
	{
		$form = $this->getQuestionpoolCreationForm();

	    if ($_REQUEST['title'])
		{
		    $title = $_REQUEST['title'];
	    }
	    else
		{
		    $title = $_REQUEST['txt_qpl'];
	    }
	    
	    if (!$title)
		{
		    ilUtil::sendInfo($this->lng->txt("questionpool_not_entered"));
		    return $this->copyAndLinkToQuestionpoolObject();
	    }
	    
		$ref_id = $this->createQuestionPool($title, $_REQUEST['description']);
		$_REQUEST['sel_qpl'] = $ref_id;

		//if ($_REQUEST['link'])
		//{
			$this->copyAndLinkQuestionsToPoolObject();
		//}
		//else
		//{
		//    $this->copyQuestionsToPoolObject();
		//}
    }

	/**
	* Called when a new question should be created from a test
	* Important: $cmd may be overwritten if no question pool is available
	*
	* @access	public
	*/
	function createQuestionpoolTargetObject($cmd)
	{
		global $ilUser, $ilTabs;
		$this->getQuestionsSubTabs();
		$ilTabs->activateSubTab('edit_test_questions');

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_qpl_select_copy.html", "Modules/Test");
		$questionpools =& $this->object->getAvailableQuestionpools(FALSE, FALSE, FALSE, TRUE, FALSE, "write");
		if(count($questionpools) == 0)
		{
			$this->tpl->setCurrentBlock("option");
			$this->tpl->setVariable("VALUE_QPL", "");
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			foreach($questionpools as $key => $value)
			{
				$this->tpl->setCurrentBlock("option");
				$this->tpl->setVariable("VALUE_OPTION", $key);
				$this->tpl->setVariable("TEXT_OPTION", $value["title"]);
				$this->tpl->parseCurrentBlock();
			}
		}

		if(isset($_REQUEST['q_id']) && is_array($_REQUEST['q_id']))
		{
			foreach($_REQUEST['q_id'] as $id)
			{
				$this->tpl->setCurrentBlock("hidden");
				$this->tpl->setVariable("HIDDEN_NAME", "q_id[]");
				$this->tpl->setVariable("HIDDEN_VALUE", $id);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("adm_content");
			}
		}
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));

		if(count($questionpools) == 0)
		{
			$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_enter_questionpool"));
			$cmd = 'createQuestionPoolAndCopy';
		}
		else
		{
			$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_select_questionpool"));
		}

		$this->tpl->setVariable("CMD_SUBMIT", $cmd);
		$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("submit"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));

		$createForm = $this->getQuestionpoolCreationForm();
		switch($cmd)
		{
			case 'copyAndLinkQuestionsToPool':
				$hidden = new ilHiddenInputGUI('link');
				$hidden->setValue(1);
				$createForm->addItem($hidden);
				break;
			case 'copyQuestionsToPool':
				break;
		}
		$createForm->setFormAction($this->ctrl->getFormAction($this));

		$this->tpl->parseCurrentBlock();
	}

	// begin-patch lok
	public  function applyTemplate($templateData, $object)
	// end-patch lok
	{
		// map formFieldName => setterName
		$simpleSetters = array(

			// general properties
			'use_pool' => 'setPoolUsage',
			'question_set_type' => 'setQuestionSetType',

			// test intro properties
			'intro_enabled' => 'setIntroductionEnabled',
			'showinfo' => 'setShowInfo',

			// test access properties
			'chb_starting_time' => 'setStartingTimeEnabled',
			'chb_ending_time' => 'setEndingTimeEnabled',
			'password_enabled' => 'setPasswordEnabled',
			'fixedparticipants' => 'setFixedParticipants',
			'limitUsers' => 'setLimitUsersEnabled',

			// test run properties
			'nr_of_tries' => 'setNrOfTries',
			'chb_processing_time' => 'setEnableProcessingTime',
			'kiosk' => 'setKiosk',
			'examid_in_test_pass' => 'setShowExamIdInTestPassEnabled',

			// question behavior properties
			'title_output' => 'setTitleOutput',
			'autosave' => null, // handled specially in loop below
			'chb_shuffle_questions' => 'setShuffleQuestions',
			'offer_hints' => 'setOfferingQuestionHintsEnabled',
			'instant_feedback' => 'setScoringFeedbackOptionsByArray',
			'obligations_enabled' => 'setObligationsEnabled',

			// test sequence properties
			'chb_use_previous_answers' => 'setUsePreviousAnswers',
			'chb_show_cancel' => 'setShowCancel',
			'chb_postpone' => 'setPostponingEnabled',
			'list_of_questions' => 'setListOfQuestionsSettings',
			'chb_show_marker' => 'setShowMarker',

			// test finish properties
			'enable_examview' => 'setEnableExamview',
			'showfinalstatement' => 'setShowFinalStatement',
			'redirection_enabled' => null, // handled specially in loop below
			'sign_submission' => 'setSignSubmission',
			'mailnotification' => 'setMailNotification',
			
			// scoring options properties
			'count_system' => 'setCountSystem',
			'mc_scoring' => 'setMCScoring',
			'score_cutting' => 'setScoreCutting',
			'pass_scoring' => 'setPassScoring',
			'pass_deletion_allowed' => 'setPassDeletionAllowed',
			
			// result summary properties
			'results_access_enabled' => 'setScoreReporting',
			'grading_status' => 'setShowGradingStatusEnabled',
			'grading_mark' => 'setShowGradingMarkEnabled',
			
			// result details properties
			'solution_details' => 'setShowSolutionDetails',
			'solution_feedback' => 'setShowSolutionFeedback',
			'solution_suggested' => 'setShowSolutionSuggested',
			'solution_printview' => 'setShowSolutionPrintview',
			'highscore_enabled' => 'setHighscoreEnabled',
			'solution_signature' => 'setShowSolutionSignature',
			'examid_in_test_res' => 'setShowExamIdInTestResultsEnabled',
			'exp_sc_short' => 'setExportSettingsSingleChoiceShort',
			
			// misc scoring & result properties
			'anonymity' => 'setAnonymity',
			'enable_archiving' => 'setEnableArchiving'
		);

	    if (!$templateData['results_presentation']['value'])
		{
			$templateData['results_presentation']['value'] = array();
	    }

		foreach($simpleSetters as $field => $setter)
		{
			if($templateData[$field] && strlen($setter))
			{
				$object->$setter($templateData[$field]['value']);
				continue;
			}

			switch($field)
			{
				case 'autosave':
					if( $templateData[$field]['value'] > 0 )
					{
						$object->setAutosave(true);
						$object->setAutosaveIval($templateData[$field]['value'] * 1000);
					}
					else
					{
						$object->setAutosave(false);
					}
					break;

				case 'redirection_enabled':
					/* if( $templateData[$field]['value'] > REDIRECT_NONE )
					{
						$object->setRedirectionMode($templateData[$field]['value']);
					}
					else
					{
						$object->setRedirectionMode(REDIRECT_NONE);
					} */
					if( strlen($templateData[$field]['value']) )
					{
						$object->setRedirectionMode(REDIRECT_ALWAYS);
						$object->setRedirectionUrl($templateData[$field]['value']);
					}
					else
					{
						$object->setRedirectionMode(REDIRECT_NONE);
						$object->setRedirectionUrl('');
					}
			}
		}
	}

	public function saveOrderAndObligationsObject()
	{
	    global $ilAccess;
	    if (!$ilAccess->checkAccess("write", "", $this->ref_id))
	    {
		    // allow only write access
		    ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
		    $this->ctrl->redirect($this, "infoScreen");
	    }

	    global $ilCtrl;
		
		$orders = $obligations = array();
		
		foreach($_REQUEST['order'] as $qId => $order)
		{
			$id = (int)str_replace('q_', '', $qId);

			$orders[$id] = $order;
		}
		
		if( $this->object->areObligationsEnabled() && isset($_REQUEST['obligatory']) && is_array($_REQUEST['obligatory']) )
		{
			foreach($_REQUEST['obligatory'] as $qId => $obligation)
			{
				$id = (int)str_replace('q_', '', $qId);

				if( ilObjTest::isQuestionObligationPossible($id) )
				{
					$obligations[$id] = $obligation;
				}
			}
		}
		
	    $this->object->setQuestionOrderAndObligations(
			$orders, $obligations
		);

	    $ilCtrl->redirect($this, 'questions');
	}

	/**
	 * Move current page
	 */
	protected function movePageFormObject()
	{
		global $lng, $ilCtrl, $tpl;

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "movePage"));
		$form->setTitle($lng->txt("test_move_page"));

		$old_pos = new ilHiddenInputGUI("q_id");
		$old_pos->setValue($_REQUEST['q_id']);
		$form->addItem($old_pos);

		$questions = $this->object->getQuestionTitlesAndIndexes();
		if (!is_array($questions))
		    $questions = array();

		foreach($questions as $k => $q) {
		    if ($k == $_REQUEST['q_id']) {
			unset($questions[$k]);
			continue;
		    }
		    $questions[$k] = $lng->txt('behind') . ' '. $q;
		}
		#$questions['0'] = $lng->txt('first');

		$options = array(
		    0 => $lng->txt('first')
		);
		foreach($questions as $k => $q) {
		    $options[$k] = $q . ' ['. $this->lng->txt('question_id_short') . ': ' . $k  . ']';
		}

		$pos = new ilSelectInputGUI($lng->txt("position"), "position_after");
		$pos->setOptions($options);
		$form->addItem($pos);

		$form->addCommandButton("movePage", $lng->txt("submit"));
		$form->addCommandButton("showPage", $lng->txt("cancel"));

		return $tpl->setContent($form->getHTML());
	}

	public function movePageObject() {
	    global $ilAccess;
	    if (!$ilAccess->checkAccess("write", "", $this->ref_id))
	    {
		    // allow only write access
		    ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
		    $this->ctrl->redirect($this, "infoScreen");
	    }
	    
	    $this->object->moveQuestionAfter($_REQUEST['q_id'], $_REQUEST['position_after']);
	    $this->showPageObject();
	}

	public function showPageObject() {
	    global $ilCtrl;

	    $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'q_id', $_REQUEST['q_id']);
	    $ilCtrl->redirectByClass('iltestexpresspageobjectgui', 'showPage');
	}

	public function copyQuestionObject() {
	    global $ilAccess;
	    if (!$ilAccess->checkAccess("write", "", $this->ref_id))
	    {
		    // allow only write access
		    ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
		    $this->ctrl->redirect($this, "infoScreen");
	    }

	    if ($_REQUEST['q_id'] && !is_array($_REQUEST['q_id']))
		$ids = array($_REQUEST['q_id']);
	    else if ($_REQUEST['q_id'])
		$ids = $_REQUEST['q_id'];
	    else
	    {
		ilUtil::sendFailure( $this->lng->txt('copy_no_questions_selected'), true );
		$this->ctrl->redirect($this, 'questions');
	    }

	    $copy_count = 0;

	    $questionTitles = $this->object->getQuestionTitles();

	    foreach($ids as $id)
	    {
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$question = assQuestion::_instanciateQuestionGUI($id);
		if ($question)
		{
		    $title = $question->object->getTitle();
		    $i = 2;
		    while(  in_array( $title . ' (' . $i . ')', $questionTitles ))
			    $i++;

		    $title .= ' (' . $i . ')';

		    $questionTitles[] = $title;

		    $new_id = $question->object->duplicate(false, $title);

		    $clone = assQuestion::_instanciateQuestionGUI($new_id);
		    $clone->object->setObjId($this->object->getId());
		    $clone->object->saveToDb();

		    $this->object->insertQuestion( $this->testQuestionSetConfigFactory->getQuestionSetConfig(), $new_id, true );

		    $copy_count++;
		}
	    }

	    ilUtil::sendSuccess($this->lng->txt('copy_questions_success'), true);

	    $this->ctrl->redirect($this, 'questions');
	}

	/**
	 * @param $testSession
	 * @param $testSequence
	 * @return bool
	 */
	private function isDeleteDynamicTestResultsButtonRequired($testSession, $testSequence)
	{
		if( !$testSession->getActiveId() )
		{
			return false;
		}
		
		if( !$this->object->isDynamicTest() )
		{
			return false;
		}
		
		if( !$this->object->isPassDeletionAllowed() )
		{
			return false;
		}
		
		if( !$testSequence->hasStarted($testSession) )
		{
			return false;
		}
		
		return true;
	}

	/**
	 * @param $testSession
	 * @param $big_button
	 */
	private function populateDeleteDynamicTestResultsButton($testSession, &$big_button)
	{
		require_once 'Modules/Test/classes/confirmations/class.ilTestPassDeletionConfirmationGUI.php';
		require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';

		$this->ctrl->setParameterByClass(
			'iltestevaluationgui', 'context',
			ilTestPassDeletionConfirmationGUI::CONTEXT_INFO_SCREEN
		);
		
		$this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $testSession->getActiveId());
		$this->ctrl->setParameterByClass('iltestevaluationgui', 'pass', $testSession->getPass());

		$btn = ilLinkButton::getInstance();
		$btn->setCaption('tst_delete_dyn_test_results_btn');
		$btn->setUrl($this->ctrl->getLinkTargetByClass('iltestevaluationgui',  'confirmDeletePass'));
		$btn->setPrimary(false);
		$big_button[] = $btn;
	}

	/**
	 * @return bool
	 */
	private function isPdfDeliveryRequest()
	{
		if( !isset($_GET['pdf']) )
		{
			return false;
		}

		if( !(bool)$_GET['pdf'] )
		{
			return false;
		}

		return true;
	}
	
	protected function determineObjectiveOrientedContainer()
	{
		require_once 'Modules/Course/classes/Objectives/class.ilLOSettings.php';
		$containerObjId = (int)ilLOSettings::isObjectiveTest($this->ref_id);

		$containerRefId = current(ilObject::_getAllReferences($containerObjId));

		$this->objectiveOrientedContainer->setObjId($containerObjId);
		$this->objectiveOrientedContainer->setRefId($containerRefId);
	}
	
	protected function getObjectiveOrientedContainer()
	{
		return $this->objectiveOrientedContainer;
	}

	private function areSkillLevelThresholdsMissing()
	{
		if( !$this->object->isSkillServiceEnabled() )
		{
			return false;
		}
		
		if( $this->object->isDynamicTest() )
		{
			$questionSetConfig = $this->testQuestionSetConfigFactory->getQuestionSetConfig();
			$questionContainerId = $questionSetConfig->getSourceQuestionPoolId();
		}
		else
		{
			$questionContainerId = $this->object->getId();
		}
		
		global $ilDB;
		
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
		require_once 'Modules/Test/classes/class.ilTestSkillLevelThreshold.php';
		
		$assignmentList = new ilAssQuestionSkillAssignmentList($ilDB);
		$assignmentList->setParentObjId($questionContainerId);
		$assignmentList->loadFromDb();

		foreach($assignmentList->getUniqueAssignedSkills() as $data)
		{
			foreach($data['skill']->getLevelData() as $level)
			{
				$treshold = new ilTestSkillLevelThreshold($ilDB);
				$treshold->setTestId($this->object->getTestId());
				$treshold->setSkillBaseId($data['skill_base_id']);
				$treshold->setSkillTrefId($data['skill_tref_id']);
				$treshold->setSkillLevelId($level['id']);
				
				if( !$treshold->dbRecordExists() )
				{
					return true;
				}
			}
		}
		
		return false;
	}
	
	private function getSkillLevelThresholdsMissingInfo()
	{
		require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdsGUI.php';
		
		$link = $this->ctrl->getLinkTargetByClass(
			array('ilTestSkillAdministrationGUI', 'ilTestSkillLevelThresholdsGUI'),
			ilTestSkillLevelThresholdsGUI::CMD_SHOW_SKILL_THRESHOLDS
		);
		
		$msg = $this->lng->txt('tst_skl_level_thresholds_missing');
		$msg .= '<br /><a href="'.$link.'">'.$this->lng->txt('tst_skl_level_thresholds_link').'</a>';
		
		return $msg;
	}
}
