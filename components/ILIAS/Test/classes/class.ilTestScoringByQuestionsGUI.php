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


/**
 * ilTestScoringByQuestionsGUI
 * @author     Michael Jansen <mjansen@databay.de>
 * @author     Björn Heyser <bheyser@databay.de>
 * @version    $Id$
 * @ingroup components\ILIASTest
 * @extends    ilTestServiceGUI
 */
class ilTestScoringByQuestionsGUI extends ilTestScoringGUI
{
    public const ONLY_FINALIZED = 1;
    public const EXCEPT_FINALIZED = 2;

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
    protected function getDefaultCommand(): string
    {
        return 'showManScoringByQuestionParticipantsTable';
    }

    /**
     * @return string
     */
    protected function getActiveSubTabId(): string
    {
        return 'man_scoring_by_qst';
    }

    /**
     * @param array $manPointsPost
     */
    protected function showManScoringByQuestionParticipantsTable($manPointsPost = []): void
    {
        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_MANUAL_SCORING);

        if (!$this->testAccess->checkScoreParticipantsAccess()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_test'), true);
            $this->ctrl->redirectByClass('ilobjtestgui', 'infoScreen');
        }

        iljQueryUtil::initjQuery();
        ilYuiUtil::initPanel();
        ilYuiUtil::initOverlay();

        $mathJaxSetting = new ilSetting('MathJax');

        if ($mathJaxSetting->get("enable")) {
            $this->tpl->addJavaScript($mathJaxSetting->get("path_to_mathjax"));
        }

        $this->tpl->addJavaScript("assets/js/Basic.js");
        $this->tpl->addJavaScript("assets/js/Form.js");
        $this->tpl->addJavascript('assets/js/Modal.js');
        $this->lng->toJSMap(['answer' => $this->lng->txt('answer')]);

