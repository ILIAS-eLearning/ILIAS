<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';
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
        require_once './Modules/TestQuestionPool/classes/class.assTextSubset.php';
        $this->object = new assTextSubset();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function writePostData($always = false)
    {
        $hasErrors = (!$always) ? $this->editQuestion(true) : false;
        if (!$hasErrors) {
            require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
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
    public function editQuestion($checkonly = false)
    {
        $save = $this->isSaveCommand();
        $this->getQuestionTemplate();

        require_once './Services/Form/classes/class.ilPropertyFormGUI.php';
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
    public function addanswers()
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['addanswers']);
        $this->object->addAnswer("", 0, $position+1);
        $this->editQuestion();
    }

    /**
    * Remove an answer
    */
    public function removeanswers()
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['removeanswers']);
        $this->object->deleteAnswer($position);
        $this->editQuestion();
    }

    /**
    * Get the question solution output
    *
    * @param integer $active_id The active user id
    * @param integer $pass The test pass
    * @param boolean $graphicalOutput Show visual feedback for right/wrong answers
    * @param boolean $result_output Show the reached points for parts of the question
    * @param boolean $show_question_only Show the question without the ILIAS content around
    * @param boolean $show_feedback Show the question feedback
    * @param boolean $show_correct_solution Show the correct solution instead of the user solution
    * @param boolean $show_manual_scoring Show specific information for the manual scoring output
    * @return The solution output of the question as HTML code
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
    ) {
        // get the solution of the user for the active pass or from the last pass if allowed
        $solutions = array();
        if (($active_id > 0) && (!$show_correct_solution)) {
            $solutions =&$this->object->getSolutionValues($active_id, $pass);
        } else {
            $rank = array();
            foreach ($this->object->answers as $answer) {
                if ($answer->getPoints() > 0) {
                    if (!is_array($rank[$answer->getPoints()])) {
                        $rank[$answer->getPoints()] = array();
                    }
                    array_push($rank[$answer->getPoints()], $answer->getAnswertext());
                }
            }
            krsort($rank, SORT_NUMERIC);
            foreach ($rank as $index => $bestsolutions) {
                array_push($solutions, array("value1" => join(",", $bestsolutions), "points" => $index));
            }
        }
        
        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_textsubset_output_solution.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        $available_answers =&$this->object->getAvailableAnswers();
        for ($i = 0; $i < $this->object->getCorrectAnswers(); $i++) {
            if ((!$test_id) && (strcmp($solutions[$i]["value1"], "") == 0)) {
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
                        if ($correct) {
                            $template->setCurrentBlock("icon_ok");
                            $template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
                            $template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
                            $template->parseCurrentBlock();
                        } else {
                            $template->setCurrentBlock("icon_ok");
                            $template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
                            $template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
                            $template->parseCurrentBlock();
                        }
                    }
                }
                $template->setCurrentBlock("textsubset_row");
                $template->setVariable("SOLUTION", $solutions[$i]["value1"]);
                $template->setVariable("COUNTER", $i+1);
                if ($result_output) {
                    $points = $solutions[$i]["points"];
                    $resulttext = ($points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")";
                    $template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
                }
                $template->parseCurrentBlock();
            }
        }
        $questiontext = $this->object->getQuestion();
        if ($show_question_text==true) {
            $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        }
        $questionoutput = $template->get();
        $feedback = ($show_feedback && !$this->isTestPresentationContext()) ? $this->getAnswerFeedbackOutput($active_id, $pass) : "";
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
    
    public function getPreview($show_question_only = false, $showInlineFeedback = false)
    {
        $solutions = is_object($this->getPreviewSession()) ? (array) $this->getPreviewSession()->getParticipantsSolution() : array();
        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_textsubset_output.html", true, true, "Modules/TestQuestionPool");
        $width = $this->object->getMaxTextboxWidth();
        for ($i = 0; $i < $this->object->getCorrectAnswers(); $i++) {
            $template->setCurrentBlock("textsubset_row");
            foreach ($solutions as $idx => $solution_value) {
                if ($idx == $i) {
                    $template->setVariable("TEXTFIELD_VALUE", " value=\"" . $solution_value . "\"");
                }
            }
            $template->setVariable("COUNTER", $i+1);
            $template->setVariable("TEXTFIELD_ID", sprintf("%02d", $i+1));
            $template->setVariable("TEXTFIELD_SIZE", $width);
            $template->parseCurrentBlock();
        }
        $questiontext = $this->object->getQuestion();
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        $questionoutput = $template->get();
        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        return $questionoutput;
    }

    public function getTestOutput($active_id, $pass = null, $is_postponed = false, $use_post_solutions = false, $inlineFeedback = false)
    {
        // get the solution of the user for the active pass or from the last pass if allowed
        $user_solution = "";
        if ($active_id) {
            $solutions = null;
            // hey: prevPassSolutions - obsolete due to central check
            #include_once "./Modules/Test/classes/class.ilObjTest.php";
            #if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
            #{
            #	if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
            #}
            // hey.
            $solutions = $this->object->getUserSolutionPreferingIntermediate($active_id, $pass);
        }
        
        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_textsubset_output.html", true, true, "Modules/TestQuestionPool");
        $width = $this->object->getMaxTextboxWidth();
        for ($i = 0; $i < $this->object->getCorrectAnswers(); $i++) {
            $template->setCurrentBlock("textsubset_row");
            foreach ($solutions as $idx => $solution_value) {
                if ($idx == $i) {
                    $template->setVariable("TEXTFIELD_VALUE", " value=\"" . ilUtil::prepareFormOutput($solution_value["value1"]) . "\"");
                }
            }
            $template->setVariable("COUNTER", $i+1);
            $template->setVariable("TEXTFIELD_ID", sprintf("%02d", $i+1));
            $template->setVariable("TEXTFIELD_SIZE", $width);
            $template->parseCurrentBlock();
        }
        $questiontext = $this->object->getQuestion();
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        $questionoutput = $template->get();
        $pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
        return $pageoutput;
    }

    /**
     * Sets the ILIAS tabs for this question type
     *
     * @access public
     *
     * @todo:	MOVE THIS STEPS TO COMMON QUESTION CLASS assQuestionGUI
     */
    public function setQuestionTabs()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->clearTargets();
        
        $this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        $q_type = $this->object->getQuestionType();

        if (strlen($q_type)) {
            $classname = $q_type . "GUI";
            $this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
            $this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
        }

        if ($_GET["q_id"]) {
            if ($rbacsystem->checkAccess('write', $_GET["ref_id"])) {
                // edit page
                $ilTabs->addTarget(
                    "edit_page",
                    $this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "edit"),
                    array("edit", "insert", "exec_pg"),
                    "",
                    "",
                    $force_active
                );
            }

            $this->addTab_QuestionPreview($ilTabs);
        }

        $force_active = false;
        if ($rbacsystem->checkAccess('write', $_GET["ref_id"])) {
            $url = "";
            if ($classname) {
                $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
            }
            // edit question properties
            $ilTabs->addTarget(
                "edit_question",
                $url,
                array("editQuestion", "save", "saveEdit", "addanswers", "removeanswers", "originalSyncForm"),
                $classname,
                "",
                $force_active
            );
        }

        // add tab for question feedback within common class assQuestionGUI
        $this->addTab_QuestionFeedback($ilTabs);

        // add tab for question hint within common class assQuestionGUI
        $this->addTab_QuestionHints($ilTabs);

        // add tab for question's suggested solution within common class assQuestionGUI
        $this->addTab_SuggestedSolution($ilTabs, $classname);

        // Assessment of questions sub menu entry
        if ($_GET["q_id"]) {
            $ilTabs->addTarget(
                "statistics",
                $this->ctrl->getLinkTargetByClass($classname, "assessment"),
                array("assessment"),
                $classname,
                ""
            );
        }

        $this->addBackTab($ilTabs);
    }
    
    public function getSpecificFeedbackOutput($userSolution)
    {
        $output = "";
        return $this->object->prepareTextareaOutput($output, true);
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
    {
        $this->object->setCorrectAnswers($_POST["correctanswers"]);
        $this->object->setTextRating($_POST["text_rating"]);
    }

    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form)
    {
        // Delete all existing answers and create new answers from the form data
        $this->object->flushAnswers();
        foreach ($_POST['answers']['answer'] as $index => $answer) {
            $answertext = $answer;
            $this->object->addAnswer($answertext, $_POST['answers']['points'][$index], $index);
        }
    }

    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form)
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
        $textrating   = new ilSelectInputGUI($this->lng->txt("text_rating"), "text_rating");
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

    public function populateAnswerSpecificFormPart(\ilPropertyFormGUI $form)
    {
        // Choices
        include_once "./Modules/TestQuestionPool/classes/class.ilAnswerWizardInputGUI.php";
        $choices = new ilAnswerWizardInputGUI($this->lng->txt("answers"), "answers");
        $choices->setRequired(true);
        $choices->setQuestionObject($this->object);
        $choices->setSingleline(true);
        $choices->setAllowMove(false);
        $choices->setMinValue(0.0);
        if ($this->object->getAnswerCount() == 0) {
            $this->object->addAnswer("", 0, 0);
        }
        $choices->setValues($this->object->getAnswers());
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
    public function getAfterParticipationSuppressionAnswerPostVars()
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
    public function getAfterParticipationSuppressionQuestionPostVars()
    {
        return array();
    }

    /**
     * Returns an html string containing a question specific representation of the answers so far
     * given in the test for use in the right column in the scoring adjustment user interface.
     *
     * @param array $relevant_answers
     *
     * @return string
     */
    public function getAggregatedAnswersView($relevant_answers)
    {
        return  $this->renderAggregateView(
            $this->aggregateAnswers($relevant_answers)
        )->get();
    }

    public function aggregateAnswers($relevant_answers_chosen)
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
    public function renderAggregateView($aggregate)
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
    
    public function getAnswersFrequency($relevantAnswers, $questionIndex)
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
        
        return $answers;
    }
    
    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        // Choices
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssAnswerCorrectionsInputGUI.php';
        $choices = new ilAssAnswerCorrectionsInputGUI($this->lng->txt("answers"), "answers");
        $choices->setRequired(true);
        $choices->setQuestionObject($this->object);
        $choices->setValues($this->object->getAnswers());
        $form->addItem($choices);
        
        return $form;
    }
    
    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        $points = $form->getInput('answers')['points'];
        
        foreach ($this->object->getAnswers() as $index => $answer) {
            /* @var ASS_AnswerBinaryStateImage $answer */
            $answer->setPoints((float) $points[$index]);
        }
    }
}
