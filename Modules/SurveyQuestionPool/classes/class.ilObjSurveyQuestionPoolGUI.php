<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjSurveyQuestionPoolGUI
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version  $Id$
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveyMultipleChoiceQuestionGUI, SurveyMetricQuestionGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveySingleChoiceQuestionGUI, SurveyTextQuestionGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: SurveyMatrixQuestionGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: ilSurveyPhrasesGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: ilObjectMetaDataGUI, ilPermissionGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjSurveyQuestionPoolGUI: ilCommonActionDispatcherGUI
*
* @extends ilObjectGUI
* @ingroup ModulesSurveyQuestionPool
*/

class ilObjSurveyQuestionPoolGUI extends ilObjectGUI
{
    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilLogger
     */
    protected $log;

    public $defaultscript;
    
    /**
    * Constructor
    * @access public
    */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->tpl = $DIC["tpl"];
        $this->user = $DIC->user();
        $this->toolbar = $DIC->toolbar();
        $this->error = $DIC["ilErr"];
        $this->locator = $DIC["ilLocator"];
        $this->help = $DIC["ilHelp"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->type = "spl";
        $lng->loadLanguageModule("survey");
        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array("ref_id"));

        parent::__construct("", $_GET["ref_id"], true, false);

        $this->log = ilLoggerFactory::getLogger('svy');
    }

    /**
    * execute command
    */
    public function executeCommand()
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
        if ($_GET["q_id"] < 1) {
            $q_type = ($_POST["sel_question_types"] != "")
                ? $_POST["sel_question_types"]
                : $_GET["sel_question_types"];
        }

        $this->log->debug("- cmd=" . $cmd . " next_class=" . $next_class);
        switch ($next_class) {
            case 'ilobjectmetadatagui':
                $this->checkPermission('write');
                include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'ilpermissiongui':
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;
                
            case "ilsurveyphrasesgui":
                include_once("./Modules/SurveyQuestionPool/classes/class.ilSurveyPhrasesGUI.php");
                $phrases_gui = new ilSurveyPhrasesGUI($this);
                $ret = &$this->ctrl->forwardCommand($phrases_gui);
                break;
                
            case 'ilobjectcopygui':
                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('spl');
                $this->ctrl->forwardCommand($cp);
                break;
            
            case 'ilinfoscreengui':
                $this->infoScreenForward();
                break;
            
            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case "":
                $cmd .= "Object";
                $ret = &$this->$cmd();
                break;
                
            default:
                include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
                $q_gui = SurveyQuestionGUI::_getQuestionGUI($q_type, $_GET["q_id"]);
                $this->log->debug("- This is the switch/case default, going to question id =" . $_GET["q_id"]);
                // $q_gui->object->setObjId($this->object->getId());
                $q_gui->setQuestionTabs();
                $ret = &$this->ctrl->forwardCommand($q_gui);
                
                // not on create
                if ($q_gui->object->isComplete()) {
                    $this->tpl->setTitle($this->lng->txt("question") . ": " . $q_gui->object->getTitle());
                }
                break;
        }
        if (strtolower($_GET["baseClass"]) != "iladministrationgui" &&
            $this->getCreationMode() != true) {
            $this->tpl->show();
        }
    }
    
    protected function initEditForm()
    {
        $obj_service = $this->object_service;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
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
    * Questionpool properties
    */
    public function propertiesObject(ilPropertyFormGUI $a_form = null)
    {
        if (!$a_form) {
            $a_form = $this->initEditForm();
        }
        
        $this->tpl->setVariable("ADM_CONTENT", $a_form->getHTML());
    }
    
    /**
    * Save questionpool properties
    */
    public function savePropertiesObject()
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

            
            ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);
            $this->ctrl->redirect($this, "properties");
        }
        
        $form->setValuesByPost();
        $this->propertiesObject($form);
    }
    

    /**
    * Copies checked questions in the questionpool to a clipboard
    */
    public function copyObject()
    {
        if (count($_POST["q_id"]) > 0) {
            foreach ($_POST["q_id"] as $key => $value) {
                $this->object->copyToClipboard($value);
            }
            ilUtil::sendInfo($this->lng->txt("spl_copy_insert_clipboard"), true);
        } else {
            ilUtil::sendInfo($this->lng->txt("spl_copy_select_none"), true);
        }
        $this->ctrl->redirect($this, "questions");
    }
    
    /**
    * mark one or more question objects for moving
    */
    public function moveObject()
    {
        if (count($_POST["q_id"]) > 0) {
            foreach ($_POST["q_id"] as $key => $value) {
                $this->object->moveToClipboard($value);
            }
            ilUtil::sendInfo($this->lng->txt("spl_move_insert_clipboard"), true);
        } else {
            ilUtil::sendInfo($this->lng->txt("spl_move_select_none"), true);
        }
        $this->ctrl->redirect($this, "questions");
    }
    
    /**
    * export a question
    */
    public function exportQuestionObject()
    {
        if (is_array($_POST['q_id']) && count($_POST['q_id']) > 0) {
            $this->createExportFileObject($_POST['q_id']);
        } else {
            ilUtil::sendInfo($this->lng->txt("qpl_export_select_none"));
            $this->questionsObject();
        }
    }
    
    /**
    * Creates a confirmation form to delete questions from the question pool
    */
    public function deleteQuestionsObject()
    {
        $this->checkPermission('write');
        
        // create an array of all checked checkboxes
        $checked_questions = $_POST['q_id'];
        if (count($checked_questions) == 0) {
            ilUtil::sendInfo($this->lng->txt("qpl_delete_select_none"));
            $this->questionsObject();
            return;
        }
        
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("qpl_confirm_delete_questions"));

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDeleteQuestions");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmDeleteQuestions");
                        
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
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

    /**
    * delete questions
    */
    public function confirmDeleteQuestionsObject()
    {
        // delete questions after confirmation
        ilUtil::sendSuccess($this->lng->txt("qpl_questions_deleted"), true);
        foreach ($_POST['q_id'] as $q_id) {
            $this->object->removeQuestion($q_id);
        }
        $this->ctrl->redirect($this, "questions");
    }
    
    /**
    * cancel delete questions
    */
    public function cancelDeleteQuestionsObject()
    {
        // delete questions after confirmation
        $this->ctrl->redirect($this, "questions");
    }
    
    /**
    * paste questios from the clipboard into the question pool
    */
    public function pasteObject()
    {
        if (array_key_exists("spl_clipboard", $_SESSION)) {
            $this->object->pasteFromClipboard();
        } else {
            ilUtil::sendInfo($this->lng->txt("spl_paste_no_objects"), true);
        }
        $this->ctrl->redirect($this, "questions");
    }

    /**
    * display the import form to import questions into the questionpool
    */
    public function importQuestionsObject()
    {
        $tpl = $this->tpl;
        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "uploadQuestions"));
        $form->setTitle($this->lng->txt("import_question"));

        include_once("./Services/Form/classes/class.ilFileInputGUI.php");
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
    public function uploadQuestionsObject()
    {
        // check if file was uploaded
        $source = $_FILES["qtidoc"]["tmp_name"];
        $error = 0;
        if (($source == 'none') || (!$source) || $_FILES["qtidoc"]["error"] > UPLOAD_ERR_OK) {
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

            ilUtil::moveUploadedFile(
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
    
    public function filterQuestionBrowserObject()
    {
        include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyQuestionsTableGUI.php";
        $table_gui = new ilSurveyQuestionsTableGUI($this, 'questions');
        $table_gui->writeFilterToSession();
        $this->ctrl->redirect($this, 'questions');
    }
    
    public function resetfilterQuestionBrowserObject()
    {
        include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyQuestionsTableGUI.php";
        $table_gui = new ilSurveyQuestionsTableGUI($this, 'questions');
        $table_gui->resetFilter();
        $this->ctrl->redirect($this, 'questions');
    }
    
    /**
    * list questions of question pool
    */
    public function questionsObject($arrFilter = null)
    {
        $ilUser = $this->user;
        $ilToolbar = $this->toolbar;

        $this->object->purgeQuestions();

        $_SESSION['q_id_table_nav'] = $_GET['q_id_table_nav'];
            
        if ($this->checkPermissionBool('write')) {
            include_once "Services/Form/classes/class.ilSelectInputGUI.php";
            $qtypes = new ilSelectInputGUI("", "sel_question_types");
            $qtypes->setValue($ilUser->getPref("svy_lastquestiontype"));
            $ilToolbar->addInputItem($qtypes);

            $options = array();
            foreach (ilObjSurveyQuestionPool::_getQuestionTypes() as $translation => $data) {
                $options[$data["type_tag"]] = $translation;
            }
            $qtypes->setOptions($options);
            
            $ilToolbar->setFormAction($this->ctrl->getFormAction($this));
            
            include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
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
                
        include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyQuestionsTableGUI.php";
        $table_gui = new ilSurveyQuestionsTableGUI($this, 'questions', (($this->checkPermissionBool('write') ? true : false)));
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

    public function updateObject()
    {
        $this->update = $this->object->update();
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
    }
    
    public function afterSave(ilObject $a_new_object)
    {
        // always send a message
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        
        ilUtil::redirect("ilias.php?ref_id=" . $a_new_object->getRefId() .
            "&baseClass=ilObjSurveyQuestionPoolGUI");
    }

    /*
    * list all export files
    */
    public function exportObject()
    {
        $ilToolbar = $this->toolbar;
        
        $ilToolbar->addButton(
            $this->lng->txt('create_export_file'),
            $this->ctrl->getLinkTarget($this, 'createExportFile')
        );
        
        include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyQuestionPoolExportTableGUI.php";
        $table_gui = new ilSurveyQuestionPoolExportTableGUI($this, 'export');
        $export_dir = $this->object->getExportDirectory();
        $export_files = $this->object->getExportFiles($export_dir);
        $data = array();
        foreach ($export_files as $exp_file) {
            $file_arr = explode("__", $exp_file);
            array_push($data, array('file' => $exp_file, 'date' => ilDatePresentation::formatDate(new ilDateTime($file_arr[0], IL_CAL_UNIX)), 'size' => filesize($export_dir . "/" . $exp_file)));
        }
        $table_gui->setData($data);
        $this->tpl->setContent($table_gui->getHTML());
    }

    /**
    * create export file
    */
    public function createExportFileObject($questions = null)
    {
        $this->checkPermission("write");
        
        include_once("./Modules/SurveyQuestionPool/classes/class.ilSurveyQuestionpoolExport.php");
        $survey_exp = new ilSurveyQuestionpoolExport($this->object);
        $survey_exp->buildExportFile($questions);
        $this->ctrl->redirect($this, "export");
    }
    
    /**
    * download export file
    */
    public function downloadExportFileObject()
    {
        if (!isset($_POST["file"])) {
            ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "export");
        }

        if (count($_POST["file"]) > 1) {
            ilUtil::sendInfo($this->lng->txt("select_max_one_item"), true);
            $this->ctrl->redirect($this, "export");
        }


        $export_dir = $this->object->getExportDirectory();
        
        $file = basename($_POST["file"][0]);
        
        ilUtil::deliverFile($export_dir . "/" . $file, $file);
    }

    /**
    * confirmation screen for export file deletion
    */
    public function confirmDeleteExportFileObject()
    {
        if (!isset($_POST["file"])) {
            ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "export");
        }

        ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));
        include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyQuestionPoolExportTableGUI.php";
        $table_gui = new ilSurveyQuestionPoolExportTableGUI($this, 'export', true);
        $export_dir = $this->object->getExportDirectory();
        $data = array();
        foreach ($_POST['file'] as $exp_file) {
            $file_arr = explode("__", $exp_file);
            array_push($data, array('file' => $exp_file, 'date' => ilDatePresentation::formatDate(new ilDateTime($file_arr[0], IL_CAL_UNIX)), 'size' => filesize($export_dir . "/" . $exp_file)));
        }
        $table_gui->setData($data);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }


    /**
    * cancel deletion of export files
    */
    public function cancelDeleteExportFileObject()
    {
        ilSession::clear("ilExportFiles");
        $this->ctrl->redirect($this, "export");
    }

    /**
    * delete export files
    */
    public function deleteExportFileObject()
    {
        $export_dir = $this->object->getExportDirectory();
        foreach ($_POST['file'] as $file) {
            $file = basename($file);
            
            $exp_file = $export_dir . "/" . $file;
            $exp_dir = $export_dir . "/" . substr($file, 0, strlen($file) - 4);
            if (@is_file($exp_file)) {
                unlink($exp_file);
            }
            if (@is_dir($exp_dir)) {
                ilUtil::delDir($exp_dir);
            }
        }
        $this->ctrl->redirect($this, "export");
    }

    protected function initImportForm($a_new_type)
    {
        $form = parent::initImportForm($a_new_type);
        $form->getItemByPostVar('importfile')->setSuffixes(array("zip", "xml"));
    
        return $form;
    }

    protected function initCreationForms($a_new_type)
    {
        $form = $this->initImportForm($a_new_type);
        
        $forms = array(self::CFORM_NEW => $this->initCreateForm($a_new_type),
            self::CFORM_IMPORT => $form);

        return $forms;
    }

    /**
    * form for new survey object import
    */
    public function importFileObject($parent_id = null, $a_catch_errors = true)
    {
        $tpl = $this->tpl;
        $ilErr = $this->error;

        if (!$parent_id) {
            $parent_id = $_GET["ref_id"];
        }
        $new_type = $_REQUEST["new_type"];

        // create permission is already checked in createObject. This check here is done to prevent hacking attempts
        if (!$this->checkPermissionBool("create", "", $new_type)) {
            $ilErr->raiseError($this->lng->txt("no_create_permission"));
        }

        $this->lng->loadLanguageModule($new_type);
        $this->ctrl->setParameter($this, "new_type", $new_type);

        $form = $this->initImportForm($new_type);
        if ($form->checkInput()) {
            include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
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
            ilUtil::moveUploadedFile(
                $upload["tmp_name"],
                $upload["name"],
                $full_path
            );

            // import qti data
            $qtiresult = $newObj->importObject($full_path);

            ilUtil::sendSuccess($this->lng->txt("object_imported"), true);
            ilUtil::redirect("ilias.php?ref_id=" . $newObj->getRefId() .
                "&baseClass=ilObjSurveyQuestionPoolGUI");
        }
        
        // display form to correct errors
        $form->setValuesByPost();
        $tpl->setContent($form->getHtml());
    }

    /**
    * create new question
    */
    public function &createQuestionObject()
    {
        $ilUser = $this->user;
        
        $ilUser->writePref("svy_lastquestiontype", $_POST["sel_question_types"]);
        
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
        $q_gui = &SurveyQuestionGUI::_getQuestionGUI($_POST["sel_question_types"]);
        $q_gui->object->setObjId($this->object->getId());
        $q_gui->object->createNewQuestion();
        
        $this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $q_gui->object->getId());
        $this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $_POST["sel_question_types"]);
        $this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
    }

    /**
    * create preview of object
    */
    public function &previewObject()
    {
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
        $q_gui = &SurveyQuestionGUI::_getQuestionGUI("", $_GET["preview"]);
        $this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $q_gui->getQuestionType());
        $this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $_GET["preview"]);
        $this->ctrl->redirectByClass(get_class($q_gui), "preview");
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
        $this->infoScreenForward();
    }
    
    /**
    * show information screen
    */
    public function infoScreenForward()
    {
        if (!$this->checkPermissionBool("read")) {
            $this->checkPermission("visible");
        }

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();

        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
        $this->ctrl->forwardCommand($info);
    }
    
    public function addLocatorItems()
    {
        $ilLocator = $this->locator;
        switch ($this->ctrl->getCmd()) {
            case "create":
            case "importFile":
            case "cancel":
                break;
            default:
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $_GET["ref_id"]);
                break;
        }
        if ((int) $_GET["q_id"]) {
            $q_id = (int) $_GET["q_id"];
            include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
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
    
    /**
    * adds tabs to tab gui object
    *
    * @param	object		$tabs_gui		ilTabsGUI object
    */
    public function getTabs()
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
                break;
        }
            
        // questions
        $force_active = (($this->ctrl->getCmdClass() == "" &&
            $this->ctrl->getCmd() != "properties" && $this->ctrl->getCmd() != "infoScreen") ||
            $this->ctrl->getCmd() == "")
            ? true
            : false;
        if (!$force_active) {
            if (is_array($_GET["sort"])) {
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
            include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
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
    public function saveObligatoryObject()
    {
        $obligatory = array();
        foreach ($_POST as $key => $value) {
            if (preg_match("/obligatory_(\d+)/", $key, $matches)) {
                $obligatory[] = $matches[1];
            }
        }
        $this->object->setObligatoryStates($obligatory);
        
        ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, "questions");
    }

    /**
    * Redirect script to call a survey question pool reference id
    *
    * @param integer $a_target The reference id of the question pool
    * @access	public
    */
    public static function _goto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        
        if ($ilAccess->checkAccess("visible", "", $a_target) ||
            $ilAccess->checkAccess("read", "", $a_target)) {
            $_GET["baseClass"] = "ilObjSurveyQuestionPoolGUI";
            $_GET["cmd"] = "infoScreen";
            $_GET["ref_id"] = $a_target;
            include_once("ilias.php");
            exit;
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }
    }
} // END class.ilObjSurveyQuestionPoolGUI
