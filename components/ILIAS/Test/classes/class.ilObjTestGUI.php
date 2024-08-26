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

use ILIAS\Test\TestDIC;
use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\Questions\QuestionPoolLinkedTitleBuilder;
use ILIAS\Test\Questions\QuestionsTable;
use ILIAS\Test\Questions\QuestionsTableQuery;
use ILIAS\Test\Settings\MainSettings\SettingsMainGUI;
use ILIAS\Test\Settings\ScoreReporting\SettingsScoringGUI;
use ILIAS\Test\Scoring\Settings\Settings as SettingsScoring;
use ILIAS\Test\Settings\ScoreReporting\SettingsResultSummary;
use ILIAS\Test\Scoring\Marks\MarkSchemaGUI;
use ILIAS\Test\Scoring\Manual\TestScoringByQuestionGUI;
use ILIAS\Test\Scoring\Manual\TestScoringByParticipantGUI;
use ILIAS\Test\Logging\LogTable;
use ILIAS\Test\Logging\TestQuestionAdministrationInteractionTypes;
use ILIAS\Test\Logging\TestAdministrationInteractionTypes;
use ILIAS\Test\Presentation\TestScreenGUI;
use ILIAS\Test\ExportImport\Factory as ExportImportFactory;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\TestQuestionPool\RequestDataCollector as QPLRequestDataCollector;
use ILIAS\TestQuestionPool\Import\TestQuestionsImportTrait;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\Component\Input\Field\Select;
use ILIAS\UI\Component\Input\Field\Radio;
use ILIAS\UI\Component\Input\Field\SwitchableGroup;
use ILIAS\GlobalScreen\Services as GlobalScreen;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\Skill\Service\SkillService;
use ILIAS\ResourceStorage\Services as IRSS;

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
 * @ilCtrl_Calls ilObjTestGUI: ilLearningProgressGUI, ILIAS\Test\Scoring\Marks\MarkSchemaGUI
 * @ilCtrl_Calls ilObjTestGUI: ilTestEvaluationGUI, ilParticipantsTestResultsGUI
 * @ilCtrl_Calls ilObjTestGUI: ilAssGenFeedbackPageGUI, ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilObjTestGUI: ilInfoScreenGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjTestGUI: ILIAS\Test\Presentation\TestScreenGUI
 * @ilCtrl_Calls ilObjTestGUI: ilRepositorySearchGUI, ilTestExportGUI
 * @ilCtrl_Calls ilObjTestGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: assOrderingQuestionGUI, assImagemapQuestionGUI, assNumericGUI, assErrorTextGUI
 * @ilCtrl_Calls ilObjTestGUI: ILIAS\Test\Scoring\Manual\TestScoringByQuestionGUI, ILIAS\Test\Scoring\Manual\TestScoringByParticipantGUI
 * @ilCtrl_Calls ilObjTestGUI: assTextSubsetGUI, assOrderingHorizontalGUI
 * @ilCtrl_Calls ilObjTestGUI: assSingleChoiceGUI, assFileUploadGUI, assTextQuestionGUI
 * @ilCtrl_Calls ilObjTestGUI: assKprimChoiceGUI, assLongMenuGUI
 * @ilCtrl_Calls ilObjTestGUI: ilEditClipboardGUI
 * @ilCtrl_Calls ilObjTestGUI: ILIAS\Test\Settings\MainSettings\SettingsMainGUI, ILIAS\Test\Settings\ScoreReporting\SettingsScoringGUI
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
    use QuestionPoolLinkedTitleBuilder;

    public const SHOW_QUESTIONS_CMD = 'showQuestions';
    private const SHOW_LOGS_CMD = 'history';

    private const INFO_SCREEN_CHILD_CLASSES = [
        'ilpublicuserprofilegui', 'ilobjportfoliogui'
    ];

    private const QUESTION_CREATION_POOL_SELECTION_NO_POOL = 1;
    private const QUESTION_CREATION_POOL_SELECTION_NEW_POOL = 2;
    private const QUESTION_CREATION_POOL_SELECTION_EXISTING_POOL = 3;

    private ilTestQuestionSetConfigFactory $test_question_set_config_factory;
    private ilTestPlayerFactory $test_player_factory;
    private ilTestSessionFactory $test_session_factory;
    private ExportImportFactory $export_factory;
    private GeneralQuestionPropertiesRepository $questionrepository;
    private QPLRequestDataCollector $qplrequest;
    protected ?ilTestTabsManager $tabs_manager = null;
    private ilTestObjectiveOrientedContainer $objective_oriented_container;
    protected ilTestAccess $test_access;
    protected ilNavigationHistory $navigation_history;
    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;
    protected ilDBInterface $db;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected ilUIService $ui_service;
    protected HTTPServices $http;
    protected ilHelpGUI $help;
    protected GlobalScreen $global_screen;
    protected ilObjectDataCache $obj_data_cache;
    protected SkillService $skills_service;
    protected IRSS $irss;
    private Archives $archives;
    protected RequestDataCollector $testrequest;
    protected ?QuestionsTableQuery $table_query = null;
    protected DataFactory $data_factory;

    protected bool $create_question_mode;

    /**
     * Constructor
     * @access public
     * @param mixed|null $refId
     */
    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->navigation_history = $DIC['ilNavigationHistory'];
        $this->component_repository = $DIC['component.repository'];
        $this->component_factory = $DIC['component.factory'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->ui_service = $DIC->uiService();
        $this->http = $DIC['http'];
        $this->error = $DIC['ilErr'];
        $this->db = $DIC['ilDB'];
        $this->help = $DIC['ilHelp'];
        $this->global_screen = $DIC['global_screen'];
        $this->obj_data_cache = $DIC['ilObjDataCache'];
        $this->irss = $DIC['resource_storage'];
        $this->skills_service = $DIC->skills();
        $this->archives = $DIC->archives();
        $this->type = 'tst';
        $this->data_factory = new DataFactory();

        $local_dic = TestDIC::dic();
        $this->questionrepository = $local_dic['question.general_properties.repository'];
        $this->qplrequest = $local_dic['question.request_data_wrapper'];
        $this->testrequest = $local_dic['request_data_collector'];
        $this->export_factory = $local_dic['exportimport.factory'];

        $ref_id = 0;
        if ($this->testrequest->hasRefId() && is_numeric($this->testrequest->getRefId())) {
            $ref_id = $this->testrequest->getRefId();
        }
        parent::__construct("", $ref_id, true, false);

        $this->ctrl->saveParameter($this, ['ref_id', 'test_ref_id', 'q_id']);

        $this->lng->loadLanguageModule('assessment');

        $this->objective_oriented_container = new ilTestObjectiveOrientedContainer();

        if (!($this->object instanceof ilObjTest)) {
            $this->setCreationMode(true);
            return;
        }

        $this->test_question_set_config_factory = new ilTestQuestionSetConfigFactory(
            $this->tree,
            $this->db,
            $this->lng,
            $this->getTestObject()->getTestlogger(),
            $this->component_repository,
            $this->getTestObject(),
            $this->questionrepository
        );

        $this->test_player_factory = new ilTestPlayerFactory($this->getTestObject());
        $this->test_session_factory = new ilTestSessionFactory($this->getTestObject(), $this->db, $this->user);
        $this->setTestAccess(new ilTestAccess($this->ref_id, $this->getTestObject()->getTestId()));

        $tabs_manager = new ilTestTabsManager(
            $this->user,
            $this->tabs_gui,
            $this->lng,
            $this->ctrl,
            $this->request_wrapper,
            $this->refinery,
            $this->rbac_review,
            $this->access,
            $this->test_access,
            $this->objective_oriented_container
        );
        $tabs_manager->setTestOBJ($this->getTestObject());
        $tabs_manager->setTestSession($this->test_session_factory->getSession());
        $tabs_manager->setTestQuestionSetConfig($this->test_question_set_config_factory->getQuestionSetConfig());
        $this->setTabsManager($tabs_manager);
    }

    /**
    * execute command
    */
    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd('testScreen');

        $table_query = $this->getQuestionsTableQuery();
        if ($table_cmd = $table_query->getQueryCommand()) {
            $row_ids = $table_query->getRowIds($this->object);
            $this->getTable()->handleCommand(
                $table_cmd,
                $row_ids,
                fn(string $func, array $args) => $this->$func(...$args)
            );
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
                $lti_gui = new ilLTIProviderObjectSettingGUI($this->getTestObject()->getRefId());
                $lti_gui->setCustomRolesForSelection($this->rbac_review->getLocalRoles($this->getTestObject()->getRefId()));
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
                    $this->export_factory,
                    $this->getTestObject()->getTestlogger(),
                    $this->obj_data_cache,
                    $this->user,
                    $this->tabs_gui,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->component_repository,
                    $this->component_factory,
                    $this->irss,
                    new ilTestHTMLGenerator(),
                    $selected_files,
                    $this->questionrepository,
                    $this->testrequest
                );
                $this->ctrl->forwardCommand($export_gui);
                break;

            case strtolower(ilInfoScreenGUI::class):
                if (
                    !$this->access->checkAccess('read', '', $this->testrequest->getRefId())
                    && !$this->access->checkAccess('visible', '', $this->testrequest->getRefId())
                ) {
                    $this->redirectAfterMissingRead();
                }

                $this->prepareOutput();
                $this->addHeaderAction();
                $this->forwardToInfoScreen();
                break;

            case strtolower(TestScreenGUI::class):
                if (!$this->access->checkAccess('read', '', $this->testrequest->getRefId()) && !$this->access->checkAccess('visible', '', $this->testrequest->getRefId())) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->ctrl->forwardCommand($this->getTestScreenGUIInstance());
                break;

            case 'ilobjectmetadatagui':
                if (!$this->access->checkAccess('write', '', $this->getTestObject()->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->prepareOutput();
                $this->addHeaderAction();
                $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_META_DATA);
                $md_gui = new ilObjectMetaDataGUI($this->getTestObject());
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'iltestdashboardgui':
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }

                if (!$this->ctrl->isAsynch()) {
                    $this->prepareOutput();
                    $this->addHeaderAction();
                }

                $gui = new ilTestDashboardGUI(
                    $this->getTestObject(),
                    $this->user,
                    $this->access,
                    $this->tpl,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->ui_service,
                    $this->data_factory,
                    $this->lng,
                    $this->db,
                    $this->ctrl,
                    $this->tabs_gui,
                    $this->toolbar,
                    $this->component_factory,
                    $this->export_factory,
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
                    $this->getTestObject()->getTestlogger(),
                    $this->component_repository,
                    $this->tabs_gui,
                    $this->toolbar,
                    $this->tpl,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->skills_service,
                    $this->questionrepository,
                    $this->testrequest,
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
                if (!$this->getTestObject()->getKioskMode()) {
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
                if (!$this->getTestObject()->getKioskMode()) {
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

            case "iltestservicegui":
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $serviceGUI = new ilTestServiceGUI($this->getTestObject());
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
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $this->getTestObject()->getRefId());
                $this->ctrl->forwardCommand($new_gui);

                break;

            case "ilcertificategui":
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();

                $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_SETTINGS);

                $gui_factory = new ilCertificateGUIFactory();
                $output_gui = $gui_factory->create($this->getTestObject());

                $this->ctrl->forwardCommand($output_gui);
                break;

            case strtolower(TestScoringByQuestionGUI::class):
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $output_gui = new TestScoringByQuestionGUI($this->getTestObject());
                $output_gui->setTestAccess($this->getTestAccess());
                $this->ctrl->forwardCommand($output_gui);
                break;

            case strtolower(TestScoringByParticipantGUI::class):
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $output_gui = new TestScoringByParticipantGUI($this->getTestObject());
                $output_gui->setTestAccess($this->getTestAccess());
                $this->ctrl->forwardCommand($output_gui);
                break;

            case strtolower(MarkSchemaGUI::class):
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->protectByWritePermission();

                $this->prepareOutput();
                $this->addHeaderAction();
                $mark_schema_gui = new MarkSchemaGUI(
                    $this->getTestObject(),
                    $this->user,
                    $this->lng,
                    $this->ctrl,
                    $this->tpl,
                    $this->toolbar,
                    $this->tabs_gui,
                    $this->getObject()->getTestLogger(),
                    $this->post_wrapper,
                    $this->request_wrapper,
                    $this->request,
                    $this->refinery,
                    $this->ui_factory,
                    $this->ui_renderer
                );
                $this->ctrl->forwardCommand($mark_schema_gui);
                break;

            case strtolower(SettingsMainGUI::class):
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }

                $this->addHeaderAction();
                $gui = new SettingsMainGUI(
                    $this->tpl,
                    $this->tabs_gui,
                    $this->toolbar,
                    $this->ctrl,
                    $this->access,
                    $this->lng,
                    $this->tree,
                    $this->db,
                    $this->obj_data_cache,
                    $this->settings,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->refinery,
                    $this->request,
                    $this->component_repository,
                    $this->user,
                    $this,
                    $this->getTestObject()->getTestLogger(),
                    $this->questionrepository
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(SettingsScoringGUI::class):
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = new SettingsScoringGUI(
                    $this->ctrl,
                    $this->access,
                    $this->lng,
                    $this->tree,
                    $this->db,
                    $this->component_repository,
                    $this,
                    $this->tpl,
                    $this->tabs_gui,
                    $this->getTestObject()->getTestLogger(),
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
                ))->withContextId($this->getTestObject()->getId());
                $gui = new ilTestRandomQuestionSetConfigGUI(
                    $this->getTestObject(),
                    $this->ctrl,
                    $this->user,
                    $this->access,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->tabs_gui,
                    $this->lng,
                    $this->getTestObject()->getTestlogger(),
                    $this->tpl,
                    $this->db,
                    $this->tree,
                    $this->component_repository,
                    $this->obj_definition,
                    $this->obj_data_cache,
                    $test_process_locker_factory,
                    $this->testrequest,
                    $this->questionrepository
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
                    $this->getTestObject()->getTestlogger(),
                    $this->component_repository,
                    $this->getTestObject(),
                    $this->access,
                    $this->http,
                    $this->refinery,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->testrequest,
                    $this->questionrepository
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
                    $this->getTestObject()->getTestlogger(),
                    $this->tree,
                    $this->component_repository,
                    $this->getTestObject(),
                    $this->questionrepository,
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

            case strtolower(ilAssQuestionPreviewGUI::class):
                if (!$this->access->checkAccess('write', '', $this->getTestObject()->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->forwardCommandToQuestionPreview($cmd);
                break;
            case 'ilassquestionpagegui':
                if ($cmd === 'finishEditing') {
                    $this->forwardCommandToQuestionPreview(ilAssQuestionPreviewGUI::CMD_SHOW);
                    break;
                }
                if ((!$this->access->checkAccess("read", "", $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                if ($cmd === 'edit' && !$this->access->checkAccess('write', '', $this->testrequest->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->prepareOutput();
                $forwarder = new ilAssQuestionPageCommandForwarder(
                    $this->getTestObject(),
                    $this->lng,
                    $this->ctrl,
                    $this->tpl,
                    $this->questionrepository,
                    $this->testrequest
                );
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
                $question_gui = assQuestionGUI::_getQuestionGUI('', $this->fetchAuthoringQuestionIdParameter());
                $question = $question_gui->getObject();
                $question->setObjId($this->getTestObject()->getId());
                $question_gui->setObject($question);
                $question_gui->setQuestionTabs();
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
                if (!$this->access->checkAccess('write', '', $this->getTestObject()->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                if ($this->getTestObject()->evalTotalPersons() !== 0) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('question_is_part_of_running_test'), true);
                    $this->forwardCommandToQuestionPreview(ilAssQuestionPreviewGUI::CMD_SHOW);
                    return;
                }
                $this->prepareSubGuiOutput();

                $question_gui = assQuestionGUI::_getQuestionGUI('', $this->fetchAuthoringQuestionIdParameter());
                $question = $question_gui->getObject();
                $question->setObjId($this->getTestObject()->getId());
                $question_gui->setObject($question);
                $question_gui->setQuestionTabs();

                $gui = new ilAssQuestionHintsGUI($question_gui);

                $gui->setEditingEnabled(
                    $this->access->checkAccess('write', '', $this->getTestObject()->getRefId())
                );

                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilassquestionfeedbackeditinggui':
                if (!$this->access->checkAccess('write', '', $this->getTestObject()->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->prepareSubGuiOutput();

                // set return target
                $this->ctrl->setReturnByClass(self::class, self::SHOW_QUESTIONS_CMD);
                $question_gui = assQuestionGUI::_getQuestionGUI('', $this->fetchAuthoringQuestionIdParameter());
                $question = $question_gui->getObject();
                $question->setObjId($this->getTestObject()->getId());
                $question_gui->setObject($question);
                $question_gui->setQuestionTabs();

                if ($this->getTestObject()->evalTotalPersons() !== 0) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("question_is_part_of_running_test"), true);
                    $this->forwardCommandToQuestionPreview(ilAssQuestionPreviewGUI::CMD_SHOW);
                }
                $gui = new ilAssQuestionFeedbackEditingGUI(
                    $question_gui,
                    $this->ctrl,
                    $this->access,
                    $this->tpl,
                    $this->tabs_gui,
                    $this->lng,
                    $this->help,
                    $this->qplrequest
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestcorrectionsgui':
                if ((!$this->access->checkAccess('read', '', $this->testrequest->getRefId()))) {
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
                    $this->getTestObject()->getTestLogger(),
                    $this->testrequest,
                    $this->getTestObject(),
                    $this->user,
                    $this->questionrepository,
                    $this->getTable()
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
                if ($cmd === 'properties'
                    && ($this->testrequest->raw('browse')
                        || $this->testrequest->raw('up')
                        || $this->testrequest->raw('down'))) {
                    $this->showQuestionsObject();
                    return;
                }

                if ($cmd === 'testScreen') {
                    $this->ctrl->forwardCommand($this->getTestScreenGUIInstance());
                    return;
                }

                $local_cmd = $cmd . 'Object';
                if (!method_exists($this, $local_cmd)) {
                    $local_cmd = self::SHOW_QUESTIONS_CMD . 'Object';
                }
                $this->$local_cmd();
                break;

            default:
                if ((!$this->access->checkAccess('read', '', $this->testrequest->getRefId()))) {
                    $this->redirectAfterMissingRead();
                }
                if (in_array(
                    $cmd,
                    ['editQuestion', 'previewQuestion', 'save', 'saveReturn',
                            'syncQuestion', 'syncQuestionReturn', 'suggestedsolution']
                )
                    && !$this->access->checkAccess('write', '', $this->getTestObject()->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                if (in_array($cmd, ['editQuestion', 'save', 'saveReturn', 'suggestedsolution'])
                    && $this->getTestObject()->evalTotalPersons() !== 0) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("question_is_part_of_running_test"), true);
                    $this->forwardCommandToQuestionPreview(ilAssQuestionPreviewGUI::CMD_SHOW);
                    return;
                }
                $this->forwardCommandToQuestion($cmd);
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
        $target_class = get_class($this->getTestObject()) . "GUI";
        $this->ctrl->setParameterByClass($target_class, 'ref_id', $this->ref_id);
        $this->ctrl->redirectByClass($target_class);
    }

    protected function redirectAfterMissingRead(): void
    {
        $this->tpl->setOnScreenMessage('failure', sprintf(
            $this->lng->txt("msg_no_perm_read_item"),
            $this->getTestObject()->getTitle()
        ), true);
        $this->ctrl->setParameterByClass('ilrepositorygui', 'ref_id', ROOT_FOLDER_ID);
        $this->ctrl->redirectByClass('ilrepositorygui');
    }

    protected function forwardCommandToQuestionPreview(
        string $cmd,
        assQuestionGUI $question_gui = null
    ): void {
        $this->prepareOutput();

        $this->ctrl->saveParameter($this, 'q_id');
        $gui = new ilAssQuestionPreviewGUI(
            $this->ctrl,
            $this->rbac_system,
            $this->tabs_gui,
            $this->tpl,
            $this->lng,
            $this->db,
            $this->refinery->random(),
            $this->global_screen,
            $this->http,
            $this->refinery,
            $this->getTestObject()->evalTotalPersons() === 0
        );

        $gui->initQuestion(
            $question_gui ?? assQuestion::instantiateQuestionGUI($this->fetchAuthoringQuestionIdParameter()),
            $this->getTestObject()->getId()
        );
        $gui->initPreviewSettings($this->getTestObject()->getRefId());
        $gui->initPreviewSession($this->user->getId(), $this->testrequest->getQuestionId());
        $gui->initHintTracking();
        $gui->initStyleSheets();
        $this->tabs_gui->setBackTarget($this->lng->txt('backtocallingtest'), $this->ctrl->getLinkTargetByClass(self::class, self::SHOW_QUESTIONS_CMD));

        $gui->{$cmd . 'Cmd'}();
    }

    protected function forwardCommandToQuestion(string $cmd): void
    {
        $this->create_question_mode = true;
        $this->prepareOutput();

        try {
            $qid = $this->fetchAuthoringQuestionIdParameter();

            $this->ctrl->setReturnByClass(self::class, self::SHOW_QUESTIONS_CMD);

            $question_gui = assQuestionGUI::_getQuestionGUI(
                ilUtil::stripSlashes($this->testrequest->strVal('question_type')),
                $qid
            );

            $question_gui->setEditContext(assQuestionGUI::EDIT_CONTEXT_AUTHORING);
            $question = $question_gui->getObject();
            $question->setObjId($this->getTestObject()->getId());
            $question_gui->setObject($question);
            $question_gui->setContextAllowsSyncToPool(true);
            $question_gui->setQuestionTabs();

            $target = strpos($cmd, 'Return') === false ? 'stay' : 'return';

            if (in_array($cmd, ['syncQuestion', 'syncQuestionReturn'])) {
                $question_gui->syncQuestion();
                $this->showNextViewAfterQuestionSave($question_gui, $target);
                return;
            }

            if ($question_gui->isSaveCommand()
                || $question_gui->cmdNeedsExistingQuestion($cmd)) {
                $question_gui = $this->addPostCreationTasksToQuestionGUI($question_gui);
            }

            if ($qid === 0 && $question_gui->cmdNeedsExistingQuestion($cmd)) {
                $question_gui->getObject()->createNewQuestion();
                $question_gui->setQuestionTabs();
                $this->executeAfterQuestionCreationTasks($question_gui);
            }

            if (!$question_gui->isSaveCommand()) {
                $this->ctrl->forwardCommand($question_gui);
                return;
            }

            if (!$question_gui->saveQuestion()) {
                return;
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            if ($qid === 0) {
                $this->executeAfterQuestionCreationTasks($question_gui);
            }
            $this->executeAfterQuestionSaveTasks($question_gui);
            $this->showNextViewAfterQuestionSave($question_gui, $target);
        } catch (ilTestException $e) {
            $this->showQuestionsObject();
        }
    }

    private function addPostCreationTasksToQuestionGUI(
        assQuestionGUI $question_gui
    ): assQuestionGUI {
        if ($this->testrequest->isset('move_after_question_with_id')) {
            $question_gui->setMoveAfterQuestionId(
                $this->testrequest->int('move_after_question_with_id')
            );
        }

        if ($this->testrequest->isset('pool_title')) {
            $question_gui->setCopyToNewPoolOnSave(
                $this->testrequest->strVal('pool_title')
            );
        }

        if ($this->testrequest->isset('pool_ref')) {
            $question_gui->setCopyToExistingPoolOnSave(
                $this->testrequest->int('pool_ref')
            );
        }
        return $question_gui;
    }

    private function executeAfterQuestionSaveTasks(assQuestionGUI $question_gui): void
    {
        if ($this->getTestObject()->getTestLogger()->isLoggingEnabled()) {
            $this->getTestObject()->getTestLogger()->logQuestionAdministrationInteraction(
                $question_gui->getObject()->toQuestionAdministrationInteraction(
                    $this->getTestObject()->getTestLogger()->getAdditionalInformationGenerator(),
                    $this->getTestObject()->getRefId(),
                    TestQuestionAdministrationInteractionTypes::QUESTION_MODIFIED
                )
            );
        }
    }

    private function executeAfterQuestionCreationTasks(assQuestionGUI $question_gui): void
    {
        if ($this->getTestObject()->getQuestionSetType() === ilObjTest::QUESTION_SET_TYPE_FIXED
            && !in_array($question_gui->getObject()->getId(), $this->getTestObject()->getQuestions())) {
            $this->getTestObject()->insertQuestion($question_gui->getObject()->getId(), true);
        }

        if ($question_gui->getMoveAfterQuestionId() !== null) {
            $this->getTestObject()->moveQuestions(
                [$question_gui->getObject()->getId()],
                $question_gui->getMoveAfterQuestionId() === 0
                    ? $this->getTestObject()->getQuestions()[1]
                    : $question_gui->getMoveAfterQuestionId(),
                $question_gui->getMoveAfterQuestionId() === 0 ? 0 : 1
            );
            $question_gui->setMoveAfterQuestionId(null);
        }

        if ($question_gui->getCopyToExistingPoolOnSave() !== null) {
            $original_id = $this->copyQuestionToPool(
                $question_gui,
                new ilObjQuestionPool($question_gui->getCopyToExistingPoolOnSave())
            );
            assQuestion::saveOriginalId($question_gui->getObject()->getId(), $original_id);
            $question_gui->setCopyToExistingPoolOnSave(null);
        }

        if ($question_gui->getCopyToNewPoolOnSave() !== null) {
            $question_pool = $this->createQuestionPool($question_gui->getCopyToNewPoolOnSave());
            $original_id = $this->copyQuestionToPool(
                $question_gui,
                $question_pool
            );
            assQuestion::saveOriginalId($question_gui->getObject()->getId(), $original_id);
            $question_gui->setCopyToNewPoolOnSave(null);
        }
    }

    private function showNextViewAfterQuestionSave(assQuestionGUI $question_gui, string $target): void
    {
        if ($target === 'return') {
            $this->forwardCommandToQuestionPreview(
                ilAssQuestionPreviewGUI::CMD_SHOW,
                $question_gui
            );
        }

        if ($target === 'stay') {
            $this->ctrl->setParameterByClass(ilAssQuestionPreviewGUI::class, 'q_id', $question_gui->getObject()->getId());
            $this->tabs_gui->setBackTarget(
                $this->lng->txt('backtocallingpage'),
                $this->ctrl->getLinkTargetByClass(
                    ilAssQuestionPreviewGUI::class,
                    ilAssQuestionPreviewGUI::CMD_SHOW
                )
            );
            $question_gui->editQuestion(false, false);
        }
    }

    protected function trackTestObjectReadEvent()
    {
        ilChangeEvent::_recordReadEvent(
            $this->getTestObject()->getType(),
            $this->getTestObject()->getRefId(),
            $this->getTestObject()->getId(),
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

        if ($qid === 0 || $this->getTestObject()->checkQuestionParent($qid)) {
            return $qid;
        }

        throw new ilTestException('question id does not relate to parent object!');
    }

    private function questionsTabGatewayObject()
    {
        if ($this->getTestObject()->isRandomTest()) {
            $this->ctrl->redirectByClass('ilTestRandomQuestionSetConfigGUI');
        }

        $this->ctrl->redirectByClass('ilObjTestGUI', self::SHOW_QUESTIONS_CMD);
    }

    public function prepareOutput(bool $show_subobjects = true): bool
    {
        if (!$this->getCreationMode()) {
            $settings = ilMemberViewSettings::getInstance();
            if ($settings->isActive() && $settings->getContainer() != $this->getTestObject()->getRefId()) {
                $settings->setContainer($this->getTestObject()->getRefId());
                $this->rbac_system->initMemberView();
            }
        }
        return parent::prepareOutput($show_subobjects);
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
        $this->tabs_gui->clearTargets();

        $this->prepareOutput();
        $this->addHeaderAction();
        $gui = new ilParticipantsTestResultsGUI(
            $this->ctrl,
            $this->lng,
            $this->db,
            $this->user,
            $this->tabs_gui,
            $this->toolbar,
            $this->tpl,
            $this->ui_factory,
            $this->ui_renderer,
            new ilTestParticipantAccessFilterFactory($this->access),
            $this->questionrepository,
            $this->testrequest,
            $this->http,
            $this->refinery
        );
        $gui->setTestObj($this->getTestObject());
        $gui->setQuestionSetConfig($this->test_question_set_config_factory->getQuestionSetConfig());
        $gui->setObjectiveParent(new ilTestObjectiveOrientedContainer());
        $gui->setTestAccess($this->getTestAccess());
        $this->tabs_gui->activateTab('results');
        $this->ctrl->forwardCommand($gui);
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
            $page_id = $this->getTestObject()->getIntroductionPageId();
        } else {
            $page_type = 'ConcludingRemarksPage';
            $this->tabs_manager->activateSubTab(ilTestTabsManager::SETTINGS_SUBTAB_ID_EDIT_CONCLUSION_PAGE);
            $page_id = $this->getTestObject()->getConcludingRemarksPageId();
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

    private function redirectTo_SettingsMainGUI_showForm_Object()
    {
        $this->ctrl->redirectByClass(SettingsMainGUI::class, SettingsMainGUI::CMD_SHOW_FORM);
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
        $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
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

        ilSession::set('path_to_import_file', $file_to_import);
        ilSession::set('path_to_uploaded_file_in_temp_dir', $path_to_uploaded_file_in_temp_dir);

        if ($qtiParser->getQuestionSetType() !== ilObjTest::QUESTION_SET_TYPE_FIXED
            || file_exists($this->buildResultsFilePath($importdir, $subdir))) {
            $this->importVerifiedFileObject();
            return;
        }

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

        if ($new_object->getTestLogger()->isLoggingEnabled()) {
            $new_object->getTestLogger()->logTestAdministrationInteraction(
                $new_object->getTestLogger()->getInteractionFactory()->buildTestAdministrationInteraction(
                    $this->getRefId(),
                    $this->user->getId(),
                    TestAdministrationInteractionTypes::NEW_TEST_CREATED,
                    []
                )
            );
        }

        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->ctrl->setParameter($this, 'ref_id', $new_object->getRefId());
        $this->ctrl->redirectByClass(SettingsMainGUI::class);
    }

    public function backToRepositoryObject()
    {
        $path = $this->tree->getPathFull($this->getTestObject()->getRefID());
        ilUtil::redirect($this->getReturnLocation("cancel", "./ilias.php?baseClass=ilRepositoryGUI&cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
    }

    public function getTestObject(): ?ilObjTest
    {
        return $this->object;
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
        $new_obj->setTitle('dummy');
        $new_obj->setDescription('test import');
        $new_obj->create(true);
        $new_obj->createReference();
        $new_obj->putInTree($this->testrequest->getRefId());
        $new_obj->setPermissions($this->testrequest->getRefId());

        $selected_questions = $this->retrieveSelectedQuestionsFromImportQuestionsSelectionForm(
            'importVerifiedFile',
            $importdir,
            $qtifile,
            $this->request
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
            $qti_parser = new ilQTIParser(
                $importdir,
                $qtifile,
                ilQTIParser::IL_MO_PARSE_QTI,
                $new_obj->getId(),
                $selected_questions
            );

            $results_file = $this->buildResultsFilePath($importdir, $subdir);
            if (!file_exists($results_file) && $selected_questions === []) {
                $qti_parser->setIgnoreItemsEnabled(true);
            }
            $qti_parser->setTestObject($new_obj);
            $qti_parser->startParsing();
            $new_obj->saveToDb();
            $question_page_parser = new ilQuestionPageParser(
                $new_obj,
                $xmlfile,
                $importdir
            );
            $question_page_parser->setQuestionMapping($qti_parser->getImportMapping());
            $question_page_parser->startParsing();

            if (file_exists($results_file)) {
                $results = new ilTestResultsImportParser(
                    $results_file,
                    $new_obj,
                    $this->db,
                    $new_obj->getTestlogger()
                );
                $results->setQuestionIdMapping($qti_parser->getQuestionIdMapping());
                $results->startParsing();
            }

            $new_obj->update();
        }


        // delete import directory
        ilFileUtils::delDir($importdir);
        $this->deleteUploadedImportFile($path_to_uploaded_file_in_temp_dir);
        ilSession::clear('path_to_import_file');
        ilSession::clear('path_to_uploaded_file_in_temp_dir');

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_imported"), true);
        $this->ctrl->setParameterByClass(ilObjTestGUI::class, 'ref_id', $new_obj->getRefId());
        $this->ctrl->redirectByClass(ilObjTestGUI::class);
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

    public function backObject()
    {
        $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
    }

    public function createQuestionPool($name = "dummy", $description = ""): ilObjQuestionPool
    {
        $parent_ref = $this->tree->getParentId($this->getTestObject()->getRefId());
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
        return $qpl;
    }

    public function createQuestionObject(): void
    {
        $this->ctrl->setReturnByClass(self::class, self::SHOW_QUESTIONS_CMD);

        $form = $this->buildQuestionCreationForm()->withRequest($this->request);
        $data_with_section = $form->getData();
        if ($data_with_section === null) {
            $this->createQuestionFormObject($form);
            return;
        }
        $data = $data_with_section[0];

        $qpl_mode = $data['pool_selection']['qpl_type'];
        if ($qpl_mode === self::QUESTION_CREATION_POOL_SELECTION_NEW_POOL && $data['pool_selection']['title'] === ''
            || $qpl_mode === self::QUESTION_CREATION_POOL_SELECTION_EXISTING_POOL && $data['pool_selection']['pool_ref_id'] === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("questionpool_not_entered"));
            $this->createQuestionFormObject($form);
            return;
        }

        /** @var assQuestionGUI $question_gui */
        $question_gui = assQuestionGUI::_getQuestionGUI(
            ilObjQuestionPool::getQuestionTypeByTypeId($data['question_type'])
        );
        $question_gui->setEditContext(assQuestionGUI::EDIT_CONTEXT_AUTHORING);
        $question = $question_gui->getObject();
        $question->setAdditionalContentEditingMode($data['editing_type']);
        $question->setObjId($this->getTestObject()->getId());
        $question_gui->setObject($question);
        $question_gui->setQuestionTabs();

        if (array_key_exists('position', $data)) {
            $question_gui->setMoveAfterQuestionId($data['position']);
        }

        if ($qpl_mode === self::QUESTION_CREATION_POOL_SELECTION_NEW_POOL) {
            $question_gui->setCopyToNewPoolOnSave($data['pool_selection']['title']);
        }

        if ($qpl_mode === self::QUESTION_CREATION_POOL_SELECTION_EXISTING_POOL) {
            $question_gui->setCopyToExistingPoolOnSave($data['pool_selection']['pool_ref_id']);
        }

        $question_gui->editQuestion();
    }

    public function cancelCreateQuestionObject(): void
    {
        $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
    }

    public function confirmRemoveQuestionsObject(array $question_ids_to_remove = []): void
    {
        if ($question_ids_to_remove === []) {
            $question_ids_to_remove = $this->testrequest->getQuestionIds();
        }

        $questions = $this->getTestObject()->getQuestionTitlesAndIndexes();

        $this->getTestObject()->removeQuestions($question_ids_to_remove);
        $this->getTestObject()->saveCompleteStatus($this->test_question_set_config_factory->getQuestionSetConfig());

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_questions_removed'), true);

        if ($this->testrequest->raw('test_express_mode')) {
            $prev = null;
            $return_to = null;
            $deleted_tmp = $question_ids_to_remove;
            $first = array_shift($deleted_tmp);
            foreach ($questions as $key => $value) {
                if (!in_array($key, $question_ids_to_remove)) {
                    $prev = $key;
                    if (!$first) {
                        $return_to = $prev;
                        break;
                    } else {
                        continue;
                    }
                } elseif ($key === $first) {
                    if ($prev !== null) {
                        $return_to = $prev;
                        break;
                    }
                    $first = array_shift($deleted_tmp);
                }
            }

            if (
                count($questions) === count($question_ids_to_remove)
                || !$return_to
            ) {
                $this->ctrl->setParameter($this, 'q_id', '');
                $this->ctrl->redirect($this, 'showPage');
            }

            $this->ctrl->setParameter($this, 'q_id', $return_to);
            $this->ctrl->redirect($this, "showPage");
            return;
        }

        $this->ctrl->setParameter($this, 'q_id', '');
        $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
    }

    public function cancelRemoveQuestionsObject()
    {
        if ($this->testrequest->raw('test_express_mode')) {
            $this->ctrl->setParameter($this, 'q_id', $this->testrequest->raw('q_id'));
            $this->ctrl->redirect($this, "showPage");
        } else {
            $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
        }
    }

    /**
    * @param array $selected_questions
    */
    public function removeQuestionsForm(array $selected_questions): void
    {
        $total = $this->getTestObject()->evalTotalPersons();
        if ($total > 0) {
            // the test was executed previously
            $question = sprintf($this->lng->txt("tst_remove_questions_and_results"), $total);
        } else {
            $question = $this->lng->txt("tst_remove_questions");
            if (count($selected_questions) === 1) {
                $question = $this->lng->txt("tst_remove_question");
            }
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($question);

        $this->ctrl->saveParameter($this, 'test_express_mode');
        $this->ctrl->saveParameter($this, 'q_id');

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelRemoveQuestions");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmRemoveQuestions");
        $removablequestions = $this->getTestObject()->getTestQuestions();
        if (count($removablequestions)) {
            foreach ($removablequestions as $data) {
                if (in_array($data["question_id"], $selected_questions)) {
                    $txt = $data["title"] . " (" . $this->questionrepository->getForQuestionId($data['question_id'])->getTypeName($this->lng) . ")";
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

        $selected_questions = $this->testrequest->raw('q_id');

        if (!is_array($selected_questions) && $selected_questions) {
            $selected_questions = [$selected_questions];
        }

        if (!is_array($selected_questions)) {
            $selected_questions = [];
        }

        if (count($selected_questions) > 0) {
            $this->removeQuestionsForm($selected_questions);
        } elseif (0 === count($selected_questions)) {
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
            $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
            return;
        }

        ilSession::set('tst_qst_move_' . $this->getTestObject()->getTestId(), $selected_questions);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_selected_for_move"), true);
        $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
    }

    /**
    * Insert checked questions before the actual selection
    */
    public function insertQuestionsBeforeObject()
    {
        // get all questions to move
        $move_questions = ilSession::get('tst_qst_move_' . $this->getTestObject()->getTestId());

        $qst_ids = $this->testrequest->getQuestionIds();
        if ($qst_ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_target_selected_for_move"), true);
            $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
        }
        if (count($qst_ids) > 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("too_many_targets_selected_for_move"), true);
            $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
        }
        $insert_mode = 0;
        $this->getTestObject()->moveQuestions(
            ilSession::get('tst_qst_move_' . $this->getTestObject()->getTestId()),
            $qst_ids[0],
            $insert_mode
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_questions_moved"), true);
        ilSession::clear('tst_qst_move_' . $this->getTestObject()->getTestId());
        $this->ctrl->redirect($this, "showQuestions");
    }

    /**
    * Insert checked questions after the actual selection
    */
    public function insertQuestionsAfterObject()
    {
        // get all questions to move
        $move_questions = ilSession::get('tst_qst_move_' . $this->getTestObject()->getTestId());
        $qst_ids = $this->testrequest->getQuestionIds();
        if ($qst_ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_target_selected_for_move"), true);
            $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
        }
        if (count($qst_ids) > 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("too_many_targets_selected_for_move"), true);
            $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
        }
        $insert_mode = 1;
        $this->getTestObject()->moveQuestions(
            ilSession::get('tst_qst_move_' . $this->getTestObject()->getTestId()),
            $qst_ids[0],
            $insert_mode
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_questions_moved"), true);
        ilSession::clear('tst_qst_move_' . $this->getTestObject()->getTestId());
        $this->ctrl->redirect($this, "showQuestions");
    }

    /**
    * Insert questions from the questionbrowser into the test
    *
    * @access	public
    */
    public function insertQuestionsObject(array $selected_array = null): void
    {
        $selected_array = $selected_array ?? $this->testrequest->getQuestionIds();
        if (!count($selected_array)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_insert_missing_question"), true);
            $this->ctrl->redirect($this, 'browseForQuestions');
        }

        $manscoring = false;
        foreach ($selected_array as $value) {
            $this->getTestObject()->insertQuestion(
                $this->test_question_set_config_factory->getQuestionSetConfig(),
                $value
            );
            if (!$manscoring) {
                $manscoring = $manscoring | assQuestion::_needsManualScoring($value);
            }
        }
        $this->getTestObject()->saveCompleteStatus($this->test_question_set_config_factory->getQuestionSetConfig());
        if ($manscoring) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('manscoring_hint'), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_questions_inserted'), true);
        }
        $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
        return;
    }

    public function createQuestionFormObject(Form $form = null): void
    {
        $this->getTabsManager()->getQuestionsSubTabs();
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);

        $sub_screen_id = ['createQuestion'];

        $this->tabs_gui->activateTab('assQuestions');
        $this->help->setScreenId('assQuestions');
        $this->help->setSubScreenId(implode('_', $sub_screen_id));

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $form ?? $this->buildQuestionCreationForm()
            )
        );
    }

    private function buildQuestionCreationForm(): Form
    {
        $inputs['question_type'] = $this->buildInputQuestionType();
        $questions = $this->getTestObject()->getQuestionTitlesAndIndexes();
        if ($questions !== []) {
            $inputs['position'] = $this->buildInputPosition($questions);
        }

        $inputs['editing_type'] = $this->buildInputEditingType();

        if ($inputs['editing_type'] instanceof Radio) {
            $sub_screen_id[] = 'editMode';
        }

        $sub_screen_id[] = 'poolSelect';

        $inputs['pool_selection'] = $this->buildInputPoolSelection();

        $section = [
            $this->ui_factory->input()->field()->section($inputs, $this->lng->txt('ass_create_question'))
        ];

        $form = $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'createQuestion'),
            $section
        )->withSubmitLabel($this->lng->txt('create'));

        return $form;
    }

    private function buildInputQuestionType(): Select
    {
        $question_types = (new ilObjQuestionPool())->getQuestionTypes(false, true, false);
        $options = [];
        foreach ($question_types as $label => $data) {
            $options[$data['question_type_id']] = $label;
        }

        return $this->ui_factory->input()->field()->select(
            $this->lng->txt('question_type'),
            $options
        )->withRequired(true);
    }

    private function buildInputPosition(array $questions): Select
    {
        $options = [0 => $this->lng->txt('first')];
        foreach ($questions as $key => $title) {
            $options[$key] = $this->lng->txt('behind') . ' ' . $title
                . ' [' . $this->lng->txt('question_id_short') . ': ' . $key . ']';
        }
        return $this->ui_factory->input()->field()->select(
            $this->lng->txt('position'),
            $options
        )->withAdditionalTransformation($this->refinery->kindlyTo()->int());
    }

    private function buildInputEditingType(): Input
    {
        if (!ilObjTestFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            return $this->ui_factory->input()->field()->hidden()->withValue(
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE
            );
        }

        return $this->ui_factory->input()->field()->radio('tst_add_quest_cont_edit_mode')
            ->withOption(
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE,
                $this->lng->txt('tst_add_quest_cont_edit_mode_IPE'),
                $this->lng->txt('tst_add_quest_cont_edit_mode_IPE_info')
            )->withOption(
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE,
                $this->lng->txt('tst_add_quest_cont_edit_mode_RTE'),
                $this->lng->txt('tst_add_quest_cont_edit_mode_RTE_info')
            )
            ->withValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE);
    }

    private function buildInputPoolSelection(): SwitchableGroup
    {
        $f = $this->ui_factory->input()->field();
        $kt = $this->refinery->kindlyTo();

        $trafo = $this->refinery->custom()->transformation(
            static function ($values) use ($kt): array {
                $return['qpl_type'] = $kt->int()->transform($values[0]);
                if ($return['qpl_type'] === self::QUESTION_CREATION_POOL_SELECTION_NO_POOL) {
                    return $return;
                }
                if ($return['qpl_type'] === self::QUESTION_CREATION_POOL_SELECTION_NEW_POOL) {
                    return $return + ['title' => $kt->string()->transform($values[1][0])];
                }
                return $return + ['pool_ref_id' => $kt->int()->transform($values[1][0])];
            }
        );

        $questionpools = ilObjQuestionPool::_getAvailableQuestionpools(false, false, true, false, false, "write");
        $pools_data = [];
        foreach ($questionpools as $key => $p) {
            $pools_data[$key] = $p['title'];
        }

        $inputs = [
            self::QUESTION_CREATION_POOL_SELECTION_NO_POOL => $f->group([], $this->lng->txt('assessment_no_pool')),
            self::QUESTION_CREATION_POOL_SELECTION_EXISTING_POOL => $f->group(
                [$f->select($this->lng->txt('select_questionpool'), $pools_data)],
                $this->lng->txt('assessment_existing_pool')
            ),
            self::QUESTION_CREATION_POOL_SELECTION_NEW_POOL => $f->group(
                [$f->text($this->lng->txt('name'))],
                $this->lng->txt('assessment_new_pool')
            )
        ];

        return $f->switchableGroup(
            $inputs,
            $this->lng->txt('assessment_pool_selection')
        )->withAdditionalTransformation($trafo)
            ->withRequired(true)
            ->withValue(1);
    }

    public function showQuestionsObject()
    {
        $this->ctrl->setParameter($this, 'test_express_mode', '');

        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_test'), true);
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
        }

        $this->getTabsManager()->getQuestionsSubTabs();
        $this->getTabsManager()->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);

        // #11631, #12994
        $this->ctrl->setParameter($this, 'q_id', '');

        if ($this->testrequest->raw("up") > 0) {
            $this->getTestObject()->questionMoveUp($this->testrequest->raw("up"));
        }
        if ($this->testrequest->raw("down") > 0) {
            $this->getTestObject()->questionMoveDown($this->testrequest->raw("down"));
        }

        if ($this->testrequest->raw("add")) {
            $selected_array = [];
            array_push($selected_array, $this->testrequest->raw("add"));
            $total = $this->getTestObject()->evalTotalPersons();
            if ($total) {
                // the test was executed previously
                $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_insert_questions"));
            }
            // @PHP8-CR This call seems to be critically important for the method, but I cannot see how to fix it yet.
            // I leave the warning "intact" for further analysis, possibly by T&A TechSquad.
            $this->insertQuestionsObject($selected_array);
            return;
        }

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questions.html", "components/ILIAS/Test");

        $has_started_test_runs = $this->getTestObject()->evalTotalPersons() !== 0;
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
                $this->toolbar->addButton($this->lng->txt("ass_create_question"), $this->ctrl->getLinkTarget($this, 'createQuestionForm'));
                $this->toolbar->addSeparator();
                $this->populateQuestionBrowserToolbarButtons($this->toolbar, ilTestQuestionBrowserTableGUI::CONTEXT_LIST_VIEW);
            }
        }

        $this->tpl->setCurrentBlock("adm_content");
        $this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable(
            'QUESTIONBROWSER',
            $this->ui_renderer->render(
                $this->getTable()
                ->withQuestionEditing(!$has_started_test_runs)
                ->getTableComponent($this->object->getTestQuestions())
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

    public function takenObject(): void
    {
    }

    public function historyObject(): void
    {
        if (!$this->rbac_review->isAssigned($this->user->getId(), SYSTEM_ROLE_ID)
            && !$this->access->checkAccess('tst_history_read', '', $this->getTestObject()->getRefId())) {
            $this->redirectAfterMissingWrite();
        }
        if ($this->getTestObject()->getTestLogger() === null) {
            return;
        }

        $here_uri = $this->data_factory->uri(ILIAS_HTTP_PATH
            . '/' . $this->ctrl->getLinkTargetByClass(self::class, self::SHOW_LOGS_CMD));
        list($url_builder, $action_parameter_token, $row_id_token) = (new URLBuilder($here_uri))->acquireParameters(
            LogTable::QUERY_PARAMETER_NAME_SPACE,
            LogTable::ACTION_TOKEN_STRING,
            LogTable::ENTRY_TOKEN_STRING
        );

        if ($this->request_wrapper->has($action_parameter_token->getName())) {
            $this->getTestObject()->getTestLogViewer()->executeLogTableAction(
                $url_builder,
                $action_parameter_token,
                $row_id_token,
                $this->getTestObject()->getRefId()
            );
        }

        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('export_legacy_logs'),
                $this->ctrl->getLinkTargetByClass(self::class, 'exportLegacyLogs')
            )
        );
        $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_HISTORY);

        list($filter, $table_gui) = $this->getTestObject()->getTestLogViewer()->getLogTable(
            $url_builder,
            $action_parameter_token,
            $row_id_token,
            $this->getTestObject()->getRefId()
        );

        $this->tpl->setVariable('ADM_CONTENT', $this->ui_renderer->render([$filter, $table_gui]));
    }

    public function exportLegacyLogsObject(): void
    {
        $csv_output = $this->getTestObject()->getTestLogViewer()->getLegacyLogExportForObjId($this->getTestObject()->getId());

        ilUtil::deliverData(
            $csv_output,
            "legacy_logs_for_{$this->getTestObject()->getRefId()}.csv"
        );
    }

    /**
       * Evaluates the actions on the participants page
       */
    public function participantsActionObject(): void
    {
        $command = $this->testrequest->strVal('command');
        if ($command === '') {
            $method = $command . 'Object';
            if (method_exists($this, $method)) {
                $this->$method();
                return;
            }
        }
        $this->ctrl->redirect($this, 'participants');
    }

    public function printObject()
    {
        if (!$this->access->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
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
        $questionHeaderBlockBuilder->setHeaderMode($this->getTestObject()->getTitleOutput());

        foreach ($this->getTestObject()->questions as $question) {
            $template->setCurrentBlock("question");
            $question_gui = $this->getTestObject()->createQuestionGUI('', $question);
            $question_gui->setPresentationContext(assQuestionGUI::PRESENTATION_CONTEXT_TEST);

            $questionHeaderBlockBuilder->setQuestionTitle($question_gui->getObject()->getTitle());
            $questionHeaderBlockBuilder->setQuestionPoints($question_gui->getObject()->getMaximumPoints());
            $questionHeaderBlockBuilder->setQuestionPosition($counter);
            $template->setVariable("QUESTION_HEADER", $questionHeaderBlockBuilder->getHTML());

            $template->setVariable("TXT_QUESTION_ID", $this->lng->txt('question_id_short'));
            $template->setVariable("QUESTION_ID", $question_gui->getObject()->getId());
            $result_output = $question_gui->getSolutionOutput(0, null, false, true, false, false);
            $template->setVariable("SOLUTION_OUTPUT", $result_output);
            $template->parseCurrentBlock("question");
            $counter++;
            $max_points += $question_gui->getObject()->getMaximumPoints();
        }

        $template->setVariable(
            'TITLE',
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                $this->getTestObject()->getTitle()
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

    public function reviewobject()
    {
        if (!$this->access->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
        }
        $this->getTabsManager()->getQuestionsSubTabs();
        $template = new ilTemplate("tpl.il_as_tst_print_test_confirm.html", true, true, "components/ILIAS/Test");

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css"), "print");

        $max_points = 0;
        $counter = 1;
        $questionHeaderBlockBuilder = new ilTestQuestionHeaderBlockBuilder($this->lng);
        $questionHeaderBlockBuilder->setHeaderMode($this->getTestObject()->getTitleOutput());

        foreach ($this->getTestObject()->questions as $question) {
            $template->setCurrentBlock("question");
            $question_gui = $this->getTestObject()->createQuestionGUI("", $question);
            $question_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PREVIEW);

            $questionHeaderBlockBuilder->setQuestionTitle($question_gui->getObject()->getTitle());
            $questionHeaderBlockBuilder->setQuestionPoints($question_gui->getObject()->getMaximumPoints());
            $questionHeaderBlockBuilder->setQuestionPosition($counter);
            $template->setVariable("QUESTION_HEADER", $questionHeaderBlockBuilder->getHTML());

            $template->setVariable("SOLUTION_OUTPUT", $question_gui->getPreview(false));
            $template->parseCurrentBlock("question");
            $counter++;
            $max_points += $question_gui->getObject()->getMaximumPoints();
        }

        $template->setVariable(
            'TITLE',
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                $this->getTestObject()->getTitle()
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
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
        }

        $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_SETTINGS);

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'addDefaults'));
        $this->toolbar->addFormButton($this->lng->txt('add'), 'addDefaults');
        $this->toolbar->addInputItem(new ilTextInputGUI($this->lng->txt('tst_defaults_defaults_of_test'), 'name'), true);
        $table = new ilTestPersonalDefaultSettingsTableGUI($this, 'defaults');
        $defaults = $this->getTestObject()->getAvailableDefaults();
        $table->setData($defaults);
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Deletes selected test defaults
     */
    public function deleteDefaultsObject()
    {
        $defaults_ids = $this->testrequest->getArrayOfIntsFromPost('chb_defaults');
        if ($defaults_ids !== null && $defaults_ids !== []) {
            foreach ($defaults_ids as $test_default_id) {
                $this->getTestObject()->deleteDefaults($test_default_id);
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
        $defaults = $this->testrequest->getArrayOfStringsFromPost('chb_defaults');
        if ($defaults !== null && $defaults !== []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_defaults_apply_select_one'));

            $this->defaultsObject();
            return;
        }

        // do not apply if user datasets exist
        if ($this->getTestObject()->evalTotalPersons() > 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_defaults_apply_not_possible'));
            $this->defaultsObject();
            return;
        }

        $default_settings = unserialize($defaults[0]['defaults'], ['allowed_classes' => false]);

        if (isset($default_settings['isRandomTest'])) {
            if ($default_settings['isRandomTest']) {
                $new_question_set_type = ilObjTest::QUESTION_SET_TYPE_RANDOM;
                $this->getTestObject()->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_RANDOM);
            } else {
                $new_question_set_type = ilObjTest::QUESTION_SET_TYPE_FIXED;
                $this->getTestObject()->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_FIXED);
            }
        } elseif (isset($default_settings['questionSetType'])) {
            $new_question_set_type = $default_settings['questionSetType'];
        }
        $old_question_set_type = $this->getTestObject()->getQuestionSetType();
        $question_set_type_setting_switched = ($old_question_set_type != $new_question_set_type);

        $old_question_set_config = $this->test_question_set_config_factory->getQuestionSetConfig();

        switch (true) {
            case !$question_set_type_setting_switched:
            case !$old_question_set_config->doesQuestionSetRelatedDataExist():
            case $confirmed:

                break;

            default:

                $confirmation = new ilTestSettingsChangeConfirmationGUI($this->getTestObject());

                $confirmation->setFormAction($this->ctrl->getFormAction($this));
                $confirmation->setCancel($this->lng->txt('cancel'), 'defaults');
                $confirmation->setConfirm($this->lng->txt('confirm'), 'confirmedApplyDefaults');

                $confirmation->setOldQuestionSetType($this->getTestObject()->getQuestionSetType());
                $confirmation->setNewQuestionSetType($new_question_set_type);
                $confirmation->setQuestionLossInfoEnabled(false);
                $confirmation->build();

                $confirmation->populateParametersFromPost();

                $this->tpl->setContent($this->ctrl->getHTML($confirmation));

                return;
        }

        if ($question_set_type_setting_switched && !$this->getTestObject()->getOfflineStatus()) {
            $this->getTestObject()->setOfflineStatus(true);

            $info = $this->lng->txt('tst_set_offline_due_to_switched_question_set_type_setting');

            $this->tpl->setOnScreenMessage('info', $info, true);
        }

        $this->getTestObject()->applyDefaults($defaults);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_defaults_applied'), true);

        if ($question_set_type_setting_switched && $old_question_set_config->doesQuestionSetRelatedDataExist()) {
            $old_question_set_config->removeQuestionSetRelatedData();
        }

        $this->ctrl->redirect($this, 'defaults');
    }

    /**
    * Adds the defaults of this test to the defaults
    */
    public function addDefaultsObject(): void
    {
        $name = $this->testrequest->strVal('name');
        if ($name !== '') {
            $this->getTestObject()->addDefaults($name);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_defaults_enter_name'));
        }
        $this->defaultsObject();
    }

    private function isCommandClassAnyInfoScreenChild(): bool
    {
        if (in_array($this->ctrl->getCmdClass(), self::INFO_SCREEN_CHILD_CLASSES)) {
            return true;
        }

        return false;
    }

    private function infoScreenObject(): void
    {
        $this->ctrl->redirectByClass(
            [ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]
        );
    }

    private function forwardToInfoScreen(): void
    {
        if (!$this->access->checkAccess('visible', '', $this->ref_id)
            && !$this->access->checkAccess('read', '', $this->testrequest->getRefId())) {
            $this->redirectAfterMissingRead();
        }

        if ($this->getTestObject()->getMainSettings()->getAdditionalSettings()->getHideInfoTab()) {
            $this->ctrl->redirectByClass(TestScreenGUI::class, TestScreenGUI::DEFAULT_CMD);
        }

        $this->tabs_gui->activateTab(ilTestTabsManager::TAB_ID_INFOSCREEN);

        if ($this->access->checkAccess('read', '', $this->testrequest->getRefId())) {
            $this->trackTestObjectReadEvent();
        }
        $info = new ilInfoScreenGUI($this);
        $info->setOpenFormTag(false);

        if ($this->isCommandClassAnyInfoScreenChild()) {
            $this->ctrl->forwardCommand($info);
        }

        $toolbar = new ilTestInfoScreenToolbarGUI(
            $this->getTestObject(),
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

        $toolbar->setSessionLockString('');
        $toolbar->build();
        $toolbar->sendMessages();

        $info->enablePrivateNotes();

        $info->addSection($this->lng->txt("tst_general_properties"));
        $info->addProperty(
            $this->lng->txt("author"),
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                $this->getTestObject()->getAuthor()
            )
        );
        $info->addProperty(
            $this->lng->txt("title"),
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                $this->getTestObject()->getTitle()
            )
        );

        if ($this->type !== 'tst') {
            $info->hideFurtherSections(false);
        }

        $info->addSection($this->lng->txt("tst_sequence_properties"));
        $info->addProperty(
            $this->lng->txt("tst_sequence"),
            $this->lng->txt(
                $this->getTestObject()->getMainSettings()->getParticipantFunctionalitySettings()->getPostponedQuestionsMoveToEnd()
                    ? "tst_sequence_postpone" : "tst_sequence_fixed"
            )
        );

        $info->addSection($this->lng->txt("tst_heading_scoring"));
        $info->addProperty(
            $this->lng->txt("tst_text_count_system"),
            $this->lng->txt(
                ($this->getTestObject()->getCountSystem() == SettingsScoring::COUNT_PARTIAL_SOLUTIONS) ? "tst_count_partial_solutions" : "tst_count_correct_solutions"
            )
        );
        if ($this->getTestObject()->isRandomTest()) {
            $info->addProperty($this->lng->txt("tst_pass_scoring"), $this->lng->txt(($this->getTestObject()->getPassScoring() == ilObjTest::SCORE_BEST_PASS) ? "tst_pass_best_pass" : "tst_pass_last_pass"));
        }

        $info->addSection($this->lng->txt("tst_score_reporting"));
        $score_reporting_text = "";
        switch ($this->getTestObject()->getScoreReporting()) {
            case SettingsResultSummary::SCORE_REPORTING_FINISHED:
                $score_reporting_text = $this->lng->txt("tst_report_after_test");
                break;
            case SettingsResultSummary::SCORE_REPORTING_IMMIDIATLY:
                $score_reporting_text = $this->lng->txt("tst_report_after_first_question");
                break;
            case SettingsResultSummary::SCORE_REPORTING_DATE:
                $score_reporting_text = $this->lng->txt("tst_report_after_date");
                break;
            case SettingsResultSummary::SCORE_REPORTING_AFTER_PASSED:
                $score_reporting_text = $this->lng->txt("tst_report_after_passed");
                break;
            default:
                $score_reporting_text = $this->lng->txt("tst_report_never");
                break;
        }
        $info->addProperty($this->lng->txt("tst_score_reporting"), $score_reporting_text);
        $reporting_date = $this->getTestObject()->getReportingDate();
        if ($reporting_date) {
            $info->addProperty(
                $this->lng->txt('tst_score_reporting_date'),
                ilDatePresentation::formatDate(new ilDateTime($reporting_date, IL_CAL_TIMESTAMP))
            );
        }

        $info->addSection($this->lng->txt("tst_session_settings"));
        $info->addProperty($this->lng->txt("tst_nr_of_tries"), $this->getTestObject()->getNrOfTries() === 0 ? $this->lng->txt("unlimited") : (string) $this->getTestObject()->getNrOfTries());
        if ($this->getTestObject()->getNrOfTries() != 1) {
            $info->addProperty(
                $this->lng->txt('tst_nr_of_tries_of_user'),
                ($this->test_session_factory->getSession()->getPass() === 0) ?
                    $this->lng->txt("tst_no_tries") : (string) $this->test_session_factory->getSession()->getPass()
            );
        }

        if ($this->getTestObject()->getEnableProcessingTime()) {
            $info->addProperty($this->lng->txt("tst_processing_time"), $this->getTestObject()->getProcessingTime());
        }

        $starting_time = $this->getTestObject()->getStartingTime();
        if ($this->getTestObject()->isStartingTimeEnabled() && $starting_time !== 0) {
            $info->addProperty($this->lng->txt("tst_starting_time"), ilDatePresentation::formatDate(new ilDateTime($starting_time, IL_CAL_UNIX)));
        }
        $ending_time = $this->getTestObject()->getEndingTime();
        if ($this->getTestObject()->isEndingTimeEnabled() && $ending_time != 0) {
            $info->addProperty($this->lng->txt("tst_ending_time"), ilDatePresentation::formatDate(new ilDateTime($ending_time, IL_CAL_UNIX)));
        }
        $info->addMetaDataSections($this->getTestObject()->getId(), 0, $this->getTestObject()->getType());

        $this->ctrl->forwardCommand($info);
    }

    protected function removeImportFailsObject()
    {
        $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->getTestObject()->getId());
        $qsaImportFails->deleteRegisteredImportFails();
        $sltImportFails = new ilTestSkillLevelThresholdImportFails($this->getTestObject()->getId());
        $sltImportFails->deleteRegisteredImportFails();

        $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
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
                    $this->getTestObject()->getTitle(),
                    $this->ctrl->getLinkTargetByClass(
                        [self::class, TestScreenGUI::class],
                        TestScreenGUI::DEFAULT_CMD
                    ),
                    '',
                    $this->testrequest->getRefId()
                );
                break;
            case "eval_stat":
            case "evalAllUsers":
            case "evalUserDetail":
                $this->locator->addItem(
                    $this->getTestObject()->getTitle(),
                    $this->ctrl->getLinkTarget($this, 'eval_stat'),
                    '',
                    $this->testrequest->getRefId()
                );
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
                $this->locator->addItem(
                    $this->getTestObject()->getTitle(),
                    $this->ctrl->getLinkTargetByClass(
                        [self::class, TestScreenGUI::class],
                        TestScreenGUI::DEFAULT_CMD
                    ),
                    '',
                    $this->testrequest->getRefId()
                );
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
        $output_gui = $guiFactory->create($this->getTestObject());

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
        $DIC->ctrl()->redirectByClass(TestScreenGUI::class, TestScreenGUI::DEFAULT_CMD);
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

        if ($ilAccess->checkAccess('read', '', (int) $target)
            || $ilAccess->checkAccess('visible', '', (int) $target)) {
            $DIC->ctrl()->setParameterByClass(self::class, 'ref_id', (int) $target);
            $DIC->ctrl()->redirectByClass(
                [
                    ilRepositoryGUI::class,
                    ilObjTestGUI::class,
                    TestScreenGUI::class
                ],
                TestScreenGUI::DEFAULT_CMD
            );
        } elseif ($ilAccess->checkAccess('read', '', ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('info', sprintf(
                $lng->txt('msg_no_perm_read_item'),
                ilObject::_lookupTitle(ilObject::_lookupObjId((int) $target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        $ilErr->raiseError($lng->txt('msg_no_perm_read_lm'), $ilErr->FATAL);
    }

    public function copyQuestionsToPoolObject()
    {
        $this->copyQuestionsToPool($this->testrequest->raw('q_id'), $this->testrequest->raw('sel_qpl'));
        $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
    }

    /**
     *
     * @param<int> array $question_ids
     */
    public function copyQuestionsToPool(array $question_ids, int $qpl_id): stdClass
    {
        $target_pool = new ilObjQuestionPool($qpl_id, false);
        $new_ids = [];
        foreach ($question_ids as $q_id) {
            $new_id = $this->copyQuestionToPool(assQuestion::instantiateQuestionGUI($q_id), $target_pool);
            $new_ids[$q_id] = $new_id;
        }

        $result = new stdClass();
        $result->ids = $new_ids;
        $result->qpoolid = $qpl_id;

        return $result;
    }

    public function copyQuestionToPool(assQuestionGUI $source_question_gui, ilObjQuestionPool $target_pool): int
    {
        $new_title = $target_pool->appendCounterToQuestionTitleIfNecessary(
            $source_question_gui->getObject()->getTitle()
        );

        return $source_question_gui->getObject()->createNewOriginalFromThisDuplicate($target_pool->getId(), $new_title);
    }

    public function copyAndLinkQuestionsToPoolObject(
        int $ref_id = null,
        array $question_ids = []
    ) {

        $ref_id = $ref_id ?? $this->testrequest->int('sel_qpl');
        if ($ref_id === null
            || $ref_id === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("questionpool_not_selected"));
            $this->showQuestionsObject();
            return;
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
                && $this->questionrepository->originalQuestionExists($original_question_id)) {
                $oldOriginal = assQuestion::instantiateQuestion($original_question_id);
                $oldOriginal->delete($oldOriginal->getId());
            }
            assQuestion::saveOriginalId($questionInstance->getId(), $newId);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_qst_added_to_pool_' . (count($result->ids) > 1 ? 'p' : 's')), true);
        $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
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
        if (!$this->testrequest->isset('q_id') || !is_array($this->testrequest->raw('q_id'))) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_no_question_selected_for_moving_to_qpl'), true);
            $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
        }

        foreach ($this->testrequest->getQuestionIds('q_id') as $q_id) {
            if (!$this->questionrepository->originalQuestionExists($q_id)) {
                continue;
            }

            $type = ilObject::_lookupType(assQuestion::lookupParentObjId($this->questionrepository->getForQuestionId($q_id)->getOriginalId()));

            if ($type !== 'tst') {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_link_only_unassigned'), true);
                $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
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

        $question_pool = $this->createQuestionPool($title, $this->testrequest->raw('description'));
        $_REQUEST['sel_qpl'] = $question_pool->getRefId();

        $this->copyAndLinkQuestionsToPoolObject();
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

        $questionpools = $this->getTestObject()->getAvailableQuestionpools(
            false,
            false,
            false,
            true,
            false,
            'write'
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
        $this->tpl->printToStdout();
        exit();
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

        $table_query = $this->getQuestionsTableQuery();
        if ($table_query->getQueryCommand()) {
            $question_ids = $table_query->getRowIds($this->object);
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
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
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



    public function saveOrderAndObligationsObject()
    {
        if (!$this->access->checkAccess("write", "", $this->ref_id)) {
            // allow only write access
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, self::class, ilInfoScreenGUI::class]);
        }

        $order = $this->testrequest->raw('order') ?? [];
        $obligatory_questions = $this->testrequest->raw('obligatory') ?? [];

        foreach ($order as $question_id => $order) {
            $orders[$question_id] = $order;
        }

        if ($this->getTestObject()->areObligationsEnabled()) {
            foreach ($obligatory_questions as $question_id => $obligation) {
                if (!ilObjTest::isQuestionObligationPossible($question_id)) {
                    unset($obligatory_questions[$question_id]);
                }
            }
        }

        $this->getTestObject()->setQuestionOrderAndObligations(
            $orders,
            $obligatory_questions
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
    }

    public function copyQuestionObject()
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
            $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
        }

        $copy_count = 0;

        $questionTitles = $this->getTestObject()->getQuestionTitles();

        foreach ($question_ids as $id) {
            $question = assQuestion::instantiateQuestionGUI($id);
            if ($question) {
                $title = $question->getObject()->getTitle();
                $i = 2;
                while (in_array($title . ' (' . $i . ')', $questionTitles)) {
                    $i++;
                }

                $title .= ' (' . $i . ')';

                $questionTitles[] = $title;

                $new_id = $question->getObject()->duplicate(false, $title);

                $clone = assQuestion::instantiateQuestionGUI($new_id);
                $question = $clone->getObject();
                $question->setObjId($this->getTestObject()->getId());
                $clone->setObject($question);
                $clone->getObject()->saveToDb();

                $this->getTestObject()->insertQuestion($new_id, true);

                $copy_count++;
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('copy_questions_success'), true);

        $this->ctrl->redirect($this, self::SHOW_QUESTIONS_CMD);
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

    private function getTestScreenGUIInstance(): TestScreenGUI
    {
        return new TestScreenGUI(
            $this->getTestObject(),
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

    protected function getQuestionsTableQuery(): QuestionsTableQuery
    {
        if ($this->table_query === null) {
            $id = $this->object ? $this->object->getId() : '';
            $this->table_query = new QuestionsTableQuery(
                $this->http,
                $this->refinery,
                new \ILIAS\Data\Factory(),
                ['qlist', $id]
            );
        }
        return $this->table_query;
    }

    protected function getTable(): QuestionsTable
    {
        return new QuestionsTable(
            $this->ui_factory,
            $this->ui_renderer,
            $this->http->request(),
            $this->getQuestionsTableQuery(),
            $this->lng,
            $this->ctrl,
            $this->object,
            $this->questionrepository,
            $this->getQuestionPoolLinkBuilder()
        );
    }

    private function getQuestionPoolLinkBuilder(): \Closure
    {
        return function (?int $qpl_id): string {
            if (!$qpl_id || ilObject::_lookupType($qpl_id) !== 'qpl') {
                return $this->lng->txt('tst_question_not_from_pool_info');
            }

            return $this->buildPossiblyLinkedQuestonPoolTitle(
                $this->ctrl,
                $this->access,
                $this->lng,
                $this->ui_factory,
                $this->ui_renderer,
                $qpl_id,
                ilObject::_lookupTitle($qpl_id)
            );
        };
    }

    protected function setMessage(string $type, string $message): void
    {
        $this->tpl->setOnScreenMessage($type, $this->lng->txt($message), true);
    }
}