        $table = new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this, $this->access);

        $qst_id = (int) $table->getFilterItemByPostVar('question')->getValue();
        $passNr = $table->getFilterItemByPostVar('pass')->getValue();
        $finalized_filter = (int)$table->getFilterItemByPostVar('finalize_evaluation')->getValue();
        $answered_filter = $table->getFilterItemByPostVar('only_answered')->getChecked();
        $table_data = [];
        $selected_questionData = null;
        $complete_feedback = $this->object->getCompleteManualFeedback($qst_id);

        if (is_numeric($qst_id)) {
            $info = $this->questioninfo->getQuestionInfo($qst_id);
            $selected_questionData = $info;
        }

        if ($selected_questionData && is_numeric($passNr)) {
            $data = $this->object->getCompleteEvaluationData(false);
            $participants = $data->getParticipants();
            $participantData = new ilTestParticipantData($this->db, $this->lng);
            $participantData->setActiveIdsFilter(array_keys($data->getParticipants()));
            $participantData->setParticipantAccessFilter(
                $this->participant_access_filter->getScoreParticipantsUserFilter($this->ref_id)
            );
            $participantData->load($this->object->getTestId());

            foreach ($participantData->getActiveIds() as $active_id) {
                $participant = $participants[$active_id];
                $testResultData = $this->object->getTestResult($active_id, $passNr - 1);

                foreach ($testResultData as $questionData) {
                    $feedback = [];
                    $is_answered = (bool) ($questionData['answered'] ?? false);
                    $finalized_evaluation = (bool) ($questionData['finalized_evaluation'] ?? false);

                    if (isset($complete_feedback[$active_id][$passNr - 1][$qst_id])) {
                        $feedback = $complete_feedback[$active_id][$passNr - 1][$qst_id];
                    }

                    $check_filter =
                        ($finalized_filter !== self::ONLY_FINALIZED || $finalized_evaluation) &&
                        ($finalized_filter !== self::EXCEPT_FINALIZED || !$finalized_evaluation);

                    $check_answered = $answered_filter == false || $is_answered;

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
                            'maximum_points' => $this->questioninfo->getMaximumPoints($questionData['qid']),
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
            $maxpoints = $this->questioninfo->getMaximumPoints($selected_questionData['question_id']);
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
        $this->tpl->setContent($table->getHTML());
    }

    protected function saveManScoringByQuestion(bool $ajax = false): void
    {
        $pass = key($_POST['scoring']);
        $active_data = current($_POST['scoring']);
        $active_ids = array_keys($active_data);

        if (!$this->testAccess->checkScoreParticipantsAccessForActiveId(current($active_ids))) {
            if ($ajax) {
                echo $this->lng->txt('cannot_edit_test');
                exit();
            }

            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_test'), true);
            $this->ctrl->redirectByClass('ilobjtestgui', 'infoScreen');
        }

        if (!isset($_POST['scoring']) || !is_array($_POST['scoring'])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_save_manscoring_failed_unknown'));
            $this->showManScoringByQuestionParticipantsTable();
            return;
        }

        $participantData = new ilTestParticipantData($this->db, $this->lng);
        $manPointsPost = [];
        $maxPointsByQuestionId = [];

        $participantData->setActiveIdsFilter($active_ids);
        $participantData->setParticipantAccessFilter(
            $this->participant_access_filter->getScoreParticipantsUserFilter($this->ref_id)
        );
        $participantData->load($this->object->getTestId());

        $changed_one = false;
        $lastAndHopefullyCurrentQuestionId = null;

        foreach ($participantData->getActiveIds() as $active_id) {
            $questions = $active_data[$active_id];
            if (!is_array($questions)) {
                continue;
            }

            // check for existing test result data
            if (!$this->object->getTestResult($active_id, $pass)) {
                continue;
            }

            $update_participant = false;
            $qst_id = null;

            foreach ($questions as $qst_id => $reached_points) {
                if (!isset($manPointsPost[$pass])) {
                    $manPointsPost[$pass] = [];
                }
                if (!isset($manPointsPost[$pass][$active_id])) {
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

                $maxPointsByQuestionId[$qst_id] = $this->questioninfo->getMaximumPoints($qst_id);
                $manPointsPost[$pass][$active_id][$qst_id] = (float) $reached_points;
                if ($reached_points > $maxPointsByQuestionId[$qst_id]) {
                    $this->tpl->setOnScreenMessage('failure', sprintf($this->lng->txt('tst_save_manscoring_failed'), $pass + 1), false);
                    $this->showManScoringByQuestionParticipantsTable($manPointsPost);
                    return;
                }

                $this->saveFinalization($active_id, $qst_id, $pass, $feedback_text, $ajax);
                // fix #35543: save manual points only if they differ from the existing points
                // this prevents a question being set to "answered" if only feedback is entered
                $old_points = assQuestion::_getReachedPoints($active_id, $qst_id, $pass);
                if ($reached_points !== $old_points) {
                    $update_participant = assQuestion::_setReachedPoints(
                        $active_id,
                        $qst_id,
                        (float) $reached_points,
                        $maxPointsByQuestionId[$qst_id],
                        $pass,
                        true,
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

        $correction_feedback = [];
        $correction_points = 0;

        if ($changed_one) {
            $qTitle = '';

            if ($lastAndHopefullyCurrentQuestionId) {
                $question = assQuestion::instantiateQuestion($lastAndHopefullyCurrentQuestionId);
                $qTitle = $question->getTitle();
            }

            $msg = sprintf(
                $this->lng->txt('tst_saved_manscoring_by_question_successfully'),
                $qTitle,
                $pass + 1
            );

            $this->tpl->setOnScreenMessage('success', $msg, true);

            if (isset($active_id) && $lastAndHopefullyCurrentQuestionId) {
                $correction_feedback = ilObjTest::getSingleManualFeedback(
                    $active_id,
                    $lastAndHopefullyCurrentQuestionId,
                    $pass
                );
                $correction_points = assQuestion::_getReachedPoints($active_id, $lastAndHopefullyCurrentQuestionId, $pass);
            }
        }

        if ($ajax && is_array($correction_feedback)) {
            $finalized_by_usr_id = $correction_feedback['finalized_by_usr_id'];
            if (!$finalized_by_usr_id) {
                $finalized_by_usr_id = $this->user->getId();
            }
            $correction_feedback['finalized_by'] = ilObjUser::_lookupFullname($finalized_by_usr_id);
            $correction_feedback['finalized_on_date'] = '';

            if (is_int($correction_feedback['finalized_tstamp'])) {
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
                $correction_feedback['finalized_by'] = '';
            }

            echo json_encode([ 'feedback' => $correction_feedback, 'points' => $correction_points, "translation" => ['yes' => $this->lng->txt('yes'), 'no' => $this->lng->txt('no')]]);
            exit();
        }

        $this->showManScoringByQuestionParticipantsTable();
    }

    /**
     *
     */
    protected function applyManScoringByQuestionFilter()
    {
        $table = new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this, $this->access);
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->showManScoringByQuestionParticipantsTable();
    }

    /**
     *
     */
    protected function resetManScoringByQuestionFilter()
    {
        $table = new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this, $this->access);
        $table->resetOffset();
        $table->resetFilter();
        $this->showManScoringByQuestionParticipantsTable();
    }

    protected function getAnswerDetail()
    {
        $active_id = $this->testrequest->getActiveId();
        $pass = $this->testrequest->getPassId();
        $question_id = (int) $this->testrequest->raw('qst_id');

        if (!$this->getTestAccess()->checkScoreParticipantsAccessForActiveId($active_id)) {
            exit; // illegal ajax call
        }

        $data = $this->object->getCompleteEvaluationData(false);
        $participant = $data->getParticipant($active_id);
        $question_gui = $this->object->createQuestionGUI('', $question_id);
        $tmp_tpl = new ilTemplate('tpl.il_as_tst_correct_solution_output.html', true, true, 'components/ILIAS/Test');
        if ($question_gui instanceof assTextQuestionGUI && $this->object->getAutosave()) {
            $aresult_output = $question_gui->getAutoSavedSolutionOutput(
                $active_id,
                $pass,
                false,
                false,
                false,
                $this->object->getShowSolutionFeedback(),
                false,
                true,
                false
            );
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

    private function appendUserNameToModal(ilTemplate $tmp_tpl, ilTestEvaluationUserData $participant_data): void
    {
        $tmp_tpl->setVariable(
            'TEXT_YOUR_SOLUTION',
            $this->lng->txt('answers_of') . ' ' . $participant_data->getName()
        );

        if (
            $this->object->getAnonymity() == 1 ||
            ($this->object->getAnonymity() == 2 && !$this->access->checkAccess('write', '', $this->object->getRefId()))
        ) {
            $tmp_tpl->setVariable(
                'TEXT_YOUR_SOLUTION',
                $this->lng->txt('answers_of') . ' ' . $this->lng->txt('anonymous')
            );
        }
    }

    private function appendQuestionTitleToModal(ilTemplate $tmp_tpl, int $question_id, float $max_points, string $title): void
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

    private function appendFormToModal(ilTemplate $tmp_tpl, int $pass, int $active_id, int $question_id, float $max_points): void
    {
        $post_var = '[' . $pass . '][' . $active_id . '][' . $question_id . ']';
        $scoring_post_var = 'scoring' . $post_var;
        $reached_points = assQuestion::_getReachedPoints($active_id, $question_id, $pass);
        $form = new ilPropertyFormGUI();
        $feedback = ilObjTest::getSingleManualFeedback((int) $active_id, (int) $question_id, (int) $pass);
        $disable = false;
        $form->setFormAction($this->ctrl->getFormAction($this, 'showManScoringByQuestionParticipantsTable'));
        $form->setTitle($this->lng->txt('manscoring'));

        if (isset($feedback['finalized_evaluation']) && $feedback['finalized_evaluation'] == 1) {
            $disable = true;
            $hidden_points = new ilHiddenInputGUI($scoring_post_var);
            $scoring_post_var = $scoring_post_var . '_disabled';
            $hidden_points->setValue((string) $reached_points);
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
        $reached_points_form->setValue((string) $reached_points);
        $reached_points_form->setClientSideValidation(true);
        $form->addItem($reached_points_form);

        $hidden_points = new ilHiddenInputGUI('qst_max_points');
        $hidden_points->setValue((string) $max_points);
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

    protected function retrieveFeedback(int $active_id, int $qst_id, int $pass): ?string
    {
        $feedback = $this->testrequest->raw('feedback');
        if ($feedback === null || $feedback === '') {
            $feedback = $this->testrequest->raw('m_feedback');
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

    protected function saveFinalization(
        int $active_id,
        int $qst_id,
        int $pass,
        ?string $feedback,
        bool $is_single_feedback
    ): void {
        $finalized = false;
        if ($this->doesValueExistsInPostArray('evaluated', $active_id, $qst_id, $pass)) {
            $finalized = (bool) ($_POST['evaluated'][$pass][$active_id][$qst_id] ?? false);
        }

        $this->object->saveManualFeedback(
            $active_id,
            $qst_id,
            $pass,
            $feedback,
            $finalized,
            $is_single_feedback
        );
    }
    /**
     * @param $post_value
     * @param $active_id
     * @param $qst_id
     * @param $pass
     * @return bool
     */
    protected function doesValueExistsInPostArray($post_value, $active_id, $qst_id, $pass): bool
    {
        return (
            isset($_POST[$post_value][$pass][$active_id][$qst_id]) &&
            $_POST[$post_value][$pass][$active_id][$qst_id] != ''
        );
    }
}
