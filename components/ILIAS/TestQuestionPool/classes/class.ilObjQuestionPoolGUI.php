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

use ILIAS\DI\RBACServices;
use ILIAS\Taxonomy\Service;
use Psr\Http\Message\ServerRequestInterface as HttpRequest;
use ILIAS\TestQuestionPool\QuestionInfoService as QuestionInfoService;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\GlobalScreen\Services as GlobalScreen;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\TestQuestionPool\Import\TestQuestionsImportTrait;
use ILIAS\FileUpload\MimeType;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;

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
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilQuestionPoolExportGUI, ilInfoScreenGUI, ilTaxonomySettingsGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilAssQuestionHintsGUI, ilAssQuestionFeedbackEditingGUI, ilLocalUnitConfigurationGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilObjQuestionPoolSettingsGeneralGUI, assFormulaQuestionGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilAssQuestionPreviewGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: assKprimChoiceGUI, assLongMenuGUI
 * @ilCtrl_Calls   ilObjQuestionPoolGUI: ilQuestionPoolSkillAdministrationGUI
 *
 * @ingroup components\ILIASTestQuestionPool
 *
 */
class ilObjQuestionPoolGUI extends ilObjectGUI implements ilCtrlBaseClassInterface
{
    use TestQuestionsImportTrait;
    public const SUPPORTED_IMPORT_MIME_TYPES = [MimeType::APPLICATION__ZIP, MimeType::TEXT__XML];
    private ilObjectCommonSettings $common_settings;
    private HttpRequest $http_request;
    private QuestionInfoService $questioninfo;
    protected Service $taxonomy;
    public ?ilObject $object;
    protected ILIAS\TestQuestionPool\InternalRequestService $qplrequest;
    protected ilDBInterface $db;
    protected RBACServices $rbac;
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

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->type = 'qpl';

        $this->db = $DIC['ilDB'];
        $this->rbac = $DIC->rbac();
        $this->log = $DIC['ilLog'];
        $this->help = $DIC['ilHelp'];
        $this->global_screen = $DIC['global_screen'];
        $this->component_factory = $DIC['component.factory'];
        $this->component_repository = $DIC['component.repository'];
        $this->navigation_history = $DIC['ilNavigationHistory'];
        $this->ui_service = $DIC->uiService();
        $this->questioninfo = $DIC->testQuestionPool()->questionInfo();
        $this->qplrequest = $DIC->testQuestionPool()->internal()->request();
        $this->taxonomy = $DIC->taxonomy();
        $this->http_request = $DIC->http()->request();
        $this->data_factory = new DataFactory();
        $this->archives = $DIC->archives();
        parent::__construct('', $this->qplrequest->raw('ref_id'), true, false);

        $this->ctrl->saveParameter($this, [
            'ref_id',
            'test_ref_id',
            'calling_test',
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
            "action", //this is the actions's parameter name
            "qids"   //this is the parameter name to be used for row-ids
        );
        $this->url_builder = $url_builder;
        $this->action_parameter_token = $action_parameter_token;
        $this->row_id_token = $row_id_token;

        $this->notes_service->gui()->initJavascript();
    }

    protected function getQueryParamString(string $param): ?string
    {
        if (!$this->request_wrapper->has($param)) {
            return null;
        }
        $trafo = $this->refinery->byTrying([
            $this->refinery->kindlyTo()->null(),
            $this->refinery->kindlyTo()->string()
        ]);
        return $this->request_wrapper->retrieve($param, $trafo);
    }

    protected function getQueryParamInt(string $param): ?int
    {
        if (!$this->request_wrapper->has($param)) {
            return null;
        }
        $trafo = $this->refinery->byTrying([
            $this->refinery->kindlyTo()->null(),
            $this->refinery->kindlyTo()->int()
        ]);
        return $this->request_wrapper->retrieve($param, $trafo);
    }

