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

declare(strict_types=1);

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\TestQuestionPool\QuestionInfoService;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\DI\LoggingServices;
use ILIAS\Skill\Service\SkillService;
use ILIAS\Test\InternalRequestService;
use ILIAS\GlobalScreen\Services as GlobalScreen;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\TestQuestionPool\Import\TestQuestionsImportTrait;

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
 * @ilCtrl_Calls ilObjTestGUI: ilTestPlayerFixedQuestionSetGUI, ilTestPlayerRandomQuestionSetGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestExpresspageObjectGUI, ilAssQuestionPageGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestDashboardGUI, ilTestResultsGUI
 * @ilCtrl_Calls ilObjTestGUI: ilLearningProgressGUI, ilMarkSchemaGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestEvaluationGUI, ilParticipantsTestResultsGUI
 * @ilCtrl_Calls ilObjTestGUI: ilAssGenFeedbackPageGUI, ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilObjTestGUI: ilInfoScreenGUI, ilObjectCopyGUI, ilTestScoringGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestScreenGUI
 * @ilCtrl_Calls ilObjTestGUI: ilRepositorySearchGUI, ilTestExportGUI
 * @ilCtrl_Calls ilObjTestGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: assOrderingQuestionGUI, assImagemapQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: assNumericGUI, assErrorTextGUI, ilTestScoringByQuestionsGUI
 * @ilCtrl_Calls ilObjTestGUI: assTextSubsetGUI, assOrderingHorizontalGUI
 * @ilCtrl_Calls ilObjTestGUI: assSingleChoiceGUI, assFileUploadGUI, assTextQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: assKprimChoiceGUI, assLongMenuGUI
 * @ilCtrl_Calls ilObjTestGUI: ilObjQuestionPoolGUI, ilEditClipboardGUI
 * @ilCtrl_Calls ilObjTestGUI: ilObjTestSettingsMainGUI, ilObjTestSettingsScoringResultsGUI
 * @ilCtrl_Calls ilObjTestGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestFixedQuestionSetConfigGUI, ilTestRandomQuestionSetConfigGUI
 * @ilCtrl_Calls ilObjTestGUI: ilAssQuestionHintsGUI, ilAssQuestionFeedbackEditingGUI, ilLocalUnitConfigurationGUI, assFormulaQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestPassDetailsOverviewTableGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestResultsToolbarGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestCorrectionsGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestSettingsChangeConfirmationGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestSkillAdministrationGUI
 * @ilCtrl_Calls ilObjTestGUI: ilAssQuestionPreviewGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestQuestionBrowserTableGUI, ilTestInfoScreenToolbarGUI, ilLTIProviderObjectSettingGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestPageGUI
 *
 * @ingroup components\ILIASTest
 */
class ilObjTestGUI extends ilObjectGUI implements ilCtrlBaseClassInterface, ilDesktopItemHandling
{
    use TestQuestionsImportTrait;
    use ILIAS\Modules\Test\QuestionPoolLinkedTitleBuilder;
    private static $infoScreenChildClasses = [
        'ilpublicuserprofilegui', 'ilobjportfoliogui'
    ];

    private ilTestQuestionSetConfigFactory $test_question_set_config_factory;
    private ilTestPlayerFactory $test_player_factory;
    private ilTestSessionFactory $test_session_factory;
    private QuestionInfoService $questioninfo;
    protected ?ilTestTabsManager $tabs_manager = null;
    private ilTestObjectiveOrientedContainer $objective_oriented_container;
    protected ilTestAccess $test_access;
    protected ilNavigationHistory $navigation_history;
    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;
    protected ilDBInterface $db;
    protected LoggingServices $logging_services;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected HTTPServices $http;
    protected ilHelpGUI $help;
    protected GlobalScreen $global_screen;
    protected ilObjectDataCache $obj_data_cache;
    protected SkillService $skills_service;
    private Archives $archives;
    protected InternalRequestService $testrequest;
    protected ilTestQuestionsTableGUI $table;

    protected bool $create_question_mode;

    /**
     * Constructor
     * @access public
     * @param mixed|null $refId
     */
    public function __construct($refId = null)
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->navigation_history = $DIC['ilNavigationHistory'];
        $this->component_repository = $DIC['component.repository'];
        $this->component_factory = $DIC['component.factory'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->http = $DIC['http'];
        $this->error = $DIC['ilErr'];
        $this->db = $DIC['ilDB'];
        $this->logging_services = $DIC->logger();
        $this->help = $DIC['ilHelp'];
        $this->global_screen = $DIC['global_screen'];
        $this->obj_data_cache = $DIC['ilObjDataCache'];
        $this->skills_service = $DIC->skills();
        $this->questioninfo = $DIC->testQuestionPool()->questionInfo();
        $this->type = 'tst';
        $this->archives = $DIC->archives();
        $this->testrequest = $DIC->test()->internal()->request();

        $ref_id = 0;
        if ($this->testrequest->hasRefId() && is_numeric($this->testrequest->getRefId())) {
            $ref_id = $this->testrequest->getRefId();
        }
        parent::__construct("", $ref_id, true, false);

        $this->ctrl->saveParameter($this, ['ref_id', 'test_ref_id', 'calling_test', 'test_express_mode', 'q_id']);
        $this->lng->loadLanguageModule('assessment');

        if ($this->object instanceof ilObjTest) {
            /**
            * 2023-08-08, sk: We check this here to avoid a crash of
            * Dynamic-Tests when the migration was not run. The check can go with ILIAS 10.
            * @todo: Remove check with ILIAS 10
            */
            if (!$this->object->isFixedTest() && !$this->object->isRandomTest()) {
                $this->tpl->setOnScreenMessage('failure', sprintf(
                    'You tried to access a Dynamic Test. This is not possible anymore with ILIAS 9. '
                     . 'Please tell your administrator to run the corresponding migration to remove this Test completely.',
                    $this->object->getTitle()
                ), true);
                $this->ctrl->setParameterByClass('ilrepositorygui', 'ref_id', ROOT_FOLDER_ID);
                $this->ctrl->redirectByClass('ilrepositorygui');
            }

            $this->test_question_set_config_factory = new ilTestQuestionSetConfigFactory(
                $this->tree,
                $this->db,
                $this->lng,
                $this->logging_services->root(),
                $this->component_repository,
                $this->object,
                $this->questioninfo
            );

            $this->test_player_factory = new ilTestPlayerFactory($this->object);
            $this->test_session_factory = new ilTestSessionFactory($this->object, $this->db, $this->user);
            $this->setTestAccess(new ilTestAccess($this->ref_id));
        } else {
            $this->setCreationMode(true); // I think?
        }
        $this->objective_oriented_container = new ilTestObjectiveOrientedContainer();

        if ($this->object instanceof ilObjTest) {
            $tabs_manager = new ilTestTabsManager(
                $this->tabs_gui,
                $this->lng,
                $this->ctrl,
                $this->request_wrapper,
                $this->refinery,
                $this->access,
                $this->test_access,
                $this->objective_oriented_container
            );
            $tabs_manager->setTestOBJ($this->object);
            $tabs_manager->setTestSession($this->test_session_factory->getSession());
            $tabs_manager->setTestQuestionSetConfig($this->test_question_set_config_factory->getQuestionSetConfig());
            $this->setTabsManager($tabs_manager);
        }

        $this->table = new ilTestQuestionsTableGUI(
            $this->ui_factory,
            new \ILIAS\Data\Factory(),
            $this->refinery,
            $this->http,
            $this->lng,
            $this->object ? (string) $this->object->getId() : '',
            $this->getQuestionPoolLinkBuilder()
        );
    }

    /**
    * execute command
    */
    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd('testScreen');

        if($table_cmd = $this->table->getTableCommand()) {

            $row_ids = $this->table->getRowIds();
            if($row_ids === ['ALL_OBJECTS']) {
                $row_ids = array_map(
                    fn($record) => $record['question_id'],
                    $this->object->getTestQuestions()
                );
            }

            switch($table_cmd) {
                case ilTestQuestionsTableGUI::ACTION_SAVE_ORDER:
                    $this->protectByWritePermission();
                    $data = $this->table->getOrderData();
                    $this->storeOrder(array_flip($data));
                    break;

                case ilTestQuestionsTableGUI::ACTION_PREVIEW:
                    $this->redirectWithQuestionParameters(
                        current($row_ids),
                        ilAssQuestionPreviewGUI::class,
                        ilAssQuestionPreviewGUI::CMD_SHOW
                    );
                    break;

                case ilTestQuestionsTableGUI::ACTION_CORRECTION:
                    $this->ctrl->setParameter($this, 'qid', (int) current($row_ids));
                    $this->redirectWithQuestionParameters(
                        current($row_ids),
                        ilTestCorrectionsGUI::class,
                        'showQuestion'
                    );
                    break;

                case ilTestQuestionsTableGUI::ACTION_STATISTICS:
                    $this->redirectWithQuestionParameters(
                        current($row_ids),
                        ilAssQuestionPreviewGUI::class,
                        ilAssQuestionPreviewGUI::CMD_STATISTICS
                    );
                    break;

                case ilTestQuestionsTableGUI::ACTION_EDIT_QUESTION:
                    $question_id = current($row_ids);
                    $qtype = $this->object->getQuestionType($question_id);
                    $target_class = $qtype . 'GUI';
                    $this->redirectWithQuestionParameters(
                        $question_id,
                        $target_class,
                        'editQuestion'
                    );
                    break;

                case ilTestQuestionsTableGUI::ACTION_EDIT_PAGE:
                    $this->redirectWithQuestionParameters(
                        current($row_ids),
                        ilAssQuestionPageGUI::class,
                        'edit'
                    );
                    break;

                case ilTestQuestionsTableGUI::ACTION_FEEDBACK:
                    $this->redirectWithQuestionParameters(
                        current($row_ids),
                        ilAssQuestionFeedbackEditingGUI::class,
                        ilAssQuestionFeedbackEditingGUI::CMD_SHOW
                    );
                    break;

                case ilTestQuestionsTableGUI::ACTION_HINTS:
                    $this->redirectWithQuestionParameters(
                        current($row_ids),
                        ilAssQuestionHintsGUI::class,
                        ilAssQuestionHintsGUI::CMD_SHOW_LIST
                    );
                    break;

                case ilTestQuestionsTableGUI::ACTION_DELETE:
                    print $this->getDeleteConfirmation(array_filter($row_ids));
                    exit();

                case ilTestQuestionsTableGUI::ACTION_DELETE_CONFIRMED:
                    $row_ids = $this->testrequest->getParsedBody()['interruptive_items'] ?? [];
                    if(array_filter($row_ids) == []) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_selection"), true);
                        $cmd = 'questions';
                        break;
                    }
                    $question_ids = $this->testrequest->getParsedBody()['interruptive_items'];
                    $this->confirmRemoveQuestionsObject($question_ids);
                    break;

                case ilTestQuestionsTableGUI::ACTION_COPY:
                    if(array_filter($row_ids) == []) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_selection"), true);
                        $cmd = 'questions';
                        break;
                    }
                    $this->protectByWritePermission();
                    $this->copyQuestions($row_ids);
                    break;

