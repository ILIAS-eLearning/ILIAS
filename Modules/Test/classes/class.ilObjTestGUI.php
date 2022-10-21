<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Refinery\ConstraintViolationException;

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

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
 * @ilCtrl_Calls ilObjTestGUI: ilTestExpresspageObjectGUI, ilAssQuestionPageGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestDashboardGUI, ilTestResultsGUI
 * @ilCtrl_Calls ilObjTestGUI: ilLearningProgressGUI, ilMarkSchemaGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestEvaluationGUI, ilParticipantsTestResultsGUI
 * @ilCtrl_Calls ilObjTestGUI: ilAssGenFeedbackPageGUI, ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilObjTestGUI: ilInfoScreenGUI, ilObjectCopyGUI, ilTestScoringGUI
 * @ilCtrl_Calls ilObjTestGUI: ilRepositorySearchGUI, ilTestExportGUI
 * @ilCtrl_Calls ilObjTestGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: assOrderingQuestionGUI, assImagemapQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: assNumericGUI, assErrorTextGUI, ilTestScoringByQuestionsGUI
 * @ilCtrl_Calls ilObjTestGUI: assTextSubsetGUI, assOrderingHorizontalGUI
 * @ilCtrl_Calls ilObjTestGUI: assSingleChoiceGUI, assFileUploadGUI, assTextQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: assKprimChoiceGUI, assLongMenuGUI
 * @ilCtrl_Calls ilObjTestGUI: ilObjQuestionPoolGUI, ilEditClipboardGUI
 * @ilCtrl_Calls ilObjTestGUI: ilObjTestSettingsGeneralGUI, ilObjTestSettingsScoringResultsGUI
 * @ilCtrl_Calls ilObjTestGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestFixedQuestionSetConfigGUI, ilTestRandomQuestionSetConfigGUI, ilObjTestDynamicQuestionSetConfigGUI
 * @ilCtrl_Calls ilObjTestGUI: ilAssQuestionHintsGUI, ilAssQuestionFeedbackEditingGUI, ilLocalUnitConfigurationGUI, assFormulaQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestPassDetailsOverviewTableGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestResultsToolbarGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestCorrectionsGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestSettingsChangeConfirmationGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestSkillAdministrationGUI
 * @ilCtrl_Calls ilObjTestGUI: ilAssQuestionPreviewGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestQuestionBrowserTableGUI, ilTestInfoScreenToolbarGUI, ilLTIProviderObjectSettingGUI
 *
 * @ingroup ModulesTest
 */
class ilObjTestGUI extends ilObjectGUI implements ilCtrlBaseClassInterface
{
    private static $infoScreenChildClasses = array(
        'ilpublicuserprofilegui', 'ilobjportfoliogui'
    );

    /** @var ilTestQuestionSetConfigFactory $testQuestionSetConfigFactory Factory for question set config. */
    private $testQuestionSetConfigFactory = null;


    /** @var ilTestSessionFactory $testSessionFactory Factory for test session. */
    private $testSessionFactory = null;

    /**
     * @var ilTestTabsManager
     */
    protected $tabsManager;

    /**
     * @var ilTestObjectiveOrientedContainer
     */
    private $objectiveOrientedContainer;

    /**
     * @var ilTestAccess
     */
    protected $testAccess;

    protected \ILIAS\Test\InternalRequestService $testrequest;

