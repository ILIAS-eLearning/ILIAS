<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Survey evaluation graphical output
*
* The ilSurveyEvaluationGUI class creates the evaluation output for the ilObjSurveyGUI
* class. This saves some heap space because the ilObjSurveyGUI class will be
* smaller.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesSurvey
*/
class ilSurveyEvaluationGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    const TYPE_XLS = "excel";
    const TYPE_SPSS = "csv";
    
    const EXCEL_SUBTITLE = "DDDDDD";
    
    public $object;
    public $lng;
    public $tpl;
    public $ctrl;
    public $appr_id = null;
    
    /**
     * ilSurveyEvaluationGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the ilSurveyEvaluationGUI object.
     *
     * @param object $a_object Associated ilObjSurvey class
     * @access public
     */
    public function __construct($a_object)
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->tree = $DIC->repositoryTree();
        $this->toolbar = $DIC->toolbar();
        $this->ui = $DIC->ui();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->object = $a_object;
        $this->log = ilLoggerFactory::getLogger("svy");
        $this->array_panels = array();

        if ($this->object->get360Mode() || $this->object->getMode() == ilObjSurvey::MODE_SELF_EVAL) {
            $this->determineAppraiseeId();
        }
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        include_once("./Services/Skill/classes/class.ilSkillManagementSettings.php");
        $skmg_set = new ilSkillManagementSettings();
        if ($this->object->getSkillService() && $skmg_set->isActivated()) {
            $cmd = $this->ctrl->getCmd("competenceEval");
        } else {
            $cmd = $this->ctrl->getCmd("evaluation");
        }
        
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->getCommand($cmd);
        switch ($next_class) {
            default:
                $this->setEvalSubTabs();
                $ret = &$this->$cmd();
                break;
        }
        return $ret;
    }

    public function getCommand($cmd)
    {
        return $cmd;
    }

    /**
    * Set the tabs for the evaluation output
    *
    * @access private
    */
    public function setEvalSubtabs()
    {
        $ilTabs = $this->tabs;
        $ilAccess = $this->access;

        include_once("./Services/Skill/classes/class.ilSkillManagementSettings.php");
        $skmg_set = new ilSkillManagementSettings();
        if ($this->object->getSkillService() && $skmg_set->isActivated()) {
            $ilTabs->addSubTabTarget(
                "svy_eval_competences",
                $this->ctrl->getLinkTarget($this, "competenceEval"),
                array("competenceEval")
            );
        }

        $ilTabs->addSubTabTarget(
            "svy_eval_cumulated",
            $this->ctrl->getLinkTarget($this, "evaluation"),
            array("evaluation", "checkEvaluationAccess")
        );

        $ilTabs->addSubTabTarget(
            "svy_eval_detail",
            $this->ctrl->getLinkTarget($this, "evaluationdetails"),
            array("evaluationdetails")
        );
        
        if ($this->hasResultsAccess()) {
            $ilTabs->addSubTabTarget(
                "svy_eval_user",
                $this->ctrl->getLinkTarget($this, "evaluationuser"),
                array("evaluationuser")
            );
        }
    }

    
    /**
     * Set appraisee id
     *
     * @param int $a_val appraisee id
     */
    public function setAppraiseeId($a_val)
    {
        $this->appr_id = $a_val;
    }
    
    /**
     * Get appraisee id
     *
     * @return int appraisee id
     */
    public function getAppraiseeId()
    {
        return $this->appr_id;
    }
    
    /**
     * Determine appraisee id
     */
    public function determineAppraiseeId()
    {
        $ilUser = $this->user;
        $rbacsystem = $this->rbacsystem;
        
        $appr_id = "";
        
        // always start with current user
        if ($_REQUEST["appr_id"] == "") {
            $req_appr_id = $ilUser->getId();
        } else {
            $req_appr_id = (int) $_REQUEST["appr_id"];
        }
        
        // write access? allow selection
        if ($req_appr_id > 0 && $this->object->get360Mode()) {
            $all_appr = ($this->object->get360Results() == ilObjSurvey::RESULTS_360_ALL);
            
            $valid = array();
            foreach ($this->object->getAppraiseesData() as $item) {
                if ($item["closed"] &&
                    ($item["user_id"] == $ilUser->getId() ||
                    $rbacsystem->checkAccess("write", $this->object->getRefId()) ||
                    $all_appr)) {
                    $valid[] = $item["user_id"];
                }
            }
            if (in_array($req_appr_id, $valid)) {
                $appr_id = $req_appr_id;
            } else {
                // current selection / user is not valid, use 1st valid instead
                $appr_id = array_shift($valid);
            }
        } else { // SVY SELF EVALUATION MODE
            $appr_id = $req_appr_id;
        }
        
        $this->ctrl->setParameter($this, "appr_id", $appr_id);
        $this->setAppraiseeId($appr_id);
    }
    
    
    /**
    * Show the detailed evaluation
    *
    * Show the detailed evaluation
    *
    * @access private
    */
    public function checkAnonymizedEvaluationAccess()
    {
        $ilUser = $this->user;
        
        if ($this->object->getAnonymize() == 1 &&
            $_SESSION["anon_evaluation_access"] == $_GET["ref_id"]) {
            return true;
        }
        
        include_once "Modules/Survey/classes/class.ilObjSurveyAccess.php";
        if (ilObjSurveyAccess::_hasEvaluationAccess(ilObject::_lookupObjId($_GET["ref_id"]), $ilUser->getId())) {
            if ($this->object->getAnonymize() == 1) {
                $_SESSION["anon_evaluation_access"] = $_GET["ref_id"];
            }
            return true;
        }
        
        if ($this->object->getAnonymize() == 1) {
            // autocode
            $surveycode = $this->object->getUserAccessCode($ilUser->getId());
            if ($this->object->isAnonymizedParticipant($surveycode)) {
                $_SESSION["anon_evaluation_access"] = $_GET["ref_id"];
                return true;
            }
            
            /* try to find code for current (registered) user from existing run
            if($this->object->findCodeForUser($ilUser->getId()))
            {
                $_SESSION["anon_evaluation_access"] = $_GET["ref_id"];
                return true;
            }
            */
            
            // code needed
            $this->tpl->setVariable("TABS", "");
            $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation_checkaccess.html", "Modules/Survey");
            $this->tpl->setCurrentBlock("adm_content");
            $this->tpl->setVariable("AUTHENTICATION_NEEDED", $this->lng->txt("svy_check_evaluation_authentication_needed"));
            $this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "checkEvaluationAccess"));
            $this->tpl->setVariable("EVALUATION_CHECKACCESS_INTRODUCTION", $this->lng->txt("svy_check_evaluation_access_introduction"));
            $this->tpl->setVariable("VALUE_CHECK", $this->lng->txt("ok"));
            $this->tpl->setVariable("VALUE_CANCEL", $this->lng->txt("cancel"));
            $this->tpl->setVariable("TEXT_SURVEY_CODE", $this->lng->txt("survey_code"));
            $this->tpl->parseCurrentBlock();
        }
        
        $_SESSION["anon_evaluation_access"] = null;
        return false;
    }

    /**
    * Checks the evaluation access after entering the survey access code
    *
    * Checks the evaluation access after entering the survey access code
    *
    * @access private
    */
    public function checkEvaluationAccess()
    {
        $surveycode = $_POST["surveycode"];
        if ($this->object->isAnonymizedParticipant($surveycode)) {
            $_SESSION["anon_evaluation_access"] = $_GET["ref_id"];
            $this->evaluation();
        } else {
            ilUtil::sendFailure($this->lng->txt("svy_check_evaluation_wrong_key", true));
            $this->cancelEvaluationAccess();
        }
    }
    
    /**
    * Cancels the input of the survey access code for evaluation access
    *
    * Cancels the input of the survey access code for evaluation access
    *
    * @access private
    */
    public function cancelEvaluationAccess()
    {
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;
        $path = $tree->getPathFull($this->object->getRefID());
        $ilCtrl->setParameterByClass(
            "ilrepositorygui",
            "ref_id",
            $path[count($path) - 2]["child"]
        );
        $ilCtrl->redirectByClass("ilrepositorygui", "frameset");
    }
    
    /**
    * Show the detailed evaluation
    *
    * Show the detailed evaluation
    *
    * @access private
    */
    public function evaluationdetails()
    {
        $this->evaluation(1);
    }
    
    public function exportCumulatedResults($details = 0)
    {
        $finished_ids = null;
        if ($this->object->get360Mode()) {
            $appr_id = $_REQUEST["appr_id"];
            if (!$appr_id) {
                $this->ctrl->redirect($this, $details ? "evaluationdetails" : "evaluation");
            }
            $finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);
            if (!sizeof($finished_ids)) {
                $finished_ids = array(-1);
            }
        }
        
        // titles
        $title_row = array();
        $do_title = $do_label = true;
        switch ($_POST['export_label']) {
            case 'label_only':
                $title_row[] = $this->lng->txt("label");
                $do_title = false;
                break;

            case 'title_only':
                $title_row[] = $this->lng->txt("title");
                $do_label = false;
                break;

            default:
                $title_row[] = $this->lng->txt("title");
                $title_row[] = $this->lng->txt("label");
                break;
        }
        $title_row[] = $this->lng->txt("question");
        $title_row[] = $this->lng->txt("question_type");
        $title_row[] = $this->lng->txt("users_answered");
        $title_row[] = $this->lng->txt("users_skipped");
        $title_row[] = $this->lng->txt("mode");
        $title_row[] = $this->lng->txt("mode_text");
        $title_row[] = $this->lng->txt("mode_nr_of_selections");
        $title_row[] = $this->lng->txt("median");
        $title_row[] = $this->lng->txt("arithmetic_mean");
        
        // creating container
        switch ($_POST["export_format"]) {
            case self::TYPE_XLS:
                include_once "Services/Excel/classes/class.ilExcel.php";
                $excel = new ilExcel();
                $excel->addSheet($this->lng->txt("svy_eval_cumulated"));
                $excel->setCellArray(array($title_row), "A1");
                $excel->setBold("A1:" . $excel->getColumnCoord(sizeof($title_row) - 1) . "1");
                break;
            
            case self::TYPE_SPSS:
                $csvfile = array($title_row);
                break;
        }
                
        
        // parse answer data in evaluation results
        $ov_row = 2;
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
        foreach ($this->object->getSurveyQuestions() as $qdata) {
            $q_eval = SurveyQuestion::_instanciateQuestionEvaluation($qdata["question_id"], $finished_ids);
            $q_res = $q_eval->getResults();
            $ov_rows = $q_eval->exportResults($q_res, $do_title, $do_label);
            
            switch ($_POST["export_format"]) {
                case self::TYPE_XLS:
                    $excel->setActiveSheet(0);
                    foreach ($ov_rows as $row) {
                        foreach ($row as $col => $value) {
                            $excel->setCell($ov_row, $col, $value);
                        }
                        $ov_row++;
                    }
                    break;
                
                case self::TYPE_SPSS:
                    foreach ($ov_rows as $row) {
                        $csvfile[] = $row;
                    }
                    break;
            }
            
            if ($details) {
                switch ($_POST["export_format"]) {
                    case self::TYPE_XLS:
                        $this->exportResultsDetailsExcel($excel, $q_eval, $q_res, $do_title, $do_label);
                        break;
                }
            }
        }
        
        // #11179
        $type = !$details
            ? $this->lng->txt("svy_eval_cumulated")
            : $this->lng->txt("svy_eval_detail");
            
        $surveyname = $this->object->getTitle() . " " . $type . " " . date("Y-m-d");
        $surveyname = preg_replace("/\s/", "_", trim($surveyname));
        $surveyname = ilUtil::getASCIIFilename($surveyname);
        
        // send to client
        switch ($_POST["export_format"]) {
            case self::TYPE_XLS:
                $excel->sendToClient($surveyname);
                break;
            
            case self::TYPE_SPSS:
                $csv = "";
                $separator = ";";
                foreach ($csvfile as $csvrow) {
                    $csvrow = $this->processCSVRow($csvrow, true, $separator);
                    $csv .= join($csvrow, $separator) . "\n";
                }
                ilUtil::deliverData($csv, $surveyname . ".csv");
                exit();
                break;
        }
    }
    
    /**
     * Export details (excel only)
     *
     * @param ilExcel $a_excel
     * @param SurveyQuestionEvaluation $a_eval
     * @param ilSurveyEvaluationResults|array $a_results
     * @param bool $a_do_title
     * @param bool|array $a_do_label
     */
    protected function exportResultsDetailsExcel(ilExcel $a_excel, SurveyQuestionEvaluation $a_eval, $a_results, $a_do_title, $a_do_label)
    {
        $question_res = $a_results;
        $matrix = false;
        if (is_array($question_res)) {
            $question_res = $question_res[0][1];
            $matrix = true;
        }
        $question = $question_res->getQuestion();
        
        $a_excel->addSheet($question->getTitle());
        
        
        // question "overview"
        
        $kv = array();
        
        if ($a_do_title) {
            $kv[$this->lng->txt("title")] = $question->getTitle();
        }
        if ($a_do_label) {
            $kv[$this->lng->txt("label")] = $question->label;
        }

        // question
        $kv[$this->lng->txt("question")] = $question->getQuestiontext();

        // question type
        $kv[$this->lng->txt("question_type")] = SurveyQuestion::_getQuestionTypeName($question->getQuestionType());
        
        // :TODO: present subtypes (hrz/vrt, mc/sc mtx, metric scale)?

        // answered and skipped users
        $kv[$this->lng->txt("users_answered")] = (int) $question_res->getUsersAnswered();
        $kv[$this->lng->txt("users_skipped")] = (int) $question_res->getUsersSkipped();		// #0021671
                
        $excel_row = 1;
        
        foreach ($kv as $key => $value) {
            $a_excel->setCell($excel_row, 0, $key);
            $a_excel->setCell($excel_row++, 1, $value);
        }
        
        if (!$matrix) {
            $this->parseResultsToExcel(
                $a_excel,
                $question_res,
                $excel_row,
                $a_eval->getExportGrid($a_results),
                $a_eval->getTextAnswers($a_results)
            );
        } else {
            // question
            $this->parseResultsToExcel(
                $a_excel,
                $question_res,
                $excel_row,
                null,
                null,
                false
            );
                        
            $texts = $a_eval->getTextAnswers($a_results);
            
            // "rows"
            foreach ($a_results as $row_results) {
                $row_title = $row_results[0];
                
                $a_excel->setCell($excel_row, 0, $this->lng->txt("row"));
                $a_excel->setCell($excel_row++, 1, $row_title);
                
                $this->parseResultsToExcel(
                    $a_excel,
                    $row_results[1],
                    $excel_row,
                    $a_eval->getExportGrid($row_results[1]),
                    is_array($texts[$row_title])
                        ? array("" => $texts[$row_title])
                        : null
                );
            }
        }

        // matrix question: overview	#21438
        if ($matrix) {
            $a_excel->setCell($excel_row++, 0, $this->lng->txt("overview"));

            // title row with variables
            $counter = 0;
            $cats = $question->getColumns();
            foreach ($cats->getCategories() as $cat) {
                $a_excel->setColors($a_excel->getCoordByColumnAndRow(1 + $counter, $excel_row), ilSurveyEvaluationGUI::EXCEL_SUBTITLE);
                $a_excel->setCell($excel_row, 1 + $counter, $cat->title);
                $counter++;
            }
            $excel_row++;

            foreach ($a_results as $row_results) {
                $row_title = $row_results[0];
                $counter = 0;
                $a_excel->setCell($excel_row, 0, $row_title);

                $vars = $row_results[1]->getVariables();
                if ($vars) {
                    foreach ($vars as $var) {
                        $a_excel->setCell($excel_row, ++$counter, $var->abs);
                    }
                }
                $excel_row++;
            }
        }

        // 1st column is bold
        $a_excel->setBold("A1:A" . $excel_row);
    }
    
    protected function parseResultsToExcel(ilExcel $a_excel, ilSurveyEvaluationResults $a_results, &$a_excel_row, array $a_grid = null, array $a_text_answers = null, $a_include_mode = true)
    {
        $kv = array();
        
        if ($a_include_mode) {
            if ($a_results->getModeValue() !== null) {
                // :TODO:
                $kv[$this->lng->txt("mode")] = is_array($a_results->getModeValue())
                    ? implode(", ", $a_results->getModeValue())
                    : $a_results->getModeValue();
                
                $kv[$this->lng->txt("mode_text")] = $a_results->getModeValueAsText();
                $kv[$this->lng->txt("mode_nr_of_selections")] = (int) $a_results->getModeNrOfSelections();
            }

            if ($a_results->getMedian() !== null) {
                $kv[$this->lng->txt("median")] = $a_results->getMedianAsText();
            }

            if ($a_results->getMean() !== null) {
                $kv[$this->lng->txt("arithmetic_mean")] = $a_results->getMean();
            }
        }
        
        foreach ($kv as $key => $value) {
            $a_excel->setCell($a_excel_row, 0, $key);
            $a_excel->setCell($a_excel_row++, 1, $value);
        }
                
        // grid
        if ($a_grid) {
            // header
            $a_excel->setColors("B" . $a_excel_row . ":E" . $a_excel_row, ilSurveyEvaluationGUI::EXCEL_SUBTITLE);
            $a_excel->setCell($a_excel_row, 0, $this->lng->txt("categories"));
            foreach ($a_grid["cols"] as $col_idx => $col) {
                $a_excel->setCell($a_excel_row, $col_idx + 1, $col);
            }
            $a_excel_row++;
            
            // rows
            foreach ($a_grid["rows"] as $cols) {
                foreach ($cols as $col_idx => $col) {
                    $a_excel->setCell($a_excel_row, $col_idx + 1, $col);
                }
                $a_excel_row++;
            }
        }
                
        // text answers
        if ($a_text_answers) {
            // "given_answers" ?
            $a_excel->setCell($a_excel_row, 0, $this->lng->txt("freetext_answers"));
            
            // mc/sc
            if (!is_array($a_text_answers[""])) {
                $a_excel->setColors("B" . $a_excel_row . ":C" . $a_excel_row, ilSurveyEvaluationGUI::EXCEL_SUBTITLE);
                $a_excel->setCell($a_excel_row, 1, $this->lng->txt("title"));
                $a_excel->setCell($a_excel_row++, 2, $this->lng->txt("answer"));
            }
            // mtx (row), txt
            else {
                $a_excel->setColors("B" . $a_excel_row . ":B" . $a_excel_row, ilSurveyEvaluationGUI::EXCEL_SUBTITLE);
                $a_excel->setCell($a_excel_row++, 1, $this->lng->txt("answer"));
            }
            
            foreach ($a_text_answers as $var => $items) {
                foreach ($items as $item) {
                    if (!is_array($a_text_answers[""])) {
                        $a_excel->setCell($a_excel_row, 1, $var);
                        $a_excel->setCell($a_excel_row++, 2, $item);
                    } else {
                        $a_excel->setCell($a_excel_row++, 1, $item);
                    }
                }
            }
        }
    }
    
    public function exportData()
    {
        if (strlen($_POST["export_format"])) {
            $this->exportCumulatedResults(0);
            return;
        } else {
            $this->ctrl->redirect($this, 'evaluation');
        }
    }
    
    public function exportDetailData()
    {
        if (strlen($_POST["export_format"])) {
            $this->exportCumulatedResults(1);
            return;
        } else {
            $this->ctrl->redirect($this, 'evaluation');
        }
    }
    
    public function printEvaluation()
    {
        ilUtil::sendInfo($this->lng->txt('use_browser_print_function'), true);
        $this->ctrl->redirect($this, 'evaluation');
    }
    
    protected function buildExportModal($a_id, $a_cmd)
    {
        $tpl = $this->tpl;
        
        $form_id = "svymdfrm";
        
        // hide modal on form submit
        $tpl->addOnLoadCode('$("#form_' . $form_id . '").submit(function() { $("#' . $a_id . '").modal("hide"); });');
        
        include_once "Services/UIComponent/Modal/classes/class.ilModalGUI.php";
        $modal = ilModalGUI::getInstance();
        $modal->setId($a_id);
        $modal->setHeading(($this->lng->txt("svy_export_format")));
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setId($form_id);
        $form->setFormAction($this->ctrl->getFormAction($this, $a_cmd));
        
        $format = new ilSelectInputGUI($this->lng->txt("filetype"), "export_format");
        $format->setOptions(array(
            self::TYPE_XLS => $this->lng->txt('exp_type_excel'),
            self::TYPE_SPSS => $this->lng->txt('exp_type_csv')
            ));
        $form->addItem($format, true);

        $label = new ilSelectInputGUI($this->lng->txt("title"), "export_label");
        $label->setOptions(array(
            'label_only' => $this->lng->txt('export_label_only'),
            'title_only' => $this->lng->txt('export_title_only'),
            'title_label' => $this->lng->txt('export_title_label')
            ));
        $form->addItem($label);

        $form->addCommandButton($a_cmd, $this->lng->txt("export"));
        $form->setPreventDoubleSubmission(false);
        
        $modal->setBody($form->getHTML());
        
        return $modal->getHTML();
    }
    
    public function evaluation($details = 0)
    {
        $rbacsystem = $this->rbacsystem;
        $ilToolbar = $this->toolbar;
        $tree = $this->tree;
        $ui = $this->ui;

        $ui_factory = $ui->factory();
        $ui_renderer = $ui->renderer();

        // auth
        if (!$this->hasResultsAccess()) {
            if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
                ilUtil::sendFailure($this->lng->txt("permission_denied"));
                return;
            }
                
            switch ($this->object->getEvaluationAccess()) {
                case ilObjSurvey::EVALUATION_ACCESS_OFF:
                    ilUtil::sendFailure($this->lng->txt("permission_denied"));
                    return;

                case ilObjSurvey::EVALUATION_ACCESS_ALL:
                case ilObjSurvey::EVALUATION_ACCESS_PARTICIPANTS:
                    if (!$this->checkAnonymizedEvaluationAccess()) {
                        ilUtil::sendFailure($this->lng->txt("permission_denied"));
                        return;
                    }
                    break;
            }
        }
        
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_evaluation.html", "Modules/Survey");
                
        if ($this->object->get360Mode()) {
            $appr_id = $this->getAppraiseeId();
            $this->addApprSelectionToToolbar();
        }

        $results = array();
        if (!$this->object->get360Mode() || $appr_id) {
            if ($details) {
                $captions = new ilSelectInputGUI($this->lng->txt("svy_eval_captions"), "cp");
                $captions->setOptions(array(
                    "ap" => $this->lng->txt("svy_eval_captions_abs_perc"),
                    "a" => $this->lng->txt("svy_eval_captions_abs"),
                    "p" => $this->lng->txt("svy_eval_captions_perc")
                    ));
                $captions->setValue($_POST["cp"]);
                $ilToolbar->addInputItem($captions, true);
                
                $view = new ilSelectInputGUI($this->lng->txt("svy_eval_view"), "vw");
                $view->setOptions(array(
                    "tc" => $this->lng->txt("svy_eval_view_tables_charts"),
                    "t" => $this->lng->txt("svy_eval_view_tables"),
                    "c" => $this->lng->txt("svy_eval_view_charts")
                    ));
                $view->setValue($_POST["vw"]);
                $ilToolbar->addInputItem($view, true);

                include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
                $button = ilSubmitButton::getInstance();
                $button->setCaption("ok");
                $button->setCommand("evaluationdetails");
                $button->setOmitPreventDoubleSubmission(true);
                $ilToolbar->addButtonInstance($button);

                $ilToolbar->addSeparator();

                //templates: results, table of contents
                $dtmpl = new ilTemplate("tpl.il_svy_svy_results_details.html", true, true, "Modules/Survey");
                $toc_tpl = new ilTemplate("tpl.svy_results_table_contents.html", true, true, "Modules/Survey");
                $this->lng->loadLanguageModule("content");
                $toc_tpl->setVariable("TITLE_TOC", $this->lng->txt('cont_toc'));
            }
            
            $modal_id = "svy_ev_exp";
            $modal = $this->buildExportModal($modal_id, $details
                ? 'exportDetailData'
                : 'exportData');
            
            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setCaption("export");
            $button->setOnClick('$(\'#' . $modal_id . '\').modal(\'show\')');
            $ilToolbar->addButtonInstance($button);
            
            $ilToolbar->addSeparator();

            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setCaption("print");
            $button->setOnClick("if(il.Accordion) { il.Accordion.preparePrint(); } window.print(); return false;");
            $button->setOmitPreventDoubleSubmission(true);
            $ilToolbar->addButtonInstance($button);
            
            $finished_ids = null;
            if ($appr_id) {
                $finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);
                if (!sizeof($finished_ids)) {
                    $finished_ids = array(-1);
                }
            }
            
            $details_figure = $_POST["cp"]
                ? $_POST["cp"]
                : "ap";
            $details_view = $_POST["vw"]
                ? $_POST["vw"]
                : "tc";
            
            // @todo
            // filter finished ids
            $finished_ids2 = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'read_results',
                'access_results',
                $this->object->getRefId(),
                (array) $finished_ids
            );

            // parse answer data in evaluation results
            include_once("./Services/UIComponent/NestedList/classes/class.ilNestedList.php");
            $list = new ilNestedList();

            include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
            foreach ($this->object->getSurveyQuestions() as $qdata) {
                $q_eval = SurveyQuestion::_instanciateQuestionEvaluation($qdata["question_id"], $finished_ids);
                $q_res = $q_eval->getResults();
                $results[] = $q_res;
                        
                if ($details) {
                    //$this->renderDetails($details_view, $details_figure, $dtmpl, $qdata, $q_eval, $q_res);
                    $this->renderDetails($details_view, $details_figure, $qdata, $q_eval, $q_res);

                    // TABLE OF CONTENTS
                    if ($qdata["questionblock_id"] &&
                        $qdata["questionblock_id"] != $this->last_questionblock_id) {
                        $qblock = ilObjSurvey::_getQuestionblock($qdata["questionblock_id"]);
                        if ($qblock["show_blocktitle"]) {
                            $list->addListNode($qdata["questionblock_title"], "q" . $qdata["questionblock_id"]);
                        }
                        $this->last_questionblock_id = $qdata["questionblock_id"];
                    }
                    $anchor_id = "svyrdq" . $qdata["question_id"];
                    $list->addListNode("<a href='#" . $anchor_id . "'>" . $qdata["title"] . "</a>", $qdata["question_id"], $qdata["questionblock_id"] ?
                        "q" . $qdata["questionblock_id"] : 0);
                }
            }

            if ($details) {
                $list->setListClass("il_Explorer");
                $toc_tpl->setVariable("LIST", $list->getHTML());

                //TABLE OF CONTENTS
                $panel_toc = $ui_factory->panel()->standard("", $ui_factory->legacy($toc_tpl->get()));
                $render_toc = $ui_renderer->render($panel_toc);
                $dtmpl->setVariable("PANEL_TOC", $render_toc);

                //REPORT
                $report_title = "";
                $panel_report = $ui_factory->panel()->report($report_title, $this->array_panels);
                $render_report = $ui_renderer->render($panel_report);
                $dtmpl->setVariable("PANEL_REPORT", $render_report);

                //print the main template
                $this->tpl->setVariable('DETAIL', $dtmpl->get());
            }
        }
        
        $this->tpl->setVariable('MODAL', $modal);
        if (!$details) {
            include_once "./Modules/Survey/classes/tables/class.ilSurveyResultsCumulatedTableGUI.php";
            $table_gui = new ilSurveyResultsCumulatedTableGUI($this, $details ? 'evaluationdetails' : 'evaluation', $results);
            $this->tpl->setVariable('CUMULATED', $table_gui->getHTML());
        }
        unset($dtmpl);
        unset($table_gui);
        unset($modal);
        
        
        // print header
        
        $path = "";
        $path_full = $tree->getPathFull($this->object->getRefId());
        foreach ($path_full as $data) {
            $path .= " &raquo; ";
            $path .= $data['title'];
        }
        
        ilDatePresentation::setUseRelativeDates(false);
        include_once "Services/Link/classes/class.ilLink.php";
        
        $props = array(
            $this->lng->txt("link") => ilLink::_getStaticLink($this->object->getRefId()),
            $this->lng->txt("path") => $path,
            $this->lng->txt("svy_results") => !$details
                ? $this->lng->txt("svy_eval_cumulated")
                : $this->lng->txt("svy_eval_detail"),
            $this->lng->txt("date") => ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX)),
        );
        
        $this->tpl->setCurrentBlock("print_header_bl");
        foreach ($props as $key => $value) {
            $this->tpl->setVariable("HEADER_PROP_KEY", $key);
            $this->tpl->setVariable("HEADER_PROP_VALUE", $value);
            $this->tpl->parseCurrentBlock();
        }
        
        // $this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
    }
    
    /**
     * Render details
     *
     * @param string $a_details_parts
     * @param string $a_details_figure
     * @param___     ilTemplate $a_tpl
     * @param array $a_qdata
     * @param SurveyQuestionEvaluation $a_eval
     * @param ilSurveyEvaluationResults|array $a_results
     */
    //protected function renderDetails($a_details_parts, $a_details_figure, ilTemplate $a_tpl, array $a_qdata, SurveyQuestionEvaluation $a_eval, $a_results)
    protected function renderDetails($a_details_parts, $a_details_figure, array $a_qdata, SurveyQuestionEvaluation $a_eval, $a_results)
    {
        $ui_factory = $this->ui->factory();
        $a_tpl = new ilTemplate("tpl.svy_results_details_panel.html", true, true, "Modules/Survey");

        $question_res = $a_results;
        $matrix = false;
        if (is_array($question_res)) {
            $question_res = $question_res[0][1];
            $matrix = true;
        }
        $question = $question_res->getQuestion();

        // question "overview"
                
        // :TODO: present subtypes (hrz/vrt, mc/sc mtx)?
        
        $a_tpl->setVariable("QTYPE", SurveyQuestion::_getQuestionTypeName($question->getQuestionType()));
        
        $kv = array();
        $kv["users_answered"] = $question_res->getUsersAnswered();
        $kv["users_skipped"] = $question_res->getUsersSkipped();
        
        if (!$matrix) {
            if ($question_res->getModeValue() !== null) {
                $kv["mode"] = wordwrap($question_res->getModeValueAsText(), 50, "<br />");
                $kv["mode_nr_of_selections"] = $question_res->getModeNrOfSelections();
            }
            if ($question_res->getMedian() !== null) {
                $kv["median"] = $question_res->getMedianAsText();
            }
            if ($question_res->getMean() !== null) {
                $kv["arithmetic_mean"] = $question_res->getMean();
            }
        }

        $svy_type_title = SurveyQuestion::_getQuestionTypeName($question->getQuestionType());
        $qst_title = $question->getTitle();
        $svy_text = nl2br($question->getQuestiontext());
        $card_table_tpl = new ilTemplate("tpl.svy_results_details_card.html", true, true, "Modules/Survey");
        foreach ($kv as $key => $value) {
            $card_table_tpl->setCurrentBlock("question_statistics_card");
            $card_table_tpl->setVariable("QUESTION_STATISTIC_KEY", $this->lng->txt($key));
            $card_table_tpl->setVariable("QUESTION_STATISTIC_VALUE", $value);
            $card_table_tpl->parseCurrentBlock();
        }
        //anchor in title. Used in TOC
        $anchor_id = "svyrdq" . $question->getId();
        $title = "<span id='$anchor_id'>$qst_title</span>";
        $panel_qst_card = $ui_factory->panel()->sub($title, $ui_factory->legacy($svy_text))
            ->withCard($ui_factory->card()->standard($svy_type_title)->withSections(array($ui_factory->legacy($card_table_tpl->get()))));
        array_push($this->array_panels, $panel_qst_card);

        // grid
        if ($a_details_parts == "t" ||
            $a_details_parts == "tc") {
            $grid = $a_eval->getGrid(
                $a_results,
                ($a_details_figure == "ap" || $a_details_figure == "a"),
                ($a_details_figure == "ap" || $a_details_figure == "p")
            );
            if ($grid) {
                foreach ($grid["cols"] as $col) {
                    $a_tpl->setCurrentBlock("grid_col_header_bl");
                    $a_tpl->setVariable("COL_HEADER", $col);
                    $a_tpl->parseCurrentBlock();
                }
                foreach ($grid["rows"] as $cols) {
                    foreach ($cols as $idx => $col) {
                        if ($idx > 0) {
                            $a_tpl->touchBlock("grid_col_nowrap_bl");
                        }
                        
                        $a_tpl->setCurrentBlock("grid_col_bl");
                        $a_tpl->setVariable("COL_CAPTION", trim($col));
                        $a_tpl->parseCurrentBlock();
                    }

                    $a_tpl->touchBlock("grid_row_bl");
                }
            }
        }
        
        // text answers
        $texts = $a_eval->getTextAnswers($a_results);
        if ($texts) {
            if (array_key_exists("", $texts)) {
                $a_tpl->setVariable("TEXT_HEADING", $this->lng->txt("given_answers"));
                foreach ($texts[""] as $item) {
                    $a_tpl->setCurrentBlock("text_direct_item_bl");
                    $a_tpl->setVariable("TEXT_DIRECT", nl2br($item));
                    $a_tpl->parseCurrentBlock();
                }
            } else {
                include_once "Services/Accordion/classes/class.ilAccordionGUI.php";
                $acc = new ilAccordionGUI();
                $acc->setId("svyevaltxt" . $question->getId());

                $a_tpl->setVariable("TEXT_HEADING", $this->lng->txt("freetext_answers"));

                foreach ($texts as $var => $items) {
                    $list = array("<ul class=\"small\">");
                    foreach ($items as $item) {
                        $list[] = "<li>" . nl2br($item) . "</li>";
                    }
                    $list[] = "</ul>";
                    $acc->addItem($var, implode("\n", $list));
                }

                $a_tpl->setVariable("TEXT_ACC", $acc->getHTML());
            }
        }
                
        // chart
        if ($a_details_parts == "c" ||
            $a_details_parts == "tc") {
            $chart = $a_eval->getChart($a_results);
            if ($chart) {
                if (is_array($chart)) {
                    // legend
                    if (is_array($chart[1])) {
                        foreach ($chart[1] as $legend_item) {
                            $r = hexdec(substr($legend_item[1], 1, 2));
                            $g = hexdec(substr($legend_item[1], 3, 2));
                            $b = hexdec(substr($legend_item[1], 5, 2));
                            
                            $a_tpl->setCurrentBlock("legend_bl");
                            $a_tpl->setVariable("LEGEND_CAPTION", $legend_item[0]);
                            $a_tpl->setVariable("LEGEND_COLOR", $legend_item[1]);
                            $a_tpl->setVariable("LEGEND_COLOR_SVG", $r . "," . $g . "," . $b);
                            $a_tpl->parseCurrentBlock();
                        }
                    }

                    $chart = $chart[0];
                }
                $a_tpl->setVariable("CHART", $chart);
            }
        }
        $panel = $ui_factory->panel()->sub("", $ui_factory->legacy($a_tpl->get()));
        array_push($this->array_panels, $panel);
    }
    
    /**
     * Add appraisee selection to toolbar
     *
     * @param
     * @return
     */
    public function addApprSelectionToToolbar()
    {
        $ilToolbar = $this->toolbar;
        $rbacsystem = $this->rbacsystem;

        $svy_mode = $this->object->getMode();
        if ($svy_mode == ilObjSurvey::MODE_360 || $svy_mode == ilObjSurvey::MODE_SELF_EVAL) {
            $appr_id = $this->getAppraiseeId();

            $options = array();
            if (!$appr_id) {
                $options[""] = $this->lng->txt("please_select");
            }

            $no_appr = true;
            if ($this->object->get360Mode()) {
                foreach ($this->object->getAppraiseesData() as $item) {
                    if ($item["closed"]) {
                        $options[$item["user_id"]] = $item["login"];
                        $no_appr = false;
                    }
                }
            } else { //self evaluation mode
                foreach ($this->object->getSurveyParticipants() as $item) {
                    $options[ilObjUser::_lookupId($item['login'])] = $item['login'];
                    $no_appr = false;
                }
            }

            if (!$no_appr) {
                if ($rbacsystem->checkAccess("write", $this->object->getRefId()) ||
                    $this->object->get360Results() == ilObjSurvey::RESULTS_360_ALL ||
                    $this->object->getSelfEvaluationResults() == ilObjSurvey::RESULTS_SELF_EVAL_ALL) {
                    include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
                    $appr = new ilSelectInputGUI($this->lng->txt("svy_participant"), "appr_id");
                    $appr->setOptions($options);
                    $appr->setValue($this->getAppraiseeId());
                    $ilToolbar->addInputItem($appr, true);
                    
                    include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
                    $button = ilSubmitButton::getInstance();
                    $button->setCaption("survey_360_select_appraisee");
                    $button->setCommand($this->ctrl->getCmd());
                    $ilToolbar->addButtonInstance($button);
    
                    if ($appr_id) {
                        $ilToolbar->addSeparator();
                    }
                }
            } else {
                ilUtil::sendFailure($this->lng->txt("survey_360_no_closed_appraisees"));
            }
        }
    }
    
    /**
    * Processes an array as a CSV row and converts the array values to correct CSV
    * values. The "converted" array is returned
    *
    * @param array $row The array containing the values for a CSV row
    * @param string $quoteAll Indicates to quote every value (=TRUE) or only values containing quotes and separators (=FALSE, default)
    * @param string $separator The value separator in the CSV row (used for quoting) (; = default)
    * @return array The converted array ready for CSV use
    * @access public
    */
    public function processCSVRow($row, $quoteAll = false, $separator = ";")
    {
        $resultarray = array();
        foreach ($row as $rowindex => $entry) {
            if (is_array($entry)) {
                $entry = implode("/", $entry);
            }
            $surround = false;
            if ($quoteAll) {
                $surround = true;
            }
            if (strpos($entry, "\"") !== false) {
                $entry = str_replace("\"", "\"\"", $entry);
                $surround = true;
            }
            if (strpos($entry, $separator) !== false) {
                $surround = true;
            }
            // replace all CR LF with LF (for Excel for Windows compatibility
            $entry = str_replace(chr(13) . chr(10), chr(10), $entry);
            if ($surround) {
                $resultarray[$rowindex] = utf8_decode("\"" . $entry . "\"");
            } else {
                $resultarray[$rowindex] = utf8_decode($entry);
            }
        }
        return $resultarray;
    }

    
    public function exportEvaluationUser()
    {
        // build title row(s)
        
        $title_row = $title_row2 = array();
        $title_row[] = $this->lng->txt("lastname"); // #12756
        $title_row[] = $this->lng->txt("firstname");
        $title_row[] = $this->lng->txt("login");
        $title_row[] = $this->lng->txt('workingtime'); // #13622
        $title_row[] = $this->lng->txt('survey_results_finished');
        $title_row2[] = "";
        $title_row2[] = "";
        $title_row2[] = "";
        $title_row2[] = "";
        $title_row2[] = "";
        if ($this->object->canExportSurveyCode()) {
            $title_row[] = $this->lng->txt("codes");
            $title_row2[] = "";
        }
        
        $questions = array();
                
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
        foreach ($this->object->getSurveyQuestions() as $qdata) {
            $q_eval = SurveyQuestion::_instanciateQuestionEvaluation($qdata["question_id"], $finished_ids);
            $q_res = $q_eval->getResults();
            
            $questions[$qdata["question_id"]] = array($q_eval, $q_res);
                        
            $question = is_array($q_res)
                ? $q_res[0][1]->getQuestion()
                : $q_res->getQuestion();

            $do_title = $do_label = true;
            switch ($_POST['export_label']) {
                case "label_only":
                    $title_row[] = $question->label;
                    $title_row2[] = "";
                    $do_title = false;
                    break;
                    
                case "title_only":
                    $title_row[] = $question->getTitle();
                    $title_row2[] = "";
                    $do_label = false;
                    break;
                    
                default:
                    $title_row[] = $question->getTitle();
                    $title_row2[] = $question->label;
                    break;
            }
        
            $q_eval->getUserSpecificVariableTitles($title_row, $title_row2, $do_title, $do_label);
        }
        
        $rows = array();
        
        // add title row(s)
        $rows[] = $title_row;
        if (implode("", $title_row2)) {
            $rows[] = $title_row2;
        }
                
        // #13620
        ilDatePresentation::setUseRelativeDates(false);
                        
        $finished_ids = null;
        if ($this->object->get360Mode()) {
            $appr_id = $_REQUEST["appr_id"];
            if (!$appr_id) {
                $this->ctrl->redirect($this, "evaluationuser");
            }
            $finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);
            if (!sizeof($finished_ids)) {
                $finished_ids = array(-1);
            }
        }
                
        //$participants = $this->object->getSurveyParticipants($finished_ids);
        $participants = $this->filterSurveyParticipantsByAccess($finished_ids);
        
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
        foreach ($participants as $user) {
            $user_id = $user["active_id"];
        
            $row = array();
            $row[] = trim($user["lastname"])
                ? $user["lastname"]
                : $user["name"]; // anonymous
            $row[] = $user["firstname"];
            $row[] = $user["login"]; // #10579
            
            if ($this->object->canExportSurveyCode()) {
                $row[] = $user_id;
            }
            
            $row[] = $this->object->getWorkingtimeForParticipant($user_id);
            
            if ((bool) $user["finished"]) {
                $dt = new ilDateTime($user["finished_tstamp"], IL_CAL_UNIX);
                $row[] = ($_POST["export_format"] == self::TYPE_XLS)
                    ? $dt
                    : ilDatePresentation::formatDate($dt);
            } else {
                $row[] = "-"; // :TODO:
            }
            
            foreach ($questions as $item) {
                $q_eval = $item[0];
                $q_res = $item[1];
                
                $q_eval->addUserSpecificResults($row, $user_id, $q_res);
            }
            
            $rows[] = $row;
        }
        
        // #11179
        $surveyname = $this->object->getTitle() . " " . $this->lng->txt("svy_eval_user") . " " . date("Y-m-d");
        $surveyname = preg_replace("/\s/", "_", trim($surveyname));
        $surveyname = ilUtil::getASCIIFilename($surveyname);
        
        switch ($_POST["export_format"]) {
            case self::TYPE_XLS:
                include_once "Services/Excel/classes/class.ilExcel.php";
                $excel = new ilExcel();
                $excel->addSheet($this->lng->txt("svy_eval_user"));
                            
                foreach ($rows as $row_idx => $row) {
                    foreach ($row as $col_idx => $col) {
                        $excel->setCell($row_idx + 1, $col_idx, $col);
                    }
                    if (!$row_idx) {
                        $excel->setBold("A1:" . $excel->getColumnCoord(sizeof($row) - 1) . "1");
                    }
                }
                $excel->sendToClient($surveyname);
                
                // no break
            case self::TYPE_SPSS:
                $csv = "";
                $separator = ";";
                foreach ($rows as $csvrow) {
                    $csvrow = str_replace("\n", " ", $this->processCSVRow($csvrow, true, $separator));
                    $csv .= join($csvrow, $separator) . "\n";
                }
                ilUtil::deliverData($csv, "$surveyname.csv");
                exit();
        }
    }
    
    /**
    * Print the survey evaluation for a selected user
    *
    * Print the survey evaluation for a selected user
    *
    * @access private
    */
    public function evaluationuser()
    {
        $ilAccess = $this->access;
        $ilToolbar = $this->toolbar;

        if (!$this->hasResultsAccess() &&
            $this->object->getMode() != ilObjSurvey::MODE_SELF_EVAL) {
            ilUtil::sendFailure($this->lng->txt("no_permission"), true);
            $this->ctrl->redirectByClass("ilObjSurveyGUI", "infoScreen");
        }
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this, "evaluationuser"));
        
        if ($this->object->get360Mode()) {
            $appr_id = $this->getAppraiseeId();
            $this->addApprSelectionToToolbar();
        }

        $tabledata = null;
        if (!$this->object->get360Mode() || $appr_id) {
            $modal_id = "svy_ev_exp";
            $modal = $this->buildExportModal($modal_id, "exportevaluationuser");
            
            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setCaption("export");
            $button->setOnClick('$(\'#' . $modal_id . '\').modal(\'show\')');
            $ilToolbar->addButtonInstance($button);
                        
            $ilToolbar->addSeparator();

            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setCaption("print");
            $button->setOnClick("window.print(); return false;");
            $button->setOmitPreventDoubleSubmission(true);
            $ilToolbar->addButtonInstance($button);
            
            $finished_ids = null;
            if ($appr_id) {
                $finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);
                if (!sizeof($finished_ids)) {
                    $finished_ids = array(-1);
                }
            }
            
            $data = $this->parseUserSpecificResults($finished_ids);
        }
        
        /*
        $this->tpl->addCss("./Modules/Survey/templates/default/survey_print.css", "print");
        $this->tpl->setCurrentBlock("generic_css");
        $this->tpl->setVariable("LOCATION_GENERIC_STYLESHEET", "./Modules/Survey/templates/default/evaluation_print.css");
        $this->tpl->setVariable("MEDIA_GENERIC_STYLESHEET", "print");
        $this->tpl->parseCurrentBlock();
        */
        
        include_once "./Modules/Survey/classes/tables/class.ilSurveyResultsUserTableGUI.php";
        $table_gui = new ilSurveyResultsUserTableGUI($this, 'evaluationuser', $this->object->hasAnonymizedResults());
        $table_gui->setData($data);
        $this->tpl->setContent($table_gui->getHTML() . $modal);
    }
    
    protected function filterSurveyParticipantsByAccess($a_finished_ids)
    {
        $all_participants = $this->object->getSurveyParticipants($a_finished_ids);
        $participant_ids = [];
        foreach ($all_participants as $participant) {
            $participant_ids[] = $participant['usr_id'];
        }
        
        
        $filtered_participant_ids = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'read_results',
            'access_results',
            $this->object->getRefId(),
            $participant_ids
        );
        $participants = [];
        foreach ($all_participants as $username => $user_data) {
            if (!$user_data['usr_id']) {
                $participants[$username] = $user_data;
            }
            if (in_array($user_data['usr_id'], $filtered_participant_ids)) {
                $participants[$username] = $user_data;
            }
        }
        return $participants;
    }



    protected function parseUserSpecificResults(array $a_finished_ids = null)
    {
        $data = array();
        
        $participants = $this->filterSurveyParticipantsByAccess($a_finished_ids);
        
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
        foreach ($this->object->getSurveyQuestions() as $qdata) {
            $q_eval = SurveyQuestion::_instanciateQuestionEvaluation($qdata["question_id"], $a_finished_ids);
            $q_res = $q_eval->getResults();
                        
            $question = is_array($q_res)
                ? $q_res[0][1]->getQuestion()
                : $q_res->getQuestion();
                
            foreach ($participants as $user) {
                $user_id = $user["active_id"];
                
                $parsed_results = $q_eval->parseUserSpecificResults($q_res, $user_id);
                
                if (!array_key_exists($user_id, $data)) {
                    $wt = $this->object->getWorkingtimeForParticipant($user_id);
                    
                    $finished = $user["finished"]
                        ? $user["finished_tstamp"]
                        : false;
                    
                    $data[$user_id] = array(
                            "username" => $user["sortname"],
                            "question" => $question->getTitle(),
                            "results" => $parsed_results,
                            "workingtime" => $wt,
                            "finished" => $finished,
                            "subitems" => array()
                        );
                } else {
                    $data[$user_id]["subitems"][] = array(
                            "username" => " ",
                            "question" => $question->getTitle(),
                            "results" => $parsed_results,
                            "workingtime" => null,
                            "finished" => null
                        );
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Competence Evaluation
     *
     * @param
     * @return
     */
    public function competenceEval()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->toolbar;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $survey = $this->object;
        
        $ilTabs->activateSubtab("svy_eval_competences");
        $ilTabs->activateTab("svy_results");

        $ilToolbar->setFormAction($this->ctrl->getFormAction($this, "competenceEval"));
        
        if ($this->object->get360Mode() || $survey->getMode() == ilObjSurvey::MODE_SELF_EVAL) {
            $appr_id = $this->getAppraiseeId();
            $this->addApprSelectionToToolbar();
        }
        
        if ($appr_id == 0) {
            return;
        }
        
        // evaluation modes
        $eval_modes = array();
        
        // get all competences of survey
        include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
        $sskill = new ilSurveySkill($survey);
        $opts = $sskill->getAllAssignedSkillsAsOptions();
        $skills = array();
        foreach ($opts as $id => $o) {
            $idarr = explode(":", $id);
            $skills[$id] = array("id" => $id, "title" => $o, "profiles" => array(),
                "base_skill" => $idarr[0], "tref_id" => $idarr[1]);
        }
        //var_dump($opts);
        
        // get matching user competence profiles
        // -> add gap analysis to profile
        include_once("./Services/Skill/classes/class.ilSkillProfile.php");
        $profiles = ilSkillProfile::getProfilesOfUser($appr_id);
        foreach ($profiles as $p) {
            $prof = new ilSkillProfile($p["id"]);
            $prof_levels = $prof->getSkillLevels();
            foreach ($prof_levels as $pl) {
                if (isset($skills[$pl["base_skill_id"] . ":" . $pl["tref_id"]])) {
                    $skills[$pl["base_skill_id"] . ":" . $pl["tref_id"]]["profiles"][] =
                        $p["id"];

                    $eval_modes["gap_" . $p["id"]] =
                        $lng->txt("svy_gap_analysis") . ": " . $prof->getTitle();
                }
            }
        }
        //var_dump($skills);
        //var_dump($eval_modes);

        // if one competence does not match any profiles
        // -> add "competences of survey" alternative
        reset($skills);
        foreach ($skills as $sk) {
            if (count($sk["profiles"]) == 0) {
                $eval_modes["skills_of_survey"] = $lng->txt("svy_all_survey_competences");
            }
        }
        
        // final determination of current evaluation mode
        $comp_eval_mode = $_GET["comp_eval_mode"];
        if ($_POST["comp_eval_mode"] != "") {
            $comp_eval_mode = $_POST["comp_eval_mode"];
        }
        
        if (!isset($eval_modes[$comp_eval_mode])) {
            reset($eval_modes);
            $comp_eval_mode = key($eval_modes);
            $ilCtrl->setParameter($this, "comp_eval_mode", $comp_eval_mode);
        }
        
        $ilCtrl->saveParameter($this, "comp_eval_mode");
        
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $mode_sel = new ilSelectInputGUI($lng->txt("svy_analysis"), "comp_eval_mode");
        $mode_sel->setOptions($eval_modes);
        $mode_sel->setValue($comp_eval_mode);
        $ilToolbar->addInputItem($mode_sel, true);
        
        $ilToolbar->addFormButton($lng->txt("select"), "competenceEval");

        if (substr($comp_eval_mode, 0, 4) == "gap_") {
            // gap analysis
            $profile_id = (int) substr($comp_eval_mode, 4);
            
            include_once("./Services/Skill/classes/class.ilPersonalSkillsGUI.php");
            $pskills_gui = new ilPersonalSkillsGUI();
            $pskills_gui->setProfileId($profile_id);
            $pskills_gui->setGapAnalysisActualStatusModePerObject($survey->getId(), $lng->txt("skmg_eval_type_1"));
            if ($survey->getFinishedIdForAppraiseeIdAndRaterId($appr_id, $appr_id) > 0) {
                $sskill = new ilSurveySkill($survey);
                $self_levels = array();
                foreach ($sskill->determineSkillLevelsForAppraisee($appr_id, true) as $sl) {
                    $self_levels[$sl["base_skill_id"]][$sl["tref_id"]] = $sl["new_level_id"];
                }
                $pskills_gui->setGapAnalysisSelfEvalLevels($self_levels);
            }
            $html = $pskills_gui->getGapAnalysisHTML($appr_id);
            
            $tpl->setContent($html);
        } else { // must be all survey competences
            include_once("./Services/Skill/classes/class.ilPersonalSkillsGUI.php");
            $pskills_gui = new ilPersonalSkillsGUI();
            #23743
            if ($survey->getMode() != ilObjSurvey::MODE_SELF_EVAL) {
                $pskills_gui->setGapAnalysisActualStatusModePerObject($survey->getId(), $lng->txt("skmg_eval_type_1"));
            }
            if ($survey->getFinishedIdForAppraiseeIdAndRaterId($appr_id, $appr_id) > 0) {
                $sskill = new ilSurveySkill($survey);
                $self_levels = array();
                foreach ($sskill->determineSkillLevelsForAppraisee($appr_id, true) as $sl) {
                    $self_levels[$sl["base_skill_id"]][$sl["tref_id"]] = $sl["new_level_id"];
                }
                $pskills_gui->setGapAnalysisSelfEvalLevels($self_levels);
            }
            $sk = array();
            foreach ($skills as $skill) {
                $sk[] = array(
                    "base_skill_id" => (int) $skill["base_skill"],
                    "tref_id" => (int) $skill["tref_id"]
                    );
            }
            $html = $pskills_gui->getGapAnalysisHTML($appr_id, $sk);

            $tpl->setContent($html);
        }
    }
    
    /**
     * Check if user can view results granted by rbac or positions
     */
    protected function hasResultsAccess()
    {
        return $this->access->checkRbacOrPositionPermissionAccess('read_results', 'access_results', $this->object->getRefId());
    }
}
