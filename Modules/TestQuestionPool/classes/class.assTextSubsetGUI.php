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

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Multiple choice question GUI representation
 *
 * The assTextSubsetGUI class encapsulates the GUI representation
 * for multiple choice questions.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 * @ilCtrl_Calls assTextSubsetGUI: ilFormPropertyDispatchGUI
 */
class assTextSubsetGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
    private $answers_from_post;

    /**
     * assTextSubsetGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the assTextSubsetGUI object.
     *
     * @param integer $id The database id of a text subset question object
     */
    public function __construct($id = -1)
    {
        parent::__construct();
        $this->object = new assTextSubset();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function writePostData(bool $always = false): int
    {
        /*
         * sk 26.09.22: This is horrific but I don't see a better way right now,
         * without needing to check most questions for side-effects.
         */
        $this->answers_from_post = $_POST['answers']['answer'];
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

    /**
     * Creates an output of the edit form for the question
     */
    public function editQuestion($checkonly = false): bool
    {
        $save = $this->isSaveCommand();
        $this->getQuestionTemplate();

        $form = new ilPropertyFormGUI();
        $this->editForm = $form;

        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setMultipart(false);
        $form->setTableWidth("100%");
        $form->setId("asstextsubset");

        $this->addBasicQuestionFormProperties($form);
        $this->populateQuestionSpecificFormPart($form);
        $this->populateAnswerSpecificFormPart($form);
        $this->populateTaxonomyFormSection($form);
        $this->addQuestionFormCommandButtons($form);

        $errors = false;
        if ($save) {
            $form->setValuesByPost();
            $points = $form->getItemByPostVar('points');
            $points->setValue($this->object->getMaximumPoints());
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
    * Add a new answer
    */
    public function addanswers(): void
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['addanswers']);
        $this->object->addAnswer("", 0, $position + 1);
        $this->editQuestion();
    }

    /**
    * Remove an answer
    */
    public function removeanswers(): void
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['removeanswers']);
        $this->object->deleteAnswer($position);
        $this->editQuestion();
    }

    /**
    * Get the question solution output
    * @param integer $active_id             The active user id
    * @param integer $pass                  The test pass
    * @param boolean $graphicalOutput       Show visual feedback for right/wrong answers
    * @param boolean $result_output         Show the reached points for parts of the question
    * @param boolean $show_question_only    Show the question without the ILIAS content around
    * @param boolean $show_feedback         Show the question feedback
    * @param boolean $show_correct_solution Show the correct solution instead of the user solution
    * @param boolean $show_manual_scoring   Show specific information for the manual scoring output
    * @return string The solution output of the question as HTML code
    */
    public function getSolutionOutput(
        $active_id,
        $pass = null,
        $graphicalOutput = false,
        $result_output = false,
        $show_question_only = true,
        $show_feedback = false,
        $show_correct_solution = false,
        $show_manual_scoring = false,
        $show_question_text = true
    ): string {
        // get the solution of the user for the active pass or from the last pass if allowed
        $solutions = array();
        if (($active_id > 0) && (!$show_correct_solution)) {
            $solutions = $this->object->getSolutionValues($active_id, $pass);
        } else {
            $rank = array();
            foreach ($this->object->answers as $answer) {
                $points_string_for_key = (string) $answer->getPoints();
                if ($answer->getPoints() > 0) {
                    if (!array_key_exists($points_string_for_key, $rank)) {
                        $rank[$points_string_for_key] = array();
                    }
                    array_push($rank[$points_string_for_key], $answer->getAnswertext());
                }
            }
            krsort($rank, SORT_NUMERIC);
            foreach ($rank as $index => $bestsolutions) {
                array_push($solutions, array("value1" => join(",", $bestsolutions), "points" => $index));
            }
        }

        $template = new ilTemplate("tpl.il_as_qpl_textsubset_output_solution.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        $available_answers = &$this->object->getAvailableAnswers();
        for ($i = 0; $i < $this->object->getCorrectAnswers(); $i++) {
            if (!array_key_exists($i, $solutions) || (strcmp($solutions[$i]["value1"], "") == 0)) {
            } else {
                if (($active_id > 0) && (!$show_correct_solution)) {
                    if ($graphicalOutput) {
                        // output of ok/not ok icons for user entered solutions
                        $index = $this->object->isAnswerCorrect($available_answers, $solutions[$i]["value1"]);
                        $correct = false;
                        if ($index !== false) {
                            unset($available_answers[$index]);
                            $correct = true;
                        }

                        $correctness_icon = $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_NOT_OK);
                        if ($correct) {
                            $correctness_icon = $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_OK);
                        }
                        $template->setCurrentBlock("icon_ok");
                        $template->setVariable("ICON_OK", $correctness_icon);
                        $template->parseCurrentBlock();
                    }
                }
                $template->setCurrentBlock("textsubset_row");
                $template->setVariable("SOLUTION", $this->escapeTemplatePlaceholders($solutions[$i]["value1"]));
                $template->setVariable("COUNTER", $i + 1);
                if ($result_output) {
                    $points = $solutions[$i]["points"];
                    $resulttext = ($points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")";
                    $template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
                }
                $template->parseCurrentBlock();
            }
        }
        if ($show_question_text == true) {
            $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        }
        $questionoutput = $template->get();
        $feedback = ($show_feedback && !$this->isTestPresentationContext()) ? $this->getGenericFeedbackOutput((int) $active_id, $pass) : "";
        if (strlen($feedback)) {
            $cssClass = (
                $this->hasCorrectSolution($active_id, $pass) ?
                ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT : ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG
            );

            $solutiontemplate->setVariable("ILC_FB_CSS_CLASS", $cssClass);
            $solutiontemplate->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($feedback, true));
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
        $solutions = is_object($this->getPreviewSession()) ? (array) $this->getPreviewSession()->getParticipantsSolution() : array();
        $template = new ilTemplate("tpl.il_as_qpl_textsubset_output.html", true, true, "Modules/TestQuestionPool");
        $width = $this->object->getMaxTextboxWidth();
        for ($i = 0; $i < $this->object->getCorrectAnswers(); $i++) {
            $template->setCurrentBlock("textsubset_row");
            foreach ($solutions as $idx => $solution_value) {
                if ($idx == $i) {
                    $template->setVariable("TEXTFIELD_VALUE", " value=\""
                        . $this->escapeTemplatePlaceholders($solution_value)
                        . "\"");
                }
            }
            $template->setVariable("COUNTER", $i + 1);
            $template->setVariable("TEXTFIELD_ID", $i);
            $template->setVariable("TEXTFIELD_SIZE", $width);
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $questionoutput = $template->get();
        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        return $questionoutput;
    }

    public function getTestOutput($active_id, $pass = null, $is_postponed = false, $use_post_solutions = false, $inlineFeedback = false): string
    {
        // get the solution of the user for the active pass or from the last pass if allowed
        $user_solution = "";
        if ($active_id) {
            $solutions = $this->object->getUserSolutionPreferingIntermediate($active_id, $pass);
        }

        $template = new ilTemplate("tpl.il_as_qpl_textsubset_output.html", true, true, "Modules/TestQuestionPool");
        $width = $this->object->getMaxTextboxWidth();
        for ($i = 0; $i < $this->object->getCorrectAnswers(); $i++) {
            $template->setCurrentBlock("textsubset_row");
            foreach ($solutions as $idx => $solution_value) {
                if ($idx == $i) {
                    $template->setVariable("TEXTFIELD_VALUE", " value=\""
                        . $this->escapeTemplatePlaceholders($solution_value["value1"])
                        . "\"");
                }
            }
            $template->setVariable("COUNTER", $i + 1);
            $template->setVariable("TEXTFIELD_ID", $i);
            $template->setVariable("TEXTFIELD_SIZE", $width);
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $questionoutput = $template->get();
        $pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
        return $pageoutput;
    }

    public function getSpecificFeedbackOutput(array $userSolution): string
    {
        $output = "";
        return $this->object->prepareTextareaOutput($output, true);
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form): void
    {
        $this->object->setCorrectAnswers($_POST["correctanswers"]);
        $this->object->setTextRating($_POST["text_rating"]);
    }

    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form): void
    {
        // Delete all existing answers and create new answers from the form data
        $this->object->flushAnswers();
        foreach ($this->answers_from_post as $index => $answertext) {
            $answertext = assQuestion::extendedTrim($answertext);
            $this->object->addAnswer(htmlentities($answertext), $_POST['answers']['points'][$index], $index);
        }
    }

    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        // number of requested answers
        $correctanswers = new ilNumberInputGUI($this->lng->txt("nr_of_correct_answers"), "correctanswers");
        $correctanswers->setMinValue(1);
        $correctanswers->setDecimals(0);
        $correctanswers->setSize(3);
        $correctanswers->setValue($this->object->getCorrectAnswers());
        $correctanswers->setRequired(true);
        $form->addItem($correctanswers);

        // maximum available points
        $points = new ilNumberInputGUI($this->lng->txt("maximum_points"), "points");
        $points->setMinValue(0.0);
        $points->setMinvalueShouldBeGreater(true);
        $points->setSize(6);
        $points->setDisabled(true);
        $points->allowDecimals(true);
        $points->setValue($this->object->getMaximumPoints());
        $points->setRequired(false);
        $form->addItem($points);

        // text rating
        $textrating = new ilSelectInputGUI($this->lng->txt("text_rating"), "text_rating");
        $text_options = array(
            "ci" => $this->lng->txt("cloze_textgap_case_insensitive"),
            "cs" => $this->lng->txt("cloze_textgap_case_sensitive")
        );
        if (!$this->object->getSelfAssessmentEditingMode()) {
            $text_options["l1"] = sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "1");
            $text_options["l2"] = sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "2");
            $text_options["l3"] = sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "3");
            $text_options["l4"] = sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "4");
            $text_options["l5"] = sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "5");
        }
        $textrating->setOptions($text_options);
        $textrating->setValue($this->object->getTextRating());
        $form->addItem($textrating);
        return $form;
    }

    public function populateAnswerSpecificFormPart(\ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        $choices = new ilAnswerWizardInputGUI($this->lng->txt("answers"), "answers");
        $choices->setRequired(true);
        $choices->setQuestionObject($this->object);
        $choices->setSingleline(true);
        $choices->setAllowMove(false);
        $choices->setMinValue(0.0);
        if ($this->object->getAnswerCount() == 0) {
            $this->object->addAnswer("", 0, 0);
        }
        $choices->setValues(array_map(
            function (ASS_AnswerBinaryStateImage $value) {
                $value->setAnswerText(html_entity_decode($value->getAnswerText()));
                return $value;
            },
            $this->object->getAnswers()
        ));
        $form->addItem($choices);
        return $form;
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
        return array();
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
        return array();
    }

    /**
     * Returns an html string containing a question specific representation of the answers so far
     * given in the test for use in the right column in the scoring adjustment user interface.
     * @param array $relevant_answers
     * @return string
     */
    public function getAggregatedAnswersView(array $relevant_answers): string
    {
        return  $this->renderAggregateView(
            $this->aggregateAnswers($relevant_answers)
        )->get();
    }

    public function aggregateAnswers($relevant_answers_chosen): array
    {
        $aggregate = array();

        foreach ($relevant_answers_chosen as $relevant_answer) {
            if (array_key_exists($relevant_answer['value1'], $aggregate)) {
                $aggregate[$relevant_answer['value1']]++;
            } else {
                $aggregate[$relevant_answer['value1']] = 1;
            }
        }
        return $aggregate;
    }

    /**
     * @param $aggregate
     *
     * @return ilTemplate
     */
    public function renderAggregateView($aggregate): ilTemplate
    {
        $tpl = new ilTemplate('tpl.il_as_aggregated_answers_table.html', true, true, "Modules/TestQuestionPool");

        foreach ($aggregate as $key => $value) {
            $tpl->setCurrentBlock('aggregaterow');
            $tpl->setVariable('OPTION', $key);
            $tpl->setVariable('COUNT', $value);
            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }

    public function getAnswersFrequency($relevantAnswers, $questionIndex): array
    {
        $answers = array();

        foreach ($relevantAnswers as $ans) {
            if (!isset($answers[$ans['value1']])) {
                $answers[$ans['value1']] = array(
                    'answer' => $ans['value1'], 'frequency' => 0
                );
            }

            $answers[$ans['value1']]['frequency']++;
        }
        $answers = $this->completeAddAnswerAction($answers, $questionIndex);
        return $answers;
    }

    protected function completeAddAnswerAction($answers, $questionIndex)
    {
        foreach ($answers as $key => $ans) {
            $found = false;

            foreach ($this->object->getAnswers() as $item) {
                if ($ans['answer'] !== $item->getAnswerText()) {
                    continue;
                }

                $found = true;
                break;
            }

            if (!$found) {
                $answers[$key]['addable'] = true;
            }
        }

        return $answers;
    }

    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        $choices = new ilAssAnswerCorrectionsInputGUI($this->lng->txt("answers"), "answers");
        $choices->setRequired(true);
        $choices->setQuestionObject($this->object);
        $choices->setValues($this->object->getAnswers());
        $form->addItem($choices);
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        $input = $form->getItemByPostVar('answers');
        $values = $input->getValues();

        foreach ($this->object->getAnswers() as $index => $answer) {
            $points = (float) str_replace(',', '.', $values[$index]->getPoints());
            $answer->setPoints($points);
        }
    }
}