    public function executeCommand(): void
    {
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $ilNavigationHistory = $this->navigation_history;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilErr = $this->error;
        $ilTabs = $this->tabs_gui;
        $lng = $this->lng;
        $ilDB = $this->db;
        $component_repository = $this->component_repository;
        $ilias = $this->ilias;
        $randomGroup = $this->refinery->random();

        $writeAccess = $ilAccess->checkAccess('write', '', $this->qplrequest->getRefId());

        if ((!$ilAccess->checkAccess('read', '', $this->qplrequest->getRefId()))
            && (!$ilAccess->checkAccess('visible', '', $this->qplrequest->getRefId()))) {
            $ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
        }

        if (!$this->getCreationMode() &&
            $ilAccess->checkAccess('read', '', $this->qplrequest->getRefId())) {
            if ('qpl' === $this->object->getType()) {
                $ilNavigationHistory->addItem(
                    $this->qplrequest->getRefId(),
                    ilLink::_getLink($this->qplrequest->getRefId(), "qpl"),
                    'qpl',
                );
            }
        }

        $cmd = $this->ctrl->getCmd('questions');
        $next_class = $this->ctrl->getNextClass($this);
        $q_id = $this->getQueryParamInt('q_id');

        if (in_array($next_class, ['', 'ilobjquestionpoolgui']) && $cmd == 'questions') {
            $q_id = -1;
        }

        $this->prepareOutput();

        $this->tpl->addCss(ilUtil::getStyleSheetLocation('output', 'test_print.css'), 'print');

        $q_type = '';
        if (!(in_array($next_class, ['', 'ilobjquestionpoolgui']) && $cmd == 'questions') && $q_id < 1) {
            $q_type = $this->qplrequest->raw('sel_question_types');
        }
        if ($cmd !== 'createQuestion' && $cmd !== 'createQuestionForTest'
            && $next_class != 'ilassquestionpagegui') {
            if (($this->qplrequest->raw('test_ref_id') != '') || ($this->qplrequest->raw('calling_test'))) {
                $ref_id = $this->qplrequest->raw('test_ref_id');
                if (!$ref_id) {
                    $ref_id = $this->qplrequest->raw('calling_test');
                }
            }
        }
        switch ($next_class) {
            case 'ilcommonactiondispatchergui':
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjectmetadatagui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
                }
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'ilassquestionpreviewgui':
                if (!$ilAccess->checkAccess('read', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->ctrl->saveParameter($this, 'q_id');
                $gui = new ilAssQuestionPreviewGUI(
                    $this->ctrl,
                    $this->rbac_system,
                    $this->tabs_gui,
                    $this->tpl,
                    $this->lng,
                    $ilDB,
                    $ilUser,
                    $randomGroup,
                    $this->global_screen
                );

                $gui->initQuestion((int) $this->qplrequest->raw('q_id'), $this->object->getId());
                $gui->initPreviewSettings($this->object->getRefId());
                $gui->initPreviewSession($ilUser->getId(), $this->fetchAuthoringQuestionIdParamater());
                $gui->initHintTracking();

                $ilHelp = $this->help;
                $ilHelp->setScreenIdComponent('qpl');

                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilassquestionpagegui':
                if ($cmd == 'finishEditing') {
                    $this->ctrl->redirectByClass('ilassquestionpreviewgui', 'show');
                    break;
                }
                if ($cmd === 'edit' && !$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->tpl->setCurrentBlock('ContentStyle');
                $this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));
                $this->tpl->parseCurrentBlock();

                $this->tpl->setCurrentBlock('SyntaxStyle');
                $this->tpl->setVariable('LOCATION_SYNTAX_STYLESHEET', ilObjStyleSheet::getSyntaxStylePath());
                $this->tpl->parseCurrentBlock();
                $q_gui = assQuestionGUI::_getQuestionGUI('', $this->fetchAuthoringQuestionIdParamater());
                $q_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PREVIEW);
                $q_gui->setQuestionTabs();
                $q_gui->outAdditionalOutput();
                $q_gui->object->setObjId($this->object->getId());

                $q_gui->setTargetGuiClass(null);
                $q_gui->setQuestionActionCmd('');

                if ($this->object->getType() == 'qpl') {
                    $q_gui->addHeaderAction();
                }

                $question = $q_gui->object;

                if ($this->questioninfo->isInActiveTest($question->getObjId())) {
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
                $this->ctrl->setReturn($this, 'questions');
                $page_gui = new ilAssQuestionPageGUI($this->qplrequest->getQuestionId());
                $page_gui->obj->addUpdateListener(
                    $question,
                    'updateTimestamp'
                );
                $page_gui->setEditPreview(true);
                $page_gui->setEnabledTabs(false);
                if (strlen(
                    $this->ctrl->getCmd()
                ) == 0 && !isset($_POST['editImagemapForward_x'])) { // workaround for page edit imagemaps, keep in mind
                    // @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
                    // $this->ctrl->setCmdClass(get_class($page_gui));
                    // $this->ctrl->setCmd('preview');
                }
                $page_gui->setQuestionHTML([$q_gui->object->getId() => $q_gui->getPreview(true)]);
                $page_gui->setTemplateTargetVar('ADM_CONTENT');
                $page_gui->setOutputMode('edit');
                $page_gui->setHeader($question->getTitle());
                $page_gui->setPresentationTitle($question->getTitle());
                $ret = $this->ctrl->forwardCommand($page_gui);
                if ($ret != '') {
                    $tpl->setContent($ret);
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

            case 'ilquestionpoolexportgui':
                $exp_gui = new ilQuestionPoolExportGUI($this);
                $exp_gui->addFormat('xml', $this->lng->txt('qpl_export_xml'));
                $exp_gui->addFormat('xlsx', $this->lng->txt('qpl_export_excel'), $this, 'createExportExcel');
                $ret = $this->ctrl->forwardCommand($exp_gui);
                break;

            case 'ilinfoscreengui':
                $this->infoScreenForward();
                break;

            case 'ilassquestionhintsgui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->ctrl->setReturn($this, 'questions');
                $questionGUI = assQuestionGUI::_getQuestionGUI(
                    $q_type ?? '',
                    $this->fetchAuthoringQuestionIdParamater()
                );
                $questionGUI->object->setObjId($this->object->getId());
                $questionGUI->setQuestionTabs();

                if ($this->questioninfo->isInActiveTest($questionGUI->object->getObjId())) {
                    $this->tpl->setOnScreenMessage(
                        'failure',
                        $this->lng->txt('question_is_part_of_running_test'),
                        true
                    );
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }

                $ilHelp = $this->help;
                $ilHelp->setScreenIdComponent('qpl');

                if ($this->object->getType() == 'qpl' && $writeAccess) {
                    $questionGUI->addHeaderAction();
                }
                $gui = new ilAssQuestionHintsGUI($questionGUI);

                $gui->setEditingEnabled(
                    $this->access->checkAccess('write', '', $this->object->getRefId())
                );

                $ilCtrl->forwardCommand($gui);

                break;

            case 'illocalunitconfigurationgui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
                }
                $questionGUI = assQuestionGUI::_getQuestionGUI($q_type, $this->fetchAuthoringQuestionIdParamater());
                $questionGUI->object->setObjId($this->object->getId());
                $questionGUI->setQuestionTabs();

                $this->ctrl->setReturn($this, 'questions');
                $gui = new ilLocalUnitConfigurationGUI(
                    new ilUnitConfigurationRepository($this->qplrequest->getQuestionId())
                );
                $ilCtrl->forwardCommand($gui);
                break;

            case 'ilassquestionfeedbackeditinggui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->ctrl->setReturn($this, 'questions');
                $questionGUI = assQuestionGUI::_getQuestionGUI($q_type, $this->fetchAuthoringQuestionIdParamater());
                $questionGUI->object->setObjId($this->object->getId());
                $questionGUI->setQuestionTabs();

                if ($this->questioninfo->isInActiveTest($questionGUI->object->getObjId())) {
                    $this->tpl->setOnScreenMessage(
                        'failure',
                        $this->lng->txt('question_is_part_of_running_test'),
                        true
                    );
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }

                $ilHelp = $this->help;
                $ilHelp->setScreenIdComponent('qpl');

                if ($this->object->getType() == 'qpl' && $writeAccess) {
                    $questionGUI->addHeaderAction();
                }
                $gui = new ilAssQuestionFeedbackEditingGUI($questionGUI, $ilCtrl, $ilAccess, $tpl, $ilTabs, $lng);
                $ilCtrl->forwardCommand($gui);

                break;

            case 'ilobjquestionpoolsettingsgeneralgui':
                $gui = new ilObjQuestionPoolSettingsGeneralGUI(
                    $ilCtrl,
                    $ilAccess,
                    $lng,
                    $tpl,
                    $ilTabs,
                    $this,
                    $this->refinery,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->http_request,
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilTaxonomySettingsGUI::class):
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                /** @var ilObjQuestionPool $obj */
                $obj = $this->object;
                $forwarder = new ilObjQuestionPoolTaxonomyEditingCommandForwarder(
                    $this->object,
                    $ilDB,
                    $this->refinery,
                    $component_repository,
                    $ilCtrl,
                    $ilTabs,
                    $lng,
                    $this->taxonomy
                );

                $forwarder->forward();

                break;

            case 'ilquestionpoolskilladministrationgui':
                $obj = $this->object;
                $gui = new ilQuestionPoolSkillAdministrationGUI(
                    $ilias,
                    $ilCtrl,
                    $this->refinery,
                    $ilAccess,
                    $ilTabs,
                    $tpl,
                    $lng,
                    $ilDB,
                    $component_repository,
                    $obj,
                    $this->ref_id
                );

                $this->ctrl->forwardCommand($gui);

                break;


            case 'ilobjquestionpoolgui':
            case '':

                //table actions.
                if ($action = $this->getQueryParamString($this->action_parameter_token)) {
                    $ids = $this->request_wrapper->retrieve(
                        $this->row_id_token->getName(),
                        $this->refinery->custom()->transformation(fn($v) => $v)
                    );

                    if (is_null($ids)) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_questions_selected'), true);
                        $this->ctrl->redirect($this, 'questions');
                    }
                    if (! is_array($ids)) {
                        $ids = explode(',', $ids);
                    }
                    $ids = array_map('intval', $ids);

                    $class = strtolower(assQuestionGUI::_getGUIClassNameForId(current($ids)));
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
                            $ret = $this->moveQuestions($ids);
                            $this->ctrl->redirect($this, 'questions');
                            break;
                        case 'copy':
                            $this->copyQuestions($ids);
                            $this->ctrl->redirect($this, 'questions');
                            break;
                        case 'delete':
                            $this->confirmDeleteQuestions($ids);
                            break;
                        case 'export':
                            $this->exportQuestions($ids);
                            $this->ctrl->redirect($this, 'questions');
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

                        default:
                            throw new \Exception("'$action'" . " not implemented");
                    }
                    break;
                }


