<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/class.ilTestServiceGUI.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';
require_once 'Modules/Test/classes/class.ilTestPassFinishTasks.php';


/**
 * Output class for assessment test evaluation
 *
 * The ilTestEvaluationGUI class creates the output for the ilObjTestGUI
 * class when authors evaluate a test. This saves some heap space because
 * the ilObjTestGUI class will be much smaller then
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup ModulesTest
 *
 * @ilCtrl_Calls ilTestEvaluationGUI: ilTestPassDetailsOverviewTableGUI
 * @ilCtrl_Calls ilTestEvaluationGUI: ilTestResultsToolbarGUI
 * @ilCtrl_Calls ilTestEvaluationGUI: ilTestPassDeletionConfirmationGUI
 */
class ilTestEvaluationGUI extends ilTestServiceGUI
{
    /**
     * @var ilTestAccess
     */
    protected $testAccess;
    
    /**
     * @var ilTestProcessLockerFactory
     */
    protected $processLockerFactory;
    
    /**
     * ilTestEvaluationGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the
     * ilTestEvaluationGUI object.
     *
     * @param ilObjTest $a_object Associated ilObjTest class
     */
    public function __construct(ilObjTest $a_object)
    {
        parent::__construct($a_object);
        
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        require_once 'Modules/Test/classes/class.ilTestProcessLockerFactory.php';
        $this->processLockerFactory = new ilTestProcessLockerFactory(
            new ilSetting('assessment'),
            $DIC->database()
        );
    }
    
    /**
     * @return ilTestAccess
     */
    public function getTestAccess()
    {
        return $this->testAccess;
    }
    
    /**
     * @param ilTestAccess $testAccess
     */
    public function setTestAccess($testAccess)
    {
        $this->testAccess = $testAccess;
    }

    /**
     * execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        $this->ctrl->saveParameter($this, "sequence");
        $this->ctrl->saveParameter($this, "active_id");
        $cmd = $this->getCommand($cmd);
        switch ($next_class) {
            case 'iltestpassdetailsoverviewtablegui':
                require_once 'Modules/Test/classes/tables/class.ilTestPassDetailsOverviewTableGUI.php';
                $tableGUI = new ilTestPassDetailsOverviewTableGUI($this->ctrl, $this, 'outUserPassDetails');
                $tableGUI->setIsPdfGenerationRequest($this->isPdfDeliveryRequest());
                $tableGUI->initFilter();
                $this->ctrl->forwardCommand($tableGUI);
                break;

            default:
                $ret = &$this->$cmd();
                break;
        }
        return $ret;
    }

    public function &getHeaderNames()
    {
        $headernames = array();
        if ($this->object->getAnonymity()) {
            array_push($headernames, $this->lng->txt("counter"));
        } else {
            array_push($headernames, $this->lng->txt("name"));
            array_push($headernames, $this->lng->txt("login"));
        }
        $additionalFields = $this->object->getEvaluationAdditionalFields();
        if (count($additionalFields)) {
            foreach ($additionalFields as $fieldname) {
                array_push($headernames, $this->lng->txt($fieldname));
            }
        }
        array_push($headernames, $this->lng->txt("tst_reached_points"));
        array_push($headernames, $this->lng->txt("tst_mark"));
        if ($this->object->getECTSOutput()) {
            array_push($headernames, $this->lng->txt("ects_grade"));
        }
        array_push($headernames, $this->lng->txt("tst_answered_questions"));
        array_push($headernames, $this->lng->txt("working_time"));
        array_push($headernames, $this->lng->txt("detailed_evaluation"));
        return $headernames;
    }
    
    public function &getHeaderVars()
    {
        $headervars = array();
        if ($this->object->getAnonymity()) {
            array_push($headervars, "counter");
        } else {
            array_push($headervars, "name");
            array_push($headervars, "login");
        }
        array_push($headervars, "resultspoints");
        array_push($headervars, "resultsmarks");
        if ($this->object->getECTSOutput()) {
            array_push($headervars, "ects_grade");
        }
        array_push($headervars, "qworkedthrough");
        array_push($headervars, "timeofwork");
        array_push($headervars, "");
        return $headervars;
    }
    
    /**
     * @deprecated command is not used any longer
     */
    public function filterEvaluation()
    {
        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }
        
