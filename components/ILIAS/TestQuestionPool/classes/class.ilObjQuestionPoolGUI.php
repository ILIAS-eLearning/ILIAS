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

use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\RequestDataCollector;
use ILIAS\TestQuestionPool\Questions\Presentation\QuestionTable;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\Test\Settings\GlobalSettings\GlobalTestSettings;
use ILIAS\Taxonomy\Service;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Field\Select;
use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\GlobalScreen\Services as GlobalScreen;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\TestQuestionPool\Import\TestQuestionsImportTrait;
use ILIAS\FileUpload\MimeType;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Style\Content\Service as ContentStyle;

/**
 * Class ilObjQuestionPoolGUI
 *
 * @author         Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author         Björn Heyser <bheyser@databay.de>
 *
 * @version        $Id$
 *
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilAssQuestionPageGUI, ilQuestionBrowserTableGUI, ilToolbarGUI, ilObjTestGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: assOrderingQuestionGUI, assImagemapQuestionGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: assNumericGUI, assTextSubsetGUI, assSingleChoiceGUI, ilPropertyFormGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: assTextQuestionGUI, ilObjectMetaDataGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilExportGUI, ilInfoScreenGUI, ilTaxonomySettingsGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilAssQuestionHintsGUI, ilAssQuestionFeedbackEditingGUI, ilLocalUnitConfigurationGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilObjQuestionPoolSettingsGeneralGUI, assFormulaQuestionGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilAssQuestionPreviewGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: assKprimChoiceGUI, assLongMenuGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilQuestionPoolSkillAdministrationGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilBulkEditQuestionsGUI
 *
 * @ingroup components\ILIASTestQuestionPool
 *
 */
class ilObjQuestionPoolGUI extends ilObjectGUI implements ilCtrlBaseClassInterface
{
    use TestQuestionsImportTrait;

    public const SUPPORTED_IMPORT_MIME_TYPES = [MimeType::APPLICATION__ZIP, MimeType::TEXT__XML];
    public const DEFAULT_CMD = 'questions';

    private HTTPServices $http;
    protected Service $taxonomy;
    protected ilDBInterface $db;
    protected ilComponentLogger $log;
    protected ilHelpGUI $help;
    protected GlobalScreen $global_screen;
    protected ilComponentFactory $component_factory;
    protected ilComponentRepository $component_repository;
    protected ilNavigationHistory $navigation_history;
    protected ilUIService $ui_service;
    protected DataFactory $data_factory;
    protected URLBuilder $url_builder;
    protected URLBuilderToken $action_parameter_token;
    protected URLBuilderToken $row_id_token;
    private Archives $archives;
    private ContentStyle $content_style;

    protected RequestDataCollector $request_data_collector;
    protected GeneralQuestionPropertiesRepository $questionrepository;
    protected GlobalTestSettings $global_test_settings;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->type = 'qpl';

        $this->db = $DIC['ilDB'];
        $this->log = $DIC['ilLog'];
        $this->help = $DIC['ilHelp'];
        $this->global_screen = $DIC['global_screen'];
        $this->component_factory = $DIC['component.factory'];
        $this->component_repository = $DIC['component.repository'];
        $this->navigation_history = $DIC['ilNavigationHistory'];
        $this->ui_service = $DIC->uiService();
        $this->taxonomy = $DIC->taxonomy();
        $this->http = $DIC->http();
        $this->archives = $DIC->archives();
        $this->content_style = $DIC->contentStyle();

        $this->data_factory = new DataFactory();

        $local_dic = QuestionPoolDIC::dic();
        $this->request_data_collector = $local_dic['request_data_collector'];
        $this->questionrepository = $local_dic['question.general_properties.repository'];
        $this->global_test_settings = $local_dic['global_test_settings'];

        parent::__construct('', $this->request_data_collector->getRefId(), true, false);

        $this->ctrl->saveParameter($this, [
            'ref_id',
            'test_ref_id',
            'test_express_mode',
            'q_id',
            'tax_node',
            'consumer_context'
        ]);
        $this->ctrl->saveParameterByClass('ilAssQuestionPageGUI', 'consumer_context');
        $this->ctrl->saveParameterByClass('ilobjquestionpoolgui', 'consumer_context');

        $this->lng->loadLanguageModule('assessment');

        $here_uri = $this->data_factory->uri($this->request->getUri()->__toString());
        $url_builder = new URLBuilder($here_uri);
        $query_params_namespace = ['qpool', 'table'];
        list($url_builder, $action_parameter_token, $row_id_token) = $url_builder->acquireParameters(
            $query_params_namespace,
            'action', //this is the actions's parameter name
            'qids'   //this is the parameter name to be used for row-ids
        );
        $this->url_builder = $url_builder;
        $this->action_parameter_token = $action_parameter_token;
        $this->row_id_token = $row_id_token;

