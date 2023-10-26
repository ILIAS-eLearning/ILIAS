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

declare(strict_types=1);

use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\GlobalScreen\Services as GSServices;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\DI\LoggingServices;

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
    protected ilTestAccess $testAccess;
    protected ilTestProcessLockerFactory $processLockerFactory;

    /**
     * ilTestEvaluationGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the
     * ilTestEvaluationGUI object.
     *
     * @param ilObjTest $a_object Associated ilObjTest class
     */
    public function __construct(ilObjTest $object)
    {
        parent::__construct($object);
        $this->participant_access_filter = new ilTestParticipantAccessFilterFactory($this->access);

        $this->processLockerFactory = new ilTestProcessLockerFactory(
            new ilSetting('assessment'),
            $this->db
        );
    }

    public function getTestAccess(): ilTestAccess
    {
        return $this->testAccess;
    }

    public function setTestAccess($testAccess): void
    {
        $this->testAccess = $testAccess;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        $this->ctrl->saveParameter($this, "sequence");
        $this->ctrl->saveParameter($this, "active_id");

        switch ($next_class) {
            case 'iltestpassdetailsoverviewtablegui':
                $tableGUI = new ilTestPassDetailsOverviewTableGUI($this->ctrl, $this, 'outUserPassDetails');
                $this->ctrl->forwardCommand($tableGUI);
                break;

            default:
                if (in_array($cmd, ['excel_scored_test_run', 'excel_all_test_runs', 'csv'])) {
                    $ret = $this->exportEvaluation($cmd);
                } else if (in_array($cmd, ['excel_all_test_runs_a', 'csv_a'])) {
                    $ret = $this->exportAggregatedResults($cmd);
                } else if ($cmd === 'certificate') {
                    $ret = $this->exportCertificate();
                } else {
                    $ret = $this->$cmd();
                }
                break;
        }
        return $ret;
    }

    public function &getHeaderNames(): array
    {
        $headernames = [];
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
        array_push($headernames, $this->lng->txt("tst_answered_questions"));
        array_push($headernames, $this->lng->txt("working_time"));
        array_push($headernames, $this->lng->txt("detailed_evaluation"));
        return $headernames;
    }

    public function &getHeaderVars(): array
    {
        $headervars = [];
        if ($this->object->getAnonymity()) {
            array_push($headervars, "counter");
        } else {
            array_push($headervars, "name");
            array_push($headervars, "login");
        }
        array_push($headervars, "resultspoints");
        array_push($headervars, "resultsmarks");
        array_push($headervars, "qworkedthrough");
        array_push($headervars, "timeofwork");
        array_push($headervars, "");
        return $headervars;
    }

    /**
     * @deprecated command should not be used any longer
     */
    public function filterEvaluation()
    {
        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $table_gui = new ilEvaluationAllTableGUI($this, 'outEvaluation', $this->settings);
        $table_gui->writeFilterToSession();
        $this->ctrl->redirect($this, "outEvaluation");
    }

    /**
     * @deprecated command should not be used any longer
     */
    public function resetfilterEvaluation()
    {
        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $table_gui = new ilEvaluationAllTableGUI($this, 'outEvaluation', $this->settings);
        $table_gui->resetFilter();
        $this->ctrl->redirect($this, "outEvaluation");
    }

    public function outEvaluation()
    {
        $ilToolbar = $this->toolbar;

        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_STATISTICS);

        $table_gui = new ilEvaluationAllTableGUI(
            $this,
            'outEvaluation',
            $this->settings,
            $this->object->getAnonymity(),
            $this->object->isOfferingQuestionHintsEnabled()
        );

        $data = [];
        $filter_array = [];

        foreach ($table_gui->getFilterItems() as $item) {
            if (!in_array($item->getValue(), [false, ''])) {
                switch ($item->getPostVar()) {
                    case 'group':
                    case 'name':
                    case 'course':
                        $filter_array[$item->getPostVar()] = $item->getValue();
                        break;
                    case 'passed_only':
                        $passedonly = $item->getChecked();
                        break;
                }
            }
        }

        $eval = new ilTestEvaluationData($this->db, $this->object);
        $eval->setFilterArray($filter_array);
        $foundParticipants = $eval->getParticipants();

        $participantData = new ilTestParticipantData($this->db, $this->lng);
        $participantData->setActiveIdsFilter($eval->getParticipantIds());

        $participantData->setParticipantAccessFilter(
            $this->participant_access_filter->getAccessStatisticsUserFilter($this->ref_id)
        );

        $participantData->load($this->object->getTestId());

        $counter = 1;
        if (count($participantData->getActiveIds()) > 0) {
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
                    $evaluationrow = [];
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
                    $userfields = [];
                    if ($userdata->getUserID() !== null) {
                        $userfields = ilObjUser::_lookupFields($userdata->getUserID());
                    }
                    $evaluationrow['gender'] = $userfields['gender'] ?? '';
                    $evaluationrow['email'] = $userfields['email'] ?? '';
                    $evaluationrow['institution'] = $userfields['institution'] ?? '';
                    $evaluationrow['street'] = $userfields['street'] ?? '';
                    $evaluationrow['city'] = $userfields['city'] ?? '';
                    $evaluationrow['zipcode'] = $userfields['zipcode'] ?? '';
                    $evaluationrow['country'] = $userfields['country'] ?? '';
                    $evaluationrow['sel_country'] = $userfields['sel_country'] ?? '';
                    $evaluationrow['department'] = $userfields['department'] ?? '';
                    $evaluationrow['matriculation'] = $userfields['matriculation'] ?? '';
                    $counter++;
                    $data[] = $evaluationrow;
                }
            }
        }

        $table_gui->setData($data);
        if (count($participantData->getActiveIds()) > 0) {
            $ilToolbar->setFormName('form_output_eval');
            $ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'exportEvaluation'));
            $export_type = new ilSelectInputGUI($this->lng->txt('exp_eval_data'), 'export_type');
            if ($this->getObject() && $this->getObject()->getQuestionSetType() !== ilObjTest::QUESTION_SET_TYPE_RANDOM) {
                $options = array(
                    $this->ui_factory->button()->shy($this->lng->txt('exp_type_excel') . ' (' . $this->lng->txt('exp_scored_test_run') . ')', $this->ctrl->getLinkTarget($this,'excel_scored_test_run')),
                    $this->ui_factory->button()->shy($this->lng->txt('exp_type_excel') . ' (' . $this->lng->txt('exp_all_test_runs') . ')', $this->ctrl->getLinkTarget($this, 'excel_all_test_runs')),
                    $this->ui_factory->button()->shy($this->lng->txt('exp_type_spss'), $this->ctrl->getLinkTarget($this, 'csv'))
                );
            } else {
                $options = array(
                    $this->ui_factory->button()->shy($this->lng->txt('exp_type_excel') . ' (' . $this->lng->txt('exp_all_test_runs') . ')', $this->ctrl->getLinkTarget($this, 'excel_all_test_runs')),
                    $this->ui_factory->button()->shy($this->lng->txt('exp_type_spss'), $this->ctrl->getLinkTarget($this, 'csv'))
                );
            }

            if (!$this->object->getAnonymity()) {
                try {
                    $globalCertificatePrerequisites = new ilCertificateActiveValidator();
                    if ($globalCertificatePrerequisites->validate()) {
                        $options[] = $this->ui_factory->button()->shy($this->lng->txt('exp_type_certificate'), 'certificate');
                    }
                } catch (ilException $e) {
                }
            }

            $select = $this->ui_factory->dropdown()->standard($options)->withLabel($this->lng->txt('exp_eval_data'));
            $ilToolbar->addComponent($select);
        }

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");

        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
        }

        $this->tpl->setContent($table_gui->getHTML());
    }

    public function detailedEvaluation()
    {
        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_STATISTICS);

        $active_id = $this->testrequest->int('active_id');

        if (!$this->getTestAccess()->checkResultsAccessForActiveId($active_id)) {
            ilObjTestGUI::accessViolationRedirect();
        }

        if ($active_id === null) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('detailed_evaluation_missing_active_id'), true);
            $this->ctrl->redirect($this, 'outEvaluation');
        }

        $this->tpl->addCss(ilUtil::getStyleSheetLocation('output', 'test_print.css', 'Modules/Test'), 'print');

        $backBtn = $this->ui_factory->button()->standard($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'outEvaluation'));
        $this->toolbar->addComponent($backBtn);

        $this->object->setAccessFilteredParticipantList(
            $this->object->buildStatisticsAccessFilteredParticipantList()
        );

        $data = $this->object->getCompleteEvaluationData();

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
        $mark = $this->object->getMarkSchema()->getMatchingMark($pct);
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

        $tables = [];

        for ($pass = 0; $pass <= $data->getParticipant($active_id)->getLastPass(); $pass++) {
            $finishdate = ilObjTest::lookupPassResultsUpdateTimestamp($active_id, $pass);
            if ($finishdate > 0) {
                if (($this->testAccess->getAccess()->checkAccess('write', '', $this->testrequest->getRefId()))) {
                    $this->ctrl->setParameter($this, 'statistics', '1');
                    $this->ctrl->setParameter($this, 'active_id', $active_id);
                    $this->ctrl->setParameter($this, 'pass', $pass);
                } else {
                    $this->ctrl->setParameter($this, 'statistics', '');
                    $this->ctrl->setParameter($this, 'active_id', '');
                    $this->ctrl->setParameter($this, 'pass', '');
                }

                $table = new ilTestDetailedEvaluationStatisticsTableGUI($this, 'detailedEvaluation', ($pass + 1) . '_' . $this->object->getId());
                $table->setTitle(sprintf($this->lng->txt("tst_eval_question_points"), $pass + 1));
                if (($this->testAccess->getAccess()->checkAccess('write', '', $this->testrequest->getRefId()))) {
                    $table->addCommandButton('outParticipantsPassDetails', $this->lng->txt('tst_show_answer_sheet'));
                }

                $questions = $data->getParticipant($active_id)->getQuestions($pass);
                if (!is_array($questions)) {
                    $questions = $data->getParticipant($active_id)->getQuestions(0);
                }

                $tableData = [];

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

        $this->tpl->setContent($form->getHTML() . implode('', $tables));
    }

    /**
     * Creates a PDF representation of the answers for a given question in a test
     *
     */
    public function exportQuestionForAllParticipants()
    {
        $question_id = $this->testrequest->int('qid');
        $question_content = $this->getQuestionResultForTestUsers($question_id, $this->object->getTestId());
        $question_title = assQuestion::instantiateQuestion($question_id)->getTitle();
        $page = $this->prepareContentForPrint($question_title, $question_content);
        $this->sendPage($page);
    }

    /**
     * Creates a ZIP file containing all file uploads for a given question in a test
     *
     */
    public function exportFileUploadsForAllParticipants()
    {
        $question_object = assQuestion::instantiateQuestion((int) $this->testrequest->raw("qid"));
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
    */
    public function eval_a()
    {
        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_STATISTICS);

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_anonymous_aggregation.html", "Modules/Test");

        $this->object->setAccessFilteredParticipantList(
            $this->object->buildStatisticsAccessFilteredParticipantList()
        );

        $eval = $this->object->getCompleteEvaluationData();
        $data = [];
        $foundParticipants = $eval->getParticipants();
        if (count($foundParticipants)) {
            $options = [
                $this->ui_factory->button()->shy($this->lng->txt('exp_type_excel'), $this->ctrl->getLinkTarget($this, 'excel_all_test_runs_a')),
                $this->ui_factory->button()->shy($this->lng->txt('exp_type_spss'), $this->ctrl->getLinkTarget($this, 'csv_a'))
            ];

            $select = $this->ui_factory->dropdown()->standard($options)->withLabel($this->lng->txt('exp_eval_data'));
            $this->toolbar->addComponent($select);

            $data[] = array(
                'result' => $this->lng->txt("tst_eval_total_persons"),
                'value' => count($foundParticipants)
            );
            $total_finished = $eval->getTotalFinishedParticipants();
            $data[] = array(
                'result' => $this->lng->txt("tst_eval_total_finished"),
                'value' => $total_finished
            );
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

        $table_gui = new ilTestAggregatedResultsTableGUI($this, 'eval_a');
        $table_gui->setData($data);
        $this->tpl->setVariable('AGGREGATED_RESULTS', $table_gui->getHTML());

        $rows = [];
        $counter = 0;
        foreach ($eval->getQuestionTitles() as $question_id => $question_title) {
            $answered = 0;
            $reached = 0;
            $max = 0;
            foreach ($foundParticipants as $userdata) {
                for ($i = 0; $i <= $userdata->getLastPass(); $i++) {
                    if (is_object($userdata->getPass($i))) {
                        $question = $userdata->getPass($i)->getAnsweredQuestionByQuestionId($question_id);
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
                [
                    'qid' => $question_id,
                    'title' => $question_title,
                    'points' => $points_reached,
                    'points_reached' => $points_reached,
                    'points_max' => $points_max,
                    'percentage' => (float) $percent,
                    'answers' => $answered
                ]
            );
        }
        $table_gui = new ilTestAverageReachedPointsTableGUI($this, 'eval_a');
        $table_gui->setData($rows);
        $this->tpl->setVariable('TBL_AVG_REACHED', $table_gui->getHTML());
    }

    public function exportEvaluation($cmd = "")
    {
        $filterby = ilTestEvaluationData::FILTER_BY_NONE;
        if ($this->testrequest->isset("g_filterby")) {
            $filterby = $this->testrequest->raw("g_filterby");
        }

        $filtertext = "";
        if ($this->testrequest->isset("g_userfilter")) {
            $filtertext = $this->testrequest->raw("g_userfilter");
        }

        $passedonly = false;
        if ($this->testrequest->isset("g_passedonly")) {
            if ($this->testrequest->raw("g_passedonly") == 1) {
                $passedonly = true;
            }
        }

        if($cmd == '') {
            $cmd = $this->testrequest->raw("export_type");
        }
        switch ($cmd) {
            case "excel_scored_test_run":
                (new ilExcelTestExport($this->object, $filterby, $filtertext, $passedonly, true))
                    ->withResultsPage()
                    ->withUserPages()
                    ->deliver($this->object->getTitle() . '_results');
                break;

            case "csv":
                (new ilCSVTestExport($this->object, $filterby, $filtertext, $passedonly))
                    ->withAllResults()
                    ->deliver($this->object->getTitle() . '_results');
                break;

            case "excel_all_test_runs":
                (new ilExcelTestExport($this->object, $filterby, $filtertext, $passedonly, false))
                    ->withResultsPage()
                    ->withUserPages()
                    ->deliver($this->object->getTitle() . '_results');
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

    public function exportAggregatedResults($cmd = '')
    {
        switch ($cmd) {
            case "excel_all_test_runs_a":
                (new ilExcelTestExport($this->object, ilTestEvaluationData::FILTER_BY_NONE, '', false, true))
                    ->withAggregatedResultsPage()
                    ->deliver($this->object->getTitle() . '_aggregated');
                break;
            case "csv_a":
                (new ilCSVTestExport($this->object, ilTestEvaluationData::FILTER_BY_NONE, '', false))
                    ->withAggregatedResults()
                    ->deliver($this->object->getTitle() . '_aggregated');
                break;
        }
    }

    public function exportCertificate()
    {
        $globalCertificatePrerequisites = new ilCertificateActiveValidator();
        if (!$globalCertificatePrerequisites->validate()) {
            $this->er->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $database = $this->db;
        $logger = $this->logging_services->root();

        $pathFactory = new ilCertificatePathFactory();
        $objectId = $this->object->getId();
        $zipAction = new ilUserCertificateZip(
            $objectId,
            $pathFactory->create($this->object)
        );

        $archive_dir = $zipAction->createArchiveDirectory();

        $this->object->setAccessFilteredParticipantList(
            $this->object->buildStatisticsAccessFilteredParticipantList()
        );

        $ilUserCertificateRepository = new ilUserCertificateRepository($database, $logger);
        $pdfGenerator = new ilPdfGenerator($ilUserCertificateRepository);

        $total_users = $this->object->evalTotalPersonsArray();
        if (count($total_users)) {
            $certValidator = new ilCertificateDownloadValidator();

            foreach ($total_users as $active_id => $name) {
                $user_id = $this->object->_getUserIdFromActiveId($active_id);

                if (!$certValidator->isCertificateDownloadable($user_id, $objectId)) {
                    continue;
                }

                $pdfAction = new ilCertificatePdfAction(
                    $pdfGenerator,
                    new ilCertificateUtilHelper(),
                    $this->lng->txt('error_creating_certificate_pdf')
                );

                $pdf = $pdfAction->createPDF($user_id, $objectId);
                if (strlen($pdf)) {
                    $zipAction->addPDFtoArchiveDirectory($pdf, $archive_dir, $user_id . "_" . str_replace(
                        " ",
                        "_",
                        ilFileUtils::getASCIIFilename($name)
                    ) . ".pdf");
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
    */
    public function outParticipantsPassDetails()
    {
        $ilTabs = $this->tabs;
        $ilObjDataCache = $this->obj_cache;

        $active_id = (int) $this->testrequest->raw("active_id");

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
        $pass = (int) $this->testrequest->raw("pass");

        if ($this->testrequest->isset('statistics') && $this->testrequest->raw('statistics') == 1) {
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

        if ($this->testrequest->isset('show_best_solutions')) {
            ilSession::set('tst_results_show_best_solutions', true);
        } elseif ($this->testrequest->isset('hide_best_solutions')) {
            ilSession::set('tst_results_show_best_solutions', false);
        } elseif (ilSession::get('tst_results_show_best_solutions') !== null) {
            ilSession::clear('tst_results_show_best_solutions');
        }

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
        }

        $template = new ilTemplate("tpl.il_as_tst_pass_details_overview_participants.html", true, true, "Modules/Test");

        $this->populateExamId($template, $active_id, (int) $pass);
        $this->populatePassFinishDate($template, ilObjTest::lookupLastTestPassAccess($active_id, $pass));


        $toolbar = $this->buildUserTestResultsToolbarGUI();
        if (ilSession::get('tst_results_show_best_solutions')) {
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

        $title = sprintf(
            $this->lng->txt("tst_result_user_name_pass"),
            $pass + 1,
            ilObjUser::_lookupFullname($this->object->_getUserIdFromActiveId($active_id))
        );

        $pass_results = $this->results_factory->getPassResultsFor(
            $this->object,
            $active_id,
            $pass,
            false
        );

        $table = $this->results_presentation_factory->getPassResultsPresentationTable(
            $pass_results,
            $title
        );

        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));

        $this->tpl->setVariable(
            "ADM_CONTENT",
            $template->get()
            . $table->render()
        );
    }

    public function outParticipantsResultsOverview()
    {
        $ilTabs = $this->tabs;
        $ilObjDataCache = $this->obj_cache;

        $active_id = (int) $this->testrequest->raw("active_id");

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

        $template = new ilTemplate("tpl.il_as_tst_pass_overview_participants.html", true, true, "Modules/Test");

        $toolbar = $this->buildUserTestResultsToolbarGUI();
        $toolbar->build();
        $template->setVariable('RESULTS_TOOLBAR', $this->ctrl->getHTML($toolbar));

        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $ilObjDataCache);
        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($testSession->getObjectiveOrientedContainerId());
            $testResultHeaderLabelBuilder->setUserId($testSession->getUserId());
            $testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
            $testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
            $testResultHeaderLabelBuilder->initObjectiveOrientedMode();
        }

        $testPassesSelector = new ilTestPassesSelector($this->db, $this->object);
        $testPassesSelector->setActiveId($testSession->getActiveId());
        $testPassesSelector->setLastFinishedPass($testSession->getLastFinishedPass());

        $passOverViewTableGUI = $this->buildPassOverviewTableGUI($this);
        $passOverViewTableGUI->setActiveId($testSession->getActiveId());
        $passOverViewTableGUI->setResultPresentationEnabled(true);
        $passOverViewTableGUI->setPassDetailsCommand('outParticipantsPassDetails');
        $passOverViewTableGUI->init();
        $passOverViewTableGUI->setData($this->getPassOverviewTableData($testSession, $testPassesSelector->getExistingPasses(), true));
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

        $this->tpl->setVariable("ADM_CONTENT", $template->get());
    }

    public function outUserPassDetails(): void
    {
        $this->tabs->clearSubTabs();
        $this->tabs->setBackTarget($this->lng->txt('tst_results_back_overview'), $this->ctrl->getLinkTarget($this));

        $testSession = $this->testSessionFactory->getSession();

        if (!$this->object->getShowPassDetails()) {
            $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
        }

        $active_id = $testSession->getActiveId();
        $user_id = $testSession->getUserId();

        $this->ctrl->saveParameter($this, "pass");
        $pass = $this->testrequest->int("pass");

        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $this->obj_cache);

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
        if ($this->object->getShowSolutionListComparison()) {
            $command_solution_details = "outCorrectSolution";
        }

        $tpl = new ilTemplate('tpl.il_as_tst_pass_details_overview_participants.html', true, true, "Modules/Test");

        $toolbar = $this->buildUserTestResultsToolbarGUI();

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
            if ($this->object->isShowExamIdInTestResultsEnabled()) {
                $tpl->setVariable('EXAM_ID', ilObjTest::lookupExamId(
                    $testSession->getActiveId(),
                    $pass
                ));
                $tpl->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
            }
        }

        if (!$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired() &&
            $this->isGradingMessageRequired() && $this->object->getNrOfTries() == 1) {
            $gradingMessageBuilder = $this->getGradingMessageBuilder($active_id);
            $gradingMessageBuilder->buildMessage();
            $gradingMessageBuilder->sendMessage();
        }

        $data = $this->object->getCompleteEvaluationData();
        $percent = $data->getParticipant($active_id)->getPass($pass)->getReachedPoints() / $data->getParticipant($active_id)->getPass($pass)->getMaxPoints() * 100;
        $result = $data->getParticipant($active_id)->getPass($pass)->getReachedPoints() . " " . strtolower($this->lng->txt("of")) . " " . $data->getParticipant($active_id)->getPass($pass)->getMaxPoints() . " (" . sprintf("%2.2f", $percent) . " %" . ")";
        $tpl->setCurrentBlock('total_score');
        $tpl->setVariable("TOTAL_RESULT_TEXT", $this->lng->txt('tst_stat_result_resultspoints'));
        $tpl->setVariable("TOTAL_RESULT", $result);
        $tpl->parseCurrentBlock();

        $tpl->setVariable("TEXT_RESULTS", $testResultHeaderLabelBuilder->getPassDetailsHeaderLabel($pass + 1));
        $tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

        $this->populateExamId($tpl, $active_id, (int) $pass);
        $this->populatePassFinishDate($tpl, ilObjTest::lookupLastTestPassAccess($active_id, $pass));

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
        }

        $title = sprintf(
            $this->lng->txt("tst_result_user_name_pass"),
            $pass + 1,
            ilObjUser::_lookupFullname($this->object->_getUserIdFromActiveId($active_id))
        );

        $pass_results = $this->results_factory->getPassResultsFor(
            $this->object,
            $active_id,
            $pass,
            true
        );

        $table = $this->results_presentation_factory->getPassResultsPresentationTable(
            $pass_results,
            $title
        );

        $tpl->setVariable("LIST_OF_ANSWERS", $table->render());

        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));

        $this->tpl->setContent(
            $tpl->get()
        );
    }

    public function outUserResultsOverview()
    {
        $testSession = $this->testSessionFactory->getSession();
        $active_id = $testSession->getActiveId();
        $user_id = $this->user->getId();
        $uname = $this->object->userLookupFullName($user_id, true);

        if (!$this->object->canShowTestResults($testSession)) {
            $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
        }

        $templatehead = new ilTemplate("tpl.il_as_tst_results_participants.html", true, true, "Modules/Test");
        $template = new ilTemplate("tpl.il_as_tst_results_participant.html", true, true, "Modules/Test");

        $toolbar = $this->buildUserTestResultsToolbarGUI();

        $validator = new ilCertificateDownloadValidator();
        if ($validator->isCertificateDownloadable($user_id, $this->object->getId())) {
            $toolbar->setCertificateLinkTarget($this->ctrl->getLinkTarget($this, 'outCertificate'));
        }

        $toolbar->build();

        $templatehead->setVariable('RESULTS_TOOLBAR', $this->ctrl->getHTML($toolbar));

        $passDetailsEnabled = $this->object->getShowPassDetails();

        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $this->obj_cache);
        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $testResultHeaderLabelBuilder->setObjectiveOrientedContainerId($testSession->getObjectiveOrientedContainerId());
            $testResultHeaderLabelBuilder->setUserId($testSession->getUserId());
            $testResultHeaderLabelBuilder->setTestObjId($this->object->getId());
            $testResultHeaderLabelBuilder->setTestRefId($this->object->getRefId());
            $testResultHeaderLabelBuilder->initObjectiveOrientedMode();
        }

        $template->setCurrentBlock("pass_overview");

        $testPassesSelector = new ilTestPassesSelector($this->db, $this->object);
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
            $loStatus = new ilTestLearningObjectivesStatusGUI($this->lng, $this->ctrl, $this->testrequest);
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

        $this->tpl->setContent($templatehead->get());
    }

    public function outUserListOfAnswerPasses()
    {
        if (!$this->object->getShowSolutionPrintview()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_permission"), true);
            $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
        }

        $template = new ilTemplate("tpl.il_as_tst_info_list_of_answers.html", true, true, "Modules/Test");

        $pass = null;
        if ($this->testrequest->isset('pass')) {
            $pass = $this->testrequest->int('pass');
        }
        $user_id = $this->user->getId();

        $testSession = $this->testSessionFactory->getSession();
        $active_id = $testSession->getActiveId();

        $template->setVariable("TEXT_RESULTS", $this->lng->txt("tst_passes"));

        $testPassesSelector = new ilTestPassesSelector($this->db, $this->object);
        $testPassesSelector->setActiveId($testSession->getActiveId());
        $testPassesSelector->setLastFinishedPass($testSession->getLastFinishedPass());

        $passOverViewTableGUI = $this->buildPassOverviewTableGUI($this);
        $passOverViewTableGUI->setActiveId($testSession->getActiveId());
        $passOverViewTableGUI->setResultPresentationEnabled(false);
        $passOverViewTableGUI->setPassDetailsCommand('outUserListOfAnswerPasses');
        $passOverViewTableGUI->init();
        $passOverViewTableGUI->setData($this->getPassOverviewTableData($testSession, $testPassesSelector->getClosedPasses(), false));
        $template->setVariable("PASS_OVERVIEW", $passOverViewTableGUI->getHTML());

        $signature = '';
        if ($pass !== null) {
            $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($this->lng, $this->obj_cache);

            $objectivesList = null;

            if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
                $testSequence = $this->testSequenceFactory->getSequenceByActiveIdAndPass($active_id, $pass);
                $testSequence->loadFromDb();
                $testSequence->loadQuestions();

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
            $user_id = $this->object->_getUserIdFromActiveId($active_id);
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

    public function passDetails()
    {
        // @PHP8-CR: With this probably never working and no detectable usages, it would be a candidate for removal...
        // Second opinion here, please, if it can go away.
        if ($this->testrequest->isset("pass") && (strlen($this->testrequest->raw("pass")) > 0)) {
            $this->ctrl->saveParameter($this, "pass");
            $this->ctrl->saveParameter($this, "active_id");
            $this->outTestResults(false, $this->testrequest->raw("pass"));
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
        if (!$this->getTestAccess()->checkStatisticsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $this->object->setAccessFilteredParticipantList(
            $this->object->buildStatisticsAccessFilteredParticipantList()
        );

        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_STATISTICS);

        $data = $this->object->getCompleteEvaluationData();
        $color_class = array("tblrow1", "tblrow2");
        $counter = 0;
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_single_answers.html", "Modules/Test");
        $foundParticipants = $data->getParticipants();
        if (count($foundParticipants) == 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("tst_no_evaluation_data"));
            return;
        } else {
            $rows = [];
            foreach ($data->getQuestionTitles() as $question_id => $question_title) {
                $answered = 0;
                $reached = 0;
                $max = 0;
                foreach ($foundParticipants as $userdata) {
                    $pass = $userdata->getScoredPass();
                    if (is_object($userdata->getPass($pass))) {
                        $question = $userdata->getPass($pass)->getAnsweredQuestionByQuestionId($question_id);
                        if (is_array($question)) {
                            $answered++;
                        }
                    }
                }
                $counter++;
                $this->ctrl->setParameter($this, "qid", $question_id);
                $question_object = assQuestion::instantiateQuestion($question_id);
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
                        'output' => "<a target='_blank' href=\"" . $this->ctrl->getLinkTarget($this, "exportQuestionForAllParticipants") . "\">" . $this->lng->txt("print") . "</a>",
                        'file_uploads' => $download
                    )
                );
            }
            if (count($rows)) {
                $table_gui = new ilResultsByQuestionTableGUI($this, "singleResults");
                $table_gui->setTitle($this->lng->txt("tst_answered_questions_test"));
                $table_gui->setData($rows);

                $this->tpl->setVariable("TBL_SINGLE_ANSWERS", $table_gui->getHTML());
            } else {
                $this->tpl->setVariable("TBL_SINGLE_ANSWERS", $this->lng->txt("adm_no_special_users"));
            }
        }
    }

    public function outCertificate()
    {
        $ilUserCertificateRepository = new ilUserCertificateRepository($this->db, $this->logging_services);
        $pdfGenerator = new ilPdfGenerator($ilUserCertificateRepository);

        $pdfAction = new ilCertificatePdfAction(
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $this->lng->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf($this->user->getId(), $this->object->getId());
    }

    public function confirmDeletePass()
    {
        if ($this->testrequest->isset('context') && strlen($this->testrequest->raw('context'))) {
            $context = $this->testrequest->raw('context');
        } else {
            $context = ilTestPassDeletionConfirmationGUI::CONTEXT_PASS_OVERVIEW;
        }

        if (!$this->object->isPassDeletionAllowed()) {
            $this->redirectToPassDeletionContext($context);
        }

        $confirm = new ilTestPassDeletionConfirmationGUI($this->ctrl, $this->lng, $this);
        $confirm->build((int) $this->testrequest->raw("active_id"), (int) $this->testrequest->raw("pass"), $context);

        $this->tpl->setContent($this->ctrl->getHTML($confirm));
    }

    public function cancelDeletePass()
    {
        $this->redirectToPassDeletionContext($_POST['context']);
    }

    private function redirectToPassDeletionContext($context)
    {
        switch ($context) {
            case ilTestPassDeletionConfirmationGUI::CONTEXT_PASS_OVERVIEW:

                $this->ctrl->redirect($this, 'outUserResultsOverview');

                // no break
            case ilTestPassDeletionConfirmationGUI::CONTEXT_INFO_SCREEN:

                $this->ctrl->redirectByClass('ilObjTestGUI', 'infoScreen');
        }
    }

    public function performDeletePass()
    {
        if (isset($_POST['context']) && strlen($_POST['context'])) {
            $context = $_POST['context'];
        } else {
            $context = ilTestPassDeletionConfirmationGUI::CONTEXT_PASS_OVERVIEW;
        }

        if (!$this->object->isPassDeletionAllowed()) {
            $this->redirectToPassDeletionContext($context);
        }

        $ilDB = $this->db;

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

        if ($pass == $this->object->_getResultPass($active_fi)) {
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

        if ($isActivePass) {
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

        // qpl_hint_tracking
        $ilDB->manipulate(
            'DELETE
				FROM qpl_hint_tracking
				WHERE qhtr_active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND qhtr_pass = ' . $ilDB->quote($pass, 'integer')
        );

        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE qpl_hint_tracking
				SET qhtr_pass = qhtr_pass - 1
				WHERE qhtr_active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND qhtr_pass > ' . $ilDB->quote($pass, 'integer')
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

        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            $this->object->logAction($this->lng->txtlng("assessment", "log_deleted_pass", ilObjAssessmentFolder::_getLogLanguage()));
        }

        $this->object->updateTestResultCache($active_fi);

        $this->redirectToPassDeletionContext($context);
    }

    protected function getFilteredTestResult(int $active_id, int $pass, bool $considerHiddenQuestions, bool $considerOptionalQuestions): array
    {
        $component_repository = $this->component_repository;
        $ilDB = $this->db;

        $resultData = $this->object->getTestResult($active_id, $pass, false, $considerHiddenQuestions);
        $questionIds = [];
        foreach ($resultData as $resultItemKey => $resultItemValue) {
            if ($resultItemKey === 'test' || $resultItemKey === 'pass') {
                continue;
            }

            $questionIds[] = $resultItemValue['qid'];
        }

        $table_gui = $this->buildPassDetailsOverviewTableGUI($this, 'outUserPassDetails');

        $questionList = new ilAssQuestionList($ilDB, $this->lng, $component_repository);
        $questionList->setParentObjId($this->object->getId());
        $questionList->setParentObjectType($this->object->getType());
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

        $filteredTestResult = [];

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
        $active_id = (int) $this->testrequest->raw("active_id");
        $access_filter = $this->participant_access_filter->getManageParticipantsUserFilter($this->ref_id);

        $participant_data = new ilTestParticipantData($this->db, $this->lng);
        $participant_data->setActiveIdsFilter([$active_id]);
        $participant_data->setParticipantAccessFilter($access_filter);
        $participant_data->load($this->object->getTestId());

        if (!in_array($active_id, $participant_data->getActiveIds())) {
            $this->redirectBackToParticipantsScreen();
        }

        if (($this->object->isEndingTimeEnabled() || $this->object->getEnableProcessingTime())
            && !$this->object->endingTimeReached()
            && !$this->object->isMaxProcessingTimeReached(
                $this->object->getStartingTimeOfUser($active_id),
                $active_id
            )) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('finish_pass_for_user_in_processing_time'));
        }

        $cgui = new ilConfirmationGUI();

        $cgui->setHeaderText(sprintf(
            $this->lng->txt("finish_pass_for_user_confirmation"),
            $participant_data->getFormatedFullnameByActiveId($active_id)
        ));

        $this->ctrl->setParameter($this, 'active_id', $active_id);
        $cgui->setFormAction($this->ctrl->getFormAction($this, "participants"));

        $cgui->setCancel($this->lng->txt("cancel"), "redirectBackToParticipantsScreen");
        $cgui->setConfirm($this->lng->txt("proceed"), "confirmFinishTestPassForUser");

        $this->tpl->setContent($cgui->getHTML());
    }

    public function confirmFinishTestPassForUser()
    {
        $active_id = (int) $this->testrequest->raw("active_id");
        $access_filter = $this->participant_access_filter->getManageParticipantsUserFilter($this->ref_id);

        $participant_data = new ilTestParticipantData($this->db, $this->lng);
        $participant_data->setActiveIdsFilter(array($active_id));
        $participant_data->setParticipantAccessFilter($access_filter);
        $participant_data->load($this->object->getTestId());

        if (in_array($active_id, $participant_data->getActiveIds())) {
            $testSession = new ilTestSession($this->db, $this->user);
            $testSession->loadFromDb($active_id);

            $this->object->updateTestPassResults(
                $active_id,
                $testSession->getPass(),
                $this->object->areObligationsEnabled(),
                null,
                $this->object->getId()
            );

            $this->finishTestPass($active_id, $this->object->getId());
        }


        $this->redirectBackToParticipantsScreen();
    }

    public function finishAllUserPasses()
    {
        if ($this->hasUsersWithWorkingTimeAvailable()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('finish_pass_for_all_users_in_processing_time'),
                true
            );
            $this->redirectBackToParticipantsScreen();
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("finish_pass_for_all_users"));
        $cgui->setCancel($this->lng->txt("cancel"), "redirectBackToParticipantsScreen");
        $cgui->setConfirm($this->lng->txt("proceed"), "confirmFinishTestPassForAllUser");
        $this->tpl->setContent($cgui->getHTML());
    }

    private function hasUsersWithWorkingTimeAvailable(): bool
    {
        if (!$this->object->isEndingTimeEnabled() && !$this->object->getEnableProcessingTime()
            || $this->object->endingTimeReached()) {
            return false;
        }

        $access_filter = $this->participant_access_filter->getManageParticipantsUserFilter($this->ref_id);
        $participant_list = new ilTestParticipantList($this->object, $this->user, $this->lng, $this->db);
        $participant_list->initializeFromDbRows($this->object->getTestParticipants());

        foreach ($participant_list->getAccessFilteredList($access_filter) as $participant) {
            if ($participant->hasUnfinishedPasses()
                && !$this->object->isMaxProcessingTimeReached(
                    $this->object->getStartingTimeOfUser($participant->getActiveId()),
                    $participant->getActiveId()
                )) {
                return true;
            }
        }

        return false;
    }

    public function confirmFinishTestPassForAllUser()
    {
        $accessFilter = $this->participant_access_filter->getManageParticipantsUserFilter($this->ref_id);

        $participant_list = new ilTestParticipantList($this->object, $this->user, $this->lng, $this->db);
        $participant_list->initializeFromDbRows($this->object->getTestParticipants());
        $filtered_participant_list = $participant_list->getAccessFilteredList($accessFilter);

        foreach ($filtered_participant_list as $participant) {
            if (!$participant->hasUnfinishedPasses()) {
                continue;
            }

            $test_session = new ilTestSession($this->db, $this->user);
            $test_session->loadFromDb($participant->getActiveId());

            $this->object->updateTestPassResults(
                $participant->getActiveId(),
                $test_session->getPass(),
                $this->object->areObligationsEnabled(),
                null,
                $this->object->getId()
            );

            $this->finishTestPass($participant->getActiveId(), $this->object->getId());
        }


        $this->redirectBackToParticipantsScreen();
    }

    protected function finishTestPass($active_id, $obj_id)
    {
        $process_locker = $this->processLockerFactory->withContextId((int) $active_id)->getLocker();

        $test_pass_finisher = new ilTestPassFinishTasks($this->testSessionFactory->getSession(), $obj_id);
        $test_pass_finisher->performFinishTasks($process_locker);
    }

    protected function redirectBackToParticipantsScreen()
    {
        $this->ctrl->redirectByClass("ilTestParticipantsGUI");
    }

    public function getObject(): ?ilObjTest
    {
        return $this->object;
    }

    protected function prepareContentForPrint(string $question_title, string $question_content): string
    {
        $tpl = new ilGlobalTemplate(
            "tpl.question_statistics_print_view.html",
            true,
            true,
            "Modules/Test"
        );

        $tpl->addCss(\ilUtil::getStyleSheetLocation("filesystem"));
        $tpl->addCss(\ilObjStyleSheet::getContentPrintStyle());
        $tpl->addCss(\ilObjStyleSheet::getSyntaxStylePath());
        $tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");

        ilMathJax::getInstance()->includeMathJax($tpl);

        foreach ($this->global_screen->layout()->meta()->getJs()->getItemsInOrderOfDelivery() as $js) {
            $path = explode("?", $js->getContent());
            $file = $path[0];
            $tpl->addJavaScript($file, $js->addVersionNumber());
        }
        foreach ($this->global_screen->layout()->meta()->getOnLoadCode()->getItemsInOrderOfDelivery() as $code) {
            $tpl->addOnLoadCode($code->getContent());
        }

        $tpl->addOnLoadCode("il.Util.print();");

        $tpl->setVariable("QUESTION_TITLE", $question_title);
        $tpl->setVariable("QUESTION_CONTENT", $question_content);
        return $tpl->printToString();
    }

    protected function sendPage(string $page)
    {
        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($page)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }
}