                case ilTestQuestionsTableGUI::ACTION_ADD_TO_POOL:
                    if(array_filter($row_ids) == []) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_selection"), true);
                        $cmd = 'questions';
                        break;
                    }
                    $this->copyAndLinkToQuestionpool($row_ids);
                    $cmd = null;
                    break;

                default:
                    throw new InvalidArgumentException("No such table_cmd: '$table_cmd'.");
            }
        }

        $cmds_disabled_due_to_offline_status = [
            'resumePlayer', 'resumePlayer', 'outUserResultsOverview', 'outUserListOfAnswerPasses'
        ];

        if (!$this->getCreationMode() && $this->object->getOfflineStatus() && in_array($cmd, $cmds_disabled_due_to_offline_status)) {
            $cmd = 'infoScreen';
        }

        $next_class = $this->ctrl->getNextClass($this);

        // add entry to navigation history
        if (!$this->getCreationMode() &&
            $this->access->checkAccess('read', '', $this->testrequest->getRefId())
        ) {
            $this->navigation_history->addItem(
                $this->testrequest->getRefId(),
                ilLink::_getLink($this->testrequest->getRefId(), "tst"),
                'tst',
            );
        }

        // elba hack for storing question id for inserting new question after
        if ($this->testrequest->raw('prev_qid')) {
            global $___prev_question_id;
            $___prev_question_id = $this->testrequest->raw('prev_qid');
            $this->ctrl->setParameter($this, 'prev_qid', $this->testrequest->raw('prev_qid'));
        }

        if (
            !$this->getCreationMode()
            && isset($this->test_question_set_config_factory)
            && $this->test_question_set_config_factory->getQuestionSetConfig()->areDepenciesBroken()
            && !$this->test_question_set_config_factory->getQuestionSetConfig()->isValidRequestOnBrokenQuestionSetDepencies($next_class, $cmd)
        ) {
            $this->ctrl->redirectByClass('ilObjTestGUI', 'infoScreen');
        }

        $this->determineObjectiveOrientedContainer();

        switch ($next_class) {
            case 'illtiproviderobjectsettinggui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->tabs_manager->getSettingsSubTabs();
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('lti_provider');
                $lti_gui = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
                $lti_gui->setCustomRolesForSelection($this->rbac_review->getLocalRoles($this->object->getRefId()));
                $lti_gui->offerLTIRolesForSelection(false);
                $this->ctrl->forwardCommand($lti_gui);
                break;


            case 'iltestexportgui':
                if (!$this->access->checkAccess('write', '', $this->ref_id)) {
                    $this->redirectAfterMissingWrite();
                }

                $this->prepareOutput();
                $this->addHeaderAction();
                $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_EXPORT);

                $selected_files = [];
                if ($this->testrequest->isset('file') && $this->testrequest->raw('file')) {
                    $selected_files = $this->testrequest->raw('file');
                }

                if (is_string($selected_files)) {
                    $selected_files = [$selected_files];
                }

                $export_gui = new ilTestExportGUI(
                    $this,
                    $this->db,
                    $this->logging_services->root(),
                    $this->obj_data_cache,
                    $this->component_repository,
                    $this->component_factory->getActivePluginsInSlot('texp'),
                    new ilTestHTMLGenerator(),
                    $selected_files,
                    $this->questioninfo
                );
                $this->ctrl->forwardCommand($export_gui);
                break;

            case "ilinfoscreengui":
                if (!$this->access->checkAccess("read", "", $this->testrequest->getRefId()) && !$this->access->checkAccess("visible", "", $this->testrequest->getRefId())) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->infoScreen(); // forwards command
                break;

            case "iltestscreengui":
                if (!$this->access->checkAccess('read', '', $this->testrequest->getRefId()) && !$this->access->checkAccess('visible', '', $this->testrequest->getRefId())) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->ctrl->forwardCommand($this->getTestScreenGUIInstance());
                break;

            case 'ilobjectmetadatagui':
                if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->prepareOutput();
                $this->addHeaderAction();
                $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_META_DATA);
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'iltestdashboardgui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();

                $gui = new ilTestDashboardGUI(
                    $this->getTestObject(),
                    $this->user,
                    $this->access,
                    $this->tpl,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->lng,
                    $this->db,
                    $this->ctrl,
                    $this->tabs_gui,
                    $this->toolbar,
                    $this->test_question_set_config_factory->getQuestionSetConfig(),
                    $this->testrequest
                );

                $gui->setTestAccess($this->getTestAccess());
                $gui->setTestTabs($this->getTabsManager());
                $gui->setObjectiveParent($this->getObjectiveOrientedContainer());

                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestresultsgui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();

                $gui = new ilTestResultsGUI(
                    $this->getTestObject(),
                    $this->test_question_set_config_factory->getQuestionSetConfig(),
                    $this->ctrl,
                    $this->access,
                    $this->db,
                    $this->refinery,
                    $this->user,
                    $this->lng,
                    $this->logging_services,
                    $this->component_repository,
                    $this->tabs_gui,
                    $this->toolbar,
                    $this->tpl,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->skills_service,
                    $this->testrequest,
                    $this->questioninfo,
                    $this->http
                );

                $gui->setTestAccess($this->getTestAccess());
                $gui->setTestSession($this->test_session_factory->getSession());
                $gui->setTestTabs($this->getTabsManager());
                $gui->setObjectiveParent($this->getObjectiveOrientedContainer());

                $this->ctrl->forwardCommand($gui);
                break;

            case "iltestplayerfixedquestionsetgui":
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
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
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
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
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->forwardToEvaluationGUI();
                break;

            case "iltestevalobjectiveorientedgui":
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                // @PHP8-CR I believe this is an indicator for an incomplete feature. I wish to leave it in place
                // "as is"for further analysis.
                $this->forwardToEvalObjectiveOrientedGUI();
                break;

            case "iltestservicegui":
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $serviceGUI = new ilTestServiceGUI($this->object);
                $this->ctrl->forwardCommand($serviceGUI);
                break;

            case 'ilpermissiongui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_PERMISSIONS);
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "illearningprogressgui":
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_LEARNING_PROGRESS);
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $this->object->getRefId());
                $this->ctrl->forwardCommand($new_gui);

                break;

            case "ilcertificategui":
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();

                $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_SETTINGS);

                $guiFactory = new ilCertificateGUIFactory();
                $output_gui = $guiFactory->create($this->object);

                $this->ctrl->forwardCommand($output_gui);
                break;

            case "iltestscoringgui":
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $output_gui = new ilTestScoringGUI($this->object);
                $output_gui->setTestAccess($this->getTestAccess());
                $this->ctrl->forwardCommand($output_gui);
                break;

            case 'ilmarkschemagui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->protectByWritePermission();

                $this->prepareOutput();
                $this->addHeaderAction();
                $mark_schema_gui = new ilMarkSchemaGUI($this->getTestObject());
                $this->ctrl->forwardCommand($mark_schema_gui);
                break;

            case 'iltestscoringbyquestionsgui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $output_gui = new ilTestScoringByQuestionsGUI($this->getTestObject());
                $output_gui->setTestAccess($this->getTestAccess());
                $this->ctrl->forwardCommand($output_gui);
                break;

            case 'ilobjtestsettingsmaingui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }

                $this->addHeaderAction();
                $gui = new ilObjTestSettingsMainGUI(
                    $this->tabs_gui,
                    $this->ctrl,
                    $this->access,
                    $this->lng,
                    $this->tree,
                    $this->db,
                    $this->component_repository,
                    $this->user,
                    $this,
                    $this->questioninfo
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjtestsettingsscoringresultsgui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = new ilObjTestSettingsScoringResultsGUI(
                    $this->ctrl,
                    $this->access,
                    $this->lng,
                    $this->tree,
                    $this->db,
                    $this->component_repository,
                    $this,
                    $this->tpl,
                    $this->tabs_gui,
                    $this->getTestObject()->getScoreSettingsRepository(),
                    $this->getTestObject()->getTestId(),
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->refinery,
                    $this->request,
                    $this->user
                );

                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestrandomquestionsetconfiggui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $test_process_locker_factory = (new ilTestProcessLockerFactory(
                    new ilSetting('assessment'),
                    $this->db
                ))->withContextId($this->object->getId());
                $gui = new ilTestRandomQuestionSetConfigGUI(
                    $this->getTestObject(),
                    $this->ctrl,
                    $this->user,
                    $this->access,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->tabs_gui,
                    $this->lng,
                    $this->logging_services->root(),
                    $this->tpl,
                    $this->db,
                    $this->tree,
                    $this->component_repository,
                    $this->obj_definition,
                    $this->obj_data_cache,
                    $test_process_locker_factory,
                    $this->testrequest,
                    $this->questioninfo
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestquestionbrowsertablegui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = new ilTestQuestionBrowserTableGUI(
                    $this->tabs_gui,
                    $this->tree,
                    $this->db,
                    $this->logging_services->root(),
                    $this->component_repository,
                    $this->getTestObject(),
                    $this->access,
                    $this->http,
                    $this->refinery,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->testrequest,
                    $this->questioninfo
                );
                $gui->setWriteAccess($this->access->checkAccess("write", "", $this->ref_id));
                $gui->init();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestskilladministrationgui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = new ilTestSkillAdministrationGUI(
                    $this->ctrl,
                    $this->access,
                    $this->tabs_gui,
                    $this->tpl,
                    $this->lng,
                    $this->refinery,
                    $this->db,
                    $this->logging_services->root(),
                    $this->tree,
                    $this->component_repository,
                    $this->getTestObject(),
                    $this->questioninfo,
                    $this->ref_id
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjectcopygui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('tst');
                $this->ctrl->forwardCommand($cp);
                break;

            case 'ilpageeditorgui':
            case 'iltestexpresspageobjectgui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->getTabsManager()->getQuestionsSubTabs();
                $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_PAGE_VIEW);
                $incompleteQuestionPurger = new ilAssIncompleteQuestionPurger($this->db);
                $incompleteQuestionPurger->setOwnerId($this->user->getId());
                $incompleteQuestionPurger->purge();

                try {
                    $qid = $this->fetchAuthoringQuestionIdParameter();
                } catch (ilTestException $e) {
                    $qid = 0;
                }

                $this->prepareOutput();
                if (!in_array($cmd, ['addQuestion', 'browseForQuestions'])) {
                    $this->buildPageViewToolbar($qid);
                }

                if (!$qid || in_array($cmd, ['insertQuestions', 'browseForQuestions'])) {
                    $pageObject = new ilTestExpressPageObjectGUI(0, 0, $this->object);
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
                $q_gui->setQuestionActionCmd('');

                $question = $q_gui->object;
                $this->ctrl->saveParameter($this, "q_id");

                #$this->lng->loadLanguageModule("content");
                $this->ctrl->setReturnByClass("ilTestExpressPageObjectGUI", "view");
                $this->ctrl->setReturn($this, "questions");

                $page_gui = new ilTestExpressPageObjectGUI($qid, 0, $this->object);
                $page_gui->setEditPreview(true);
                $page_gui->setEnabledTabs(false);
                if (strlen($this->ctrl->getCmd()) == 0) {
                    // @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
                    // $this->ctrl->setCmdClass(get_class($page_gui));
                    // $this->ctrl->setCmd("preview");
                }

                $page_gui->setQuestionHTML([$q_gui->object->getId() => $q_gui->getPreview(true)]);
                $page_gui->setTemplateTargetVar("ADM_CONTENT");

                $page_gui->setOutputMode($this->object->evalTotalPersons() == 0 ? "edit" : 'preview');

                $page_gui->setHeader($question->getTitle());
                $page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
                $page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "fullscreen"));
                $page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this));
                $page_gui->setPresentationTitle($question->getTitle() . ' [' . $this->lng->txt('question_id_short') . ': ' . $question->getId() . ']');
                $ret = $this->ctrl->forwardCommand($page_gui);
                if ($ret != "") {
                    $this->tpl->setContent($ret);
                }
                $this->tabs_gui->activateTab('assQuestions');

                break;

            case 'ilassquestionpreviewgui':
                if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->prepareOutput();

                $this->ctrl->saveParameter($this, "q_id");

                $gui = new ilAssQuestionPreviewGUI(
                    $this->ctrl,
                    $this->rbac_system,
                    $this->tabs_gui,
                    $this->tpl,
                    $this->lng,
                    $this->db,
                    $this->user,
                    $this->refinery->random(),
                    $this->global_screen
                );

                $gui->initQuestion($this->fetchAuthoringQuestionIdParameter(), $this->object->getId());
                $gui->initPreviewSettings($this->object->getRefId());
                $gui->initPreviewSession($this->user->getId(), $this->testrequest->getQuestionId());
                $gui->initHintTracking();
                $gui->initStyleSheets();

                $this->ctrl->forwardCommand($gui);

                break;

            case 'ilassquestionpagegui':
                if ($cmd == 'finishEditing') {
                    $this->ctrl->redirectByClass('ilassquestionpreviewgui', ilAssQuestionPreviewGUI::CMD_SHOW);
                    break;
                }
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                if ($cmd === 'edit' && !$this->access->checkAccess('write', '', $this->testrequest->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->prepareOutput();
                $forwarder = new ilAssQuestionPageCommandForwarder();
                $forwarder->setTestObj($this->getTestObject());
                $forwarder->forward();
                break;

            case 'ilassspecfeedbackpagegui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $pg_gui = new ilAssSpecFeedbackPageGUI((int) $this->testrequest->raw("feedback_id"));
                $this->ctrl->forwardCommand($pg_gui);
                break;

            case 'ilassgenfeedbackpagegui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $pg_gui = new ilAssGenFeedbackPageGUI($this->testrequest->int("feedback_id"));
                $this->ctrl->forwardCommand($pg_gui);
                break;

            case 'illocalunitconfigurationgui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
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
                if (!$this->access->checkAccess("read", "", $this->testrequest->getRefId()) && !$this->access->checkAccess("visible", "", $this->testrequest->getRefId())) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilassquestionhintsgui':
                if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->prepareSubGuiOutput();

                // set return target
                $this->ctrl->setReturn($this, "questions");
                $questionGUI = assQuestionGUI::_getQuestionGUI('', $this->fetchAuthoringQuestionIdParameter());
                $questionGUI->object->setObjId($this->object->getId());
                $questionGUI->setQuestionTabs();

                if ($this->object->evalTotalPersons() !== 0) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("question_is_part_of_running_test"), true);
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }
                $gui = new ilAssQuestionHintsGUI($questionGUI);

                $gui->setEditingEnabled(
                    $this->access->checkAccess('write', '', $this->object->getRefId())
                );

                $this->ctrl->forwardCommand($gui);

                break;

            case 'ilassquestionfeedbackeditinggui':
                if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->prepareSubGuiOutput();

                // set return target
                $this->ctrl->setReturn($this, "questions");
                $questionGUI = assQuestionGUI::_getQuestionGUI('', $this->fetchAuthoringQuestionIdParameter());
                $questionGUI->object->setObjId($this->object->getId());
                $questionGUI->setQuestionTabs();

                if ($this->object->evalTotalPersons() !== 0) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("question_is_part_of_running_test"), true);
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }
                $gui = new ilAssQuestionFeedbackEditingGUI($questionGUI, $this->ctrl, $this->access, $this->tpl, $this->tabs_gui, $this->lng);
                $this->ctrl->forwardCommand($gui);

                break;

            case 'iltestcorrectionsgui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $gui = new ilTestCorrectionsGUI(
                    $this->db,
                    $this->ctrl,
                    $this->access,
                    $this->lng,
                    $this->tabs_gui,
                    $this->help,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->tpl,
                    $this->refinery,
                    $this->request,
                    $this->testrequest,
                    $this->getTestObject(),
                    $this->questioninfo,
                    $this->table
                        ->withContextCorrections()
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestpagegui':
                if ((!$this->access->checkAccess("write", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingWrite();
                }
                $this->showEditTestPageGUI($cmd);
                break;

            case '':
            case 'ilobjtestgui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()) && !$this->access->checkAccess("visible", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                if ($cmd && (strcmp($cmd, "properties") == 0) && ($this->testrequest->raw("browse"))) {
                    $this->questionsObject();
                    return;
                }
                if ($cmd && (strcmp($cmd, "properties") == 0) && ($this->testrequest->raw("up") || $this->testrequest->raw("down"))) {
                    $this->questionsObject();
                    return;
                }
                if($cmd) {
                    $ret = $cmd === 'testScreen' ? $this->ctrl->forwardCommand($this->getTestScreenGUIInstance()) : $this->{$cmd . "Object"}();
                }
                break;
            default:
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                if (in_array($cmd, ['editQuestion', 'save', 'suggestedsolution'])
                    && !$this->access->checkAccess('write', '', $this->object->getRefId())) {
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

                    if (in_array($cmd, ['editQuestion', 'save', 'suggestedsolution'])
                        && $this->object->evalTotalPersons() !== 0) {
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
        if (!in_array(strtolower($this->testrequest->raw('baseClass')), ['iladministrationgui', 'ilrepositorygui'])
            && $this->getCreationMode() !== true) {
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

    protected function redirectAfterMissingRead(): void
    {
        $this->tpl->setOnScreenMessage('failure', sprintf(
            $this->lng->txt("msg_no_perm_read_item"),
            $this->object->getTitle()
        ), true);
        $this->ctrl->setParameterByClass('ilrepositorygui', 'ref_id', ROOT_FOLDER_ID);
        $this->ctrl->redirectByClass('ilrepositorygui');
    }

    protected function trackTestObjectReadEvent()
    {
        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->user->getId()
        );
    }

    /**
     * Gateway for exports initiated from workspace, as there is a generic
     * forward to {objTypeMainGUI}::export()
     */
    protected function exportObject()
    {
        $this->ctrl->redirectByClass('ilTestExportGUI');
    }

    /**
     * @return int
     * @throws ilTestException
     */
    protected function fetchAuthoringQuestionIdParameter(): int
    {
        $qid = $this->testrequest->int('q_id');

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
        // @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
        // $this->ctrl->setCmdClass('ilTestEvaluationGUI');
        // $this->ctrl->setCmd('outUserResultsOverview');
        $this->tabs_gui->clearTargets();

        $this->forwardToEvaluationGUI();
    }

    private function testResultsGatewayObject(): void
    {
        $this->ctrl->redirectByClass(
            [
                ilRepositoryGUI::class,
                __CLASS__,
                ilTestResultsGUI::class,
                ilParticipantsTestResultsGUI::class
            ],
            'showParticipants'
        );
    }

    private function showEditTestPageGUI(string $cmd): void
    {
        $this->prepareOutput();
        $this->tabs_manager->getSettingsSubTabs();

        if ($this->request_wrapper->has('page_type')
            && $this->request_wrapper->retrieve(
                'page_type',
                $this->refinery->kindlyTo()->string()
            ) === 'introductionpage'
        ) {
            $page_type = 'IntroductionPage';
            $this->tabs_manager->activateSubTab(ilTestTabsManager::SETTINGS_SUBTAB_ID_EDIT_INTRODUCTION_PAGE);
            $page_id = $this->object->getIntroductionPageId();
        } else {
            $page_type = 'ConcludingRemarksPage';
            $this->tabs_manager->activateSubTab(ilTestTabsManager::SETTINGS_SUBTAB_ID_EDIT_CONCLUSION_PAGE);
            $page_id = $this->object->getConcludingRemarksPageId();
        }
        $this->ctrl->saveParameterByClass(ilTestPageGUI::class, 'page_type');

        $gui = new ilTestPageGUI('tst', $page_id);
        $this->tpl->setContent($this->ctrl->forwardCommand($gui));

        $this->tabs_manager->activateTab(ilTestTabsManager::TAB_ID_SETTINGS);
    }

    public function getTestAccess(): ilTestAccess
    {
        return $this->test_access;
    }

    public function setTestAccess(ilTestAccess $test_access)
    {
        $this->test_access = $test_access;
    }

    public function getTabsManager(): ilTestTabsManager
    {
        return $this->tabs_manager;
    }

    public function setTabsManager(ilTestTabsManager $tabs_manager): void
    {
        $this->tabs_manager = $tabs_manager;
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

    private function redirectTo_ilObjTestSettingsMainGUI_showForm_Object()
    {
        $this->ctrl->redirectByClass('ilObjTestSettingsMainGUI', ilObjTestSettingsMainGUI::CMD_SHOW_FORM);
    }

    private function prepareSubGuiOutput()
    {
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
    protected function importFile(string $file_to_import, string $path_to_uploaded_file_in_temp_dir): void
    {
        list($subdir, $importdir, $xmlfile, $qtifile) = $this->buildImportDirectoriesFromImportFile($file_to_import);

        $options = (new ILIAS\Filesystem\Util\Archive\UnzipOptions())
            ->withZipOutputPath($this->getImportTempDirectory());

        $unzip = $this->archives->unzip(Streams::ofResource(fopen($file_to_import, 'r')), $options);
        $unzip->extract();

        if (!is_file($qtifile)) {
            ilFileUtils::delDir($importdir);
            $this->deleteUploadedImportFile($path_to_uploaded_file_in_temp_dir);
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_import_non_ilias_zip'), true);
        }
        $qtiParser = new ilQTIParser($importdir, $qtifile, ilQTIParser::IL_MO_VERIFY_QTI, 0, []);
        $qtiParser->startParsing();
        $founditems = $qtiParser->getFoundItems();

        $complete = 0;
        $incomplete = 0;
        foreach ($founditems as $item) {
            if ($item["type"] !== '') {
                $complete++;
            } else {
                $incomplete++;
            }
        }

        if (count($founditems) && $complete == 0) {
            ilFileUtils::delDir($importdir);
            $this->deleteUploadedImportFile($path_to_uploaded_file_in_temp_dir);
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('qpl_import_non_ilias_files'));
            return;
        }

        if ($qtiParser->getQuestionSetType() !== ilObjTest::QUESTION_SET_TYPE_FIXED) {
            $this->importVerifiedFileObject();
            return;
        }

        ilSession::set('path_to_import_file', $file_to_import);
        ilSession::set('path_to_uploaded_file_in_temp_dir', $path_to_uploaded_file_in_temp_dir);

        $form = $this->buildImportQuestionsSelectionForm(
            'importVerifiedFile',
            $importdir,
            $qtifile,
            $file_to_import,
            $path_to_uploaded_file_in_temp_dir
        );

        if ($form === null) {
            return;
        }

        $panel = $this->ui_factory->panel()->standard(
            $this->lng->txt('import_tst'),
            [
                $this->ui_factory->legacy($this->lng->txt('qpl_import_verify_found_questions')),
                $form
            ]
        );
        $this->tpl->setContent($this->ui_renderer->render($panel));
        $this->tpl->printToStdout();
        exit;
    }

    public function retrieveAdditionalDidacticTemplateOptions(): array
    {
        $tst = new ilObjTest();
        $defaults = $tst->getAvailableDefaults();
        if ($defaults === []) {
            return [];
        }

        $additional_options = [];
        foreach ($defaults as $row) {
            $additional_options["tstdef_" . $row["test_defaults_id"]] = [$row["name"],
                $this->lng->txt("tst_default_settings")];
        }
        return $additional_options;
    }

    /**
    * save object
    * @access	public
    */
    public function afterSave(ilObject $new_object): void
    {
        $new_object->saveToDb();

        $test_def_id = $this->getDidacticTemplateVar("tstdef");
        if ($test_def_id !== 0) {
            $test_defaults = $new_object->getTestDefaults($test_def_id);
            $new_object->applyDefaults($test_defaults);
        }

        $template_id = $this->getDidacticTemplateVar("tsttpl");
        if ($template_id) {
            $new_object->setTemplate($template_id);
        }

        $new_object->saveToDb();

        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->ctrl->setParameter($this, 'ref_id', $new_object->getRefId());
        $this->ctrl->redirectByClass('ilObjTestSettingsMainGUI');
    }

    public function backToRepositoryObject()
    {
        $path = $this->tree->getPathFull($this->object->getRefID());
        ilUtil::redirect($this->getReturnLocation("cancel", "./ilias.php?baseClass=ilRepositoryGUI&cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
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
        $file_to_import = ilSession::get('path_to_import_file');
        $path_to_uploaded_file_in_temp_dir = ilSession::get('path_to_uploaded_file_in_temp_dir');
        list($subdir, $importdir, $xmlfile, $qtifile) = $this->buildImportDirectoriesFromImportFile($file_to_import);

        $new_obj = new ilObjTest(0, true);
        $new_obj->setTitle("dummy");
        $new_obj->setDescription("test import");
        $new_obj->create(true);
        $new_obj->createReference();
        $new_obj->putInTree($this->testrequest->getRefId());
        $new_obj->setPermissions($this->testrequest->getRefId());
        $new_obj->resetMarkSchema();

        $selected_questions = $this->retrieveSelectedQuestionsFromImportQuestionsSelectionForm(
            'importVerifiedFile',
            $importdir,
            $qtifile
        );

        if (is_file($importdir . DIRECTORY_SEPARATOR . "/manifest.xml")) {
            $new_obj->saveToDb();

            ilSession::set('tst_import_selected_questions', $selected_questions);
            ilSession::set('tst_import_qst_parent', $new_obj->getId());

            $imp = new ilImport($this->testrequest->getRefId());
            $map = $imp->getMapping();
            $map->addMapping('components/ILIAS/Test', 'tst', 'new_id', (string) $new_obj->getId());
            $imp->importObject($new_obj, $file_to_import, basename($file_to_import), 'tst', 'components/ILIAS/Test', true);
        } else {
            $qtiParser = new ilQTIParser(
                $importdir,
                $qtifile,
                ilQTIParser::IL_MO_PARSE_QTI,
                $new_obj->getId(),
                $selected_questions
            );
            if ($idents === '') {
                $qtiParser->setIgnoreItemsEnabled(true);
            }
            $qtiParser->setTestObject($new_obj);
            $qtiParser->startParsing();
            $new_obj->saveToDb();
            $questionPageParser = new ilQuestionPageParser(
                $new_obj,
                $xmlfile,
                $importdir
            );
            $questionPageParser->setQuestionMapping($qtiParser->getImportMapping());
            $questionPageParser->startParsing();

            if (count($idents) == $qtiParser->getNumImportedItems()) {
                // import test results
                if (file_exists(ilSession::get("tst_import_results_file"))) {
                    $results = new ilTestResultsImportParser(
                        ilSession::get("tst_import_results_file"),
                        $new_obj,
                        $this->db,
                        $this->logging_services->root()
                    );
                    $results->setQuestionIdMapping($qtiParser->getQuestionIdMapping());
                    $results->startParsing();
                }
            }
            $new_obj->update();
        }


        // delete import directory
        ilFileUtils::delDir($importdir);
        $this->deleteUploadedImportFile($path_to_uploaded_file_in_temp_dir);
        ilSession::clear('path_to_import_file');
        ilSession::clear('path_to_uploaded_file_in_temp_dir');

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_imported"), true);
        $this->ctrl->setParameterByClass(self::class, 'ref_id', $new_obj->getRefId());
        $this->ctrl->redirectByClass(self::class);
    }

    /**
    * display status information or report errors messages
    * in case of error
    *
    * @access	public
    */
    public function uploadObject()
    {
        $this->importFile();
    }

    /**
    * download file
    */
    public function downloadFileObject()
    {
        $file = explode("_", $this->testrequest->raw("file_id"));
        $fileObj = new ilObjFile((int) $file[count($file) - 1], false);
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
        $pg_obj->sendParagraph($this->testrequest->raw("par_id"), $this->testrequest->raw("downloadtitle"));
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
        $parent_ref = $this->tree->getParentId($this->object->getRefId());
        $qpl = new ilObjQuestionPool();
        $qpl->setType("qpl");
        $qpl->setTitle($name);
        $qpl->setDescription($description);
        $qpl->create();
        $qpl->createReference();
        $qpl->putInTree($parent_ref);
        $qpl->setPermissions($parent_ref);
        $qpl->getObjectProperties()->storePropertyIsOnline($qpl->getObjectProperties()->getPropertyIsOnline()->withOnline()); // must be online to be available
        $qpl->saveToDb();
        return $qpl->getRefId();
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

        if (($qpl_mode === 2 && $this->testrequest->raw("txt_qpl") === '')
            || $qpl_mode === 3 && $qpl_ref_id === '') {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("questionpool_not_entered"));
            $this->createQuestionObject();
            return;
        }

        ilSession::set("test_id", $this->object->getRefId());
        if ($qpl_mode === 2) {
            // create a new question pool and return the reference id
            $qpl_ref_id = $this->createQuestionPool($this->testrequest->raw("txt_qpl"));
        } elseif ($qpl_mode === 1) {
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
        $this->getTabsManager()->getQuestionsSubTabs();
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);
        $questionpools = $this->object->getAvailableQuestionpools(false, false, false, true, false, "write");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "executeCreateQuestion"));
        $form->setTitle($this->lng->txt("ass_create_question"));

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
            $ri = new ilRadioGroupInputGUI($this->lng->txt("tst_add_quest_cont_edit_mode"), "add_quest_cont_edit_mode");

            $option_ipe = new ilRadioOption(
                $this->lng->txt('tst_add_quest_cont_edit_mode_IPE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE
            );
            $option_ipe->setInfo($this->lng->txt('tst_add_quest_cont_edit_mode_IPE_info'));
            $ri->addOption($option_ipe);

            $option_rte = new ilRadioOption(
                $this->lng->txt('tst_add_quest_cont_edit_mode_RTE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE
            );
            $option_rte->setInfo($this->lng->txt('tst_add_quest_cont_edit_mode_RTE_info'));
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
        $no_pool = new ilRadioOption($this->lng->txt("assessment_no_pool"), '1');
        $usage->addOption($no_pool);
        $existing_pool = new ilRadioOption($this->lng->txt("assessment_existing_pool"), '3');
        $usage->addOption($existing_pool);
        $new_pool = new ilRadioOption($this->lng->txt("assessment_new_pool"), '2');
        $usage->addOption($new_pool);
        $form->addItem($usage);

        $usage->setValue('1');

        $questionpools = ilObjQuestionPool::_getAvailableQuestionpools(false, false, true, false, false, "write");
        $pools_data = [];
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

        $form->addCommandButton("executeCreateQuestion", $this->lng->txt("submit"));
        $form->addCommandButton("cancelCreateQuestion", $this->lng->txt("cancel"));

        $this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
    }

    /**
     * Remove questions from the test after confirmation
     */
    public function confirmRemoveQuestionsObject(array $question_ids = [])
    {
        if($question_ids === []) {
            $removeQuestionIds = (array) $_POST["q_id"];
        } else {
            $removeQuestionIds = $question_ids;
        }

        $questions = $this->object->getQuestionTitlesAndIndexes();

        $this->object->removeQuestions($removeQuestionIds);

        $this->object->saveCompleteStatus($this->test_question_set_config_factory->getQuestionSetConfig());

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_questions_removed"), true);

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
                    $txt = $data["title"] . " (" . $this->questioninfo->getQuestionTypeName($data["question_id"]) . ")";
                    $txt .= ' [' . $this->lng->txt('question_id_short') . ': ' . $data['question_id'] . ']';

                    if ($data["description"]) {
                        $txt .= "<div class=\"small\">" . $data["description"] . "</div>";
                    }

                    $cgui->addItem("q_id[]", (string) $data["question_id"], $txt);
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
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);

        $checked_questions = $this->testrequest->raw('q_id');

        if (!is_array($checked_questions) && $checked_questions) {
            $checked_questions = [$checked_questions];
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
    public function moveQuestionsObject(): void
    {
        $selected_questions = $this->testrequest->getQuestionIds();
        $selected_question = $this->testrequest->getQuestionId();
        if ($selected_questions === [] && $selected_question !== 0) {
            $selected_questions = [$selected_question];
        }

        if ($selected_questions === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_selection_for_move'), true);
            $this->ctrl->redirect($this, 'questions');
            return;
        }

        ilSession::set('tst_qst_move_' . $this->object->getTestId(), $selected_questions);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_selected_for_move"), true);
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
        $selected_array = (is_array($_POST['q_id'])) ? $_POST['q_id'] : [];
        if (!count($selected_array)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_insert_missing_question"), true);
            $this->ctrl->redirect($this, "browseForQuestions");
        } else {
            $manscoring = false;
            foreach ($selected_array as $key => $value) {
                $this->object->insertQuestion($this->test_question_set_config_factory->getQuestionSetConfig(), $value);
                if (!$manscoring) {
                    $manscoring = $manscoring | assQuestion::_needsManualScoring((int) $value);
                }
            }
            $this->object->saveCompleteStatus($this->test_question_set_config_factory->getQuestionSetConfig());
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
        $this->getTabsManager()->getQuestionsSubTabs();
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);

        $subScreenId = ['createQuestion'];

        $this->ctrl->setParameter($this, 'qtype', $this->testrequest->raw('qtype'));

        $form = new ilPropertyFormGUI();

        $form->setFormAction($this->ctrl->getFormAction($this, "executeCreateQuestion"));
        $form->setTitle($this->lng->txt("ass_create_question"));

        $pool = new ilObjQuestionPool();
        $questionTypes = $pool->getQuestionTypes(false, true, false);
        $options = [];

        // question type
        foreach ($questionTypes as $label => $data) {
            $options[$data['question_type_id']] = $label;
        }
        $si = new ilSelectInputGUI($this->lng->txt("question_type"), "qtype");
        $si->setOptions($options);
        $form->addItem($si, true);

        // position
        $questions = $this->object->getQuestionTitlesAndIndexes();
        if ($questions) {
            $si = new ilSelectInputGUI($this->lng->txt("position"), "position");
            $options = ['0' => $this->lng->txt('first')];
            foreach ($questions as $key => $title) {
                $options[$key] = $this->lng->txt('behind') . ' ' . $title . ' [' . $this->lng->txt('question_id_short') . ': ' . $key . ']';
            }
            $si->setOptions($options);
            $si->setValue($this->testrequest->raw('q_id'));
            $form->addItem($si, true);
        }

        // content editing mode
        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $subScreenId[] = 'editMode';

            $ri = new ilRadioGroupInputGUI($this->lng->txt("tst_add_quest_cont_edit_mode"), "add_quest_cont_edit_mode");

            $option_ipe = new ilRadioOption(
                $this->lng->txt('tst_add_quest_cont_edit_mode_IPE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE
            );
            $option_ipe->setInfo($this->lng->txt('tst_add_quest_cont_edit_mode_IPE_info'));
            $ri->addOption($option_ipe);

            $option_rte = new ilRadioOption(
                $this->lng->txt('tst_add_quest_cont_edit_mode_RTE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE
            );
            $option_rte->setInfo($this->lng->txt('tst_add_quest_cont_edit_mode_RTE_info'));
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
        $no_pool = new ilRadioOption($this->lng->txt("assessment_no_pool"), '1');
        $usage->addOption($no_pool);
        $existing_pool = new ilRadioOption($this->lng->txt("assessment_existing_pool"), '3');
        $usage->addOption($existing_pool);
        $new_pool = new ilRadioOption($this->lng->txt("assessment_new_pool"), '2');
        $usage->addOption($new_pool);
        $form->addItem($usage);

        $usage->setValue('1');

        $questionpools = ilObjQuestionPool::_getAvailableQuestionpools(false, false, true, false, false, "write");
        $pools_data = [];
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

        $form->addCommandButton("executeCreateQuestion", $this->lng->txt("create"));
        $form->addCommandButton("questions", $this->lng->txt("cancel"));

        $this->tabs_gui->activateTab('assQuestions');
        $this->help->setScreenId('assQuestions');
        $this->help->setSubScreenId(implode('_', $subScreenId));

        return $this->tpl->setContent($form->getHTML());
    }

    public function questionsObject()
    {
        $this->ctrl->setParameter($this, 'test_express_mode', '');

        if (!$this->access->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }

        $this->getTabsManager()->getQuestionsSubTabs();
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);

        // #11631, #12994
        $this->ctrl->setParameter($this, 'q_id', '');

        if ($this->testrequest->raw("eqid") && $this->testrequest->raw("eqpl")) {
            ilUtil::redirect("ilias.php?baseClass=ilObjQuestionPoolGUI&ref_id="
                . $this->testrequest->raw("eqpl") . "&cmd=editQuestionForTest&calling_test="
                . $this->testrequest->getRefId() . "&q_id=" . $this->testrequest->raw("eqid"));
        }

        if ($this->testrequest->raw("up") > 0) {
            $this->object->questionMoveUp($this->testrequest->raw("up"));
        }
        if ($this->testrequest->raw("down") > 0) {
            $this->object->questionMoveDown($this->testrequest->raw("down"));
        }

        if ($this->testrequest->raw("add")) {
            $selected_array = [];
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

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questions.html", "components/ILIAS/Test");

        $has_started_test_runs = $this->object->evalTotalPersons() !== 0;
        if ($this->access->checkAccess("write", "", $this->ref_id)) {
            if ($has_started_test_runs) {
                $link = $this->ui_factory->link()->standard(
                    $this->lng->txt("test_has_datasets_warning_page_view_link"),
                    $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilParticipantsTestResultsGUI'])
                );

                $message = $this->lng->txt("test_has_datasets_warning_page_view");
                $massage_box = $this->ui_factory->messageBox()->info($message)->withLinks([$link]);
                $this->tpl->setCurrentBlock('mess');
                $this->tpl->setVariable(
                    'MESSAGE',
                    $this->ui_renderer->render($massage_box)
                );
                $this->tpl->parseCurrentBlock();
            } else {
                $this->toolbar->addButton($this->lng->txt("ass_create_question"), $this->ctrl->getLinkTarget($this, "addQuestion"));
                $this->toolbar->addSeparator();
                $this->populateQuestionBrowserToolbarButtons($this->toolbar, ilTestQuestionBrowserTableGUI::CONTEXT_LIST_VIEW);
            }
        }

        $this->tpl->setCurrentBlock("adm_content");
        $this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable(
            'QUESTIONBROWSER',
            $this->ui_renderer->render(
                $this->table
                ->withQuestionEditing(!$has_started_test_runs)
                ->getTable($this->object->getTestQuestions())
                ->withOrderingDisabled($has_started_test_runs)
            )
        );
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
        $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_HISTORY);
        $table_gui = new ilTestHistoryTableGUI($this, 'history');
        $table_gui->setTestObject($this->object);
        $log = ilObjAssessmentFolder::_getLog(0, time(), $this->object->getId(), true);
        $table_gui->setData($log);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
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
    public function printObject()
    {
        if (!$this->access->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }

        $this->getTabsManager()->getQuestionsSubTabs();
        $template = new ilTemplate("tpl.il_as_tst_print_test_confirm.html", true, true, "components/ILIAS/Test");

        $template->setCurrentBlock("navigation_buttons");
        $template->setVariable("BUTTON_PRINT", $this->lng->txt("print"));
        $template->parseCurrentBlock();

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css"), "print");

        $print_date = mktime((int) date("H"), (int) date("i"), (int) date("s"), (int) date("m"), (int) date("d"), (int) date("Y"));
        $max_points = 0;
        $counter = 1;
        $questionHeaderBlockBuilder = new ilTestQuestionHeaderBlockBuilder($this->lng);
        $questionHeaderBlockBuilder->setHeaderMode($this->object->getTitleOutput());

        foreach ($this->object->questions as $question) {
            $template->setCurrentBlock("question");
            $question_gui = $this->object->createQuestionGUI("", $question);
            $question_gui->setPresentationContext(assQuestionGUI::PRESENTATION_CONTEXT_TEST);

            $questionHeaderBlockBuilder->setQuestionTitle($question_gui->object->getTitle());
            $questionHeaderBlockBuilder->setQuestionPoints($question_gui->object->getMaximumPoints());
            $questionHeaderBlockBuilder->setQuestionPosition($counter);
            $template->setVariable("QUESTION_HEADER", $questionHeaderBlockBuilder->getHTML());

            $template->setVariable("TXT_QUESTION_ID", $this->lng->txt('question_id_short'));
            $template->setVariable("QUESTION_ID", $question_gui->object->getId());
            $result_output = $question_gui->getSolutionOutput(0, null, false, true, false, false);
            $template->setVariable("SOLUTION_OUTPUT", $result_output);
            $template->parseCurrentBlock("question");
            $counter++;
            $max_points += $question_gui->object->getMaximumPoints();
        }

        $template->setVariable(
            'TITLE',
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                $this->object->getTitle()
            )
        );
        $template->setVariable("PRINT_TEST", ilLegacyFormElementsUtil::prepareFormOutput($this->lng->txt("tst_print")));
        $template->setVariable("TXT_PRINT_DATE", ilLegacyFormElementsUtil::prepareFormOutput($this->lng->txt("date")));
        $template->setVariable(
            "VALUE_PRINT_DATE",
            ilDatePresentation::formatDate(new ilDateTime($print_date, IL_CAL_UNIX))
        );
        $template->setVariable(
            "TXT_MAXIMUM_POINTS",
            ilLegacyFormElementsUtil::prepareFormOutput($this->lng->txt("tst_maximum_points"))
        );
        $template->setVariable("VALUE_MAXIMUM_POINTS", ilLegacyFormElementsUtil::prepareFormOutput($max_points));
        $this->tpl->setVariable("PRINT_CONTENT", $template->get());
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
        if (!$this->access->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }
        $this->getTabsManager()->getQuestionsSubTabs();
        $template = new ilTemplate("tpl.il_as_tst_print_test_confirm.html", true, true, "components/ILIAS/Test");

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css"), "print");

        $max_points = 0;
        $counter = 1;
        $questionHeaderBlockBuilder = new ilTestQuestionHeaderBlockBuilder($this->lng);
        $questionHeaderBlockBuilder->setHeaderMode($this->object->getTitleOutput());

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

        $template->setVariable(
            'TITLE',
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                $this->object->getTitle()
            )
        );
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

        $template->setCurrentBlock("navigation_buttons");
        $template->setVariable("BUTTON_PRINT", $this->lng->txt("print"));
        $template->parseCurrentBlock();

        $this->tpl->setVariable("PRINT_CONTENT", $template->get());
    }

    /**
     * Displays the settings page for test defaults
     */
    public function defaultsObject()
    {
        if (!$this->access->checkAccess("write", "", $this->ref_id)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }

        $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_SETTINGS);

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'addDefaults'));
        $this->toolbar->addFormButton($this->lng->txt('add'), 'addDefaults');
        $this->toolbar->addInputItem(new ilTextInputGUI($this->lng->txt('tst_defaults_defaults_of_test'), 'name'), true);
        $table = new ilTestPersonalDefaultSettingsTableGUI($this, 'defaults');
        $defaults = $this->object->getAvailableDefaults();
        $table->setData($defaults);
        $this->tpl->setContent($table->getHTML());
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
        if (!isset($_POST['chb_defaults']) || !is_array($_POST["chb_defaults"]) || 1 !== count($_POST["chb_defaults"])) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_defaults_apply_select_one"));

            $this->defaultsObject();
            return;
        }

        // do not apply if user datasets exist
        if ($this->object->evalTotalPersons() > 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_defaults_apply_not_possible"));

            $this->defaultsObject();
            return;
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

        $oldQuestionSetConfig = $this->test_question_set_config_factory->getQuestionSetConfig();

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
        // @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
        // $this->ctrl->setCmd("showSummary");
        // $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    public function redirectToInfoScreenObject()
    {
        // @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
        // $this->ctrl->setCmd("showSummary");
        // $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen($this->testrequest->raw('lock') ?? '');
    }

    /**
    * show information screen
    */
    public function infoScreen($session_lock = "")
    {
        if (!$this->access->checkAccess("visible", "", $this->ref_id) && !$this->access->checkAccess("read", "", $_GET["ref_id"])) {
            $this->redirectAfterMissingRead();
            return '';
        }

        if ($this->object->getMainSettings()->getAdditionalSettings()->getHideInfoTab()) {
            $this->ctrl->redirectByClass(ilTestScreenGUI::class, ilTestScreenGUI::DEFAULT_CMD);
            return '';
        }

        $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_INFOSCREEN);

        if ($this->access->checkAccess("read", "", $this->testrequest->getRefId())) {
            $this->trackTestObjectReadEvent();
        }
        $info = new ilInfoScreenGUI($this);
        $info->setOpenFormTag(false);

        if ($this->isCommandClassAnyInfoScreenChild()) {
            return $this->ctrl->forwardCommand($info);
        }

        $toolbar = new ilTestInfoScreenToolbarGUI(
            $this->object,
            $this->test_player_factory->getPlayerGUI(),
            $this->test_question_set_config_factory->getQuestionSetConfig(),
            $this->test_session_factory->getSession(),
            $this->db,
            $this->access,
            $this->ctrl,
            $this->lng,
            $this->ui_factory,
            $this->ui_renderer,
            $this->tpl,
            $this->toolbar
        );

        $toolbar->setCloseFormTag(false);

        $toolbar->setSessionLockString($session_lock);
        $toolbar->build();
        $toolbar->sendMessages();

        $info->enablePrivateNotes();

        $info->addSection($this->lng->txt("tst_general_properties"));
        $info->addProperty(
            $this->lng->txt("author"),
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                $this->object->getAuthor()
            )
        );
        $info->addProperty(
            $this->lng->txt("title"),
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                $this->object->getTitle()
            )
        );

        if ($this->type !== 'tst') {
            $info->hideFurtherSections(false);
        }

        $info->addSection($this->lng->txt("tst_sequence_properties"));
        $info->addProperty(
            $this->lng->txt("tst_sequence"),
            $this->lng->txt(
                $this->object->getMainSettings()->getParticipantFunctionalitySettings()->getPostponedQuestionsMoveToEnd()
                    ? "tst_sequence_postpone" : "tst_sequence_fixed"
            )
        );

        $info->addSection($this->lng->txt("tst_heading_scoring"));
        $info->addProperty(
            $this->lng->txt("tst_text_count_system"),
            $this->lng->txt(
                ($this->object->getCountSystem() == ilObjTestSettingsScoring::COUNT_PARTIAL_SOLUTIONS) ? "tst_count_partial_solutions" : "tst_count_correct_solutions"
            )
        );
        if ($this->object->isRandomTest()) {
            $info->addProperty($this->lng->txt("tst_pass_scoring"), $this->lng->txt(($this->object->getPassScoring() == ilObjTest::SCORE_BEST_PASS) ? "tst_pass_best_pass" : "tst_pass_last_pass"));
        }

        $info->addSection($this->lng->txt("tst_score_reporting"));
        $score_reporting_text = "";
        switch ($this->object->getScoreReporting()) {
            case ilObjTestSettingsResultSummary::SCORE_REPORTING_FINISHED:
                $score_reporting_text = $this->lng->txt("tst_report_after_test");
                break;
            case ilObjTestSettingsResultSummary::SCORE_REPORTING_IMMIDIATLY:
                $score_reporting_text = $this->lng->txt("tst_report_after_first_question");
                break;
            case ilObjTestSettingsResultSummary::SCORE_REPORTING_DATE:
                $score_reporting_text = $this->lng->txt("tst_report_after_date");
                break;
            case ilObjTestSettingsResultSummary::SCORE_REPORTING_AFTER_PASSED:
                $score_reporting_text = $this->lng->txt("tst_report_after_passed");
                break;
            default:
                $score_reporting_text = $this->lng->txt("tst_report_never");
                break;
        }
        $info->addProperty($this->lng->txt("tst_score_reporting"), $score_reporting_text);
        $reporting_date = $this->object->getReportingDate();
        if ($reporting_date) {
            $info->addProperty(
                $this->lng->txt('tst_score_reporting_date'),
                ilDatePresentation::formatDate(new ilDateTime($reporting_date, IL_CAL_TIMESTAMP))
            );
        }

        $info->addSection($this->lng->txt("tst_session_settings"));
        $info->addProperty($this->lng->txt("tst_nr_of_tries"), $this->object->getNrOfTries() === 0 ? $this->lng->txt("unlimited") : (string) $this->object->getNrOfTries());
        if ($this->object->getNrOfTries() != 1) {
            $info->addProperty(
                $this->lng->txt('tst_nr_of_tries_of_user'),
                ($this->test_session_factory->getSession()->getPass() === 0) ?
                    $this->lng->txt("tst_no_tries") : (string) $this->test_session_factory->getSession()->getPass()
            );
        }

        if ($this->object->getEnableProcessingTime()) {
            $info->addProperty($this->lng->txt("tst_processing_time"), $this->object->getProcessingTime());
        }

        $starting_time = $this->object->getStartingTime();
        if ($this->object->isStartingTimeEnabled() && $starting_time !== 0) {
            $info->addProperty($this->lng->txt("tst_starting_time"), ilDatePresentation::formatDate(new ilDateTime($starting_time, IL_CAL_UNIX)));
        }
        $ending_time = $this->object->getEndingTime();
        if ($this->object->isEndingTimeEnabled() && $ending_time != 0) {
            $info->addProperty($this->lng->txt("tst_ending_time"), ilDatePresentation::formatDate(new ilDateTime($ending_time, IL_CAL_UNIX)));
        }
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

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
                $this->locator->addItem(
                    $this->object->getTitle(),
                    $this->ctrl->getLinkTargetByClass(ilTestScreenGUI::class, ilTestScreenGUI::DEFAULT_CMD),
                    '',
                    $this->testrequest->getRefId()
                );
                break;
            case "eval_stat":
            case "evalAllUsers":
            case "evalUserDetail":
                $this->locator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "eval_stat"), "", $this->testrequest->getRefId());
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
                $this->locator->addItem($this->object->getTitle(), $this->ctrl->getLinkTargetByClass(ilTestScreenGUI::class, ilTestScreenGUI::DEFAULT_CMD), '', $this->testrequest->getRefId());
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
        $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_SETTINGS);

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
        $this->help->setScreenIdComponent("tst");

        if ($this->tabs_manager === null) {
            return;
        }

        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $courseLink = ilLink::_getLink($this->getObjectiveOrientedContainer()->getRefId());
            $this->getTabsManager()->setParentBackLabel($this->lng->txt('back_to_objective_container'));
            $this->getTabsManager()->setParentBackHref($courseLink);
        }

        $this->getTabsManager()->perform();
    }

    protected function setTitleAndDescription(): void
    {
        parent::setTitleAndDescription();

        $icon = ilObject::_getIcon($this->object->getId(), 'big', $this->object->getType());
        $this->tpl->setTitleIcon($icon, $this->lng->txt('obj_' . $this->object->getType()));
    }

    public static function accessViolationRedirect()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $main_tpl->setOnScreenMessage('failure', $DIC->language()->txt("no_permission"), true);
        $DIC->ctrl()->redirectByClass(ilTestScreenGUI::class, ilTestScreenGUI::DEFAULT_CMD);
    }

    /**
    * Redirect script to call a test with the test reference id
    *
    * @param integer $a_target The reference id of the test
    * @access	public
    */
    public static function _goto($target)
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];

        if ($ilAccess->checkAccess("read", "", (int) $target) || $ilAccess->checkAccess("visible", "", (int) $target)) {
            $DIC->ctrl()->setParameterByClass('ilObjTestGUI', 'ref_id', (int) $target);
            $DIC->ctrl()->redirectByClass([ilRepositoryGUI::class, ilObjTestGUI::class, ilTestScreenGUI::class], ilTestScreenGUI::DEFAULT_CMD);
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('info', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId((int) $target))
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
        $this->ctrl->saveParameter($this, 'q_mode');

        $this->ctrl->setParameterByClass('iltestexpresspageobjectgui', 'test_express_mode', 1);
        $this->ctrl->setParameter($this, 'test_express_mode', 1);
        $this->ctrl->setParameterByClass('iltestexpresspageobjectgui', 'q_id', $this->testrequest->raw('q_id'));
        $this->ctrl->setParameter($this, 'q_id', $this->testrequest->raw('q_id'));
        $this->toolbar->setFormAction($this->ctrl->getFormActionByClass('iltestexpresspageobjectgui', 'edit'));

        if ($this->object->evalTotalPersons() == 0) {
            $this->toolbar->addFormButton($this->lng->txt("ass_create_question"), "addQuestion");

            $this->toolbar->addSeparator();

            $this->populateQuestionBrowserToolbarButtons($this->toolbar, ilTestQuestionBrowserTableGUI::CONTEXT_PAGE_VIEW);

            $show_separator = true;
        }

        $questions = $this->object->getQuestionTitlesAndIndexes();

        // desc
        $options = [];
        foreach ($questions as $id => $label) {
            $options[$id] = $label . ' [' . $this->lng->txt('question_id_short') . ': ' . $id . ']';
        }

        $optionKeys = array_keys($options);

        if (!$options) {
            $options[] = $this->lng->txt('none');
        }

        if (count($questions)) {
            if (isset($show_separator) && $show_separator) {
                $this->toolbar->addSeparator();
            }

            $btn = $this->ui[0]->linkButton()->standard($lng->txt("test_prev_question"), $this->ctrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'prevQuestion'));
            $this->toolbar->addComponent($btn);

            if (count($options) <= 1 || $optionKeys[0] == $qid) {
                $btn->setDisabled(true);
            }

            $btn = $this->ui[0]->linkButton()->standard($lng->txt("test_next_question"), $this->ctrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'nextQuestion'));
            $this->toolbar->addComponent($btn);

            if (count($options) <= 1 || $optionKeys[count($optionKeys) - 1] == $qid) {
                $btn->setDisabled(true);
            }
        }

        if (count($questions) > 1) {
            $this->toolbar->addSeparator();
            $si = new ilSelectInputGUI($this->lng->txt("test_jump_to"), "q_id");
            $si->addCustomAttribute("onChange=\"forms['ilToolbar'].submit();\"");
            $si->setOptions($options);

            if ($qid) {
                $si->setValue($qid);
            }

            $this->toolbar->addInputItem($si, true);
        }

        $total = $this->object->evalTotalPersons();

        if (count($questions) && !$total) {
            $this->ctrl->setParameter($this, 'q_id', $this->testrequest->raw('q_id'));
            $this->toolbar->addSeparator();
            $this->toolbar->addButton($this->lng->txt("test_delete_page"), $this->ctrl->getLinkTarget($this, "removeQuestions"));
        }

        if (count($questions) > 1 && !$total) {
            $this->toolbar->addSeparator();
            $this->toolbar->addButton($this->lng->txt("test_move_page"), $this->ctrl->getLinkTarget($this, "movePageForm"));
        }

        $missing_participant_access = $this->test_access->isParticipantAllowed(
            $this->getTestObject()->getId(),
            $this->user->getId()
        ) !== ParticipantAccess::ALLOWED;

        if ($this->object->getOfflineStatus()
            || !$this->object->isComplete($this->test_question_set_config_factory->getQuestionSetConfig())
            || $missing_participant_access
            || !$this->access->checkAccess("read", "", $this->ref_id)
        ) {
            return;
        }

        $testSession = $this->test_session_factory->getSession();
        $executable = $this->object->isExecutable($testSession, $this->user->getId(), true);
        if (!$executable["executable"]) {
            return;
        }

        $player_factory = new ilTestPlayerFactory($this->getTestObject());
        $player_instance = $player_factory->getPlayerGUI();
        $this->toolbar->addSeparator();
        if ($testSession->getActiveId() > 0) {
            $this->toolbar->addButton($this->lng->txt('tst_resume_test'), $this->ctrl->getLinkTarget($player_instance, 'resumePlayer'));
            return;
        }
        $this->toolbar->addButton($this->lng->txt('tst_start_test'), $this->ctrl->getLinkTarget($player_instance, 'startTest'));
    }

    public function copyQuestionsToPoolObject()
    {
        $this->copyQuestionsToPool($this->testrequest->raw('q_id'), $this->testrequest->raw('sel_qpl'));
        $this->ctrl->redirect($this, 'questions');
    }

    /**
     *
     * @param<int> array $question_ids
     */
    public function copyQuestionsToPool(array $question_ids, int $qpl_id): stdClass
    {
        $new_ids = [];
        foreach ($question_ids as $q_id) {
            $new_id = $this->copyQuestionToPool($q_id, $qpl_id);
            $new_ids[$q_id] = $new_id;
        }

        $result = new stdClass();
        $result->ids = $new_ids;
        $result->qpoolid = $qpl_id;

        return $result;
    }

    public function copyQuestionToPool(int $source_question_id, int $target_parent_id)
    {
        $question_gui = assQuestion::instantiateQuestionGUI($source_question_id);

        $new_title = $question_gui->object->getTitle();
        if ($this->questioninfo->questionTitleExistsInPool($target_parent_id, $question_gui->object->getTitle())) {
            $counter = 2;
            while ($this->questioninfo->questionTitleExistsInPool($target_parent_id, $question_gui->object->getTitle() . " ($counter)")) {
                $counter++;
            }
            $new_title = $question_gui->object->getTitle() . " ($counter)";
        }

        return $question_gui->object->createNewOriginalFromThisDuplicate($target_parent_id, $new_title);
    }

    /**
     * @global ilObjectDataCache $ilObjDataCache
     */
    public function copyAndLinkQuestionsToPoolObject(
        int $ref_id = null,
        array $question_ids = []
    ) {
        if ($ref_id == null) {
            if ($this->testrequest->int('sel_qpl') === 0) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("questionpool_not_selected"));
                //$this->copyAndLinkToQuestionpoolObject();
                $this->ctrl->redirect($this, 'questions');
            }
            $ref_id = $this->testrequest->int('sel_qpl');
        }

        $qpl_id = $this->obj_data_cache->lookupObjId($ref_id);


        if ($question_ids === []) {
            $question_ids = $this->testrequest->getQuestionIds();
            $question_id = $this->testrequest->getQuestionId();
            if ($question_ids === [] && $question_id !== 0) {
                $question_ids = [$question_id];
            }
        }

        if ($question_ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("tst_no_question_selected_for_moving_to_qpl"));
            $this->ctrl->redirect($this, 'questions');
        }

        $result = $this->copyQuestionsToPool($question_ids, $qpl_id);

        foreach ($result->ids as $oldId => $newId) {
            $questionInstance = assQuestion::instantiateQuestion($oldId);

            $original_question_id = $questionInstance->getOriginalId();
            if ($original_question_id !== null
                && $this->questioninfo->originalQuestionExists($original_question_id)) {
                $oldOriginal = assQuestion::instantiateQuestion($original_question_id);
                $oldOriginal->delete($oldOriginal->getId());
            }
            assQuestion::saveOriginalId($questionInstance->getId(), $newId);
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_qst_added_to_pool_' . (count($result->ids) > 1 ? 'p' : 's')), true);
        $this->ctrl->redirect($this, 'questions');
    }

    private function getQuestionpoolCreationForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setRequired(true);
        $form->addItem($title);

        $description = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $form->addItem($description);

        $form->addCommandButton('createQuestionPoolAndCopy', $this->lng->txt('create'));

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
        $ids = [];
        if ($this->testrequest->isset('q_id') && is_array($this->testrequest->raw('q_id'))) {
            $ids = $this->testrequest->raw('q_id');
        }
        $this->copyAndLinkToQuestionpool($ids);
    }

    protected function copyAndLinkToQuestionpool(array $question_ids): void
    {
        if ($question_ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_no_question_selected_for_moving_to_qpl'), true);
            $this->ctrl->redirect($this, 'questions');
        }

        foreach ($question_ids as $q_id) {
            if (!$this->questioninfo->originalQuestionExists((int) $q_id)) {
                continue;
            }

            $type = ilObject::_lookupType(assQuestion::lookupParentObjId($this->questioninfo->getOriginalId((int) $q_id)));
            if ($type !== 'tst') {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_link_only_unassigned'), true);
                $this->ctrl->redirect($this, 'questions');
                return;
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
        $this->copyAndLinkQuestionsToPoolObject($ref_id);
    }

    /**
    * Called when a new question should be created from a test
    * Important: $cmd may be overwritten if no question pool is available
    *
    * @access	public
    */
    public function createQuestionpoolTargetObject($cmd)
    {
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
                    $hidden->setValue('1');
                    $form->addItem($hidden);
                    break;
            }
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function getTargetQuestionpoolForm($questionpools, string $cmd): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->addCommandButton($cmd, $this->lng->txt('submit'));
        $form->addCommandButton('cancelCreateQuestion', $this->lng->txt('cancel'));

        if (count($questionpools) == 0) {
            $form->setTitle($this->lng->txt("tst_enter_questionpool"));

            $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
            $title->setRequired(true);
            $form->addItem($title);

            $description = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
            $form->addItem($description);
        } else {
            $form->setTitle($this->lng->txt("tst_select_questionpool"));

            $selectOptions = [
                '' => $this->lng->txt('please_select')
            ];

            foreach ($questionpools as $key => $value) {
                $selectOptions[$key] = $value["title"];
            }

            $select = new ilSelectInputGUI($this->lng->txt('tst_source_question_pool'), 'sel_qpl');
            $select->setRequired(true);
            $select->setOptions($selectOptions);

            $form->addItem($select);
        }

        if($this->table->getTableCommand()) {
            $question_ids = $this->table->getRowIds();
        } elseif ($this->testrequest->isset('q_id') && is_array($this->testrequest->raw('q_id'))) {
            $question_ids = $this->testrequest->raw('q_id');
        }

        foreach ($question_ids as $id) {
            $hidden = new ilHiddenInputGUI('q_id[]');
            $hidden->setValue((string) $id);
            $form->addItem($hidden);
        }

        return $form;
    }

    protected function protectByWritePermission(): void
    {
        if (!$this->access->checkAccess("write", "", $this->ref_id)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }
    }

    /**
     * @param array<int,int> $order $question_fi=>$position
     */
    protected function storeOrder(array $order): void
    {
        $this->object->setQuestionOrderAndObligations($order, []);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'questions');
    }

    protected function redirectWithQuestionParameters(
        int $question_id,
        string $target_class,
        string $cmd
    ): void {

        $this->ctrl->setParameterByClass(
            $target_class,
            'q_id',
            $question_id
        );

        $this->ctrl->setParameterByClass(
            $target_class,
            'calling_test',
            (string) $this->ref_id
        );

        $this->ctrl->redirectByClass($target_class, $cmd);
    }

    public function saveOrderAndObligationsObject()
    {
        if (!$this->access->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }

        $order = $this->testrequest->raw('order') ?? [];
        $obligatory_questions = $this->testrequest->raw('obligatory') ?? [];

        foreach ($order as $question_id => $order) {
            $orders[$question_id] = $order;
        }

        if ($this->object->areObligationsEnabled()) {
            foreach ($obligatory_questions as $question_id => $obligation) {
                if (!ilObjTest::isQuestionObligationPossible($question_id)) {
                    unset($obligatory_questions[$question_id]);
                }
            }
        }

        $this->object->setQuestionOrderAndObligations(
            $orders,
            $obligatory_questions
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'questions');
    }

    /**
     * Move current page
     */
    protected function movePageFormObject()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "movePage"));
        $form->setTitle($this->lng->txt("test_move_page"));

        $old_pos = new ilHiddenInputGUI("q_id");
        $old_pos->setValue($this->testrequest->raw('q_id'));
        $form->addItem($old_pos);

        $questions = $this->object->getQuestionTitlesAndIndexes();
        if (!is_array($questions)) {
            $questions = [];
        }

        foreach ($questions as $k => $q) {
            if ($k == $this->testrequest->raw('q_id')) {
                unset($questions[$k]);
                continue;
            }
            $questions[$k] = $this->lng->txt('behind') . ' ' . $q;
        }

        $options = [
            0 => $this->lng->txt('first')
        ];
        foreach ($questions as $k => $q) {
            $options[$k] = $q . ' [' . $this->lng->txt('question_id_short') . ': ' . $k . ']';
        }

        $pos = new ilSelectInputGUI($this->lng->txt("position"), "position_after");
        $pos->setOptions($options);
        $form->addItem($pos);

        $form->addCommandButton("movePage", $this->lng->txt("submit"));
        $form->addCommandButton("showPage", $this->lng->txt("cancel"));

        return $this->tpl->setContent($form->getHTML());
    }

    public function movePageObject()
    {
        if (!$this->access->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirect($this, "infoScreen");
        }

        $this->object->moveQuestionAfter($this->testrequest->raw('q_id'), $this->testrequest->raw('position_after'));
        $this->showPageObject();
    }

    public function showPageObject()
    {
        $this->ctrl->setParameterByClass('iltestexpresspageobjectgui', 'q_id', $this->testrequest->raw('q_id'));
        $this->ctrl->redirectByClass('iltestexpresspageobjectgui', 'showPage');
    }

    public function copyQuestionObject(): void
    {
        $this->protectByWritePermission();

        $ids = [];
        if ($this->testrequest->hasQuestionId() && !is_array($this->testrequest->raw('q_id'))) {
            $ids = [$this->testrequest->getQuestionId()];
        } elseif ($this->testrequest->getQuestionIds()) {
            $ids = $this->testrequest->getQuestionIds();
        }
        $this->copyQuestions($ids);
    }

    protected function copyQuestions(array $question_ids): void
    {
        if ($question_ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('copy_no_questions_selected'), true);
            $this->ctrl->redirect($this, 'questions');
        }

        $copy_count = 0;

        $questionTitles = $this->object->getQuestionTitles();

        foreach ($question_ids as $id) {
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

                $this->object->insertQuestion($this->test_question_set_config_factory->getQuestionSetConfig(), $new_id, true);

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

        $this->objective_oriented_container->setObjId($container_obj_id);
        $this->objective_oriented_container->setRefId($container_ref_id);
    }

    protected function getObjectiveOrientedContainer(): ilTestObjectiveOrientedContainer
    {
        return $this->objective_oriented_container;
    }

    private function getTestScreenGUIInstance(): ilTestScreenGUI
    {
        return new ilTestScreenGUI(
            $this->object,
            $this->user,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->refinery,
            $this->ctrl,
            $this->tpl,
            $this->http,
            $this->tabs_gui,
            $this->access,
            $this->test_access,
            $this->db,
            $this->rbac_system
        );
    }

    private function getQuestionPoolLinkBuilder(): \Closure
    {
        return function (?int $qpl_id): string {
            if (!$qpl_id || ilObject::_lookupType($qpl_id) !== 'qpl') {
                return $this->lng->txt('tst_question_not_from_pool_info');
            } else {
                $title = ilObject::_lookupTitle($qpl_id);
            }

            return $this->buildPossiblyLinkedQuestonPoolTitle(
                $this->ctrl,
                $this->access,
                $this->lng,
                $this->ui_factory,
                $this->ui_renderer,
                $qpl_id,
                $title
            );
        };
    }

    protected function getDeleteConfirmation(array $row_ids): string
    {
        $items = [];
        foreach ($row_ids as $id) {
            $qdata = $this->object->getQuestionDataset($id);
            $type = $this->lng->txt(
                $this->questioninfo->getQuestionType($qdata->question_type_fi)
            );
            $items[] = [$id, $qdata->title, $type];
        }
        return $this->ui_renderer->renderAsync(
            $this->table->getDeleteConfirmation($items)
        );
    }
}
