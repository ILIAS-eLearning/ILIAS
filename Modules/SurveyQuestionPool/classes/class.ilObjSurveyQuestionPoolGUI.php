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

use ILIAS\SurveyQuestionPool\Editing\EditingGUIRequest;

/**
 * Class ilObjSurveyQuestionPoolGUI
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveyMultipleChoiceQuestionGUI, SurveyMetricQuestionGUI
 * @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveySingleChoiceQuestionGUI, SurveyTextQuestionGUI
 * @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveyMatrixQuestionGUI
 * @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: ilSurveyPhrasesGUI, ilInfoScreenGUI
 * @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: ilObjectMetaDataGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: ilCommonActionDispatcherGUI
 */
class ilObjSurveyQuestionPoolGUI extends ilObjectGUI implements ilCtrlBaseClassInterface
{
    protected \ILIAS\SurveyQuestionPool\Editing\EditManager $edit_manager;
    protected bool $update;
    protected EditingGUIRequest $edit_request;
    protected ilNavigationHistory $nav_history;
    protected ilHelpGUI $help;
    protected ilLogger $log;
    public string $defaultscript;

    public function __construct()
    {
        global $DIC;

        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->toolbar = $DIC->toolbar();
        $this->help = $DIC["ilHelp"];

        $this->edit_request = $DIC->surveyQuestionPool()
            ->internal()
            ->gui()
            ->editing()
            ->request();
        $this->edit_manager = $DIC->surveyQuestionPool()
                                  ->internal()
                                  ->domain()
                                  ->editing();

        $this->type = "spl";

        parent::__construct(
            "",
            $this->edit_request->getRefId(),
            true,
            false
        );
        $this->lng->loadLanguageModule("survey");
        $this->ctrl->saveParameter($this, array("ref_id"));
        $this->log = ilLoggerFactory::getLogger('svy');
    }