                if ($cmd == 'questions') {
                    $this->ctrl->setParameter($this, 'q_id', '');
                }
                $cmd .= 'Object';
                $ret = $this->$cmd();
                break;

            default:
                if (in_array($cmd, ['editQuestion', 'save', 'suggestedsolution']) && !$ilAccess->checkAccess(
                    'write',
                    '',
                    $this->object->getRefId()
                )) {
                    $this->redirectAfterMissingWrite();
                }

                if ($cmd === 'assessment' &&
                    $this->object->getType() === 'tst' &&
                    !$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->ctrl->setReturn($this, 'questions');

                $questionGUI = assQuestionGUI::_getQuestionGUI($q_type, $this->fetchAuthoringQuestionIdParamater());
                $questionGUI->setEditContext(assQuestionGUI::EDIT_CONTEXT_AUTHORING);
                $questionGUI->object->setObjId($this->object->getId());

                if (in_array(
                    $cmd,
                    ['editQuestion', 'save', 'suggestedsolution']
                ) && $this->questioninfo->isInActiveTest($questionGUI->object->getObjId())
                ) {
                    $this->tpl->setOnScreenMessage(
                        'failure',
                        $this->lng->txt('question_is_part_of_running_test'),
                        true
                    );
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }

                if ($this->object->getType() == 'qpl') {
                    $questionGUI->setTaxonomyIds($this->object->getTaxonomyIds());

                    if ($writeAccess) {
                        $questionGUI->addHeaderAction();
                    }
                }
                $questionGUI->setQuestionTabs();

                $ilHelp = $this->help;
                $ilHelp->setScreenIdComponent('qpl');
                $ret = $this->ctrl->forwardCommand($questionGUI);
                break;
        }

