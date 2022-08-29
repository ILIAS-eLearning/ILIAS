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
 * Survey evaluation graphical output
 *
 * The ilSurveyEvaluationGUI class creates the evaluation output for the ilObjSurveyGUI
 * class. This saves some heap space because the ilObjSurveyGUI class will be
 * smaller.
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilSurveyEvaluationGUI
{
    public const TYPE_XLS = "excel";
    public const TYPE_SPSS = "csv";
    public const EXCEL_SUBTITLE = "DDDDDD";
    protected \ILIAS\Survey\Access\AccessManager $access_manager;
    protected \ILIAS\Survey\PrintView\GUIService $print;
    /**
     * @var mixed
     */
    protected $last_questionblock_id;
    protected array $array_panels;

    protected ilLogger $log;
    protected \ILIAS\DI\UIServices $ui;
    protected \ILIAS\Survey\Evaluation\EvaluationManager $evaluation_manager;
    protected ilTabsGUI $tabs;
    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected ilRbacSystem $rbacsystem;
    protected ilTree $tree;
    protected ilToolbarGUI $toolbar;
    protected ilObjSurvey $object;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ?int $appr_id = null;
    protected ?\ILIAS\Survey\Mode\UIModifier $ui_modifier = null;
    protected \ILIAS\Survey\Evaluation\EvaluationGUIRequest $request;
    protected \ILIAS\Skill\Service\SkillProfileService $skill_profile_service;

    public function __construct(
        ilObjSurvey $a_object
    ) {
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

        $this->request = $DIC->survey()->internal()->gui()->evaluation($this->object)->request();

        $this->ctrl->saveParameter($this, ["appr_id", "rater_id"]);
        $this->evaluation_manager = $DIC
            ->survey()
            ->internal()
            ->domain()
            ->evaluation(
                $this->object,
                $DIC->user()->getId(),
                $this->request->getAppraiseeId(),
                $this->request->getRaterId()
            );

        $this->setAppraiseeId(
            $this->evaluation_manager->getCurrentAppraisee()
        );

        $this->ui_modifier = $DIC->survey()
             ->internal()
             ->gui()
             ->modeUIModifier($this->object->getMode());
        $this->print = $DIC->survey()
            ->internal()
            ->gui()
            ->print();
        $this->access_manager = $DIC->survey()
            ->internal()
            ->domain()
            ->access(
                $this->object->getRefId(),
                $DIC->user()->getId()
            );
        $this->skill_profile_service = $DIC->skills()->profile();
    }

    public function executeCommand(): string
    {
        $skmg_set = new ilSkillManagementSettings();
        if ($this->object->getSkillService() && $skmg_set->isActivated()) {
            $cmd = $this->ctrl->getCmd("competenceEval");
        } else {
            $cmd = $this->ctrl->getCmd("evaluation");
        }

        $next_class = $this->ctrl->getNextClass($this);

        $this->log->debug($cmd);

        switch ($next_class) {
            default:
                $this->setEvalSubtabs();
                $ret = (string) $this->$cmd();
                break;
        }
        return $ret;
    }

    /**
     * Set the tabs for the evaluation output
     */
    public function setEvalSubtabs(): void
    {
        $ilTabs = $this->tabs;

        $skmg_set = new ilSkillManagementSettings();
        if ($this->object->getSkillService() && $skmg_set->isActivated()) {
            $ilTabs->addSubTabTarget(
                "svy_eval_competences",
                $this->ctrl->getLinkTarget($this, "competenceEval"),
                array("competenceEval")
            );
        }

        if ($this->object->getMode() !== ilObjSurvey::MODE_IND_FEEDB) {
            $ilTabs->addSubTabTarget(
                "svy_eval_cumulated",
                $this->ctrl->getLinkTarget($this, "evaluation"),
                array("evaluation", "checkEvaluationAccess")
            );
        }

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

        if ($this->object->getCalculateSumScore()) {
            $ilTabs->addSubTabTarget(
                "svy_sum_score",
                $this->ctrl->getLinkTarget($this, "sumscore"),
                array("sumscore")
            );
        }
    }


    public function setAppraiseeId(
        int $a_val
    ): void {
        $this->appr_id = $a_val;
    }

    public function getAppraiseeId(): int
    {
        return $this->appr_id;
    }

    public function checkAnonymizedEvaluationAccess(): bool
    {
        $ilUser = $this->user;

        if ($this->object->getAnonymize() === 1 &&
            $this->evaluation_manager->getAnonEvaluationAccess() === $this->request->getRefId()) {
            return true;
        }

        if (ilObjSurveyAccess::_hasEvaluationAccess(
            ilObject::_lookupObjId($this->request->getRefId()),
            $ilUser->getId()
        )) {
            if ($this->object->getAnonymize() === 1) {
                $this->evaluation_manager->setAnonEvaluationAccess($this->request->getRefId());
            }
            return true;
        }

        if ($this->object->getAnonymize() === 1) {
            // autocode
            $surveycode = $this->object->getUserAccessCode($ilUser->getId());
            if ($this->object->isAnonymizedParticipant($surveycode)) {
                $this->evaluation_manager->setAnonEvaluationAccess($this->request->getRefId());
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

        $this->evaluation_manager->clearAnonEvaluationAccess();
        return false;
    }

    /**
     * Checks the evaluation access after entering the survey access code
     */
    public function checkEvaluationAccess(): void
    {
        $surveycode = $this->request->getSurveyCode();
        if ($this->object->isAnonymizedParticipant($surveycode)) {
            $this->evaluation_manager->setAnonEvaluationAccess($this->request->getRefId());
            $this->evaluation();
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("svy_check_evaluation_wrong_key", true));
            $this->cancelEvaluationAccess();
        }
    }

    /**
     * Cancels the input of the survey access code for evaluation access
     */
    public function cancelEvaluationAccess(): void
    {
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;
        $path = $tree->getPathFull($this->object->getRefId());
        $ilCtrl->setParameterByClass(
            "ilrepositorygui",
            "ref_id",
            $path[count($path) - 2]["child"]
        );
        $ilCtrl->redirectByClass("ilrepositorygui", "frameset");
    }

    /**
     * Show the detailed evaluation
     */
    protected function evaluationdetails(): void
    {
        $this->evaluation(1);
    }

    public function exportCumulatedResults(
        int $details = 0
    ): void {
        $finished_ids = null;
        if ($this->object->get360Mode()) {
            $appr_id = $this->request->getAppraiseeId();
            if (!$appr_id) {
                $this->ctrl->redirect($this, $details ? "evaluationdetails" : "evaluation");
            }
            $finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);
            if (!count($finished_ids)) {
                $finished_ids = array(-1);
            }
        }

        // titles
        $title_row = array();
        $do_title = $do_label = true;
        switch ($this->request->getExportLabel()) {
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
        $excel = null;
        $csvfile = null;
        switch ($this->request->getExportFormat()) {
            case self::TYPE_XLS:
                $excel = new ilExcel();
                $excel->addSheet($this->lng->txt("svy_eval_cumulated"));
                $excel->setCellArray(array($title_row), "A1");
                $excel->setBold("A1:" . $excel->getColumnCoord(count($title_row) - 1) . "1");
                break;

            case self::TYPE_SPSS:
                $csvfile = array($title_row);
                break;
        }


        // parse answer data in evaluation results
        $ov_row = 2;
        foreach ($this->object->getSurveyQuestions() as $qdata) {
            $q_eval = SurveyQuestion::_instanciateQuestionEvaluation($qdata["question_id"], $finished_ids);
            $q_res = $q_eval->getResults();
            $ov_rows = $q_eval->exportResults($q_res, $do_title, $do_label);

            switch ($this->request->getExportFormat()) {
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
                switch ($this->request->getExportFormat()) {
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
        $surveyname = ilFileUtils::getASCIIFilename($surveyname);

        // send to client
        switch ($this->request->getExportFormat()) {
            case self::TYPE_XLS:
                $excel->sendToClient($surveyname);
                break;

            case self::TYPE_SPSS:
                $csv = "";
                $separator = ";";
                foreach ($csvfile as $csvrow) {
                    $csvrow = $this->processCSVRow($csvrow, true, $separator);
                    $csv .= implode($separator, $csvrow) . "\n";
                }
                ilUtil::deliverData($csv, $surveyname . ".csv");
                exit();
        }
    }

    /**
     * Export details (excel only)
     * @param ilSurveyEvaluationResults|array $a_results
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function exportResultsDetailsExcel(
        ilExcel $a_excel,
        SurveyQuestionEvaluation $a_eval,
        $a_results,
        bool $a_do_title,
        bool $a_do_label
    ): void {
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
        $kv[$this->lng->txt("users_answered")] = $question_res->getUsersAnswered();
        $kv[$this->lng->txt("users_skipped")] = $question_res->getUsersSkipped();		// #0021671

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
                $a_excel->setColors($a_excel->getCoordByColumnAndRow(1 + $counter, $excel_row), self::EXCEL_SUBTITLE);
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

    protected function parseResultsToExcel(
        ilExcel $a_excel,
        ilSurveyEvaluationResults $a_results,
        int &$a_excel_row,
        array $a_grid = null,
        array $a_text_answers = null,
        bool $a_include_mode = true
    ): void {
        $kv = array();

        if ($a_include_mode) {
            if ($a_results->getModeValue() !== null) {
                // :TODO:
                $kv[$this->lng->txt("mode")] = is_array($a_results->getModeValue())
                    ? implode(", ", $a_results->getModeValue())
                    : $a_results->getModeValue();

                $kv[$this->lng->txt("mode_text")] = $a_results->getModeValueAsText();
                $kv[$this->lng->txt("mode_nr_of_selections")] = $a_results->getModeNrOfSelections();
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
            $a_excel->setColors("B" . $a_excel_row . ":E" . $a_excel_row, self::EXCEL_SUBTITLE);
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
                $a_excel->setColors("B" . $a_excel_row . ":C" . $a_excel_row, self::EXCEL_SUBTITLE);
                $a_excel->setCell($a_excel_row, 1, $this->lng->txt("title"));
                $a_excel->setCell($a_excel_row++, 2, $this->lng->txt("answer"));
            }
            // mtx (row), txt
            else {
                $a_excel->setColors("B" . $a_excel_row . ":B" . $a_excel_row, self::EXCEL_SUBTITLE);
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

    public function exportData(): void
    {
        if ($this->request->getExportFormat() !== '') {
            $this->exportCumulatedResults(0);
        } else {
            $this->ctrl->redirect($this, 'evaluation');
        }
    }

    public function exportDetailData(): void
    {
        if ($this->request->getExportFormat() !== '') {
            $this->exportCumulatedResults(1);
        } else {
            $this->ctrl->redirect($this, 'evaluation');
        }
    }

    public function printEvaluation(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('use_browser_print_function'), true);
        $this->ctrl->redirect($this, 'evaluation');
    }

    /**
     * get modal html
     * @throws ilCtrlException
     */
    protected function buildExportModal(
        string $a_id,
        string $a_cmd
    ): string {
        $tpl = $this->tpl;

        $form_id = "svymdfrm";

        // hide modal on form submit
        $tpl->addOnLoadCode('$("#form_' . $form_id . '").submit(function() { $("#' . $a_id . '").modal("hide"); });');

        $modal = ilModalGUI::getInstance();
        $modal->setId($a_id);
        $modal->setHeading(($this->lng->txt("svy_export_format")));

        $form = new ilPropertyFormGUI();
        $form->setId($form_id);
        $form->setFormAction($this->ctrl->getFormAction($this, $a_cmd));

        $format = new ilSelectInputGUI($this->lng->txt("filetype"), "export_format");
        $format->setOptions(array(
            self::TYPE_XLS => $this->lng->txt('exp_type_excel'),
            self::TYPE_SPSS => $this->lng->txt('exp_type_csv')
            ));
        $form->addItem($format);

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

    protected function openEvaluation(): void
    {
        $skmg_set = new ilSkillManagementSettings();
        if ($this->object->getSkillService() && $skmg_set->isActivated()) {
            $this->competenceEval();
        } else {
            $this->evaluation();
        }
    }

    public function evaluation(
        int $details = 0
    ): void {
        $ilToolbar = $this->toolbar;
        $tree = $this->tree;
        $ui = $this->ui;

        $ui_factory = $ui->factory();
        $ui_renderer = $ui->renderer();

        $this->lng->loadLanguageModule("survey");

        $this->log->debug("check access");

        // auth
        if (!$this->hasResultsAccess()) {
            if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"));
                return;
            }

            switch ($this->object->getEvaluationAccess()) {
                case ilObjSurvey::EVALUATION_ACCESS_OFF:
                    if ($this->object->getMode() !== ilObjSurvey::MODE_IND_FEEDB) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"));
                        return;
                    }
                    break;

                case ilObjSurvey::EVALUATION_ACCESS_ALL:
                case ilObjSurvey::EVALUATION_ACCESS_PARTICIPANTS:
                    if (!$this->checkAnonymizedEvaluationAccess()) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"));
                        return;
                    }
                    break;
            }
        }

        $this->log->debug("check access ok");
        // setup toolbar

        $appr_id = $this->evaluation_manager->getCurrentAppraisee();
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));
        $results = array();

        $eval_tpl = new ilTemplate("tpl.il_svy_svy_evaluation.html", true, true, "Modules/Survey");


        if ($details) {
            $this->ui_modifier->setResultsDetailToolbar(
                $this->object,
                $ilToolbar,
                $this->user->getId()
            );
        } else {
            $this->ui_modifier->setResultsOverviewToolbar(
                $this->object,
                $ilToolbar,
                $this->user->getId()
            );
        }

        if (!$this->object->get360Mode() || $appr_id) {
            if ($details) {
                //templates: results, table of contents
                $dtmpl = new ilTemplate("tpl.il_svy_svy_results_details.html", true, true, "Modules/Survey/Evaluation");
                $toc_tpl = new ilTemplate("tpl.svy_results_table_contents.html", true, true, "Modules/Survey/Evaluation");
                $this->lng->loadLanguageModule("content");
                $toc_tpl->setVariable("TITLE_TOC", $this->lng->txt('cont_toc'));
            }

            $finished_ids = $this->evaluation_manager->getFilteredFinishedIds();

            // parse answer data in evaluation results
            $list = new ilNestedList();

            $panels = [];
            foreach ($this->object->getSurveyQuestions() as $qdata) {
                $q_eval = SurveyQuestion::_instanciateQuestionEvaluation($qdata["question_id"], $finished_ids);
                $q_res = $q_eval->getResults();
                $results[] = $q_res;

                if ($details) {
                    $panels = array_merge(
                        $panels,
                        $this->ui_modifier->getDetailPanels(
                            $this->object->getSurveyParticipants(),
                            $this->request,
                            $q_eval
                        )
                    );

                    // TABLE OF CONTENTS
                    if ($qdata["questionblock_id"] &&
                        $qdata["questionblock_id"] != $this->last_questionblock_id) {
                        $qblock = ilObjSurvey::_getQuestionblock($qdata["questionblock_id"]);
                        if ($qblock["show_blocktitle"]) {
                            $list->addListNode($qdata["questionblock_title"], "q" . $qdata["questionblock_id"]);
                        } else {
                            $list->addListNode("", "q" . $qdata["questionblock_id"]);
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
                $panel_report = $ui_factory->panel()->report($report_title, $panels);
                $render_report = $ui_renderer->render($panel_report);
                $dtmpl->setVariable("PANEL_REPORT", $render_report);

                //print the main template
                $eval_tpl->setVariable('DETAIL', $dtmpl->get());
            }
        }

        //$eval_tpl->setVariable('MODAL', $modal);
        if (!$details) {
            $table_gui = new ilSurveyResultsCumulatedTableGUI($this, 'evaluation', $results);
            $eval_tpl->setVariable('CUMULATED', $table_gui->getHTML());
        }

        //
        // print header
        //

        $path = "";
        $path_full = $tree->getPathFull($this->object->getRefId());
        foreach ($path_full as $data) {
            $path .= " &raquo; ";
            $path .= $data['title'];
        }

        ilDatePresentation::setUseRelativeDates(false);
        $props = array(
            $this->lng->txt("link") => ilLink::_getStaticLink($this->object->getRefId()),
            $this->lng->txt("path") => $path,
            $this->lng->txt("svy_results") => !$details
                ? $this->lng->txt("svy_eval_cumulated")
                : $this->lng->txt("svy_eval_detail"),
            $this->lng->txt("date") => ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX)),
        );
        $eval_tpl->setCurrentBlock("print_header_bl");
        foreach ($props as $key => $value) {
            $eval_tpl->setVariable("HEADER_PROP_KEY", $key);
            $eval_tpl->setVariable("HEADER_PROP_VALUE", $value);
            $eval_tpl->parseCurrentBlock();
        }

        $this->log->debug("end");

        $this->tpl->setContent($eval_tpl->get());
    }

    /**
     * Processes an array as a CSV row and converts the array values to correct CSV
     * values. The "converted" array is returned
     * @param array $row The array containing the values for a CSV row
     * @param bool $quoteAll Indicates to quote every value (=TRUE) or only values containing quotes and separators (=FALSE, default)
     * @param string $separator The value separator in the CSV row (used for quoting) (; = default)
     * @return array The converted array ready for CSV use
     */
    public function processCSVRow(
        array $row,
        bool $quoteAll = false,
        string $separator = ";"
    ): array {
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

    public function exportEvaluationUser(): void
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

        foreach ($this->object->getSurveyQuestions() as $qdata) {
            $q_eval = SurveyQuestion::_instanciateQuestionEvaluation($qdata["question_id"], null);
            $q_res = $q_eval->getResults();

            $questions[$qdata["question_id"]] = array($q_eval, $q_res);

            $question = is_array($q_res)
                ? $q_res[0][1]->getQuestion()
                : $q_res->getQuestion();

            $do_title = $do_label = true;
            switch ($this->request->getExportLabel()) {
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
            $appr_id = $this->request->getAppraiseeId();
            if (!$appr_id) {
                $this->ctrl->redirect($this, "evaluationuser");
            }
            $finished_ids = $this->object->getFinishedIdsForAppraiseeId($appr_id);
            if (!count($finished_ids)) {
                $finished_ids = array(-1);
            }
        }

        //$participants = $this->object->getSurveyParticipants($finished_ids);
        $participants = $this->access_manager->canReadResultOfParticipants($finished_ids);

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

            if ($user["finished"]) {
                $dt = new ilDateTime($user["finished_tstamp"], IL_CAL_UNIX);
                $row[] = ($this->request->getExportFormat() === self::TYPE_XLS)
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
        $surveyname = ilFileUtils::getASCIIFilename($surveyname);

        switch ($this->request->getExportFormat()) {
            case self::TYPE_XLS:
                $excel = new ilExcel();
                $excel->addSheet($this->lng->txt("svy_eval_user"));

                foreach ($rows as $row_idx => $row) {
                    foreach ($row as $col_idx => $col) {
                        $excel->setCell($row_idx + 1, $col_idx, $col);
                    }
                    if (!$row_idx) {
                        $excel->setBold("A1:" . $excel->getColumnCoord(count($row) - 1) . "1");
                    }
                }
                $excel->sendToClient($surveyname);

                // no break
            case self::TYPE_SPSS:
                $csv = "";
                $separator = ";";
                foreach ($rows as $csvrow) {
                    $csvrow = str_replace("\n", " ", $this->processCSVRow($csvrow, true, $separator));
                    $csv .= implode($separator, $csvrow) . "\n";
                }
                ilUtil::deliverData($csv, "$surveyname.csv");
                exit();
        }
    }

    /**
     * Print the survey evaluation for a selected user
     */
    public function evaluationuser(): void
    {
        $ilToolbar = $this->toolbar;

        if (!$this->hasResultsAccess() &&
            $this->object->getMode() !== ilObjSurvey::MODE_SELF_EVAL) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_permission"), true);
            $this->ctrl->redirectByClass("ilObjSurveyGUI", "infoScreen");
        }

        $ilToolbar->setFormAction($this->ctrl->getFormAction($this, "evaluationuser"));

        $modal = "";
        $appr_id = null;
        $data = [];

        if ($this->object->get360Mode()) {
            $appr_id = $this->getAppraiseeId();
        }

        if (!$this->object->get360Mode() || $appr_id) {
            $modal_id = "svy_ev_exp";
            $modal = $this->buildExportModal($modal_id, "exportevaluationuser");

            $button = ilLinkButton::getInstance();
            $button->setCaption("export");
            $button->setOnClick('$(\'#' . $modal_id . '\').modal(\'show\')');
            $ilToolbar->addButtonInstance($button);

            $ilToolbar->addSeparator();

            $pv = $this->print->resultsDetails($this->object->getRefId());
            $modal_elements = $pv->getModalElements(
                $this->ctrl->getLinkTargetByClass(
                    "ilSurveyEvaluationGUI",
                    "printResultsPerUserSelection"
                )
            );
            $ilToolbar->addComponent($modal_elements->button);
            $ilToolbar->addComponent($modal_elements->modal);

            $data = $this->evaluation_manager->getUserSpecificResults();
        }

        $table_gui = new ilSurveyResultsUserTableGUI($this, 'evaluationuser');
        $table_gui->setData($data);
        $this->tpl->setContent($table_gui->getHTML() . $modal);
    }

    public function competenceEval(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->toolbar;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $survey = $this->object;

        $ilTabs->activateSubTab("svy_eval_competences");
        $ilTabs->activateTab("svy_results");

        $ilToolbar->setFormAction($this->ctrl->getFormAction($this, "competenceEval"));

        $appr_id = $this->getAppraiseeId();

        if ($appr_id === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("svy_no_appraisees_found"));
            return;
        }

        $this->ui_modifier->setResultsCompetenceToolbar(
            $this->object,
            $ilToolbar,
            $this->user->getId()
        );

        // evaluation modes
        $eval_modes = array();

        // get all competences of survey
        $sskill = new ilSurveySkill($survey);
        $opts = $sskill->getAllAssignedSkillsAsOptions();
        $skills = array();
        foreach ($opts as $id => $o) {
            $idarr = explode(":", $id);
            $skills[$id] = array("id" => $id, "title" => $o, "profiles" => array(),
                "base_skill" => $idarr[0], "tref_id" => $idarr[1]);
        }

        // get matching user competence profiles
        // -> add gap analysis to profile
        $profiles = $this->skill_profile_service->getProfilesOfUser($appr_id);
        foreach ($profiles as $p) {
            $prof = $this->skill_profile_service->getById($p["id"]);
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

        // if one competence does not match any profiles
        // -> add "competences of survey" alternative
        foreach ($skills as $sk) {
            if (count($sk["profiles"]) === 0) {
                $eval_modes["skills_of_survey"] = $lng->txt("svy_all_survey_competences");
            }
        }

        // final determination of current evaluation mode
        $comp_eval_mode = $this->request->getCompEvalMode();

        if (!isset($eval_modes[$comp_eval_mode])) {
            $comp_eval_mode = key($eval_modes);
            $ilCtrl->setParameter($this, "comp_eval_mode", $comp_eval_mode);
        }

        $ilCtrl->saveParameter($this, "comp_eval_mode");

        $mode_sel = new ilSelectInputGUI($lng->txt("svy_analysis"), "comp_eval_mode");
        $mode_sel->setOptions($eval_modes);
        $mode_sel->setValue($comp_eval_mode);
        $ilToolbar->addInputItem($mode_sel, true);

        $ilToolbar->addFormButton($lng->txt("select"), "competenceEval");

        $pskills_gui = new ilPersonalSkillsGUI();
        $rater = $this->evaluation_manager->getCurrentRater();
        if ($rater !== "") {
            if (strpos($rater, "u") === 0) {
                $rater = substr($rater, 1);
            }
            $pskills_gui->setTriggerUserFilter([$rater]);
        }

        if (strpos($comp_eval_mode, "gap_") === 0) {
            // gap analysis
            $profile_id = (int) substr($comp_eval_mode, 4);

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
        } else { // must be all survey competences
            #23743
            if ($survey->getMode() !== ilObjSurvey::MODE_SELF_EVAL &&
                $survey->getMode() !== ilObjSurvey::MODE_IND_FEEDB) {
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
        }
        $tpl->setContent($html);
    }

    /**
     * Check if user can view results granted by rbac or positions
     * @todo move to access manager
     */
    protected function hasResultsAccess(): bool
    {
        return $this->access->checkRbacOrPositionPermissionAccess('read_results', 'access_results', $this->object->getRefId());
    }

    /**
     * Show sum score table
     */
    public function sumscore(): void
    {
        $ilToolbar = $this->toolbar;

        if (!$this->hasResultsAccess() &&
            $this->object->getMode() !== ilObjSurvey::MODE_SELF_EVAL) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_permission"), true);
            $this->ctrl->redirectByClass("ilObjSurveyGUI", "infoScreen");
        }

        $this->tpl->setOnScreenMessage('info', $this->lng->txt("svy_max_sum_score") . ": " . $this->object->getMaxSumScore());

        $ilToolbar->setFormAction($this->ctrl->getFormAction($this, "evaluationuser"));

        $modal_id = "svy_ev_exp";
        $modal = $this->buildExportModal($modal_id, "exportevaluationuser");

        $button = ilLinkButton::getInstance();
        $button->setCaption("print");
        $button->setOnClick("window.print(); return false;");
        $button->setOmitPreventDoubleSubmission(true);
        $ilToolbar->addButtonInstance($button);

        $finished_ids = null;

        $sum_scores = $this->getSumScores($finished_ids);
        $table_gui = new ilSumScoreTableGUI($this, 'sumscore', $this->object->hasAnonymizedResults());
        $table_gui->setSumScores($sum_scores);
        $this->tpl->setContent($table_gui->getHTML() . $modal);
    }

    /**
     * @todo move to evaluation manager, use dto
     */
    protected function getSumScores(
        ?array $a_finished_ids = null
    ): array {
        $sum_scores = [];
        foreach ($this->access_manager->canReadResultOfParticipants($a_finished_ids) as $p) {
            $sum_scores[$p["active_id"]] = [
                "username" => $p["sortname"],
                "score" => 0
            ];
        }

        foreach ($this->object->getSurveyQuestions() as $qdata) {
            $q_eval = SurveyQuestion::_instanciateQuestionEvaluation($qdata["question_id"], $a_finished_ids);
            foreach ($q_eval->getSumScores() as $finished_id => $sum_score) {
                if ($sum_score === null) {
                    $sum_scores[$finished_id]["score"] = null;
                }
                if ($sum_scores[$finished_id]["score"] !== null) {
                    $sum_scores[$finished_id]["score"] += (int) $sum_score;
                }
            }
        }
        return $sum_scores;
    }

    /**
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    public function printResultsOverviewSelection(): void
    {
        $view = $this->print->resultsOverview($this->object->getRefId());
        $view->sendForm();
    }

    /**
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    public function printResultsDetailsSelection(): void
    {
        $view = $this->print->resultsDetails($this->object->getRefId());
        $view->sendForm();
    }

    public function printResultsDetails(): void
    {
        $view = $this->print->resultsDetails($this->object->getRefId());
        $view->sendPrintView();
    }

    /**
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    public function printResultsPerUserSelection(): void
    {
        $view = $this->print->resultsPerUser($this->object->getRefId());
        $view->sendForm();
    }

    public function printResultsPerUser(): void
    {
        $view = $this->print->resultsPerUser($this->object->getRefId());
        $view->sendPrintView();
    }
}
