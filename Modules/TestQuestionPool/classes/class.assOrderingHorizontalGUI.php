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
 * The assOrderingHorizontalGUI class encapsulates the GUI representation for horizontal ordering questions.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 *
 * @ilctrl_iscalledby assOrderingHorizontalGUI: ilObjQuestionPoolGUI
 * @ilCtrl_Calls assOrderingHorizontalGUI: ilPropertyFormGUI, ilFormPropertyDispatchGUI
 */
class assOrderingHorizontalGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable
{
    /**
    * assOrderingHorizontalGUI constructor
    *
    * The constructor takes possible arguments an creates an instance of the assOrderingHorizontalGUI object.
    *
    * @param integer $id The database id of a single choice question object
    * @access public
    */
    public function __construct($id = -1)
    {
        parent::__construct();
        include_once "./Modules/TestQuestionPool/classes/class.assOrderingHorizontal.php";
        $this->object = new assOrderingHorizontal();
        $this->setErrorMessage($this->lng->txt("msg_form_save_error"));
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }

    public function getCommand($cmd)
    {
        return $cmd;
    }

    /**
     * {@inheritdoc}
     */
    protected function writePostData(bool $always = false): int
    {
        $hasErrors = (!$always) ? $this->editQuestion(true) : false;
        if (!$hasErrors) {
            require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
            $this->writeQuestionGenericPostData();
            $this->writeQuestionSpecificPostData(new ilPropertyFormGUI());
            $this->saveTaxonomyAssignments();
            return 0;
        }
        return 1;
    }

    /**
    * Creates an output of the edit form for the question
    *
    * @access public
    */
    public function editQuestion($checkonly = false): bool
    {
        $save = $this->isSaveCommand();
        $this->getQuestionTemplate();

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $this->editForm = $form;

        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setMultipart(false);
        $form->setTableWidth("100%");
        $form->setId("orderinghorizontal");

        $this->addBasicQuestionFormProperties($form);
        $this->populateQuestionSpecificFormPart($form);


        $this->populateTaxonomyFormSection($form);

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
    * Get the question solution output
    * @param integer $active_id             The active user id
    * @param integer $pass                  The test pass
    * @param boolean $graphicalOutput       Show visual feedback for right/wrong answers
    * @param boolean $result_output         Show the reached points for parts of the question
    * @param boolean $show_question_only    Show the question without the ILIAS content around
    * @param boolean $show_feedback         Show the question feedback
    * @param boolean $show_correct_solution Show the correct solution instead of the user solution
    * @param boolean $show_manual_scoring   Show specific information for the manual scoring output
    * @return string solution output of the question as HTML code
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
        $template = new ilTemplate("tpl.il_as_qpl_orderinghorizontal_output_solution.html", true, true, "Modules/TestQuestionPool");

        if (($active_id > 0) && (!$show_correct_solution)) {
            $elements = [];
            $solutions = $this->object->getSolutionValues($active_id, $pass);

            if (count($solutions) && strlen($solutions[0]["value1"])) {
                $elements = explode("{::}", $solutions[0]["value1"]);
            }

            if (!count($elements)) {
                $elements = $this->object->getRandomOrderingElements();
            }

            foreach ($elements as $id => $element) {
                $template->setCurrentBlock("element");
                $template->setVariable("ELEMENT_ID", "sol_e_" . $this->object->getId() . "_$id");
                $template->setVariable("ELEMENT_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($element));
                $template->parseCurrentBlock();
            }
        } else {
            $elements = $this->object->getOrderingElements();
            foreach ($elements as $id => $element) {
                $template->setCurrentBlock("element");
                $template->setVariable("ELEMENT_ID", "sol_e_" . $this->object->getId() . "_$id");
                $template->setVariable("ELEMENT_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($element));
                $template->parseCurrentBlock();
            }
        }

        if (($active_id > 0) && (!$show_correct_solution)) {
            if ($this->object->getStep() === null) {
                $reached_points = $this->object->getReachedPoints($active_id, $pass);
            } else {
                $reached_points = $this->object->calculateReachedPoints($active_id, $pass);
            }
            if ($graphicalOutput) {
                $correctness_icon = $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_NOT_OK);
                if ($reached_points == $this->object->getMaximumPoints()) {
                    $correctness_icon = $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_OK);
                } elseif ($reached_points > 0) {
                    $correctness_icon = $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_MOSTLY_OK);
                }
                $template->setCurrentBlock("icon_ok");
                $template->setVariable("ICON_OK", $correctness_icon);
                $template->parseCurrentBlock();
            }
        } else {
            $reached_points = $this->object->getPoints();
        }