        if (!(strtolower($this->qplrequest->raw('baseClass')) == 'iladministrationgui'
                || strtolower($this->qplrequest->raw('baseClass')) == 'ilrepositorygui')
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
        $file = explode('_', $this->qplrequest->raw('file_id'));
        $fileObj = new ilObjFile($file[count($file) - 1], false);
        $fileObj->sendFile();
        exit;
    }

    /**
     * show fullscreen view
     */
    public function fullscreenObject(): void
    {
        $page_gui = new ilAssQuestionPageGUI($this->qplrequest->raw('pg_id'));
        $page_gui->showMediaFullscreen();
    }

    /**
     * download source code paragraph
     */
    public function download_paragraphObject(): void
    {
        $pg_obj = new ilAssQuestionPage($this->qplrequest->raw('pg_id'));
        $pg_obj->sendParagraph($this->qplrequest->raw('par_id'), $this->qplrequest->raw('downloadtitle'));
        exit;
    }

    public function importVerifiedFileObject(): void
    {
        $file_to_import = ilSession::get('path_to_import_file');
        list($subdir, $importdir, $xmlfile, $qtifile) = $this->buildImportDirectoriesFromImportFile($file_to_import);

        $new_obj = new ilObjQuestionPool(0, true);
        $new_obj->setType($this->qplrequest->raw('new_type'));
        $new_obj->setTitle('dummy');
        $new_obj->setDescription('questionpool import');
        $new_obj->create(true);
        $new_obj->createReference();
        $new_obj->putInTree($this->qplrequest->getRefId());
        $new_obj->setPermissions($this->qplrequest->getRefId());

        $selected_questions = $this->retrieveSelectedQuestionsFromImportQuestionsSelectionForm(
            'importVerifiedFile',
            $importdir,
            $qtifile
        );

        if (is_file($importdir . DIRECTORY_SEPARATOR . 'manifest.xml')) {
            $this->importQuestionPoolWithValidManifest(
                $new_obj,
                $selected_questions,
                'importVerifiedFile',
                $importdir,
                $qtifile,
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

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('questions_imported'), true);
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
                $file_to_import
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
                $qtifile
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
        string $import_cmd,
        string $importdir,
        string $qtifile,
        string $file_to_import
    ): void {

        ilSession::set('qpl_import_selected_questions', $selected_questions);
        $imp = new ilImport($this->qplrequest->getRefId());
        $map = $imp->getMapping();
        $map->addMapping('components/ILIAS/TestQuestionPool', 'qpl', 'new_id', $obj->getId());
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
        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $addContEditMode = $_POST['add_quest_cont_edit_mode'];
        } else {
            $addContEditMode = assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE;
        }
        $q_gui = assQuestionGUI::_getQuestionGUI($_POST['sel_question_types']);
        $q_gui->object->setObjId($this->object->getId());
        $q_gui->object->setAdditionalContentEditingMode($addContEditMode);
        $q_gui->object->createNewQuestion();
        $this->ctrl->setParameterByClass(get_class($q_gui), 'q_id', $q_gui->object->getId());
        $this->ctrl->setParameterByClass(get_class($q_gui), 'sel_question_types', $_POST['sel_question_types']);
        $this->ctrl->redirectByClass(get_class($q_gui), 'editQuestion');
    }