    public function executeCommand(): void
    {
        $ilNavigationHistory = $this->nav_history;

        if (!$this->checkPermissionBool("visible") &&
            !$this->checkPermissionBool("read")) {
            $this->checkPermission("read");
        }

        // add entry to navigation history
        if (!$this->getCreationMode() &&
            $this->checkPermissionBool("read")) {
            $ilNavigationHistory->addItem(
                $this->ref_id,
                "ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&cmd=questions&ref_id=" . $this->ref_id,
                "spl"
            );
        }

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "survey.css", "Modules/Survey"), "screen");
        $this->prepareOutput();

        $cmd = $this->ctrl->getCmd("questions");
        $next_class = $this->ctrl->getNextClass($this);
        $this->ctrl->setReturn($this, "questions");
        $q_type = "";
        if ($this->edit_request->getQuestionId() < 1) {
            $q_type = $this->edit_request->getSelectedQuestionTypes();
        }

        $this->log->debug("- cmd=" . $cmd . " next_class=" . $next_class);
        switch ($next_class) {
            case 'ilobjectmetadatagui':
                $this->checkPermission('write');
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilsurveyphrasesgui":
                $phrases_gui = new ilSurveyPhrasesGUI($this);
                $this->ctrl->forwardCommand($phrases_gui);
                break;

            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('spl');
                $this->ctrl->forwardCommand($cp);
                break;

            case 'ilinfoscreengui':
                $this->infoScreenForward();
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case "":
                $cmd .= "Object";
                $this->$cmd();
                break;

            default:
                $q_gui = SurveyQuestionGUI::_getQuestionGUI(
                    $q_type,
                    $this->edit_request->getQuestionId()
                );
                $this->log->debug("- This is the switch/case default, going to question id =" . $this->edit_request->getQuestionId());
                $q_gui->setQuestionTabs();
                $this->ctrl->forwardCommand($q_gui);

                // not on create
                if ($q_gui->object->isComplete()) {
                    $this->tpl->setTitle($this->lng->txt("question") . ": " . $q_gui->object->getTitle());
                }
                break;
        }
        if (strtolower($this->edit_request->getBaseClass()) !== "iladministrationgui" &&
            $this->getCreationMode() !== true) {
            $this->tpl->printToStdout();
        }
    }

    protected function initEditForm(): ilPropertyFormGUI
    {
        $obj_service = $this->object_service;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'properties'));
        $form->setTitle($this->lng->txt("properties"));
        $form->setMultipart(false);
        $form->setId("properties");

        // title
        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setSubmitFormOnEnter(true);
        $title->setValue($this->object->getTitle());
        $title->setSize(min(40, ilObject::TITLE_LENGTH));
        $title->setMaxLength(ilObject::TITLE_LENGTH);
        $title->setRequired(true);
        $form->addItem($title);

        // desc
        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
        $desc->setValue($this->object->getLongDescription());
        $desc->setRows(2);
        $desc->setCols(40);
        $form->addItem($desc);

        // online
        $online = new ilCheckboxInputGUI($this->lng->txt("spl_online_property"), "online");
        $online->setInfo($this->lng->txt("spl_online_property_description"));
        $online->setChecked($this->object->getOnline());
        $form->addItem($online);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('obj_presentation'));
        $form->addItem($section);

        // tile image
        $obj_service->commonSettings()->legacyForm($form, $this->object)->addTileImage();

        $form->addCommandButton("saveProperties", $this->lng->txt("save"));

        return $form;
    }

    /**
     * Edit question pool properties
     */
    public function propertiesObject(ilPropertyFormGUI $a_form = null): void
    {
        if (!$a_form) {
            $a_form = $this->initEditForm();
        }

        $this->tpl->setVariable("ADM_CONTENT", $a_form->getHTML());
    }

    public function savePropertiesObject(): void
    {
        $obj_service = $this->object_service;
        $form = $this->initEditForm();
        if ($form->checkInput()) {
            $this->object->setTitle($form->getInput("title"));
            $this->object->setDescription($form->getInput("desc"));
            $this->object->setOnline((int) $form->getInput("online"));

            $this->object->saveToDb();

            // tile image
            $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"), true);
            $this->ctrl->redirect($this, "properties");
        }

        $form->setValuesByPost();
        $this->propertiesObject($form);
    }


    /**
     * Copies checked questions in the questionpool to a clipboard
     */
    public function copyObject(): void
    {
        $qids = $this->edit_request->getQuestionIds();
        if (count($qids) > 0) {
            foreach ($qids as $key => $value) {
                $this->object->copyToClipboard($value);
            }
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("spl_copy_insert_clipboard"), true);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("spl_copy_select_none"), true);
        }
        $this->ctrl->redirect($this, "questions");
    }

    /**
     * mark one or more question objects for moving
     */
    public function moveObject(): void
    {
        $qids = $this->edit_request->getQuestionIds();
        if (count($qids) > 0) {
            foreach ($qids as $key => $value) {
                $this->object->moveToClipboard($value);
            }
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("spl_move_insert_clipboard"), true);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("spl_move_select_none"), true);
        }
        $this->ctrl->redirect($this, "questions");
    }

    /**
     * export a question
     */
    public function exportQuestionObject(): void
    {
        $qids = $this->edit_request->getQuestionIds();
        if (count($qids) > 0) {
            $this->createExportFileObject($qids);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_export_select_none"));
            $this->questionsObject();
        }
    }

    /**
     * Creates a confirmation form to delete questions from the question pool
     */
    public function deleteQuestionsObject(): void
    {
        $this->checkPermission('write');

        // create an array of all checked checkboxes
        $checked_questions = $this->edit_request->getQuestionIds();
        if (count($checked_questions) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_delete_select_none"));
            $this->questionsObject();
            return;
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("qpl_confirm_delete_questions"));

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDeleteQuestions");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmDeleteQuestions");

        $infos = $this->object->getQuestionInfos($checked_questions);
        foreach ($infos as $data) {
            $txt = $data["title"] . " (" .
                SurveyQuestion::_getQuestionTypeName($data["type_tag"]) . ")";
            if ($data["description"]) {
                $txt .= "<div class=\"small\">" . $data["description"] . "</div>";
            }

            $cgui->addItem("q_id[]", $data["id"], $txt);
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    public function confirmDeleteQuestionsObject(): void
    {
        // delete questions after confirmation
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("qpl_questions_deleted"), true);
        $qids = $this->edit_request->getQuestionIds();
        foreach ($qids as $q_id) {
            $this->object->removeQuestion($q_id);
        }
        $this->ctrl->redirect($this, "questions");
    }

    public function cancelDeleteQuestionsObject(): void
    {
        // delete questions after confirmation
        $this->ctrl->redirect($this, "questions");
    }

    /**
     * paste questions from the clipboard into the question pool
     */
    public function pasteObject(): void
    {
        $clip_questions = $this->edit_manager->getQuestionsFromClipboard();
        if (count($clip_questions) > 0) {
            $this->object->pasteFromClipboard();
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("spl_paste_no_objects"), true);
        }
        $this->ctrl->redirect($this, "questions");
    }

    /**
     * display the import form to import questions into the question pool
     */
    public function importQuestionsObject(): void
    {
        $tpl = $this->tpl;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "uploadQuestions"));
        $form->setTitle($this->lng->txt("import_question"));

        $fi = new ilFileInputGUI($this->lng->txt("select_file"), "qtidoc");
        $fi->setSuffixes(array("xml", "zip"));
        $fi->setRequired(true);
        $form->addItem($fi);

        $form->addCommandButton("uploadQuestions", $this->lng->txt("import"));
        $form->addCommandButton("questions", $this->lng->txt("cancel"));

        $tpl->setContent($form->getHTML());
    }

    /**
     * imports question(s) into the questionpool
     */
    public function uploadQuestionsObject(): void
    {
        // check if file was uploaded
        $source = $_FILES["qtidoc"]["tmp_name"];
        $error = 0;
        if (($source === 'none') || (!$source) || $_FILES["qtidoc"]["error"] > UPLOAD_ERR_OK) {
            $error = 1;
        }
        // check correct file type
        if (!$error && strpos("xml", $_FILES["qtidoc"]["type"]) !== false) {
            $error = 1;
        }
        if (!$error) {
            // import file into questionpool
            // create import directory
            $this->object->createImportDirectory();

            // copy uploaded file to import directory
            $full_path = $this->object->getImportDirectory() . "/" . $_FILES["qtidoc"]["name"];

            ilFileUtils::moveUploadedFile(
                $_FILES["qtidoc"]["tmp_name"],
                $_FILES["qtidoc"]["name"],
                $full_path
            );
            $source = $full_path;
            $this->object->importObject($source, true);
            unlink($source);
        }
        $this->ctrl->redirect($this, "questions");
    }

    public function filterQuestionBrowserObject(): void
    {
        $table_gui = new ilSurveyQuestionsTableGUI($this, 'questions');
        $table_gui->writeFilterToSession();
        $this->ctrl->redirect($this, 'questions');
    }

    public function resetfilterQuestionBrowserObject(): void
    {
        $table_gui = new ilSurveyQuestionsTableGUI($this, 'questions');
        $table_gui->resetFilter();
        $this->ctrl->redirect($this, 'questions');
    }

    /**
     * list questions of question pool
     */
    public function questionsObject(): void
    {
        $ilUser = $this->user;
        $ilToolbar = $this->toolbar;

        $this->object->purgeQuestions();

        if ($this->checkPermissionBool('write')) {
            $qtypes = new ilSelectInputGUI("", "sel_question_types");
            $qtypes->setValue($ilUser->getPref("svy_lastquestiontype"));
            $ilToolbar->addInputItem($qtypes);

            $options = array();
            foreach (ilObjSurveyQuestionPool::_getQuestiontypes() as $translation => $data) {
                $options[$data["type_tag"]] = $translation;
            }
            $qtypes->setOptions($options);

            $ilToolbar->setFormAction($this->ctrl->getFormAction($this));

            $button = ilSubmitButton::getInstance();
            $button->setCaption("svy_create_question");
            $button->setCommand("createQuestion");
            $ilToolbar->addButtonInstance($button);

            $ilToolbar->addSeparator();

            $button = ilSubmitButton::getInstance();
            $button->setCaption("import");
            $button->setCommand("importQuestions");
            $ilToolbar->addButtonInstance($button);
        }

        $table_gui = new ilSurveyQuestionsTableGUI($this, 'questions', $this->checkPermissionBool('write'));
        $table_gui->setEditable($this->checkPermissionBool('write'));
        $arrFilter = array();
        foreach ($table_gui->getFilterItems() as $item) {
            if ($item->getValue() !== false) {
                $arrFilter[$item->getPostVar()] = $item->getValue();
            }
        }
        $table_gui->setData($this->object->getQuestionsData($arrFilter));
        $this->tpl->setContent($table_gui->getHTML());
    }

    public function updateObject(): void
    {
        $this->update = $this->object->update();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
    }

    protected function afterSave(ilObject $new_object): void
    {
        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);

        ilUtil::redirect("ilias.php?ref_id=" . $new_object->getRefId() .
            "&baseClass=ilObjSurveyQuestionPoolGUI");
    }

    /**
     * list all export files
     */
    public function exportObject(): void
    {
        $ilToolbar = $this->toolbar;

        $ilToolbar->addButton(
            $this->lng->txt('create_export_file'),
            $this->ctrl->getLinkTarget($this, 'createExportFile')
        );

        $table_gui = new ilSurveyQuestionPoolExportTableGUI($this, 'export');
        $export_dir = $this->object->getExportDirectory();
        $export_files = $this->object->getExportFiles($export_dir);
        $data = array();
        foreach ($export_files as $exp_file) {
            $file_arr = explode("__", $exp_file);
            $data[] = array('file' => $exp_file,
                            'date' => ilDatePresentation::formatDate(new ilDateTime($file_arr[0], IL_CAL_UNIX)),
                            'size' => filesize($export_dir . "/" . $exp_file)
            );
        }
        $table_gui->setData($data);
        $this->tpl->setContent($table_gui->getHTML());
    }

    /**
     * create export file
     */
    public function createExportFileObject($questions = null): void
    {
        $this->checkPermission("write");

        /** @var ilObjSurveyQuestionPool $svy */
        $svy = $this->object;
        $survey_exp = new ilSurveyQuestionpoolExport($svy);
        $survey_exp->buildExportFile($questions);
        $this->ctrl->redirect($this, "export");
    }

    /**
     * download export file
     */
    public function downloadExportFileObject(): void
    {
        $files = $this->edit_request->getFiles();
        if (count($files) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "export");
        }

        if (count($files) > 1) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("select_max_one_item"), true);
            $this->ctrl->redirect($this, "export");
        }


        $export_dir = $this->object->getExportDirectory();

        $file = basename($files[0]);

        ilFileDelivery::deliverFileLegacy($export_dir . "/" . $file, $file);
    }

    /**
     * confirmation screen for export file deletion
     */
    public function confirmDeleteExportFileObject(): void
    {
        $files = $this->edit_request->getFiles();
        if (count($files) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "export");
        }

        $this->tpl->setOnScreenMessage('question', $this->lng->txt("info_delete_sure"));
        $table_gui = new ilSurveyQuestionPoolExportTableGUI($this, 'export', true);
        $export_dir = $this->object->getExportDirectory();
        $data = array();
        foreach ($files as $exp_file) {
            $file_arr = explode("__", $exp_file);
            $data[] = array('file' => $exp_file,
                            'date' => ilDatePresentation::formatDate(new ilDateTime($file_arr[0], IL_CAL_UNIX)),
                            'size' => filesize($export_dir . "/" . $exp_file)
            );
        }
        $table_gui->setData($data);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }

    public function cancelDeleteExportFileObject(): void
    {
        ilSession::clear("ilExportFiles");
        $this->ctrl->redirect($this, "export");
    }

    public function deleteExportFileObject(): void
    {
        $export_dir = $this->object->getExportDirectory();
        $files = $this->edit_request->getFiles();
        foreach ($files as $file) {
            $file = basename($file);

            $exp_file = $export_dir . "/" . $file;
            $exp_dir = $export_dir . "/" . substr($file, 0, -4);
            if (is_file($exp_file)) {
                unlink($exp_file);
            }
            if (is_dir($exp_dir)) {
                ilFileUtils::delDir($exp_dir);
            }
        }
        $this->ctrl->redirect($this, "export");
    }

    protected function initImportForm(string $new_type): ilPropertyFormGUI
    {
        $form = parent::initImportForm($new_type);
        $form->getItemByPostVar('importfile')->setSuffixes(array("zip", "xml"));

        return $form;
    }

    protected function initCreationForms(string $new_type): array
    {
        $form = $this->initImportForm($new_type);

        $forms = array(self::CFORM_NEW => $this->initCreateForm($new_type),
            self::CFORM_IMPORT => $form);

        return $forms;
    }

    protected function importFileObject(int $parent_id = null, bool $catch_errors = true): void
    {
        $tpl = $this->tpl;

        if (!$parent_id) {
            $parent_id = $this->edit_request->getRefId();
        }
        $new_type = $this->edit_request->getNewType();

        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
        if (!$this->checkPermissionBool("create", "", $new_type)) {
            throw new ilPermissionException($this->lng->txt("no_create_permission"));
        }

        $this->lng->loadLanguageModule($new_type);
        $this->ctrl->setParameter($this, "new_type", $new_type);

        $form = $this->initImportForm($new_type);
        if ($form->checkInput()) {
            $newObj = new ilObjSurveyQuestionPool();
            $newObj->setType($new_type);
            $newObj->setTitle("dummy");
            $newObj->create(true);
            $this->putObjectInTree($newObj);

            $newObj->createImportDirectory();

            // copy uploaded file to import directory
            $upload = $_FILES["importfile"];
            $file = pathinfo($upload["name"]);
            $full_path = $newObj->getImportDirectory() . "/" . $upload["name"];
            ilFileUtils::moveUploadedFile(
                $upload["tmp_name"],
                $upload["name"],
                $full_path
            );

            // import qti data
            $newObj->importObject($full_path);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_imported"), true);
            ilUtil::redirect("ilias.php?ref_id=" . $newObj->getRefId() .
                "&baseClass=ilObjSurveyQuestionPoolGUI");
        }

        // display form to correct errors
        $form->setValuesByPost();
        $tpl->setContent($form->getHTML());
    }

    /**
     * create new question
     */
    public function createQuestionObject(): void
    {
        $ilUser = $this->user;

        $ilUser->writePref(
            "svy_lastquestiontype",
            $this->edit_request->getSelectedQuestionTypes()
        );

        $q_gui = SurveyQuestionGUI::_getQuestionGUI(
            $this->edit_request->getSelectedQuestionTypes()
        );
        $q_gui->object->setObjId($this->object->getId());
        $q_gui->object->createNewQuestion();

        $this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $q_gui->object->getId());
        $this->ctrl->setParameterByClass(
            get_class($q_gui),
            "sel_question_types",
            $this->edit_request->getSelectedQuestionTypes()
        );
        $this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
    }

    /**
     * create preview of object
     */
    public function previewObject(): void
    {
        $q_gui = SurveyQuestionGUI::_getQuestionGUI(
            "",
            $this->edit_request->getPreview()
        );
        $this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $q_gui->getQuestionType());
        $this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $this->edit_request->getPreview());
        $this->ctrl->redirectByClass(get_class($q_gui), "preview");
    }

    /**
     * this one is called from the info button in the repository
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
        if (!$this->checkPermissionBool("read")) {
            $this->checkPermission("visible");
        }

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();

        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        $this->ctrl->forwardCommand($info);
    }

    protected function addLocatorItems(): void
    {
        $ilLocator = $this->locator;
        switch ($this->ctrl->getCmd()) {
            case "create":
            case "importFile":
            case "cancel":
                break;
            default:
                $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->edit_request->getRefId());
                break;
        }
        if ($this->edit_request->getQuestionId() > 0) {
            $q_id = $this->edit_request->getQuestionId();
            $q_type = SurveyQuestion::_getQuestionType($q_id) . "GUI";
            $q_title = SurveyQuestion::_getTitle($q_id);
            if ($q_title) {
                // not on create
                $this->ctrl->setParameterByClass($q_type, "q_id", $q_id);
                $ilLocator->addItem(
                    $q_title,
                    $this->ctrl->getLinkTargetByClass($q_type, "editQuestion")
                );
            }
        }
    }

    protected function getTabs(): void
    {
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("spl");

        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case "":
            case "ilpermissiongui":
            case "ilobjectmetadatagui":
            case "ilsurveyphrasesgui":
                break;
            default:
                return;
        }

        // questions
        $force_active = ($this->ctrl->getCmdClass() === "" &&
            $this->ctrl->getCmd() !== "properties" && $this->ctrl->getCmd() !== "infoScreen") ||
            ($this->ctrl->getCmd() === "" || $this->ctrl->getCmd() === null);
        if (!$force_active) {
            $sort = $this->edit_request->getSort();
            if (count($sort) > 0) {
                $force_active = true;
            }
        }

        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addTarget(
                "survey_questions",
                $this->ctrl->getLinkTarget($this, 'questions'),
                array("questions", "filterQuestionBrowser", "filter", "reset", "createQuestion",
                 "importQuestions", "deleteQuestions", "copy", "paste",
                 "exportQuestions", "confirmDeleteQuestions", "cancelDeleteQuestions",
                 "confirmPasteQuestions", "cancelPasteQuestions", "uploadQuestions",
                 "editQuestion", "addMaterial", "removeMaterial", "save", "cancel",
                 "cancelExplorer", "linkChilds", "addGIT", "addST", "addPG", "preview",
                 "moveCategory", "deleteCategory", "addPhrase", "addCategory", "savePhrase",
                 "addSelectedPhrase", "cancelViewPhrase", "confirmSavePhrase", "cancelSavePhrase",
                 "insertBeforeCategory", "insertAfterCategory", "confirmDeleteCategory",
                 "cancelDeleteCategory", "categories", "saveCategories",
                 "savePhrase", "addPhrase"
                 ),
                array("ilobjsurveyquestionpoolgui", "ilsurveyphrasesgui"),
                "",
                $force_active
            );

            $this->tabs_gui->addTarget(
                "info_short",
                $this->ctrl->getLinkTarget($this, "infoScreen"),
                array("infoScreen", "showSummary")
            );
        }

        if ($this->checkPermissionBool('write')) {
            // properties
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, 'properties'),
                array("properties", "saveProperties"),
                "",
                ""
            );

            // manage phrases
            $this->tabs_gui->addTarget(
                "manage_phrases",
                $this->ctrl->getLinkTargetByClass("ilsurveyphrasesgui", "phrases"),
                array("phrases", "deletePhrase", "confirmDeletePhrase", "cancelDeletePhrase", "editPhrase", "newPhrase", "saveEditPhrase", "phraseEditor"),
                "ilsurveyphrasesgui",
                ""
            );

            // meta data
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

            // export
            $this->tabs_gui->addTarget(
                "export",
                $this->ctrl->getLinkTarget($this, 'export'),
                array("export", "createExportFile", "confirmDeleteExportFile",
                 "downloadExportFile", "cancelDeleteExportFile", "deleteExportFile"),
                "",
                ""
            );
        }

        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }

    /**
     * Save obligatory states
     */
    public function saveObligatoryObject(): void
    {
        $obligatory = $this->edit_request->getObligatory();
        $this->object->setObligatoryStates($obligatory);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, "questions");
    }

    /**
     * Redirect script to call a survey question pool reference id
     */
    public static function _goto(string $a_target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ctrl = $DIC->ctrl();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();

        if ($ilAccess->checkAccess("visible", "", $a_target) ||
            $ilAccess->checkAccess("read", "", $a_target)) {
            $ctrl->setParameterByClass("ilObjSurveyQuestionPoolGUI", "ref_id", $a_target);
            $ctrl->redirectByClass("ilObjSurveyQuestionPoolGUI", "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }
    }
}
