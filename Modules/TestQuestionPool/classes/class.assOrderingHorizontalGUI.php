<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
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
    protected function writePostData($always = false)
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
    public function editQuestion($checkonly = false)
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
        $template = new ilTemplate("tpl.il_as_qpl_orderinghorizontal_output_solution.html", true, true, "Modules/TestQuestionPool");

        //$solutionvalue = "";
        if (($active_id > 0) && (!$show_correct_solution)) {
            $elements = [];
            $solutions = &$this->object->getSolutionValues($active_id, $pass);
            if (strlen($solutions[0]["value1"])) {
                $elements = explode("{::}", $solutions[0]["value1"]);
            }

            if (!count($elements)) {
                $elements = $this->object->getRandomOrderingElements();
            }

            foreach ($elements as $id => $element) {
                $template->setCurrentBlock("element");
                $template->setVariable("ELEMENT_ID", "sol_e_" . $this->object->getId() . "_$id");
                $template->setVariable("ELEMENT_VALUE", ilUtil::prepareFormOutput($element));
                $template->parseCurrentBlock();
            }

            //$solutionvalue = str_replace("{::}", " ", $solutions[0]["value1"]);
        } else {
            $elements = $this->object->getOrderingElements();
            foreach ($elements as $id => $element) {
                $template->setCurrentBlock("element");
                $template->setVariable("ELEMENT_ID", "sol_e_" . $this->object->getId() . "_$id");
                $template->setVariable("ELEMENT_VALUE", ilUtil::prepareFormOutput($element));
                $template->parseCurrentBlock();
            }
            //$solutionvalue = join($this->object->getOrderingElements(), " ");
        }

        if (($active_id > 0) && (!$show_correct_solution)) {
            if ($this->object->getStep() === null) {
                $reached_points = $this->object->getReachedPoints($active_id, $pass);
            } else {
                $reached_points = $this->object->calculateReachedPoints($active_id, $pass);
            }
            if ($graphicalOutput) {
                // output of ok/not ok icons for user entered solutions
                if ($reached_points == $this->object->getMaximumPoints()) {
                    $template->setCurrentBlock("icon_ok");
                    $template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
                    $template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
                    $template->parseCurrentBlock();
                } else {
                    $template->setCurrentBlock("icon_ok");
                    if ($reached_points > 0) {
                        $template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.svg"));
                        $template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
                    } else {
                        $template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
                        $template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
                    }
                    $template->parseCurrentBlock();
                }
            }
        } else {
            $reached_points = $this->object->getPoints();
        }

        if ($result_output) {
            $resulttext = ($reached_points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")";
            $template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $reached_points));
        }
        if ($show_question_text == true) {
            $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), true));
        }
        //		$template->setVariable("SOLUTION_TEXT", ilUtil::prepareFormOutput($solutionvalue));
        if ($this->object->textsize >= 10) {
            $template->setVariable("STYLE", " style=\"font-size: " . $this->object->textsize . "%;\"");
        }

        $questionoutput = $template->get();
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);


        $feedback = '';
        if ($show_feedback) {
            if (!$this->isTestPresentationContext()) {
                $fb = $this->getGenericFeedbackOutput($active_id, $pass);
                $feedback .= strlen($fb) ? $fb : '';
            }
            
            $fb = $this->getSpecificFeedbackOutput(array());
            $feedback .= strlen($fb) ? $fb : '';
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
    
    public function getPreview($show_question_only = false, $showInlineFeedback = false)
    {
        if (is_object($this->getPreviewSession()) && strlen((string) $this->getPreviewSession()->getParticipantsSolution())) {
            $elements = (string) $this->getPreviewSession()->getParticipantsSolution();
            $elements = $this->object->splitAndTrimOrderElementText($elements, $this->object->getAnswerSeparator());
        } else {
            $elements = $this->object->getRandomOrderingElements();
        }
        
        $template = new ilTemplate("tpl.il_as_qpl_orderinghorizontal_preview.html", true, true, "Modules/TestQuestionPool");
        foreach ($elements as $id => $element) {
            $template->setCurrentBlock("element");
            $template->setVariable("ELEMENT_ID", "e_" . $this->object->getId() . "_$id");
            $template->setVariable("ORDERING_VALUE", ilUtil::prepareFormOutput($element));
            $template->setVariable("ELEMENT_VALUE", ilUtil::prepareFormOutput($element));
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTION_ID", $this->object->getId());
        $template->setVariable("VALUE_ORDERRESULT", ' value="' . join($elements, '{::}') . '"');
        if ($this->object->textsize >= 10) {
            $template->setVariable("STYLE", " style=\"font-size: " . $this->object->textsize . "%;\"");
        }
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), true));
        $questionoutput = $template->get();
        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        if ($DIC['ilBrowser']->isMobile() || $DIC['ilBrowser']->isIpad()) {
            $this->tpl->addJavaScript('./libs/bower/bower_components/jqueryui-touch-punch/jquery.ui.touch-punch.min.js');
        }
        $this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/orderinghorizontal.js");
        return $questionoutput;
    }
    
    // hey: prevPassSolutions - pass will be always available from now on
    public function getTestOutput($active_id, $pass, $is_postponed = false, $use_post_solutions = false, $show_feedback = false)
    // hey.
    {
        // generate the question output
        $template = new ilTemplate("tpl.il_as_qpl_orderinghorizontal_output.html", true, true, "Modules/TestQuestionPool");
        $elements = $this->object->getRandomOrderingElements();
        
        if ($active_id) {
            // hey: prevPassSolutions - obsolete due to central check
            #$solutions = NULL;
            #include_once "./Modules/Test/classes/class.ilObjTest.php";
            #if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
            #{
            #	if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
            #}
            $solutions = $this->object->getTestOutputSolutions($active_id, $pass);
            // hey.
            if (is_array($solutions) && count($solutions) == 1) {
                $elements = explode("{::}", $solutions[0]["value1"]);
            }
        }
        if (!is_array($solutions) || count($solutions) == 0) {
            $_SESSION['qst_ordering_horizontal_elements'] = $elements;
        } else {
            unset($_SESSION['qst_ordering_horizontal_elements']);
        }
        foreach ($elements as $id => $element) {
            $template->setCurrentBlock("element");
            $template->setVariable("ELEMENT_ID", "e_" . $this->object->getId() . "_$id");
            $template->setVariable("ORDERING_VALUE", ilUtil::prepareFormOutput($element));
            $template->setVariable("ELEMENT_VALUE", ilUtil::prepareFormOutput($element));
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTION_ID", $this->object->getId());
        if ($this->object->textsize >= 10) {
            $template->setVariable("STYLE", " style=\"font-size: " . $this->object->textsize . "%;\"");
        }
        $template->setVariable("VALUE_ORDERRESULT", ' value="' . join($elements, '{::}') . '"');
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), true));
        $questionoutput = $template->get();
        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        if ($DIC['ilBrowser']->isMobile() || $DIC['ilBrowser']->isIpad()) {
            $this->tpl->addJavaScript('./libs/bower/bower_components/jqueryui-touch-punch/jquery.ui.touch-punch.min.js');
        }
        $this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/orderinghorizontal.js");
        $questionoutput = $template->get();
        $pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
        return $pageoutput;
    }

    /**
    * Saves the feedback for a single choice question
    *
    * @access public
    */
    public function saveFeedback()
    {
        include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
        $errors = $this->feedback(true);
        $this->object->saveFeedbackGeneric(0, $_POST["feedback_incomplete"]);
        $this->object->saveFeedbackGeneric(1, $_POST["feedback_complete"]);
        $this->object->cleanupMediaObjectUsage();
        parent::saveFeedback();
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
            $commands = $_POST["cmd"];
            if (is_array($commands)) {
                foreach ($commands as $key => $value) {
                    if (preg_match("/^suggestrange_.*/", $key, $matches)) {
                        $force_active = true;
                    }
                }
            }
            // edit question properties
            $ilTabs->addTarget(
                "edit_question",
                $url,
                array("editQuestion", "save", "saveEdit", "originalSyncForm"),
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
        if (strpos($this->object->getOrderText(), '::')) {
            $answers = explode('::', $this->object->getOrderText());
        } else {
            $answers = explode(' ', $this->object->getOrderText());
        }

        if (!$this->object->feedbackOBJ->specificAnswerFeedbackExists()) {
            return '';
        }

        $output = '<table class="test_specific_feedback"><tbody>';

        foreach ($answers as $idx => $answer) {
            $feedback = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                $this->object->getId(),
                0,
                $idx
            );

            $output .= "<tr><td>{$answer}</td><td>{$feedback}</td></tr>";
        }

        $output .= '</tbody></table>';

        return $this->object->prepareTextareaOutput($output, true);
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
    {
        $this->object->setTextSize($_POST["textsize"]);
        $this->object->setOrderText($_POST["ordertext"]);
        $this->object->setPoints($_POST["points"]);
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

    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form)
    {
        // ordertext
        $ordertext = new ilTextAreaInputGUI($this->lng->txt("ordertext"), "ordertext");
        $ordertext->setValue($this->object->prepareTextareaOutput($this->object->getOrderText()));
        $ordertext->setRequired(true);
        $ordertext->setInfo(sprintf($this->lng->txt("ordertext_info"), $this->object->separator));
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
            $this->aggregateAnswers($relevant_answers, $this->object->getOrderText())
        )->get();
    }

    public function aggregateAnswers($relevant_answers_chosen, $answer_defined_on_question)
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
    public function renderAggregateView($aggregate)
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
    
    public function getAnswersFrequency($relevantAnswers, $questionIndex)
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
    
    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form)
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
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        $this->object->setPoints((float) $form->getInput('points'));
    }
}
