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
use ILIAS\SurveyQuestionPool\Editing\EditManager;

/**
 * Basic class for all survey question types
 * The SurveyQuestionGUI class defines and encapsulates basic methods and attributes
 * for survey question types to be used for all parent classes.
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
abstract class SurveyQuestionGUI
{
    protected EditingGUIRequest $request;
    protected EditManager $edit_manager;
    protected ilRbacSystem $rbacsystem;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected ilTree $tree;
    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected array $cumulated = [];
    protected string $parent_url = "";
    protected ilLogger $log;
    public ?SurveyQuestion $object = null;

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


        $this->request = $DIC->surveyQuestionPool()
                             ->internal()
                             ->gui()
                             ->editing()
                             ->request();

        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, "q_id");
        $this->ctrl->setParameterByClass(
            $this->ctrl->getCmdClass(),
            "sel_question_types",
            $this->request->getSelectedQuestionTypes()
        );
        $this->cumulated = array();
        $this->tabs = $DIC->tabs();

        $this->initObject();

        if ($a_id > 0) {
            $this->object->loadFromDb($a_id);
        }
        $this->log = ilLoggerFactory::getLogger('svy');

        $this->edit_manager = $DIC->surveyQuestionPool()
            ->internal()
            ->domain()
            ->editing();
    }

    abstract protected function initObject(): void;

    abstract public function setQuestionTabs(): void;

    public function executeCommand(): string
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }
        return (string) $ret;
    }

    /**
     * Creates a question gui representation
     * @todo move to factory
     */
    public static function _getQuestionGUI(
        ?string $questiontype,
        int $question_id = -1
    ): SurveyQuestionGUI {
        if ((!$questiontype) and ($question_id > 0)) {
            $questiontype = SurveyQuestion::_getQuestionType($question_id);
        }
        SurveyQuestion::_includeClass($questiontype, 1);
        $question_type_gui = $questiontype . "GUI";
        $question = new $question_type_gui($question_id);
        return $question;
    }

    public static function _getGUIClassNameForId(int $a_q_id): string
    {
        $q_type = SurveyQuestion::_getQuestionType($a_q_id);
        $class_name = SurveyQuestionGUI::_getClassNameForQType($q_type);
        return $class_name;
    }

    public static function _getClassNameForQType(string $q_type): string
    {
        return $q_type;
    }

    /**
     * Returns the question type string
     */
    public function getQuestionType(): string
    {
        return $this->object->getQuestionType();
    }

    protected function outQuestionText(ilTemplate $template): void
    {
        $questiontext = $this->object->getQuestiontext();
        if (preg_match("/^<.[\\>]?>(.*?)<\\/.[\\>]*?>$/", $questiontext, $matches)) {
            $questiontext = $matches[1];
        }
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        if ($this->object->getObligatory()) {
            $template->setVariable("OBLIGATORY_TEXT", ' *');
        }
    }

    public function setBackUrl(string $a_url): void
    {
        $this->parent_url = $a_url;
    }

    public function setQuestionTabsForClass(string $guiclass): void
    {
        $rbacsystem = $this->rbacsystem;
        $ilTabs = $this->tabs;

        $this->ctrl->setParameterByClass($guiclass, "sel_question_types", $this->getQuestionType());
        $this->ctrl->setParameterByClass(
            $guiclass,
            "q_id",
            $this->request->getQuestionId()
        );

        if ($this->parent_url) {
            $addurl = "";
            if ($this->request->getNewForSurvey() > 0) {
                $addurl = "&new_id=" . $this->request->getQuestionId();
            }
            $ilTabs->setBackTarget($this->lng->txt("menubacktosurvey"), $this->parent_url . $addurl);
        } else {
            $ilTabs->setBackTarget($this->lng->txt("spl"), $this->ctrl->getLinkTargetByClass("ilObjSurveyQuestionPoolGUI", "questions"));
        }
        if ($this->request->getQuestionId()) {
            $ilTabs->addNonTabbedLink(
                "preview",
                $this->lng->txt("preview"),
                $this->ctrl->getLinkTargetByClass($guiclass, "preview")
            );
        }

        if ($rbacsystem->checkAccess('edit', $this->request->getRefId())) {
            $ilTabs->addTab(
                "edit_properties",
                $this->lng->txt("properties"),
                $this->ctrl->getLinkTargetByClass($guiclass, "editQuestion")
            );

            if (stripos($guiclass, "matrix") !== false) {
                $ilTabs->addTab(
                    "layout",
                    $this->lng->txt("layout"),
                    $this->ctrl->getLinkTargetByClass($guiclass, "layout")
                );
            }
        }
        if ($this->request->getQuestionId() > 0) {
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

    protected function initEditForm(): ilPropertyFormGUI
    {
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

    protected function addCommandButtons(ilPropertyFormGUI $a_form): void
    {
        $a_form->addCommandButton("saveReturn", $this->lng->txt("save_return"));
        $a_form->addCommandButton("save", $this->lng->txt("save"));

        // pool question?
        if (ilObject::_lookupType($this->object->getObjId()) === "spl" && $this->object->hasCopies()) {
            $a_form->addCommandButton("saveSync", $this->lng->txt("svy_save_sync"));
        }
    }

    protected function editQuestion(ilPropertyFormGUI $a_form = null): void
    {
        $ilTabs = $this->tabs;

        $ilTabs->activateTab("edit_properties");

        if (!$a_form) {
            $a_form = $this->initEditForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    protected function saveSync(): void
    {
        $this->save($this->request->getReturn(), true);
    }

    protected function saveReturn(): void
    {
        $this->save(true);
    }

    protected function saveForm(): bool
    {
        $form = $this->initEditForm();
        if ($form->checkInput() && $this->validateEditForm($form)) {
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

        $form->setValuesByPost();
        $this->editQuestion($form);
        return false;
    }

    protected function save(
        bool $a_return = false,
        bool $a_sync = false
    ): void {
        $ilUser = $this->user;

        if ($this->saveForm()) {
            // #13784
            if ($a_return &&
                !SurveyQuestion::_isComplete($this->object->getId())) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("survey_error_insert_incomplete_question"));
                $this->editQuestion();
                return;
            }

            $ilUser->setPref("svy_lastquestiontype", $this->object->getQuestionType());
            $ilUser->writePref("svy_lastquestiontype", $this->object->getQuestionType());

            $originalexists = SurveyQuestion::_questionExists((int) $this->object->original_id);
            $this->ctrl->setParameter($this, "q_id", $this->object->getId());

            // pool question?
            if ($a_sync) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->redirect($this, 'copySyncForm');
            } elseif ($originalexists &&
                SurveyQuestion::_isWriteable($this->object->original_id, $ilUser->getId())) {
                // form: update original pool question, too?
                if ($a_return) {
                    $this->ctrl->setParameter($this, 'rtrn', 1);
                }
                $this->ctrl->redirect($this, 'originalSyncForm');
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->redirectAfterSaving($a_return);
        }
    }

    protected function copySyncForm(): void
    {
        $ilTabs = $this->tabs;

        $ilTabs->activateTab("edit_properties");

        $tbl = new ilSurveySyncTableGUI($this, "copySyncForm", $this->object);

        $this->tpl->setContent($tbl->getHTML());
    }

    protected function syncCopies(): void
    {
        $lng = $this->lng;
        $ilAccess = $this->access;

        $qids = $this->request->getQuestionIds();
        if (count($qids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("select_one"));
            $this->copySyncForm();
            return;
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
                    if (in_array($qid, $qids)) {
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

        $this->tpl->setOnScreenMessage('success', $lng->txt("survey_sync_success"), true);
        $this->redirectAfterSaving($this->request->getReturn());
    }

    protected function originalSyncForm(): void
    {
        $ilTabs = $this->tabs;

        $ilTabs->activateTab("edit_properties");

        $this->ctrl->saveParameter($this, "rtrn");

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("confirm_sync_questions"));

        $cgui->setFormAction($this->ctrl->getFormAction($this, "confirmRemoveQuestions"));
        $cgui->setCancel($this->lng->txt("no"), "cancelSync");
        $cgui->setConfirm($this->lng->txt("yes"), "sync");

        $this->tpl->setContent($cgui->getHTML());
    }

    protected function sync(): void
    {
        $original_id = $this->object->original_id;
        if ($original_id) {
            $this->object->syncWithOriginal();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->redirectAfterSaving($this->request->getReturn());
    }

    protected function cancelSync(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("question_changed_in_survey_only"), true);
        $this->redirectAfterSaving($this->request->getReturn());
    }

    /**
     * Redirect to calling survey or to edit form
     */
    protected function redirectAfterSaving(
        bool $a_return = false
    ): void {
        // return?
        if ($a_return) {
            // to calling survey
            if ($this->parent_url) {
                $addurl = "";
                if ($this->request->getNewForSurvey() > 0) {
                    $addurl = "&new_id=" . $this->request->getQuestionId();
                }
                ilUtil::redirect(str_replace("&amp;", "&", $this->parent_url) . $addurl);
            }
            // to pool
            else {
                $this->ctrl->redirectByClass("ilObjSurveyQuestionPoolGUI", "questions");
            }
        }
        // stay in form
        else {
            $this->ctrl->setParameterByClass(
                $this->ctrl->getCmdClass(),
                "q_id",
                $this->object->getId()
            );
            $this->ctrl->setParameterByClass(
                $this->ctrl->getCmdClass(),
                "sel_question_types",
                $this->request->getSelectedQuestionTypes()
            );
            $this->ctrl->setParameterByClass(
                $this->ctrl->getCmdClass(),
                "new_for_survey",
                $this->request->getNewForSurvey()
            );
            $this->ctrl->redirectByClass($this->ctrl->getCmdClass(), "editQuestion");
        }
    }

    protected function cancel(): void
    {
        if ($this->parent_url) {
            ilUtil::redirect($this->parent_url);
        } else {
            $this->ctrl->redirectByClass("ilobjsurveyquestionpoolgui", "questions");
        }
    }

    protected function validateEditForm(ilPropertyFormGUI $a_form): bool
    {
        return true;
    }

    abstract protected function addFieldsToEditForm(ilPropertyFormGUI $a_form): void;

    abstract protected function importEditFormValues(ilPropertyFormGUI $a_form): void;

    abstract public function getPrintView(
        int $question_title = 1,
        bool $show_questiontext = true,
        ?int $survey_id = null,
        ?array $working_data = null
    ): string;

    protected function getPrintViewQuestionTitle(
        int $question_title = 1
    ): string {
        $title = "";
        switch ($question_title) {
            case ilObjSurvey::PRINT_HIDE_LABELS:
                $title = ilLegacyFormElementsUtil::prepareFormOutput($this->object->getTitle());
                break;

            #19448  get rid of showing only the label without title
            //case 2:
            //	$title = ilUtil::prepareFormOutput($this->object->getLabel());
            //	break;

            case ilObjSurvey::PRINT_SHOW_LABELS:
                $title = ilLegacyFormElementsUtil::prepareFormOutput($this->object->getTitle());
                if (trim($this->object->getLabel())) {
                    $title .= ' <span class="questionLabel">(' . ilLegacyFormElementsUtil::prepareFormOutput(
                        $this->object->getLabel()
                    ) . ')</span>';
                }
                break;
        }
        return $title;
    }

    public function preview(): void
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

        $panel = ilPanelGUI::getInstance();
        $panel->setBody($tpl->get());

        $this->tpl->setContent($panel->getHTML());
    }


    //
    // EXECUTION
    //

    abstract public function getWorkingForm(
        array $working_data = null,
        int $question_title = 1,
        bool $show_questiontext = true,
        string $error_message = "",
        int $survey_id = null,
        bool $compress_view = false
    ): string;

    /**
     * Creates the HTML output of the question material(s)
     */
    protected function getMaterialOutput(): string
    {
        if (count($this->object->getMaterial())) {
            $template = new ilTemplate("tpl.il_svy_qpl_material.html", true, true, "Modules/SurveyQuestionPool");
            foreach ($this->object->getMaterial() as $material) {
                $template->setCurrentBlock('material');
                switch ($material->type) {
                    case 0:
                        $href = SurveyQuestion::_getInternalLinkHref($material->internal_link, $this->request->getRefId());
                        $template->setVariable('MATERIAL_TYPE', 'internallink');
                        $template->setVariable('MATERIAL_HREF', $href);
                        break;
                }
                $template->setVariable('MATERIAL_TITLE', (strlen($material->title)) ? ilLegacyFormElementsUtil::prepareFormOutput(
                    $material->title
                ) : $this->lng->txt('material'));
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
    public function material(
        bool $checkonly = false
    ): bool {
        $rbacsystem = $this->rbacsystem;

        $ilTabs = $this->tabs;
        $ilTabs->activateTab("material");

        $href = "";
        $type = "";
        $add_html = '';
        $errors = false;

        if ($rbacsystem->checkAccess('write', $this->request->getRefId())) {
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
            $table_gui = new ilSurveyMaterialsTableGUI(
                $this,
                'material',
                $rbacsystem->checkAccess('write', $this->request->getRefId())
            );
            $data = array();
            foreach ($this->object->getMaterial() as $material) {
                switch ($material->type) {
                    case 0:
                        $href = SurveyQuestion::_getInternalLinkHref($material->internal_link, $this->request->getRefId());
                        $type = $this->lng->txt('internal_link');
                        break;
                }
                $title = (strlen($material->title)) ? ilLegacyFormElementsUtil::prepareFormOutput(
                    $material->title
                ) : $this->lng->txt('material');
                $data[] = array('href' => $href, 'title' => $title, 'type' => $type);
            }
            $table_gui->setData($data);
            $mat_html = $table_gui->getHTML();
        }

        if (!$checkonly) {
            $this->tpl->setVariable("ADM_CONTENT", $add_html . $mat_html);
        }
        return $errors;
    }

    public function deleteMaterial(): void
    {
        $mids = $this->request->getMaterialIndexes();
        if (count($mids) > 0) {
            $this->object->deleteMaterials($mids);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('materials_deleted'), true);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
        }
        $this->ctrl->redirect($this, 'material');
    }

    /**
     * Add materials to a question
     */
    public function addMaterial(): void
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;

        $ilTabs->activateTab("material");

        $ilToolbar->addButton(
            $this->lng->txt("cancel"),
            $this->ctrl->getLinkTarget($this, "material")
        );

        if ($this->edit_manager->getNewLinkType() !== "" || !$this->material(true)) {
            switch ($this->request->getNewLinkType()) {
                case "lm":
                    $this->edit_manager->setNewLinkType("lm");
                    $this->edit_manager->setSearchLinkType("lm");
                    break;
                case "glo":
                    $this->edit_manager->setNewLinkType("glo");
                    $this->edit_manager->setSearchLinkType("glo");
                    break;
                case "st":
                    $this->edit_manager->setNewLinkType("lm");
                    $this->edit_manager->setSearchLinkType("st");
                    break;
                case "pg":
                    $this->edit_manager->setNewLinkType("lm");
                    $this->edit_manager->setSearchLinkType("pg");
                    break;
            }

            $exp = new ilMaterialExplorer(
                $this,
                'addMaterial',
                $this->edit_manager->getNewLinkType()
            );
            $exp->setPathOpen($this->request->getRefId());
            if (!$exp->handleCommand()) {
                $panel = ilPanelGUI::getInstance();
                $panel->setHeading($this->lng->txt("select_object_to_link"));
                $panel->setBody($exp->getHTML());

                $this->tpl->setContent($panel->getHTML());
            }
        }
    }

    public function removeMaterial(): void
    {
        $this->object->material = array();
        $this->object->saveToDb();
        $this->editQuestion();
    }

    public function cancelExplorer(): void
    {
        $this->edit_manager->clearNewLinkType();
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, 'material');
    }

    public function addPG(): void
    {
        $this->object->addInternalLink("il__pg_" . $this->request->getLinkItemId("pg"));
        $this->edit_manager->clearNewLinkType();
        $this->edit_manager->clearSearchLinkType();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("material_added_successfully"), true);
        $this->ctrl->redirect($this, "material");
    }

    public function addST(): void
    {
        $this->object->addInternalLink("il__st_" . $this->request->getLinkItemId("st"));
        $this->edit_manager->clearNewLinkType();
        $this->edit_manager->clearSearchLinkType();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("material_added_successfully"), true);
        $this->ctrl->redirect($this, "material");
    }

    public function addGIT(): void
    {
        $this->object->addInternalLink("il__git_" . $this->request->getLinkItemId("git"));
        $this->edit_manager->clearNewLinkType();
        $this->edit_manager->clearSearchLinkType();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("material_added_successfully"), true);
        $this->ctrl->redirect($this, "material");
    }

    public function linkChilds(): void
    {
        $ilTabs = $this->tabs;

        $selectable_items = array();

        $source_id = $this->request->getLinkSourceId();

        switch ($this->edit_manager->getSearchLinkType()) {
            case "pg":
                $cont_obj_gui = new ilObjContentObjectGUI("", $source_id, true);
                $cont_obj = $cont_obj_gui->getObject();
                $pages = ilLMPageObject::getPageList($cont_obj->getId());
                foreach ($pages as $page) {
                    if ($page["type"] === $this->edit_manager->getSearchLinkType()) {
                        $selectable_items[] = array(
                            "item_type" => $page["type"]
                            ,"item_id" => $page["obj_id"]
                            ,"title" => $page["title"]
                        );
                    }
                }
                break;

            case "st":
                $cont_obj_gui = new ilObjContentObjectGUI("", $source_id, true);
                $cont_obj = $cont_obj_gui->getObject();
                // get all chapters
                $ctree = $cont_obj->getLMTree();
                $nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));
                foreach ($nodes as $node) {
                    if ($node["type"] === $this->edit_manager->getSearchLinkType()) {
                        $selectable_items[] = array(
                            "item_type" => $node["type"]
                            ,"item_id" => $node["obj_id"]
                            ,"title" => $node["title"]
                        );
                    }
                }
                break;

            case "glo":
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

        if (count($selectable_items)) {
            $ilTabs->activateTab("material");
            $this->ctrl->setParameter($this, "q_id", $this->object->getId());
            $this->ctrl->setParameter($this, "source_id", $source_id);

            $tbl = new SurveyMaterialsSourceTableGUI($this, "linkChilds", "addMaterial");
            $tbl->setData($selectable_items);
            $this->tpl->setContent($tbl->getHTML());
        } elseif ($this->edit_manager->getSearchLinkType() === "lm") {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("material_added_successfully"), true);

            $this->edit_manager->clearSearchLinkType();
            $this->edit_manager->clearNewLinkType();
            $this->ctrl->redirect($this, "material");
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("material_added_empty"), true);
            $this->ctrl->redirect($this, "addMaterial");
        }
    }


    //
    // PHRASES (see SurveyMatrixQuestionGUI)
    //

    protected function initPhrasesForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "addSelectedPhrase"));
        $form->setTitle($this->lng->txt("add_phrase"));
        // $form->setDescription($this->lng->txt("add_phrase_introduction"));

        $group = new ilRadioGroupInputGUI($this->lng->txt("phrase"), "phrases");
        $group->setRequired(true);
        $form->addItem($group);

        foreach (ilSurveyPhrases::_getAvailablePhrases() as $phrase_id => $phrase_array) {
            $categories = ilSurveyPhrases::_getCategoriesForPhrase($phrase_id);

            $opt = new ilRadioOption($phrase_array["title"], $phrase_id);
            $opt->setInfo(implode(",", $categories));
            $group->addOption($opt);

            if (($phrase_array["org_title"] ?? "") === "dp_standard_numbers") {
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
    protected function addPhrase(ilPropertyFormGUI $a_form = null): void
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

    protected function addSelectedPhrase(): void
    {
        $form = $this->initPhrasesForm();
        if ($form->checkInput()) {
            $phrase_id = $form->getInput("phrases");

            $valid = true;
            if (strcmp($this->object->getPhrase($phrase_id), "dp_standard_numbers") !== 0) {
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

                $this->tpl->setOnScreenMessage('success', $this->lng->txt('phrase_added'), true);
                $this->ctrl->redirect($this, 'editQuestion');
            }
        }

        $form->setValuesByPost();
        $this->addPhrase($form);
    }

    /**
     * Creates an output to save the current answers as a phrase
     */
    public function savePhrase(
        bool $a_reload = false
    ): void {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;

        $ilTabs->activateTab("edit_properties");

        if (!$a_reload) {
            $result = $this->saveForm();
            if ($result) {
                $this->object->saveToDb();
            }
        }

        $txt = new ilTextInputGUI($this->lng->txt("enter_phrase_title"), "phrase_title");
        $ilToolbar->addInputItem($txt, true);
        $ilToolbar->addFormButton($this->lng->txt("confirm"), "confirmSavePhrase");
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));

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
        $this->edit_manager->setPhraseData($data);

        $this->tpl->setContent($table_gui->getHTML());
    }

    /**
     * Save a new phrase to the database
     */
    public function confirmSavePhrase(): void
    {
        $title = $this->request->getPhraseTitle();

        $valid = true;
        if (!trim($title)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("qpl_savephrase_empty"));
            $valid = false;
        } elseif ($this->object->phraseExists($title)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("qpl_savephrase_exists"));
            $valid = false;
        }

        if ($valid) {
            $this->object->savePhrase($title);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("phrase_saved"), true);
            $this->ctrl->redirect($this, "editQuestion");
        }

        $this->savePhrase(true);
    }

    protected function renderStatisticsDetailsTable(
        array $a_head,
        array $a_rows,
        array $a_foot = null
    ): string {
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
