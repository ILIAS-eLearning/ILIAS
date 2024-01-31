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
 * The assErrorTextGUI class encapsulates the GUI representation for error text questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup 	ModulesTestQuestionPool
 *
 * @ilctrl_iscalledby assErrorTextGUI: ilObjQuestionPoolGUI
 * @ilCtrl_Calls assErrorTextGUI: ilFormPropertyDispatchGUI
 */
class assErrorTextGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
    private const DEFAULT_POINTS_WRONG = -1;

    private ilTabsGUI $tabs;

    public function __construct($id = -1)
    {
        global $DIC;
        $this->tabs = $DIC->tabs();

        parent::__construct();
        $this->object = new assErrorText();
        $this->setErrorMessage($this->lng->txt("msg_form_save_error"));
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }

        $this->tpl->addOnloadCode(
            "let form = document.getElementById('form_orderinghorizontal');
            let button = form.querySelector('input[name=\"cmd[save]\"]');
            if (form && button) {
                form.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && e.target.type !== 'textarea') {
                        e.preventDefault();
                        form.requestSubmit(button);
                    }
                })
            }"
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function writePostData(bool $always = false): int
    {
        $hasErrors = (!$always) ? $this->editQuestion(true) : false;
        if (!$hasErrors) {
            $this->writeQuestionGenericPostData();
            $this->writeQuestionSpecificPostData(new ilPropertyFormGUI());
            $this->writeAnswerSpecificPostData(new ilPropertyFormGUI());
            $this->saveTaxonomyAssignments();
            return 0;
        }
        return 1;
    }

    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form): void
    {
        $errordata = $this->restructurePostDataForSaving($this->request->raw('errordata') ?? []);
        $this->object->setErrorData($errordata);
        $this->object->removeErrorDataWithoutPosition();
    }

    private function restructurePostDataForSaving(array $post): array
    {
        $keys = $post['key'] ?? [];
        $restructured_array = [];
        foreach ($keys as $key => $text_wrong) {
            $restructured_array[] = new assAnswerErrorText(
                $text_wrong,
                $post['value'][$key],
                (float) str_replace(',', '.', $post['points'][$key])
            );
        }
        return $restructured_array;
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form): void
    {
        $this->object->setQuestion(
            $this->request->raw('question')
        );

        $this->object->setErrorText(
            $this->request->raw('errortext')
        );

        $this->object->parseErrorText();

        $points_wrong = str_replace(",", ".", $this->request->raw('points_wrong') ?? '');
        if (mb_strlen($points_wrong) == 0) {
            $points_wrong = self::DEFAULT_POINTS_WRONG;
        }
        $this->object->setPointsWrong((float) $points_wrong);

        if (!$this->object->getSelfAssessmentEditingMode()) {
            $this->object->setTextSize(
                (float) str_replace(',', '.', $this->request->raw('textsize'))
            );
        }
    }

    /**
     * Creates an output of the edit form for the question
     *
     * @param bool $checkonly
     *
     * @return bool
     */
    public function editQuestion($checkonly = false): bool
    {
        $this->tabs->setTabActive('edit_question');
        $save = $this->isSaveCommand();
        $this->getQuestionTemplate();

        $form = new ilPropertyFormGUI();
        $this->editForm = $form;

        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setMultipart(false);
        $form->setTableWidth("100%");
        $form->setId("orderinghorizontal");

        $this->addBasicQuestionFormProperties($form);

        $this->populateQuestionSpecificFormPart($form);

        if (count($this->object->getErrorData()) || $checkonly) {
            $this->populateAnswerSpecificFormPart($form);
        }

        $this->populateTaxonomyFormSection($form);

        $form->addCommandButton("analyze", $this->lng->txt('analyze_errortext'));
        $this->addQuestionFormCommandButtons($form);

        $errors = false;

        if ($save) {
            $form->setValuesByPost();
            $errors = !$form->checkInput();
            $form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling and we need this if we don't want to have duplication of backslashes
            if ($errors) {
                $checkonly = false;
            }
        }

        if (!$checkonly) {
            $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
        }
        return $errors;
    }

    /**
     * @param ilPropertyFormGUI $form
     * @return ilPropertyFormGUI
     */
    public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("errors_section"));
        $form->addItem($header);

        $errordata = new ilErrorTextWizardInputGUI($this->lng->txt("errors"), "errordata");
        $errordata->setKeyName($this->lng->txt('text_wrong'));
        $errordata->setValueName($this->lng->txt('text_correct'));
        $errordata->setValues($this->object->getErrorData());
        $form->addItem($errordata);

        // points for wrong selection
        $points_wrong = new ilNumberInputGUI($this->lng->txt("points_wrong"), "points_wrong");
        $points_wrong->allowDecimals(true);
        $points_wrong->setMaxValue(0);
        $points_wrong->setMaxvalueShouldBeLess(true);
        $points_wrong->setValue($this->object->getPointsWrong());
        $points_wrong->setInfo($this->lng->txt("points_wrong_info"));
        $points_wrong->setSize(6);
        $points_wrong->setRequired(true);
        $form->addItem($points_wrong);
        return $form;
    }

    /**
     * @param $form ilPropertyFormGUI
     * @return ilPropertyFormGUI
     */
    public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        // errortext
        $errortext = new ilTextAreaInputGUI($this->lng->txt("errortext"), "errortext");
        $errortext->setValue($this->object->getErrorText());
        $errortext->setRequired(true);
        $errortext->setInfo($this->lng->txt("errortext_info"));
        $errortext->setRows(10);
        $errortext->setCols(80);
        $form->addItem($errortext);

        if (!$this->object->getSelfAssessmentEditingMode()) {
            // textsize
            $textsize = new ilNumberInputGUI($this->lng->txt("textsize"), "textsize");
            $textsize->setValue($this->object->getTextSize() ?? 100.0);
            $textsize->setInfo($this->lng->txt("textsize_errortext_info"));
            $textsize->setSize(6);
            $textsize->setSuffix("%");
            $textsize->setMinValue(10);
            $textsize->setRequired(true);
            $form->addItem($textsize);
        }
        return $form;
    }

    /**
    * Parse the error text
    */
    public function analyze(): void
    {
        $this->writePostData(true);
        $this->saveTaxonomyAssignments();
        $this->object->setErrorsFromParsedErrorText();
        $this->editQuestion();
    }

    /**
     * Get the question solution output
     * The getSolutionOutput() method is used to print either the
     * user's pass' solution or the best possible solution for the
     * current errorText question object.
     * @param	integer		$active_id             The active test id
     * @param	integer		$pass                  The test pass counter
     * @param	boolean		$graphicalOutput       Show visual feedback for right/wrong answers
     * @param	boolean		$result_output         Show the reached points for parts of the question
     * @param	boolean		$show_question_only    Show the question without the ILIAS content around
     * @param	boolean		$show_feedback         Show the question feedback
     * @param	boolean		$show_correct_solution Show the correct solution instead of the user solution
     * @param	boolean		$show_manual_scoring   Show specific information for the manual scoring output
     * @return	string	HTML solution output
     **/
    public function getSolutionOutput(
        $active_id,
        $pass = null,
        $graphical_output = false,
        $result_output = false,
        $show_question_only = true,
        $show_feedback = false,
        $show_correct_solution = false,
        $show_manual_scoring = false,
        $show_question_text = true
    ): string {
        // get the solution of the user for the active pass or from the last pass if allowed
        $template = new ilTemplate("tpl.il_as_qpl_errortext_output_solution.html", true, true, "components/ILIAS/TestQuestionPool");


        $selections = [
            'user' => $this->getUsersSolutionFromPreviewOrDatabase((int) $active_id, $pass)
        ];
        $selections['best'] = $this->object->getBestSelection();

        $reached_points = $this->object->getPoints();
        if ($active_id > 0 && !$show_correct_solution) {
            $reached_points = $this->object->getReachedPoints($active_id, $pass);
        }

        if ($result_output === true) {
            $resulttext = ($reached_points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")";
            $template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $reached_points));
        }

        if ($this->object->getTextSize() >= 10) {
            $template->setVariable("STYLE", " style=\"font-size: " . $this->object->getTextSize() . "%;\"");
        }

        if ($show_question_text === true) {
            $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        }

        $correctness_icons = [
            'correct' => $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_OK),
            'not_correct' => $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_NOT_OK)
        ];
        $errortext = $this->object->assembleErrorTextOutput($selections, $graphical_output, $show_correct_solution, false, $correctness_icons);

        $template->setVariable("ERRORTEXT", $errortext);
        $questionoutput = $template->get();

        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "components/ILIAS/TestQuestionPool");

        $feedback = '';
        if ($show_feedback) {
            if (!$this->isTestPresentationContext()) {
                $fb = $this->getGenericFeedbackOutput((int) $active_id, $pass);
                $feedback .= mb_strlen($fb) ? $fb : '';
            }

            $fb = $this->getSpecificFeedbackOutput(array());
            $feedback .= mb_strlen($fb) ? $fb : '';
        }
        if (mb_strlen($feedback)) {
            $cssClass = (
                $this->hasCorrectSolution($active_id, $pass) ?
                ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT : ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG
            );

            $solutiontemplate->setVariable("ILC_FB_CSS_CLASS", $cssClass);
            $solutiontemplate->setVariable("FEEDBACK", ilLegacyFormElementsUtil::prepareTextareaOutput($feedback, true));
        }

        $solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

        $solutionoutput = $solutiontemplate->get();
        if (!$show_question_only) {
            // get page object output
            $solutionoutput = $this->getILIASPage($solutionoutput);
        }
        return $solutionoutput;
    }

    public function getPreview($show_question_only = false, $showInlineFeedback = false): string
    {
        $selections = [
            'user' => $this->getUsersSolutionFromPreviewOrDatabase()
         ];

        return $this->generateQuestionOutput($selections, $show_question_only);
    }

    public function getTestOutput(
        $active_id,
        $pass,
        $is_postponed = false,
        $use_post_solutions = false,
        $show_feedback = false
    ): string {
        $selections = [
            'user' => $this->getUsersSolutionFromPreviewOrDatabase($active_id, $pass)
         ];

        return $this->outQuestionPage(
            '',
            $is_postponed,
            $active_id,
            $this->generateQuestionOutput($selections, false)
        );
    }

    private function generateQuestionOutput($selections, $show_question_only): string
    {
        $template = new ilTemplate("tpl.il_as_qpl_errortext_output.html", true, true, "components/ILIAS/TestQuestionPool");

        if ($this->object->getTextSize() >= 10) {
            $template->setVariable("STYLE", " style=\"font-size: " . $this->object->getTextSize() . "%;\"");
        }
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $errortext = $this->object->assembleErrorTextOutput($selections);
        if ($this->getTargetGuiClass() !== null) {
            $this->ctrl->setParameterByClass($this->getTargetGuiClass(), 'errorvalue', '');
        }
        $template->setVariable("ERRORTEXT", $errortext);
        $template->setVariable("ERRORTEXT_ID", "qst_" . $this->object->getId());
        $template->setVariable("ERRORTEXT_VALUE", join(',', $selections['user']));

        $this->tpl->addOnLoadCode('il.test.player.errortext.init()');
        $this->tpl->addJavascript('assets/js/errortext.js');
        $questionoutput = $template->get();

        if ($show_question_only) {
            return $questionoutput;
        }

        return $this->getILIASPage($questionoutput);
    }

    private function getUsersSolutionFromPreviewOrDatabase(int $active_id = 0, ?int $pass = null): array
    {
        if (is_object($this->getPreviewSession())) {
            return (array) $this->getPreviewSession()->getParticipantsSolution();
        }

        if ($active_id > 0) {
            $selections = [];
            $solutions = $this->object->getTestOutputSolutions($active_id, $pass ?? 0);
            foreach ($solutions as $solution) {
                $selections[] = $solution['value1'];
            }
            return $selections;
        }

        return [];
    }

    public function getSpecificFeedbackOutput(array $user_solution): string
    {
        if (!$this->object->feedbackOBJ->specificAnswerFeedbackExists()) {
            return '';
        }

        $feedback = '<table class="test_specific_feedback"><tbody>';
        $elements = $this->object->getErrorData();
        foreach ($elements as $index => $element) {
            $feedback .= '<tr>';
            $feedback .= '<td class="text-nowrap">' . $index . '. ' . $element->getTextWrong() . ':</td>';
            $feedback .= '<td>' . $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                $this->object->getId(),
                0,
                $index
            ) . '</td>';

            $feedback .= '</tr>';
        }
        $feedback .= '</tbody></table>';

        return ilLegacyFormElementsUtil::prepareTextareaOutput($feedback, true);
    }

    /**
     * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
     * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
     * make sense in the given context.
     *
     * E.g. array('cloze_type', 'image_filename')
     *
     * @return string[]
     */
    public function getAfterParticipationSuppressionAnswerPostVars(): array
    {
        return [];
    }

    /**
     * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
     * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
     * make sense in the given context.
     *
     * E.g. array('cloze_type', 'image_filename')
     *
     * @return string[]
     */
    public function getAfterParticipationSuppressionQuestionPostVars(): array
    {
        return [];
    }

    /**
     * Returns an html string containing a question specific representation of the answers so far
     * given in the test for use in the right column in the scoring adjustment user interface.
     * @param array $relevant_answers
     * @return string
     */
    public function getAggregatedAnswersView(array $relevant_answers): string
    {
        $errortext = $this->object->getErrorText();

        $passdata = []; // Regroup answers into units of passes.
        foreach ($relevant_answers as $answer_chosen) {
            $passdata[$answer_chosen['active_fi'] . '-' . $answer_chosen['pass']][$answer_chosen['value2']][] = $answer_chosen['value1'];
        }

        $html = '';
        foreach ($passdata as $key => $pass) {
            $passdata[$key] = $this->object->createErrorTextOutput($pass);
            $html .= $passdata[$key] . '<hr /><br />';
        }

        return $html;
    }

    public function getAnswersFrequency($relevant_answers, $question_index): array
    {
        $answers_by_active_and_pass = [];

        foreach ($relevant_answers as $row) {
            $key = $row['active_fi'] . ':' . $row['pass'];

            if (!isset($answers_by_active_and_pass[$key])) {
                $answers_by_active_and_pass[$key] = ['user' => []];
            }

            $answers_by_active_and_pass[$key]['user'][] = $row['value1'];
        }

        $answers = [];

        foreach ($answers_by_active_and_pass as $answer) {
            $error_text = '<div class="errortext">' . $this->object->assembleErrorTextOutput($answer) . '</div>';
            $error_text_hashed = md5($error_text);

            if (!isset($answers[$error_text_hashed])) {
                $answers[$error_text_hashed] = [
                    'answer' => $error_text, 'frequency' => 0
                ];
            }

            $answers[$error_text_hashed]['frequency']++;
        }

        return array_values($answers);
    }

    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        $errordata = new ilAssErrorTextCorrectionsInputGUI($this->lng->txt('errors'), 'errordata');
        $errordata->setKeyName($this->lng->txt('text_wrong'));
        $errordata->setValueName($this->lng->txt('text_correct'));
        $errordata->setValues($this->object->getErrorData());
        $form->addItem($errordata);

        // points for wrong selection
        $points_wrong = new ilNumberInputGUI($this->lng->txt('points_wrong'), 'points_wrong');
        $points_wrong->allowDecimals(true);
        $points_wrong->setMaxValue(0);
        $points_wrong->setMaxvalueShouldBeLess(true);
        $points_wrong->setValue($this->object->getPointsWrong());
        $points_wrong->setInfo($this->lng->txt('points_wrong_info'));
        $points_wrong->setSize(6);
        $points_wrong->setRequired(true);
        $form->addItem($points_wrong);
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        $existing_errordata = $this->object->getErrorData();
        $this->object->flushErrorData();
        $new_errordata = $this->request->raw('errordata');
        $errordata = [];
        foreach ($new_errordata['points'] as $index => $points) {
            $errordata[$index] = $existing_errordata[$index]->withPoints(
                (float) str_replace(',', '.', $points)
            );
        }
        $this->object->setErrorData($errordata);
        $this->object->setPointsWrong((float) str_replace(',', '.', $form->getInput('points_wrong')));
    }
}
