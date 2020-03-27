<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/COPage/classes/class.ilPageContentGUI.php";
include_once "./Services/COPage/classes/class.ilPCQuestion.php";

/**
* Class ilPCQuestionGUI
*
* Adapter User Interface class for assessment questions
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*
* @ingroup ServicesCOPage
*/
class ilPCQuestionGUI extends ilPageContentGUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
    {
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
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        
        // get current command
        $cmd = $ilCtrl->getCmd();
        $next_class = $ilCtrl->getNextClass($this);

        $q_type = ($_POST["q_type"] != "")
            ? $_POST["q_type"]
            : $_GET["q_type"];

        switch ($next_class) {
            default:
                //set tabs
                if ($cmd != "insert") {
                    $this->setTabs();
                } elseif ($_GET["subCmd"] != "") {
                    $cmd = $_GET["subCmd"];
                }
                
                $ret = $this->$cmd();
        }
        
        
        
        return $ret;
    }

    /**
    * Set Self Assessment Mode.
    *
    * @param	boolean	$a_selfassessmentmode	Self Assessment Mode
    */
    public function setSelfAssessmentMode($a_selfassessmentmode)
    {
        $this->selfassessmentmode = $a_selfassessmentmode;
    }

    /**
    * Get Self Assessment Mode.
    *
    * @return	boolean	Self Assessment Mode
    */
    public function getSelfAssessmentMode()
    {
        return $this->selfassessmentmode;
    }

    /**
     * Set insert tabs
     *
     * @param string $a_active active tab id
     */
    public function setInsertTabs($a_active)
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
    public function insert($a_mode = "create")
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->setInsertTabs("new_question");

        $this->displayValidationError();
        
        // get all question types (@todo: we have to check, whether they are
        // suitable for self assessment or not)
        include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
        $all_types = ilObjQuestionPool::_getSelfAssessmentQuestionTypes();
        $options = array();
        $all_types = ilUtil::sortArray($all_types, "order", "asc", true, true);

        foreach ($all_types as $k => $v) {
            $options[$v["type_tag"]] = $k;
        }
        
        // new table form (input of rows and columns)
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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
        include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
        if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()) {
            $ri = new ilRadioGroupInputGUI($this->lng->txt("tst_add_quest_cont_edit_mode"), "add_quest_cont_edit_mode");
            
            $ri->addOption(new ilRadioOption(
                $this->lng->txt('tst_add_quest_cont_edit_mode_default'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT
            ));

            $ri->addOption(new ilRadioOption(
                $this->lng->txt('tst_add_quest_cont_edit_mode_page_object'),
                assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT
            ));
            
            $ri->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT);

            $this->form_gui->addItem($ri, true);
        } else {
            $hi = new ilHiddenInputGUI("question_content_editing_type");
            $hi->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT);
            $this->form_gui->addItem($hi, true);
        }

        
        // Select Question Pool
        /*
                include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
                $qpools = ilObjQuestionPool::_getAvailableQuestionpools(false, false, false, true, false, "write");

                if (count($qpools) > 0)
                {
                    $pool_options = array();
                    foreach ($qpools as $key => $value)
                    {
                        $pool_options[$key] = $value["title"];
                    }
                    $pool_input = new ilSelectInputGUI($lng->txt("cont_question_pool"), "qpool_ref_id");
                    $pool_input->setOptions($pool_options);
                    $pool_input->setRequired(true);
                    $this->form_gui->addItem($pool_input);
                }
                else
                {
                    $pool_input = new ilTextInputGUI($lng->txt("cont_question_pool"), "qpool_title");
                    $pool_input->setRequired(true);
                    $this->form_gui->addItem($pool_input);
                }
        */
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
    public function create()
    {
        global	$lng, $ilCtrl, $ilTabs;

        $ilTabs->setTabActive('question');
        
        $this->content_obj = new ilPCQuestion($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id);
        
        $this->updated = $this->pg_obj->update();

        if ($this->updated) {
            // create question pool, if necessary
            /*			if ($_POST["qpool_ref_id"] <= 0)
                        {
                            $pool_ref_id = $this->createQuestionPool($_POST["qpool_title"]);
                        }
                        else
                        {
                            $pool_ref_id = $_POST["qpool_ref_id"];
                        }*/
            
            $this->pg_obj->stripHierIDs();
            $this->pg_obj->addHierIDs();
            $hier_id = $this->content_obj->lookupHierId();
            $ilCtrl->setParameter($this, "q_type", $_POST["q_type"]);
            $ilCtrl->setParameter($this, "add_quest_cont_edit_mode", $_POST["add_quest_cont_edit_mode"]);
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
    public function setNewQuestionId($a_par)
    {
        if ($a_par["new_id"] > 0) {
            $this->content_obj->setQuestionReference("il__qst_" . $a_par["new_id"]);
            $this->pg_obj->update();
        }
    }
    
    /**
    * edit question
    */
    public function edit()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        
        $ilTabs->setTabActive('question');
        
        
        if ($this->getSelfAssessmentMode()) {		// behaviour in content pages, e.g. scorm
            $q_ref = $this->content_obj->getQuestionReference();
            
            if ($q_ref != "") {
                $inst_id = ilInternalLink::_extractInstOfTarget($q_ref);
                if (!($inst_id > 0)) {
                    $q_id = ilInternalLink::_extractObjIdOfTarget($q_ref);
                }
            }
            
            $q_type = ($_POST["q_type"] != "")
                ? $_POST["q_type"]
                : $_GET["q_type"];
            $ilCtrl->setParameter($this, "q_type", $q_type);
            
            if ($q_id == "" && $q_type == "") {
                return $this->insert("edit_empty");
            }
                        
            include_once("./Modules/TestQuestionPool/classes/class.ilQuestionEditGUI.php");
            include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
            include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php");
            
            /*			$ilCtrl->setCmdClass("ilquestioneditgui");
                        $ilCtrl->setCmd("editQuestion");
                        $edit_gui = new ilQuestionEditGUI();*/
            
            // create question first-hand (needed for uploads)
            if ($q_id < 1 && $q_type) {
                include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
                $q_gui = assQuestionGUI::_getQuestionGUI($q_type);

                // feedback editing mode
                include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
                if (ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled()
                    && $_REQUEST['add_quest_cont_edit_mode'] != "") {
                    $addContEditMode = $_GET['add_quest_cont_edit_mode'];
                } else {
                    $addContEditMode = assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT;
                }
                $q_gui->object->setAdditionalContentEditingMode($addContEditMode);
                
                //set default tries
                $q_gui->object->setDefaultNrOfTries(ilObjSAHSLearningModule::_getTries($this->scormlmid));
                $q_id = $q_gui->object->createNewQuestion(true);
                $this->content_obj->setQuestionReference("il__qst_" . $q_id);
                $this->pg_obj->update();
                unset($q_gui);
            }
            $ilCtrl->setParameterByClass("ilQuestionEditGUI", "q_id", $q_id);
            $ilCtrl->redirectByClass(array(get_class($this->pg_obj) . "GUI", "ilQuestionEditGUI"), "editQuestion");
            
            /*			$edit_gui->setPoolObjId(0);
                        $edit_gui->setQuestionId($q_id);
                        $edit_gui->setQuestionType($q_type);
                        $edit_gui->setSelfAssessmentEditingMode(true);
                        $edit_gui->setPageConfig($this->getPageConfig());
                        $ret = $ilCtrl->forwardCommand($edit_gui);
                        $this->tpl->setContent($ret);*/
            return $ret;
        } else {	// behaviour in question pool
            require_once("./Modules/TestQuestionPool/classes/class.assQuestionGUI.php");
            $q_gui = assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
            $this->ctrl->redirectByClass(array("ilobjquestionpoolgui", get_class($q_gui)), "editQuestion");
        }
    }

    public function feedback()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        
        include_once("./Modules/TestQuestionPool/classes/class.ilQuestionEditGUI.php");
        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        
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
    *
    * Creates a new questionpool and returns the reference id
    *
    * @return integer Reference id of the newly created questionpool
    * @access	public
    */
    public function createQuestionPool($name = "Dummy")
    {
        $tree = $this->tree;
        $parent_ref = $tree->getParentId($_GET["ref_id"]);
        include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
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
    
    /**
    * Set tabs
    */
    public function setTabs()
    {
        if ($this->getSelfAssessmentMode()) {
            return;
        }
        
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        
        if ($this->content_obj != "") {
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
                require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionFeedbackEditingGUI.php';
                $tabCommands = assQuestionGUI::getCommandsFromClassConstants('ilAssQuestionFeedbackEditingGUI');
                $tabLink = ilUtil::appendUrlParameterString(
                    $ilCtrl->getLinkTargetByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW),
                    "q_id=" . (int) $q_id
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
    public function insertFromPool()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilToolbar = $this->toolbar;
        //var_dump($_SESSION["cont_qst_pool"]);
        if ($_SESSION["cont_qst_pool"] != "" &&
            $ilAccess->checkAccess("write", "", $_SESSION["cont_qst_pool"])
            && ilObject::_lookupType(ilObject::_lookupObjId($_SESSION["cont_qst_pool"])) == "qpl") {
            $this->listPoolQuestions();
        } else {
            $this->poolSelection();
        }
    }

    /**
     * Pool selection
     *
     * @param
     * @return
     */
    public function poolSelection()
    {
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $this->setInsertTabs("copy_question");

        include_once "./Services/COPage/classes/class.ilPoolSelectorGUI.php";

        $ilCtrl->setParameter($this, "subCmd", "poolSelection");
        $exp = new ilPoolSelectorGUI($this, "insert");

        // filter
        $exp->setTypeWhiteList(array("root", "cat", "grp", "fold", "crs", "qpl"));
        $exp->setClickableTypes(array('qpl'));

        if (!$exp->handleCommand()) {
            $tpl->setContent($exp->getHTML());
        }
    }
    
    /**
     * Select concrete question pool
     */
    public function selectPool()
    {
        $ilCtrl = $this->ctrl;
        
        $_SESSION["cont_qst_pool"] = $_GET["pool_ref_id"];
        $ilCtrl->setParameter($this, "subCmd", "insertFromPool");
        $ilCtrl->redirect($this, "insert");
    }

    /**
     * List questions of pool
     *
     * @param
     * @return
     */
    public function listPoolQuestions()
    {
        $ilToolbar = $this->toolbar;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        ilUtil::sendInfo($lng->txt("cont_cp_question_diff_formats_info"));
        
        $ilCtrl->setParameter($this, "subCmd", "poolSelection");
        $ilToolbar->addButton(
            $lng->txt("cont_select_other_qpool"),
            $ilCtrl->getLinkTarget($this, "insert")
        );
        $ilCtrl->setParameter($this, "subCmd", "");

        $this->setInsertTabs("copy_question");

        include_once "./Services/COPage/classes/class.ilCopySelfAssQuestionTableGUI.php";
        
        $ilCtrl->setParameter($this, "subCmd", "listPoolQuestions");
        $table_gui = new ilCopySelfAssQuestionTableGUI(
            $this,
            'insert',
            $_SESSION["cont_qst_pool"]
        );

        $tpl->setContent($table_gui->getHTML());
    }
    
    /**
     * Copy question into page
     *
     * @param
     * @return
     */
    public function copyQuestion()
    {
        $ilCtrl = $this->ctrl;
        
        $this->content_obj = new ilPCQuestion($this->getPage());
        $this->content_obj->create($this->pg_obj, $_GET["hier_id"]);
        
        $this->content_obj->copyPoolQuestionIntoPage(
            (int) $_GET["q_id"],
            $_GET["hier_id"]
        );
        
        $this->updated = $this->pg_obj->update();
        
        $ilCtrl->returnToParent($this);
    }
}
