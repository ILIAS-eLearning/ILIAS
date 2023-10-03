<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Modules/Test/classes/inc.AssessmentConstants.php';
include_once 'Modules/Test/classes/class.ilTestScoringGUI.php';

/**
 * ilTestScoringByQuestionsGUI
 * @author     Michael Jansen <mjansen@databay.de>
 * @author     Björn Heyser <bheyser@databay.de>
 * @version    $Id$
 * @ingroup    ModulesTest
 * @extends    ilTestServiceGUI
 */
class ilTestScoringByQuestionsGUI extends ilTestScoringGUI
{
    const ONLY_FINALIZED = 1;
    const EXCEPT_FINALIZED = 2;

    /**
     * @param ilObjTest $a_object
     */
    public function __construct(ilObjTest $a_object)
    {
        parent::__construct($a_object);
    }

    /**
     * @return string
     */
    protected function getDefaultCommand()
    {
        return 'showManScoringByQuestionParticipantsTable';
    }

    /**
     * @return string
     */
    protected function getActiveSubTabId()
    {
        return 'man_scoring_by_qst';
    }

    /**
     * @param array $manPointsPost
     */
    protected function showManScoringByQuestionParticipantsTable($manPointsPost = array())
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();
        $ilAccess = $DIC->access();

        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_MANUAL_SCORING);

        if (
            false == $ilAccess->checkAccess("write", "", $this->ref_id) &&
            false == $ilAccess->checkAccess("man_scoring_access", "", $this->ref_id)
        ) {
            ilUtil::sendInfo($this->lng->txt('cannot_edit_test'), true);
            $this->ctrl->redirectByClass('ilobjtestgui', 'infoScreen');
        }

        iljQueryUtil::initjQuery();
        ilYuiUtil::initPanel();
        ilYuiUtil::initOverlay();

        $mathJaxSetting = new ilSetting('MathJax');

        if ($mathJaxSetting->get("enable")) {
            $tpl->addJavaScript($mathJaxSetting->get("path_to_mathjax"));
        }

        $tpl->addJavaScript("./Services/JavaScript/js/Basic.js");
        $tpl->addJavaScript("./Services/Form/js/Form.js");
        $tpl->addJavascript('./Services/UIComponent/Modal/js/Modal.js');
        $this->lng->toJSMap(['answer' => $this->lng->txt('answer')]);

        $table = new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this);
        $table->setManualScoringPointsPostData($manPointsPost);

        $qst_id = $table->getFilterItemByPostVar('question')->getValue();
        $passNr = $table->getFilterItemByPostVar('pass')->getValue();
        $finalized_filter = $table->getFilterItemByPostVar('finalize_evaluation')->getValue();
        $answered_filter = $table->getFilterItemByPostVar('only_answered')->getChecked();
        $table_data = [];
        $selected_questionData = null;
        $complete_feedback = $this->object->getCompleteManualFeedback($qst_id);

        if (is_numeric($qst_id)) {
            $info = assQuestion::_getQuestionInfo($qst_id);
            $selected_questionData = $info;
        }

        if ($selected_questionData && is_numeric($passNr)) {
            $data = $this->object->getCompleteEvaluationData(false);
            $participants = $data->getParticipants();
            $participantData = new ilTestParticipantData($DIC->database(), $DIC->language());
            $participantData->setActiveIdsFilter(array_keys($data->getParticipants()));
            $participantData->setParticipantAccessFilter(
                ilTestParticipantAccessFilter::getScoreParticipantsUserFilter($this->ref_id)
            );
            $participantData->load($this->object->getTestId());

            foreach ($participantData->getActiveIds() as $active_id) {
                $participant = $participants[$active_id];
                $testResultData = $this->object->getTestResult($active_id, $passNr - 1);

                foreach ($testResultData as $questionData) {
                    $feedback = [];

                    if (isset($complete_feedback[$active_id][$passNr - 1][$qst_id])) {
                        $feedback = $complete_feedback[$active_id][$passNr - 1][$qst_id];
                    }

                    if (false == isset($feedback['finalized_evaluation'])) {
                        $feedback['finalized_evaluation'] = "";
                    }

                    $check_filter =
                        ($finalized_filter != self::ONLY_FINALIZED || $feedback['finalized_evaluation'] == 1) &&
                        ($finalized_filter != self::EXCEPT_FINALIZED || $feedback['finalized_evaluation'] != 1);

                    $check_answered = ($answered_filter == false || $questionData['answered']);

                    if (
                        isset($questionData['qid']) &&
                        $questionData['qid'] == $selected_questionData['question_id'] &&
                        $check_filter &&
                        $check_answered
                    ) {
                        $table_data[] = [
                            'pass_id' => $passNr - 1,
                            'active_id' => $active_id,
                            'qst_id' => $questionData['qid'],
                            'reached_points' => assQuestion::_getReachedPoints($active_id, $questionData['qid'], $passNr - 1),
                            'maximum_points' => assQuestion::_getMaximumPoints($questionData['qid']),
                'name' => $participant->getName()
                        ] + $feedback;
                    }
                }
            }
        } else {
            $table->disable('header');
        }

        $table->setTitle($this->lng->txt('tst_man_scoring_by_qst'));

        if ($selected_questionData) {
            $maxpoints = assQuestion::_getMaximumPoints($selected_questionData['question_id']);
            $table->setCurQuestionMaxPoints($maxpoints);
            $maxpoints = ' (' . $maxpoints . ' ' . $this->lng->txt('points') . ')';
            if ($maxpoints == 1) {
                $maxpoints = ' (' . $maxpoints . ' ' . $this->lng->txt('point') . ')';
            }

            $table->setTitle(
                $this->lng->txt('tst_man_scoring_by_qst') . ': ' . $selected_questionData['title'] . $maxpoints .
                ' [' . $this->lng->txt('question_id_short') . ': ' . $selected_questionData['question_id'] . ']'
            );
        }

        $table->setData($table_data);
        $tpl->setContent($table->getHTML());
    }

    /**
     * @param bool $ajax
     */
    protected function saveManScoringByQuestion($ajax = false)
    {
        global $DIC;
        $ilAccess = $DIC->access();

        if (
            false == $ilAccess->checkAccess("write", "", $this->ref_id) &&
            false == $ilAccess->checkAccess("man_scoring_access", "", $this->ref_id)
        ) {
            if ($ajax) {
                echo $this->lng->txt('cannot_edit_test');
                exit();
            }

            ilUtil::sendInfo($this->lng->txt('cannot_edit_test'), true);
            $this->ctrl->redirectByClass('ilobjtestgui', 'infoScreen');
        }

        if (false == isset($_POST['scoring']) || false == is_array($_POST['scoring'])) {
            ilUtil::sendFailure($this->lng->txt('tst_save_manscoring_failed_unknown'));
            $this->showManScoringByQuestionParticipantsTable();
            return;
        }

        $pass = key($_POST['scoring']);
        $activeData = current($_POST['scoring']);
        $participantData = new ilTestParticipantData($DIC->database(), $DIC->language());
        $manPointsPost = [];
        $skipParticipant = [];
        $maxPointsByQuestionId = [];

        $participantData->setActiveIdsFilter(array_keys($activeData));
        $participantData->setParticipantAccessFilter(
            ilTestParticipantAccessFilter::getScoreParticipantsUserFilter($this->ref_id)
        );
        $participantData->load($this->object->getTestId());

        foreach ($participantData->getActiveIds() as $active_id) {
            $questions = $activeData[$active_id];

            // check for existing test result data
            if (!$this->object->getTestResult($active_id, $pass)) {
                if (false == isset($skipParticipant[$pass])) {
                    $skipParticipant[$pass] = [];
                }
                $skipParticipant[$pass][$active_id] = true;

                continue;
            }

            foreach ((array) $questions as $qst_id => $reached_points) {
                if (false == isset($manPointsPost[$pass])) {
                    $manPointsPost[$pass] = [];
                }
                if (false == isset($manPointsPost[$pass][$active_id])) {
                    $manPointsPost[$pass][$active_id] = [];
                }

                $feedback_text = $this->retrieveFeedback($active_id, $qst_id, $pass);

                /**
                 * 26.09.23 sk: Ok, this is a hack, but to do this right we need
                 * to go through the whole flow here. It is very convoluted and
                 * I'm unsure what would happen if I would really change something.
                 * This feature is in urgent need of refactoring and a repo.
                 */
                $current_feedback_info = ilObjTest::getSingleManualFeedback($active_id, $qst_id, $pass);
                if (isset($current_feedback_info['finalized_evaluation']) && $current_feedback_info['finalized_evaluation'] === 1) {
                    $reached_points = assQuestion::_getReachedPoints($active_id, $qst_id, $pass);
                    $feedback_text = $current_feedback_info['feedback'];
                }

                $maxPointsByQuestionId[$qst_id] = assQuestion::_getMaximumPoints($qst_id);
                $manPointsPost[$pass][$active_id][$qst_id] = $reached_points;

                if ($reached_points > $maxPointsByQuestionId[$qst_id]) {
                    ilUtil::sendFailure(sprintf($this->lng->txt('tst_save_manscoring_failed'), $pass + 1));
                    $this->showManScoringByQuestionParticipantsTable($manPointsPost);
                    return;
                }
            }
        }

        $changed_one = false;
        $lastAndHopefullyCurrentQuestionId = null;

        foreach ($participantData->getActiveIds() as $active_id) {
            $questions = $activeData[$active_id];
            $update_participant = false;

            if (false == $skipParticipant[$pass][$active_id]) {
                foreach ((array) $questions as $qst_id => $reached_points) {
                    $this->saveFinalization((int) $active_id, (int) $qst_id, (int) $pass, $feedback_text, $ajax);
                    // fix #35543: save manual points only if they differ from the existing points
                    // this prevents a question being set to "answered" if only feedback is entered
                    $old_points = assQuestion::_getReachedPoints($active_id, $qst_id, $pass);
                    if ($reached_points != $old_points) {
                        $update_participant = assQuestion::_setReachedPoints(
                            $active_id,
                            $qst_id,
                            $reached_points,
                            $maxPointsByQuestionId[$qst_id],
                            $pass,
                            1,
                            $this->object->areObligationsEnabled()
                        );
                    }
                }

                if ($update_participant) {
                    ilLPStatusWrapper::_updateStatus(
                        $this->object->getId(),
                        ilObjTestAccess::_getParticipantId($active_id)
                    );
                }

                $changed_one = true;
                $lastAndHopefullyCurrentQuestionId = $qst_id;
            }
        }

        $correction_feedback = [];
        $correction_points = 0;

        if ($changed_one) {
            $qTitle = '';

            if ($lastAndHopefullyCurrentQuestionId) {
                $question = assQuestion::_instantiateQuestion($lastAndHopefullyCurrentQuestionId);
                $qTitle = $question->getTitle();
            }

            $msg = sprintf(
                $this->lng->txt('tst_saved_manscoring_by_question_successfully'),
                $qTitle,
                $pass + 1
            );

            ilUtil::sendSuccess($msg, true);

            if (isset($active_id)) {
                $correction_feedback = $this->object->getSingleManualFeedback($active_id, $qst_id, $pass);
                $correction_points = assQuestion::_getReachedPoints($active_id, $qst_id, $pass);
            }
        }

        if ($ajax && is_array($correction_feedback)) {
            $correction_feedback['finalized_by'] = ilObjUser::_lookupFullname($correction_feedback['finalized_by_usr_id']);
            $correction_feedback['finalized_on_date'] = '';

            if (strlen($correction_feedback['finalized_tstamp']) > 0) {
                $time = new ilDateTime($correction_feedback['finalized_tstamp'], IL_CAL_UNIX);
                $correction_feedback['finalized_on_date'] = $time->get(IL_CAL_DATETIME);
            }

            if (!$correction_feedback['feedback']) {
                $correction_feedback['feedback'] = [];
            }
            if ($correction_feedback['finalized_evaluation'] == 1) {
                $correction_feedback['finalized_evaluation'] = $this->lng->txt('yes');
            } else {
                $correction_feedback['finalized_evaluation'] = $this->lng->txt('no');
            }
            echo json_encode([ 'feedback' => $correction_feedback, 'points' => $correction_points, "translation" => ['yes' => $this->lng->txt('yes'), 'no' => $this->lng->txt('no')]]);
            exit();
        } else {
            $this->showManScoringByQuestionParticipantsTable();
        }
    }

    /**
     *
     */
    protected function applyManScoringByQuestionFilter()
    {
        $table = new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this);
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->showManScoringByQuestionParticipantsTable();
    }

    /**
     *
     */
    protected function resetManScoringByQuestionFilter()
    {
        $table = new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this);
        $table->resetOffset();
        $table->resetFilter();
        $this->showManScoringByQuestionParticipantsTable();
    }

    protected function getAnswerDetail()
    {
        $active_id = (int) $_GET['active_id'];
        $pass = (int) $_GET['pass_id'];
        $question_id = (int) $_GET['qst_id'];

        if (!$this->getTestAccess()->checkScoreParticipantsAccessForActiveId($active_id)) {
            exit; // illegal ajax call
        }

        $data = $this->object->getCompleteEvaluationData(false);
        $participant = $data->getParticipant($active_id);
        $question_gui = $this->object->createQuestionGUI('', $question_id);
        $tmp_tpl = new ilTemplate('tpl.il_as_tst_correct_solution_output.html', true, true, 'Modules/Test');
        if ($question_gui->supportsIntermediateSolutionOutput() && $question_gui->hasIntermediateSolution($active_id, $pass)) {
            $question_gui->setUseIntermediateSolution(true);
            $aresult_output = $question_gui->getSolutionOutput($active_id, $pass, false, false, true, false, false, true);
            $question_gui->setUseIntermediateSolution(false);
            $tmp_tpl->setVariable('TEXT_ASOLUTION_OUTPUT', $this->lng->txt('autosavecontent'));
            $tmp_tpl->setVariable('ASOLUTION_OUTPUT', $aresult_output);
        }
        $result_output = $question_gui->getSolutionOutput(
            $active_id,
            $pass,
            false,
            false,
            false,
            $this->object->getShowSolutionFeedback(),
            false,
            true
        );
        $max_points = $question_gui->object->getMaximumPoints();

        $this->appendUserNameToModal($tmp_tpl, $participant);
        $this->appendQuestionTitleToModal($tmp_tpl, $question_id, $max_points, $question_gui->object->getTitle());
        $this->appendSolutionAndPointsToModal(
            $tmp_tpl,
            $result_output,
            $question_gui->object->getReachedPoints($active_id, $pass),
            $max_points
        );
        $this->appendFormToModal($tmp_tpl, $pass, $active_id, $question_id, $max_points);
        $tmp_tpl->setVariable('TEXT_YOUR_SOLUTION', $this->lng->txt('answers_of') . ' ' . $participant->getName());
        $suggested_solution = assQuestion::_getSuggestedSolutionOutput($question_id);
        if ($this->object->getShowSolutionSuggested() && strlen($suggested_solution) > 0) {
            $tmp_tpl->setVariable('TEXT_SOLUTION_HINT', $this->lng->txt("solution_hint"));
            $tmp_tpl->setVariable("SOLUTION_HINT", assQuestion::_getSuggestedSolutionOutput($question_id));
        }

        $tmp_tpl->setVariable('TEXT_SOLUTION_OUTPUT', $this->lng->txt('question'));
        $tmp_tpl->setVariable('TEXT_RECEIVED_POINTS', $this->lng->txt('scoring'));
        $add_title = ' [' . $this->lng->txt('question_id_short') . ': ' . $question_id . ']';
        $question_title = $this->object->getQuestionTitle($question_gui->object->getTitle());
        $lng = $this->lng->txt('points');
        if ($max_points == 1) {
            $lng = $this->lng->txt('point');
        }

        $tmp_tpl->setVariable(
            'QUESTION_TITLE',
            $question_title . ' (' . $max_points . ' ' . $lng . ')' . $add_title
        );
        $tmp_tpl->setVariable('SOLUTION_OUTPUT', $result_output);

        $tmp_tpl->setVariable(
            'RECEIVED_POINTS',
            sprintf(
                $this->lng->txt('part_received_a_of_b_points'),
                $question_gui->object->getReachedPoints($active_id, $pass),
                $max_points
            )
        );

        echo $tmp_tpl->get();
        exit();
    }

    /**
     *
     */
    public function checkConstraintsBeforeSaving()
    {
        $this->saveManScoringByQuestion(true);
    }

    /**
     * @param ilTemplate $tmp_tpl
     * @param $participant
     */
    private function appendUserNameToModal($tmp_tpl, $participant)
    {
        global $DIC;
        $ilAccess = $DIC->access();

        $tmp_tpl->setVariable(
            'TEXT_YOUR_SOLUTION',
            $this->lng->txt('answers_of') . ' ' . $participant->getName()
        );

        if (
            $this->object->anonymity == 1 ||
            ($this->object->getAnonymity() == 2 && !$ilAccess->checkAccess('write', '', $this->object->getRefId()))
        ) {
            $tmp_tpl->setVariable(
                'TEXT_YOUR_SOLUTION',
                $this->lng->txt('answers_of') . ' ' . $this->lng->txt('anonymous')
            );
        }
    }

    /**
     * @param ilTemplate $tmp_tpl
     * @param $question_id
     * @param $max_points
     * @param $title
     */
    private function appendQuestionTitleToModal($tmp_tpl, $question_id, $max_points, $title)
    {
        $add_title = ' [' . $this->lng->txt('question_id_short') . ': ' . $question_id . ']';
        $question_title = $this->object->getQuestionTitle($title);
        $lng = $this->lng->txt('points');
        if ($max_points == 1) {
            $lng = $this->lng->txt('point');
        }

        $tmp_tpl->setVariable(
            'QUESTION_TITLE',
            $question_title . ' (' . $max_points . ' ' . $lng . ')' . $add_title
        );
    }

    /**
     * @param ilTemplate $tmp_tpl
     * @param $pass
     * @param $active_id
     * @param $question_id
     * @param $max_points
     */
    private function appendFormToModal($tmp_tpl, $pass, $active_id, $question_id, $max_points)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $post_var = '[' . $pass . '][' . $active_id . '][' . $question_id . ']';
        $scoring_post_var = 'scoring' . $post_var;
        $reached_points = assQuestion::_getReachedPoints($active_id, $question_id, $pass);
        $form = new ilPropertyFormGUI();
        $feedback = $this->object->getSingleManualFeedback($active_id, $question_id, $pass);
        $disable = false;
        $form->setFormAction($ilCtrl->getFormAction($this, 'showManScoringByQuestionParticipantsTable'));
        $form->setTitle($this->lng->txt('manscoring'));

        if (isset($feedback['finalized_evaluation']) && $feedback['finalized_evaluation'] == 1) {
            $disable = true;
            $hidden_points = new ilHiddenInputGUI($scoring_post_var);
            $scoring_post_var = $scoring_post_var . '_disabled';
            $hidden_points->setValue($reached_points);
            $form->addItem($hidden_points);
        }

        $feedback_text = '';
        if (array_key_exists('feedback', $feedback)) {
            $feedback_text = $feedback['feedback'];
        }

        if ($disable) {
            $feedback_input = new ilNonEditableValueGUI($this->lng->txt('set_manual_feedback'), 'm_feedback' . $post_var, true);
        } else {
            $tmp_tpl->setVariable('TINYMCE_ACTIVE', ilObjAdvancedEditing::_getRichTextEditor());
            $feedback_input = new ilTextAreaInputGUI($this->lng->txt('set_manual_feedback'), 'm_feedback' . $post_var);
        }
        $feedback_input->setValue($feedback_text);
        $form->addItem($feedback_input);

        $reached_points_form = new ilNumberInputGUI($this->lng->txt('tst_change_points_for_question'), $scoring_post_var);
        $reached_points_form->allowDecimals(true);
        $reached_points_form->setSize(5);
        $reached_points_form->setMaxValue($max_points, true);
        $reached_points_form->setMinValue(0);
        $reached_points_form->setDisabled($disable);
        $reached_points_form->setValue($reached_points);
        $reached_points_form->setClientSideValidation(true);
        $form->addItem($reached_points_form);

        $hidden_points = new ilHiddenInputGUI('qst_max_points');
        $hidden_points->setValue($max_points);
        $form->addItem($hidden_points);

        $hidden_points_name = new ilHiddenInputGUI('qst_hidden_points_name');
        $hidden_points_name->setValue('scoring' . $post_var);
        $form->addItem($hidden_points_name);

        $hidden_feedback_name = new ilHiddenInputGUI('qst_hidden_feedback_name');
        $hidden_feedback_name->setValue('m_feedback' . $post_var);
        $form->addItem($hidden_feedback_name);

        $hidden_feedback_id = new ilHiddenInputGUI('qst_hidden_feedback_id');
        $post_id = '__' . $pass . '____' . $active_id . '____' . $question_id . '__';
        $hidden_feedback_id->setValue('m_feedback' . $post_id);
        $form->addItem($hidden_feedback_id);

        $evaluated = new ilCheckboxInputGUI($this->lng->txt('finalized_evaluation'), 'evaluated' . $post_var);
        if (isset($feedback['finalized_evaluation']) && $feedback['finalized_evaluation'] == 1) {
            $evaluated->setChecked(true);
        }
        $form->addItem($evaluated);

        $form->addCommandButton('checkConstraintsBeforeSaving', $this->lng->txt('save'));
        $CharSelector = ilCharSelectorGUI::_getCurrentGUI();
        $CharSelector->getConfig()->setAvailability(ilCharSelectorConfig::DISABLED);

        $tmp_tpl->setVariable(
            'MANUAL_FEEDBACK',
            $form->getHTML()
        );
        $tmp_tpl->setVariable(
            'MODAL_AJAX_URL',
            $this->ctrl->getLinkTarget($this, 'checkConstraintsBeforeSaving', '', true, false)
        );
        $tmp_tpl->setVariable(
            'INFO_TEXT_MAX_POINTS_EXCEEDS',
            sprintf($this->lng->txt('tst_manscoring_maxpoints_exceeded_input_alert'), $max_points)
        );
    }

    /**
     * @param ilTemplate $tmp_tpl
     * @param $result_output
     * @param $reached_points
     * @param $max_points
     */
    private function appendSolutionAndPointsToModal($tmp_tpl, $result_output, $reached_points, $max_points)
    {
        $tmp_tpl->setVariable(
            'SOLUTION_OUTPUT',
            $result_output
        );
        $tmp_tpl->setVariable(
            'RECEIVED_POINTS',
            sprintf(
                $this->lng->txt('part_received_a_of_b_points'),
                $reached_points,
                $max_points
            )
        );
    }

    protected function retrieveFeedback(int $active_id, int $qst_id, int $pass) : ?string
    {
        $feedback = $_POST['feedback'];
        if ($feedback === null || $feedback === '') {
            $feedback = $_POST['m_feedback'];
        }

        if ($feedback === null || $feedback === '') {
            return null;
        }

        return ilUtil::stripSlashes(
            $feedback[$pass][$active_id][$qst_id],
            false,
            ilObjAdvancedEditing::_getUsedHTMLTagsAsString('assessment')
        );
    }

    /**
     * @param $active_id
     * @param $qst_id
     * @param $pass
     * @param $feedback
     */
    protected function saveFinalization($active_id, $qst_id, $pass, $feedback, $is_single_feedback)
    {
        $evaluated = false;
        if ($this->doesValueExistsInPostArray('evaluated', $active_id, $qst_id, $pass)) {
            $evaluated = (int) $_POST['evaluated'][$pass][$active_id][$qst_id];
            if ($evaluated === 1) {
                $evaluated = true;
            }
        }
        $this->object->saveManualFeedback($active_id, $qst_id, $pass, $feedback, $evaluated, $is_single_feedback);
    }
    /**
     * @param $post_value
     * @param $active_id
     * @param $qst_id
     * @param $pass
     * @return bool
     */
    protected function doesValueExistsInPostArray($post_value, $active_id, $qst_id, $pass)
    {
        return (
            isset($_POST[$post_value][$pass][$active_id][$qst_id]) &&
            strlen($_POST[$post_value][$pass][$active_id][$qst_id]) > 0
        );
    }
}