    /**
     * Constructor
     * @access public
     * @param mixed|null $refId
     */
    public function __construct($refId = null)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $component_repository = $DIC['component.repository'];
        $tree = $DIC['tree'];
        $lng->loadLanguageModule("assessment");
        $this->type = "tst";
        $this->error = $DIC['ilErr'];
        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array("ref_id", "test_ref_id", "calling_test", "test_express_mode", "q_id"));
        $this->testrequest = $DIC->test()->internal()->request();
        if ($this->testrequest->hasRefId() && is_numeric($this->testrequest->getRefId())) {
            $refId = $this->testrequest->getRefId();
        }
        parent::__construct("", (int) $refId, true, false);

        if ($this->object instanceof ilObjTest) {
            $this->testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory($tree, $ilDB, $component_repository, $this->object);
            $this->testSessionFactory = new ilTestSessionFactory($this->object);
            $this->setTestAccess(new ilTestAccess($this->ref_id, $this->object->getTestId()));
        } else {
            $this->setCreationMode(true); // I think?
        }
        $this->objectiveOrientedContainer = new ilTestObjectiveOrientedContainer();

        if ($this->object instanceof ilObjTest) {
            $tabsManager = new ilTestTabsManager($this->testAccess, $this->objectiveOrientedContainer);
            $tabsManager->setTestOBJ($this->object);
            $tabsManager->setTestSession($this->testSessionFactory->getSession());
            $tabsManager->setTestQuestionSetConfig($this->testQuestionSetConfigFactory->getQuestionSetConfig());
            if ($this->object->isDynamicTest()) {
                $hidden_tabs = $tabsManager->getHiddenTabs();
                $tabsManager->setHiddenTabs(array_merge($hidden_tabs, ['manscoring', 'scoringadjust']));
            }
            $this->setTabsManager($tabsManager);
        }
    }

    /**
    * execute command
    */
    public function executeCommand(): void
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ilAccess = $DIC['ilAccess'];
        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilTabs = $DIC['ilTabs'];
        $component_repository = $DIC['component.repository'];
        $ilDB = $DIC['ilDB'];
        $tree = $DIC['tree'];
        $ilias = $DIC['ilias'];
        $ilUser = $DIC['ilUser'];
        $randomGroup = $DIC->refinery()->random();

        $cmd = $this->ctrl->getCmd("infoScreen");

        $cmdsDisabledDueToOfflineStatus = array(
            'resumePlayer', 'resumePlayer', 'outUserResultsOverview', 'outUserListOfAnswerPasses'
        );

        if (!$this->getCreationMode() && $this->object->getOfflineStatus() && in_array($cmd, $cmdsDisabledDueToOfflineStatus)) {
            $cmd = 'infoScreen';
        }

        $next_class = $this->ctrl->getNextClass($this);
        $this->ctrl->setReturn($this, "infoScreen");

        // add entry to navigation history
        if (!$this->getCreationMode() &&
            $ilAccess->checkAccess("read", "", $this->testrequest->getRefId())
        ) {
            $ilNavigationHistory->addItem(
                $this->testrequest->getRefId(),
                "ilias.php?baseClass=ilObjTestGUI&cmd=infoScreen&ref_id=" . $this->testrequest->getRefId(),
                "tst"
            );
        }

        // elba hack for storing question id for inserting new question after
        if ($this->testrequest->raw('prev_qid')) {
            global $___prev_question_id;
            $___prev_question_id = $this->testrequest->raw('prev_qid');
            $this->ctrl->setParameter($this, 'prev_qid', $this->testrequest->raw('prev_qid'));
        }

        if (!$this->getCreationMode() && $this->testQuestionSetConfigFactory->getQuestionSetConfig()->areDepenciesBroken()) {
            if (!$this->testQuestionSetConfigFactory->getQuestionSetConfig()->isValidRequestOnBrokenQuestionSetDepencies($next_class, $cmd)) {
                $this->ctrl->redirectByClass('ilObjTestGUI', 'infoScreen');
            }
        }

        $this->determineObjectiveOrientedContainer();

        switch ($next_class) {
            case 'illtiproviderobjectsettinggui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->tabsManager->getSettingsSubTabs();
                $GLOBALS['DIC']->tabs()->activateTab('settings');
                $GLOBALS['DIC']->tabs()->activateSubTab('lti_provider');
                $lti_gui = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
                $lti_gui->setCustomRolesForSelection($GLOBALS['DIC']->rbac()->review()->getLocalRoles($this->object->getRefId()));
                $lti_gui->offerLTIRolesForSelection(false);
                $this->ctrl->forwardCommand($lti_gui);
                break;


            case 'iltestexportgui':
                if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
                    $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
                }

                $this->prepareOutput();
                $this->addHeaderAction();
                $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_EXPORT);
                $ilCtrl->forwardCommand(new ilTestExportGUI($this));
                break;

            case "ilinfoscreengui":
                if (!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()) && !$ilAccess->checkAccess("visible", "", $this->testrequest->getRefId())) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->infoScreen(); // forwards command
                break;
            case 'ilobjectmetadatagui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
                }

                $this->prepareOutput();
                $this->addHeaderAction();
                $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_META_DATA);
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'iltestdashboardgui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();

                $gui = new ilTestDashboardGUI(
                    $this->getTestObject(),
                    $this->testQuestionSetConfigFactory->getQuestionSetConfig()
                );

                $gui->setTestAccess($this->getTestAccess());
                $gui->setTestTabs($this->getTabsManager());
                $gui->setObjectiveParent($this->getObjectiveOrientedContainer());

                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestresultsgui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();

                $gui = new ilTestResultsGUI(
                    $this->getTestObject(),
                    $this->testQuestionSetConfigFactory->getQuestionSetConfig()
                );

                $gui->setTestAccess($this->getTestAccess());
                $gui->setTestSession($this->testSessionFactory->getSession());
                $gui->setTestTabs($this->getTabsManager());
                $gui->setObjectiveParent($this->getObjectiveOrientedContainer());

                $this->ctrl->forwardCommand($gui);
                break;

            case "iltestplayerfixedquestionsetgui":
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->trackTestObjectReadEvent();
                if (!$this->object->getKioskMode()) {
                    $this->prepareOutput();
                }
                $gui = new ilTestPlayerFixedQuestionSetGUI($this->getTestObject());
                $gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
                $this->ctrl->forwardCommand($gui);
                break;

            case "iltestplayerrandomquestionsetgui":
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->trackTestObjectReadEvent();
                if (!$this->object->getKioskMode()) {
                    $this->prepareOutput();
                }
                $gui = new ilTestPlayerRandomQuestionSetGUI($this->getTestObject());
                $gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
                $this->ctrl->forwardCommand($gui);
                break;

            case "iltestevaluationgui":
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->forwardToEvaluationGUI();
                break;

            case "iltestevalobjectiveorientedgui":
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                // @PHP8-CR I believe this is an indicator for an incomplete feature. I wish to leave it in place
                // "as is"for further analysis.
                $this->forwardToEvalObjectiveOrientedGUI();
                break;

            case "iltestservicegui":
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $serviceGUI = new ilTestServiceGUI($this->object);
                $this->ctrl->forwardCommand($serviceGUI);
                break;

            case 'ilpermissiongui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_PERMISSIONS);
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "illearningprogressgui":
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_LEARNING_PROGRESS);
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $this->object->getRefId());
                $this->ctrl->forwardCommand($new_gui);

                break;

            case "ilcertificategui":
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();

                $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_SETTINGS);

                $guiFactory = new ilCertificateGUIFactory();
                $output_gui = $guiFactory->create($this->object);

                $this->ctrl->forwardCommand($output_gui);
                break;

            case "iltestscoringgui":
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $output_gui = new ilTestScoringGUI($this->object);
                $output_gui->setTestAccess($this->getTestAccess());
                $this->ctrl->forwardCommand($output_gui);
                break;

            case 'ilmarkschemagui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_test'), true);
                    $this->ctrl->redirect($this, 'infoScreen');
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $mark_schema_gui = new ilMarkSchemaGUI($this->getTestObject());
                $this->ctrl->forwardCommand($mark_schema_gui);
                break;

            case 'iltestscoringbyquestionsgui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $output_gui = new ilTestScoringByQuestionsGUI($this->getTestObject());
                $output_gui->setTestAccess($this->getTestAccess());
                $this->ctrl->forwardCommand($output_gui);
                break;

            case 'ilobjtestsettingsgeneralgui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = new ilObjTestSettingsGeneralGUI(
                    $this->ctrl,
                    $ilAccess,
                    $this->lng,
                    $this->tree,
                    $ilDB,
                    $component_repository,
                    $ilUser,
                    $this
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjtestsettingsscoringresultsgui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = new ilObjTestSettingsScoringResultsGUI(
                    $this->ctrl,
                    $ilAccess,
                    $this->lng,
                    $this->tree,
                    $ilDB,
                    $component_repository,
                    $this
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjtestfixedquestionsetconfiggui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = new ilObjTestDynamicQuestionSetConfigGUI($this->ctrl, $ilAccess, $ilTabs, $this->lng, $this->tpl, $ilDB, $tree, $component_repository, $this->getTestObject());
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestrandomquestionsetconfiggui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = new ilTestRandomQuestionSetConfigGUI(
                    $this->ctrl,
                    $ilAccess,
                    $ilTabs,
                    $this->lng,
                    $this->tpl,
                    $ilDB,
                    $tree,
                    $component_repository,
                    $this->getTestObject(),
                    (new ilTestProcessLockerFactory(
                        new ilSetting('assessment'),
                        $ilDB
                    ))->withContextId($this->object->getId())
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjtestdynamicquestionsetconfiggui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = new ilObjTestDynamicQuestionSetConfigGUI($this->ctrl, $ilAccess, $ilTabs, $this->lng, $this->tpl, $ilDB, $tree, $component_repository, $this->getTestObject());
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestquestionbrowsertablegui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = new ilTestQuestionBrowserTableGUI(
                    $this->ctrl,
                    $this->tpl,
                    $ilTabs,
                    $this->lng,
                    $tree,
                    $ilDB,
                    $component_repository,
                    $this->getTestObject(),
                    $ilAccess,
                    $DIC->http(),
                    $DIC->refinery()
                );
                $gui->setWriteAccess($ilAccess->checkAccess("write", "", $this->ref_id));
                $gui->init();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestskilladministrationgui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = new ilTestSkillAdministrationGUI($ilias, $this->ctrl, $ilAccess, $ilTabs, $this->tpl, $this->lng, $ilDB, $tree, $component_repository, $this->getTestObject(), $this->ref_id);
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjectcopygui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('tst');
                $this->ctrl->forwardCommand($cp);
                break;

            case 'ilpageeditorgui':
            case 'iltestexpresspageobjectgui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->getTabsManager()->getQuestionsSubTabs();
                $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_PAGE_VIEW);
                $incompleteQuestionPurger = new ilAssIncompleteQuestionPurger($ilDB);
                $incompleteQuestionPurger->setOwnerId($ilUser->getId());
                $incompleteQuestionPurger->purge();

                try {
                    $qid = $this->fetchAuthoringQuestionIdParameter();
                } catch (ilTestException $e) {
                    $qid = 0;
                }

                $this->prepareOutput();
                if (!in_array($cmd, array('addQuestion', 'browseForQuestions'))) {
                    $this->buildPageViewToolbar($qid);
                }

                if (!$qid || in_array($cmd, array('insertQuestions', 'browseForQuestions'))) {
                    $pageObject = new ilTestExpressPageObjectGUI(0);
                    $pageObject->test_object = $this->object;
                    $ret = $this->ctrl->forwardCommand($pageObject);
                    $this->tpl->setContent($ret);
                    break;
                }
                $this->tpl->setCurrentBlock("ContentStyle");
                $this->tpl->setVariable(
                    "LOCATION_CONTENT_STYLESHEET",
                    ilObjStyleSheet::getContentStylePath(0)
                );
                $this->tpl->parseCurrentBlock();

                // syntax style
                $this->tpl->setCurrentBlock("SyntaxStyle");
                $this->tpl->setVariable(
                    "LOCATION_SYNTAX_STYLESHEET",
                    ilObjStyleSheet::getSyntaxStylePath()
                );
                $this->tpl->parseCurrentBlock();

                $q_gui = assQuestionGUI::_getQuestionGUI("", $qid);
                if (!($q_gui instanceof assQuestionGUI)) {
                    $this->ctrl->setParameterByClass('iltestexpresspageobjectgui', 'q_id', '');
                    $this->ctrl->redirectByClass('iltestexpresspageobjectgui', $this->ctrl->getCmd());
                }

                $q_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PREVIEW);

                $q_gui->outAdditionalOutput();
                $q_gui->object->setObjId($this->object->getId());

                $q_gui->setTargetGuiClass(null);
                $q_gui->setQuestionActionCmd(null);

                $question = $q_gui->object;
                $this->ctrl->saveParameter($this, "q_id");

                #$this->lng->loadLanguageModule("content");
                $this->ctrl->setReturnByClass("ilTestExpressPageObjectGUI", "view");
                $this->ctrl->setReturn($this, "questions");

                $page_gui = new ilTestExpressPageObjectGUI($qid);
                $page_gui->test_object = $this->object;
                $page_gui->setEditPreview(true);
                $page_gui->setEnabledTabs(false);
                if (strlen($this->ctrl->getCmd()) == 0) {
                    $this->ctrl->setCmdClass(get_class($page_gui));
                    $this->ctrl->setCmd("preview");
                }

                $page_gui->setQuestionHTML(array($q_gui->object->getId() => $q_gui->getPreview(true)));
                $page_gui->setTemplateTargetVar("ADM_CONTENT");

                $page_gui->setOutputMode($this->object->evalTotalPersons() == 0 ? "edit" : 'preview');

                $page_gui->setHeader($question->getTitle());
                $page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
                $page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "fullscreen"));
                $page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this));
                $page_gui->setPresentationTitle($question->getTitle() . ' [' . $this->lng->txt('question_id_short') . ': ' . $question->getId() . ']');
                $ret = $this->ctrl->forwardCommand($page_gui);
                if ($ret != "") {
                    $tpl->setContent($ret);
                }

                global $DIC;
                $ilTabs = $DIC['ilTabs'];
                $ilTabs->activateTab('assQuestions');

                break;

            case 'ilassquestionpreviewgui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->prepareOutput();

                $this->ctrl->saveParameter($this, "q_id");

                $gui = new ilAssQuestionPreviewGUI($this->ctrl, $this->tabs_gui, $this->tpl, $this->lng, $ilDB, $ilUser, $randomGroup);

                $gui->initQuestion($this->fetchAuthoringQuestionIdParameter(), $this->object->getId());
                $gui->initPreviewSettings($this->object->getRefId());
                $gui->initPreviewSession($ilUser->getId(), $this->testrequest->getQuestionId());
                $gui->initHintTracking();
                $gui->initStyleSheets();

                $this->ctrl->forwardCommand($gui);

                break;

            case 'ilassquestionpagegui':
                if ($cmd == 'finishEditing') {
                    $this->ctrl->redirectByClass('ilassquestionpreviewgui', 'show');
                    break;
                }
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                if ($cmd === 'edit' && !$ilAccess->checkAccess('write', '', $this->testrequest->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->prepareOutput();
                $forwarder = new ilAssQuestionPageCommandForwarder();
                $forwarder->setTestObj($this->getTestObject());
                $forwarder->forward();
                break;

            case 'ilassspecfeedbackpagegui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $pg_gui = new ilAssSpecFeedbackPageGUI((int) $this->testrequest->raw("feedback_id"));
                $this->ctrl->forwardCommand($pg_gui);
                break;

            case 'ilassgenfeedbackpagegui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $pg_gui = new ilAssGenFeedbackPageGUI($this->testrequest->int("feedback_id"));
                $this->ctrl->forwardCommand($pg_gui);
                break;

            case 'illocalunitconfigurationgui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareSubGuiOutput();

                // set return target
                $this->ctrl->setReturn($this, "questions");
                $questionGUI = assQuestionGUI::_getQuestionGUI('', $this->fetchAuthoringQuestionIdParameter());
                $questionGUI->object->setObjId($this->object->getId());
                $questionGUI->setQuestionTabs();
                $gui = new ilLocalUnitConfigurationGUI(
                    new ilUnitConfigurationRepository($this->testrequest->getQuestionId())
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case "ilcommonactiondispatchergui":
                if (!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()) && !$ilAccess->checkAccess("visible", "", $this->testrequest->getRefId())) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilassquestionhintsgui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->prepareSubGuiOutput();

                // set return target
                $this->ctrl->setReturn($this, "questions");
                $questionGUI = assQuestionGUI::_getQuestionGUI('', $this->fetchAuthoringQuestionIdParameter());
                $questionGUI->object->setObjId($this->object->getId());
                $questionGUI->setQuestionTabs();

                if ($questionGUI->object->isInActiveTest()) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("question_is_part_of_running_test"), true);
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }
                $gui = new ilAssQuestionHintsGUI($questionGUI);

                $gui->setEditingEnabled(
                    $DIC->access()->checkAccess('write', '', $this->object->getRefId())
                );

                $ilCtrl->forwardCommand($gui);

                break;

            case 'ilassquestionfeedbackeditinggui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->prepareSubGuiOutput();

                // set return target
                $this->ctrl->setReturn($this, "questions");
                $questionGUI = assQuestionGUI::_getQuestionGUI('', $this->fetchAuthoringQuestionIdParameter());
                $questionGUI->object->setObjId($this->object->getId());
                $questionGUI->setQuestionTabs();

                if ($questionGUI->object->isInActiveTest()) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("question_is_part_of_running_test"), true);
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }
                $gui = new ilAssQuestionFeedbackEditingGUI($questionGUI, $ilCtrl, $ilAccess, $tpl, $ilTabs, $lng);
                $ilCtrl->forwardCommand($gui);

                break;

            case 'iltestcorrectionsgui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $gui = new ilTestCorrectionsGUI($DIC, $this->getTestObject());
                $this->ctrl->forwardCommand($gui);
                break;

            case '':
            case 'ilobjtestgui':
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()) && !$ilAccess->checkAccess("visible", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                if ((strcmp($cmd, "properties") == 0) && ($this->testrequest->raw("browse"))) {
                    $this->questionsObject();
                    return;
                }
                if ((strcmp($cmd, "properties") == 0) && ($this->testrequest->raw("up") || $this->testrequest->raw("down"))) {
                    $this->questionsObject();
                    return;
                }
                $cmd .= "Object";
                $ret = $this->$cmd();
                break;
            default:
                if ((!$ilAccess->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
                }
                if (in_array($cmd, ['editQuestion', 'save', 'suggestedsolution']) && !$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                // elba hack for storing question id for inserting new question after
                if ($this->testrequest->raw('prev_qid')) {
                    global $___prev_question_id;
                    $___prev_question_id = $this->testrequest->raw('prev_qid');
                    $this->ctrl->setParameterByClass('ilassquestionpagegui', 'prev_qid', $this->testrequest->raw('prev_qid'));
                    $this->ctrl->setParameterByClass($this->testrequest->raw('sel_question_types') . 'gui', 'prev_qid', $this->testrequest->raw('prev_qid'));
                }
                $this->create_question_mode = true;
                $this->prepareOutput();

                $this->ctrl->setReturn($this, "questions");

                try {
                    $qid = $this->fetchAuthoringQuestionIdParameter();

                    $questionGui = assQuestionGUI::_getQuestionGUI(
                        ilUtil::stripSlashes($this->testrequest->raw('sel_question_types') ?? ''),
                        $qid
                    );

                    $questionGui->setEditContext(assQuestionGUI::EDIT_CONTEXT_AUTHORING);
                    $questionGui->object->setObjId($this->object->getId());

                    if (in_array($cmd, ['editQuestion', 'save', 'suggestedsolution']) && $questionGui->object->isInActiveTest()) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("question_is_part_of_running_test"), true);
                        $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                    }

                    $questionGuiClass = get_class($questionGui);
                    $this->ctrl->setParameterByClass($questionGuiClass, 'prev_qid', $this->testrequest->raw('prev_qid'));
                    $this->ctrl->setParameterByClass($questionGuiClass, 'test_ref_id', $this->testrequest->getRefId());
                    $this->ctrl->setParameterByClass($questionGuiClass, 'q_id', $qid);

                    if ($this->testrequest->isset('test_express_mode')) {
                        $this->ctrl->setParameterByClass($questionGuiClass, 'test_express_mode', 1);
                    }

                    $questionGui->setQuestionTabs();

                    $this->ctrl->forwardCommand($questionGui);
                } catch (ilTestException $e) {
                    if ($this->testrequest->isset('test_express_mode')) {
                        $this->ctrl->redirect($this, 'showPage');
                    } else {
                        $this->ctrl->redirect($this, 'questions');
                    }
                }
                break;
        }
        if (!in_array(strtolower($this->testrequest->raw("baseClass")), array('iladministrationgui', 'ilrepositorygui')) &&
            $this->getCreationMode() != true) {
            $this->tpl->printToStdout();
        }
    }

    protected function redirectAfterMissingWrite()
    {
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_permission"), true);
        $target_class = get_class($this->object) . "GUI";
        $this->ctrl->setParameterByClass($target_class, 'ref_id', $this->ref_id);
        $this->ctrl->redirectByClass($target_class);
    }

    protected function trackTestObjectReadEvent()
    {
        /* @var ILIAS\DI\Container $DIC */ global $DIC;

        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $DIC->user()->getId()
        );
    }

    /**
     * Gateway for exports initiated from workspace, as there is a generic
     * forward to {objTypeMainGUI}::export()
     */
    protected function exportObject()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $DIC->ctrl()->redirectByClass('ilTestExportGUI');
    }

    /**
     * @return int
     * @throws ilTestException
     */
    protected function fetchAuthoringQuestionIdParameter(): int
    {
        $qid = $this->testrequest->raw('q_id');

        if (!$qid || $qid == 'Array') {
            $questions = $this->object->getQuestionTitlesAndIndexes();

            $keys = array_keys($questions);
            $qid = (int) ($keys[0] ?? 0);

            $_REQUEST['q_id'] = $qid;
            $_GET['q_id'] = $qid;
            $_POST['q_id'] = $qid;
        }

        if ($this->object->checkQuestionParent($qid)) {
            return $qid;
        }

        throw new ilTestException('question id does not relate to parent object!');
    }

    private function questionsTabGatewayObject()
    {
        if ($this->object->isRandomTest()) {
            $this->ctrl->redirectByClass('ilTestRandomQuestionSetConfigGUI');
        }

        $this->ctrl->redirectByClass('ilObjTestGUI', 'questions');
    }

    private function userResultsGatewayObject()
    {
        $this->ctrl->setCmdClass('ilTestEvaluationGUI');
        $this->ctrl->setCmd('outUserResultsOverview');
        $this->tabs_gui->clearTargets();

        $this->forwardToEvaluationGUI();
    }

    private function testResultsGatewayObject()
    {
        global $DIC;
        $this->tabs_gui->clearTargets();

        $this->prepareOutput();
        $this->addHeaderAction();

        $this->ctrl->setCmdClass('ilParticipantsTestResultsGUI');
        $this->ctrl->setCmd('showParticipants');


        $gui = new ilParticipantsTestResultsGUI();
        $gui->setTestObj($this->object);

        $factory = new ilTestQuestionSetConfigFactory(
            $this->tree,
            $DIC->database(),
            $DIC['component.repository'],
            $this->object
        );
        $gui->setQuestionSetConfig($factory->getQuestionSetConfig());
        $gui->setObjectiveParent(new ilTestObjectiveOrientedContainer());
        $gui->setTestAccess($this->getTestAccess());
        $this->tabs_gui->activateTab('results');
        $this->ctrl->forwardCommand($gui);
    }

    /**
     * @return ilTestAccess
     */
    public function getTestAccess(): ilTestAccess
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
    public function getTabsManager(): ilTestTabsManager
    {
        return $this->tabsManager;
    }

    /**
     * @param ilTestTabsManager $tabsManager
     */
    public function setTabsManager($tabsManager)
    {
        $this->tabsManager = $tabsManager;
    }

    private function forwardToEvaluationGUI()
    {
        $this->prepareOutput();
        $this->addHeaderAction();
        $gui = new ilTestEvaluationGUI($this->getTestObject());
        $gui->setObjectiveOrientedContainer($this->getObjectiveOrientedContainer());
        $gui->setTestAccess($this->getTestAccess());

        $this->ctrl->forwardCommand($gui);
    }

    private function redirectTo_ilObjTestSettingsGeneralGUI_showForm_Object()
    {
        $this->ctrl->redirectByClass('ilObjTestSettingsGeneralGUI', ilObjTestSettingsGeneralGUI::CMD_SHOW_FORM);
    }

    private function prepareSubGuiOutput()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $this->tpl->loadStandardTemplate();

        // set locator
        $this->setLocator();

        // set title and description and title icon
        $this->setTitleAndDescription();
    }

    public function runObject()
    {
        $this->ctrl->redirect($this, "infoScreen");
    }

    public function outEvaluationObject()
    {
        $this->ctrl->redirectByClass("iltestevaluationgui", "outEvaluation");
    }

    /**
    * form for new test object import
    */
    protected function importFileObject(int $parent_id = null, bool $catch_errors = true): void
    {
        $form = $this->initImportForm($this->testrequest->raw("new_type"));
        if ($form->checkInput()) {
            $this->ctrl->setParameter($this, "new_type", $this->type);
            $this->uploadTstObject();
            return;
        }

        // display form to correct errors
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    public function addDidacticTemplateOptions(array &$options): void
    {
        $tst = new ilObjTest();
        $defaults = $tst->getAvailableDefaults();
        if (count($defaults)) {
            foreach ($defaults as $row) {
                $options["tstdef_" . $row["test_defaults_id"]] = array($row["name"],
                    $this->lng->txt("tst_default_settings"));
            }
        }
    }

    /**
    * save object
    * @access	public
    */
    public function afterSave(ilObject $new_object): void
    {
        $tstdef = $this->getDidacticTemplateVar("tstdef");
        if ($tstdef) {
            $testDefaultsId = $tstdef;
            $testDefaults = ilObjTest::_getTestDefaults($testDefaultsId);
            $new_object->applyDefaults($testDefaults);
        }

        $template_id = $this->getDidacticTemplateVar("tsttpl");
        if ($template_id) {
            $new_object->setTemplate($template_id);
        }

        $new_object->saveToDb();

        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->ctrl->setParameter($this, 'ref_id', $new_object->getRefId());
        $this->ctrl->redirectByClass('ilObjTestSettingsGeneralGUI');
    }

    public function backToRepositoryObject()
    {
        $path = $this->tree->getPathFull($this->object->getRefID());
        ilUtil::redirect($this->getReturnLocation("cancel", "./ilias.php?baseClass=ilRepositoryGUI&cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
    }

    /**
    * imports test and question(s)
    */
    public function uploadTstObject()
    {
        if ($_FILES["xmldoc"]["error"] > UPLOAD_ERR_OK) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("error_upload"));
            $this->createObject();
            return;
        }
        // create import directory
        $basedir = ilObjTest::_createImportDirectory();

        // copy uploaded file to import directory
        $file = pathinfo($_FILES["xmldoc"]["name"]);
        $full_path = $basedir . "/" . $_FILES["xmldoc"]["name"];
        ilFileUtils::moveUploadedFile($_FILES["xmldoc"]["tmp_name"], $_FILES["xmldoc"]["name"], $full_path);

        // unzip file
        ilFileUtils::unzip($full_path);

        // determine filenames of xml files
        $subdir = basename($file["basename"], "." . $file["extension"]);
        ilObjTest::_setImportDirectory($basedir);
        $xml_file = ilObjTest::_getImportDirectory() . '/' . $subdir . '/' . $subdir . ".xml";
        $qti_file = ilObjTest::_getImportDirectory() . '/' . $subdir . '/' . preg_replace("/test|tst/", "qti", $subdir) . ".xml";
        $results_file = ilObjTest::_getImportDirectory() . '/' . $subdir . '/' . preg_replace("/test|tst/", "results", $subdir) . ".xml";

        if (!is_file($qti_file)) {
            ilFileUtils::delDir($basedir);
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("tst_import_non_ilias_zip"));
            $this->createObject();
            return;
        }
        $qtiParser = new ilQTIParser($qti_file, ilQTIParser::IL_MO_VERIFY_QTI, 0, "");
        $qtiParser->startParsing();
        $founditems = $qtiParser->getFoundItems();

        $complete = 0;
        $incomplete = 0;
        foreach ($founditems as $item) {
            if (strlen($item["type"])) {
                $complete++;
            } else {
                $incomplete++;
            }
        }

        if (count($founditems) && $complete == 0) {
            // delete import directory
            ilFileUtils::delDir($basedir);

            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_import_non_ilias_files"));
            $this->createObject();
            return;
        }

        ilSession::set("tst_import_results_file", $results_file);
        ilSession::set("tst_import_xml_file", $xml_file);
        ilSession::set("tst_import_qti_file", $qti_file);
        ilSession::set("tst_import_subdir", $subdir);

        if ($qtiParser->getQuestionSetType() != ilObjTest::QUESTION_SET_TYPE_FIXED) {
            $this->importVerifiedFileObject();
            return;
        }

        $importVerificationTpl = new ilTemplate('tpl.tst_import_verification.html', true, true, 'Modules/Test');

        // on import creation screen the pool was chosen (-1 for no pool)
        // BUT when no pool is available the input on creation screen is missing, so the field value -1 for no pool is not submitted.
        $QplOrTstID = isset($_POST["qpl"]) && (int) $_POST["qpl"] != 0 ? $_POST["qpl"] : -1;

        $importVerificationTpl->setVariable("TEXT_TYPE", $this->lng->txt("question_type"));
        $importVerificationTpl->setVariable("TEXT_TITLE", $this->lng->txt("question_title"));
        $importVerificationTpl->setVariable("FOUND_QUESTIONS_INTRODUCTION", $this->lng->txt("tst_import_verify_found_questions"));
        $importVerificationTpl->setVariable("VERIFICATION_HEADING", $this->lng->txt("import_tst"));
        $importVerificationTpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $importVerificationTpl->setVariable("ARROW", ilUtil::getImagePath("arrow_downright.svg"));
        $importVerificationTpl->setVariable("QUESTIONPOOL_ID", $QplOrTstID);
        $importVerificationTpl->setVariable("VALUE_IMPORT", $this->lng->txt("import"));
        $importVerificationTpl->setVariable("VALUE_CANCEL", $this->lng->txt("cancel"));

        $row_class = ["tblrow1", "tblrow2"];
        $counter = 0;
        foreach ($founditems as $item) {
            $importVerificationTpl->setCurrentBlock("verification_row");
            $importVerificationTpl->setVariable("ROW_CLASS", $row_class[$counter++ % 2]);
            $importVerificationTpl->setVariable("QUESTION_TITLE", $item["title"]);
            $importVerificationTpl->setVariable("QUESTION_IDENT", $item["ident"]);

            switch ($item["type"]) {
                case MULTIPLE_CHOICE_QUESTION_IDENTIFIER:
                case ilQTIItem::QT_MULTIPLE_CHOICE_MR:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assMultipleChoice"));
                    break;
                case SINGLE_CHOICE_QUESTION_IDENTIFIER:
                case ilQTIItem::QT_MULTIPLE_CHOICE_SR:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assSingleChoice"));
                    break;
                case KPRIM_CHOICE_QUESTION_IDENTIFIER:
                case ilQTIItem::QT_KPRIM_CHOICE:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assKprimChoice"));
                    break;
                case LONG_MENU_QUESTION_IDENTIFIER:
                case ilQTIItem::QT_LONG_MENU:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assLongMenu"));
                    break;
                case NUMERIC_QUESTION_IDENTIFIER:
                case ilQTIItem::QT_NUMERIC:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assNumeric"));
                    break;
                case FORMULA_QUESTION_IDENTIFIER:
                case ilQTIItem::QT_FORMULA:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assFormulaQuestion"));
                    break;
                case TEXTSUBSET_QUESTION_IDENTIFIER:
                case ilQTIItem::QT_TEXTSUBSET:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assTextSubset"));
                    break;
                case CLOZE_TEST_IDENTIFIER:
                case ilQTIItem::QT_CLOZE:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assClozeTest"));
                    break;
                case ERROR_TEXT_IDENTIFIER:
                case ilQTIItem::QT_ERRORTEXT:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assErrorText"));
                    break;
                case IMAGEMAP_QUESTION_IDENTIFIER:
                case ilQTIItem::QT_IMAGEMAP:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assImagemapQuestion"));
                    break;
                case MATCHING_QUESTION_IDENTIFIER:
                case ilQTIItem::QT_MATCHING:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assMatchingQuestion"));
                    break;
                case ORDERING_QUESTION_IDENTIFIER:
                case ilQTIItem::QT_ORDERING:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assOrderingQuestion"));
                    break;
                case ORDERING_HORIZONTAL_IDENTIFIER:
                case ilQTIItem::QT_ORDERING_HORIZONTAL:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assOrderingHorizontal"));
                    break;
                case TEXT_QUESTION_IDENTIFIER:
                case ilQTIItem::QT_TEXT:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assTextQuestion"));
                    break;
                case FILE_UPLOAD_IDENTIFIER:
                case ilQTIItem::QT_FILEUPLOAD:
                    $importVerificationTpl->setVariable("QUESTION_TYPE", $this->lng->txt("assFileUpload"));
                    break;
            }
            $importVerificationTpl->parseCurrentBlock();
        }

        $this->tpl->setContent($importVerificationTpl->get());
    }

    public function getTestObject(): ?ilObjTest
    {
        /** @var null|ilObjTest $test */
        $test = $this->object;
        return $test;
    }

    /**
    * imports question(s) into the questionpool (after verification)
    */
    public function importVerifiedFileObject()
    {
        // create new questionpool object
        $newObj = new ilObjTest(0, true);
        // set type of questionpool object
        $newObj->setType($this->testrequest->raw("new_type"));
        // set title of questionpool object to "dummy"
        $newObj->setTitle("dummy");
        // set description of questionpool object
        $newObj->setDescription("test import");
        // create the questionpool class in the ILIAS database (object_data table)
        $newObj->create(true);
        // create a reference for the questionpool object in the ILIAS database (object_reference table)
        $newObj->createReference();
        // put the questionpool object in the administration tree
        $newObj->putInTree($this->testrequest->getRefId());
        // get default permissions and set the permissions for the questionpool object
        $newObj->setPermissions($this->testrequest->getRefId());
        // empty mark schema
        $newObj->mark_schema->flush();

        // Handle selection of "no questionpool" as qpl_id = -1 -> use test object id instead.
        // possible hint: chek if empty strings in $_POST["qpl_id"] relates to a bug or not
        if (!isset($_POST["qpl"]) || "-1" === (string) $_POST["qpl"]) {
            $questionParentObjId = $newObj->getId();
        } else {
            $questionParentObjId = $_POST["qpl"];
        }

        if (is_file(ilSession::get("tst_import_dir") . '/' . ilSession::get("tst_import_subdir") . "/manifest.xml")) {
            $newObj->saveToDb();

            ilSession::set('tst_import_idents', $_POST['ident']);
            ilSession::set('tst_import_qst_parent', $questionParentObjId);

            $fileName = ilSession::get('tst_import_subdir') . '.zip';
            $fullPath = ilSession::get('tst_import_dir') . '/' . $fileName;
            $imp = new ilImport($this->testrequest->getRefId());
            $map = $imp->getMapping();
            $map->addMapping('Modules/Test', 'tst', 'new_id', $newObj->getId());
            $imp->importObject($newObj, $fullPath, $fileName, 'tst', 'Modules/Test', true);
        } else {
            $qtiParser = new ilQTIParser(ilSession::get("tst_import_qti_file"), ilQTIParser::IL_MO_PARSE_QTI, $questionParentObjId, $_POST["ident"]);
            if (!isset($_POST["ident"]) || !is_array($_POST["ident"]) || !count($_POST["ident"])) {
                $qtiParser->setIgnoreItemsEnabled(true);
            }
            $qtiParser->setTestObject($newObj);
            $qtiParser->startParsing();
            $newObj->saveToDb();
            $contParser = new ilContObjParser($newObj, ilSession::get("tst_import_xml_file"), ilSession::get("tst_import_subdir"));
            $contParser->setQuestionMapping($qtiParser->getImportMapping());
            $contParser->startParsing();

            if (isset($_POST["ident"]) && is_array($_POST["ident"]) && count($_POST["ident"]) == $qtiParser->getNumImportedItems()) {
                // import test results
                if (@file_exists(ilSession::get("tst_import_results_file"))) {
                    $results = new ilTestResultsImportParser(ilSession::get("tst_import_results_file"), $newObj);
                    $results->setQuestionIdMapping($qtiParser->getQuestionIdMapping());
                    $results->startParsing();
                }
            }
            $newObj->update();
        }


        // delete import directory
        ilFileUtils::delDir(ilObjTest::_getImportDirectory());
        //Note, has been in ilTestImporter, however resetting this there, lead to problem in delDir.
        // See: https://github.com/ILIAS-eLearning/ILIAS/pull/5097
        ilObjTest::_setImportDirectory();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_imported"), true);
        ilUtil::redirect("ilias.php?ref_id=" . $newObj->getRefId() . "&baseClass=ilObjTestGUI");
    }

    /**
    * display status information or report errors messages
    * in case of error
    *
    * @access	public
    */
    public function uploadObject($redirect = true)
    {
        $this->uploadTstObject();
    }

    /**
    * download file
    */
    public function downloadFileObject()
    {
        $file = explode("_", $this->testrequest->raw("file_id"));
        $fileObj = new ilObjFile($file[count($file) - 1], false);
        $fileObj->sendFile();
        exit;
    }

    /**
    * show fullscreen view
    */
    public function fullscreenObject()
    {
        $page_gui = new ilAssQuestionPageGUI($this->testrequest->raw("pg_id"));
        $page_gui->showMediaFullscreen();
    }

    /**
    * download source code paragraph
    */
    public function download_paragraphObject()
    {
        $pg_obj = new ilAssQuestionPage($this->testrequest->raw("pg_id"));
        $pg_obj->send_paragraph($this->testrequest->raw("par_id"), $this->testrequest->raw("downloadtitle"));
        exit;
    }

    /*
    * Removing to get rid of a warning, leaving commented out for further analysis.
   public function filterObject()
   {
       $this->questionBrowser();
   }
   */

    /*
     * Removing to get rid of a warning, leaving commented out for further analysis.
     *
    public function resetFilterObject()
    {
        $this->questionBrowser();
    }
    */
    /**
    * Called when the back button in the question browser was pressed
    *
    * Called when the back button in the question browser was pressed
    *
    * @access	public
    */
    public function backObject()
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
    public function createQuestionPool($name = "dummy", $description = ""): int
    {
        global $DIC;
        $tree = $DIC['tree'];
        $parent_ref = $tree->getParentId($this->object->getRefId());
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

    public function randomselectObject()
    {
        $this->getTabsManager()->getQuestionsSubTabs();
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('random_selection'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'cancelRandomSelect'));

        $form->addCommandButton('createRandomSelection', $this->lng->txt('submit'));
        $form->addCommandButton('cancelRandomSelect', $this->lng->txt('cancel'));

        $amount = new ilNumberInputGUI($this->lng->txt('tst_random_nr_of_questions'), 'nr_of_questions');
        $amount->allowDecimals(false);
        $amount->setSize(5);
        $amount->setMinValue(1);
        $amount->setValue(5);
        $form->addItem($amount);

        $poolSelection = new ilSelectInputGUI($this->lng->txt('tst_source_question_pool'), 'sel_qpl');
        $poolSelection->setInfo($this->lng->txt('tst_random_select_questionpool'));
        $poolSelection->setRequired(true);
        $poolOptions = [];
        $questionpools = $this->object->getAvailableQuestionpools(false, false, false, true);
        foreach ($questionpools as $key => $value) {
            $poolOptions[$key] = $value['title'];
        }
        $poolSelection->setOptions(
            ['0' => $this->lng->txt('all_available_question_pools')] + $poolOptions
        );
        $form->addItem($poolSelection);

        $questionType = new ilHiddenInputGUI('sel_question_types');
        $questionType->setValue(ilUtil::stripSlashes($_POST['sel_question_types']));
        $form->addItem($questionType);

        $this->tpl->setContent($form->getHTML());
    }

    public function cancelRandomSelectObject()
    {
        $this->ctrl->redirect($this, "questions");
    }

    public function createRandomSelectionObject()
    {
        $this->getTabsManager()->getQuestionsSubTabs();
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);

        $randomQuestionSelectionTable = new ilTestRandomQuestionSelectionTableGUI($this, 'createRandomSelection', $this->getTestObject());

        $this->tpl->setContent(
            $randomQuestionSelectionTable
                ->build((int) $_POST['nr_of_questions'], (int) $_POST['sel_qpl'])
                ->getHtml()
        );
    }

    /**
    * Inserts a random selection into the test
    *
    * Inserts a random selection into the test
    *
    * @access	public
    */
    public function insertRandomSelectionObject()
    {
        $selected_array = explode(",", $_POST["chosen_questions"]);
        if (!count($selected_array)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_insert_missing_question"));
        } else {
            $total = $this->object->evalTotalPersons();
            if ($total) {
                // the test was executed previously
                $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_insert_questions"));
            }
            foreach ($selected_array as $key => $value) {
                $this->object->insertQuestion($this->testQuestionSetConfigFactory->getQuestionSetConfig(), $value);
            }
            $this->object->saveCompleteStatus($this->testQuestionSetConfigFactory->getQuestionSetConfig());
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_questions_inserted"), true);
            $this->ctrl->redirect($this, "questions");
            return;
        }
    }

    /*
     * Removing to get rid of a warning, leaving commented out for further analysis.
    public function browseForQuestionsObject()
    {
        $this->questionBrowser();
    }
    */

    /**
    * Called when a new question should be created from a test after confirmation
    */
    public function executeCreateQuestionObject(): void
    {
        $qpl_ref_id = $this->testrequest->raw("sel_qpl");

        try {
            $qpl_mode = $this->testrequest->int('usage');
        } catch (ConstraintViolationException $e) {
            $qpl_mode = 1;
        }

        if ($this->testrequest->isset('qtype')) {
            $sel_question_types = ilObjQuestionPool::getQuestionTypeByTypeId($this->testrequest->raw("qtype"));
        } elseif ($this->testrequest->isset('sel_question_types')) {
            $sel_question_types = $this->testrequest->raw("sel_question_types");
        }

        if (($qpl_mode == 2 && strcmp($this->testrequest->raw("txt_qpl"), "") == 0) || ($qpl_mode == 3 && strcmp($qpl_ref_id, "") == 0)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("questionpool_not_entered"));
            $this->createQuestionObject();
            return;
        } else {
            ilSession::set("test_id", $this->object->getRefId());
            if ($qpl_mode == 2 && strcmp($this->testrequest->raw("txt_qpl"), "") != 0) {
                // create a new question pool and return the reference id
                $qpl_ref_id = $this->createQuestionPool($this->testrequest->raw("txt_qpl"));
            } elseif ($qpl_mode == 1) {
                $qpl_ref_id = $this->testrequest->getRefId();
            }
            $baselink = "ilias.php?baseClass=ilObjQuestionPoolGUI&ref_id=" . $qpl_ref_id . "&cmd=createQuestionForTest&test_ref_id=" . $this->testrequest->getRefId() . "&calling_test=" . $this->testrequest->getRefId() . "&sel_question_types=" . $sel_question_types;

            if ($this->testrequest->isset('prev_qid')) {
                $baselink .= '&prev_qid=' . $this->testrequest->raw('prev_qid');
            } elseif ($this->testrequest->isset('position')) {
                $baselink .= '&prev_qid=' . $this->testrequest->raw('position');
            }

            if ($this->testrequest->raw('test_express_mode')) {
                $baselink .= '&test_express_mode=1';
            }

            if ($this->testrequest->isset('add_quest_cont_edit_mode')) {
                $baselink = ilUtil::appendUrlParameterString(
                    $baselink,
                    "add_quest_cont_edit_mode={$this->testrequest->raw('add_quest_cont_edit_mode')}",
                    false
                );
            }

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
    public function cancelCreateQuestionObject()
    {
        $this->ctrl->redirect($this, "questions");
    }

    /**
    * Called when a new question should be created from a test
    *
    * Called when a new question should be created from a test    *
    * @access	public

    */
    public function createQuestionObject()
    {
        global $DIC;
        $lng = $DIC['lng'];

        $this->getTabsManager()->getQuestionsSubTabs();
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);
        //$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_qpl_select.html", "Modules/Test");
        $questionpools = $this->object->getAvailableQuestionpools(false, false, false, true, false, "write");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "executeCreateQuestion"));
        $form->setTitle($lng->txt("ass_create_question"));

        if ($this->testrequest->isset('qtype')) {
            $sel_question_types = ilObjQuestionPool::getQuestionTypeByTypeId($this->testrequest->raw("qtype"));
        } elseif ($this->testrequest->isset('sel_question_types')) {
            $sel_question_types = $this->testrequest->raw("sel_question_types");
        }


        $hidden = new ilHiddenInputGUI('sel_question_types');
        $hidden->setValue($sel_question_types);
        $form->addItem($hidden);

        // content editing mode
        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $ri = new ilRadioGroupInputGUI($lng->txt("tst_add_quest_cont_edit_mode"), "add_quest_cont_edit_mode");

            $option_ipe = new ilRadioOption(
                $lng->txt('tst_add_quest_cont_edit_mode_IPE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE
            );
            $option_ipe->setInfo($lng->txt('tst_add_quest_cont_edit_mode_IPE_info'));
            $ri->addOption($option_ipe);

            $option_rte = new ilRadioOption(
                $lng->txt('tst_add_quest_cont_edit_mode_RTE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE
            );
            $option_rte->setInfo($lng->txt('tst_add_quest_cont_edit_mode_RTE_info'));
            $ri->addOption($option_rte);

            $ri->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE);

            $form->addItem($ri, true);
        } else {
            $hi = new ilHiddenInputGUI("question_content_editing_type");
            $hi->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE);
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

        $questionpools = ilObjQuestionPool::_getAvailableQuestionpools(false, false, true, false, false, "write");
        $pools_data = array();
        foreach ($questionpools as $key => $p) {
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

        $this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
    }

    /**
     * Remove questions from the test after confirmation
     */
    public function confirmRemoveQuestionsObject()
    {
        $removeQuestionIds = (array) $_POST["q_id"];

        $questions = $this->object->getQuestionTitlesAndIndexes();

        $this->object->removeQuestions($removeQuestionIds);

        $this->object->saveCompleteStatus($this->testQuestionSetConfigFactory->getQuestionSetConfig());

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_questions_removed"));

        if ($this->testrequest->raw('test_express_mode')) {
            $prev = null;
            $return_to = null;
            $deleted_tmp = $removeQuestionIds;
            $first = array_shift($deleted_tmp);
            foreach ($questions as $key => $value) {
                if (!in_array($key, $removeQuestionIds)) {
                    $prev = $key;
                    if (!$first) {
                        $return_to = $prev;
                        break;
                    } else {
                        continue;
                    }
                } elseif ($key == $first) {
                    if ($prev) {
                        $return_to = $prev;
                        break;
                    }
                    $first = array_shift($deleted_tmp);
                }
            }

            if (
                count($questions) == count($removeQuestionIds) ||
                !$return_to
            ) {
                $this->ctrl->setParameter($this, 'q_id', '');
                $this->ctrl->redirect($this, 'showPage');
            }

            $this->ctrl->setParameter($this, 'q_id', $return_to);
            $this->ctrl->redirect($this, "showPage");
        } else {
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
    public function cancelRemoveQuestionsObject()
    {
        if ($this->testrequest->raw('test_express_mode')) {
            $this->ctrl->setParameter($this, 'q_id', $this->testrequest->raw('q_id'));
            $this->ctrl->redirect($this, "showPage");
        } else {
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
    public function removeQuestionsForm($checked_questions)
    {
        $total = $this->object->evalTotalPersons();
        if ($total) {
            // the test was executed previously
            $question = sprintf($this->lng->txt("tst_remove_questions_and_results"), $total);
        } else {
            if (count($checked_questions) == 1) {
                $question = $this->lng->txt("tst_remove_question");
            } else {
                $question = $this->lng->txt("tst_remove_questions");
            }
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($question);

        $this->ctrl->saveParameter($this, 'test_express_mode');
        $this->ctrl->saveParameter($this, 'q_id');

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelRemoveQuestions");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmRemoveQuestions");
        $removablequestions = $this->object->getTestQuestions();
        if (count($removablequestions)) {
            foreach ($removablequestions as $data) {
                if (in_array($data["question_id"], $checked_questions)) {
                    $txt = $data["title"] . " (" . assQuestion::_getQuestionTypeName($data["type_tag"]) . ")";
                    $txt .= ' [' . $this->lng->txt('question_id_short') . ': ' . $data['question_id'] . ']';

                    if ($data["description"]) {
                        $txt .= "<div class=\"small\">" . $data["description"] . "</div>";
                    }

                    $cgui->addItem("q_id[]", $data["question_id"], $txt);
                }
            }
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * Called when a selection of questions should be removed from the test
     */
    public function removeQuestionsObject()
    {
        $this->getTabsManager()->getQuestionsSubTabs();

        $checked_questions = $this->testrequest->raw('q_id');

        if (!is_array($checked_questions) && $checked_questions) {
            $checked_questions = array($checked_questions);
        }

        if (!is_array($checked_questions)) {
            $checked_questions = [];
        }

        if (count($checked_questions) > 0) {
            $this->removeQuestionsForm($checked_questions);
        } elseif (0 === count($checked_questions)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("tst_no_question_selected_for_removal"), true);
            $this->ctrl->redirect($this, "questions");
        }
    }

    /**
    * Marks selected questions for moving
    */
    public function moveQuestionsObject()
    {
        $selected_questions = $this->testrequest->getQuestionId();
        if ($selected_questions === null && is_numeric($this->testrequest->getQuestionId())) {
            $selected_questions = [$this->testrequest->getQuestionId()];
        }
        if (is_array($selected_questions)) {
            ilSession::set('tst_qst_move_' . $this->object->getTestId(), $selected_questions);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_selected_for_move"), true);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_selection_for_move'), true);
        }
        $this->ctrl->redirect($this, 'questions');
    }

    /**
    * Insert checked questions before the actual selection
    */
    public function insertQuestionsBeforeObject()
    {
        // get all questions to move
        $move_questions = ilSession::get('tst_qst_move_' . $this->object->getTestId());

        if (!is_array($_POST['q_id']) || 0 === count($_POST['q_id'])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_target_selected_for_move"), true);
            $this->ctrl->redirect($this, 'questions');
        }
        if (count($_POST['q_id']) > 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("too_many_targets_selected_for_move"), true);
            $this->ctrl->redirect($this, 'questions');
        }
        $insert_mode = 0;
        $this->object->moveQuestions(ilSession::get('tst_qst_move_' . $this->object->getTestId()), $_POST['q_id'][0], $insert_mode);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_questions_moved"), true);
        ilSession::clear('tst_qst_move_' . $this->object->getTestId());
        $this->ctrl->redirect($this, "questions");
    }

    /**
    * Insert checked questions after the actual selection
    */
    public function insertQuestionsAfterObject()
    {
        // get all questions to move
        $move_questions = ilSession::get('tst_qst_move_' . $this->object->getTestId());
        if (!is_array($_POST['q_id']) || 0 === count($_POST['q_id'])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_target_selected_for_move"), true);
            $this->ctrl->redirect($this, 'questions');
        }
        if (count($_POST['q_id']) > 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("too_many_targets_selected_for_move"), true);
            $this->ctrl->redirect($this, 'questions');
        }
        $insert_mode = 1;
        $this->object->moveQuestions(ilSession::get('tst_qst_move_' . $this->object->getTestId()), $_POST['q_id'][0], $insert_mode);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_questions_moved"), true);
        ilSession::clear('tst_qst_move_' . $this->object->getTestId());
        $this->ctrl->redirect($this, "questions");
    }

    /**
    * Insert questions from the questionbrowser into the test
    *
    * @access	public
    */
    public function insertQuestionsObject()
    {
        $selected_array = (is_array($_POST['q_id'])) ? $_POST['q_id'] : array();
        if (!count($selected_array)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_insert_missing_question"), true);
            $this->ctrl->redirect($this, "browseForQuestions");
        } else {
            $manscoring = false;
            foreach ($selected_array as $key => $value) {
                $this->object->insertQuestion($this->testQuestionSetConfigFactory->getQuestionSetConfig(), $value);
                if (!$manscoring) {
                    $manscoring = $manscoring | assQuestion::_needsManualScoring($value);
                }
            }
            $this->object->saveCompleteStatus($this->testQuestionSetConfigFactory->getQuestionSetConfig());
            if ($manscoring) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("manscoring_hint"), true);
            } else {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_questions_inserted"), true);
            }
            $this->ctrl->redirect($this, "questions");
            return;
        }
    }

    public function addQuestionObject()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilHelp = $DIC['ilHelp']; /* @var ilHelpGUI $ilHelp */

        $this->getTabsManager()->getQuestionsSubTabs();
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);

        $subScreenId = array('createQuestion');

        $ilCtrl->setParameter($this, 'qtype', $this->testrequest->raw('qtype'));

        $form = new ilPropertyFormGUI();

        $form->setFormAction($ilCtrl->getFormAction($this, "executeCreateQuestion"));
        $form->setTitle($lng->txt("ass_create_question"));

        $pool = new ilObjQuestionPool();
        $questionTypes = $pool->getQuestionTypes(false, true, false);
        $options = array();

        // question type
        foreach ($questionTypes as $label => $data) {
            $options[$data['question_type_id']] = $label;
        }
        $si = new ilSelectInputGUI($lng->txt("question_type"), "qtype");
        $si->setOptions($options);
        $form->addItem($si, true);

        // position
        $questions = $this->object->getQuestionTitlesAndIndexes();
        if ($questions) {
            $si = new ilSelectInputGUI($lng->txt("position"), "position");
            $options = array('0' => $lng->txt('first'));
            foreach ($questions as $key => $title) {
                $options[$key] = $lng->txt('behind') . ' ' . $title . ' [' . $this->lng->txt('question_id_short') . ': ' . $key . ']';
            }
            $si->setOptions($options);
            $si->setValue($this->testrequest->raw('q_id'));
            $form->addItem($si, true);
        }

        // content editing mode
        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $subScreenId[] = 'editMode';

            $ri = new ilRadioGroupInputGUI($lng->txt("tst_add_quest_cont_edit_mode"), "add_quest_cont_edit_mode");

            $option_ipe = new ilRadioOption(
                $lng->txt('tst_add_quest_cont_edit_mode_IPE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE
            );
            $option_ipe->setInfo($lng->txt('tst_add_quest_cont_edit_mode_IPE_info'));
            $ri->addOption($option_ipe);

            $option_rte = new ilRadioOption(
                $lng->txt('tst_add_quest_cont_edit_mode_RTE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE
            );
            $option_rte->setInfo($lng->txt('tst_add_quest_cont_edit_mode_RTE_info'));
            $ri->addOption($option_rte);

            $ri->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE);

            $form->addItem($ri, true);
        } else {
            $hi = new ilHiddenInputGUI("question_content_editing_type");
            $hi->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE);
            $form->addItem($hi, true);
        }

        $subScreenId[] = 'poolSelect';

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

        $questionpools = ilObjQuestionPool::_getAvailableQuestionpools(false, false, true, false, false, "write");
        $pools_data = array();
        foreach ($questionpools as $key => $p) {
            $pools_data[$key] = $p['title'];
        }
        $pools = new ilSelectInputGUI($this->lng->txt("select_questionpool"), "sel_qpl");
        $pools->setOptions($pools_data);
        $existing_pool->addSubItem($pools);

        $name = new ilTextInputGUI($this->lng->txt("name"), "txt_qpl");
        $name->setSize(50);
        $name->setMaxLength(50);
        $new_pool->addSubItem($name);

        $form->addCommandButton("executeCreateQuestion", $lng->txt("create"));
        $form->addCommandButton("questions", $lng->txt("cancel"));

        $DIC->tabs()->activateTab('assQuestions');
        $ilHelp->setScreenId('assQuestions');
        $ilHelp->setSubScreenId(implode('_', $subScreenId));

        return $tpl->setContent($form->getHTML());
    }

    public function questionsObject()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ilAccess = $DIC['ilAccess'];
        $ilTabs = $DIC['ilTabs'];

        // #12590
        $this->ctrl->setParameter($this, 'test_express_mode', '');

        if (!$ilAccess->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }

        /*
        * Removing to get rid of a warning, leaving commented out for further analysis.
        if ($_GET['browse']) {
            $this->questionbrowser();
            return;
        }
        */

        $this->getTabsManager()->getQuestionsSubTabs();
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);

        // #11631, #12994
        $this->ctrl->setParameter($this, 'q_id', '');

        if ($this->testrequest->raw("eqid") && $this->testrequest->raw("eqpl")) {
            ilUtil::redirect("ilias.php?baseClass=ilObjQuestionPoolGUI&ref_id=" . $this->testrequest->raw("eqpl") . "&cmd=editQuestionForTest&calling_test=" . $this->testrequest->getRefId() . "&q_id=" . $this->testrequest->raw("eqid"));
        }

        if ($this->testrequest->raw("up") > 0) {
            $this->object->questionMoveUp($this->testrequest->raw("up"));
        }
        if ($this->testrequest->raw("down") > 0) {
            $this->object->questionMoveDown($this->testrequest->raw("down"));
        }

        if ($this->testrequest->raw("add")) {
            $selected_array = array();
            array_push($selected_array, $this->testrequest->raw("add"));
            $total = $this->object->evalTotalPersons();
            if ($total) {
                // the test was executed previously
                $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_insert_questions"));
            }
            // @PHP8-CR This call seems to be critically important for the method, but I cannot see how to fix it yet.
            // I leave the warning "intact" for further analysis, possibly by T&A TechSquad.
            $this->insertQuestions($selected_array);
            return;
        }

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questions.html", "Modules/Test");

        $total = $this->object->evalTotalPersons();
        if ($ilAccess->checkAccess("write", "", $this->ref_id)) {
            if ($total != 0) {
                $link = $DIC->ui()->factory()->link()->standard(
                    $DIC->language()->txt("test_has_datasets_warning_page_view_link"),
                    $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilParticipantsTestResultsGUI'))
                );

                $message = $DIC->language()->txt("test_has_datasets_warning_page_view");

                $msgBox = $DIC->ui()->factory()->messageBox()->info($message)->withLinks(array($link));

                $DIC->ui()->mainTemplate()->setCurrentBlock('mess');
                $DIC->ui()->mainTemplate()->setVariable(
                    'MESSAGE',
                    $DIC->ui()->renderer()->render($msgBox)
                );
                $DIC->ui()->mainTemplate()->parseCurrentBlock();
            } else {
                $ilToolbar = $DIC['ilToolbar'];

                $ilToolbar->addButton($this->lng->txt("ass_create_question"), $this->ctrl->getLinkTarget($this, "addQuestion"));

                $ilToolbar->addSeparator();
                $this->populateQuestionBrowserToolbarButtons($ilToolbar, ilTestQuestionBrowserTableGUI::CONTEXT_LIST_VIEW);
            }
        }

        $table_gui = new ilTestQuestionsTableGUI(
            $this,
            'questions',
            $this->object->getRefId()
        );

        $isset = ilSession::get('tst_qst_move_' . $this->object->getTestId()) !== null;
        $table_gui->setPositionInsertCommandsEnabled(
            $isset
            && is_array(ilSession::get('tst_qst_move_' . $this->object->getTestId()))
            && count(ilSession::get('tst_qst_move_' . $this->object->getTestId()))
        );

        $table_gui->setQuestionPositioningEnabled(!$total);
        $table_gui->setQuestionManagingEnabled(!$total);
        $table_gui->setObligatoryQuestionsHandlingEnabled($this->object->areObligationsEnabled());

        $table_gui->setTotalPoints($this->object->getFixedQuestionSetTotalPoints());
        $table_gui->setTotalWorkingTime($this->object->getFixedQuestionSetTotalWorkingTime());

        $table_gui->init();

        $table_gui->setData($this->object->getTestQuestions());

        $this->tpl->setCurrentBlock("adm_content");
        $this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable('QUESTIONBROWSER', $table_gui->getHTML());
        $this->tpl->parseCurrentBlock();
    }

    private function populateQuestionBrowserToolbarButtons(ilToolbarGUI $toolbar, string $context): void
    {
        $this->ctrl->setParameterByClass(
            ilTestQuestionBrowserTableGUI::class,
            ilTestQuestionBrowserTableGUI::CONTEXT_PARAMETER,
            $context
        );
        $this->ctrl->setParameterByClass(
            ilTestQuestionBrowserTableGUI::class,
            ilTestQuestionBrowserTableGUI::MODE_PARAMETER,
            ilTestQuestionBrowserTableGUI::MODE_BROWSE_POOLS
        );

        $toolbar->addButton(
            $this->lng->txt("tst_browse_for_qpl_questions"),
            $this->ctrl->getLinkTargetByClass(
                ilTestQuestionBrowserTableGUI::class,
                ilTestQuestionBrowserTableGUI::CMD_BROWSE_QUESTIONS
            )
        );

        $this->ctrl->setParameterByClass(
            ilTestQuestionBrowserTableGUI::class,
            ilTestQuestionBrowserTableGUI::MODE_PARAMETER,
            ilTestQuestionBrowserTableGUI::MODE_BROWSE_TESTS
        );

        $toolbar->addButton(
            $this->lng->txt("tst_browse_for_tst_questions"),
            $this->ctrl->getLinkTargetByClass(
                ilTestQuestionBrowserTableGUI::class,
                ilTestQuestionBrowserTableGUI::CMD_BROWSE_QUESTIONS
            )
        );
    }

    public function takenObject()
    {
    }

    /**
    * Creates the change history for a test
    *
    * Creates the change history for a test
    *
    * @access	public
    */
    public function historyObject()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_HISTORY);
        $table_gui = new ilTestHistoryTableGUI($this, 'history');
        $table_gui->setTestObject($this->object);
        $log = ilObjAssessmentFolder::_getLog(0, time(), $this->object->getId(), true);
        $table_gui->setData($log);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }

    public function initImportForm(string $new_type): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $n_type = $this->testrequest->raw("new_type");
        $this->ctrl->setParameter($this, "new_type", $n_type);
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("import_tst"));
        $fi = new ilFileInputGUI($this->lng->txt("import_file"), "xmldoc");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $form->addItem($fi);
        $tst = new ilObjTest();
        $questionpools = $tst->getAvailableQuestionpools(true, false, true, true);
        if (count($questionpools)) {
            $options = array("-1" => $this->lng->txt("dont_use_questionpool"));
            foreach ($questionpools as $key => $value) {
                $options[$key] = $value["title"];
            }

            $pool = new ilSelectInputGUI($this->lng->txt("select_questionpool"), "qpl");
            $pool->setInfo($this->lng->txt('select_question_pool_info'));
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
    public function participantsActionObject()
    {
        $command = $_POST["command"];
        if (strlen($command)) {
            $method = $command . "Object";
            if (method_exists($this, $method)) {
                $this->$method();
                return;
            }
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
    public function printobject()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilias = $DIC['ilias'];
        if (!$ilAccess->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }

        $isPdfDeliveryRequest = $DIC->test()->internal()->request()->isset('pdf') &&
            $DIC->test()->internal()->request()->raw('pdf');

        $this->getTabsManager()->getQuestionsSubTabs();
        $template = new ilTemplate("tpl.il_as_tst_print_test_confirm.html", true, true, "Modules/Test");

        if (!$isPdfDeliveryRequest) { // #15243
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
        // prepare generation before contents are processed (for mathjax)
        else {
            ilPDFGeneratorUtils::prepareGenerationRequest("Test", PDF_PRINT_VIEW_QUESTIONS);
        }

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");

        global $DIC;
        $ilUser = $DIC['ilUser'];
        $print_date = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
        $max_points = 0;
        $counter = 1;
        $questionHeaderBlockBuilder = new ilTestQuestionHeaderBlockBuilder($this->lng);
        $questionHeaderBlockBuilder->setHeaderMode($this->object->getTitleOutput());

        if ($isPdfDeliveryRequest) {
            ilWACSignedPath::setTokenMaxLifetimeInSeconds(60);
        }

        foreach ($this->object->questions as $question) {
            $template->setCurrentBlock("question");
            $question_gui = $this->object->createQuestionGUI("", $question);

            if ($isPdfDeliveryRequest) {
                $question_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PRINT_PDF);
            }

            $questionHeaderBlockBuilder->setQuestionTitle($question_gui->object->getTitle());
            $questionHeaderBlockBuilder->setQuestionPoints($question_gui->object->getMaximumPoints());
            $questionHeaderBlockBuilder->setQuestionPosition($counter);
            $template->setVariable("QUESTION_HEADER", $questionHeaderBlockBuilder->getHTML());

            $template->setVariable("TXT_QUESTION_ID", $this->lng->txt('question_id_short'));
            $template->setVariable("QUESTION_ID", $question_gui->object->getId());
            $result_output = $question_gui->getSolutionOutput("", null, false, true, false, $this->object->getShowSolutionFeedback());
            $template->setVariable("SOLUTION_OUTPUT", $result_output);
            $template->parseCurrentBlock("question");
            $counter++;
            $max_points += $question_gui->object->getMaximumPoints();
        }

        $template->setVariable("TITLE", ilLegacyFormElementsUtil::prepareFormOutput($this->object->getTitle()));
        $template->setVariable("PRINT_TEST", ilLegacyFormElementsUtil::prepareFormOutput($this->lng->txt("tst_print")));
        $template->setVariable("TXT_PRINT_DATE", ilLegacyFormElementsUtil::prepareFormOutput($this->lng->txt("date")));
        $used_relative_dates = ilDatePresentation::useRelativeDates();
        $template->setVariable(
            "VALUE_PRINT_DATE",
            ilDatePresentation::formatDate(new ilDateTime($print_date, IL_CAL_UNIX))
        );
        $use = ilDatePresentation::setUseRelativeDates($used_relative_dates);
        $template->setVariable(
            "TXT_MAXIMUM_POINTS",
            ilLegacyFormElementsUtil::prepareFormOutput($this->lng->txt("tst_maximum_points"))
        );
        $template->setVariable("VALUE_MAXIMUM_POINTS", ilLegacyFormElementsUtil::prepareFormOutput($max_points));

        if ($isPdfDeliveryRequest) {
            ilTestPDFGenerator::generatePDF($template->get(), ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD, $this->object->getTitleFilenameCompliant(), PDF_PRINT_VIEW_QUESTIONS);
        } else {
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
    public function reviewobject()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if (!$ilAccess->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }
        $this->getTabsManager()->getQuestionsSubTabs();
        $template = new ilTemplate("tpl.il_as_tst_print_test_confirm.html", true, true, "Modules/Test");

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");

        $isPdfDeliveryRequest = $DIC->test()->internal()->request()->isset('pdf') &&
            $DIC->test()->internal()->request()->raw('pdf');

        $max_points = 0;
        $counter = 1;
        $questionHeaderBlockBuilder = new ilTestQuestionHeaderBlockBuilder($this->lng);
        $questionHeaderBlockBuilder->setHeaderMode($this->object->getTitleOutput());

        if ($isPdfDeliveryRequest) {
            ilWACSignedPath::setTokenMaxLifetimeInSeconds(60);

            // prepare generation before contents are processed (for mathjax)
            ilPDFGeneratorUtils::prepareGenerationRequest("Test", PDF_PRINT_VIEW_QUESTIONS);
        }

        foreach ($this->object->questions as $question) {
            $template->setCurrentBlock("question");
            $question_gui = $this->object->createQuestionGUI("", $question);
            $question_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PREVIEW);

            $questionHeaderBlockBuilder->setQuestionTitle($question_gui->object->getTitle());
            $questionHeaderBlockBuilder->setQuestionPoints($question_gui->object->getMaximumPoints());
            $questionHeaderBlockBuilder->setQuestionPosition($counter);
            $template->setVariable("QUESTION_HEADER", $questionHeaderBlockBuilder->getHTML());

            $template->setVariable("SOLUTION_OUTPUT", $question_gui->getPreview(false));
            $template->parseCurrentBlock("question");
            $counter++;
            $max_points += $question_gui->object->getMaximumPoints();
        }

        $template->setVariable("TITLE", ilLegacyFormElementsUtil::prepareFormOutput($this->object->getTitle()));
        $template->setVariable(
            "PRINT_TEST",
            ilLegacyFormElementsUtil::prepareFormOutput($this->lng->txt("review_view"))
        );
        $template->setVariable("TXT_PRINT_DATE", ilLegacyFormElementsUtil::prepareFormOutput($this->lng->txt("date")));
        $usedRelativeDates = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        $template->setVariable(
            "VALUE_PRINT_DATE",
            ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX))
        );
        ilDatePresentation::setUseRelativeDates($usedRelativeDates);
        $template->setVariable(
            "TXT_MAXIMUM_POINTS",
            ilLegacyFormElementsUtil::prepareFormOutput($this->lng->txt("tst_maximum_points"))
        );
        $template->setVariable("VALUE_MAXIMUM_POINTS", ilLegacyFormElementsUtil::prepareFormOutput($max_points));

        if ($isPdfDeliveryRequest) {
            ilTestPDFGenerator::generatePDF($template->get(), ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD, $this->object->getTitleFilenameCompliant(), PDF_PRINT_VIEW_QUESTIONS);
        } else {
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

    /**
     * Displays the settings page for test defaults
     */
    public function defaultsObject()
    {
        /**
         * @var $ilAccess  ilAccessHandler
         * @var $ilToolbar ilToolbarGUI
         * @var $tpl       ilTemplate
         */
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilToolbar = $DIC['ilToolbar'];
        $tpl = $DIC['tpl'];

        if (!$ilAccess->checkAccess("write", "", $this->ref_id)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }

        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_SETTINGS);

        $ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'addDefaults'));
        $ilToolbar->addFormButton($this->lng->txt('add'), 'addDefaults');
        $ilToolbar->addInputItem(new ilTextInputGUI($this->lng->txt('tst_defaults_defaults_of_test'), 'name'), true);
        $table = new ilTestPersonalDefaultSettingsTableGUI($this, 'defaults');
        $defaults = $this->object->getAvailableDefaults();
        $table->setData($defaults);
        $tpl->setContent($table->getHTML());
    }

    /**
     * Deletes selected test defaults
     */
    public function deleteDefaultsObject()
    {
        if (isset($_POST['chb_defaults']) && is_array($_POST['chb_defaults']) && count($_POST['chb_defaults'])) {
            foreach ($_POST['chb_defaults'] as $test_default_id) {
                $this->object->deleteDefaults($test_default_id);
            }
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
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
        if (!is_array($_POST["chb_defaults"]) || 1 !== count($_POST["chb_defaults"])) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_defaults_apply_select_one"));

            $this->defaultsObject();
        }

        // do not apply if user datasets exist
        if ($this->object->evalTotalPersons() > 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_defaults_apply_not_possible"));

            $this->defaultsObject();
        }

        $defaults = $this->object->getTestDefaults($_POST["chb_defaults"][0]);
        $defaultSettings = unserialize($defaults["defaults"]);

        if (isset($defaultSettings['isRandomTest'])) {
            if ($defaultSettings['isRandomTest']) {
                $newQuestionSetType = ilObjTest::QUESTION_SET_TYPE_RANDOM;
                $this->object->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_RANDOM);
            } else {
                $newQuestionSetType = ilObjTest::QUESTION_SET_TYPE_FIXED;
                $this->object->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_FIXED);
            }
        } elseif (isset($defaultSettings['questionSetType'])) {
            $newQuestionSetType = $defaultSettings['questionSetType'];
        }
        $oldQuestionSetType = $this->object->getQuestionSetType();
        $questionSetTypeSettingSwitched = ($oldQuestionSetType != $newQuestionSetType);

        $oldQuestionSetConfig = $this->testQuestionSetConfigFactory->getQuestionSetConfigByType();

        switch (true) {
            case !$questionSetTypeSettingSwitched:
            case !$oldQuestionSetConfig->doesQuestionSetRelatedDataExist():
            case $confirmed:

                break;

            default:

                $confirmation = new ilTestSettingsChangeConfirmationGUI($this->getTestObject());

                $confirmation->setFormAction($this->ctrl->getFormAction($this));
                $confirmation->setCancel($this->lng->txt('cancel'), 'defaults');
                $confirmation->setConfirm($this->lng->txt('confirm'), 'confirmedApplyDefaults');

                $confirmation->setOldQuestionSetType($this->object->getQuestionSetType());
                $confirmation->setNewQuestionSetType($newQuestionSetType);
                $confirmation->setQuestionLossInfoEnabled(false);
                $confirmation->build();

                $confirmation->populateParametersFromPost();

                $this->tpl->setContent($this->ctrl->getHTML($confirmation));

                return;
        }

        if ($questionSetTypeSettingSwitched && !$this->object->getOfflineStatus()) {
            $this->object->setOfflineStatus(true);

            $info = $this->lng->txt("tst_set_offline_due_to_switched_question_set_type_setting");

            $this->tpl->setOnScreenMessage('info', $info, true);
        }

        $this->object->applyDefaults($defaults);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_defaults_applied"), true);

        if ($questionSetTypeSettingSwitched && $oldQuestionSetConfig->doesQuestionSetRelatedDataExist()) {
            $oldQuestionSetConfig->removeQuestionSetRelatedData();
        }

        $this->ctrl->redirect($this, 'defaults');
    }

    /**
    * Adds the defaults of this test to the defaults
    */
    public function addDefaultsObject()
    {
        if (strlen($_POST["name"]) > 0) {
            $this->object->addDefaults($_POST['name']);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_defaults_enter_name"));
        }
        $this->defaultsObject();
    }

    private function isCommandClassAnyInfoScreenChild(): bool
    {
        if (in_array($this->ctrl->getCmdClass(), self::$infoScreenChildClasses)) {
            return true;
        }

        return false;
    }

    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreenObject()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    public function redirectToInfoScreenObject()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen($this->testrequest->raw('lock'));
    }

    /**
    * show information screen
    */
    public function infoScreen($session_lock = "")
    {
        /* @var ILIAS\DI\Container $DIC */
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];
        $ilToolbar = $DIC['ilToolbar'];

        if (!$ilAccess->checkAccess("visible", "", $this->ref_id) && !$ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->MESSAGE);
        }

        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_INFOSCREEN);

        if ($ilAccess->checkAccess("read", "", $this->testrequest->getRefId())) {
            $this->trackTestObjectReadEvent();
        }
        $info = new ilInfoScreenGUI($this);
        $info->setOpenFormTag(false);

        if ($this->isCommandClassAnyInfoScreenChild()) {
            return $this->ctrl->forwardCommand($info);
        }
        $toolbarFactory = new ilTestInfoScreenToolbarFactory();
        $toolbarFactory->setTestOBJ($this->getTestObject());
        $toolbar = $toolbarFactory->getToolbarInstance();

        $toolbar->setGlobalToolbar($GLOBALS['DIC']['ilToolbar']);
        $toolbar->setCloseFormTag(false);

        $toolbar->setSessionLockString($session_lock);
        $toolbar->build();
        $toolbar->sendMessages();

        if ($this->object->getShowInfo()) {
            $info->enablePrivateNotes();
        }

        if ($this->object->getIntroduction() !== '') {
            $info->addSection($this->lng->txt("tst_introduction"));
            $info->addProperty("", $this->object->prepareTextareaOutput($this->object->getIntroduction(), true) .
                "<br />" . $info->getHiddenToggleButton());
        } else {
            $info->addSection($this->lng->txt("show_details"));
            $info->addProperty("", $info->getHiddenToggleButton());
        }

        $info->addSection($this->lng->txt("tst_general_properties"));
        if ($this->object->getShowInfo()) {
            $info->addProperty($this->lng->txt("author"), $this->object->getAuthor());
            $info->addProperty($this->lng->txt("title"), $this->object->getTitle());
        }
        if (!$this->object->getOfflineStatus() &&
            $this->object->isComplete($this->testQuestionSetConfigFactory->getQuestionSetConfig()) &&
            $ilAccess->checkAccess("read", "", $this->ref_id) &&
            !$this->object->isRandomTest() &&
            !$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired() &&
            $this->object->getNrOfTries() != 1) {
            if ($this->object->getUsePreviousAnswers() == 0) {
                if ($this->object->getShowInfo()) {
                    $info->addProperty($this->lng->txt("tst_use_previous_answers"), $this->lng->txt("tst_dont_use_previous_answers"));
                }
            } else {
                $checked_previous_answers = false;
                if ($ilUser->getPref("tst_use_previous_answers")) {
                    $checked_previous_answers = true;
                }
                $info->addPropertyCheckbox($this->lng->txt("tst_use_previous_answers"), "chb_use_previous_answers", 1, $this->lng->txt("tst_use_previous_answers_user"), $checked_previous_answers);
            }
        }

        $info->hideFurtherSections(false);

        if ($this->object->getShowInfo()) {
            $info->addSection($this->lng->txt("tst_sequence_properties"));
            $info->addProperty($this->lng->txt("tst_sequence"), $this->lng->txt(($this->object->getSequenceSettings() == TEST_FIXED_SEQUENCE) ? "tst_sequence_fixed" : "tst_sequence_postpone"));

            $info->addSection($this->lng->txt("tst_heading_scoring"));
            $info->addProperty($this->lng->txt("tst_text_count_system"), $this->lng->txt(($this->object->getCountSystem() == COUNT_PARTIAL_SOLUTIONS) ? "tst_count_partial_solutions" : "tst_count_correct_solutions"));
            if ($this->object->isRandomTest()) {
                $info->addProperty($this->lng->txt("tst_pass_scoring"), $this->lng->txt(($this->object->getPassScoring() == SCORE_BEST_PASS) ? "tst_pass_best_pass" : "tst_pass_last_pass"));
            }

            $info->addSection($this->lng->txt("tst_score_reporting"));
            $score_reporting_text = "";
            switch ($this->object->getScoreReporting()) {
                case ilObjTest::SCORE_REPORTING_FINISHED:
                    $score_reporting_text = $this->lng->txt("tst_report_after_test");
                    break;
                case ilObjTest::SCORE_REPORTING_IMMIDIATLY:
                    $score_reporting_text = $this->lng->txt("tst_report_after_first_question");
                    break;
                case ilObjTest::SCORE_REPORTING_DATE:
                    $score_reporting_text = $this->lng->txt("tst_report_after_date");
                    break;
                case ilObjTest::SCORE_REPORTING_AFTER_PASSED:
                    $score_reporting_text = $this->lng->txt("tst_report_after_passed");
                    break;
                default:
                    $score_reporting_text = $this->lng->txt("tst_report_never");
                    break;
            }
            $info->addProperty($this->lng->txt("tst_score_reporting"), $score_reporting_text);
            $reporting_date = $this->object->getReportingDate();
            if ($reporting_date) {
                #preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $reporting_date, $matches);
                #$txt_reporting_date = date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]));
                #$info->addProperty($this->lng->txt("tst_score_reporting_date"), $txt_reporting_date);
                $info->addProperty(
                    $this->lng->txt('tst_score_reporting_date'),
                    ilDatePresentation::formatDate(new ilDateTime($reporting_date, IL_CAL_TIMESTAMP))
                );
            }

            $info->addSection($this->lng->txt("tst_session_settings"));
            $info->addProperty($this->lng->txt("tst_nr_of_tries"), ($this->object->getNrOfTries() == 0) ? $this->lng->txt("unlimited") : $this->object->getNrOfTries());
            if ($this->object->getNrOfTries() != 1) {
                $info->addProperty($this->lng->txt("tst_nr_of_tries_of_user"), ($toolbar->getTestSession()->getPass() == false) ? $this->lng->txt("tst_no_tries") : $toolbar->getTestSequence()->getPass());
            }

            if ($this->object->getEnableProcessingTime()) {
                $info->addProperty($this->lng->txt("tst_processing_time"), $this->object->getProcessingTime());
            }
            if (strlen($this->object->getAllowedUsers()) && ($this->object->getAllowedUsersTimeGap())) {
                $info->addProperty($this->lng->txt("tst_allowed_users"), $this->object->getAllowedUsers());
            }

            $starting_time = $this->object->getStartingTime();
            if ($this->object->isStartingTimeEnabled() && $starting_time != 0) {
                $info->addProperty($this->lng->txt("tst_starting_time"), ilDatePresentation::formatDate(new ilDateTime($starting_time, IL_CAL_UNIX)));
            }
            $ending_time = $this->object->getEndingTime();
            if ($this->object->isEndingTimeEnabled() && $ending_time != 0) {
                $info->addProperty($this->lng->txt("tst_ending_time"), ilDatePresentation::formatDate(new ilDateTime($ending_time, IL_CAL_UNIX)));
            }
            $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
            // forward the command
        }

        $this->ctrl->forwardCommand($info);
        return null;
    }

    protected function removeImportFailsObject()
    {
        $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->object->getId());
        $qsaImportFails->deleteRegisteredImportFails();
        $sltImportFails = new ilTestSkillLevelThresholdImportFails($this->object->getId());
        $sltImportFails->deleteRegisteredImportFails();

        $this->ctrl->redirect($this, 'infoScreen');
    }

    public function addLocatorItems(): void
    {
        global $DIC;
        $ilLocator = $DIC['ilLocator'];
        switch ($this->ctrl->getCmd()) {
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
                $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $this->testrequest->getRefId());
                break;
            case "eval_stat":
            case "evalAllUsers":
            case "evalUserDetail":
                $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "eval_stat"), "", $this->testrequest->getRefId());
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
                $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->testrequest->getRefId());
                break;
        }
    }

    public function statisticsObject()
    {
    }

    /**
    * Shows the certificate editor
    */
    public function certificateObject()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_SETTINGS);

        $guiFactory = new ilCertificateGUIFactory();
        $output_gui = $guiFactory->create($this->object);

        $output_gui->certificateEditor();
    }

    /**
    * adds tabs to tab gui object
    *
    * @param ilTabsGUI $tabs_gui
    */
    public function getTabs(): void
    {
        global $DIC;
        $help = $DIC['ilHelp'];
        $help->setScreenIdComponent("tst");

        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $courseLink = ilLink::_getLink($this->getObjectiveOrientedContainer()->getRefId());
            $this->getTabsManager()->setParentBackLabel($this->lng->txt('back_to_objective_container'));
            $this->getTabsManager()->setParentBackHref($courseLink);
        }

        $this->getTabsManager()->perform();
    }

    public static function accessViolationRedirect()
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate(); /* @var ILIAS\DI\Container $DIC */

        $main_tpl->setOnScreenMessage('info', $DIC->language()->txt("no_permission"), true);
        $DIC->ctrl()->redirectByClass('ilObjTestGUI', "infoScreen");
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
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];

        if ($ilAccess->checkAccess("read", "", $a_target) || $ilAccess->checkAccess("visible", "", $a_target)) {
            $DIC->ctrl()->setParameterByClass('ilObjTestGUI', 'ref_id', $a_target);
            $DIC->ctrl()->redirectByClass('ilObjTestGUI', 'infoScreen');
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('info', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
    }

    public function buildPageViewToolbar($qid = 0)
    {
        if ($this->create_question_mode) {
            return;
        }

        global $DIC;
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $ilCtrl->saveParameter($this, 'q_mode');

        $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'test_express_mode', 1);
        $ilCtrl->setParameter($this, 'test_express_mode', 1);
        $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'q_id', $this->testrequest->raw('q_id'));
        $ilCtrl->setParameter($this, 'q_id', $this->testrequest->raw('q_id'));
        $ilToolbar->setFormAction($ilCtrl->getFormActionByClass('iltestexpresspageobjectgui', 'edit'));

        if ($this->object->evalTotalPersons() == 0) {
            $ilToolbar->addFormButton($lng->txt("ass_create_question"), "addQuestion");

            $ilToolbar->addSeparator();

            $this->populateQuestionBrowserToolbarButtons($ilToolbar, ilTestQuestionBrowserTableGUI::CONTEXT_PAGE_VIEW);

            $show_separator = true;
        }

        $questions = $this->object->getQuestionTitlesAndIndexes();

        // desc
        $options = array();
        foreach ($questions as $id => $label) {
            $options[$id] = $label . ' [' . $this->lng->txt('question_id_short') . ': ' . $id . ']';
        }

        $optionKeys = array_keys($options);

        if (!$options) {
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

        if (count($questions)) {
            if (isset($show_separator) && $show_separator) {
                $ilToolbar->addSeparator();
            }

            $btn = ilLinkButton::getInstance();
            $btn->setCaption("test_prev_question");
            $btn->setUrl($ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'prevQuestion'));
            $ilToolbar->addButtonInstance($btn);

            if (count($options) <= 1 || $optionKeys[0] == $qid) {
                $btn->setDisabled(true);
            }

            $btn = ilLinkButton::getInstance();
            $btn->setCaption("test_next_question");
            $btn->setUrl($ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'nextQuestion'));
            $ilToolbar->addButtonInstance($btn);

            if (count($options) <= 1 || $optionKeys[count($optionKeys) - 1] == $qid) {
                $btn->setDisabled(true);
            }
        }

        if (count($questions) > 1) {
            $ilToolbar->addSeparator();
            $si = new ilSelectInputGUI($lng->txt("test_jump_to"), "q_id");
            $si->addCustomAttribute("onChange=\"forms['ilToolbar'].submit();\"");
            $si->setOptions($options);

            if ($qid) {
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

        if (count($questions) && !$total) {
            $ilCtrl->setParameter($this, 'q_id', $this->testrequest->raw('q_id'));
            $ilToolbar->addSeparator();
            $ilToolbar->addButton($lng->txt("test_delete_page"), $ilCtrl->getLinkTarget($this, "removeQuestions"));
        }

        if (count($questions) > 1 && !$total) {
            $ilToolbar->addSeparator();
            $ilToolbar->addButton($lng->txt("test_move_page"), $ilCtrl->getLinkTarget($this, "movePageForm"));
        }

        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];

        $online_access = false;
        if ($this->object->getFixedParticipants()) {
            $online_access_result = ilObjTestAccess::_lookupOnlineTestAccess($this->object->getId(), $ilUser->getId());
            if ($online_access_result === true) {
                $online_access = true;
            }
        }

        if (!$this->object->getOfflineStatus() && $this->object->isComplete($this->testQuestionSetConfigFactory->getQuestionSetConfig())) {
            if ((!$this->object->getFixedParticipants() || $online_access) && $ilAccess->checkAccess("read", "", $this->ref_id)) {
                $testSession = $this->testSessionFactory->getSession();

                $executable = $this->object->isExecutable($testSession, $ilUser->getId(), $allowPassIncrease = true);

                if ($executable["executable"]) {
                    $player_factory = new ilTestPlayerFactory($this->getTestObject());
                    $player_instance = $player_factory->getPlayerGUI();

                    $ilToolbar->addSeparator();
                    if ($testSession->getActiveId() > 0) {
                        $ilToolbar->addButton($lng->txt('tst_resume_test'), $ilCtrl->getLinkTarget($player_instance, 'resumePlayer'));
                    } else {
                        $ilToolbar->addButton($lng->txt('tst_start_test'), $ilCtrl->getLinkTarget($player_instance, 'startTest'));
                    }
                }
            }
        }
    }

    public function copyQuestionsToPoolObject()
    {
        $this->copyQuestionsToPool($this->testrequest->raw('q_id'), $this->testrequest->raw('sel_qpl'));
        $this->ctrl->redirect($this, 'questions');
    }

    public function copyQuestionsToPool($questionIds, $qplId): stdClass
    {
        $newIds = array();
        foreach ($questionIds as $q_id) {
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
        $question_gui = assQuestion::instantiateQuestionGUI($sourceQuestionId);

        $newtitle = $question_gui->object->getTitle();
        if ($question_gui->object->questionTitleExists($targetParentId, $question_gui->object->getTitle())) {
            $counter = 2;
            while ($question_gui->object->questionTitleExists($targetParentId, $question_gui->object->getTitle() . " ($counter)")) {
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
        global $DIC;
        $ilObjDataCache = $DIC['ilObjDataCache'];

        if (!(int) $this->testrequest->raw('sel_qpl')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("questionpool_not_selected"));
            $this->copyAndLinkToQuestionpoolObject();
            return;
        }

        $qplId = $ilObjDataCache->lookupObjId((int) $this->testrequest->raw('sel_qpl'));
        $result = $this->copyQuestionsToPool($this->testrequest->raw('q_id'), $qplId);

        foreach ($result->ids as $oldId => $newId) {
            $questionInstance = assQuestion::instantiateQuestion($oldId);

            if (assQuestion::originalQuestionExists($questionInstance->getOriginalId())) {
                $oldOriginal = assQuestion::instantiateQuestion($questionInstance->getOriginalId());
                $oldOriginal->delete($oldOriginal->getId());
            }

            $questionInstance->setNewOriginalId($newId);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_qst_added_to_pool_' . (count($result->ids) > 1 ? 'p' : 's')), true);
        $this->ctrl->redirect($this, 'questions');
    }

    private function getQuestionpoolCreationForm(): ilPropertyFormGUI
    {
        global $DIC;
        $lng = $DIC['lng'];
        $form = new ilPropertyFormGUI();

        $title = new ilTextInputGUI($lng->txt('title'), 'title');
        $title->setRequired(true);
        $form->addItem($title);

        $description = new ilTextAreaInputGUI($lng->txt('description'), 'description');
        $form->addItem($description);

        $form->addCommandButton('createQuestionPoolAndCopy', $lng->txt('create'));

        if ($this->testrequest->isset('q_id') && is_array($this->testrequest->raw('q_id'))) {
            foreach ($this->testrequest->raw('q_id') as $id) {
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
        global $DIC;
        $lng = $DIC['lng'];

        // #13761; All methods use for this request should be revised, thx japo ;-)
        if (
            'copyAndLinkToQuestionpool' == $this->ctrl->getCmd() &&
            (!$this->testrequest->isset('q_id') || !is_array($this->testrequest->raw('q_id')))
        ) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_no_question_selected_for_moving_to_qpl'), true);
            $this->ctrl->redirect($this, 'questions');
        }

        if ($this->testrequest->isset('q_id') && is_array($this->testrequest->raw('q_id'))) {
            foreach ($this->testrequest->raw('q_id') as $q_id) {
                if (!assQuestion::originalQuestionExists($q_id)) {
                    continue;
                }

                $type = ilObject::_lookupType(assQuestion::lookupParentObjId(assQuestion::_getOriginalId($q_id)));

                if ($type !== 'tst') {
                    $this->tpl->setOnScreenMessage('failure', $lng->txt('tst_link_only_unassigned'), true);
                    $this->ctrl->redirect($this, 'questions');
                    return;
                }
            }
        }

        $this->createQuestionpoolTargetObject('copyAndLinkQuestionsToPool');
    }

    public function createQuestionPoolAndCopyObject()
    {
        if ($this->testrequest->raw('title')) {
            $title = $this->testrequest->raw('title');
        } else {
            $title = $this->testrequest->raw('txt_qpl');
        }

        if (!$title) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("questionpool_not_entered"));
            $this->copyAndLinkToQuestionpoolObject();
            return;
        }

        $ref_id = $this->createQuestionPool($title, $this->testrequest->raw('description'));
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
    public function createQuestionpoolTargetObject($cmd)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->getTabsManager()->getQuestionsSubTabs();
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);

        $questionpools = $this->object->getAvailableQuestionpools(
            false,
            false,
            false,
            true,
            false,
            "write"
        );

        if (count($questionpools) == 0) {
            $form = $this->getTargetQuestionpoolForm($questionpools, 'createQuestionPoolAndCopy');
        } else {
            $form = $this->getTargetQuestionpoolForm($questionpools, $cmd);

            switch ($cmd) {
                case 'copyQuestionsToPool':
                    break;

                case 'copyAndLinkQuestionsToPool':
                    $hidden = new ilHiddenInputGUI('link');
                    $hidden->setValue(1);
                    $form->addItem($hidden);
                    break;
            }
        }

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    protected function getTargetQuestionpoolForm($questionpools, $cmd): ilPropertyFormGUI
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = new ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormAction($this));
        $form->addCommandButton($cmd, $DIC->language()->txt('submit'));
        $form->addCommandButton('cancelCreateQuestion', $DIC->language()->txt('cancel'));

        if (count($questionpools) == 0) {
            $form->setTitle($this->lng->txt("tst_enter_questionpool"));

            $title = new ilTextInputGUI($DIC->language()->txt('title'), 'title');
            $title->setRequired(true);
            $form->addItem($title);

            $description = new ilTextAreaInputGUI($DIC->language()->txt('description'), 'description');
            $form->addItem($description);
        } else {
            $form->setTitle($this->lng->txt("tst_select_questionpool"));

            $selectOptions = [
                '' => $DIC->language()->txt('please_select')
            ];

            foreach ($questionpools as $key => $value) {
                $selectOptions[$key] = $value["title"];
            }

            $select = new ilSelectInputGUI($DIC->language()->txt('tst_source_question_pool'), 'sel_qpl');
            $select->setRequired(true);
            $select->setOptions($selectOptions);

            $form->addItem($select);
        }

        if ($this->testrequest->isset('q_id') && is_array($this->testrequest->raw('q_id'))) {
            foreach ($this->testrequest->raw('q_id') as $id) {
                $hidden = new ilHiddenInputGUI('q_id[]');
                $hidden->setValue($id);
                $form->addItem($hidden);
            }
        }

        return $form;
    }

    // begin-patch lok
    public function applyTemplate($templateData, ilObjTest $object)
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
            'instant_feedback_contents' => 'setInstantFeedbackOptionsByArray',
            'instant_feedback_trigger' => 'setForceInstantFeedbackEnabled',
            'answer_fixation_handling' => null, // handled specially in loop below
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

        if (isset($templateData['results_presentation']['value'])) {
            $templateData['results_presentation']['value'] = array();
        }

        foreach ($simpleSetters as $field => $setter) {
            if ($templateData[$field] && strlen($setter)) {
                $object->$setter($templateData[$field]['value']);
                continue;
            }

            switch ($field) {
                case 'autosave':
                    if ($templateData[$field]['value'] > 0) {
                        $object->setAutosave(true);
                        $object->setAutosaveIval($templateData[$field]['value'] * 1000);
                    } else {
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
                    if (strlen($templateData[$field]['value'])) {
                        $object->setRedirectionMode(REDIRECT_ALWAYS);
                        $object->setRedirectionUrl($templateData[$field]['value']);
                    } else {
                        $object->setRedirectionMode(REDIRECT_NONE);
                        $object->setRedirectionUrl('');
                    }
                    break;

                case 'answer_fixation_handling':
                    switch ($templateData[$field]['value']) {
                        case ilObjTestSettingsGeneralGUI::ANSWER_FIXATION_NONE:
                            $object->setInstantFeedbackAnswerFixationEnabled(false);
                            $object->setFollowupQuestionAnswerFixationEnabled(false);
                            break;

                        case ilObjTestSettingsGeneralGUI::ANSWER_FIXATION_ON_INSTANT_FEEDBACK:
                            $object->setInstantFeedbackAnswerFixationEnabled(true);
                            $object->setFollowupQuestionAnswerFixationEnabled(false);
                            break;

                        case ilObjTestSettingsGeneralGUI::ANSWER_FIXATION_ON_FOLLOWUP_QUESTION:
                            $object->setInstantFeedbackAnswerFixationEnabled(false);
                            $object->setFollowupQuestionAnswerFixationEnabled(true);
                            break;

                        case ilObjTestSettingsGeneralGUI::ANSWER_FIXATION_ON_IFB_OR_FUQST:
                            $object->setInstantFeedbackAnswerFixationEnabled(true);
                            $object->setFollowupQuestionAnswerFixationEnabled(true);
                            break;
                    }
                    break;
            }
        }
    }

    public function saveOrderAndObligationsObject()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        if (!$ilAccess->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $orders = $obligations = array();

        foreach ((array) $this->testrequest->raw('order') as $qId => $order) {
            $id = (int) str_replace('q_', '', $qId);

            $orders[$id] = $order;
        }

        if ($this->object->areObligationsEnabled() && $this->testrequest->isset('obligatory') && is_array($this->testrequest->raw('obligatory'))) {
            foreach ($this->testrequest->raw('obligatory') as $qId => $obligation) {
                $id = (int) str_replace('q_', '', $qId);

                if (ilObjTest::isQuestionObligationPossible($id)) {
                    $obligations[$id] = $obligation;
                }
            }
        }

        $this->object->setQuestionOrderAndObligations(
            $orders,
            $obligations
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $ilCtrl->redirect($this, 'questions');
    }

    /**
     * Move current page
     */
    protected function movePageFormObject()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "movePage"));
        $form->setTitle($lng->txt("test_move_page"));

        $old_pos = new ilHiddenInputGUI("q_id");
        $old_pos->setValue($this->testrequest->raw('q_id'));
        $form->addItem($old_pos);

        $questions = $this->object->getQuestionTitlesAndIndexes();
        if (!is_array($questions)) {
            $questions = array();
        }

        foreach ($questions as $k => $q) {
            if ($k == $this->testrequest->raw('q_id')) {
                unset($questions[$k]);
                continue;
            }
            $questions[$k] = $lng->txt('behind') . ' ' . $q;
        }
        #$questions['0'] = $lng->txt('first');

        $options = array(
            0 => $lng->txt('first')
        );
        foreach ($questions as $k => $q) {
            $options[$k] = $q . ' [' . $this->lng->txt('question_id_short') . ': ' . $k . ']';
        }

        $pos = new ilSelectInputGUI($lng->txt("position"), "position_after");
        $pos->setOptions($options);
        $form->addItem($pos);

        $form->addCommandButton("movePage", $lng->txt("submit"));
        $form->addCommandButton("showPage", $lng->txt("cancel"));

        return $tpl->setContent($form->getHTML());
    }

    public function movePageObject()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        if (!$ilAccess->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }

        $this->object->moveQuestionAfter($this->testrequest->raw('q_id'), $this->testrequest->raw('position_after'));
        $this->showPageObject();
    }

    public function showPageObject()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'q_id', $this->testrequest->raw('q_id'));
        $ilCtrl->redirectByClass('iltestexpresspageobjectgui', 'showPage');
    }

    public function copyQuestionObject()
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        if (!$ilAccess->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }

        if ($this->testrequest->raw('q_id') && !is_array($this->testrequest->raw('q_id'))) {
            $ids = array($this->testrequest->raw('q_id'));
        } elseif ($this->testrequest->raw('q_id')) {
            $ids = $this->testrequest->raw('q_id');
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('copy_no_questions_selected'), true);
            $this->ctrl->redirect($this, 'questions');
        }

        $copy_count = 0;

        $questionTitles = $this->object->getQuestionTitles();

        foreach ($ids as $id) {
            $question = assQuestion::instantiateQuestionGUI($id);
            if ($question) {
                $title = $question->object->getTitle();
                $i = 2;
                while (in_array($title . ' (' . $i . ')', $questionTitles)) {
                    $i++;
                }

                $title .= ' (' . $i . ')';

                $questionTitles[] = $title;

                $new_id = $question->object->duplicate(false, $title);

                $clone = assQuestion::instantiateQuestionGUI($new_id);
                $clone->object->setObjId($this->object->getId());
                $clone->object->saveToDb();

                $this->object->insertQuestion($this->testQuestionSetConfigFactory->getQuestionSetConfig(), $new_id, true);

                $copy_count++;
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('copy_questions_success'), true);

        $this->ctrl->redirect($this, 'questions');
    }

    protected function determineObjectiveOrientedContainer()
    {
        if (!ilLOSettings::isObjectiveTest($this->ref_id)) {
            return;
        }

        $path = $this->tree->getPathFull($this->ref_id);

        while ($parent = array_pop($path)) {
            if ($parent['type'] === 'crs') {
                $container_ref_id = $parent['ref_id'];
                break;
            }
        }

        $container_obj_id = ilObject2::_lookupObjId($container_ref_id);

        $this->objectiveOrientedContainer->setObjId($container_obj_id);
        $this->objectiveOrientedContainer->setRefId($container_ref_id);
    }

    protected function getObjectiveOrientedContainer(): ilTestObjectiveOrientedContainer
    {
        return $this->objectiveOrientedContainer;
    }
}
