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

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Class ilObjQuestionPoolGUI
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 *
 * @version		$Id$
 *
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilAssQuestionPageGUI, ilQuestionBrowserTableGUI, ilToolbarGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: assOrderingQuestionGUI, assImagemapQuestionGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: assNumericGUI, assTextSubsetGUI, assSingleChoiceGUI, ilPropertyFormGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: assTextQuestionGUI, ilObjectMetaDataGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilQuestionPoolExportGUI, ilInfoScreenGUI, ilObjTaxonomyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilAssQuestionHintsGUI, ilAssQuestionFeedbackEditingGUI, ilLocalUnitConfigurationGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilObjQuestionPoolSettingsGeneralGUI, assFormulaQuestionGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilAssQuestionPreviewGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: assKprimChoiceGUI, assLongMenuGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilQuestionPoolSkillAdministrationGUI
 *
 * @ingroup ModulesTestQuestionPool
 *
 */
class ilObjQuestionPoolGUI extends ilObjectGUI implements ilCtrlBaseClassInterface
{
    public ?ilObject $object;


    /**
    * Constructor
    * @access public
    */
    public function __construct()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $rbacsystem = $DIC['rbacsystem'];
        $lng->loadLanguageModule("assessment");
        $this->type = "qpl";
        $this->error = $DIC['ilErr'];
        $this->ctrl = &$ilCtrl;

        $this->ctrl->saveParameter($this, array(
            "ref_id", "test_ref_id", "calling_test", "test_express_mode", "q_id", 'tax_node', 'calling_consumer', 'consumer_context'
        ));
        $this->ctrl->saveParameter($this, "calling_consumer");
        $this->ctrl->saveParameterByClass('ilAssQuestionPageGUI', 'calling_consumer');
        $this->ctrl->saveParameterByClass('ilAssQuestionPageGUI', 'consumer_context');
        $this->ctrl->saveParameterByClass('ilobjquestionpoolgui', 'calling_consumer');
        $this->ctrl->saveParameterByClass('ilobjquestionpoolgui', 'consumer_context');
        $this->qplrequest = $DIC->testQuestionPool()->internal()->request();

        parent::__construct("", $this->qplrequest->raw("ref_id"), true, false);
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

    /**
     * execute command
     *
     * @global ilLocatorGUI $ilLocator
     * @global ilAccessHandler $ilAccess
     * @global ilNavigationHistory $ilNavigationHistory
     * @global ilTemplate $tpl
     * @global ilCtrl $ilCtrl
     * @global ilTabsGUI $ilTabs
     * @global ilLanguage $lng
     * @global ILIAS $ilias
     */
    public function executeCommand(): void
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilLocator = $DIC['ilLocator'];
        $ilAccess = $DIC['ilAccess'];
        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilErr = $DIC['ilErr'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];
        $component_repository = $DIC['component.repository'];
        $ilias = $DIC['ilias'];
        $randomGroup = $DIC->refinery()->random();

        $writeAccess = $ilAccess->checkAccess("write", "", $this->qplrequest->getRefId());

        if ((!$ilAccess->checkAccess("read", "", $this->qplrequest->getRefId()))
            && (!$ilAccess->checkAccess("visible", "", $this->qplrequest->getRefId()))) {
            global $DIC;
            $ilias = $DIC['ilias'];
            $ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
        }

        // add entry to navigation history
        if (!$this->getCreationMode() &&
            $ilAccess->checkAccess("read", "", $this->qplrequest->getRefId())) {
            if ('qpl' == $this->object->getType()) {
                $ilNavigationHistory->addItem(
                    $this->qplrequest->getRefId(),
                    "ilias.php?baseClass=ilObjQuestionPoolGUI&cmd=questions&ref_id=" . $this->qplrequest->getRefId(),
                    "qpl"
                );
            }
        }

        $cmd = $this->ctrl->getCmd("questions");
        $next_class = $this->ctrl->getNextClass($this);
        $q_id = $this->getQueryParamInt('q_id');

        if (in_array($next_class, array('', 'ilobjquestionpoolgui')) && $cmd == 'questions') {
            $q_id = -1;
        }

        $this->prepareOutput();

        $this->ctrl->setReturn($this, "questions");

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");

        $q_type = '';
        if (!(in_array($next_class, array('', 'ilobjquestionpoolgui')) && $cmd == 'questions') && $q_id < 1) {
            $q_type = $this->qplrequest->raw("sel_question_types");
        }
        if ($cmd != "createQuestion" && $cmd != "createQuestionForTest"
            && $next_class != "ilassquestionpagegui") {
            if (($this->qplrequest->raw("test_ref_id") != "") or ($this->qplrequest->raw("calling_test"))) {
                $ref_id = $this->qplrequest->raw("test_ref_id");
                if (!$ref_id) {
                    $ref_id = $this->qplrequest->raw("calling_test");
                }
            }
        }
        switch ($next_class) {
            case "ilcommonactiondispatchergui":
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
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->ctrl->saveParameter($this, "q_id");
                $gui = new ilAssQuestionPreviewGUI($this->ctrl, $this->tabs_gui, $this->tpl, $this->lng, $ilDB, $ilUser, $randomGroup);

                $gui->initQuestion((int) $this->qplrequest->raw('q_id'), $this->object->getId());
                $gui->initPreviewSettings($this->object->getRefId());
                $gui->initPreviewSession($ilUser->getId(), $this->fetchAuthoringQuestionIdParamater());
                $gui->initHintTracking();
                $gui->initStyleSheets();

                global $DIC;
                $ilHelp = $DIC['ilHelp'];
                $ilHelp->setScreenIdComponent("qpl");

                $this->ctrl->forwardCommand($gui);
                break;

            case "ilassquestionpagegui":
                if ($cmd == 'finishEditing') {
                    $this->ctrl->redirectByClass('ilassquestionpreviewgui', 'show');
                    break;
                }
                if ($cmd === 'edit' && !$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                $this->tpl->setCurrentBlock("ContentStyle");
                $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
                $this->tpl->parseCurrentBlock();

                // syntax style
                $this->tpl->setCurrentBlock("SyntaxStyle");
                $this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
                $this->tpl->parseCurrentBlock();
                $q_gui = assQuestionGUI::_getQuestionGUI("", $this->fetchAuthoringQuestionIdParamater());
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

                if ($question->isInActiveTest()) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("question_is_part_of_running_test"), true);
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }

                $this->ctrl->saveParameter($this, "q_id");
                $this->lng->loadLanguageModule("content");
                $this->ctrl->setReturnByClass("ilAssQuestionPageGUI", "view");
                $this->ctrl->setReturn($this, "questions");
                $page_gui = new ilAssQuestionPageGUI($this->qplrequest->getQuestionId());
                $page_gui->obj->addUpdateListener(
                    $question,
                    'updateTimestamp'
                );
                $page_gui->setEditPreview(true);
                $page_gui->setEnabledTabs(false);
                if (strlen($this->ctrl->getCmd()) == 0 && !isset($_POST["editImagemapForward_x"])) { // workaround for page edit imagemaps, keep in mind
                    $this->ctrl->setCmdClass(get_class($page_gui));
                    $this->ctrl->setCmd("preview");
                }
                $page_gui->setQuestionHTML(array($q_gui->object->getId() => $q_gui->getPreview(true)));
                $page_gui->setTemplateTargetVar("ADM_CONTENT");
                $page_gui->setOutputMode("edit");
                $page_gui->setHeader($question->getTitle());
                $page_gui->setPresentationTitle($question->getTitle());
                $ret = $this->ctrl->forwardCommand($page_gui);
                if ($ret != "") {
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

            case "ilquestionpoolexportgui":
                $exp_gui = new ilQuestionPoolExportGUI($this);
                $exp_gui->addFormat('xml', $this->lng->txt('qpl_export_xml'));
                $exp_gui->addFormat('xls', $this->lng->txt('qpl_export_excel'), $this, 'createExportExcel');
                $ret = $this->ctrl->forwardCommand($exp_gui);
                break;

            case "ilinfoscreengui":
                $this->infoScreenForward();
                break;

            case 'ilassquestionhintsgui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }
                // set return target
                $this->ctrl->setReturn($this, "questions");
                $questionGUI = assQuestionGUI::_getQuestionGUI($q_type ?? '', $this->fetchAuthoringQuestionIdParamater());
                $questionGUI->object->setObjId($this->object->getId());
                $questionGUI->setQuestionTabs();

                if ($questionGUI->object->isInActiveTest()) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("question_is_part_of_running_test"), true);
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }

                $ilHelp = $DIC['ilHelp'];
                $ilHelp->setScreenIdComponent("qpl");

                if ($this->object->getType() == 'qpl' && $writeAccess) {
                    $questionGUI->addHeaderAction();
                }
                $gui = new ilAssQuestionHintsGUI($questionGUI);

                $gui->setEditingEnabled(
                    $DIC->access()->checkAccess('write', '', $this->object->getRefId())
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

                // set return target
                $this->ctrl->setReturn($this, "questions");
                $questionGUI = assQuestionGUI::_getQuestionGUI($q_type, $this->fetchAuthoringQuestionIdParamater());
                $questionGUI->object->setObjId($this->object->getId());
                $questionGUI->setQuestionTabs();

                if ($questionGUI->object->isInActiveTest()) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("question_is_part_of_running_test"), true);
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }

                $ilHelp = $DIC['ilHelp'];
                $ilHelp->setScreenIdComponent("qpl");

                if ($this->object->getType() == 'qpl' && $writeAccess) {
                    $questionGUI->addHeaderAction();
                }
                $gui = new ilAssQuestionFeedbackEditingGUI($questionGUI, $ilCtrl, $ilAccess, $tpl, $ilTabs, $lng);
                $ilCtrl->forwardCommand($gui);

                break;

            case 'ilobjquestionpoolsettingsgeneralgui':
                $gui = new ilObjQuestionPoolSettingsGeneralGUI($ilCtrl, $ilAccess, $lng, $tpl, $ilTabs, $this);
                $this->ctrl->forwardCommand($gui);
                break;

            case "ilobjtaxonomygui":

                /** @var ilObjQuestionPool $obj */
                $obj = $this->object;
                $forwarder = new ilObjQuestionPoolTaxonomyEditingCommandForwarder(
                    $obj,
                    $ilDB,
                    $component_repository,
                    $ilCtrl,
                    $ilTabs,
                    $lng
                );

                $forwarder->forward();

                break;

            case 'ilquestionpoolskilladministrationgui':

                /** @var ilObjQuestionPool $obj */
                $obj = $this->object;
                $gui = new ilQuestionPoolSkillAdministrationGUI(
                    $ilias,
                    $ilCtrl,
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

            case 'ilquestionbrowsertablegui':
                $this->ctrl->forwardCommand($this->buildQuestionBrowserTableGUI($taxIds = array())); // no tax ids required
                break;

            case "ilobjquestionpoolgui":
            case "":

                if ($cmd == 'questions') {
                    $this->ctrl->setParameter($this, 'q_id', '');
                }

                $cmd .= "Object";
                $ret = $this->$cmd();
                break;

            default:
                if (in_array($cmd, ['editQuestion', 'save', 'suggestedsolution']) && !$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                if ($cmd === 'assessment' &&
                    $this->object->getType() === 'tst' &&
                    !$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $this->redirectAfterMissingWrite();
                }

                $this->ctrl->setReturn($this, "questions");

                $questionGUI = assQuestionGUI::_getQuestionGUI($q_type, $this->fetchAuthoringQuestionIdParamater());
                $questionGUI->setEditContext(assQuestionGUI::EDIT_CONTEXT_AUTHORING);
                $questionGUI->object->setObjId($this->object->getId());

                if (in_array($cmd, ['editQuestion', 'save', 'suggestedsolution']) && $questionGUI->object->isInActiveTest()) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("question_is_part_of_running_test"), true);
                    $this->ctrl->redirectByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW);
                }

                if ($this->object->getType() == 'qpl') {
                    $questionGUI->setTaxonomyIds($this->object->getTaxonomyIds());

                    if ($writeAccess) {
                        $questionGUI->addHeaderAction();
                    }
                }
                $questionGUI->setQuestionTabs();

                $ilHelp = $DIC['ilHelp'];
                $ilHelp->setScreenIdComponent("qpl");
                $ret = $this->ctrl->forwardCommand($questionGUI);
                break;
        }

        if (!(strtolower($this->qplrequest->raw("baseClass")) == "iladministrationgui"
                || strtolower($this->qplrequest->raw('baseClass')) == 'ilrepositorygui')
            && $this->getCreationMode() != true) {
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

    /**
     * Gateway for exports initiated from workspace, as there is a generic
     * forward to {objTypeMainGUI}::export()
     */
    protected function exportObject(): void
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $DIC->ctrl()->redirectByClass('ilQuestionPoolExportGUI');
    }

    /**
    * download file
    */
    public function downloadFileObject(): void
    {
        $file = explode("_", $this->qplrequest->raw("file_id"));
        $fileObj = new ilObjFile($file[count($file) - 1], false);
        $fileObj->sendFile();
        exit;
    }

    /**
    * show fullscreen view
    */
    public function fullscreenObject(): void
    {
        $page_gui = new ilAssQuestionPageGUI($this->qplrequest->raw("pg_id"));
        $page_gui->showMediaFullscreen();
    }


    /**
    * set question list filter
    */
    public function filterObject(): void
    {
        $this->questionsObject();
    }

    /**
    * resets filter
    */
    public function resetFilterObject(): void
    {
        $_POST["filter_text"] = "";
        $_POST["sel_filter_type"] = "";
        $this->questionsObject();
    }

    /**
    * download source code paragraph
    */
    public function download_paragraphObject(): void
    {
        $pg_obj = new ilAssQuestionPage($this->qplrequest->raw("pg_id"));
        $pg_obj->send_paragraph($this->qplrequest->raw("par_id"), $this->qplrequest->raw("downloadtitle"));
        exit;
    }

    /**
    * imports question(s) into the questionpool
    */
    public function uploadQplObject($questions_only = false)
    {
        $this->ctrl->setParameter($this, 'new_type', $this->qplrequest->raw('new_type'));
        if ($_FILES["xmldoc"]["error"] > UPLOAD_ERR_OK) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("error_upload"), true);
            if (!$questions_only) {
                $this->ctrl->redirect($this, 'create');
            }
            return false;
        }
        $basedir = ilObjQuestionPool::_createImportDirectory();

        $xml_file = '';
        $qti_file = '';
        $subdir = '';

        global $DIC; /* @var ILIAS\DI\Container $DIC */
        // copy uploaded file to import directory
        $file = pathinfo($_FILES["xmldoc"]["name"]);
        $full_path = $basedir . "/" . $_FILES["xmldoc"]["name"];
        $DIC['ilLog']->write(__METHOD__ . ": full path " . $full_path);
        ilFileUtils::moveUploadedFile($_FILES["xmldoc"]["tmp_name"], $_FILES["xmldoc"]["name"], $full_path);
        $DIC['ilLog']->write(__METHOD__ . ": full path " . $full_path);
        if (strcmp($_FILES["xmldoc"]["type"], "text/xml") == 0) {
            $qti_file = $full_path;
            ilObjTest::_setImportDirectory($basedir);
        } else {
            // unzip file
            ilFileUtils::unzip($full_path);

            // determine filenames of xml files
            $subdir = basename($file["basename"], "." . $file["extension"]);
            ilObjQuestionPool::_setImportDirectory($basedir);
            $xml_file = ilObjQuestionPool::_getImportDirectory() . '/' . $subdir . '/' . $subdir . ".xml";
            $qti_file = ilObjQuestionPool::_getImportDirectory() . '/' . $subdir . '/' . str_replace("qpl", "qti", $subdir) . ".xml";
        }
        $qtiParser = new ilQTIParser($qti_file, ilQTIParser::IL_MO_VERIFY_QTI, 0, "");
        $qtiParser->startParsing();
        $founditems = &$qtiParser->getFoundItems();
        if (count($founditems) == 0) {
            // nothing found

            // delete import directory
            ilFileUtils::delDir($basedir);

            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("qpl_import_no_items"), true);
            if (!$questions_only) {
                $this->ctrl->redirect($this, 'create');
            }
            return false;
        }

        $complete = 0;
        $incomplete = 0;
        foreach ($founditems as $item) {
            if (strlen($item["type"])) {
                $complete++;
            } else {
                $incomplete++;
            }
        }

        if ($complete == 0) {
            // delete import directory
            ilFileUtils::delDir($basedir);

            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("qpl_import_non_ilias_files"), true);
            if (!$questions_only) {
                $this->ctrl->redirect($this, 'create');
            }
            return false;
        }

        ilSession::set("qpl_import_xml_file", $xml_file);
        ilSession::set("qpl_import_qti_file", $qti_file);
        ilSession::set("qpl_import_subdir", $subdir);

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.qpl_import_verification.html", "Modules/TestQuestionPool");
        $table = new ilQuestionPoolImportVerificationTableGUI($this, 'uploadQplObject');
        $rows = array();

        foreach ($founditems as $item) {
            $row = array(
                'title' => $item['title'],
                'ident' => $item['ident'],
            );
            switch ($item["type"]) {
                case CLOZE_TEST_IDENTIFIER:
                    $type = $this->lng->txt("assClozeTest");
                    break;
                case IMAGEMAP_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt("assImagemapQuestion");
                    break;
                case MATCHING_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt("assMatchingQuestion");
                    break;
                case MULTIPLE_CHOICE_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt("assMultipleChoice");
                    break;
                case KPRIM_CHOICE_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt("assKprimChoice");
                    break;
                case LONG_MENU_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt("assLongMenu");
                    break;
                case SINGLE_CHOICE_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt("assSingleChoice");
                    break;
                case ORDERING_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt("assOrderingQuestion");
                    break;
                case TEXT_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt("assTextQuestion");
                    break;
                case NUMERIC_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt("assNumeric");
                    break;
                case TEXTSUBSET_QUESTION_IDENTIFIER:
                    $type = $this->lng->txt("assTextSubset");
                    break;
                default:
                    $type = $this->lng->txt($item["type"]);
                    break;
            }

            if (strcmp($type, "-" . $item["type"] . "-") == 0) {
                global $DIC;
                $component_factory = $DIC['component.factory'];
                $component_repository = $DIC["component.repository"];
                $plugins = $component_repository->getPluginSlotById("qst")->getActivePlugins();
                foreach ($component_factory->getActivePluginsInSlot("qst") as $pl) {
                    if (strcmp($pl->getQuestionType(), $item["type"]) == 0) {
                        $type = $pl->getQuestionTypeTranslation();
                    }
                }
            }

            $row['type'] = $type;

            $rows[] = $row;
        }
        $table->setData($rows);

        $this->tpl->setCurrentBlock("import_qpl");
        if (is_file($xml_file)) {
            // read file into a string
            $fh = @fopen($xml_file, "r") or die("");
            $xml = @fread($fh, filesize($xml_file));
            @fclose($fh);
            if (preg_match("/<ContentObject.*?MetaData.*?General.*?Title[^>]*?>([^<]*?)</", $xml, $matches)) {
                $this->tpl->setVariable("VALUE_NEW_QUESTIONPOOL", $matches[1]);
            }
        }
        $this->tpl->setVariable("TEXT_CREATE_NEW_QUESTIONPOOL", $this->lng->txt("qpl_import_create_new_qpl"));
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("adm_content");
        $this->tpl->setVariable("FOUND_QUESTIONS_INTRODUCTION", $this->lng->txt("qpl_import_verify_found_questions"));
        if ($questions_only) {
            $this->tpl->setVariable("VERIFICATION_HEADING", $this->lng->txt("import_questions_into_qpl"));
            $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        } else {
            $this->tpl->setVariable("VERIFICATION_HEADING", $this->lng->txt("import_qpl"));

            $this->ctrl->setParameter($this, "new_type", $this->type);
            $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        }

        $value_questions_only = 0;
        if ($questions_only) {
            $value_questions_only = 1;
        }
        $this->tpl->setVariable("VALUE_QUESTIONS_ONLY", $value_questions_only);
        $this->tpl->setVariable("VERIFICATION_TABLE", $table->getHtml());
        $this->tpl->setVariable("VERIFICATION_FORM_NAME", $table->getFormName());

        $this->tpl->parseCurrentBlock();

        return true;
    }

    /**
    * imports question(s) into the questionpool (after verification)
    */
    public function importVerifiedFileObject(): void
    {
        if ($_POST["questions_only"] == 1) {
            $newObj = &$this->object;
        } else {
            // create new questionpool object
            $newObj = new ilObjQuestionPool(0, true);
            // set type of questionpool object
            $newObj->setType($this->qplrequest->raw("new_type"));
            // set title of questionpool object to "dummy"
            $newObj->setTitle("dummy");
            // set description of questionpool object
            $newObj->setDescription("questionpool import");
            // create the questionpool class in the ILIAS database (object_data table)
            $newObj->create(true);
            // create a reference for the questionpool object in the ILIAS database (object_reference table)
            $newObj->createReference();
            // put the questionpool object in the administration tree
            $newObj->putInTree($this->qplrequest->getRefId());
            // get default permissions and set the permissions for the questionpool object
            $newObj->setPermissions($this->qplrequest->getRefId());
        }

        if (is_file(ilSession::get("qpl_import_dir") . '/' . ilSession::get("qpl_import_subdir") . "/manifest.xml")) {
            ilSession::set("qpl_import_idents", $this->qplrequest->raw("ident"));

            $fileName = ilSession::get("qpl_import_subdir") . '.zip';
            $fullPath = ilSession::get("qpl_import_dir") . '/' . $fileName;
            $imp = new ilImport($this->qplrequest->getRefId());
            $map = $imp->getMapping();
            $map->addMapping("Modules/TestQuestionPool", "qpl", "new_id", $newObj->getId());
            $imp->importObject($newObj, $fullPath, $fileName, "qpl", "Modules/TestQuestionPool", true);
        } else {
            $qtiParser = new ilQTIParser(ilSession::get("qpl_import_qti_file"), ilQTIParser::IL_MO_PARSE_QTI, $newObj->getId(), $this->qplrequest->raw("ident"));
            $qtiParser->startParsing();
            // import page data
            if (strlen(ilSession::get("qpl_import_xml_file"))) {
                $contParser = new ilContObjParser($newObj, ilSession::get("qpl_import_xml_file"), ilSession::get("qpl_import_subdir"));
                $contParser->setQuestionMapping($qtiParser->getImportMapping());
                $contParser->startParsing();
                // #20494
                $newObj->fromXML(ilSession::get("qpl_import_xml_file"));
            }

            // set another question pool name (if possible)
            if (isset($_POST["qpl_new"]) && strlen($_POST["qpl_new"])) {
                $newObj->setTitle($_POST["qpl_new"]);
            }

            $newObj->update();
            $newObj->saveToDb();
        }
        ilFileUtils::delDir(dirname(ilObjQuestionPool::_getImportDirectory()));

        if ($_POST["questions_only"] == 1) {
            $this->ctrl->redirect($this, "questions");
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_imported"), true);
            ilUtil::redirect("ilias.php?ref_id=" . $newObj->getRefId() .
                "&baseClass=ilObjQuestionPoolGUI");
        }
    }

    public function cancelImportObject(): void
    {
        if ($_POST["questions_only"] == 1) {
            $this->ctrl->redirect($this, "questions");
        } else {
            $this->ctrl->redirect($this, "cancel");
        }
    }

    /**
    * imports question(s) into the questionpool
    */
    public function uploadObject(): void
    {
        $upload_valid = true;
        $form = $this->getImportQuestionsForm();
        if ($form->checkInput()) {
            if (!$this->uploadQplObject(true)) {
                $form->setValuesByPost();
                $this->importQuestionsObject($form);
            }
        } else {
            $form->setValuesByPost();
            $this->importQuestionsObject($form);
        }
    }

    /**
    * display the import form to import questions into the questionpool
    */
    public function importQuestionsObject(ilPropertyFormGUI $form = null): void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->getImportQuestionsForm();
        }

        $this->tpl->setContent($form->getHtml());
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getImportQuestionsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('import_question'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'upload'));

        $file = new ilFileInputGUI($this->lng->txt('select_file'), 'xmldoc');
        $file->setRequired(true);
        $form->addItem($file);

        $form->addCommandButton('upload', $this->lng->txt('upload'));
        $form->addCommandButton('questions', $this->lng->txt('cancel'));

        return $form;
    }

    /**
    * create new question
    */
    public function createQuestionObject(): void
    {
        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $addContEditMode = $_POST['add_quest_cont_edit_mode'];
        } else {
            $addContEditMode = assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE;
        }
        $q_gui = assQuestionGUI::_getQuestionGUI($_POST["sel_question_types"]);
        $q_gui->object->setObjId($this->object->getId());
        $q_gui->object->setAdditionalContentEditingMode($addContEditMode);
        $q_gui->object->createNewQuestion();
        $this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $q_gui->object->getId());
        $this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $_POST["sel_question_types"]);
        $this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
    }

    /**
    * create new question
    */
    public function &createQuestionForTestObject(): void
    {
        if (!$this->qplrequest->raw('q_id')) {
            if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
                $addContEditMode = $this->qplrequest->raw('add_quest_cont_edit_mode');
            } else {
                $addContEditMode = assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE;
            }
            $q_gui = assQuestionGUI::_getQuestionGUI($this->qplrequest->raw("sel_question_types"));
            $q_gui->object->setObjId($this->object->getId());
            $q_gui->object->setAdditionalContentEditingMode($addContEditMode);
            $q_gui->object->createNewQuestion();

            $class = get_class($q_gui);
            $qId = $q_gui->object->getId();
        } else {
            $class = $this->qplrequest->raw("sel_question_types") . 'gui';
            $qId = $this->qplrequest->raw('q_id');
        }

        $this->ctrl->setParameterByClass($class, "q_id", $qId);
        $this->ctrl->setParameterByClass($class, "sel_question_types", $this->qplrequest->raw("sel_question_types"));
        $this->ctrl->setParameterByClass($class, "prev_qid", $this->qplrequest->raw("prev_qid"));

        $this->ctrl->redirectByClass($class, "editQuestion");
    }

    /**
    * save object
    * @access	public
    */
    public function afterSave(ilObject $new_object): void
    {
        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);

        ilUtil::redirect("ilias.php?ref_id=" . $new_object->getRefId() .
            "&baseClass=ilObjQuestionPoolGUI");
    }

    public function questionObject(): void
    {
        // @PHP8-CR: With this probably never working and no detectable usages, it would be a candidate for removal...
        // but it is one of the magic command-methods ($cmd.'Object' - pattern) so I live to leave this in here for now
        // until it can be further investigated.
        //echo "<br>ilObjQuestionPoolGUI->questionObject()";
        $type = $this->qplrequest->raw("sel_question_types");
        $this->editQuestionForm($type);
    }

    /**
    * delete questions confirmation screen
    */
    public function deleteQuestionsObject(): void
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        $questionIdsToDelete = $this->qplrequest->isset('q_id') ? (array) $this->qplrequest->raw('q_id') : array();
        if (0 === count($questionIdsToDelete) && $this->qplrequest->isset('q_id')) {
            $questionIdsToDelete = array($this->qplrequest->getQuestionId());
        }

        $questionIdsToDelete = array_filter(array_map('intval', $questionIdsToDelete));
        if (0 === count($questionIdsToDelete)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_delete_select_none"), true);
            $this->ctrl->redirect($this, "questions");
        }

        $this->tpl->setOnScreenMessage('question', $this->lng->txt("qpl_confirm_delete_questions"));
        $deleteable_questions = &$this->object->getDeleteableQuestionDetails($questionIdsToDelete);
        $table_gui = new ilQuestionBrowserTableGUI($this, 'questions', (($rbacsystem->checkAccess('write', $this->qplrequest->getRefId()) ? true : false)), true);
        $table_gui->setShowRowsSelector(false);
        $table_gui->setLimit(PHP_INT_MAX);
        $table_gui->setEditable($rbacsystem->checkAccess('write', $this->qplrequest->getRefId()));
        $table_gui->setData($deleteable_questions);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }


    /**
    * delete questions
    */
    public function confirmDeleteQuestionsObject(): void
    {
        // delete questions after confirmation
        foreach ($_POST["q_id"] as $key => $value) {
            $this->object->deleteQuestion($value);
            $this->object->cleanupClipboard($value);
        }
        if (count($_POST["q_id"])) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("qpl_questions_deleted"), true);
        }

        $this->ctrl->setParameter($this, 'q_id', '');

        $this->ctrl->redirect($this, "questions");
    }

    /**
    * Cancel question deletion
    */
    public function cancelDeleteQuestionsObject(): void
    {
        $this->ctrl->redirect($this, "questions");
    }

    /**
    * export question
    */
    public function exportQuestionObject(): void
    {
        // export button was pressed
        $post = $this->qplrequest->getParsedBody();
        if (array_key_exists('q_id', $post) && is_array($post['q_id']) && count($post['q_id']) > 0) {
            $qpl_exp = new ilQuestionpoolExport($this->object, "xml", $post["q_id"]);
            // @PHP8-CR: This seems to be a pointer to an issue with exports. I like to leave this open for now and
            // schedule a thorough examination / analysis for later, eventually involved T&A TechSquad
            $export_file = $qpl_exp->buildExportFile();
            $filename = $export_file;
            $filename = preg_replace("/.*\//", "", $filename);
            if ($export_file === '') {
                $export_file = "StandIn";
            }
            ilFileDelivery::deliverFileLegacy($export_file, $filename);
            exit();
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_export_select_none"), true);
        }
        $this->ctrl->redirect($this, "questions");
    }

    public function filterQuestionBrowserObject(): void
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $enableComments = $DIC->rbac()->system()->checkAccess('write', $this->qplrequest->getRefId());
        $taxIds = ilObjTaxonomy::getUsageOfObject($this->object->getId());
        $table_gui = new ilQuestionBrowserTableGUI($this, 'questions', false, false, $taxIds, $enableComments);
        $table_gui->resetOffset();
        $table_gui->writeFilterToSession();
        $this->questionsObject();
    }

    public function resetQuestionBrowserObject(): void
    {
        $taxIds = ilObjTaxonomy::getUsageOfObject($this->object->getId());

        $table_gui = new ilQuestionBrowserTableGUI(
            $this,
            'questions',
            false,
            false,
            $taxIds
        );

        $table_gui->resetOffset();
        $table_gui->resetFilter();
        $this->questionsObject();
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
    public function questionsObject(): void
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $component_repository = $DIC['component.repository'];

        if (get_class($this->object) == "ilObjTest") {
            if ($this->qplrequest->raw("calling_test") > 0) {
                $ref_id = $this->qplrequest->raw("calling_test");
                $q_id = $this->qplrequest->raw("q_id");

                if ($this->qplrequest->raw('test_express_mode')) {
                    if ($q_id) {
                        ilUtil::redirect("ilias.php?ref_id=" . $ref_id . "&q_id=" . $q_id . "&test_express_mode=1&cmd=showPage&cmdClass=iltestexpresspageobjectgui&baseClass=ilObjTestGUI");
                    } else {
                        ilUtil::redirect("ilias.php?ref_id=" . $ref_id . "&test_express_mode=1&cmd=showPage&cmdClass=iltestexpresspageobjectgui&baseClass=ilObjTestGUI");
                    }
                } else {
                    ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&ref_id=" . $ref_id . "&cmd=questions");
                }
            }
        } elseif ($this->qplrequest->isset('calling_consumer') && (int) $this->qplrequest->raw('calling_consumer')) {
            $ref_id = (int) $this->qplrequest->raw('calling_consumer');
            $consumer = ilObjectFactory::getInstanceByRefId($ref_id);
            if ($consumer instanceof ilQuestionEditingFormConsumer) {
                ilUtil::redirect($consumer->getQuestionEditingFormBackTarget($this->qplrequest->raw('consumer_context')));
            }
            ilUtil::redirect(ilLink::_getLink($ref_id));
        }

        $this->object->purgeQuestions();
        // reset test_id SESSION variable
        ilSession::set("test_id", "");
        $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->object->getId());
        if ($qsaImportFails->failedImportsRegistered()) {
            $button = ilLinkButton::getInstance();
            $button->setUrl($this->ctrl->getLinkTarget($this, 'renoveImportFails'));
            $button->setCaption('ass_skl_import_fails_remove_btn');

            $this->tpl->setOnScreenMessage('failure', $qsaImportFails->getFailedImportsMessage($this->lng) . '<br />' . $button->render());
        }
        $taxIds = ilObjTaxonomy::getUsageOfObject($this->object->getId());

        $table_gui = $this->buildQuestionBrowserTableGUI($taxIds);
        $table_gui->setPreventDoubleSubmission(false);

        if ($rbacsystem->checkAccess('write', $this->qplrequest->getRefId())) {
            $toolbar = new ilToolbarGUI();
            $btn = ilLinkButton::getInstance();
            $btn->setCaption('ass_create_question');
            $btn->setUrl($this->ctrl->getLinkTarget($this, 'createQuestionForm'));
            $btn->setPrimary(true);
            $toolbar->addButtonInstance($btn);


            $btnImport = ilLinkButton::getInstance();
            $btnImport->setCaption('import');
            $btnImport->setUrl($this->ctrl->getLinkTarget($this, 'importQuestions'));
            $toolbar->addButtonInstance($btnImport);

            if (ilSession::get("qpl_clipboard") != null && count(ilSession::get('qpl_clipboard'))) {
                $btnPaste = ilLinkButton::getInstance();
                $btnPaste->setCaption('paste');
                $btnPaste->setUrl($this->ctrl->getLinkTarget($this, 'paste'));
                $toolbar->addButtonInstance($btnPaste);
            }

            $this->tpl->setContent(
                $this->ctrl->getHTML($toolbar) . $this->ctrl->getHTML($table_gui)
            );
        } else {
            $this->tpl->setContent($this->ctrl->getHTML($table_gui));
        }

        if ($this->object->getShowTaxonomies()) {
            $this->lng->loadLanguageModule('tax');

            foreach ($taxIds as $taxId) {
                if ($taxId != $this->object->getNavTaxonomyId()) {
                    continue;
                }

                $taxExp = new ilTaxonomyExplorerGUI(
                    $this,
                    'showNavTaxonomy',
                    $taxId,
                    'ilobjquestionpoolgui',
                    'questions'
                );

                if (!$taxExp->handleCommand()) {
                    $this->tpl->setLeftContent($taxExp->getHTML() . "&nbsp;");
                }

                break;
            }
        }
    }

    /**
     * @return mixed
     */
    protected function fetchAuthoringQuestionIdParamater()
    {
        $qId = $this->qplrequest->getQuestionId();

        if ($this->object->checkQuestionParent($qId)) {
            return $qId;
        }

        throw new ilTestQuestionPoolException('question id does not relate to parent object!');
    }

    private function createQuestionFormObject(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilHelp = $DIC['ilHelp']; /* @var ilHelpGUI $ilHelp */

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
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('ass_create_question'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        // question type

        $options = array();
        foreach ($this->object->getQuestionTypes(false, true, false) as $translation => $data) {
            $options[$data['type_tag']] = $translation;
        }
        $si = new ilSelectInputGUI($this->lng->txt('question_type'), 'sel_question_types');
        $si->setOptions($options);
        //$si->setValue($ilUser->getPref("tst_lastquestiontype"));

        $form->addItem($si);

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

            $ri->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE);

            $form->addItem($ri, true);
        } else {
            $hi = new ilHiddenInputGUI("question_content_editing_type");
            $hi->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE);
            $form->addItem($hi, true);
        }

        // commands

        $form->addCommandButton('createQuestion', $this->lng->txt('create'));
        $form->addCommandButton('questions', $this->lng->txt('cancel'));

        return $form;
    }

    /**
     * Creates a print view for a question pool
     */
    public function printObject(): void
    {
        /**
         * @var $ilToolbar ilToolbarGUI
         */
        global $DIC;
        $ilToolbar = $DIC['ilToolbar'];

        $ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'print'));
        $mode = new ilSelectInputGUI($this->lng->txt('output_mode'), 'output');
        $mode->setOptions(array(
            'overview' => $this->lng->txt('overview'),
            'detailed' => $this->lng->txt('detailed_output_solutions'),
            'detailed_printview' => $this->lng->txt('detailed_output_printview')
        ));
        $mode->setValue(ilUtil::stripSlashes((string) $_POST['output']));

        $ilToolbar->setFormName('printviewOptions');
        $ilToolbar->addInputItem($mode, true);
        $ilToolbar->addFormButton($this->lng->txt('submit'), 'print');
        $table_gui = new ilQuestionPoolPrintViewTableGUI($this, 'print', $_POST['output']);
        $data = $this->object->getPrintviewQuestions();
        $totalPoints = 0;
        foreach ($data as $d) {
            $totalPoints += $d['points'];
        }
        $table_gui->setTotalPoints($totalPoints);
        $table_gui->initColumns();
        $table_gui->setData($data);
        $this->tpl->setContent($table_gui->getHTML());
    }

    public function updateObject(): void
    {
        $this->object->update();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
    }

    /**
    * paste questios from the clipboard into the question pool
    */
    public function pasteObject(): void
    {
        if (ilSession::get("qpl_clipboard") != null) {
            if ($this->object->pasteFromClipboard()) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("qpl_paste_success"), true);
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("qpl_paste_error"), true);
            }
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_paste_no_objects"), true);
        }
        $this->ctrl->redirect($this, "questions");
    }

    /**
    * copy one or more question objects to the clipboard
    */
    public function copyObject(): void
    {
        if (isset($_POST["q_id"]) && is_array($_POST["q_id"]) && count($_POST["q_id"]) > 0) {
            foreach ($_POST["q_id"] as $key => $value) {
                $this->object->copyToClipboard($value);
            }
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_copy_insert_clipboard"), true);
        } elseif ($this->qplrequest->isset('q_id') && $this->qplrequest->getQuestionId() > 0) {
            $this->object->copyToClipboard($this->qplrequest->getQuestionId());
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_copy_insert_clipboard"), true);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_copy_select_none"), true);
        }
        $this->ctrl->redirect($this, "questions");
    }

    /**
    * mark one or more question objects for moving
    */
    public function moveObject(): void
    {
        if (isset($_POST["q_id"]) && is_array($_POST["q_id"]) && count($_POST["q_id"]) > 0) {
            foreach ($_POST["q_id"] as $key => $value) {
                $this->object->moveToClipboard($value);
            }
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_move_insert_clipboard"), true);
        } elseif ($this->qplrequest->isset('q_id') && $this->qplrequest->getQuestionId() > 0) {
            $this->object->moveToClipboard($this->qplrequest->getQuestionId());
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_copy_insert_clipboard"), true);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_move_select_none"), true);
        }
        $this->ctrl->redirect($this, "questions");
    }

    public function createExportExcel(): void
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        if ($rbacsystem->checkAccess("write", $this->qplrequest->getRefId())) {
            $question_ids = &$this->object->getAllQuestionIds();
            $qpl_exp = new ilQuestionpoolExport($this->object, 'xls', $question_ids);
            $qpl_exp->buildExportFile();
            $this->ctrl->redirectByClass("ilquestionpoolexportgui", "");
        }
    }

    /**
    * edit question
    */
    public function &editQuestionForTestObject(): void
    {
        global $DIC;

        $p_gui = new ilAssQuestionPreviewGUI(
            $this->ctrl,
            $this->tabs_gui,
            $this->tpl,
            $this->lng,
            $DIC->database(),
            $DIC->user(),
            new ILIAS\Refinery\Random\Group()
        );
        $this->ctrl->redirectByClass(get_class($p_gui), "show");
    }

    protected function initImportForm(string $new_type): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("import_qpl"));

        $fi = new ilFileInputGUI($this->lng->txt("import_file"), "xmldoc");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $form->addItem($fi);

        $form->addCommandButton("importFile", $this->lng->txt("import"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }

    /**
    * form for new questionpool object import
    */
    protected function importFileObject(int $parent_id = null, bool $catch_errors = true): void
    {
        $form = $this->initImportForm($this->qplrequest->raw("new_type"));
        if ($form->checkInput()) {
            $this->uploadQplObject();
        }

        // display form to correct errors
        $this->tpl->setContent($form->getHTML());
    }

    public function addLocatorItems(): void
    {
        global $DIC;
        $ilLocator = $DIC['ilLocator'];
        switch ($this->ctrl->getCmd()) {
            case "create":
            case "importFile":
            case "cancel":
                break;
            default:
                $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->qplrequest->getRefId());
                break;
        }
        if (!is_array($this->qplrequest->raw("q_id")) && $this->qplrequest->raw("q_id") > 0 && $this->qplrequest->raw('cmd') !== 'questions') {
            $q_gui = assQuestionGUI::_getQuestionGUI("", $this->qplrequest->raw("q_id"));
            if ($q_gui !== null && $q_gui->object instanceof assQuestion) {
                $q_gui->object->setObjId($this->object->getId());
                $title = $q_gui->object->getTitle();
                if (!$title) {
                    $title = $this->lng->txt('new') . ': ' . assQuestion::_getQuestionTypeName($q_gui->object->getQuestionType());
                }
                $ilLocator->addItem($title, $this->ctrl->getLinkTargetByClass(get_class($q_gui), "editQuestion"));
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

        if (!is_array($this->qplrequest->raw("q_id")) && $this->qplrequest->raw("q_id") > 0 && $this->qplrequest->raw('cmd') !== 'questions') {
            $q_gui = assQuestionGUI::_getQuestionGUI("", $this->qplrequest->getQuestionId());
            if ($q_gui->object instanceof assQuestion) {
                $q_gui->object->setObjId($this->object->getId());
                $title = $q_gui->object->getTitle();
                if (!$title) {
                    $title = $this->lng->txt('new') . ': ' . assQuestion::_getQuestionTypeName($q_gui->object->getQuestionType());
                }
                $this->tpl->setTitle($title);
                $this->tpl->setDescription($q_gui->object->getComment());
                $this->tpl->setTitleIcon(ilObject2::_getIcon($this->object->getId(), "big", $this->object->getType()));
            } else {
                // Workaround for context issues: If no object was found, redirect without q_id parameter
                $this->ctrl->setParameter($this, 'q_id', '');
                $this->ctrl->redirect($this);
            }
        } else {
            $this->tpl->setTitle($this->object->getTitle());
            $this->tpl->setDescription($this->object->getLongDescription());
            $this->tpl->setTitleIcon(ilObject2::_getIcon($this->object->getId(), "big", $this->object->getType()));
        }
    }

    /**
    * adds tabs to tab gui object
    *
    * @param	object		$tabs_gui		ilTabsGUI object
    */
    public function getTabs(): void
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilHelp = $DIC['ilHelp'];

        $currentUserHasWriteAccess = $ilAccess->checkAccess("write", "", $this->object->getRefId());

        $ilHelp->setScreenIdComponent("qpl");

        $next_class = strtolower($this->ctrl->getNextClass());
        switch ($next_class) {
            case "":
            case "ilpermissiongui":
            case "ilobjectmetadatagui":
            case "ilquestionpoolexportgui":
            case "ilquestionpoolskilladministrationgui":
                break;

            case 'ilobjtaxonomygui':
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
        //$commands = $_POST["cmd"];
        $commands = $this->getQueryParamString("cmd");
        if (is_array($commands)) {
            foreach ($commands as $key => $value) {
                if (preg_match("/^delete_.*/", $key, $matches) ||
                    preg_match("/^addSelectGap_.*/", $key, $matches) ||
                    preg_match("/^addTextGap_.*/", $key, $matches) ||
                    preg_match("/^deleteImage_.*/", $key, $matches) ||
                    preg_match("/^upload_.*/", $key, $matches) ||
                    preg_match("/^addSuggestedSolution_.*/", $key, $matches)
                ) {
                    $force_active = true;
                }
            }
        }
        if (isset($_POST['imagemap_x'])) {
            $force_active = true;
        }
        if (!$force_active) {
            $force_active = ((strtolower($this->ctrl->getCmdClass()) == strtolower(get_class($this)) || strlen($this->ctrl->getCmdClass()) == 0) &&
                $this->ctrl->getCmd() == "")
                ? true
                : false;
        }
        if ($ilAccess->checkAccess("write", "", $this->qplrequest->getRefId())) {
            $this->tabs_gui->addTarget(
                "assQuestions",
                $this->ctrl->getLinkTarget($this, "questions"),
                array("questions", "filter", "resetFilter", "createQuestion",
                    "importQuestions", "deleteQuestions", "filterQuestionBrowser",
                    "view", "preview", "editQuestion", "exec_pg",
                    "addItem", "upload", "save", "cancel", "addSuggestedSolution",
                    "cancelExplorer", "linkChilds", "removeSuggestedSolution",
                    "add", "addYesNo", "addTrueFalse", "createGaps", "saveEdit",
                    "setMediaMode", "uploadingImage", "uploadingImagemap", "addArea",
                    "deletearea", "saveShape", "back", "addPair", "uploadingJavaapplet",
                    "addParameter", "assessment", "addGIT", "addST", "addPG", "delete",
                    "toggleGraphicalAnswers", "deleteAnswer", "deleteImage", "removeJavaapplet"),
                "",
                "",
                $force_active
            );
        }
        if ($ilAccess->checkAccess("read", "", $this->ref_id) || $ilAccess->checkAccess("visible", "", $this->ref_id)) {
            $this->tabs_gui->addTarget(
                "info_short",
                $this->ctrl->getLinkTarget($this, "infoScreen"),
                array("infoScreen", "showSummary")
            );
        }

        if ($ilAccess->checkAccess("write", "", $this->qplrequest->getRefId())) {
            // properties
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTargetByClass('ilObjQuestionPoolSettingsGeneralGUI'),
                array(),
                array('ilObjQuestionPoolSettingsGeneralGUI', 'ilObjTaxonomyGUI')
            );

            // skill service
            if ($this->isSkillsTabRequired()) {
                $link = $this->ctrl->getLinkTargetByClass(
                    array('ilQuestionPoolSkillAdministrationGUI', 'ilAssQuestionSkillAssignmentsGUI'),
                    ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
                );

                $this->tabs_gui->addTarget('qpl_tab_competences', $link, array(), array());
            }
        }

        if ($ilAccess->checkAccess("write", "", $this->qplrequest->getRefId())) {
            // print view
            $this->tabs_gui->addTarget(
                "print_view",
                $this->ctrl->getLinkTarget($this, 'print'),
                array("print"),
                "",
                ""
            );
        }

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTarget(
                    "meta_data",
                    $mdtab,
                    "",
                    "ilmdeditorgui"
                );
            }

            //			$this->tabs_gui->addTarget("export",
            //				 $this->ctrl->getLinkTarget($this,'export'),
            //				 array("export", "createExportFile", "confirmDeleteExportFile", "downloadExportFile"),
            //				 "", "");
        }

        if ($currentUserHasWriteAccess) {
            $this->tabs_gui->addTarget(
                "export",
                $this->ctrl->getLinkTargetByClass("ilquestionpoolexportgui", ""),
                "",
                "ilquestionpoolexportgui"
            );
        }

        if ($ilAccess->checkAccess("edit_permission", "", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
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
        $tabs->addSubTabTarget(
            'qpl_settings_subtab_general',
            $this->ctrl->getLinkTargetByClass('ilObjQuestionPoolSettingsGeneralGUI'),
            '',
            'ilObjQuestionPoolSettingsGeneralGUI'
        );

        $tabs->addSubTabTarget(
            'qpl_settings_subtab_taxonomies',
            $this->ctrl->getLinkTargetByClass('ilObjTaxonomyGUI', 'editAOTaxonomySettings'),
            '',
            'ilObjTaxonomyGUI'
        );
    }

    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreenObject(): void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }

    /**
    * show information screen
    */
    public function infoScreenForward(): void
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];
        $ilAccess = $DIC['ilAccess'];

        if (!$ilAccess->checkAccess("visible", "", $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"));
        }
        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();

        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        $this->ctrl->forwardCommand($info);
    }

    /**
    * Redirect script to call a test with the question pool reference id
    *
    * Redirect script to call a test with the question pool reference id
    *
    * @param integer $a_target The reference id of the question pool
    * @access	public
    */
    public static function _goto($a_target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ctrl = $DIC['ilCtrl'];

        if ($ilAccess->checkAccess("write", "", (int) $a_target)
            || $ilAccess->checkAccess('read', '', (int) $a_target)
        ) {
            $target_class = ilObjQuestionPoolGUI::class;
            $target_cmd = 'questions';
            $ctrl->setParameterByClass($target_class, 'ref_id', $a_target);
            $ctrl->redirectByClass([ilRepositoryGUI::class, $target_class], $target_cmd);
            exit;
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('info', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }
        $ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
    }

    /**
     * @param array $taxIds
     * @global ilRbacSystem  $rbacsystem
     * @global ilDBInterface $ilDB
     * @global ilLanguage $lng
     * @return ilQuestionBrowserTableGUI
     */
    private function buildQuestionBrowserTableGUI($taxIds): ilQuestionBrowserTableGUI
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        /* @var ilComponentRepository $component_repository */
        $component_repository = $DIC['component.repository'];

        $writeAccess = (bool) $rbacsystem->checkAccess('write', $this->qplrequest->getRefId());
        $enableCommenting = $writeAccess;

        $table_gui = new ilQuestionBrowserTableGUI(
            $this,
            'questions',
            $writeAccess,
            false,
            $taxIds,
            $enableCommenting
        );

        $table_gui->setEditable($writeAccess);
        $questionList = new ilAssQuestionList($ilDB, $lng, $component_repository);
        $questionList->setParentObjId($this->object->getId());

        foreach ($table_gui->getFilterItems() as $item) {
            if (substr($item->getPostVar(), 0, strlen('tax_')) == 'tax_') {
                $v = $item->getValue();

                if (is_array($v) && count($v) && !(int) $v[0]) {
                    continue;
                }

                $taxId = substr($item->getPostVar(), strlen('tax_'));

                $questionList->addTaxonomyFilter(
                    $taxId,
                    $item->getValue(),
                    $this->object->getId(),
                    $this->object->getType()
                );
            } elseif ($item->getValue() != false) {
                $questionList->addFieldFilter($item->getPostVar(), $item->getValue());
            }
        }

        if ($this->object->isNavTaxonomyActive() && (int) $this->qplrequest->raw('tax_node')) {
            $taxTree = new ilTaxonomyTree($this->object->getNavTaxonomyId());
            $rootNodeId = $taxTree->readRootId();

            if ((int) $this->qplrequest->raw('tax_node') != $rootNodeId) {
                $questionList->addTaxonomyFilter(
                    $this->object->getNavTaxonomyId(),
                    array((int) $this->qplrequest->raw('tax_node')),
                    $this->object->getId(),
                    $this->object->getType()
                );
            }
        }

        $questionList->load();
        $data = $questionList->getQuestionDataArray();

        $table_gui->setQuestionData($data);

        return $table_gui;
    }
} // END class.ilObjQuestionPoolGUI
