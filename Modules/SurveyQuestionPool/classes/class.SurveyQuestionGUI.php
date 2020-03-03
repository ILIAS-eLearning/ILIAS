<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Basic class for all survey question types
*
* The SurveyQuestionGUI class defines and encapsulates basic methods and attributes
* for survey question types to be used for all parent classes.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesSurveyQuestionPool
*/
abstract class SurveyQuestionGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    protected $tpl;
    protected $lng;
    protected $ctrl;
    protected $cumulated; // [array]
    protected $parent_url;

    /**
     * @var ilLogger
     */
    protected $log;
    
    public $object;
        
    public function __construct($a_id = -1)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->toolbar = $DIC->toolbar();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, "q_id");
        $this->ctrl->setParameterByClass($_GET["cmdClass"], "sel_question_types", $_GET["sel_question_types"]);
        $this->cumulated = array();
        $this->tabs = $DIC->tabs();
        
        $this->initObject();
        
        if ($a_id > 0) {
            $this->object->loadFromDb($a_id);
        }
        $this->log = ilLoggerFactory::getLogger('svy');
    }
    
    abstract protected function initObject();
    abstract public function setQuestionTabs();
    
    public function &executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            default:
                $ret =&$this->$cmd();
                break;
        }
        return $ret;
    }

    /**
    * Creates a question gui representation
    *
    * Creates a question gui representation and returns the alias to the question gui
    * note: please do not use $this inside this method to allow static calls
    *
    * @param string $question_type The question type as it is used in the language database
    * @param integer $question_id The database ID of an existing question to load it into ASS_QuestionGUI
    * @return object The alias to the question object
    * @access public
    */
    public static function _getQuestionGUI($questiontype, $question_id = -1)
    {
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
        if ((!$questiontype) and ($question_id > 0)) {
            $questiontype = SurveyQuestion::_getQuestiontype($question_id);
        }
        SurveyQuestion::_includeClass($questiontype, 1);
        $question_type_gui = $questiontype . "GUI";
        $question = new $question_type_gui($question_id);
        return $question;
    }
    
    public static function _getGUIClassNameForId($a_q_id)
    {
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
        $q_type = SurveyQuestion::_getQuestiontype($a_q_id);
        $class_name = SurveyQuestionGUI::_getClassNameForQType($q_type);
        return $class_name;
    }

    public static function _getClassNameForQType($q_type)
    {
        return $q_type;
    }
    
    /**
    * Returns the question type string
    *
    * @result string The question type string
    * @access public
    */
    public function getQuestionType()
    {
        return $this->object->getQuestionType();
    }
        
    protected function outQuestionText($template)
    {
        $questiontext = $this->object->getQuestiontext();
        if (preg_match("/^<.[\\>]?>(.*?)<\\/.[\\>]*?>$/", $questiontext, $matches)) {
            $questiontext = $matches[1];
        }
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        if ($this->object->getObligatory($survey_id)) {
            $template->setVariable("OBLIGATORY_TEXT", ' *');
        }
    }
    
    public function setBackUrl($a_url)
    {
        $this->parent_url = $a_url;
    }
    
    public function setQuestionTabsForClass($guiclass)
    {
        $rbacsystem = $this->rbacsystem;
        $ilTabs = $this->tabs;
        
        $this->ctrl->setParameterByClass($guiclass, "sel_question_types", $this->getQuestionType());
        $this->ctrl->setParameterByClass($guiclass, "q_id", $_GET["q_id"]);
        
        if ($this->parent_url) {
            $addurl = "";
            if (strlen($_GET["new_for_survey"])) {
                $addurl = "&new_id=" . $_GET["q_id"];
            }
            $ilTabs->setBackTarget($this->lng->txt("menubacktosurvey"), $this->parent_url . $addurl);
        } else {
            $this->ctrl->setParameterByClass("ilObjSurveyQuestionPoolGUI", "q_id_table_nav", $_SESSION['q_id_table_nav']);
            $ilTabs->setBackTarget($this->lng->txt("spl"), $this->ctrl->getLinkTargetByClass("ilObjSurveyQuestionPoolGUI", "questions"));
        }
        if ($_GET["q_id"]) {
            $ilTabs->addNonTabbedLink(
                "preview",
                $this->lng->txt("preview"),
                $this->ctrl->getLinkTargetByClass($guiclass, "preview")
            );
        }
        
        if ($rbacsystem->checkAccess('edit', $_GET["ref_id"])) {
            $ilTabs->addTab(
                "edit_properties",
                $this->lng->txt("properties"),
                $this->ctrl->getLinkTargetByClass($guiclass, "editQuestion")
            );
            
            if (stristr($guiclass, "matrix")) {
                $ilTabs->addTab(
                    "layout",
                    $this->lng->txt("layout"),
                    $this->ctrl->getLinkTargetByClass($guiclass, "layout")
                );
            }
        }
        if ($_GET["q_id"]) {
            $ilTabs->addTab(
                "material",
                $this->lng->txt("material"),
                $this->ctrl->getLinkTargetByClass($guiclass, "material")
            );
        }

        if ($this->object->getId() > 0) {
            $title = $this->lng->txt("edit") . " &quot;" . $this->object->getTitle() . "&quot";
        } else {
            $title = $this->lng->txt("create_new") . " " . $this->lng->txt($this->getQuestionType());
        }

        $this->tpl->setVariable("HEADER", $title);
    }

    
    //
    // EDITOR
    //
    
    protected function initEditForm()
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "save"));
        $form->setTitle($this->lng->txt($this->getQuestionType()));
        $form->setMultipart(false);
        $form->setTableWidth("100%");
        // $form->setId("essay");

        // title
        $title = new ilTextInputGUI($this->lng->txt("title"), "title");
        $title->setRequired(true);
        $form->addItem($title);
        
        // label
        $label = new ilTextInputGUI($this->lng->txt("label"), "label");
        $label->setInfo($this->lng->txt("label_info"));
        $label->setRequired(false);
        $form->addItem($label);

        // author
        $author = new ilTextInputGUI($this->lng->txt("author"), "author");
        $author->setRequired(true);
        $form->addItem($author);
        
        // description
        $description = new ilTextInputGUI($this->lng->txt("description"), "description");
        $description->setRequired(false);
        $form->addItem($description);
        
        // questiontext
        $question = new ilTextAreaInputGUI($this->lng->txt("question"), "question");
        $question->setRequired(true);
        $question->setRows(10);
        $question->setCols(80);
        $question->setUseRte(true);
        include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
        $question->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("survey"));
        $question->addPlugin("latex");
        $question->addButton("latex");
        $question->addButton("pastelatex");
        $question->setRTESupport($this->object->getId(), "spl", "survey");
        $form->addItem($question);
        
        // obligatory
        $shuffle = new ilCheckboxInputGUI($this->lng->txt("obligatory"), "obligatory");
        $shuffle->setValue(1);
        $shuffle->setRequired(false);
        $form->addItem($shuffle);
        
        $this->addFieldsToEditForm($form);
        
        $this->addCommandButtons($form);
                
        // values
        $title->setValue($this->object->getTitle());
        $label->setValue($this->object->label);
        $author->setValue($this->object->getAuthor());
        $description->setValue($this->object->getDescription());
        $question->setValue($this->object->prepareTextareaOutput($this->object->getQuestiontext()));
        $shuffle->setChecked($this->object->getObligatory());
        
        return $form;
    }
    
    protected function addCommandButtons($a_form)
    {
        $a_form->addCommandButton("saveReturn", $this->lng->txt("save_return"));
        $a_form->addCommandButton("save", $this->lng->txt("save"));
        
        // pool question?
        if (ilObject::_lookupType($this->object->getObjId()) == "spl") {
            if ($this->object->hasCopies()) {
                $a_form->addCommandButton("saveSync", $this->lng->txt("svy_save_sync"));
            }
        }
    }
            
    protected function editQuestion(ilPropertyFormGUI $a_form = null)
    {
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("edit_properties");
        
        if (!$a_form) {
            $a_form = $this->initEditForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    protected function saveSync()
    {
        $this->save($_REQUEST["rtrn"], true);
    }

    protected function saveReturn()
    {
        $this->save(true);
    }
    
    protected function saveForm()
    {
        $form = $this->initEditForm();
        if ($form->checkInput()) {
            if ($this->validateEditForm($form)) {
                $this->object->setTitle($form->getInput("title"));
                $this->object->label = ($form->getInput("label"));
                $this->object->setAuthor($form->getInput("author"));
                $this->object->setDescription($form->getInput("description"));
                $this->object->setQuestiontext($form->getInput("question"));
                $this->object->setObligatory($form->getInput("obligatory"));
                
                $this->importEditFormValues($form);
                
                // will save both core and extended data
                $this->object->saveToDb();
                
                return true;
            }
        }
                
        $form->setValuesByPost();
        $this->editQuestion($form);
        return false;
    }
    
    protected function save($a_return = false, $a_sync = false)
    {
        $ilUser = $this->user;
                    
        if ($this->saveForm()) {
            // #13784
            if ($a_return &&
                !SurveyQuestion::_isComplete($this->object->getId())) {
                ilUtil::sendFailure($this->lng->txt("survey_error_insert_incomplete_question"));
                return $this->editQuestion();
            }
            
            $ilUser->setPref("svy_lastquestiontype", $this->object->getQuestionType());
            $ilUser->writePref("svy_lastquestiontype", $this->object->getQuestionType());

            $originalexists = SurveyQuestion::_questionExists($this->object->original_id);
            $this->ctrl->setParameter($this, "q_id", $this->object->getId());
            include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";

            // pool question?
            if ($a_sync) {
                ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->redirect($this, 'copySyncForm');
            } else {
                // form: update original pool question, too?
                if ($originalexists &&
                    SurveyQuestion::_isWriteable($this->object->original_id, $ilUser->getId())) {
                    if ($a_return) {
                        $this->ctrl->setParameter($this, 'rtrn', 1);
                    }
                    $this->ctrl->redirect($this, 'originalSyncForm');
                }
            }

            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->redirectAfterSaving($a_return);
        }
    }
        
    protected function copySyncForm()
    {
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("edit_properties");
        
        include_once "Modules/SurveyQuestionPool/classes/class.ilSurveySyncTableGUI.php";
        $tbl = new ilSurveySyncTableGUI($this, "copySyncForm", $this->object);
        
        $this->tpl->setContent($tbl->getHTML());
    }
    
    protected function syncCopies()
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        
        if (!sizeof($_POST["qid"])) {
            ilUtil::sendFailure($lng->txt("select_one"));
            return $this->copySyncForm();
        }
        
        foreach ($this->object->getCopyIds(true) as $survey_id => $questions) {
            // check permissions for "parent" survey
            $can_write = false;
            $ref_ids = ilObject::_getAllReferences($survey_id);
            foreach ($ref_ids as $ref_id) {
                if ($ilAccess->checkAccess("edit", "", $ref_id)) {
                    $can_write = true;
                    break;
                }
            }
            
            if ($can_write) {
                foreach ($questions as $qid) {
                    if (in_array($qid, $_POST["qid"])) {
                        $id = $this->object->getId();
                        
                        $this->object->setId($qid);
                        $this->object->setOriginalId($id);
                        $this->object->saveToDb();
                        
                        $this->object->setId($id);
                        $this->object->setOriginalId(null);
                        
                        // see: SurveyQuestion::syncWithOriginal()
                        // what about material?
                    }
                }
            }
        }
        
        ilUtil::sendSuccess($lng->txt("survey_sync_success"), true);
        $this->redirectAfterSaving($_REQUEST["rtrn"]);
    }
    
    protected function originalSyncForm()
    {
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("edit_properties");
        
        $this->ctrl->saveParameter($this, "rtrn");
        
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("confirm_sync_questions"));

        $cgui->setFormAction($this->ctrl->getFormAction($this, "confirmRemoveQuestions"));
        $cgui->setCancel($this->lng->txt("no"), "cancelSync");
        $cgui->setConfirm($this->lng->txt("yes"), "sync");

        $this->tpl->setContent($cgui->getHTML());
    }
    
    protected function sync()
    {
        $original_id = $this->object->original_id;
        if ($original_id) {
            $this->object->syncWithOriginal();
        }

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->redirectAfterSaving($_REQUEST["rtrn"]);
    }

    protected function cancelSync()
    {
        ilUtil::sendInfo($this->lng->txt("question_changed_in_survey_only"), true);
        $this->redirectAfterSaving($_REQUEST["rtrn"]);
    }
            
    /**
     * Redirect to calling survey or to edit form
     *
     * @param bool $a_return
     */
    protected function redirectAfterSaving($a_return = false)
    {
        // return?
        if ($a_return) {
            // to calling survey
            if ($this->parent_url) {
                $addurl = "";
                if (strlen($_GET["new_for_survey"])) {
                    $addurl = "&new_id=" . $_GET["q_id"];
                }
                ilUtil::redirect(str_replace("&amp;", "&", $this->parent_url) . $addurl);
            }
            // to pool
            else {
                $this->ctrl->setParameterByClass("ilObjSurveyQuestionPoolGUI", "q_id_table_nav", $_SESSION['q_id_table_nav']);
                $this->ctrl->redirectByClass("ilObjSurveyQuestionPoolGUI", "questions");
            }
        }
        // stay in form
        else {
            $this->ctrl->setParameterByClass($_GET["cmdClass"], "q_id", $this->object->getId());
            $this->ctrl->setParameterByClass($_GET["cmdClass"], "sel_question_types", $_GET["sel_question_types"]);
            $this->ctrl->setParameterByClass($_GET["cmdClass"], "new_for_survey", $_GET["new_for_survey"]);
            $this->ctrl->redirectByClass($_GET["cmdClass"], "editQuestion");
        }
    }
    
    protected function cancel()
    {
        if ($this->parent_url) {
            ilUtil::redirect($this->parent_url);
        } else {
            $this->ctrl->redirectByClass("ilobjsurveyquestionpoolgui", "questions");
        }
    }
        
    protected function validateEditForm(ilPropertyFormGUI $a_form)
    {
        return true;
    }
        
    abstract protected function addFieldsToEditForm(ilPropertyFormGUI $a_form);
    abstract protected function importEditFormValues(ilPropertyFormGUI $a_form);
                
    abstract public function getPrintView($question_title = 1, $show_questiontext = 1);
    
    protected function getPrintViewQuestionTitle($question_title = 1)
    {
        switch ($question_title) {
            case ilObjSurvey::PRINT_HIDE_LABELS:
                $title = ilUtil::prepareFormOutput($this->object->getTitle());
                break;

            #19448  get rid of showing only the label without title
            //case 2:
            //	$title = ilUtil::prepareFormOutput($this->object->getLabel());
            //	break;

            case ilObjSurvey::PRINT_SHOW_LABELS:
                $title = ilUtil::prepareFormOutput($this->object->getTitle());
                if (trim($this->object->getLabel())) {
                    $title .= ' <span class="questionLabel">(' . ilUtil::prepareFormOutput($this->object->getLabel()) . ')</span>';
                }
                break;
        }
        return $title;
    }
    
    /**
    * Creates a preview of the question
    *
    * @access private
    */
    public function preview()
    {
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("preview");
        
        $tpl = new ilTemplate("tpl.il_svy_qpl_preview.html", true, true, "Modules/SurveyQuestionPool");
        
        if ($this->object->getObligatory()) {
            $tpl->setCurrentBlock("required");
            $tpl->setVariable("TEXT_REQUIRED", $this->lng->txt("required_field"));
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setVariable("QUESTION_OUTPUT", $this->getWorkingForm());
        
        include_once "Services/UIComponent/Panel/classes/class.ilPanelGUI.php";
        $panel = ilPanelGUI::getInstance();
        $panel->setBody($tpl->get());
        
        $this->tpl->setContent($panel->getHTML());
    }
    
    
    //
    // EXECUTION
    //
    
    abstract public function getWorkingForm($working_data = "", $question_title = 1, $show_questiontext = 1, $error_message = "", $survey_id = null);
    
    /**
    * Creates the HTML output of the question material(s)
    */
    protected function getMaterialOutput()
    {
        if (count($this->object->getMaterial())) {
            $template = new ilTemplate("tpl.il_svy_qpl_material.html", true, true, "Modules/SurveyQuestionPool");
            foreach ($this->object->getMaterial() as $material) {
                $template->setCurrentBlock('material');
                switch ($material->type) {
                    case 0:
                        $href = SurveyQuestion::_getInternalLinkHref($material->internal_link, $_GET['ref_id']);
                        $template->setVariable('MATERIAL_TYPE', 'internallink');
                        $template->setVariable('MATERIAL_HREF', $href);
                        break;
                }
                $template->setVariable('MATERIAL_TITLE', (strlen($material->title)) ? ilUtil::prepareFormOutput($material->title) : $this->lng->txt('material'));
                $template->setVariable('TEXT_AVAILABLE_MATERIALS', $this->lng->txt('material'));
                $template->parseCurrentBlock();
            }
            return $template->get();
        }
        return "";
    }
    
    //
    // MATERIAL
    //
    
    /**
    * Material tab of the survey questions
    */
    public function material($checkonly = false)
    {
        $rbacsystem = $this->rbacsystem;
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("material");

        $add_html = '';
        if ($rbacsystem->checkAccess('write', $_GET['ref_id'])) {
            include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
            $form = new ilPropertyFormGUI();
            $form->setFormAction($this->ctrl->getFormAction($this));
            $form->setTitle($this->lng->txt('add_material'));
            $form->setMultipart(false);
            $form->setTableWidth("100%");
            $form->setId("material");

            // material
            $material = new ilRadioGroupInputGUI($this->lng->txt("material"), "internalLinkType");
            $material->setRequired(true);
            $material->addOption(new ilRadioOption($this->lng->txt('obj_lm'), "lm"));
            $material->addOption(new ilRadioOption($this->lng->txt('obj_st'), "st"));
            $material->addOption(new ilRadioOption($this->lng->txt('obj_pg'), "pg"));
            $material->addOption(new ilRadioOption($this->lng->txt('glossary_term'), "glo"));
            $form->addItem($material);

            $form->addCommandButton("addMaterial", $this->lng->txt("add"));

            $errors = false;

            if ($checkonly) {
                $form->setValuesByPost();
                $errors = !$form->checkInput();
                if ($errors) {
                    $checkonly = false;
                }
            }
            $add_html = $form->getHTML();
        }


        $mat_html = "";
        if (count($this->object->getMaterial())) {
            include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyMaterialsTableGUI.php";
            $table_gui = new ilSurveyMaterialsTableGUI($this, 'material', (($rbacsystem->checkAccess('write', $_GET['ref_id']) ? true : false)));
            $data = array();
            foreach ($this->object->getMaterial() as $material) {
                switch ($material->type) {
                    case 0:
                        $href = SurveyQuestion::_getInternalLinkHref($material->internal_link, $_GET['ref_id']);
                        $type = $this->lng->txt('internal_link');
                        break;
                }
                $title = (strlen($material->title)) ? ilUtil::prepareFormOutput($material->title) : $this->lng->txt('material');
                array_push($data, array('href' => $href, 'title' => $title, 'type' => $type));
            }
            $table_gui->setData($data);
            $mat_html = $table_gui->getHTML();
        }

        if (!$checkonly) {
            $this->tpl->setVariable("ADM_CONTENT", $add_html . $mat_html);
        }
        return $errors;
    }
    
    public function deleteMaterial()
    {
        if (is_array($_POST['idx'])) {
            $this->object->deleteMaterials($_POST['idx']);
            ilUtil::sendSuccess($this->lng->txt('materials_deleted'), true);
        } else {
            ilUtil::sendFailure($this->lng->txt('no_checkbox'), true);
        }
        $this->ctrl->redirect($this, 'material');
    }

    /**
    * Add materials to a question
    */
    public function addMaterial()
    {
        $tree = $this->tree;
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        
        $ilTabs->activateTab("material");
        
        $ilToolbar->addButton(
            $this->lng->txt("cancel"),
            $this->ctrl->getLinkTarget($this, "material")
        );
        
        if (strlen($_SESSION["link_new_type"]) || !$this->material(true)) {
            include_once("./Modules/SurveyQuestionPool/classes/class.ilMaterialExplorer.php");
            switch ($_POST["internalLinkType"]) {
                case "lm":
                    $_SESSION["link_new_type"] = "lm";
                    $_SESSION["search_link_type"] = "lm";
                    break;
                case "glo":
                    $_SESSION["link_new_type"] = "glo";
                    $_SESSION["search_link_type"] = "glo";
                    break;
                case "st":
                    $_SESSION["link_new_type"] = "lm";
                    $_SESSION["search_link_type"] = "st";
                    break;
                case "pg":
                    $_SESSION["link_new_type"] = "lm";
                    $_SESSION["search_link_type"] = "pg";
                    break;
            }

            $exp = new ilMaterialExplorer($this, 'addMaterial', $_SESSION["link_new_type"]);
            $exp->setPathOpen((int) $_GET["ref_id"]);
            if (!$exp->handleCommand()) {
                include_once "Services/UIComponent/Panel/classes/class.ilPanelGUI.php";
                $panel = ilPanelGUI::getInstance();
                $panel->setHeading($this->lng->txt("select_object_to_link"));
                $panel->setBody($exp->getHTML());

                $this->tpl->setContent($panel->getHTML());
            }
        }
    }
    
    public function removeMaterial()
    {
        $this->object->material = array();
        $this->object->saveToDb();
        $this->editQuestion();
    }
    
    public function cancelExplorer()
    {
        unset($_SESSION["link_new_type"]);
        ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, 'material');
    }
        
    public function addPG()
    {
        $this->object->addInternalLink("il__pg_" . $_GET["pg"]);
        unset($_SESSION["link_new_type"]);
        unset($_SESSION["search_link_type"]);
        ilUtil::sendSuccess($this->lng->txt("material_added_successfully"), true);
        $this->ctrl->redirect($this, "material");
    }
    
    public function addST()
    {
        $this->object->addInternalLink("il__st_" . $_GET["st"]);
        unset($_SESSION["link_new_type"]);
        unset($_SESSION["search_link_type"]);
        ilUtil::sendSuccess($this->lng->txt("material_added_successfully"), true);
        $this->ctrl->redirect($this, "material");
    }

    public function addGIT()
    {
        $this->object->addInternalLink("il__git_" . $_GET["git"]);
        unset($_SESSION["link_new_type"]);
        unset($_SESSION["search_link_type"]);
        ilUtil::sendSuccess($this->lng->txt("material_added_successfully"), true);
        $this->ctrl->redirect($this, "material");
    }
    
    public function linkChilds()
    {
        $ilTabs = $this->tabs;
        
        $selectable_items = array();
        
        $source_id = $_GET["source_id"];
        
        switch ($_SESSION["search_link_type"]) {
            case "pg":
                include_once "./Modules/LearningModule/classes/class.ilLMPageObject.php";
                include_once("./Modules/LearningModule/classes/class.ilObjContentObjectGUI.php");
                $cont_obj_gui = new ilObjContentObjectGUI("", $source_id, true);
                $cont_obj = $cont_obj_gui->object;
                $pages = ilLMPageObject::getPageList($cont_obj->getId());
                foreach ($pages as $page) {
                    if ($page["type"] == $_SESSION["search_link_type"]) {
                        $selectable_items[] = array(
                            "item_type" => $page["type"]
                            ,"item_id" => $page["obj_id"]
                            ,"title" => $page["title"]
                        );
                    }
                }
                break;
                
            case "st":
                include_once("./Modules/LearningModule/classes/class.ilObjContentObjectGUI.php");
                $cont_obj_gui = new ilObjContentObjectGUI("", $source_id, true);
                $cont_obj = $cont_obj_gui->object;
                // get all chapters
                $ctree =&$cont_obj->getLMTree();
                $nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));
                foreach ($nodes as $node) {
                    if ($node["type"] == $_SESSION["search_link_type"]) {
                        $selectable_items[] = array(
                            "item_type" => $node["type"]
                            ,"item_id" => $node["obj_id"]
                            ,"title" => $node["title"]
                        );
                    }
                }
                break;
                
            case "glo":
                include_once "./Modules/Glossary/classes/class.ilObjGlossary.php";
                $glossary = new ilObjGlossary($source_id, true);
                // get all glossary items
                $terms = $glossary->getTermList();
                foreach ($terms as $term) {
                    $selectable_items[] = array(
                            "item_type" => "GIT"
                            ,"item_id" => $term["id"]
                            ,"title" => $term["term"]
                        );
                }
                break;
                
            case "lm":
                $this->object->addInternalLink("il__lm_" . $source_id);
                break;
        }
        
        if (sizeof($selectable_items)) {
            $ilTabs->activateTab("material");
            $this->ctrl->setParameter($this, "q_id", $this->object->getId());
            $this->ctrl->setParameter($this, "source_id", $source_id);
                
            include_once "Modules/SurveyQuestionPool/classes/tables/class.SurveyMaterialsSourceTableGUI.php";
            $tbl = new SurveyMaterialsSourceTableGUI($this, "linkChilds", "addMaterial");
            $tbl->setData($selectable_items);
            $this->tpl->setContent($tbl->getHTML());
        } else {
            if ($_SESSION["search_link_type"] == "lm") {
                ilUtil::sendSuccess($this->lng->txt("material_added_successfully"), true);
                
                unset($_SESSION["link_new_type"]);
                unset($_SESSION["search_link_type"]);
                $this->ctrl->redirect($this, "material");
            } else {
                ilUtil::sendFailure($this->lng->txt("material_added_empty"), true);
                $this->ctrl->redirect($this, "addMaterial");
            }
        }
    }
    
        
    //
    // PHRASES (see SurveyMatrixQuestionGUI)
    //
    
    protected function initPhrasesForm()
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "addSelectedPhrase"));
        $form->setTitle($this->lng->txt("add_phrase"));
        // $form->setDescription($this->lng->txt("add_phrase_introduction"));
        
        $group = new ilRadioGroupInputGUI($this->lng->txt("phrase"), "phrases");
        $group->setRequired(true);
        $form->addItem($group);
        
        include_once "./Modules/SurveyQuestionPool/classes/class.ilSurveyPhrases.php";
        foreach (ilSurveyPhrases::_getAvailablePhrases() as $phrase_id => $phrase_array) {
            $categories = ilSurveyPhrases::_getCategoriesForPhrase($phrase_id);
                
            $opt = new ilRadioOption($phrase_array["title"], $phrase_id);
            $opt->setInfo(join($categories, ","));
            $group->addOption($opt);
            
            if ($phrase_array["org_title"] == "dp_standard_numbers") {
                $min = new ilNumberInputGUI($this->lng->txt("lower_limit"), "lower_limit");
                $min->setRequired(true);
                $min->setSize(5);
                $opt->addSubItem($min);

                $max = new ilNumberInputGUI($this->lng->txt("upper_limit"), "upper_limit");
                $max->setRequired(true);
                $max->setSize(5);
                $opt->addSubItem($max);
            }
        }
        
        $form->addCommandButton("addSelectedPhrase", $this->lng->txt("add_phrase"));
        $form->addCommandButton("editQuestion", $this->lng->txt("cancel"));
        
        return $form;
    }
        
    /**
    * Creates an output for the addition of phrases
    */
    protected function addPhrase(ilPropertyFormGUI $a_form = null)
    {
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("edit_properties");
        
        if (!$a_form) {
            $result = $this->saveForm();
            if ($result) {
                $this->object->saveToDb();
            }
            
            $a_form = $this->initPhrasesForm();
        }
                    
        $this->tpl->setContent($a_form->getHTML());
    }

    protected function addSelectedPhrase()
    {
        $form = $this->initPhrasesForm();
        if ($form->checkInput()) {
            $phrase_id = $form->getInput("phrases");
            
            $valid = true;
            if (strcmp($this->object->getPhrase($phrase_id), "dp_standard_numbers") != 0) {
                $this->object->addPhrase($phrase_id);
            } else {
                $min = $form->getInput("lower_limit");
                $max = $form->getInput("upper_limit");

                if ($max <= $min) {
                    $max_field = $form->getItemByPostVar("upper_limit");
                    $max_field->setAlert($this->lng->txt("upper_limit_must_be_greater"));
                    $valid = false;
                } else {
                    $this->object->addStandardNumbers($min, $max);
                }
            }
            
            if ($valid) {
                $this->object->saveToDb();
                
                ilUtil::sendSuccess($this->lng->txt('phrase_added'), true);
                $this->ctrl->redirect($this, 'editQuestion');
            }
        }
        
        $form->setValuesByPost();
        $this->addPhrase($form);
    }
    
    /**
    * Creates an output to save the current answers as a phrase
    *
    * @access public
    */
    public function savePhrase($a_reload = false)
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        
        $ilTabs->activateTab("edit_properties");
        
        if (!$a_reload) {
            $result = $this->saveForm();
            if ($result) {
                $this->object->saveToDb();
            }
        }
        
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $txt = new ilTextInputGUI($this->lng->txt("enter_phrase_title"), "phrase_title");
        $ilToolbar->addInputItem($txt, true);
        $ilToolbar->addFormButton($this->lng->txt("confirm"), "confirmSavePhrase");
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));
        
        include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveySavePhraseTableGUI.php";
        $table_gui = new ilSurveySavePhraseTableGUI($this, 'editQuestion');
        $table_gui->setDescription($this->lng->txt("save_phrase_introduction"));
        
        // matrix?
        if (method_exists($this->object, "getCategories")) {
            $categories = $this->object->getCategories();
        } else {
            $categories = $this->object->getColumns();
        }

        $data = array();
        for ($i = 0; $i < $categories->getCategoryCount(); $i++) {
            $cat = $categories->getCategory($i);
            
            $data[] = array(
                "answer" => $cat->title,
                "other" => $cat->other,
                "scale" => $cat->scale,
                "neutral" => $cat->neutral
            );
        }
        $table_gui->setData($data);
        $_SESSION['save_phrase_data'] = $data; // :TODO: see savePhrase()
        
        $this->tpl->setContent($table_gui->getHTML());
    }

    /**
    * Save a new phrase to the database
    *
    * @access public
    */
    public function confirmSavePhrase()
    {
        $title = $_POST["phrase_title"];
        
        $valid = true;
        if (!trim($title)) {
            ilUtil::sendFailure($this->lng->txt("qpl_savephrase_empty"));
            $valid = false;
        } elseif ($this->object->phraseExists($title)) {
            ilUtil::sendFailure($this->lng->txt("qpl_savephrase_exists"));
            $valid = false;
        }
        
        if ($valid) {
            $this->object->savePhrase($title);

            ilUtil::sendSuccess($this->lng->txt("phrase_saved"), true);
            $this->ctrl->redirect($this, "editQuestion");
        }
        
        $this->savePhrase(true);
    }
    
    protected function renderStatisticsDetailsTable(array $a_head, array $a_rows, array $a_foot = null)
    {
        $html = array();
        $html[] = '<div class="ilTableOuter table-responsive">';
        $html[] = '<table class="table table-striped">';

        $html[] = "<thead>";
        $html[] = "<tr>";
        foreach ($a_head as $col) {
            $col = trim($col);
            $html[] = "<th>";
            $html[] = ($col != "") ? $col : "&nbsp;";
            $html[] = "</th>";
        }
        $html[] = "</tr>";
        $html[] = "</thead>";

        $html[] = "<tbody>";
        foreach ($a_rows as $row) {
            $html[] = "<tr>";
            foreach ($row as $col) {
                $col = trim($col);
                $html[] = "<td>";
                $html[] = ($col != "") ? $col : "&nbsp;";
                $html[] = "</td>";
            }
            $html[] = "</tr>";
        }
        $html[] = "</tbody>";
                
        if ($a_foot) {
            $html[] = "<tfoot>";
            $html[] = "<tr>";
            foreach ($a_foot as $col) {
                $col = trim($col);
                $html[] = "<td>";
                $html[] = ($col != "") ? $col : "&nbsp;";
                $html[] = "</td>";
            }
            $html[] = "</tr>";
            $html[] = "</tfoot>";
        }
                
        $html[] = "</table>";
        $html[] = "</div>";
        return implode("\n", $html);
    }
}