    public function createQuestionForTestObject(): void
    {
        if (!$this->qplrequest->raw('q_id')) {
            if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
                $add_cont_edit_mode = $this->qplrequest->raw('add_quest_cont_edit_mode');
            } else {
                $add_cont_edit_mode = assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE;
            }
            $q_gui = assQuestionGUI::_getQuestionGUI($this->qplrequest->raw('sel_question_types'));
            $q_gui->object->setObjId($this->object->getId());
            $q_gui->object->setAdditionalContentEditingMode($add_cont_edit_mode);
            $q_gui->object->createNewQuestion();

            $class = get_class($q_gui);
            $q_id = $q_gui->object->getId();
        } else {
            $class = $this->qplrequest->raw('sel_question_types') . 'gui';
            $q_id = $this->qplrequest->raw('q_id');
        }

        $this->ctrl->setParameterByClass($class, 'q_id', $q_id);
        $this->ctrl->setParameterByClass($class, 'sel_question_types', $this->qplrequest->raw('sel_question_types'));
        $this->ctrl->setParameterByClass($class, 'prev_qid', $this->qplrequest->raw('prev_qid'));

        $this->ctrl->redirectByClass($class, 'editQuestion');
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

    public function questionObject(): void
    {
        // @PHP8-CR: With this probably never working and no detectable usages, it would be a candidate for removal...
        // but it is one of the magic command-methods ($cmd.'Object' - pattern) so I live to leave this in here for now
        // until it can be further investigated.
        $type = $this->qplrequest->raw('sel_question_types');
        $this->editQuestionForm($type);
    }

    public function confirmDeleteQuestions(array $ids): void
    {
        $rbacsystem = $this->rbac_system;

        $questionIdsToDelete = array_filter(array_map('intval', $ids));
        if (0 === count($questionIdsToDelete)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('qpl_delete_select_none'), true);
            $this->ctrl->redirect($this, 'questions');
        }