        include_once "./Modules/Test/classes/tables/class.ilEvaluationAllTableGUI.php";
        $table_gui = new ilEvaluationAllTableGUI($this, 'outEvaluation');
        $table_gui->writeFilterToSession();
        $this->ctrl->redirect($this, "outEvaluation");
    }
    
    /**
     * @deprecated command is not used any longer
     */
    public function resetfilterEvaluation()
    {
        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }
        
        include_once "./Modules/Test/classes/tables/class.ilEvaluationAllTableGUI.php";
        $table_gui = new ilEvaluationAllTableGUI($this, 'outEvaluation');
        $table_gui->resetFilter();
        $this->ctrl->redirect($this, "outEvaluation");
    }

    /**
    * Creates the evaluation output for the test
    *
    * @access public
    */
    public function outEvaluation()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ilToolbar = $DIC->toolbar();

        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }
        
        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_STATISTICS);
        
        include_once "./Modules/Test/classes/tables/class.ilEvaluationAllTableGUI.php";
        
        $table_gui = new ilEvaluationAllTableGUI(
            $this,
            'outEvaluation',
            $this->object->getAnonymity(),
            $this->object->isOfferingQuestionHintsEnabled()
        );
        
        $data = array();
        $arrFilter = array();
        
        foreach ($table_gui->getFilterItems() as $item) {
            if ($item->getValue() !== false) {
                switch ($item->getPostVar()) {
                    case 'group':
                    case 'name':
                    case 'course':
                        $arrFilter[$item->getPostVar()] = $item->getValue();
                        break;
                    case 'passed_only':
                        $passedonly = $item->getChecked();
                        break;
                }
            }
        }
        include_once "./Modules/Test/classes/class.ilTestEvaluationData.php";
        $eval = new ilTestEvaluationData($this->object);
        $eval->setFilterArray($arrFilter);
        $foundParticipants = $eval->getParticipants();
        
        require_once 'Modules/Test/classes/class.ilTestParticipantData.php';
        require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
        
        $participantData = new ilTestParticipantData($DIC->database(), $DIC->language());
        $participantData->setActiveIdsFilter($eval->getParticipantIds());
        
        $participantData->setParticipantAccessFilter(
            ilTestParticipantAccessFilter::getAccessStatisticsUserFilter($this->ref_id)
        );
        
        $participantData->load($this->object->getTestId());
        
        $counter = 1;
        if (count($participantData->getActiveIds()) > 0) {
            if ($this->object->getECTSOutput()) {
                $passed_array = &$this->object->getTotalPointsPassedArray();
            }
            foreach ($participantData->getActiveIds() as $active_id) {
                if (!isset($foundParticipants[$active_id]) || !($foundParticipants[$active_id] instanceof ilTestEvaluationUserData)) {
                    continue;
                }
                
                /* @var $userdata ilTestEvaluationUserData */
                $userdata = $foundParticipants[$active_id];
                
                $remove = false;
                if ($passedonly) {
                    $mark_obj = $this->object->getMarkSchema()->getMatchingMark($userdata->getReachedPointsInPercent());
                    
                    if ($mark_obj->getPassed() == false || !$userdata->areObligationsAnswered()) {
                        $remove = true;
                    }
                }
                if (!$remove) {
                    // build the evaluation row
                    $evaluationrow = array();
                    if ($this->object->getAnonymity()) {
                        $evaluationrow['name'] = $counter;
                        $evaluationrow['login'] = '';
                    } else {
                        $evaluationrow['name'] = $userdata->getName();
                        if (strlen($userdata->getLogin())) {
                            $evaluationrow['login'] = "[" . $userdata->getLogin() . "]";
                        } else {
                            $evaluationrow['login'] = '';
                        }
                    }

                    $evaluationrow['reached'] = $userdata->getReached();
                    $evaluationrow['max'] = $userdata->getMaxpoints();
                    $evaluationrow['hint_count'] = $userdata->getRequestedHintsCountFromScoredPass();
                    $evaluationrow['exam_id'] = $userdata->getExamIdFromScoredPass();
                    $percentage = $userdata->getReachedPointsInPercent();
                    $mark = $this->object->getMarkSchema()->getMatchingMark($percentage);
                    if (is_object($mark)) {
                        $evaluationrow['mark'] = $mark->getShortName();
                    }
                    if ($this->object->getECTSOutput()) {
                        $ects_mark = $this->object->getECTSGrade($passed_array, $userdata->getReached(), $userdata->getMaxPoints());
                        $evaluationrow['ects_grade'] = $ects_mark;
                    }
                    $evaluationrow['answered'] = $userdata->getQuestionsWorkedThroughInPercent();
                    $evaluationrow['questions_worked_through'] = $userdata->getQuestionsWorkedThrough();
                    $evaluationrow['number_of_questions'] = $userdata->getNumberOfQuestions();
                    $time_seconds = $userdata->getTimeOfWork();
                    $time_hours = floor($time_seconds / 3600);
                    $time_seconds -= $time_hours * 3600;
                    $time_minutes = floor($time_seconds / 60);
                    $time_seconds -= $time_minutes * 60;
                    $evaluationrow['working_time'] = sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds);
                    $this->ctrl->setParameter($this, "active_id", $active_id);
                    $href = $this->ctrl->getLinkTarget($this, "detailedEvaluation");
                    $detailed_evaluation = $this->lng->txt("detailed_evaluation_show");
                    $evaluationrow['details'] = "<a class=\"il_ContainerItemCommand\" href=\"$href\">$detailed_evaluation</a>";
                    $userfields = ilObjUser::_lookupFields($userdata->getUserID());
                    $evaluationrow['gender'] = $userfields['gender'];
                    $evaluationrow['email'] = $userfields['email'];
                    $evaluationrow['institution'] = $userfields['institution'];
                    $evaluationrow['street'] = $userfields['street'];
                    $evaluationrow['city'] = $userfields['city'];
                    $evaluationrow['zipcode'] = $userfields['zipcode'];
                    $evaluationrow['country'] = $userfields['country'];
                    $evaluationrow['sel_country'] = $userfields['sel_country'];
                    $evaluationrow['department'] = $userfields['department'];
                    $evaluationrow['matriculation'] = $userfields['matriculation'];
                    $counter++;
                    $data[] = $evaluationrow;
                }
            }
        }
        
        $table_gui->setData($data);
        if (count($participantData->getActiveIds()) > 0) {
            $ilToolbar->setFormName('form_output_eval');
            $ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'exportEvaluation'));
            require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
            $export_type = new ilSelectInputGUI($this->lng->txt('exp_eval_data'), 'export_type');
            $options = array(
                'excel' => $this->lng->txt('exp_type_excel'),
                'csv' => $this->lng->txt('exp_type_spss')
            );
            
            if (!$this->object->getAnonymity()) {
                try {
                    $globalCertificatePrerequisites = new ilCertificateActiveValidator();
                    if ($globalCertificatePrerequisites->validate()) {
                        $options['certificate'] = $this->lng->txt('exp_type_certificate');
                    }
                } catch (ilException $e) {
                }
            }

            $export_type->setOptions($options);
            
            $ilToolbar->addInputItem($export_type, true);
            require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';
            $button = ilSubmitButton::getInstance();
            $button->setCommand('exportEvaluation');
            $button->setCaption('export');
            $button->getOmitPreventDoubleSubmission();
            $ilToolbar->addButtonInstance($button);
        }

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
        }

        $this->tpl->setContent($table_gui->getHTML());
    }
    
    /**
    * Creates the detailed evaluation output for a selected participant
    *
    * Creates the detailed evaluation output for a selected participant
    *
    * @access public
    */
    public function detailedEvaluation()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }
        
        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_STATISTICS);

        $active_id = $_GET['active_id'];
        
        if (!$this->getTestAccess()->checkResultsAccessForActiveId($active_id)) {
            ilObjTestGUI::accessViolationRedirect();
        }
        
        if (strlen($active_id) == 0) {
            ilUtil::sendInfo($this->lng->txt('detailed_evaluation_missing_active_id'), true);
            $this->ctrl->redirect($this, 'outEvaluation');
        }
        
        $this->tpl->addCss(ilUtil::getStyleSheetLocation('output', 'test_print.css', 'Modules/Test'), 'print');

        $toolbar = $DIC['ilToolbar'];

        require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
        $backBtn = ilLinkButton::getInstance();
        $backBtn->setCaption('back');
        $backBtn->setUrl($this->ctrl->getLinkTarget($this, 'outEvaluation'));
        $toolbar->addInputItem($backBtn);

        $this->object->setAccessFilteredParticipantList(
            $this->object->buildStatisticsAccessFilteredParticipantList()
        );
        
        $data = &$this->object->getCompleteEvaluationData();

        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setTitle(sprintf(
            $this->lng->txt('detailed_evaluation_for'),
            $data->getParticipant($active_id)->getName()
        ));

        $resultPoints = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_resultspoints'));
        $resultPoints->setValue($data->getParticipant($active_id)->getReached() . " " . strtolower($this->lng->txt("of")) . " " . $data->getParticipant($active_id)->getMaxpoints() . " (" . sprintf("%2.2f", $data->getParticipant($active_id)->getReachedPointsInPercent()) . " %" . ")");
        $form->addItem($resultPoints);

        if (strlen($data->getParticipant($active_id)->getMark())) {
            $resultMarks = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_resultsmarks'));
            $resultMarks->setValue($data->getParticipant($active_id)->getMark());
            $form->addItem($resultMarks);
            if (strlen($data->getParticipant($active_id)->getECTSMark())) {
                $ectsGrade = new ilNonEditableValueGUI($this->lng->txt('ects_grade'));
                $ectsGrade->setValue($data->getParticipant($active_id)->getECTSMark());
                $form->addItem($ectsGrade);
            }
        }

        if ($this->object->isOfferingQuestionHintsEnabled()) {
            $requestHints = new ilNonEditableValueGUI($this->lng->txt('tst_question_hints_requested_hint_count_header'));
            $requestHints->setValue($data->getParticipant($active_id)->getRequestedHintsCountFromScoredPass());
            $form->addItem($requestHints);
        }

        $time_seconds = $data->getParticipant($active_id)->getTimeOfWork();
        $atime_seconds = $data->getParticipant($active_id)->getNumberOfQuestions() ? $time_seconds / $data->getParticipant($active_id)->getNumberOfQuestions() : 0;
        $time_hours = floor($time_seconds / 3600);
        $time_seconds -= $time_hours * 3600;
        $time_minutes = floor($time_seconds / 60);
        $time_seconds -= $time_minutes * 60;
        $timeOfWork = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_timeofwork'));
        $timeOfWork->setValue(sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
        $form->addItem($timeOfWork);

        $this->tpl->setVariable("TXT_ATIMEOFWORK", $this->lng->txt(""));
        $time_hours = floor($atime_seconds / 3600);
        $atime_seconds -= $time_hours * 3600;
        $time_minutes = floor($atime_seconds / 60);
        $atime_seconds -= $time_minutes * 60;
        $avgTimeOfWork = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_atimeofwork'));
        $avgTimeOfWork->setValue(sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $atime_seconds));
        $form->addItem($avgTimeOfWork);

        $firstVisit = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_firstvisit'));
        $firstVisit->setValue(ilDatePresentation::formatDate(new ilDateTime($data->getParticipant($active_id)->getFirstVisit(), IL_CAL_UNIX)));
        $form->addItem($firstVisit);

        $lastVisit = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_lastvisit'));
        $lastVisit->setValue(ilDatePresentation::formatDate(new ilDateTime($data->getParticipant($active_id)->getLastVisit(), IL_CAL_UNIX)));
        $form->addItem($lastVisit);

        $nrPasses = new ilNonEditableValueGUI($this->lng->txt('tst_nr_of_passes'));
        $nrPasses->setValue($data->getParticipant($active_id)->getLastPass() + 1);
        $form->addItem($nrPasses);

        $scoredPass = new ilNonEditableValueGUI($this->lng->txt('scored_pass'));
        if ($this->object->getPassScoring() == SCORE_BEST_PASS) {
            $scoredPass->setValue($data->getParticipant($active_id)->getBestPass() + 1);
        } else {
            $scoredPass->setValue($data->getParticipant($active_id)->getLastPass() + 1);
        }
        $form->addItem($scoredPass);

        $median = $data->getStatistics()->getStatistics()->median();
        $pct = $data->getParticipant($active_id)->getMaxpoints() ? ($median / $data->getParticipant($active_id)->getMaxpoints()) * 100.0 : 0;
        $mark = $this->object->mark_schema->getMatchingMark($pct);
        if (is_object($mark)) {
            $markMedian = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_mark_median'));
            $markMedian->setValue($mark->getShortName());
            $form->addItem($markMedian);
        }

        $rankParticipant = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_rank_participant'));
        $rankParticipant->setValue($data->getStatistics()->getStatistics()->rank($data->getParticipant($active_id)->getReached()));
        $form->addItem($rankParticipant);

        $rankMedian = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_rank_median'));
        $rankMedian->setValue($data->getStatistics()->getStatistics()->rank_median());
        $form->addItem($rankMedian);

        $totalParticipants = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_total_participants'));
        $totalParticipants->setValue($data->getStatistics()->getStatistics()->count());
        $form->addItem($totalParticipants);

        $medianField = new ilNonEditableValueGUI($this->lng->txt('tst_stat_result_median'));
        $medianField->setValue($median);
        $form->addItem($medianField);

        $this->tpl->setContent($form->getHTML());

        $tables = array();

        for ($pass = 0; $pass <= $data->getParticipant($active_id)->getLastPass(); $pass++) {
            $finishdate = ilObjTest::lookupPassResultsUpdateTimestamp($active_id, $pass);
            if ($finishdate > 0) {
                if (($DIC->access()->checkAccess('write', '', (int) $_GET['ref_id']))) {
                    $this->ctrl->setParameter($this, 'statistics', '1');
                    $this->ctrl->setParameter($this, 'active_id', $active_id);
                    $this->ctrl->setParameter($this, 'pass', $pass);
                } else {
                    $this->ctrl->setParameter($this, 'statistics', '');
                    $this->ctrl->setParameter($this, 'active_id', '');
                    $this->ctrl->setParameter($this, 'pass', '');
                }

                require_once 'Modules/Test/classes/tables/class.ilTestDetailedEvaluationStatisticsTableGUI.php';
                $table = new ilTestDetailedEvaluationStatisticsTableGUI($this, 'detailedEvaluation', ($pass + 1) . '_' . $this->object->getId());
                $table->setTitle(sprintf($this->lng->txt("tst_eval_question_points"), $pass + 1));
                if (($DIC->access()->checkAccess('write', '', (int) $_GET['ref_id']))) {
                    $table->addCommandButton('outParticipantsPassDetails', $this->lng->txt('tst_show_answer_sheet'));
                }

                $questions = $data->getParticipant($active_id)->getQuestions($pass);
                if (!is_array($questions)) {
                    $questions = $data->getParticipant($active_id)->getQuestions(0);
                }

                $tableData = array();

                $counter = 0;
                foreach ((array) $questions as $question) {
                    $userDataData = array(
                        'counter' => ++$counter,
                        'id' => $question['id'],
                        'id_txt' => $this->lng->txt('question_id_short'),
                        'title' => $data->getQuestionTitle($question['id'])
                    );

                    $answeredquestion = $data->getParticipant($active_id)->getPass($pass)->getAnsweredQuestionByQuestionId($question["id"]);
                    if (is_array($answeredquestion)) {
                        $percent = $answeredquestion['points'] ? $answeredquestion['reached'] / $answeredquestion['points'] * 100.0 : 0;
                        $userDataData['points'] = $answeredquestion['reached'] . ' ' . strtolower($this->lng->txt('of')) . " " . $answeredquestion['points'] . ' (' . sprintf("%.2f", $percent) . ' %)';
                    } else {
                        $userDataData['points'] = '0 ' . strtolower($this->lng->txt('of')) . ' ' . $question['points'] . ' (' . sprintf("%.2f", 0) . ' %) - ' . $this->lng->txt('question_not_answered');
                    }

                    $tableData[] = $userDataData;
                }
                $table->setData($tableData);

                $tables[] = $table->getHTML();
            }
        }
        
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $DIC['tpl']->setContent($form->getHTML() . implode('', $tables));
    }
    
    /**
     * Creates a PDF representation of the answers for a given question in a test
     *
     */
    public function exportQuestionForAllParticipants()
    {
        $this->getQuestionResultForTestUsers($_GET["qid"], $this->object->getTestId());
    }
    
    /**
     * Creates a ZIP file containing all file uploads for a given question in a test
     *
     */
    public function exportFileUploadsForAllParticipants()
    {
        require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
        $question_object = assQuestion::_instanciateQuestion($_GET["qid"]);
        if ($question_object instanceof ilObjFileHandlingQuestionType) {
            $question_object->deliverFileUploadZIPFile(
                $this->ref_id,
                $this->object->getTestId(),
                $this->object->getTitle()
            );
        } else {
            $this->ctrl->redirect($this, "singleResults");
        }
    }
    
    /**
    * Output of anonymous aggregated results for the test
    *
    * Output of anonymous aggregated results for the test
    *
    * @access public
    */
    public function eval_a()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ilToolbar = $DIC->toolbar();
        
        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }
        
        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_STATISTICS);

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_anonymous_aggregation.html", "Modules/Test");

        $this->object->setAccessFilteredParticipantList(
            $this->object->buildStatisticsAccessFilteredParticipantList()
        );
        
        $eval = &$this->object->getCompleteEvaluationData();
        $data = array();
        $foundParticipants = &$eval->getParticipants();
        if (count($foundParticipants)) {
            $ilToolbar->setFormName('form_output_eval');
            $ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'exportAggregatedResults'));
            require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
            $export_type = new ilSelectInputGUI($this->lng->txt('exp_eval_data'), 'export_type');
            $export_type->setOptions(array(
                'excel' => $this->lng->txt('exp_type_excel'),
                'csv' => $this->lng->txt('exp_type_spss')
            ));
            $ilToolbar->addInputItem($export_type, true);
            require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';
            $button = ilSubmitButton::getInstance();
            $button->setCommand('exportAggregatedResults');
            $button->setCaption('export');
            $button->getOmitPreventDoubleSubmission();
            $ilToolbar->addButtonInstance($button);

            array_push($data, array(
                'result' => $this->lng->txt("tst_eval_total_persons"),
                'value' => count($foundParticipants)
            ));
            $total_finished = $eval->getTotalFinishedParticipants();
            array_push($data, array(
                'result' => $this->lng->txt("tst_eval_total_finished"),
                'value' => $total_finished
            ));
            $average_time = $this->object->evalTotalStartedAverageTime(
                $eval->getParticipantIds()
            );
            $diff_seconds = $average_time;
            $diff_hours = floor($diff_seconds / 3600);
            $diff_seconds -= $diff_hours * 3600;
            $diff_minutes = floor($diff_seconds / 60);
            $diff_seconds -= $diff_minutes * 60;
            array_push($data, array(
                'result' => $this->lng->txt("tst_eval_total_finished_average_time"),
                'value' => sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds)
            ));
            $total_passed = 0;
            $total_passed_reached = 0;
            $total_passed_max = 0;
            $total_passed_time = 0;
            foreach ($foundParticipants as $userdata) {
                if ($userdata->getPassed()) {
                    $total_passed++;
                    $total_passed_reached += $userdata->getReached();
                    $total_passed_max += $userdata->getMaxpoints();
                    $total_passed_time += $userdata->getTimeOfWork();
                }
            }
            $average_passed_reached = $total_passed ? $total_passed_reached / $total_passed : 0;
            $average_passed_max = $total_passed ? $total_passed_max / $total_passed : 0;
            $average_passed_time = $total_passed ? $total_passed_time / $total_passed : 0;
            array_push($data, array(
                'result' => $this->lng->txt("tst_eval_total_passed"),
                'value' => $total_passed
            ));
            array_push($data, array(
                'result' => $this->lng->txt("tst_eval_total_passed_average_points"),
                'value' => sprintf("%2.2f", $average_passed_reached) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%2.2f", $average_passed_max)
            ));
            $average_time = $average_passed_time;
            $diff_seconds = $average_time;
            $diff_hours = floor($diff_seconds / 3600);
            $diff_seconds -= $diff_hours * 3600;
            $diff_minutes = floor($diff_seconds / 60);
            $diff_seconds -= $diff_minutes * 60;
            array_push($data, array(
                'result' => $this->lng->txt("tst_eval_total_passed_average_time"),
                'value' => sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds)
            ));
        }

        include_once "./Modules/Test/classes/tables/class.ilTestAggregatedResultsTableGUI.php";
        $table_gui = new ilTestAggregatedResultsTableGUI($this, 'eval_a');
        $table_gui->setData($data);
        $this->tpl->setVariable('AGGREGATED_RESULTS', $table_gui->getHTML());
        
        $rows = array();
        foreach ($eval->getQuestionTitles() as $question_id => $question_title) {
            $answered = 0;
            $reached = 0;
            $max = 0;
            foreach ($foundParticipants as $userdata) {
                for ($i = 0; $i <= $userdata->getLastPass(); $i++) {
                    if (is_object($userdata->getPass($i))) {
                        $question = &$userdata->getPass($i)->getAnsweredQuestionByQuestionId($question_id);
                        if (is_array($question)) {
                            $answered++;
                            $reached += $question["reached"];
                            $max += $question["points"];
                        }
                    }
                }
            }
            $percent = $max ? $reached / $max * 100.0 : 0;
            $counter++;
            $this->ctrl->setParameter($this, "qid", $question_id);

            $points_reached = ($answered ? $reached / $answered : 0);
            $points_max = ($answered ? $max / $answered : 0);
            array_push(
                $rows,
                array(
                    'qid' => $question_id,
                    'title' => $question_title,
                    'points' => $points_reached,
                    'points_reached' => $points_reached,
                    'points_max' => $points_max,
                    'percentage' => (float) $percent,
                    'answers' => $answered
                )
            );
        }
        include_once "./Modules/Test/classes/tables/class.ilTestAverageReachedPointsTableGUI.php";
        $table_gui = new ilTestAverageReachedPointsTableGUI($this, 'eval_a');
        $table_gui->setData($rows);
        $this->tpl->setVariable('TBL_AVG_REACHED', $table_gui->getHTML());
    }

    /**
     * Exports the evaluation data to a selected file format
     */
    public function exportEvaluation()
    {
        $filterby = "";
        if (array_key_exists("g_filterby", $_GET)) {
            $filterby = $_GET["g_filterby"];
        }

        $filtertext = "";
        if (array_key_exists("g_userfilter", $_GET)) {
            $filtertext = $_GET["g_userfilter"];
        }

        $passedonly = false;
        if (array_key_exists("g_passedonly", $_GET)) {
            if ($_GET["g_passedonly"] == 1) {
                $passedonly = true;
            }
        }

        require_once 'Modules/Test/classes/class.ilTestExportFactory.php';
        $expFactory = new ilTestExportFactory($this->object);

        switch ($_POST["export_type"]) {
            case "excel":
                $expFactory->getExporter('results')->exportToExcel(
                    $deliver = true,
                    $filterby,
                    $filtertext,
                    $passedonly
                );
                break;

            case "csv":
                $expFactory->getExporter('results')->exportToCSV(
                    $deliver = true,
                    $filterby,
                    $filtertext,
                    $passedonly
                );
                break;

            case "certificate":
                if ($passedonly) {
                    $this->ctrl->setParameterByClass("iltestcertificategui", "g_passedonly", "1");
                }
                if (strlen($filtertext)) {
                    $this->ctrl->setParameterByClass("iltestcertificategui", "g_userfilter", $filtertext);
                }
                $this->ctrl->redirect($this, "exportCertificate");
                break;
        }
    }

    /**
    * Exports the aggregated results
    *
    * @access public
    */
    public function exportAggregatedResults()
    {
        require_once 'Modules/Test/classes/class.ilTestExportFactory.php';
        $expFactory = new ilTestExportFactory($this->object);
        $exportObj = $expFactory->getExporter('aggregated');

        switch ($_POST["export_type"]) {
            case "excel":
                $exportObj->exportToExcel($deliver = true);
                break;
            case "csv":
                $exportObj->exportToCSV($deliver = true);
                break;
        }
    }

    /**
    * Exports the user results as PDF certificates using
    * XSL-FO via XML:RPC calls
    *
    * @access public
    */
    public function exportCertificate()
    {
        global $DIC;

        $globalCertificatePrerequisites = new ilCertificateActiveValidator();
        if (!$globalCertificatePrerequisites->validate()) {
            $DIC['ilErr']->raiseError($this->lng->txt('permission_denied'), $DIC['ilErr']->MESSAGE);
        }

        $database = $DIC->database();
        $logger = $DIC->logger()->root();

        $pathFactory = new ilCertificatePathFactory();
        $objectId = $this->object->getId();
        $zipAction = new ilUserCertificateZip(
            $objectId,
            $pathFactory->create($this->object)
        );

        $archive_dir = $zipAction->createArchiveDirectory();
        $total_users = array();
        
        $this->object->setAccessFilteredParticipantList(
            $this->object->buildStatisticsAccessFilteredParticipantList()
        );

        $ilUserCertificateRepository = new ilUserCertificateRepository($database, $logger);
        $pdfGenerator = new ilPdfGenerator($ilUserCertificateRepository, $logger);

        $total_users = &$this->object->evalTotalPersonsArray();
        if (count($total_users)) {
            $certValidator = new ilCertificateDownloadValidator();
            
            foreach ($total_users as $active_id => $name) {
                $user_id = $this->object->_getUserIdFromActiveId($active_id);
                
                if (!$certValidator->isCertificateDownloadable($user_id, $objectId)) {
                    continue;
                }
                
                $pdfAction = new ilCertificatePdfAction(
                    $logger,
                    $pdfGenerator,
                    new ilCertificateUtilHelper(),
                    $this->lng->txt('error_creating_certificate_pdf')
                );

                $pdf = $pdfAction->createPDF($user_id, $objectId);
                if (strlen($pdf)) {
                    $zipAction->addPDFtoArchiveDirectory($pdf, $archive_dir, $user_id . "_" . str_replace(" ", "_", ilUtil::getASCIIFilename($name)) . ".pdf");
                }
            }
            $zipArchive = $zipAction->zipCertificatesInArchiveDirectory($archive_dir, true);
        }
    }
    
    /**
     * Returns the ID of a question for evaluation purposes. If a question id and the id of the
     * original question are given, this function returns the original id, otherwise the  question id
     *
     * @return int question or original id
     **/
    public function getEvaluationQuestionId($question_id, $original_id = "")
    {
        if ($original_id > 0) {
            return $original_id;
        } else {
            return $question_id;
        }
    }
    
    /**
    * Output of the pass details of an existing test pass for the test statistics
    *
    * Output of the pass details of an existing test pass for the test statistics
    *
    * @access public
    */
    public function outParticipantsPassDetails()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $ilAccess = $DIC['ilAccess'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        $active_id = (int) $_GET["active_id"];
        
        if (!$this->getTestAccess()->checkResultsAccessForActiveId($active_id)) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $this->ctrl->saveParameter($this, "active_id");
        $testSession = $this->testSessionFactory->getSession($active_id);

        // protect actives from other tests
        if ($testSession->getTestId() != $this->object->getTestId()) {
            ilObjTestGUI::accessViolationRedirect();
        }
        
        $this->ctrl->saveParameter($this, "pass");
        $pass = (int) $_GET["pass"];

        if (isset($_GET['statistics']) && $_GET['statistics'] == 1) {
            $this->ctrl->setParameterByClass("ilTestEvaluationGUI", "active_id", $active_id);
            $this->ctrl->saveParameter($this, 'statistics');

            $ilTabs->setBackTarget(
                $this->lng->txt('back'),
                $this->ctrl->getLinkTargetByClass('ilTestEvaluationGUI', 'detailedEvaluation')
            );
        } elseif ($this->object->getNrOfTries() == 1) {
            $ilTabs->setBackTarget(
                $this->lng->txt('back'),
                $this->ctrl->getLinkTargetByClass('ilParticipantsTestResultsGUI')
            );
        } else {
            $ilTabs->setBackTarget(
                $this->lng->txt('tst_results_back_overview'),
                $this->ctrl->getLinkTarget($this, 'outParticipantsResultsOverview')
            );
        }

        // prepare generation before contents are processed (for mathjax)
        if ($this->isPdfDeliveryRequest()) {
            ilPDFGeneratorUtils::prepareGenerationRequest("Test", PDF_USER_RESULT);
        }

        require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $ilObjDataCache);

        $objectivesList = null;

        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $testSequence = $this->testSequenceFactory->getSequenceByActiveIdAndPass($active_id, $pass);
            $testSequence->loadFromDb();
            $testSequence->loadQuestions();

            require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';
            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($testSession);

            $objectivesList = $this->buildQuestionRelatedObjectivesList($objectivesAdapter, $testSequence);
            $objectivesList->loadObjectivesTitles();

            $testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($testSession->getObjectiveOrientedContainerId());
            $testResultHeaderLabelBuilder->setUserId($testSession->getUserId());
            $testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
            $testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
            $testResultHeaderLabelBuilder->initObjectiveOrientedMode();
        }

        $result_array = $this->getFilteredTestResult($active_id, $pass, false, !$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired());

        $overviewTableGUI = $this->getPassDetailsOverviewTableGUI($result_array, $active_id, $pass, $this, "outParticipantsPassDetails", '', true, $objectivesList);
        $overviewTableGUI->setTitle($testResultHeaderLabelBuilder->getPassDetailsHeaderLabel($pass + 1));
        $user_data = $this->getAdditionalUsrDataHtmlAndPopulateWindowTitle($testSession, $active_id, false);
        $user_id = $this->object->_getUserIdFromActiveId($active_id);

        $template = new ilTemplate("tpl.il_as_tst_pass_details_overview_participants.html", true, true, "Modules/Test");

        $toolbar = $this->buildUserTestResultsToolbarGUI();
            
        $this->ctrl->setParameter($this, 'pdf', '1');
        $toolbar->setPdfExportLinkTarget($this->ctrl->getLinkTarget($this, 'outParticipantsPassDetails'));
        $this->ctrl->setParameter($this, 'pdf', '');

        if (isset($_GET['show_best_solutions'])) {
            $_SESSION['tst_results_show_best_solutions'] = true;
        } elseif (isset($_GET['hide_best_solutions'])) {
            $_SESSION['tst_results_show_best_solutions'] = false;
        } elseif (!isset($_SESSION['tst_results_show_best_solutions'])) {
            $_SESSION['tst_results_show_best_solutions'] = false;
        }

        if ($_SESSION['tst_results_show_best_solutions']) {
            $this->ctrl->setParameter($this, 'hide_best_solutions', '1');
            $toolbar->setHideBestSolutionsLinkTarget($this->ctrl->getLinkTarget($this, 'outParticipantsPassDetails'));
            $this->ctrl->setParameter($this, 'hide_best_solutions', '');
        } else {
            $this->ctrl->setParameter($this, 'show_best_solutions', '1');
            $toolbar->setShowBestSolutionsLinkTarget($this->ctrl->getLinkTarget($this, 'outParticipantsPassDetails'));
            $this->ctrl->setParameter($this, 'show_best_solutions', '');
        }

        $toolbar->build();
        $template->setVariable('RESULTS_TOOLBAR', $this->ctrl->getHTML($toolbar));

        if ($this->isGradingMessageRequired() && $this->object->getNrOfTries() == 1) {
            $gradingMessageBuilder = $this->getGradingMessageBuilder($active_id);
            $gradingMessageBuilder->buildList();

            $template->setCurrentBlock('grading_message');
            $template->setVariable('GRADING_MESSAGE', $gradingMessageBuilder->getList());
            $template->parseCurrentBlock();
        }

        $list_of_answers = $this->getPassListOfAnswers($result_array, $active_id, $pass, $_SESSION['tst_results_show_best_solutions'], false, false, false, true, $objectivesList, $testResultHeaderLabelBuilder);
        $template->setVariable("LIST_OF_ANSWERS", $list_of_answers);
        $template->setVariable("PASS_DETAILS", $this->ctrl->getHTML($overviewTableGUI));

        $data = &$this->object->getCompleteEvaluationData();
		$result = $data->getParticipant($active_id)->getReached() . " " . strtolower($this->lng->txt("of")) . " " . $data->getParticipant($active_id)->getMaxpoints() . " (" . sprintf("%2.2f", $data->getParticipant($active_id)->getReachedPointsInPercent()) . " %" . ")";
		$template->setCurrentBlock('total_score');
		$template->setVariable("TOTAL_RESULT_TEXT",$this->lng->txt('tst_stat_result_resultspoints'));
		$template->setVariable("TOTAL_RESULT",$result);
        $template->parseCurrentBlock();

        if (!$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $template->setVariable("USER_DATA", $user_data);
            
            $uname = $this->object->userLookupFullName($user_id);
            $template->setVariable("TEXT_HEADING", sprintf($this->lng->txt("tst_result_user_name_pass"), $pass + 1, $uname));

            $template->setVariable("TEXT_RESULTS", $testResultHeaderLabelBuilder->getPassDetailsHeaderLabel($pass + 1));
        }

        $template->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

        $this->populateExamId($template, (int) $active_id, (int) $pass);
        $this->populatePassFinishDate($template, ilObjTest::lookupLastTestPassAccess($active_id, $pass));

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
        }

        if ($this->isPdfDeliveryRequest()) {
            //$this->object->deliverPDFfromHTML($template->get());
            ilTestPDFGenerator::generatePDF($template->get(), ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD, $this->object->getTitleFilenameCompliant(), PDF_USER_RESULT);
        } else {
            $this->tpl->setVariable("ADM_CONTENT", $template->get());
        }
    }

    /**
    * Output of the pass overview for a test called from the statistics
    *
    * @access public
    */
    public function outParticipantsResultsOverview()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        $active_id = (int) $_GET["active_id"];
        
        if (!$this->getTestAccess()->checkResultsAccessForActiveId($active_id)) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $testSession = $this->testSessionFactory->getSession($active_id);

        // protect actives from other tests
        if ($testSession->getTestId() != $this->object->getTestId()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        if ($this->object->getNrOfTries() == 1) {
            $this->ctrl->setParameter($this, "active_id", $active_id);
            $this->ctrl->setParameter($this, "pass", ilObjTest::_getResultPass($active_id));
            $this->ctrl->redirect($this, "outParticipantsPassDetails");
        }

        $ilTabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTargetByClass(['ilObjTestGUI', 'ilTestResultsGUI', 'ilParticipantsTestResultsGUI'])
        );

        // prepare generation before contents are processed (for mathjax)
        if ($this->isPdfDeliveryRequest()) {
            ilPDFGeneratorUtils::prepareGenerationRequest("Test", PDF_USER_RESULT);
        }

        $template = new ilTemplate("tpl.il_as_tst_pass_overview_participants.html", true, true, "Modules/Test");

        $toolbar = $this->buildUserTestResultsToolbarGUI();
        
        $this->ctrl->setParameter($this, 'pdf', '1');
        $toolbar->setPdfExportLinkTarget($this->ctrl->getLinkTarget($this, __FUNCTION__));
        $this->ctrl->setParameter($this, 'pdf', '');

        $toolbar->build();
        $template->setVariable('RESULTS_TOOLBAR', $this->ctrl->getHTML($toolbar));

        require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $ilObjDataCache);
        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($testSession->getObjectiveOrientedContainerId());
            $testResultHeaderLabelBuilder->setUserId($testSession->getUserId());
            $testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
            $testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
            $testResultHeaderLabelBuilder->initObjectiveOrientedMode();
        }
        
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
        $testPassesSelector = new ilTestPassesSelector($DIC['ilDB'], $this->object);
        $testPassesSelector->setActiveId($testSession->getActiveId());
        $testPassesSelector->setLastFinishedPass($testSession->getLastFinishedPass());

        $passOverViewTableGUI = $this->buildPassOverviewTableGUI($this);
        $passOverViewTableGUI->setActiveId($testSession->getActiveId());
        $passOverViewTableGUI->setResultPresentationEnabled(true);
        $passOverViewTableGUI->setPassDetailsCommand('outParticipantsPassDetails');
        $passOverViewTableGUI->init();
        $passOverViewTableGUI->setData($this->getPassOverviewTableData($testSession, $testPassesSelector->getExistingPasses(), true, true));
        $passOverViewTableGUI->setTitle($testResultHeaderLabelBuilder->getPassOverviewHeaderLabel());
        $template->setVariable("PASS_OVERVIEW", $passOverViewTableGUI->getHTML());

        if ($this->isGradingMessageRequired()) {
            $gradingMessageBuilder = $this->getGradingMessageBuilder($active_id);
            $gradingMessageBuilder->buildList();

            $template->setCurrentBlock('grading_message');
            $template->setVariable('GRADING_MESSAGE', $gradingMessageBuilder->getList());
            $template->parseCurrentBlock();
        }

        $user_data = $this->getAdditionalUsrDataHtmlAndPopulateWindowTitle($testSession, $active_id, true);
        $user_id = $this->object->_getUserIdFromActiveId($active_id);

        if (!$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            if ($this->object->getAnonymity()) {
                $template->setVariable("TEXT_HEADING", $this->lng->txt("tst_result"));
            } else {
                $uname = $this->object->userLookupFullName($user_id, true);
                $template->setVariable("TEXT_HEADING", sprintf($this->lng->txt("tst_result_user_name"), $uname));
                $template->setVariable("USER_DATA", $user_data);
            }
        }
        
        $template->parseCurrentBlock();


        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
        }

        if (array_key_exists("pdf", $_GET) && ($_GET["pdf"] == 1)) {
            //$this->object->deliverPDFfromHTML($template->get(), $this->object->getTitle());

            $name = ilObjUser::_lookupName($user_id);
            $filename = $name['lastname'] . '_' . $name['firstname'] . '_' . $name['login'] . '__' . $this->object->getTitleFilenameCompliant();
            ilTestPDFGenerator::generatePDF($template->get(), ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD, $filename, PDF_USER_RESULT);
        //ilUtil::deliverData($file, ilUtil::getASCIIFilename($this->object->getTitle()) . ".pdf", "application/pdf", false, true);
            //$template->setVariable("PDF_FILE_LOCATION", $filename);
        } else {
            $this->tpl->setVariable("ADM_CONTENT", $template->get());
        }
    }

    public function outUserPassDetailsSetTableFilter()
    {
        $tableGUI = $this->buildPassDetailsOverviewTableGUI($this, 'outUserPassDetails');
        $tableGUI->initFilter();
        $tableGUI->resetOffset();
        $tableGUI->writeFilterToSession();
        $this->outUserPassDetails();
    }

    public function outUserPassDetailsResetTableFilter()
    {
        $tableGUI = $this->buildPassDetailsOverviewTableGUI($this, 'outUserPassDetails');
        $tableGUI->initFilter();
        $tableGUI->resetOffset();
        $tableGUI->resetFilter();
        $this->outUserPassDetails();
    }

    public function outParticipantsPassDetailsSetTableFilter()
    {
        $tableGUI = $this->buildPassDetailsOverviewTableGUI($this, 'outParticipantsPassDetails');
        $tableGUI->initFilter();
        $tableGUI->resetOffset();
        $tableGUI->writeFilterToSession();
        $this->outParticipantsPassDetails();
    }

    public function outParticipantsPassDetailsResetTableFilter()
    {
        $tableGUI = $this->buildPassDetailsOverviewTableGUI($this, 'outParticipantsPassDetails');
        $tableGUI->initFilter();
        $tableGUI->resetOffset();
        $tableGUI->resetFilter();
        $this->outParticipantsPassDetails();
    }

    /**
     * Output of the pass details of an existing test pass for the active test participant
     *
     * @access public
     */
    public function outUserPassDetails()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $ilUser = $DIC['ilUser'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $ilTabs->clearSubTabs();
        $ilTabs->setBackTarget($this->lng->txt('tst_results_back_overview'), $this->ctrl->getLinkTarget($this));

        $testSession = $this->testSessionFactory->getSession();

        if (!$this->object->getShowPassDetails()) {
            #$executable = $this->object->isExecutable($testSession, $ilUser->getId());

            #if($executable["executable"])
            #{
            $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
            #}
        }

        $active_id = $testSession->getActiveId();
        $user_id = $testSession->getUserId();

        $this->ctrl->saveParameter($this, "pass");
        $pass = $_GET["pass"];

        // prepare generation before contents are processed (for mathjax)
        if ($this->isPdfDeliveryRequest()) {
            ilPDFGeneratorUtils::prepareGenerationRequest("Test", PDF_USER_RESULT);
        }

        require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $ilObjDataCache);

        $objectivesList = null;

        $considerHiddenQuestions = true;
        $considerOptionalQuestions = true;
        
        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $considerHiddenQuestions = false;
            
            $testSequence = $this->testSequenceFactory->getSequenceByActiveIdAndPass($active_id, $pass);
            $testSequence->loadFromDb();
            $testSequence->loadQuestions();
            
            if ($this->object->isRandomTest() && !$testSequence->isAnsweringOptionalQuestionsConfirmed()) {
                $considerOptionalQuestions = false;
            }

            require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';
            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($testSession);

            $objectivesList = $this->buildQuestionRelatedObjectivesList($objectivesAdapter, $testSequence);
            $objectivesList->loadObjectivesTitles();
            
            $testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($testSession->getObjectiveOrientedContainerId());
            $testResultHeaderLabelBuilder->setUserId($testSession->getUserId());
            $testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
            $testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
            $testResultHeaderLabelBuilder->initObjectiveOrientedMode();
        }
        
        $result_array = $this->getFilteredTestResult($active_id, $pass, $considerHiddenQuestions, $considerOptionalQuestions);

        $command_solution_details = "";
        if ($this->object->getShowSolutionDetails()) {
            $command_solution_details = "outCorrectSolution";
        }
        $questionAnchorNav = $this->object->canShowSolutionPrintview();

        $tpl = new ilTemplate('tpl.il_as_tst_pass_details_overview_participants.html', true, true, "Modules/Test");

        if (!$this->isPdfDeliveryRequest()) {
            $toolbar = $this->buildUserTestResultsToolbarGUI();

            $this->ctrl->setParameter($this, 'pdf', '1');
            $toolbar->setPdfExportLinkTarget($this->ctrl->getLinkTarget($this, 'outUserPassDetails'));
            $this->ctrl->setParameter($this, 'pdf', '');

            $validator = new ilCertificateDownloadValidator();
            if ($validator->isCertificateDownloadable($user_id, $this->object->getId())) {
                $toolbar->setCertificateLinkTarget($this->ctrl->getLinkTarget($this, 'outCertificate'));
            }

            $toolbar->build();

            $tpl->setVariable('RESULTS_TOOLBAR', $this->ctrl->getHTML($toolbar));

            $tpl->setCurrentBlock('signature');
            $tpl->setVariable("SIGNATURE", $this->getResultsSignature());
            $tpl->parseCurrentBlock();
            
            if ($this->object->isShowExamIdInTestResultsEnabled()) {
                $tpl->setCurrentBlock('exam_id');
                $tpl->setVariable('EXAM_ID', ilObjTest::lookupExamId(
                    $testSession->getActiveId(),
                    $pass
                ));
                $tpl->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
                $tpl->parseCurrentBlock();
            }
        }

        if (!$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired() &&
            $this->isGradingMessageRequired() && $this->object->getNrOfTries() == 1) {
            $gradingMessageBuilder = $this->getGradingMessageBuilder($active_id);
            $gradingMessageBuilder->buildMessage();
            $gradingMessageBuilder->sendMessage();

            #$template->setCurrentBlock('grading_message');
            #$template->setVariable('GRADING_MESSAGE', );
            #$template->parseCurrentBlock();
        }

        $overviewTableGUI = $this->getPassDetailsOverviewTableGUI(
            $result_array,
            $active_id,
            $pass,
            $this,
            "outUserPassDetails",
            $command_solution_details,
            $questionAnchorNav,
            $objectivesList
        );
        $overviewTableGUI->setTitle($testResultHeaderLabelBuilder->getPassDetailsHeaderLabel($pass + 1));
        $tpl->setVariable("PASS_DETAILS", $this->ctrl->getHTML($overviewTableGUI));

        $data = &$this->object->getCompleteEvaluationData();
		$result = $data->getParticipant($active_id)->getReached() . " " . strtolower($this->lng->txt("of")) . " " . $data->getParticipant($active_id)->getMaxpoints() . " (" . sprintf("%2.2f", $data->getParticipant($active_id)->getReachedPointsInPercent()) . " %" . ")";
		$tpl->setCurrentBlock('total_score');
		$tpl->setVariable("TOTAL_RESULT_TEXT",$this->lng->txt('tst_stat_result_resultspoints'));
		$tpl->setVariable("TOTAL_RESULT",$result);
        $tpl->parseCurrentBlock();
        
        if ($this->object->canShowSolutionPrintview()) {
            $list_of_answers = $this->getPassListOfAnswers(
                $result_array,
                $active_id,
                $pass,
                $this->object->getShowSolutionListComparison(),
                false,
                false,
                false,
                true,
                $objectivesList,
                $testResultHeaderLabelBuilder
            );
            $tpl->setVariable("LIST_OF_ANSWERS", $list_of_answers);
        }
        
        $tpl->setVariable("TEXT_RESULTS", $testResultHeaderLabelBuilder->getPassDetailsHeaderLabel($pass + 1));
        $tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

        $uname = $this->object->userLookupFullName($user_id, true);
        $user_data = $this->getAdditionalUsrDataHtmlAndPopulateWindowTitle($testSession, $active_id, true);
        if (!$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            if ($this->object->getAnonymity()) {
                $tpl->setVariable("TEXT_HEADING", $this->lng->txt("tst_result_pass"));
            } else {
                $tpl->setVariable("TEXT_HEADING", sprintf($this->lng->txt("tst_result_user_name_pass"), $pass + 1, $uname));
                $tpl->setVariable("USER_DATA", $user_data);
            }
        }

        $this->populateExamId($tpl, (int) $active_id, (int) $pass);
        $this->populatePassFinishDate($tpl, ilObjTest::lookupLastTestPassAccess($active_id, $pass));
        
        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
        }

        if ($this->isPdfDeliveryRequest()) {
            ilTestPDFGenerator::generatePDF($tpl->get(), ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD, $this->object->getTitleFilenameCompliant(), PDF_USER_RESULT);
        } else {
            $this->tpl->setContent($tpl->get());
        }
    }

    /**
     * Output of the pass overview for a test called by a test participant
     *
     * @global ilTabsGUI $ilTabs
     */
    public function outUserResultsOverview()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $testSession = $this->testSessionFactory->getSession();
        $active_id = $testSession->getActiveId();
        $user_id = $ilUser->getId();
        $uname = $this->object->userLookupFullName($user_id, true);

        if (!$this->object->canShowTestResults($testSession)) {
            $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
        }

        // prepare generation before contents are processed (for mathjax)
        if ($this->isPdfDeliveryRequest()) {
            ilPDFGeneratorUtils::prepareGenerationRequest("Test", PDF_USER_RESULT);
        }

        $templatehead = new ilTemplate("tpl.il_as_tst_results_participants.html", true, true, "Modules/Test");
        $template = new ilTemplate("tpl.il_as_tst_results_participant.html", true, true, "Modules/Test");

        $toolbar = $this->buildUserTestResultsToolbarGUI();

        $this->ctrl->setParameter($this, 'pdf', '1');
        $toolbar->setPdfExportLinkTarget($this->ctrl->getLinkTarget($this, 'outUserResultsOverview'));
        $this->ctrl->setParameter($this, 'pdf', '');

        $validator = new ilCertificateDownloadValidator();
        if ($validator->isCertificateDownloadable($user_id, $this->object->getId())) {
            $toolbar->setCertificateLinkTarget($this->ctrl->getLinkTarget($this, 'outCertificate'));
        }

        $toolbar->build();
        
        $templatehead->setVariable('RESULTS_TOOLBAR', $this->ctrl->getHTML($toolbar));

        $passDetailsEnabled = $this->object->getShowPassDetails();
        #if (!$passDetailsEnabled)
        #{
        #	$executable = $this->object->isExecutable($testSession, $ilUser->getId());
        #	if (!$executable["executable"]) $passDetailsEnabled = true;
        #}

        require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $ilObjDataCache);
        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($testSession->getObjectiveOrientedContainerId());
            $testResultHeaderLabelBuilder->setUserId($testSession->getUserId());
            $testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
            $testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
            $testResultHeaderLabelBuilder->initObjectiveOrientedMode();
        }

        $template->setCurrentBlock("pass_overview");
        
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
        $testPassesSelector = new ilTestPassesSelector($DIC['ilDB'], $this->object);
        $testPassesSelector->setActiveId($testSession->getActiveId());
        $testPassesSelector->setLastFinishedPass($testSession->getLastFinishedPass());

        $passOverViewTableGUI = $this->buildPassOverviewTableGUI($this);
        $passOverViewTableGUI->setActiveId($testSession->getActiveId());
        $passOverViewTableGUI->setResultPresentationEnabled(true);
        if ($passDetailsEnabled) {
            $passOverViewTableGUI->setPassDetailsCommand('outUserPassDetails');
        }
        if ($this->object->isPassDeletionAllowed()) {
            $passOverViewTableGUI->setPassDeletionCommand('confirmDeletePass');
        }
        $passOverViewTableGUI->init();
        $passOverViewTableGUI->setData($this->getPassOverviewTableData($testSession, $testPassesSelector->getReportablePasses(), true));
        $passOverViewTableGUI->setTitle($testResultHeaderLabelBuilder->getPassOverviewHeaderLabel());
        $overview = $passOverViewTableGUI->getHTML();
        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            require_once 'Modules/Test/classes/class.ilTestLearningObjectivesStatusGUI.php';
            $loStatus = new ilTestLearningObjectivesStatusGUI($this->lng);
            $loStatus->setCrsObjId($this->getObjectiveOrientedContainer()->getObjId());
            $loStatus->setUsrId($testSession->getUserId());
            $overview .= "<br />" . $loStatus->getHTML();
        }
        $template->setVariable("PASS_OVERVIEW", $overview);
        $template->parseCurrentBlock();

        if ($this->isGradingMessageRequired()) {
            $gradingMessageBuilder = $this->getGradingMessageBuilder($active_id);
            $gradingMessageBuilder->buildMessage();
            $gradingMessageBuilder->sendMessage();

            #$template->setCurrentBlock('grading_message');
            #$template->setVariable('GRADING_MESSAGE', );
            #$template->parseCurrentBlock();
        }

        $user_data = $this->getAdditionalUsrDataHtmlAndPopulateWindowTitle($testSession, $active_id, true);

        if (!$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            if ($this->object->getAnonymity()) {
                $template->setVariable("TEXT_HEADING", $this->lng->txt("tst_result"));
            } else {
                $template->setVariable("TEXT_HEADING", sprintf($this->lng->txt("tst_result_user_name"), $uname));
                $template->setVariable("USER_DATA", $user_data);
            }
        }

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
        }
        $templatehead->setVariable("RESULTS_PARTICIPANT", $template->get());

        if ($this->isPdfDeliveryRequest()) {
            ilTestPDFGenerator::generatePDF($template->get(), ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD, $this->object->getTitleFilenameCompliant(), PDF_USER_RESULT);
        } else {
            $this->tpl->setContent($templatehead->get());
        }
    }

    /**
    * Output of the pass overview for a user when he/she wants to see his/her list of answers
    *
    * Output of the pass overview for a user when he/she wants to see his/her list of answers
    *
    * @access public
    */
    public function outUserListOfAnswerPasses()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ilUser = $DIC['ilUser'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        if (!$this->object->getShowSolutionPrintview()) {
            ilUtil::sendInfo($this->lng->txt("no_permission"), true);
            $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
        }

        $template = new ilTemplate("tpl.il_as_tst_info_list_of_answers.html", true, true, "Modules/Test");

        $pass = null;
        if (array_key_exists("pass", $_GET)) {
            if (strlen($_GET["pass"])) {
                $pass = $_GET["pass"];
            }
        }
        $user_id = $ilUser->getId();
        
        $testSession = $this->testSessionFactory->getSession();
        $active_id = $testSession->getActiveId();
        
        $template->setVariable("TEXT_RESULTS", $this->lng->txt("tst_passes"));
        
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        require_once 'Modules/Test/classes/class.ilTestPassesSelector.php';
        $testPassesSelector = new ilTestPassesSelector($DIC['ilDB'], $this->object);
        $testPassesSelector->setActiveId($testSession->getActiveId());
        $testPassesSelector->setLastFinishedPass($testSession->getLastFinishedPass());
        
        $passOverViewTableGUI = $this->buildPassOverviewTableGUI($this);
        $passOverViewTableGUI->setActiveId($testSession->getActiveId());
        $passOverViewTableGUI->setResultPresentationEnabled(false);
        $passOverViewTableGUI->setPassDetailsCommand('outUserListOfAnswerPasses');
        $passOverViewTableGUI->init();
        $passOverViewTableGUI->setData($this->getPassOverviewTableData($testSession, $testPassesSelector->getClosedPasses(), false));
        $template->setVariable("PASS_OVERVIEW", $passOverViewTableGUI->getHTML());

        $signature = "";
        if (strlen($pass)) {
            require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
            $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $ilObjDataCache);

            $objectivesList = null;

            if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
                $testSequence = $this->testSequenceFactory->getSequenceByActiveIdAndPass($active_id, $pass);
                $testSequence->loadFromDb();
                $testSequence->loadQuestions();

                require_once 'Modules/Course/classes/Objectives/class.ilLOTestQuestionAdapter.php';
                $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($testSession);

                $objectivesList = $this->buildQuestionRelatedObjectivesList($objectivesAdapter, $testSequence);
                $objectivesList->loadObjectivesTitles();

                $testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($testSession->getObjectiveOrientedContainerId());
                $testResultHeaderLabelBuilder->setUserId($testSession->getUserId());
                $testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
                $testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
                $testResultHeaderLabelBuilder->initObjectiveOrientedMode();
            }

            $result_array = $this->object->getTestResult(
                $active_id,
                $pass,
                false,
                !$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()
            );
            
            $signature = $this->getResultsSignature();
            $user_id = &$this->object->_getUserIdFromActiveId($active_id);
            $showAllAnswers = true;
            if ($this->object->isExecutable($testSession, $user_id)) {
                $showAllAnswers = false;
            }
            $this->setContextResultPresentation(false);
            $answers = $this->getPassListOfAnswers($result_array, $active_id, $pass, false, $showAllAnswers, false, false, false, $objectivesList, $testResultHeaderLabelBuilder);
            $template->setVariable("PASS_DETAILS", $answers);
        }
        $template->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $template->setVariable("PRINT_TEXT", $this->lng->txt("print"));
        $template->setVariable("PRINT_URL", "javascript:window.print();");

        $user_data = $this->getAdditionalUsrDataHtmlAndPopulateWindowTitle($testSession, $active_id, true);
        $template->setVariable("USER_DATA", $user_data);
        $template->setVariable("TEXT_LIST_OF_ANSWERS", $this->lng->txt("tst_list_of_answers"));
        if (strlen($signature)) {
            $template->setVariable("SIGNATURE", $signature);
        }
        if (!is_null($pass) && $this->object->isShowExamIdInTestResultsEnabled()) {
            $template->setCurrentBlock('exam_id_footer');
            $template->setVariable('EXAM_ID_VAL', ilObjTest::lookupExamId(
                $testSession->getActiveId(),
                $pass
            ));
            $template->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
            $template->parseCurrentBlock();
        }
        $this->tpl->setVariable("ADM_CONTENT", $template->get());

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
        }
    }

    /**
    * Output of the learners view of an existing test pass
    *
    * Output of the learners view of an existing test pass
    *
    * @access public
    */
    public function passDetails()
    {
        if (array_key_exists("pass", $_GET) && (strlen($_GET["pass"]) > 0)) {
            $this->ctrl->saveParameter($this, "pass");
            $this->ctrl->saveParameter($this, "active_id");
            $this->outTestResults(false, $_GET["pass"]);
        } else {
            $this->outTestResults(false);
        }
    }

    /**
     * Creates user results for single questions
     *
     */
    public function singleResults()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $this->object->setAccessFilteredParticipantList(
            $this->object->buildStatisticsAccessFilteredParticipantList()
        );
        
        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_STATISTICS);
        
        $data = &$this->object->getCompleteEvaluationData();
        $color_class = array("tblrow1", "tblrow2");
        $counter = 0;
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_single_answers.html", "Modules/Test");
        $foundParticipants = &$data->getParticipants();
        if (count($foundParticipants) == 0) {
            ilUtil::sendInfo($this->lng->txt("tst_no_evaluation_data"));
            return;
        } else {
            $rows = array();
            foreach ($data->getQuestionTitles() as $question_id => $question_title) {
                $answered = 0;
                $reached = 0;
                $max = 0;
                foreach ($foundParticipants as $userdata) {
                    $pass = $userdata->getScoredPass();
                    if (is_object($userdata->getPass($pass))) {
                        $question = &$userdata->getPass($pass)->getAnsweredQuestionByQuestionId($question_id);
                        if (is_array($question)) {
                            $answered++;
                        }
                    }
                }
                $counter++;
                $this->ctrl->setParameter($this, "qid", $question_id);
                require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
                $question_object = assQuestion::_instanciateQuestion($question_id);
                $download = "";
                if ($question_object instanceof ilObjFileHandlingQuestionType) {
                    if ($question_object->hasFileUploads($this->object->getTestId())) {
                        $download = "<a href=\"" . $this->ctrl->getLinkTarget($this, "exportFileUploadsForAllParticipants") . "\">" . $this->lng->txt("download") . "</a>";
                    }
                }
                array_push(
                    $rows,
                    array(
                        'qid' => $question_id,
                        'question_title' => $question_title,
                        'number_of_answers' => $answered,
                        'output' => "<a href=\"" . $this->ctrl->getLinkTarget($this, "exportQuestionForAllParticipants") . "\">" . $this->lng->txt("pdf_export") . "</a>",
                        'file_uploads' => $download
                    )
                );
            }
            if (count($rows)) {
                require_once './Modules/Test/classes/tables/class.ilResultsByQuestionTableGUI.php';
                $table_gui = new ilResultsByQuestionTableGUI($this, "singleResults");
                $table_gui->setTitle($this->lng->txt("tst_answered_questions_test"));
                $table_gui->setData($rows);

                $this->tpl->setVariable("TBL_SINGLE_ANSWERS", $table_gui->getHTML());
            } else {
                $this->tpl->setVariable("TBL_SINGLE_ANSWERS", $this->lng->txt("adm_no_special_users"));
            }
        }
    }

    /**
    * Output of a test certificate
    */
    public function outCertificate()
    {
        global $DIC;

        $user = $DIC->user();
        $database = $DIC->database();
        $logger = $DIC->logger()->root();

        $ilUserCertificateRepository = new ilUserCertificateRepository($database, $logger);
        $pdfGenerator = new ilPdfGenerator($ilUserCertificateRepository, $logger);

        $pdfAction = new ilCertificatePdfAction(
            $logger,
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $this->lng->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf((int) $user->getId(), (int) $this->object->getId());
    }

    public function confirmDeletePass()
    {
        if (isset($_GET['context']) && strlen($_GET['context'])) {
            $context = $_GET['context'];
        } else {
            $context = ilTestPassDeletionConfirmationGUI::CONTEXT_PASS_OVERVIEW;
        }
        
        if (!$this->object->isPassDeletionAllowed() && !$this->object->isDynamicTest()) {
            $this->redirectToPassDeletionContext($context);
        }

        require_once 'Modules/Test/classes/confirmations/class.ilTestPassDeletionConfirmationGUI.php';

        $confirm = new ilTestPassDeletionConfirmationGUI($this->ctrl, $this->lng, $this);
        $confirm->build((int) $_GET['active_id'], (int) $_GET['pass'], $context);

        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->setContent($this->ctrl->getHTML($confirm));
    }

    public function cancelDeletePass()
    {
        $this->redirectToPassDeletionContext($_POST['context']);
    }
    
    private function redirectToPassDeletionContext($context)
    {
        require_once 'Modules/Test/classes/confirmations/class.ilTestPassDeletionConfirmationGUI.php';

        switch ($context) {
            case ilTestPassDeletionConfirmationGUI::CONTEXT_PASS_OVERVIEW:

                $this->ctrl->redirect($this, 'outUserResultsOverview');

                // no break
            case ilTestPassDeletionConfirmationGUI::CONTEXT_INFO_SCREEN:

                $this->ctrl->redirectByClass('ilObjTestGUI', 'infoScreen');

                // no break
            case ilTestPassDeletionConfirmationGUI::CONTEXT_DYN_TEST_PLAYER:

                $this->ctrl->redirectByClass('ilTestPlayerDynamicQuestionSetGUI', 'startTest');
        }
    }
    
    public function performDeletePass()
    {
        if (isset($_POST['context']) && strlen($_POST['context'])) {
            $context = $_POST['context'];
        } else {
            $context = ilTestPassDeletionConfirmationGUI::CONTEXT_PASS_OVERVIEW;
        }
        
        if (!$this->object->isPassDeletionAllowed() && !$this->object->isDynamicTest()) {
            $this->redirectToPassDeletionContext($context);
        }
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $active_fi = null;
        $pass = null;

        if (isset($_POST['active_id']) && (int) $_POST['active_id']) {
            $active_fi = $_POST['active_id'];
        }

        if (isset($_POST['pass']) && is_numeric($_POST['pass'])) {
            $pass = $_POST['pass'];
        }

        if (is_null($active_fi) || is_null($pass)) {
            $this->ctrl->redirect($this, 'outUserResultsOverview');
        }

        if (!$this->object->isDynamicTest() && $pass == $this->object->_getResultPass($active_fi)) {
            $this->ctrl->redirect($this, 'outUserResultsOverview');
        }
            
        // Get information
        $result = $ilDB->query("
				SELECT tst_active.tries, tst_active.last_finished_pass, tst_sequence.pass
				FROM tst_active
				LEFT JOIN tst_sequence
				ON tst_sequence.active_fi = tst_active.active_id
				AND tst_sequence.pass = tst_active.tries
				WHERE tst_active.active_id = {$ilDB->quote($active_fi, 'integer')}
			");
        
        $row = $ilDB->fetchAssoc($result);
            
        $tries = $row['tries'];
        $lastFinishedPass = is_numeric($row['last_finished_pass']) ? $row['last_finished_pass'] : -1;
        
        if ($pass < $lastFinishedPass) {
            $isActivePass = false;
            $must_renumber = true;
        } elseif ($pass == $lastFinishedPass) {
            $isActivePass = false;
                
            if ($tries == $row['pass']) {
                $must_renumber = true;
            } else {
                $must_renumber = false;
            }
        } elseif ($pass == $row['pass']) {
            $isActivePass = true;
            $must_renumber = false;
        } else {
            throw new ilTestException('This should not happen, please contact Bjoern Heyser to clean up this pass salad!');
        }

        if (!$this->object->isDynamicTest() && $isActivePass) {
            $this->ctrl->redirect($this, 'outUserResultsOverview');
        }
        
        if ($pass == 0 && (
            ($lastFinishedPass == 0 && $tries == 1 && $tries != $row['pass'])
                    || ($isActivePass == true) // should be equal to || ($lastFinishedPass == -1 && $tries == 0)
                )) {
            $last_pass = true;
        } else {
            $last_pass = false;
        }
                        
        // Work on tables:
        // tst_active
        if ($last_pass) {
            $ilDB->manipulate(
                'DELETE
					FROM tst_active
					WHERE active_id = ' . $ilDB->quote($active_fi, 'integer')
                );
        } elseif (!$isActivePass) {
            $ilDB->manipulate(
                'UPDATE tst_active
					SET tries = ' . $ilDB->quote($tries - 1, 'integer') . ',
					last_finished_pass = ' . $ilDB->quote($lastFinishedPass - 1, 'integer') . '
					WHERE active_id = ' . $ilDB->quote($active_fi, 'integer')
                );
        }
        // tst_manual_fb
        $ilDB->manipulate(
            'DELETE
				FROM tst_manual_fb
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
            );
            
        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_manual_fb
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
                );
        }
            
        // tst_mark -> nothing to do
        //
        // tst_pass_result
        $ilDB->manipulate(
            'DELETE
				FROM tst_pass_result
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
            );
            
        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_pass_result
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
                );
        }
            
        // tst_qst_solved -> nothing to do
            
        // tst_rnd_copy -> nothing to do
        // tst_rnd_qpl_title -> nothing to do
            
        // tst_sequence
        $ilDB->manipulate(
            'DELETE
				FROM tst_sequence
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
            );
            
        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_sequence
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
                );
        }
        
        if ($this->object->isDynamicTest()) {
            $tables = array(
                'tst_seq_qst_tracking', 'tst_seq_qst_answstatus', 'tst_seq_qst_postponed', 'tst_seq_qst_checked'
            );
            
            foreach ($tables as $table) {
                $ilDB->manipulate("
						DELETE FROM $table
						WHERE active_fi = {$ilDB->quote($active_fi, 'integer')}
						AND pass = {$ilDB->quote($pass, 'integer')}
				");
                
                if ($must_renumber) {
                    $ilDB->manipulate("
						UPDATE $table
						SET pass = pass - 1
						WHERE active_fi = {$ilDB->quote($active_fi, 'integer')}
						AND pass > {$ilDB->quote($pass, 'integer')}
					");
                }
            }
        }
                        
        // tst_solutions
        $ilDB->manipulate(
            'DELETE
				FROM tst_solutions
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
            );
            
        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_solutions
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
                );
        }

        // tst_test_result
        $ilDB->manipulate(
            'DELETE
				FROM tst_test_result
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
            );
            
        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_test_result
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
                );
        }
            
        // tst_test_rnd_qst -> nothing to do
            
        // tst_times
        $ilDB->manipulate(
            'DELETE
				FROM tst_times
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
            );
            
        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_times
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
                );
        }
            
        require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            $this->object->logAction($this->lng->txtlng("assessment", "log_deleted_pass", ilObjAssessmentFolder::_getLogLanguage()));
        }
        // tst_result_cache
        // Ggfls. nur renumbern.
        require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
        assQuestion::_updateTestResultCache($active_fi);
        
        if ($this->object->isDynamicTest()) {
            require_once 'Modules/Test/classes/tables/class.ilTestDynamicQuestionSetStatisticTableGUI.php';
            unset($_SESSION['form_' . ilTestDynamicQuestionSetStatisticTableGUI::FILTERED_TABLE_ID]);
        }

        $this->redirectToPassDeletionContext($context);
    }

    protected function getFilteredTestResult($active_id, $pass, $considerHiddenQuestions, $considerOptionalQuestions)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $resultData = $this->object->getTestResult($active_id, $pass, false, $considerHiddenQuestions);
        $questionIds = array();
        foreach ($resultData as $resultItemKey => $resultItemValue) {
            if ($resultItemKey === 'test' || $resultItemKey === 'pass') {
                continue;
            }

            $questionIds[] = $resultItemValue['qid'];
        }

        $table_gui = $this->buildPassDetailsOverviewTableGUI($this, 'outUserPassDetails');
        $table_gui->initFilter();

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
        $questionList = new ilAssQuestionList($ilDB, $this->lng, $ilPluginAdmin);

        $questionList->setIncludeQuestionIdsFilter($questionIds);
        $questionList->setQuestionInstanceTypeFilter(null);

        foreach ($table_gui->getFilterItems() as $item) {
            if (substr($item->getPostVar(), 0, strlen('tax_')) == 'tax_') {
                $v = $item->getValue();

                if (is_array($v) && count($v) && !(int) $v[0]) {
                    continue;
                }

                $taxId = substr($item->getPostVar(), strlen('tax_'));
                $questionList->addTaxonomyFilter($taxId, $item->getValue(), $this->object->getId(), 'tst');
            } elseif ($item->getValue() !== false) {
                $questionList->addFieldFilter($item->getPostVar(), $item->getValue());
            }
        }

        $questionList->load();

        $filteredTestResult = array();
        
        foreach ($resultData as $resultItemKey => $resultItemValue) {
            if ($resultItemKey === 'test' || $resultItemKey === 'pass') {
                continue;
            }

            if (!$questionList->isInList($resultItemValue['qid'])) {
                continue;
            }

            $filteredTestResult[] = $resultItemValue;
        }

        return $filteredTestResult;
    }

    public function finishTestPassForSingleUser()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $activeId = (int) $_GET["active_id"];
        
        require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
        $accessFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->ref_id);
        
        require_once 'Modules/Test/classes/class.ilTestParticipantList.php';
        $participantData = new ilTestParticipantData($DIC->database(), $DIC->language());
        $participantData->setActiveIdsFilter(array($activeId));
        $participantData->setParticipantAccessFilter($accessFilter);
        $participantData->load($this->object->getTestId());
        
        if (!in_array($activeId, $participantData->getActiveIds())) {
            $this->redirectBackToParticipantsScreen();
        }

        /**
         * warn if the processing time of the user is not yet over
         * @see https://mantis.ilias.de/view.php?id=30357
         */
        if ($this->object->isEndingTimeEnabled() || $this->object->getEnableProcessingTime()) {
            if ($this->object->endingTimeReached() == false) {
                $starting_time = $this->object->getStartingTimeOfUser($activeId);
                if ($this->object->isMaxProcessingTimeReached($starting_time, $activeId) == false) {
                    ilUtil::sendInfo($this->lng->txt("finish_pass_for_user_in_processing_time"));
                }
            }
        }

        require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $cgui = new ilConfirmationGUI();
        
        $cgui->setHeaderText(sprintf(
            $this->lng->txt("finish_pass_for_user_confirmation"),
            $participantData->getFormatedFullnameByActiveId($activeId)
        ));

        $this->ctrl->setParameter($this, 'active_id', $activeId);
        $cgui->setFormAction($this->ctrl->getFormAction($this, "participants"));

        $cgui->setCancel($this->lng->txt("cancel"), "redirectBackToParticipantsScreen");
        $cgui->setConfirm($this->lng->txt("proceed"), "confirmFinishTestPassForUser");
        
        $this->tpl->setContent($cgui->getHTML());
    }

    public function confirmFinishTestPassForUser()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $activeId = (int) $_GET["active_id"];
        
        require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
        $accessFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->ref_id);
        
        require_once 'Modules/Test/classes/class.ilTestParticipantList.php';
        $participantData = new ilTestParticipantData($DIC->database(), $DIC->language());
        $participantData->setActiveIdsFilter(array($activeId));
        $participantData->setParticipantAccessFilter($accessFilter);
        $participantData->load($this->object->getTestId());
        
        if (in_array($activeId, $participantData->getActiveIds())) {
            $this->finishTestPass($activeId, $this->object->getId());
        }

        $this->redirectBackToParticipantsScreen();
    }

    public function finishAllUserPasses()
    {
        /**
         * give error if the processing time of at least user is not yet over
         * @see https://mantis.ilias.de/view.php?id=30357
         */
        if ($this->object->isEndingTimeEnabled() || $this->object->getEnableProcessingTime()) {
            if ($this->object->endingTimeReached() == false) {

                $accessFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->ref_id);
                $participantList = new ilTestParticipantList($this->object);
                $participantList->initializeFromDbRows($this->object->getTestParticipants());
                $participantList = $participantList->getAccessFilteredList($accessFilter);

                foreach ($participantList as $participant) {
                    if (!$participant->hasUnfinishedPasses()) {
                        continue;
                    }
                    $starting_time = $this->object->getStartingTimeOfUser($participant->getActiveId());
                    if ($this->object->isMaxProcessingTimeReached($starting_time, $participant->getActiveId()) == false) {
                        ilUtil::sendFailure($this->lng->txt("finish_pass_for_all_users_in_processing_time"), true);
                        $this->redirectBackToParticipantsScreen();
                    }
                }
            }
        }

        require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("finish_pass_for_all_users"));
        $cgui->setCancel($this->lng->txt("cancel"), "redirectBackToParticipantsScreen");
        $cgui->setConfirm($this->lng->txt("proceed"), "confirmFinishTestPassForAllUser");
        $this->tpl->setContent($cgui->getHTML());
    }

    public function confirmFinishTestPassForAllUser()
    {
        require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
        $accessFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->ref_id);

        require_once 'Modules/Test/classes/class.ilTestParticipantList.php';
        $participantList = new ilTestParticipantList($this->object);
        $participantList->initializeFromDbRows($this->object->getTestParticipants());
        $participantList = $participantList->getAccessFilteredList($accessFilter);
        
        foreach ($participantList as $participant) {
            if (!$participant->hasUnfinishedPasses()) {
                continue;
            }
            
            $this->finishTestPass($participant->getActiveId(), $this->object->getId());
        }
        
        $this->redirectBackToParticipantsScreen();
    }

    protected function finishTestPass($active_id, $obj_id)
    {
        $processLocker = $this->processLockerFactory->withContextId((int) $active_id)->getLocker();

        $test_pass_finisher = new ilTestPassFinishTasks($active_id, $obj_id);
        $test_pass_finisher->performFinishTasks($processLocker);
    }
    
    protected function redirectBackToParticipantsScreen()
    {
        $this->ctrl->redirectByClass("ilTestParticipantsGUI");
    }
}