        $this->notes_service->gui()->initJavascript();
    }

    public function executeCommand(): void
    {
        $write_access = $this->access->checkAccess('write', '', $this->request_data_collector->getRefId());

        if ((!$this->access->checkAccess('read', '', $this->request_data_collector->getRefId()))
            && (!$this->access->checkAccess('visible', '', $this->request_data_collector->getRefId()))) {
            $this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
        }

        if (!$this->getCreationMode() &&
            $this->access->checkAccess('read', '', $this->request_data_collector->getRefId())) {
            if ('qpl' === $this->object->getType()) {
                $this->navigation_history->addItem(
                    $this->request_data_collector->getRefId(),
                    ilLink::_getLink($this->request_data_collector->getRefId(), "qpl"),
                    'qpl',
                );
            }
        }

        $cmd = $this->ctrl->getCmd(self::DEFAULT_CMD);
        $next_class = $this->ctrl->getNextClass($this);
        $q_id = $this->request_data_collector->getQuestionId() ?? null;

        if (in_array($next_class, ['', 'ilobjquestionpoolgui']) && $cmd == self::DEFAULT_CMD) {
            $q_id = -1;
        }

        $this->prepareOutput();

        $this->tpl->addCss(ilUtil::getStyleSheetLocation('output', 'test_print.css'), 'print');

        $q_type = $this->request_data_collector->string('question_type');
        switch ($next_class) {
            case 'ilcommonactiondispatchergui':
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjectmetadatagui':
                if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->WARNING);
                }
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'ilassquestionpreviewgui':
                if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->ctrl->saveParameterByClass(ilAssQuestionPreviewGUI::class, 'q_id');
                $this->ctrl->saveParameterByClass(ilAssQuestionHintRequestGUI::class, 'q_id');
                $this->ctrl->saveParameter($this, 'q_id');
                $gui = new ilAssQuestionPreviewGUI(
                    $this->ctrl,
                    $this->rbac_system,
                    $this->tabs_gui,
                    $this->toolbar,
                    $this->tpl,
                    $this->ui_factory,
                    $this->lng,
                    $this->db,
                    $this->refinery->random(),
                    $this->global_screen,
                    $this->http,
                    $this->refinery,
                    $this->ref_id
                );

                $question_gui = assQuestion::instantiateQuestionGUI($this->request_data_collector->int('q_id'));
                $gui->setPrimaryCmd(
                    $this->lng->txt('edit_question'),
                    $this->ctrl->getLinkTargetByClass(
                        get_class($question_gui),
                        'editQuestion'
                    )
                );
                $gui->addAdditionalCmd(
                    $this->lng->txt('edit_page'),
                    $this->ctrl->getLinkTargetByClass(
                        ilAssQuestionPageGUI::class,
                        'edit'
                    )
                );

                $gui->initQuestion(
                    $question_gui,
                    $this->object->getId()
                );
                $gui->initPreviewSettings($this->object->getRefId());
                $gui->initPreviewSession($this->user->getId(), $this->fetchAuthoringQuestionIdParamater());
                $gui->initHintTracking();
                $this->ctrl->clearParameterByClass(self::class, 'q_id');
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt('backtocallingpool'),
                    $this->ctrl->getLinkTargetByClass(self::class, self::DEFAULT_CMD)
                );

                $this->help->setScreenIdComponent('qpl');

                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilassquestionpagegui':
                if ($cmd == 'finishEditing') {
                    $this->ctrl->redirectByClass('ilassquestionpreviewgui', 'show');
                    break;
                }
                if ($cmd === 'edit' && !$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->tpl->setCurrentBlock('ContentStyle');
                $this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));
                $this->tpl->parseCurrentBlock();

                $this->tpl->setCurrentBlock('SyntaxStyle');
                $this->tpl->setVariable('LOCATION_SYNTAX_STYLESHEET', ilObjStyleSheet::getSyntaxStylePath());
                $this->tpl->parseCurrentBlock();
                $question_gui = assQuestionGUI::_getQuestionGUI('', $this->fetchAuthoringQuestionIdParamater());
                $question_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PREVIEW);
                $question_gui->setQuestionTabs();
                $question_gui->getObject()->setObjId($this->object->getId());
                $question_gui->setQuestionActionCmd('');

                if ($this->object->getType() === 'qpl') {
                    $question_gui->addHeaderAction();
                }

                $question = $question_gui->getObject();

                if ($this->questionrepository->isInActiveTest($question->getObjId())) {
                    $this->tpl->setOnScreenMessage(
                        'failure',
                        $this->lng->txt('question_is_part_of_running_test'),
                        true
                    );
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }

                $this->ctrl->saveParameter($this, 'q_id');
                $this->lng->loadLanguageModule('content');
                $this->ctrl->setReturnByClass('ilAssQuestionPageGUI', 'view');
                $this->ctrl->setReturn($this, self::DEFAULT_CMD);
                $page_gui = new ilAssQuestionPageGUI($this->request_data_collector->getQuestionId());
                $page_gui->obj->addUpdateListener(
                    $question,
                    'updateTimestamp'
                );
                $page_gui->setEditPreview(true);
                $page_gui->setEnabledTabs(false);
                $page_gui->setQuestionHTML([$question_gui->getObject()->getId() => $question_gui->getPreview(true)]);
                $page_gui->setTemplateTargetVar('ADM_CONTENT');
                $page_gui->setOutputMode('edit');
                $page_gui->setHeader($question->getTitle());
                $page_gui->setPresentationTitle($question->getTitle());
                $ret = $this->ctrl->forwardCommand($page_gui);
                if ($ret != '') {
                    $this->tpl->setContent($ret);
                }
                break;

            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('qpl');
                $this->ctrl->forwardCommand($cp);
                break;

            case 'ilexportgui':
                $exp_gui = new ilExportGUI($this);
                $ret = $this->ctrl->forwardCommand($exp_gui);
                break;

            case strtolower(ilInfoScreenGUI::class):
                $this->infoScreenForward();
                break;

            case 'ilassquestionhintsgui':
                if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->ctrl->setReturn($this, self::DEFAULT_CMD);
                $question_gui = assQuestionGUI::_getQuestionGUI(
                    $q_type,
                    $this->fetchAuthoringQuestionIdParamater()
                );
                $question = $question_gui->getObject();
                $question->setObjId($this->object->getId());
                $question_gui->setObject($question);
                $question_gui->setQuestionTabs();

                if ($this->questionrepository->isInActiveTest($question_gui->getObject()->getObjId())) {
                    $this->tpl->setOnScreenMessage(
                        'failure',
                        $this->lng->txt('question_is_part_of_running_test'),
                        true
                    );
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }

                $this->help->setScreenIdComponent('qpl');

                if ($this->object->getType() == 'qpl' && $write_access) {
                    $question_gui->addHeaderAction();
                }
                $gui = new ilAssQuestionHintsGUI($question_gui);

                $gui->setEditingEnabled(
                    $this->access->checkAccess('write', '', $this->object->getRefId())
                );

                $this->ctrl->forwardCommand($gui);

                break;

            case 'illocalunitconfigurationgui':
                if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->WARNING);
                }
                $question_gui = assQuestionGUI::_getQuestionGUI(
                    $q_type,
                    $this->fetchAuthoringQuestionIdParamater()
                );
                $question = $question_gui->getObject();
                $question->setObjId($this->object->getId());
                $question_gui->setObject($question);
                $question_gui->setQuestionTabs();

                $this->ctrl->setReturn($this, self::DEFAULT_CMD);
                $gui = new ilLocalUnitConfigurationGUI(
                    new ilUnitConfigurationRepository($this->request_data_collector->getQuestionId())
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilassquestionfeedbackeditinggui':
                if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->ctrl->setReturn($this, self::DEFAULT_CMD);
                $question_gui = assQuestionGUI::_getQuestionGUI(
                    $q_type,
                    $this->fetchAuthoringQuestionIdParamater()
                );
                $question = $question_gui->getObject();
                $question->setObjId($this->object->getId());
                $question_gui->setObject($question);
                $question_gui->setQuestionTabs();

                if ($this->questionrepository->isInActiveTest($question_gui->getObject()->getObjId())) {
                    $this->tpl->setOnScreenMessage(
                        'failure',
                        $this->lng->txt('question_is_part_of_running_test'),
                        true
                    );
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }

                $this->help->setScreenIdComponent('qpl');

                if ($this->object->getType() == 'qpl' && $write_access) {
                    $question_gui->addHeaderAction();
                }
                $gui = new ilAssQuestionFeedbackEditingGUI(
                    $question_gui,
                    $this->ctrl,
                    $this->access,
                    $this->tpl,
                    $this->tabs_gui,
                    $this->lng,
                    $this->help,
                    $this->request_data_collector,
                    $this->content_style,
                    true
                );
                $this->ctrl->forwardCommand($gui);

                break;

            case 'ilobjquestionpoolsettingsgeneralgui':
                $gui = new ilObjQuestionPoolSettingsGeneralGUI(
                    $this->ctrl,
                    $this->access,
                    $this->lng,
                    $this->tpl,
                    $this->tabs_gui,
                    $this,
                    $this->refinery,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->request,
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilTaxonomySettingsGUI::class):
                if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                /** @var ilObjQuestionPool $obj */
                $obj = $this->object;
                $forwarder = new ilObjQuestionPoolTaxonomyEditingCommandForwarder(
                    $this->object,
                    $this->db,
                    $this->refinery,
                    $this->component_repository,
                    $this->ctrl,
                    $this->tabs_gui,
                    $this->lng,
                    $this->taxonomy
                );

                $forwarder->forward();

                break;

            case 'ilquestionpoolskilladministrationgui':
                $obj = $this->object;
                $gui = new ilQuestionPoolSkillAdministrationGUI(
                    $this->ilias,
                    $this->ctrl,
                    $this->refinery,
                    $this->access,
                    $this->tabs_gui,
                    $this->tpl,
                    $this->lng,
                    $this->db,
                    $this->component_repository,
                    $obj,
                    $this->ref_id
                );

                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilbulkeditquestionsgui':
                if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt('backtocallingpool'),
                    $this->ctrl->getLinkTargetByClass(self::class, self::DEFAULT_CMD)
                );
                $this->tabs_gui->addTarget(
                    'edit_questions',
                    '#',
                    '',
                    $this->ctrl->getCmdClass(),
                    ''
                );
                $this->tabs_gui->setTabActive('edit_questions');

                $gui = new \ilBulkEditQuestionsGUI(
                    $this->tpl,
                    $this->ctrl,
                    $this->lng,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->refinery,
                    $this->request,
                    $this->request_wrapper,
                    $this->object->getId(),
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjquestionpoolgui':
            case '':
                //table actions.
                if ($action = $this->request_data_collector->string($this->action_parameter_token->getName())) {
                    $ids = $this->request_data_collector->raw($this->row_id_token->getName()) ?? null;

                    if (is_null($ids)) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_questions_selected'), true);
                        $this->ctrl->redirect($this, self::DEFAULT_CMD);
                    }
                    if ($ids[0] === 'ALL_OBJECTS') {
                        $ids = $this->object->getAllQuestionIds();
                    }
                    if (!is_array($ids)) {
                        $ids = explode(',', $ids);
                    }
                    $ids = array_map('intval', $ids);

                    $class = strtolower($this->questionrepository->getForQuestionId(current($ids))->getGuiClassName());
                    $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", current($ids));
                    $this->ctrl->setParameterByClass("ilAssQuestionPreviewGUI", "q_id", current($ids));
                    $this->ctrl->setParameterByClass('ilAssQuestionFeedbackEditingGUI', 'q_id', current($ids));
                    $this->ctrl->setParameterByClass('ilAssQuestionHintsGUI', 'q_id', current($ids));
                    $this->ctrl->setParameterByClass($class, "q_id", current($ids));

                    switch ($action) {
                        case 'preview':
                            $url = $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                            $this->ctrl->redirectToURL($url);
                            break;
                        case 'statistics':
                            $url = $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_STATISTICS);
                            $this->ctrl->redirectToURL($url);
                            break;
                        case 'edit_question':
                            $url = $this->ctrl->getLinkTargetByClass($class, 'editQuestion');
                            $this->ctrl->redirectToURL($url);
                            break;
                        case 'edit_page':
                            $url = $this->ctrl->getLinkTargetByClass('ilAssQuestionPageGUI', 'edit');
                            $this->ctrl->redirectToURL($url);
                            break;
                        case 'feedback':
                            $url = $this->ctrl->getLinkTargetByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW);
                            $this->ctrl->redirectToURL($url);
                            break;
                        case 'hints':
                            $url = $this->ctrl->getLinkTargetByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
                            $this->ctrl->redirectToURL($url);
                            break;
                        case 'move':
                            $this->moveQuestions($ids);
                            $this->ctrl->redirect($this, self::DEFAULT_CMD);
                            break;
                        case 'copy':
                            $this->copyQuestions($ids);
                            $this->ctrl->redirect($this, self::DEFAULT_CMD);
                            break;
                        case 'delete':
                            $this->confirmDeleteQuestions($ids);
                            break;
                        case 'export':
                            $this->exportQuestions($ids);
                            $this->ctrl->redirect($this, self::DEFAULT_CMD);
                            break;
                        case 'comments':
                            $ajax_hash = ilCommonActionDispatcherGUI::buildAjaxHash(
                                ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
                                $this->object->getRefId(),
                                'quest',
                                $this->object->getId(),
                                'quest',
                                current($ids)
                            );
                            echo ''
                                . '<script>'
                                . ' event = new Event("click");'
                                . ilCommentGUI::getListCommentsJSCall($ajax_hash)
                                . '</script>'
                            ;
                            exit();

                        case ilBulkEditQuestionsGUI::CMD_EDITTAUTHOR:
                        case ilBulkEditQuestionsGUI::CMD_EDITLIFECYCLE:
                        case ilBulkEditQuestionsGUI::CMD_EDITTAXONOMIES:
                            $this->ctrl->clearParameters($this);
                            $this->ctrl->setParameterByClass(
                                ilBulkEditQuestionsGUI::class,
                                ilBulkEditQuestionsGUI::PARAM_IDS,
                                implode(',', $ids)
                            );
                            $url = $this->ctrl->getLinkTargetByClass(
                                ilBulkEditQuestionsGUI::class,
                                $action
                            );
                            $this->ctrl->redirectToURL($url);
                            break;

                        default:
                            throw new \Exception("'$action'" . " not implemented");
                    }
                    break;
                }


                if ($cmd == self::DEFAULT_CMD) {
                    $this->ctrl->setParameter($this, 'q_id', '');
                }
                $cmd .= 'Object';
                $ret = $this->$cmd();
                break;

            default:
                if (in_array($cmd, ['editQuestion', 'save', 'suggestedsolution']) && !$this->access->checkAccess(
                    'write',
                    '',
                    $this->object->getRefId()
                )) {
                    $this->redirectAfterMissingWrite();
                }

                $this->ctrl->setReturnByClass(self::class, self::DEFAULT_CMD);

                $qid = $this->fetchAuthoringQuestionIdParamater();
                $question_gui = assQuestionGUI::_getQuestionGUI(
                    $q_type,
                    $qid
                );
                $question_gui->setEditContext(assQuestionGUI::EDIT_CONTEXT_AUTHORING);
                $question = $question_gui->getObject();
                $question->setObjId($this->object->getId());
                $question_gui->setObject($question);

                if ($this->object->getType() === 'qpl') {
                    $question_gui->setTaxonomyIds($this->object->getTaxonomyIds());

                    if ($write_access) {
                        $question_gui->addHeaderAction();
                    }
                }

                $this->help->setScreenIdComponent('qpl');

                $question_gui->setQuestionTabs();

                if ($qid === 0 && $question_gui->cmdNeedsExistingQuestion($cmd)) {
                    $question_gui->getObject()->createNewQuestion();
                    $question_gui->setQuestionTabs();
                }

                if (!in_array($cmd, ['save', 'saveReturn'])) {
                    $question_gui->$cmd();
                    return;
                }

                if (!$question_gui->saveQuestion()) {
                    return;
                }
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
                if ($cmd === 'saveReturn') {
                    $this->ctrl->setParameterByClass(
                        ilAssQuestionPreviewGUI::class,
                        'q_id',
                        (string) $question_gui->getObject()->getId()
                    );
                    $this->ctrl->redirectToURL(
                        $this->ctrl->getLinkTargetByClass(ilAssQuestionPreviewGUI::class, ilAssQuestionPreviewGUI::CMD_SHOW)
                    );
                }

                if ($cmd === 'save') {
                    $this->tabs_gui->activateTab('edit_question');
                    $question_gui->editQuestion(false, false);
                }
                break;
        }

        if (!(strtolower($this->request_data_collector->raw('baseClass')) == 'iladministrationgui'
                || strtolower($this->request_data_collector->raw('baseClass')) == 'ilrepositorygui')
            && $this->getCreationMode() != true) {
            $this->tpl->printToStdout();
        }
    }

    protected function redirectAfterMissingWrite()
    {
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'), true);
        $target_class = get_class($this->object) . 'GUI';
        $this->ctrl->setParameterByClass($target_class, 'ref_id', $this->ref_id);
        $this->ctrl->redirectByClass($target_class);
    }

    /**
     * Gateway for exports initiated from workspace, as there is a generic
     * forward to {objTypeMainGUI}::export()
     */
    protected function exportObject(): void
    {
        $this->ctrl->redirectByClass('ilQuestionPoolExportGUI');
    }

    public function downloadFileObject(): void
    {
        $file = explode('_', $this->request_data_collector->raw('file_id'));
        $fileObj = new ilObjFile((int) $file[count($file) - 1], false);
        $fileObj->sendFile();
        exit;
    }

    /**
     * show fullscreen view
     */
    public function fullscreenObject(): void
    {
        $page_gui = new ilAssQuestionPageGUI($this->request_data_collector->raw('pg_id'));
        $page_gui->showMediaFullscreen();
    }

    /**
     * download source code paragraph
     */
    public function download_paragraphObject(): void
    {
        $pg_obj = new ilAssQuestionPage($this->request_data_collector->raw('pg_id'));
        $pg_obj->sendParagraph($this->request_data_collector->raw('par_id'), $this->request_data_collector->raw('downloadtitle'));
        exit;
    }

    public function importVerifiedFileObject(): void
    {
        $file_to_import = ilSession::get('path_to_import_file');
        list($subdir, $importdir, $xmlfile, $qtifile) = $this->buildImportDirectoriesFromImportFile($file_to_import);

        $new_obj = new ilObjQuestionPool(0, true);
        $new_obj->setType($this->request_data_collector->raw('new_type'));
        $new_obj->setTitle('dummy');
        $new_obj->setDescription('questionpool import');
        $new_obj->create(true);
        $new_obj->createReference();
        $new_obj->putInTree($this->request_data_collector->getRefId());
        $new_obj->setPermissions($this->request_data_collector->getRefId());

        $selected_questions = $this->retrieveSelectedQuestionsFromImportQuestionsSelectionForm(
            'importVerifiedFile',
            $importdir,
            $qtifile,
            $this->request
        );

        if (is_file($importdir . DIRECTORY_SEPARATOR . 'manifest.xml')) {
            $this->importQuestionPoolWithValidManifest(
                $new_obj,
                $selected_questions,
                $file_to_import
            );
        } else {
            $this->importQuestionsFromQtiFile(
                $new_obj,
                $selected_questions,
                $qtifile,
                $importdir,
                $xmlfile
            );

            $new_obj->fromXML($xmlfile);

            $new_obj->update();
            $new_obj->saveToDb();
        }
        $this->cleanupAfterImport($importdir);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('object_imported'), true);
        $this->ctrl->setParameterByClass(self::class, 'ref_id', $new_obj->getRefId());
        $this->ctrl->redirectByClass(self::class);
    }

    public function importVerifiedQuestionsFileObject(): void
    {
        $file_to_import = ilSession::get('path_to_import_file');

        if (mb_substr($file_to_import, -3) === 'xml') {
            $importdir = dirname($file_to_import);
            $selected_questions = $this->retrieveSelectedQuestionsFromImportQuestionsSelectionForm(
                'importVerifiedQuestionsFile',
                $importdir,
                $file_to_import,
                $this->request
            );
            $this->importQuestionsFromQtiFile(
                $this->getObject(),
                $selected_questions,
                $file_to_import,
                $importdir
            );
        } else {
            list($subdir, $importdir, $xmlfile, $qtifile) = $this->buildImportDirectoriesFromImportFile($file_to_import);
            $selected_questions = $this->retrieveSelectedQuestionsFromImportQuestionsSelectionForm(
                'importVerifiedQuestionsFile',
                $importdir,
                $qtifile,
                $this->request
            );
            if (is_file($importdir . DIRECTORY_SEPARATOR . 'manifest.xml')) {
                $this->importQuestionPoolWithValidManifest(
                    $this->getObject(),
                    $selected_questions,
                    $file_to_import
                );
            } else {
                $this->importQuestionsFromQtiFile(
                    $this->getObject(),
                    $selected_questions,
                    $qtifile,
                    $importdir,
                    $xmlfile
                );
            }
        }

        $this->cleanupAfterImport($importdir);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('object_imported'), true);
        $this->questionsObject();
    }

    public function uploadQuestionsImportObject(): void
    {
        $import_questions_modal = $this->buildImportQuestionsModal()->withRequest($this->request);
        $data = $import_questions_modal->getData();
        if ($data === null) {
            $this->questionsObject(
                $import_questions_modal->withOnLoad(
                    $import_questions_modal->getShowSignal()
                )
            );
            return;
        }
        $path_to_imported_file_in_temp_dir = $data['import_file'][0];
        $this->importQuestionsFile($path_to_imported_file_in_temp_dir);
    }

    private function buildImportQuestionsModal(): RoundTripModal
    {
        $constraint = $this->refinery->custom()->constraint(
            function ($vs): bool {
                if ($vs === []) {
                    return false;
                }
                return true;
            },
            $this->lng->txt('msg_no_files_selected')
        );

        $file_upload_input = $this->ui_factory->input()->field()
            ->file(new \QuestionPoolImportUploadHandlerGUI(), $this->lng->txt('import_file'))
                ->withAcceptedMimeTypes(self::SUPPORTED_IMPORT_MIME_TYPES)
                ->withMaxFiles(1)
                ->withAdditionalTransformation($constraint);
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('import'),
            [],
            ['import_file' => $file_upload_input],
            $this->ctrl->getFormActionByClass(self::class, 'uploadQuestionsImport')
        )->withSubmitLabel($this->lng->txt('import'));
    }

    private function importQuestionPoolWithValidManifest(
        ilObjQuestionPool $obj,
        array $selected_questions,
        string $file_to_import
    ): void {

        ilSession::set('qpl_import_selected_questions', $selected_questions);
        $imp = new ilImport($this->request_data_collector->getRefId());
        $map = $imp->getMapping();
        $map->addMapping('components/ILIAS/TestQuestionPool', 'qpl', 'new_id', (string) $obj->getId());
        $imp->importObject($obj, $file_to_import, basename($file_to_import), 'qpl', 'components/ILIAS/TestQuestionPool', true);
    }

    private function importQuestionsFromQtiFile(
        ilObjQuestionPool $obj,
        array $selected_questions,
        string $qtifile,
        string $importdir,
        string $xmlfile = ''
    ): void {
        $qti_parser = new ilQTIParser(
            $importdir,
            $qtifile,
            ilQTIParser::IL_MO_PARSE_QTI,
            $obj->getId(),
            $selected_questions
        );
        $qti_parser->startParsing();

        if ($xmlfile === '') {
            return;
        }

        $cont_parser = new ilQuestionPageParser(
            $obj,
            $xmlfile,
            $importdir
        );
        $cont_parser->setQuestionMapping($qti_parser->getImportMapping());
        $cont_parser->startParsing();
    }

    private function cleanupAfterImport(string $importdir): void
    {
        ilFileUtils::delDir($importdir);
        $this->deleteUploadedImportFile(ilSession::get('path_to_uploaded_file_in_temp_dir'));
        ilSession::clear('path_to_import_file');
        ilSession::clear('path_to_uploaded_file_in_temp_dir');
    }

    public function createQuestionObject(): void
    {
        $form = $this->buildQuestionCreationForm()->withRequest($this->request);
        $data_with_section = $form->getData();
        if ($data_with_section === null) {
            $this->createQuestionFormObject($form);
            return;
        }
        $data = $data_with_section[0];

        $this->ctrl->setReturnByClass(self::class, self::DEFAULT_CMD);

        /** @var assQuestionGUI $question_gui */
        $question_gui = assQuestionGUI::_getQuestionGUI(
            ilObjQuestionPool::getQuestionTypeByTypeId($data['question_type'])
        );
        $question = $question_gui->getObject();
        $question->setObjId($this->object->getId());
        $question->setAdditionalContentEditingMode($data['editing_type']);
        $question_gui->setObject($question);
        $question_gui->setQuestionTabs();
        $question_gui->editQuestion();
    }

    public function afterSave(ilObject $new_object): void
    {
        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('object_added'), true);

        ilUtil::redirect(
            'ilias.php?ref_id=' . $new_object->getRefId() .
            '&baseClass=ilObjQuestionPoolGUI'
        );
    }

    public function confirmDeleteQuestions(array $ids): void
    {
        $rbacsystem = $this->rbac_system;

        $questionIdsToDelete = array_filter(array_map('intval', $ids));
        if (0 === count($questionIdsToDelete)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('qpl_delete_select_none'), true);
            $this->ctrl->redirect($this, self::DEFAULT_CMD);
        }

        $this->tpl->setOnScreenMessage('question', $this->lng->txt('qpl_confirm_delete_questions'));
        $deleteable_questions = $this->object->getDeleteableQuestionDetails($questionIdsToDelete);
        $table_gui = new ilQuestionBrowserTableGUI($this, self::DEFAULT_CMD, (($rbacsystem->checkAccess('write', $this->request_data_collector->getRefId()) ? true : false)), true);
        $table_gui->setShowRowsSelector(false);
        $table_gui->setLimit(PHP_INT_MAX);
        $table_gui->setEditable($rbacsystem->checkAccess('write', $this->request_data_collector->getRefId()));
        $table_gui->setData($deleteable_questions);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }

    /**
     * delete questions confirmation screen
     */
    public function deleteQuestionsObject(): void
    {
        $rbacsystem = $this->rbac_system;

        $questionIdsToDelete = $this->request_data_collector->isset('q_id') ? (array) $this->request_data_collector->raw('q_id') : [];
        if ($questionIdsToDelete === [] && $this->request_data_collector->isset('q_id')) {
            $questionIdsToDelete = [$this->request_data_collector->getQuestionId()];
        }

        $questionIdsToDelete = array_filter(array_map('intval', $questionIdsToDelete));
        if ($questionIdsToDelete === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('qpl_delete_select_none'), true);
            $this->ctrl->redirect($this, self::DEFAULT_CMD);
        }

        $this->tpl->setOnScreenMessage('question', $this->lng->txt('qpl_confirm_delete_questions'));
        $deleteable_questions = &$this->object->getDeleteableQuestionDetails($questionIdsToDelete);
        $table_gui = new ilQuestionBrowserTableGUI(
            $this,
            self::DEFAULT_CMD,
            (($rbacsystem->checkAccess('write', $this->request_data_collector->getRefId()) ? true : false)),
            true
        );
        $table_gui->setShowRowsSelector(false);
        $table_gui->setLimit(PHP_INT_MAX);
        $table_gui->setEditable($rbacsystem->checkAccess('write', $this->request_data_collector->getRefId()));
        $table_gui->setData($deleteable_questions);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }

    /**
     * delete questions after confirmation
     */
    public function confirmDeleteQuestionsObject(): void
    {
        $qst_ids = $this->request_data_collector->intArray('q_id');
        foreach ($qst_ids as $value) {
            $this->object->deleteQuestion((int) $value);
            $this->object->cleanupClipboard((int) $value);
        }
        if ($qst_ids !== []) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('qpl_questions_deleted'), true);
        }

        $this->ctrl->setParameter($this, 'q_id', '');
        $this->ctrl->redirect($this, self::DEFAULT_CMD);
    }

    public function cancelDeleteQuestionsObject(): void
    {
        $this->ctrl->redirect($this, self::DEFAULT_CMD);
    }

    public function exportQuestions(array $ids): void
    {
        if ($ids !== []) {
            $qpl_exp = new ilQuestionpoolExport($this->object, 'xml', $ids);
            // @PHP8-CR: This seems to be a pointer to an issue with exports. I like to leave this open for now and
            // schedule a thorough examination / analysis for later, eventually involved T&A TechSquad
            $export_file = $qpl_exp->buildExportFile();
            $filename = $export_file;
            $filename = preg_replace('/.*\//', '', $filename);
            if ($export_file === '') {
                $export_file = 'StandIn';
            }
            ilFileDelivery::deliverFileLegacy($export_file, $filename);
            exit();
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('qpl_export_select_none'), true);
        }
    }

    protected function renoveImportFailsObject(): void
    {
        $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->object->getId());
        $qsaImportFails->deleteRegisteredImportFails();

        $this->ctrl->redirectByClass(
            [
                ilRepositoryGUI::class,
                self::class,
                ilInfoScreenGUI::class
            ]
        );
    }

    /**
     * list questions of question pool
     */
    public function questionsObject(?RoundTripModal $import_questions_modal = null): void
    {
        if (!$this->access->checkAccess("read", "", $this->request_data_collector->getRefId())) {
            $this->infoScreenForward();
            return;
        }

        $this->object->purgeQuestions();
        $qsa_import_fails = new ilAssQuestionSkillAssignmentImportFails($this->object->getId());
        if ($qsa_import_fails->failedImportsRegistered()) {
            $button = $this->ui_factory->button()->standard(
                $this->lng->txt('ass_skl_import_fails_remove_btn'),
                $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, self::class], 'renoveImportFails')
            );
            $this->tpl->setOnScreenMessage(
                'failure',
                $qsa_import_fails->getFailedImportsMessage($this->lng) . '<br />' . $this->ui_renderer->render(
                    $button
                )
            );
        }

        $out = [];
        if ($this->rbac_system->checkAccess('write', $this->request_data_collector->getRefId())) {
            $btn = $this->ui_factory->button()->primary(
                $this->lng->txt('ass_create_question'),
                $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, self::class], 'createQuestionForm')
            );
            $this->toolbar->addComponent($btn);

            if ($import_questions_modal === null) {
                $import_questions_modal = $this->buildImportQuestionsModal();
            }

            $btn_import = $this->ui_factory->button()->standard(
                $this->lng->txt('import'),
                $import_questions_modal->getShowSignal()
            );
            $this->toolbar->addComponent($btn_import);
            $out[] = $this->ui_renderer->render($import_questions_modal);

            if (ilSession::get('qpl_clipboard') != null && count(ilSession::get('qpl_clipboard'))) {
                $btn_paste = $this->ui_factory->button()->standard(
                    $this->lng->txt('paste'),
                    $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, self::class], 'paste')
                );
                $this->toolbar->addComponent($btn_paste);
            }
        }

        $this->tpl->setPermanentLink($this->object->getType(), $this->object->getRefId());
        $out[] = $this->getTable();
        $this->tpl->setContent(implode('', $out));
    }

    protected function fetchAuthoringQuestionIdParamater(): int
    {
        $q_id = $this->request_data_collector->getQuestionId();

        if ($q_id === 0 || $this->object->checkQuestionParent($q_id)) {
            return $q_id;
        }

        throw new ilTestQuestionPoolException('question id does not relate to parent object!');
    }

    private function createQuestionFormObject(?Form $form = null): void
    {
        $this->help->setScreenId('assQuestions');
        if ($this->global_test_settings->isPageEditorEnabled()) {
            $this->help->setSubScreenId('createQuestion_editMode');
        } else {
            $this->help->setSubScreenId('createQuestion');
        }

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $form ?? $this->buildQuestionCreationForm()
            )
        );
    }

    private function buildQuestionCreationForm(): Form
    {
        $inputs['question_type'] = $this->buildInputQuestionType();
        $inputs['editing_type'] = $this->buildInputEditingType();

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

    private function buildInputEditingType(): Input
    {
        if (!$this->global_test_settings->isPageEditorEnabled()) {
            return $this->ui_factory->input()->field()->hidden()->withValue(
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE
            );
        }

        return $this->ui_factory->input()->field()->radio($this->lng->txt('tst_add_quest_cont_edit_mode'))
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

    public function printObject(): void
    {
        $this->tabs_gui->activateTab('print_view');
        $this->ctrl->setParameter($this, 'output', 'overview');
        $output_link = $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, self::class], 'print');
        $this->ctrl->setParameter($this, 'output', 'detailed_output_solutions');
        $output_link_detailed = $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, self::class], 'print');
        $this->ctrl->setParameter($this, 'output', 'detailed_output_printview');
        $output_link_printview = $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, self::class], 'print');

        $mode = $this->ui_factory->dropdown()->standard([
            $this->ui_factory->button()->shy($this->lng->txt('overview'), $output_link),
            $this->ui_factory->button()->shy($this->lng->txt('detailed_output_solutions'), $output_link_detailed),
            $this->ui_factory->button()->shy($this->lng->txt('detailed_output_printview'), $output_link_printview)
        ])->withLabel($this->lng->txt('output_mode'));

        $output = $this->request_data_collector->raw('output') ?? '';

        $table_gui = new ilQuestionPoolPrintViewTableGUI($this, 'print', $output);
        $data = $this->object->getPrintviewQuestions();
        $totalPoints = 0;
        foreach ($data as $d) {
            $totalPoints += $d['points'];
        }
        $table_gui->setTotalPoints($totalPoints);
        $table_gui->initColumns();
        $table_gui->setData($data);
        $this->tpl->setContent($this->ui_renderer->render($mode) . $table_gui->getHTML());
    }

    public function updateObject(): void
    {
        $this->object->update();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
    }

    public function pasteObject(): void
    {
        if (ilSession::get('qpl_clipboard') != null) {
            if ($this->object->pasteFromClipboard()) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('qpl_paste_success'), true);
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('qpl_paste_error'), true);
            }
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('qpl_paste_no_objects'), true);
        }
        $this->ctrl->redirect($this, self::DEFAULT_CMD);
    }

    public function copyQuestions(array $ids): void
    {
        if ($ids) {
            foreach ($ids as $id) {
                $this->object->copyToClipboard($id);
            }
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('qpl_copy_insert_clipboard'), true);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('qpl_copy_select_none'), true);
        }
    }

    public function moveQuestions(array $ids): void
    {
        if ($ids) {
            foreach ($ids as $id) {
                $this->object->moveToClipboard($id);
            }
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('qpl_move_insert_clipboard'), true);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('qpl_move_select_none'), true);
        }
    }

    public function createExportExcel(): void
    {
        $rbacsystem = $this->rbac_system;
        if ($rbacsystem->checkAccess('write', $this->request_data_collector->getRefId())) {
            $question_ids = &$this->object->getAllQuestionIds();
            $qpl_exp = new ilQuestionpoolExport($this->object, 'xlsx', $question_ids);
            $qpl_exp->buildExportFile();
            $this->ctrl->redirectByClass('ilquestionpoolexportgui', '');
        }
    }

    public function editQuestionForTestObject(): void
    {
        $this->ctrl->redirectByClass(ilAssQuestionPreviewGUI::class, 'show');
    }

    protected function importQuestionsFile(string $path_to_uploaded_file_in_temp_dir): void
    {
        if (!$this->temp_file_system->hasDir($path_to_uploaded_file_in_temp_dir)
            || ($files = $this->temp_file_system->listContents($path_to_uploaded_file_in_temp_dir)) === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('obj_import_file_error'));
        }

        $file_to_import = $this->import_temp_directory . DIRECTORY_SEPARATOR . $files[0]->getPath();
        $qtifile = $file_to_import;
        $importdir = dirname($file_to_import);


        if ($this->temp_file_system->getMimeType($files[0]->getPath()) === MimeType::APPLICATION__ZIP) {
            $options = (new ILIAS\Filesystem\Util\Archive\UnzipOptions())
                ->withZipOutputPath($this->getImportTempDirectory());
            $unzip = $this->archives->unzip($this->temp_file_system->readStream($files[0]->getPath()), $options);
            $unzip->extract();
            list($subdir, $importdir, $xmlfile, $qtifile) = $this->buildImportDirectoriesFromImportFile($file_to_import);
        }
        if (!file_exists($qtifile)) {
            ilFileUtils::delDir($importdir);
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('cannot_find_xml'), true);
            $this->questionsObject();
            return;
        }

        ilSession::set('path_to_import_file', $file_to_import);
        ilSession::set('path_to_uploaded_file_in_temp_dir', $path_to_uploaded_file_in_temp_dir);

        $form = $this->buildImportQuestionsSelectionForm(
            'importVerifiedQuestionsFile',
            $importdir,
            $qtifile,
            $path_to_uploaded_file_in_temp_dir
        );

        if ($form === null) {
            return;
        }

        $panel = $this->ui_factory->panel()->standard(
            $this->lng->txt('import_question'),
            [
                $this->ui_factory->legacy()->content($this->lng->txt('qpl_import_verify_found_questions')),
                $form
            ]
        );
        $this->tpl->setContent($this->ui_renderer->render($panel));
        $this->tpl->printToStdout();
        exit;
    }

    protected function importFile(string $file_to_import, string $path_to_uploaded_file_in_temp_dir): void
    {
        list($subdir, $importdir, $xmlfile, $qtifile) = $this->buildImportDirectoriesFromImportFile($file_to_import);

        $options = (new ILIAS\Filesystem\Util\Archive\UnzipOptions())
            ->withZipOutputPath($this->getImportTempDirectory());

        $unzip = $this->archives->unzip(Streams::ofResource(fopen($file_to_import, 'r')), $options);
        $unzip->extract();

        if (!file_exists($qtifile)) {
            ilFileUtils::delDir($importdir);
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('cannot_find_xml'), true);
            return;
        }

        ilSession::set('path_to_import_file', $file_to_import);
        ilSession::set('path_to_uploaded_file_in_temp_dir', $path_to_uploaded_file_in_temp_dir);

        $this->ctrl->setParameterByClass(self::class, 'new_type', $this->type);
        $form = $this->buildImportQuestionsSelectionForm(
            'importVerifiedFile',
            $importdir,
            $qtifile,
            $path_to_uploaded_file_in_temp_dir
        );

        if ($form === null) {
            return;
        }

        $panel = $this->ui_factory->panel()->standard(
            $this->lng->txt('import_qpl'),
            [
                $this->ui_factory->legacy()->content($this->lng->txt('qpl_import_verify_found_questions')),
                $form
            ]
        );
        $this->tpl->setContent($this->ui_renderer->render($panel));
        $this->tpl->printToStdout();
        exit;
    }

    public function addLocatorItems(): void
    {
        $ilLocator = $this->locator;

        switch ($this->ctrl->getCmd()) {
            case 'create':
            case 'importFile':
            case 'cancel':
                break;
            default:
                $this->ctrl->clearParameterByClass(self::class, 'q_id');
                $ilLocator->addItem(
                    $this->object->getTitle(),
                    $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, self::class], ''),
                    '',
                    $this->request_data_collector->getRefId()
                );
                $this->ctrl->setParameter($this, 'q_id', $this->request_data_collector->getQuestionId());
                break;
        }

        if (!is_array($this->request_data_collector->raw('q_id')) && $this->request_data_collector->raw('q_id') > 0 && $this->request_data_collector->raw(
            'cmd'
        ) !== self::DEFAULT_CMD) {
            $question_gui = assQuestionGUI::_getQuestionGUI('', $this->request_data_collector->getQuestionId());
            if ($question_gui !== null && $question_gui->getObject() instanceof assQuestion) {
                $question = $question_gui->getObject();
                $question->setObjId($this->object->getId());
                $question_gui->setObject($question);
                $title = $question_gui->getObject()->getTitle();
                if (!$title) {
                    $title = $this->lng->txt('new') . ': ' . $this->questionrepository->getForQuestionId(
                        $question_gui->getObject()->getId()
                    )->getTypeName($this->lng);
                }
                $ilLocator->addItem($title, $this->ctrl->getLinkTargetByClass(get_class($question_gui), 'editQuestion'));
            } else {
                // Workaround for context issues: If no object was found, redirect without q_id parameter
                $this->ctrl->setParameter($this, 'q_id', '');
                $this->ctrl->redirect($this);
            }
        }
    }

    /**
     * called by prepare output
     */
    public function setTitleAndDescription(): void
    {
        parent::setTitleAndDescription();

        if (!is_array($this->request_data_collector->raw('q_id')) && $this->request_data_collector->raw('q_id') > 0 && $this->request_data_collector->raw(
            'cmd'
        ) !== self::DEFAULT_CMD) {
            $question_gui = assQuestionGUI::_getQuestionGUI('', $this->request_data_collector->getQuestionId());
            if ($question_gui->getObject() instanceof assQuestion) {
                $question = $question_gui->getObject();
                $question->setObjId($this->object->getId());
                $question_gui->setObject($question);
                $title = $this->object->getTitle() . ': ' . $question_gui->getObject()->getTitle();
                if (!$title) {
                    $title = $this->lng->txt('new') . ': ' . $this->questionrepository->getForQuestionId(
                        $question_gui->getObject()->getId()
                    )->getTypeName($this->lng);
                }
                $this->tpl->setTitle(
                    $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                        $title,
                    )
                );
                $this->tpl->setDescription(
                    $question_gui->getObject()->getDescriptionForHTMLOutput()
                );
                $this->tpl->setTitleIcon(ilObject2::_getIcon($this->object->getId(), 'big', $this->object->getType()));
            } else {
                // Workaround for context issues: If no object was found, redirect without q_id parameter
                $this->ctrl->setParameter($this, 'q_id', '');
                $this->ctrl->redirect($this);
            }
        } else {
            $this->tpl->setTitle(
                $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                    $this->object->getTitle()
                )
            );
            $this->tpl->setDescription(
                $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                    $this->object->getLongDescription()
                )
            );
            $this->tpl->setTitleIcon(ilObject2::_getIcon($this->object->getId(), 'big', $this->object->getType()));
        }
    }

    /**
     * adds tabs to tab gui object
     *
     * @param object $tabs_gui ilTabsGUI object
     */
    public function getTabs(): void
    {
        $with_write_access = $this->access->checkAccess('write', '', $this->object->getRefId());
        $with_read_access = $this->access->checkAccess('read', '', $this->object->getRefId());

        $this->help->setScreenIdComponent('qpl');

        switch ($this->ctrl->getNextClass()) {
            case '':
            case strtolower(ilInfoScreenGUI::class):
            case strtolower(ilPermissionGUI::class):
            case strtolower(ilObjectMetaDataGUI::class):
            case strtolower(ilExportGUI::class):
            case strtolower(ilQuestionPoolSkillAdministrationGUI::class):
                break;

            case strtolower(ilTaxonomySettingsGUI::class):
            case strtolower(ilObjQuestionPoolSettingsGeneralGUI::class):
                if ($with_write_access) {
                    $this->addSettingsSubTabs($this->tabs_gui);
                }

                break;

            default:
                return;
        }
        // questions
        $force_active = false;
        $commands = $this->request_data_collector->raw('cmd');
        if (is_array($commands)) {
            foreach ($commands as $key => $value) {
                if (preg_match('/^delete_.*/', $key, $matches) ||
                    preg_match('/^addSelectGap_.*/', $key, $matches) ||
                    preg_match('/^addTextGap_.*/', $key, $matches) ||
                    preg_match('/^deleteImage_.*/', $key, $matches) ||
                    preg_match('/^upload_.*/', $key, $matches) ||
                    preg_match('/^addSuggestedSolution_.*/', $key, $matches)
                ) {
                    $force_active = true;
                }
            }
        }

        $force_active = $force_active || $this->request_data_collector->isset('imagemap_x');
        if (!$force_active) {
            $force_active = strtolower($this->ctrl->getCmdClass()) === strtolower(self::class)
                || $this->ctrl->getCmdClass() === '' && $this->ctrl->getCmd() === ''
                ? true
                : false;
        }
        if ($with_read_access) {
            $this->tabs_gui->addTarget(
                'assQuestions',
                $this->ctrl->getLinkTargetByClass(
                    [ilRepositoryGUI::class, self::class],
                    self::DEFAULT_CMD
                ),
                [
                    self::DEFAULT_CMD,
                    'filter',
                    'resetFilter',
                    'createQuestion',
                    'importQuestions',
                    'deleteQuestions',
                    'filterQuestionBrowser',
                    'view',
                    'preview',
                    'editQuestion',
                    'exec_pg',
                    'addItem',
                    'upload',
                    'save',
                    'cancel',
                    'addSuggestedSolution',
                    'cancelExplorer',
                    'linkChilds',
                    'removeSuggestedSolution',
                    'add',
                    'addYesNo',
                    'addTrueFalse',
                    'createGaps',
                    'setMediaMode',
                    'uploadingImage',
                    'uploadingImagemap',
                    'addArea',
                    'deletearea',
                    'saveShape',
                    'back',
                    'addPair',
                    'uploadingJavaapplet',
                    'addParameter',
                    'assessment',
                    'addGIT',
                    'addST',
                    'addPG',
                    'delete',
                    'toggleGraphicalAnswers',
                    'deleteAnswer',
                    'deleteImage',
                    'removeJavaapplet'
                ],
                '',
                '',
                $force_active
            );
        }
        if ($with_read_access) {
            $this->tabs_gui->addTab(
                'info_short',
                $this->lng->txt('info_short'),
                $this->ctrl->getLinkTargetByClass(
                    [
                        ilRepositoryGUI::class,
                        self::class,
                        ilInfoScreenGUI::class
                    ]
                )
            );
        }

        if ($with_write_access) {
            // properties
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTargetByClass(ilObjQuestionPoolSettingsGeneralGUI::class),
                [],
                [ilObjQuestionPoolSettingsGeneralGUI::class, ilObjTaxonomyGUI::class]
            );

            // skill service
            if ($this->isSkillsTabRequired()) {
                $link = $this->ctrl->getLinkTargetByClass(
                    [ilQuestionPoolSkillAdministrationGUI::class, ilAssQuestionSkillAssignmentsGUI::class],
                    ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
                );

                $this->tabs_gui->addTarget('qpl_tab_competences', $link, [], []);
            }
        }

        if ($with_read_access) {
            // print view
            $this->tabs_gui->addTarget(
                'print_view',
                $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, self::class], 'print'),
                ['print'],
                '',
                ''
            );
        }

        if ($with_write_access) {
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTarget(
                    'meta_data',
                    $mdtab,
                    '',
                    'ilmdeditorgui'
                );
            }
        }

        if ($with_write_access) {
            $this->tabs_gui->addTarget(
                'export',
                $this->ctrl->getLinkTargetByClass(ilExportGUI::class, ''),
                '',
                'ilexportgui'
            );
        }

        if ($this->access->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, self::class, ilPermissionGUI::class], 'perm'),
                ['perm', 'info', 'owner'],
                'ilpermissiongui'
            );
        }
    }

    private function isSkillsTabRequired(): bool
    {
        if (!($this->object instanceof ilObjQuestionPool)) {
            return false;
        }

        if (!$this->object->isSkillServiceEnabled()) {
            return false;
        }

        if (!ilObjQuestionPool::isSkillManagementGloballyActivated()) {
            return false;
        }

        return true;
    }

    private function addSettingsSubTabs(ilTabsGUI $tabs): void
    {
        $tabs->addSubTab(
            ilObjQuestionPoolSettingsGeneralGUI::TAB_COMMON_SETTINGS,
            $this->lng->txt('qpl_settings_subtab_general'),
            $this->ctrl->getLinkTargetByClass(ilObjQuestionPoolSettingsGeneralGUI::class),
        );

        $tabs->addSubTab(
            'tax_settings',
            $this->lng->txt('qpl_settings_subtab_taxonomies'),
            $this->ctrl->getLinkTargetByClass(ilTaxonomySettingsGUI::class, ''),
        );
    }

    public function infoScreenObject(): void
    {
        $this->ctrl->redirectByClass(
            [
                ilRepositoryGUI::class,
                self::class,
                ilInfoScreenGUI::class
            ]
        );
    }

    public function infoScreenForward(): void
    {
        if (!$this->access->checkAccess('visible', '', $this->ref_id)) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'));
        }

        $this->tabs_gui->activateTab('info_short');
        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        $this->ctrl->forwardCommand($info);
    }

    public static function _goto($a_target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ctrl = $DIC['ilCtrl'];

        if ($ilAccess->checkAccess('write', '', (int) $a_target)
            || $ilAccess->checkAccess('read', '', (int) $a_target)
        ) {
            $ctrl->setParameterByClass(ilObjQuestionPoolGUI::class, 'ref_id', $a_target);
            $ctrl->redirectByClass([ilRepositoryGUI::class, ilObjQuestionPoolGUI::class], self::DEFAULT_CMD);
            return;
        }
        if ($ilAccess->checkAccess('visible', '', $a_target)) {
            $DIC->ctrl()->setParameterByClass(ilInfoScreenGUI::class, 'ref_id', $a_target);
            $DIC->ctrl()->redirectByClass(
                [
                    ilRepositoryGUI::class,
                    self::class,
                    ilInfoScreenGUI::class
                ]
            );
        }
        if ($ilAccess->checkAccess('read', '', ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage(
                'info',
                sprintf(
                    $lng->txt('msg_no_perm_read_item'),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                ),
                true
            );
            ilObjectGUI::_gotoRepositoryRoot();
            return;
        }
        $ilErr->raiseError($lng->txt('msg_no_perm_read_lm'), $ilErr->FATAL);
    }

    protected function getTable(): string
    {
        $f = $this->ui_factory;
        $r = $this->ui_renderer;

        $table = new QuestionTable(
            $f,
            $r,
            $this->data_factory,
            $this->refinery,
            $this->url_builder,
            $this->action_parameter_token,
            $this->row_id_token,
            $this->db,
            $this->lng,
            $this->component_repository,
            $this->rbac_system,
            $this->user,
            $this->taxonomy->domain(),
            $this->notes_service,
            $this->object->getId(),
            $this->request_data_collector->getRefId()
        );

        /**
         * Filters should be part of the Table; for now, since they are not fully
         * integrated, they are rendered and applied seperately
         */
        $filter_action = $this->ctrl->getLinkTarget($this, self::DEFAULT_CMD);
        $filter = $table->getFilter($this->ui_service, $filter_action);

        $filter_params = $this->ui_service->filter()->getData($filter);

        if ($filter_params) {
            foreach (array_filter($filter_params) as $item => $value) {
                switch ($item) {
                    case 'taxonomies':
                        foreach ($value as $tax_value) {
                            if ($tax_value === 'null') {
                                $table->addTaxonomyFilterNoTaxonomySet(true);
                            } else {
                                $tax_nodes = explode('-', $tax_value);
                                $tax_id = array_shift($tax_nodes);
                                $table->addTaxonomyFilter(
                                    $tax_id,
                                    $tax_nodes,
                                    $this->object->getId(),
                                    $this->object->getType()
                                );
                            }
                        }
                        break;
                    case 'commented':
                        $table->setCommentFilter((int) $value);
                        break;
                    default:
                        $table->addFieldFilter($item, $value);
                }
            }
        }

        return $r->render([
            $filter,
            $table->getTable()
            ->withRequest($this->request)
        ]);
    }
}