        if ($result_output) {
            $resulttext = ($reached_points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")";
            $template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $reached_points));
        }
        if ($show_question_text == true) {
            $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        }
        //		$template->setVariable("SOLUTION_TEXT", ilUtil::prepareFormOutput($solutionvalue));
        if ($this->object->getTextSize() >= 10) {
            $template->setVariable("STYLE", " style=\"font-size: " . $this->object->getTextSize() . "%;\"");
        }

        $questionoutput = $template->get();
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);


        $feedback = '';
        if ($show_feedback) {
            if (!$this->isTestPresentationContext()) {
                $fb = $this->getGenericFeedbackOutput((int) $active_id, $pass);
                $feedback .= strlen($fb) ? $fb : '';
            }
        }
        if (strlen($feedback)) {
            $cssClass = (
                $this->hasCorrectSolution($active_id, $pass) ?
                ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT : ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG
            );

            $solutiontemplate->setVariable("ILC_FB_CSS_CLASS", $cssClass);
            $solutiontemplate->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($feedback, true));
        }
        $solutionoutput = $solutiontemplate->get();
        if (!$show_question_only) {
            // get page object output
            $solutionoutput = $this->getILIASPage($solutionoutput);
        }
        return $solutionoutput;
    }


    public function getPreview($show_question_only = false, $showInlineFeedback = false): string
    {
        if (is_object($this->getPreviewSession()) && strlen((string) $this->getPreviewSession()->getParticipantsSolution())) {
            $elements = (string) $this->getPreviewSession()->getParticipantsSolution();
            $elements = $this->object->splitAndTrimOrderElementText($elements, $this->object->getAnswerSeparator());
        } else {
            $elements = $this->object->getRandomOrderingElements();
        }

        $template = new ilTemplate("tpl.il_as_qpl_orderinghorizontal_preview.html", true, true, "Modules/TestQuestionPool");
        $js = <<<JS

        $('#horizontal_{QUESTION_ID}').ilHorizontalOrderingQuestion({
            result_value_selector  : '.ilOrderingValue',
            result_separator       : '{::}'
        });

JS;
        $js = str_replace('{QUESTION_ID}', $this->object->getId(), $js);
        $this->tpl->addOnLoadCode($js);

        foreach ($elements as $id => $element) {
            $template->setCurrentBlock("element");
            $template->setVariable("ELEMENT_ID", "e_" . $this->object->getId() . "_$id");
            $template->setVariable("ORDERING_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($element));
            $template->setVariable("ELEMENT_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($element));
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTION_ID", $this->object->getId());
        $template->setVariable("VALUE_ORDERRESULT", ' value="' . join('{::}', $elements) . '"');
        if ($this->object->getTextSize() >= 10) {
            $template->setVariable("STYLE", " style=\"font-size: " . $this->object->getTextSize() . "%;\"");
        }
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $questionoutput = $template->get();
        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        if ($DIC->http()->agent()->isMobile() || $DIC->http()->agent()->isIpad()) {
            require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
            iljQueryUtil::initjQuery();
            iljQueryUtil::initjQueryUI();
            $this->tpl->addJavaScript('./node_modules/@andxor/jquery-ui-touch-punch-fix/jquery.ui.touch-punch.js');
        }
        $this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/orderinghorizontal.js");
        return $questionoutput;
    }

    // hey: prevPassSolutions - pass will be always available from now on
    public function getTestOutput($active_id, $pass, $is_postponed = false, $use_post_solutions = false, $show_feedback = false): string
    // hey.
    {
        // generate the question output
        $template = new ilTemplate("tpl.il_as_qpl_orderinghorizontal_output.html", true, true, "Modules/TestQuestionPool");
        $js = <<<JS
    $().ready(function() {
        if (typeof $.fn.ilHorizontalOrderingQuestion != 'undefined') {
            $('#horizontal_{QUESTION_ID}').ilHorizontalOrderingQuestion({
                result_value_selector: '.ilOrderingValue',
                result_separator: '{::}'
            });
        }
    });
JS;
        $js = str_replace('{QUESTION_ID}', $this->object->getId(), $js);
        $this->tpl->addOnLoadCode($js);


        $elements = $this->object->getRandomOrderingElements();

        if ($active_id) {
            $solutions = $this->object->getTestOutputSolutions($active_id, $pass);
            // hey.
            if (is_array($solutions) && count($solutions) == 1) {
                $elements = explode("{::}", $solutions[0]["value1"]);
            }
        }
        if (!is_array($solutions) || count($solutions) == 0) {
            ilSession::set('qst_ordering_horizontal_elements', $elements);
        } else {
            ilSession::clear('qst_ordering_horizontal_elements');
        }
        foreach ($elements as $id => $element) {
            $template->setCurrentBlock("element");
            $template->setVariable("ELEMENT_ID", "e_" . $this->object->getId() . "_$id");
            $template->setVariable("ORDERING_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($element));
            $template->setVariable("ELEMENT_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($element));
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTION_ID", $this->object->getId());
        if ($this->object->getTextSize() >= 10) {
            $template->setVariable("STYLE", " style=\"font-size: " . $this->object->getTextSize() . "%;\"");
        }
        $template->setVariable("VALUE_ORDERRESULT", ' value="' . join('{::}', $elements) . '"');
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $questionoutput = $template->get();
        //if (!$show_question_only) {
        // get page object output
        $questionoutput = $this->getILIASPage($questionoutput);
        //}
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        if ($DIC->http()->agent()->isMobile() || $DIC->http()->agent()->isIpad()) {
            require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
            iljQueryUtil::initjQuery();
            iljQueryUtil::initjQueryUI();
            $this->tpl->addJavaScript('./node_modules/@andxor/jquery-ui-touch-punch-fix/jquery.ui.touch-punch.js');
        }
        $this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/orderinghorizontal.js");
        $questionoutput = $template->get();
        $pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
        return $pageoutput;
    }

    public function getPresentationJavascripts(): array
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $files = array();

        if ($DIC->http()->agent()->isMobile() || $DIC->http()->agent()->isIpad()) {
            $files[] = './node_modules/@andxor/jquery-ui-touch-punch-fix/jquery.ui.touch-punch.js';
        }

        return $files;
    }

    public function getSpecificFeedbackOutput(array $userSolution): string
    {
        return '';
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form): void
    {
        $this->object->setTextSize($_POST["textsize"]);
        $this->object->setOrderText($_POST["ordertext"]);
        $this->object->setPoints((float) str_replace(',', '.', $_POST["points"]));
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

    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        // ordertext
        $ordertext = new ilTextAreaInputGUI($this->lng->txt("ordertext"), "ordertext");
        $ordertext->setValue((string) self::prepareTextareaOutput($this->object->getOrderText(), false, true));
        $ordertext->setRequired(true);
        $ordertext->setInfo(sprintf($this->lng->txt("ordertext_info"), $this->object->getSeparator()));
        $ordertext->setRows(10);
        $ordertext->setCols(80);
        $form->addItem($ordertext);
        // textsize
        $textsize = new ilNumberInputGUI($this->lng->txt("textsize"), "textsize");
        $textsize->setValue($this->object->getTextSize());
        $textsize->setInfo($this->lng->txt("textsize_info"));
        $textsize->setSize(6);
        $textsize->setMinValue(10);
        $textsize->setRequired(false);
        $form->addItem($textsize);
        // points
        $points = new ilNumberInputGUI($this->lng->txt("points"), "points");

        $points->allowDecimals(true);
        // mbecker: Fix for mantis bug 7866: Predefined values schould make sense.
        // This implements a default value of "1" for this question type.
        if ($this->object->getPoints() == null) {
            $points->setValue("1");
        } else {
            $points->setValue($this->object->getPoints());
        }
        $points->setRequired(true);
        $points->setSize(3);
        $points->setMinValue(0.0);
        $points->setMinvalueShouldBeGreater(true);
        $form->addItem($points);
        return $form;
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
            $this->aggregateAnswers($relevant_answers, $this->object->getOrderText())
        )->get();
    }

    public function aggregateAnswers($relevant_answers_chosen, $answer_defined_on_question): array
    {
        $aggregate = array();
        foreach ($relevant_answers_chosen as $answer) {
            $answer = str_replace($this->object->getAnswerSeparator(), '&nbsp;&nbsp;-&nbsp;&nbsp;', $answer);
            if (in_array($answer['value1'], $aggregate)) {
                $aggregate[$answer['value1']] = $aggregate[$answer['value1']] + 1;
            } else {
                $aggregate[$answer['value1']] = 1;
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

        foreach ($aggregate as $key => $line_data) {
            $tpl->setCurrentBlock('aggregaterow');
            $tpl->setVariable('COUNT', $line_data);
            $tpl->setVariable('OPTION', $key);
            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }

    public function getAnswersFrequency($relevantAnswers, $questionIndex): array
    {
        $answers = array();

        foreach ($relevantAnswers as $ans) {
            $md5 = md5($ans['value1']);

            if (!isset($answers[$md5])) {
                $answer = str_replace(
                    $this->object->getAnswerSeparator(),
                    '&nbsp;&nbsp;-&nbsp;&nbsp;',
                    $ans['value1']
                );

                $answers[$md5] = array(
                    'answer' => $answer, 'frequency' => 0
                );
            }

            $answers[$md5]['frequency']++;
        }

        return $answers;
    }

    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        // points
        $points = new ilNumberInputGUI($this->lng->txt("points"), "points");

        $points->allowDecimals(true);
        $points->setValue($this->object->getPoints());
        $points->setRequired(true);
        $points->setSize(3);
        $points->setMinValue(0.0);
        $points->setMinvalueShouldBeGreater(true);
        $form->addItem($points);
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        $this->object->setPoints((float) str_replace(',', '.', $form->getInput('points')));
    }
}
