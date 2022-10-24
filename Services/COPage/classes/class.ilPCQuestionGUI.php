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

/**
 * Class ilPCQuestionGUI
 * Adapter User Interface class for assessment questions
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCQuestionGUI extends ilPageContentGUI
{
    protected ilPropertyFormGUI $form_gui;
    protected int $scormlmid;
    protected bool $selfassessmentmode;
    protected ilAccessHandler $access;
    protected ilTabsGUI $tabs;
    protected ilObjUser $user;
    protected ilTree $tree;
    protected ilToolbarGUI $toolbar;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->tree = $DIC->repositoryTree();
        $this->toolbar = $DIC->toolbar();
        $ilCtrl = $DIC->ctrl();
        $this->scormlmid = $a_pg_obj->parent_id;
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
        $ilCtrl->saveParameter($this, array("qpool_ref_id"));
    }

    /**
     * @return mixed
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        // get current command
        $cmd = $ilCtrl->getCmd();
        $next_class = $ilCtrl->getNextClass($this);

        switch ($next_class) {
            default:
                //set tabs
                if ($cmd != "insert") {
                    $this->setTabs();
                } elseif ($this->sub_command != "") {
                    $cmd = $this->sub_command;
                }

                $ret = $this->$cmd();
        }

        return $ret;
    }

    public function setSelfAssessmentMode(bool $a_selfassessmentmode): void
    {
        $this->selfassessmentmode = $a_selfassessmentmode;
    }

    public function getSelfAssessmentMode(): bool
    {
        return $this->selfassessmentmode;
    }

    public function setInsertTabs(bool $a_active): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        // new question
        $ilTabs->addSubTab(
            "new_question",
            $lng->txt("cont_new_question"),
            $ilCtrl->getLinkTarget($this, "insert")
        );

        // copy from pool
        $ilCtrl->setParameter($this, "subCmd", "insertFromPool");
        $ilTabs->addSubTab(
            "copy_question",
            $lng->txt("cont_copy_question_from_pool"),
            $ilCtrl->getLinkTarget($this, "insert")
        );

        $ilTabs->activateSubTab($a_active);

        $ilCtrl->setParameter($this, "subCmd", "");
    }

    /**
     * Insert new question form
     */
    public function insert(string $a_mode = "create"): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->setInsertTabs("new_question");

        $this->displayValidationError();

        // get all question types (@todo: we have to check, whether they are
        // suitable for self assessment or not)
        $all_types = ilObjQuestionPool::_getSelfAssessmentQuestionTypes();
        $options = array();
        $all_types = ilArrayUtil::sortArray($all_types, "order", "asc", true, true);

        foreach ($all_types as $k => $v) {
            $options[$v["type_tag"]] = $k;
        }

        // new table form (input of rows and columns)
        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction($ilCtrl->getFormAction($this));
        $this->form_gui->setTitle($lng->txt("cont_ed_insert_pcqst"));

        // Select Question Type
        $qtype_input = new ilSelectInputGUI($lng->txt("cont_question_type"), "q_type");
        $qtype_input->setOptions($options);
        $qtype_input->setRequired(true);
        $this->form_gui->addItem($qtype_input);

        // additional content editor
        // assessment
        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $ri = new ilRadioGroupInputGUI($this->lng->txt("tst_add_quest_cont_edit_mode"), "add_quest_cont_edit_mode");

            $option_rte = new ilRadioOption(
                $this->lng->txt('tst_add_quest_cont_edit_mode_RTE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE
            );
            $option_rte->setInfo($this->lng->txt('tst_add_quest_cont_edit_mode_RTE_info'));
            $ri->addOption($option_rte);

            $option_ipe = new ilRadioOption(
                $this->lng->txt('tst_add_quest_cont_edit_mode_IPE'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE
            );
            $option_ipe->setInfo($this->lng->txt('tst_add_quest_cont_edit_mode_IPE_info'));
            $ri->addOption($option_ipe);

            $ri->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE);

            $this->form_gui->addItem($ri);
        } else {
            $hi = new ilHiddenInputGUI("question_content_editing_type");
            $hi->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE);
            $this->form_gui->addItem($hi);
        }

        if ($a_mode == "edit_empty") {
            $this->form_gui->addCommandButton("edit", $lng->txt("save"));
        } else {
            $this->form_gui->addCommandButton("create_pcqst", $lng->txt("save"));
            $this->form_gui->addCommandButton("cancelCreate", $lng->txt("cancel"));
        }

        $this->tpl->setContent($this->form_gui->getHTML());
    }


    /**
     * Create new question
     */
    public function create(): void
    {
        global	$ilCtrl, $ilTabs;

        $ilTabs->setTabActive('question');

        $this->content_obj = new ilPCQuestion($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id);

        $this->updated = $this->pg_obj->update();

        if ($this->updated) {
            $this->pg_obj->stripHierIDs();
            $this->pg_obj->addHierIDs();
            $ilCtrl->setParameter(
                $this,
                "q_type",
                $this->request->getString("q_type")
            );
            $ilCtrl->setParameter(
                $this,
                "add_quest_cont_edit_mode",
                $this->request->getString("add_quest_cont_edit_mode")
            );
            //			$ilCtrl->setParameter($this, "qpool_ref_id", $pool_ref_id);
            //$ilCtrl->setParameter($this, "hier_id", $hier_id);
            $ilCtrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
            $ilCtrl->setParameter($this, "pc_id", $this->content_obj->readPCId());

            $ilCtrl->redirect($this, "edit");
        }

        $this->insert();
    }

    /**
     * Set new question id
     */
    public function setNewQuestionId(array $a_par): void
    {
        if ($a_par["new_id"] > 0) {
            $this->content_obj->setQuestionReference("il__qst_" . $a_par["new_id"]);
            $this->pg_obj->update();
        }
    }

    /**
     * edit question
     */
    public function edit(): void
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $q_id = "";

        $ilTabs->setTabActive('question');

        if ($this->getSelfAssessmentMode()) {		// behaviour in content pages, e.g. scorm
            $q_ref = $this->content_obj->getQuestionReference();

            if ($q_ref != "") {
                $inst_id = ilInternalLink::_extractInstOfTarget($q_ref);
                if (!($inst_id > 0)) {
                    $q_id = ilInternalLink::_extractObjIdOfTarget($q_ref);
                }
            }

            $q_type = $this->request->getString("q_type");
            $ilCtrl->setParameter($this, "q_type", $q_type);

            if ($q_id == "" && $q_type == "") {
                $this->insert("edit_empty");
                return;
            }

            // create question first-hand (needed for uploads)
            if ($q_id < 1 && $q_type) {
                $q_gui = assQuestionGUI::_getQuestionGUI($q_type);

                // feedback editing mode
                $add_quest_cont_edit_mode = $this->request->getString("add_quest_cont_edit_mode");
                if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()
                    && $add_quest_cont_edit_mode != "") {
                    $addContEditMode = $add_quest_cont_edit_mode;
                } else {
                    $addContEditMode = assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE;
                }
                $q_gui->object->setAdditionalContentEditingMode($addContEditMode);

                //set default tries
                $q_gui->object->setObjId(0);
                $q_id = $q_gui->object->createNewQuestion(true);
                $this->content_obj->setQuestionReference("il__qst_" . $q_id);
                $this->pg_obj->update();
                unset($q_gui);
            }
            $ilCtrl->setParameterByClass("ilQuestionEditGUI", "q_id", $q_id);
            $ilCtrl->redirectByClass(array(get_class($this->pg_obj) . "GUI", "ilQuestionEditGUI"), "editQuestion");
        } else {	// behaviour in question pool
            $q_gui = assQuestionGUI::_getQuestionGUI(
                "",
                $this->request->getInt("q_id")
            );
            $this->ctrl->redirectByClass(array("ilobjquestionpoolgui", get_class($q_gui)), "editQuestion");
        }
    }

    /**
     * @return mixed
     * @throws ilCtrlException
     */
    public function feedback()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $q_id = "";

        $ilTabs->setTabActive('feedback');

        $q_ref = $this->content_obj->getQuestionReference();

        if ($q_ref != "") {
            $inst_id = ilInternalLink::_extractInstOfTarget($q_ref);
            if (!($inst_id > 0)) {
                $q_id = ilInternalLink::_extractObjIdOfTarget($q_ref);
            }
        }

        $ilCtrl->setCmdClass("ilquestioneditgui");
        $ilCtrl->setCmd("feedback");
        $edit_gui = new ilQuestionEditGUI();
        if ($q_id > 0) {
            $edit_gui->setQuestionId($q_id);
        }
        //		$edit_gui->setQuestionType("assSingleChoice");
        $edit_gui->setSelfAssessmentEditingMode(true);
        $edit_gui->setPageConfig($this->getPageConfig());
        $ret = $ilCtrl->forwardCommand($edit_gui);
        $this->tpl->setContent($ret);
        return $ret;
    }

    /**
     * Creates a new questionpool and returns the reference id
     * @return int Reference id of the newly created questionpool
     */
    public function createQuestionPool(string $name = "Dummy"): int
    {
        $tree = $this->tree;
        $parent_ref = $tree->getParentId($this->requested_ref_id);
        $qpl = new ilObjQuestionPool();
        $qpl->setType("qpl");
        $qpl->setTitle($name);
        $qpl->setDescription("");
        $qpl->create();
        $qpl->createReference();
        $qpl->putInTree($parent_ref);
        $qpl->setPermissions($parent_ref);
        $qpl->setOnline(1); // must be online to be available
        $qpl->saveToDb();
        return $qpl->getRefId();
    }

    public function setTabs(): void
    {
        $q_ref = "";
        $q_id = 0;

        if ($this->getSelfAssessmentMode()) {
            return;
        }

        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        if (!is_null($this->content_obj)) {
            $q_ref = $this->content_obj->getQuestionReference();
        }

        if ($q_ref != "") {
            $inst_id = ilInternalLink::_extractInstOfTarget($q_ref);
            if (!($inst_id > 0)) {
                $q_id = ilInternalLink::_extractObjIdOfTarget($q_ref);
            }
        }

        $ilTabs->addTarget(
            "question",
            $ilCtrl->getLinkTarget($this, "edit"),
            array("editQuestion", "save", "cancel", "addSuggestedSolution",
                "cancelExplorer", "linkChilds", "removeSuggestedSolution",
                "addPair", "addTerm", "delete", "deleteTerms", "editMode", "upload",
                "saveEdit","uploadingImage", "uploadingImagemap", "addArea",
                "deletearea", "saveShape", "back", "saveEdit", "changeGapType","createGaps","addItem","addYesNo", "addTrueFalse",
                "toggleGraphicalAnswers", "setMediaMode"),
            ""
        );

        if ($q_id > 0) {
            if (assQuestion::_getQuestionType($q_id) != "assTextQuestion") {
                $tabCommands = assQuestionGUI::getCommandsFromClassConstants('ilAssQuestionFeedbackEditingGUI');
                $tabLink = ilUtil::appendUrlParameterString(
                    $ilCtrl->getLinkTargetByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW),
                    "q_id=" . $q_id
                );
                $ilTabs->addTarget('feedback', $tabLink, $tabCommands, $ilCtrl->getCmdClass(), '');
            }
        }
    }

    ////
    //// Get question from pool
    ////

    /**
     * Insert question from ppol
     */
    public function insertFromPool(): void
    {
        $ilAccess = $this->access;
        if ($this->edit_repo->getQuestionPool() > 0 &&
            $ilAccess->checkAccess("write", "", $this->edit_repo->getQuestionPool())
            && ilObject::_lookupType(ilObject::_lookupObjId($this->edit_repo->getQuestionPool())) == "qpl") {
            $this->listPoolQuestions();
        } else {
            $this->poolSelection();
        }
    }

    public function poolSelection(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $this->setInsertTabs("copy_question");

        $ilCtrl->setParameter($this, "subCmd", "poolSelection");
        $exp = new ilPoolSelectorGUI($this, "insert");

        // filter
        $exp->setTypeWhiteList(array("root", "cat", "grp", "fold", "crs", "qpl"));
        $exp->setClickableTypes(array('qpl'));

        if (!$exp->handleCommand()) {
            $tpl->setContent($exp->getHTML());
        }
    }

    public function selectPool(): void
    {
        $ilCtrl = $this->ctrl;

        $this->edit_repo->setQuestionPool($this->request->getInt("pool_ref_id"));
        $ilCtrl->setParameter($this, "subCmd", "insertFromPool");
        $ilCtrl->redirect($this, "insert");
    }

    public function listPoolQuestions(): void
    {
        $ilToolbar = $this->toolbar;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->tpl->setOnScreenMessage('info', $lng->txt("cont_cp_question_diff_formats_info"));

        $ilCtrl->setParameter($this, "subCmd", "poolSelection");
        $ilToolbar->addButton(
            $lng->txt("cont_select_other_qpool"),
            $ilCtrl->getLinkTarget($this, "insert")
        );
        $ilCtrl->setParameter($this, "subCmd", "");

        $this->setInsertTabs("copy_question");

        $ilCtrl->setParameter($this, "subCmd", "listPoolQuestions");
        $table_gui = new ilCopySelfAssQuestionTableGUI(
            $this,
            'insert',
            $this->edit_repo->getQuestionPool()
        );

        $tpl->setContent($table_gui->getHTML());
    }

    public function copyQuestion(): void
    {
        $ilCtrl = $this->ctrl;

        $this->content_obj = new ilPCQuestion($this->getPage());
        $this->content_obj->create(
            $this->pg_obj,
            $this->request->getHierId()
        );

        $this->content_obj->copyPoolQuestionIntoPage(
            $this->request->getInt("q_id"),
            $this->request->getHierId()
        );

        $this->updated = $this->pg_obj->update();

        $ilCtrl->returnToParent($this);
    }
}