        $this->tpl->setOnScreenMessage('question', $this->lng->txt('qpl_confirm_delete_questions'));
        $deleteable_questions = $this->object->getDeleteableQuestionDetails($questionIdsToDelete);
        $table_gui = new ilQuestionBrowserTableGUI($this, 'questions', (($rbacsystem->checkAccess('write', $this->qplrequest->getRefId()) ? true : false)), true);
        $table_gui->setShowRowsSelector(false);
        $table_gui->setLimit(PHP_INT_MAX);
        $table_gui->setEditable($rbacsystem->checkAccess('write', $this->qplrequest->getRefId()));
        $table_gui->setData($deleteable_questions);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }

    /**
     * delete questions confirmation screen
     */
    public function deleteQuestionsObject(): void
    {
        $rbacsystem = $this->rbac_system;

        $questionIdsToDelete = $this->qplrequest->isset('q_id') ? (array) $this->qplrequest->raw('q_id') : [];
        if (0 === count($questionIdsToDelete) && $this->qplrequest->isset('q_id')) {
            $questionIdsToDelete = [$this->qplrequest->getQuestionId()];
        }

        $questionIdsToDelete = array_filter(array_map('intval', $questionIdsToDelete));
        if (0 === count($questionIdsToDelete)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('qpl_delete_select_none'), true);
            $this->ctrl->redirect($this, 'questions');
        }

        $this->tpl->setOnScreenMessage('question', $this->lng->txt('qpl_confirm_delete_questions'));
        $deleteable_questions = &$this->object->getDeleteableQuestionDetails($questionIdsToDelete);
        $table_gui = new ilQuestionBrowserTableGUI(
            $this,
            'questions',
            (($rbacsystem->checkAccess('write', $this->qplrequest->getRefId()) ? true : false)),
            true
        );
        $table_gui->setShowRowsSelector(false);
        $table_gui->setLimit(PHP_INT_MAX);
        $table_gui->setEditable($rbacsystem->checkAccess('write', $this->qplrequest->getRefId()));
        $table_gui->setData($deleteable_questions);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }

    /**
     * delete questions after confirmation
     */
    public function confirmDeleteQuestionsObject(): void
    {
        foreach ($_POST['q_id'] as $key => $value) {
            $this->object->deleteQuestion($value);
            $this->object->cleanupClipboard($value);
        }
        if (count($_POST['q_id'])) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('qpl_questions_deleted'), true);
        }

        $this->ctrl->setParameter($this, 'q_id', '');
        $this->ctrl->redirect($this, 'questions');
    }

    public function cancelDeleteQuestionsObject(): void
    {
        $this->ctrl->redirect($this, 'questions');
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

        $this->ctrl->redirect($this, 'infoScreen');
    }

    /**
     * list questions of question pool
     */
    public function questionsObject(RoundTripModal $import_questions_modal = null): void
    {
        if (!$this->access->checkAccess("read", "", $this->qplrequest->getRefId())) {
            $this->infoScreenForward();
            return;
        }

        if (get_class($this->object) == 'ilObjTest') {
            if ($this->qplrequest->raw('calling_test') > 0) {
                $ref_id = $this->qplrequest->raw('calling_test');
                $q_id = $this->qplrequest->raw('q_id');

                if ($this->qplrequest->raw('test_express_mode')) {
                    if ($q_id) {
                        ilUtil::redirect(
                            'ilias.php?ref_id=' . $ref_id . '&q_id=' . $q_id . '&test_express_mode=1&cmd=showPage&cmdClass=iltestexpresspageobjectgui&baseClass=ilObjTestGUI'
                        );
                    } else {
                        ilUtil::redirect(
                            'ilias.php?ref_id=' . $ref_id . '&test_express_mode=1&cmd=showPage&cmdClass=iltestexpresspageobjectgui&baseClass=ilObjTestGUI'
                        );
                    }
                } else {
                    ilUtil::redirect('ilias.php?baseClass=ilObjTestGUI&ref_id=' . $ref_id . '&cmd=questions');
                }
            }
        }

        $this->object->purgeQuestions();
        // reset test_id SESSION variable
        ilSession::set('test_id', '');
        $qsa_import_fails = new ilAssQuestionSkillAssignmentImportFails($this->object->getId());
        if ($qsa_import_fails->failedImportsRegistered()) {
            $button = $this->ui_factory->button()->standard(
                $this->lng->txt('ass_skl_import_fails_remove_btn'),
                $this->ctrl->getLinkTarget($this, 'renoveImportFails')
            );
            $this->tpl->setOnScreenMessage(
                'failure',
                $qsa_import_fails->getFailedImportsMessage($this->lng) . '<br />' . $this->ui_renderer->render(
                    $button
                )
            );
        }

        $out = [];

        if ($this->rbac_system->checkAccess('write', $this->qplrequest->getRefId())) {
            $toolbar = new ilToolbarGUI();
            $btn = $this->ui_factory->button()->primary(
                $this->lng->txt('ass_create_question'),
                $this->ctrl->getLinkTarget($this, 'createQuestionForm')
            );
            $toolbar->addComponent($btn);

            if ($import_questions_modal === null) {
                $import_questions_modal = $this->buildImportQuestionsModal();
            }

            $btn_import = $this->ui_factory->button()->standard(
                $this->lng->txt('import'),
                $import_questions_modal->getShowSignal()
            );
            $toolbar->addComponent($btn_import);
            $out[] = $this->ui_renderer->render($import_questions_modal);

            if (ilSession::get('qpl_clipboard') != null && count(ilSession::get('qpl_clipboard'))) {
                $btn_paste = $this->ui_factory->button()->standard(
                    $this->lng->txt('paste'),
                    $this->ctrl->getLinkTarget($this, 'paste')
                );
                $toolbar->addComponent($btn_paste);
            }

            $this->tpl->setContent(
                $out[] = $this->ctrl->getHTML($toolbar)
            );
        }

        $this->tpl->setPermanentLink($this->object->getType(), $this->object->getRefId());
        $out[] = $this->getTable();
        $this->tpl->setContent(implode('', $out));
    }

    protected function fetchAuthoringQuestionIdParamater(): int
    {
        $q_id = $this->qplrequest->getQuestionId();

        if ($this->object->checkQuestionParent($q_id)) {
            return $q_id;
        }

        throw new ilTestQuestionPoolException('question id does not relate to parent object!');
    }

    private function createQuestionFormObject(): void
    {
        $ilHelp = $this->help;

        $ilHelp->setScreenId('assQuestions');

        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $ilHelp->setSubScreenId('createQuestion_editMode');
        } else {
            $ilHelp->setSubScreenId('createQuestion');
        }

        $form = $this->buildCreateQuestionForm();

        $this->tpl->setContent($this->ctrl->getHTML($form));
    }

    private function buildCreateQuestionForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('ass_create_question'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $options = [];
        foreach ($this->object->getQuestionTypes(false, true, false) as $translation => $data) {
            $options[$data['type_tag']] = $translation;
        }
        $si = new ilSelectInputGUI($this->lng->txt('question_type'), 'sel_question_types');
        $si->setOptions($options);

        $form->addItem($si);

        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $ri = new ilRadioGroupInputGUI($this->lng->txt('tst_add_quest_cont_edit_mode'), 'add_quest_cont_edit_mode');

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
            $hi = new ilHiddenInputGUI('question_content_editing_type');
            $hi->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE);
            $form->addItem($hi, true);
        }

        // commands

        $form->addCommandButton('createQuestion', $this->lng->txt('create'));
        $form->addCommandButton('questions', $this->lng->txt('cancel'));

        return $form;
    }

    public function printObject(): void
    {
        $this->ctrl->setParameter($this, 'output', 'overview');
        $output_link = $this->ctrl->getLinkTarget($this, 'print');
        $this->ctrl->setParameter($this, 'output', 'detailed_output_solutions');
        $output_link_detailed = $this->ctrl->getLinkTarget($this, 'print');
        $this->ctrl->setParameter($this, 'output', 'detailed_output_printview');
        $output_link_printview = $this->ctrl->getLinkTarget($this, 'print');

        $mode = $this->ui_factory->dropdown()->standard([
            $this->ui_factory->button()->shy($this->lng->txt('overview'), $output_link),
            $this->ui_factory->button()->shy($this->lng->txt('detailed_output_solutions'), $output_link_detailed),
            $this->ui_factory->button()->shy($this->lng->txt('detailed_output_printview'), $output_link_printview)
        ])->withLabel($this->lng->txt('output_mode'));

        $output = $this->qplrequest->raw('output') ?? '';

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
        $this->ctrl->redirect($this, 'questions');
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
        if ($rbacsystem->checkAccess('write', $this->qplrequest->getRefId())) {
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
                $this->ui_factory->legacy($this->lng->txt('qpl_import_verify_found_questions')),
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
                $this->ui_factory->legacy($this->lng->txt('qpl_import_verify_found_questions')),
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
                    $this->ctrl->getLinkTarget($this, ''),
                    '',
                    $this->qplrequest->getRefId()
                );
                $this->ctrl->setParameter($this, 'q_id', $this->qplrequest->getQuestionId());
                break;
        }

        if (!is_array($this->qplrequest->raw('q_id')) && $this->qplrequest->raw('q_id') > 0 && $this->qplrequest->raw(
            'cmd'
        ) !== 'questions') {
            $q_gui = assQuestionGUI::_getQuestionGUI('', $this->qplrequest->raw('q_id'));
            if ($q_gui !== null && $q_gui->object instanceof assQuestion) {
                $q_gui->object->setObjId($this->object->getId());
                $title = $q_gui->object->getTitle();
                if (!$title) {
                    $title = $this->lng->txt('new') . ': ' . $this->questioninfo->getQuestionTypeName(
                        $q_gui->object->getId()
                    );
                }
                $ilLocator->addItem($title, $this->ctrl->getLinkTargetByClass(get_class($q_gui), 'editQuestion'));
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

        if (!is_array($this->qplrequest->raw('q_id')) && $this->qplrequest->raw('q_id') > 0 && $this->qplrequest->raw(
            'cmd'
        ) !== 'questions') {
            $q_gui = assQuestionGUI::_getQuestionGUI('', $this->qplrequest->getQuestionId());
            if ($q_gui->object instanceof assQuestion) {
                $q_gui->object->setObjId($this->object->getId());
                $title = $q_gui->object->getTitle();
                if (!$title) {
                    $title = $this->lng->txt('new') . ': ' . $this->questioninfo->getQuestionTypeName(
                        $q_gui->object->getId()
                    );
                }
                $this->tpl->setTitle(
                    strip_tags(
                        $title,
                        self::ALLOWED_TAGS_IN_TITLE_AND_DESCRIPTION
                    )
                );
                $this->tpl->setDescription(
                    $q_gui->object->getDescriptionForHTMLOutput()
                );
                $this->tpl->setTitleIcon(ilObject2::_getIcon($this->object->getId(), 'big', $this->object->getType()));
            } else {
                // Workaround for context issues: If no object was found, redirect without q_id parameter
                $this->ctrl->setParameter($this, 'q_id', '');
                $this->ctrl->redirect($this);
            }
        } else {
            $this->tpl->setTitle(
                strip_tags(
                    $this->object->getTitle(),
                    self::ALLOWED_TAGS_IN_TITLE_AND_DESCRIPTION
                )
            );
            $this->tpl->setDescription(
                strip_tags(
                    $this->object->getLongDescription(),
                    self::ALLOWED_TAGS_IN_TITLE_AND_DESCRIPTION
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
        $ilHelp = $this->help;

        $currentUserHasWriteAccess = $this->access->checkAccess('write', '', $this->object->getRefId());
        $currentUserHasReadAccess = $this->access->checkAccess('read', '', $this->object->getRefId());

        $ilHelp->setScreenIdComponent('qpl');

        $next_class = strtolower($this->ctrl->getNextClass());
        switch ($next_class) {
            case '':
            case 'ilpermissiongui':
            case 'ilobjectmetadatagui':
            case 'ilquestionpoolexportgui':
            case 'ilquestionpoolskilladministrationgui':
                break;

            case strtolower(ilTaxonomySettingsGUI::class):
            case 'ilobjquestionpoolsettingsgeneralgui':

                if ($currentUserHasWriteAccess) {
                    $this->addSettingsSubTabs($this->tabs_gui);
                }

                break;

            default:
                return;
                break;
        }
        // questions
        $force_active = false;
        $commands = $this->getQueryParamString('cmd');
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
        if (isset($_POST['imagemap_x'])) {
            $force_active = true;
        }
        if (!$force_active) {
            $force_active = ((strtolower($this->ctrl->getCmdClass()) == strtolower(get_class($this)) || strlen(
                $this->ctrl->getCmdClass()
            ) == 0) &&
                $this->ctrl->getCmd() == '')
                ? true
                : false;
        }
        if ($currentUserHasReadAccess) {
            $this->tabs_gui->addTarget(
                'assQuestions',
                $this->ctrl->getLinkTarget($this, 'questions'),
                [
                    'questions',
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
                    'saveEdit',
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
        if ($currentUserHasReadAccess) {
            $this->tabs_gui->addTarget(
                'info_short',
                $this->ctrl->getLinkTarget($this, 'infoScreen'),
                ['infoScreen', 'showSummary']
            );
        }

        if ($currentUserHasWriteAccess) {
            // properties
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTargetByClass('ilObjQuestionPoolSettingsGeneralGUI'),
                [],
                ['ilObjQuestionPoolSettingsGeneralGUI', 'ilObjTaxonomyGUI']
            );

            // skill service
            if ($this->isSkillsTabRequired()) {
                $link = $this->ctrl->getLinkTargetByClass(
                    ['ilQuestionPoolSkillAdministrationGUI', 'ilAssQuestionSkillAssignmentsGUI'],
                    ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
                );

                $this->tabs_gui->addTarget('qpl_tab_competences', $link, [], []);
            }
        }

        if ($currentUserHasReadAccess) {
            // print view
            $this->tabs_gui->addTarget(
                'print_view',
                $this->ctrl->getLinkTarget($this, 'print'),
                ['print'],
                '',
                ''
            );
        }

        if ($currentUserHasWriteAccess) {
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

        if ($currentUserHasWriteAccess) {
            $this->tabs_gui->addTarget(
                'export',
                $this->ctrl->getLinkTargetByClass('ilquestionpoolexportgui', ''),
                '',
                'ilquestionpoolexportgui'
            );
        }

        if ($this->access->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass([get_class($this), 'ilpermissiongui'], 'perm'),
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
            $this->ctrl->getLinkTargetByClass('ilObjQuestionPoolSettingsGeneralGUI'),
        );
        if ($this->object->getShowTaxonomies()) {
            $tabs->addSubTab(
                'tax_settings',
                $this->lng->txt('qpl_settings_subtab_taxonomies'),
                $this->ctrl->getLinkTargetByClass('ilTaxonomySettingsGUI', ''),
            );
        }
    }

    /**
     * this one is called from the info button in the repository
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     */
    public function infoScreenObject(): void
    {
        // @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
        // $this->ctrl->setCmd('showSummary');
        // $this->ctrl->setCmdClass('ilinfoscreengui');
        $this->infoScreenForward();
    }

    public function infoScreenForward(): void
    {
        if (!$this->access->checkAccess('visible', '', $this->ref_id)) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'));
        }
        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();

        // standard meta data
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
            $target_class = ilObjQuestionPoolGUI::class;
            $target_cmd = 'questions';
            $ctrl->setParameterByClass($target_class, 'ref_id', $a_target);
            $ctrl->redirectByClass([ilRepositoryGUI::class, $target_class], $target_cmd);
            return;
        }
        if ($ilAccess->checkAccess('visible', "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, 'infoScreen');
            return;
        }
        if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage(
                'info',
                sprintf(
                    $lng->txt("msg_no_perm_read_item"),
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
            $this->taxonomy->domain(),
            $this->notes_service,
            $this->object->getId(),
            (int) $this->qplrequest->getRefId()
        );

        /**
         * Filters should be part of the Table; for now, since they are not fully
         * integrated, they are rendered and applied seperately
         */
        $filter_action = $this->ctrl->getLinkTarget($this, 'questions');
        $filter = $table->getFilter($this->ui_service, $filter_action);

        $filter_params = $this->ui_service->filter()->getData($filter);
        if ($filter_params) {
            foreach (array_filter($filter_params) as $item => $value) {

                switch ($item) {
                    case 'taxonomies':
                        if($value === 'null') {
                            $table->addTaxonomyFilterNoTaxonomySet(true);
                        } else {
                            $tax_nodes = explode('-', $value);
                            $tax_id = array_shift($tax_nodes);
                            $table->addTaxonomyFilter(
                                $tax_id,
                                $tax_nodes,
                                $this->object->getId(),
                                $this->object->getType()
                            );
                        }
                        break;
                    case 'commented':
                        $table->setCommentFilter($value);
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
