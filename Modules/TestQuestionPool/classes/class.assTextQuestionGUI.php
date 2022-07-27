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
 */

/**
 * Text question GUI representation
 *
 * The assTextQuestionGUI class encapsulates the GUI representation for text questions.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 * @ilCtrl_Calls assTextQuestionGUI: ilFormPropertyDispatchGUI
 */
class assTextQuestionGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
    /**
     * assTextQuestionGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the assTextQuestionGUI object.
     *
     * @param integer $id The database id of a text question object
     */
    public function __construct($id = -1)
    {
        parent::__construct();
        include_once "./Modules/TestQuestionPool/classes/class.assTextQuestion.php";
        $this->object = new assTextQuestion();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function writePostData(bool $always = false) : int
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
    *
    * @access public
    */
    public function editQuestion($checkonly = false) : bool
    {
        $save = $this->isSaveCommand();
        $this->getQuestionTemplate();

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $this->editForm = $form;

        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setMultipart(true);
        $form->setTableWidth("100%");
        $form->setId("asstextquestion");

        $this->addBasicQuestionFormProperties($form);
        $this->populateQuestionSpecificFormPart($form);
        $this->populateAnswerSpecificFormPart($form);

        
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
    
    private static function buildAnswerTextOnlyArray($answers) : array
    {
        $answerTexts = array();
        
        foreach ($answers as $answer) {
            $answerTexts[] = $answer->getAnswertext();
        }
        
        return $answerTexts;
    }

    public function magicAfterTestOutput() : void
    {
        // TODO - BEGIN: what exactly is done here? cant we use the parent method?

        include_once "./Services/RTE/classes/class.ilRTE.php";
        $rtestring = ilRTE::_getRTEClassname();
        include_once "./Services/RTE/classes/class.$rtestring.php";
        $rte = new $rtestring();
        $rte->addUserTextEditor("textinput");
        $this->outAdditionalOutput();

        // TODO - END: what exactly is done here? cant we use the parent method?
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
    ) : string {
        // get the solution of the user for the active pass or from the last pass if allowed
        
        $user_solution = $this->getUserAnswer($active_id, $pass);
        
        if (($active_id > 0) && (!$show_correct_solution)) {
            $solution = $user_solution;
        } else {
            $solution = $this->getBestAnswer($this->renderPurposeSupportsFormHtml());
        }
        
        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_text_question_output_solution.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");

        $solution = $this->object->getHtmlUserSolutionPurifier()->purify($solution);
        if ($this->renderPurposeSupportsFormHtml()) {
            $template->setCurrentBlock('essay_div');
            $template->setVariable("DIV_ESSAY", $this->object->prepareTextareaOutput($solution, true));
        } else {
            $template->setCurrentBlock('essay_textarea');
            $template->setVariable("TA_ESSAY", $this->object->prepareTextareaOutput($solution, true, true));
        }
        $template->parseCurrentBlock();

        $questiontext = $this->object->getQuestion();
        
        if (!$show_correct_solution) {
            $max_no_of_chars = $this->object->getMaxNumOfChars();
            
            if ($max_no_of_chars == 0) {
                $max_no_of_chars = ucfirst($this->lng->txt('unlimited'));
            }
            
            $act_no_of_chars = $this->object->countLetters($solution);
            $template->setVariable("CHARACTER_INFO", '<b>' . $max_no_of_chars . '</b>' .
                $this->lng->txt('answer_characters') . ' <b>' . $act_no_of_chars . '</b>');

            if ($this->object->isWordCounterEnabled()) {
                $template->setCurrentBlock('word_count');
                $template->setVariable(
                    'WORD_COUNT',
                    $this->lng->txt('qst_essay_written_words') .
                    ' <b>' . $this->object->countWords($solution) . '</b>'
                );
                $template->parseCurrentBlock();
            }
        }
        if (($active_id > 0) && (!$show_correct_solution)) {
            if ($graphicalOutput) {
                // output of ok/not ok icons for user entered solutions
                $reached_points = $this->object->getReachedPoints($active_id, $pass);
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
        }
        if ($show_question_text == true) {
            $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        }
        $questionoutput = $template->get();

        $feedback = '';
        if ($show_feedback) {
            if (!$this->isTestPresentationContext()) {
                $fb = $this->getGenericFeedbackOutput((int) $active_id, $pass);
                $feedback .= strlen($fb) ? $fb : '';
            }

            $fb = $this->getSpecificFeedbackOutput(
                array($user_solution => '')
            );

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

        $solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

        $solutionoutput = $solutiontemplate->get();
        if (!$show_question_only) {
            // get page object output
            $solutionoutput = $this->getILIASPage($solutionoutput);
        }
        return $solutionoutput;
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
    * @return string The solution output of the question as HTML code
    */
    public function getAutoSavedSolutionOutput(
        $active_id,
        $pass = null,
        $graphicalOutput = false,
        $result_output = false,
        $show_question_only = true,
        $show_feedback = false,
        $show_correct_solution = false,
        $show_manual_scoring = false,
        $show_question_text = true
    ) : string {
        // get the solution of the user for the active pass or from the last pass if allowed

        $user_solution = $this->getUserAnswer($active_id, $pass);

        if (($active_id > 0) && (!$show_correct_solution)) {
            $solution = $user_solution;
        } else {
            $solution = $this->getBestAnswer($this->renderPurposeSupportsFormHtml());
        }

        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_text_question_output_solution.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");

        $solution = $this->object->getHtmlUserSolutionPurifier()->purify($this->object->getLatestAutosaveContent($active_id));
        if ($this->renderPurposeSupportsFormHtml()) {
            $template->setCurrentBlock('essay_div');
            $template->setVariable("DIV_ESSAY", $this->object->prepareTextareaOutput($solution, true));
        } else {
            $template->setCurrentBlock('essay_textarea');
            $template->setVariable("TA_ESSAY", $this->object->prepareTextareaOutput($solution, true, true));
        }
        $template->parseCurrentBlock();

        $questiontext = $this->object->getQuestion();

        if (!$show_correct_solution) {
            $max_no_of_chars = $this->object->getMaxNumOfChars();

            if ($max_no_of_chars == 0) {
                $max_no_of_chars = ucfirst($this->lng->txt('unlimited'));
            }

            $act_no_of_chars = $this->object->countLetters($solution);
            $template->setVariable("CHARACTER_INFO", '<b>' . $max_no_of_chars . '</b>' .
                $this->lng->txt('answer_characters') . ' <b>' . $act_no_of_chars . '</b>');

            if ($this->object->isWordCounterEnabled()) {
                $template->setCurrentBlock('word_count');
                $template->setVariable(
                    'WORD_COUNT',
                    $this->lng->txt('qst_essay_written_words') .
                    ' <b>' . $this->object->countWords($solution) . '</b>'
                );
                $template->parseCurrentBlock();
            }
        }
        if ($show_question_text == true) {
            $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        }
        $questionoutput = $template->get();

        $feedback = '';
        if ($show_feedback) {
            if (!$this->isTestPresentationContext()) {
                $fb = $this->getGenericFeedbackOutput((int) $active_id, $pass);
                $feedback .= strlen($fb) ? $fb : '';
            }

            $fb = $this->getSpecificFeedbackOutput(
                array($user_solution => '')
            );

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

        $solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

        $solutionoutput = $solutiontemplate->get();
        if (!$show_question_only) {
            // get page object output
            $solutionoutput = $this->getILIASPage($solutionoutput);
        }
        return $solutionoutput;
    }

    private function getBestAnswer($asHtml) : string
    {
        $answers = $this->object->getAnswers();
        if (!count($answers)) {
            return '';
        }
        
        if ($asHtml) {
            $tplFile = 'tpl.il_as_qpl_text_question_best_solution_html.html';
        } else {
            $tplFile = 'tpl.il_as_qpl_text_question_best_solution_ta.html';
        }

        $tpl = new ilTemplate($tplFile, true, true, 'Modules/TestQuestionPool');
        
        foreach ($answers as $answer) {
            $keywordString = $answer->getAnswertext();
            
            if (in_array($this->object->getKeywordRelation(), assTextQuestion::getScoringModesWithPointsByKeyword())) {
                $keywordString .= ' ' . $this->lng->txt('for') . ' ';
                $keywordString .= $answer->getPoints() . ' ' . $this->lng->txt('points');
            }

            $tpl->setCurrentBlock('keyword');
            $tpl->setVariable('KEYWORD', $keywordString);
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setVariable('KEYWORD_HEADER', $this->lng->txt('solution_contain_keywords'));
        $tpl->setVariable('SCORING_LABEL', $this->lng->txt('essay_scoring_mode') . ': ');
        
        switch ($this->object->getKeywordRelation()) {
            case 'any':
                $tpl->setVariable('SCORING_MODE', $this->lng->txt('essay_scoring_mode_keyword_relation_any'));
                break;
            case 'all':
                $tpl->setVariable('SCORING_MODE', $this->lng->txt('essay_scoring_mode_keyword_relation_all'));
                break;
            case 'one':
                $tpl->setVariable('SCORING_MODE', $this->lng->txt('essay_scoring_mode_keyword_relation_one'));
                break;
        }

        return $tpl->get();
    }

    private function getUserAnswer($active_id, $pass)
    {
        $user_solution = "";
        $solutions = $this->object->getSolutionValues($active_id, $pass);
        foreach ($solutions as $idx => $solution_value) {
            $user_solution = $solution_value["value1"];
        }
        return $user_solution;
    }

    public function getPreview($show_question_only = false, $showInlineFeedback = false) : string
    {
        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_text_question_output.html", true, true, "Modules/TestQuestionPool");
        if ($this->object->getMaxNumOfChars()) {
            $template->setCurrentBlock("maximum_char_hint");
            $template->setVariable("MAXIMUM_CHAR_HINT", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxNumOfChars()));
            $template->parseCurrentBlock();
            #mbecker: No such block. $template->setCurrentBlock("has_maxchars");
            $template->setVariable("MAXCHARS", $this->object->getMaxNumOfChars());
            $template->parseCurrentBlock();
            $template->setCurrentBlock("maxchars_counter");
            $template->setVariable("QID", $this->object->getId());
            $template->setVariable("MAXCHARS", $this->object->getMaxNumOfChars());
            $template->setVariable("TEXTBOXSIZE", strlen($this->object->getMaxNumOfChars()));
            $template->setVariable("CHARACTERS", $this->lng->txt("qst_essay_chars_remaining"));
            $template->parseCurrentBlock();
        }

        if ($this->object->isWordCounterEnabled()) {
            $template->setCurrentBlock("word_counter");
            $template->setVariable("QID", $this->object->getId());
            $template->setVariable("WORDCOUNTER", $this->lng->txt("qst_essay_allready_written_words"));
            $template->parseCurrentBlock();
        }

        if (is_object($this->getPreviewSession())) {
            $template->setVariable(
                "ESSAY",
                ilLegacyFormElementsUtil::prepareFormOutput(
                    (string) $this->getPreviewSession()->getParticipantsSolution()
                )
            );
        }
        
        $questiontext = $this->object->getQuestion();
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        $template->setVariable("QID", $this->object->getId());
        
        $questionoutput = $template->get();
        
        $questionoutput .= $this->getJsCode();

        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        return $questionoutput;
    }

    public function getTestOutput($active_id, $pass = null, $is_postponed = false, $use_post_solutions = false, $inlineFeedback = false) : string
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
            foreach ($solutions as $idx => $solution_value) {
                $user_solution = $solution_value["value1"];
            }
        }
        
        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_text_question_output.html", true, true, "Modules/TestQuestionPool");
        if ($this->object->getMaxNumOfChars()) {
            $template->setCurrentBlock("maximum_char_hint");
            $template->setVariable("MAXIMUM_CHAR_HINT", sprintf($this->lng->txt("text_maximum_chars_allowed"), $this->object->getMaxNumOfChars()));
            $template->parseCurrentBlock();
            #mbecker: No such block. $template->setCurrentBlock("has_maxchars");
            $template->setVariable("MAXCHARS", $this->object->getMaxNumOfChars());
            $template->parseCurrentBlock();
            $template->setCurrentBlock("maxchars_counter");
            $template->setVariable("MAXCHARS", $this->object->getMaxNumOfChars());
            $template->setVariable("QID", $this->object->getId());
            $template->setVariable("TEXTBOXSIZE", strlen($this->object->getMaxNumOfChars()));
            $template->setVariable("CHARACTERS", $this->lng->txt("qst_essay_chars_remaining"));
            $template->parseCurrentBlock();
        }

        if ($this->object->isWordCounterEnabled()) {
            $template->setCurrentBlock("word_counter");
            $template->setVariable("QID", $this->object->getId());
            $template->setVariable("WORDCOUNTER", $this->lng->txt("qst_essay_allready_written_words"));
            $template->parseCurrentBlock();
        }

        $template->setVariable("QID", $this->object->getId());
        $template->setVariable("ESSAY", ilLegacyFormElementsUtil::prepareFormOutput($user_solution));
        $questiontext = $this->object->getQuestion();
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        $questionoutput = $template->get();
        
        $questionoutput .= $this->getJsCode();
        
        $pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
        include_once "./Services/YUI/classes/class.ilYuiUtil.php";
        ilYuiUtil::initDomEvent();
        return $pageoutput;
    }
    
    protected function getJsCode() : string
    {
        $tpl = new ilTemplate('tpl.charcounter.html', true, true, 'Modules/TestQuestionPool');
        
        $tpl->setCurrentBlock('tinymce_handler');
        $tpl->touchBlock('tinymce_handler');
        $tpl->parseCurrentBlock();
        
        if ($this->object->getMaxNumOfChars() > 0) {
            $tpl->setCurrentBlock('letter_counter_js');
            $tpl->setVariable("MAXCHARS", $this->object->getMaxNumOfChars());
            $tpl->parseCurrentBlock();
        }

        if ($this->object->isWordCounterEnabled()) {
            $tpl->setCurrentBlock('word_counter_js');
            $tpl->touchBlock('word_counter_js');
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock('counter_js');
        $tpl->setVariable("QID", $this->object->getId());
        $tpl->parseCurrentBlock();

        $this->tpl->addOnLoadCode('mcQuestionCharCounterInit();');
        return $tpl->get();
    }

    public function addSuggestedSolution() : void
    {
        ilSession::set("subquestion_index", 0);
        if ($_POST["cmd"]["addSuggestedSolution"]) {
            if ($this->writePostData()) {
                $this->tpl->setOnScreenMessage('info', $this->getErrorMessage());
                $this->editQuestion();
                return;
            }
            // @MBECKER: Check this in running test
            if (!$this->checkInput()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("fill_out_all_required_fields_add_answer"));
                $this->editQuestion();
                return;
            }
        }
        $this->object->saveToDb();
        $this->ctrl->setParameter($this, "q_id", $this->object->getId());
        $this->tpl->setVariable("HEADER", $this->object->getTitle());
        $this->getQuestionTemplate();
    }

    public function getSpecificFeedbackOutput(array $userSolution) : string
    {
        $user_solution = array_keys($userSolution);
        $user_answer = array_shift($user_solution);
  
        $feedback = '<table><tbody>';
      
        foreach ($this->object->getAnswers() as $idx => $ans) {
            if ($this->object->isKeywordMatching($user_answer, $ans->getAnswertext())) {
                $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                    $this->object->getId(),
                    0,
                    $idx
                );
                $feedback .= '<tr><td><b><i>' . $ans->getAnswertext() . '</i></b></td><td>';
                $feedback .= $fb . '</td> </tr>';
            }
        }
        
        $feedback .= '</tbody></table>';
        return $this->object->prepareTextareaOutput($feedback, true);
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form) : void
    {
        $this->object->setWordCounterEnabled(isset($_POST['wordcounter']) && $_POST['wordcounter']);
        $this->object->setMaxNumOfChars($_POST["maxchars"]);
        $this->object->setTextRating($_POST["text_rating"]);
        $this->object->setKeywordRelation($_POST['scoring_mode']);
    }

    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form) : void
    {
        switch ($this->object->getKeywordRelation()) {
            case 'non':
                $this->object->setAnswers(array());
                $this->object->setPoints($_POST['non_keyword_points']);
                break;
            case 'any':
                $this->object->setAnswers($_POST['any_keyword']);
                $this->object->setPoints($this->object->getMaximumPoints());
                break;
            case 'all':
                $this->object->setAnswers($_POST['all_keyword']);
                $this->object->setPoints($_POST['all_keyword_points']);
                break;
            case 'one':
                $this->object->setAnswers($_POST['one_keyword']);
                $this->object->setPoints($_POST['one_keyword_points']);
                break;
        }
    }

    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form) : ilPropertyFormGUI
    {
        // wordcounter
        $wordcounter = new ilCheckboxInputGUI($this->lng->txt('qst_essay_wordcounter_enabled'), 'wordcounter');
        $wordcounter->setInfo($this->lng->txt('qst_essay_wordcounter_enabled_info'));
        $wordcounter->setChecked($this->object->isWordCounterEnabled());
        $form->addItem($wordcounter);

        // maxchars
        $maxchars = new ilNumberInputGUI($this->lng->txt("maxchars"), "maxchars");
        $maxchars->setSize(5);
        if ($this->object->getMaxNumOfChars() > 0) {
            $maxchars->setValue($this->object->getMaxNumOfChars());
        }
        $maxchars->setInfo($this->lng->txt("description_maxchars"));
        $form->addItem($maxchars);

        // text rating
        $textrating = new ilSelectInputGUI($this->lng->txt("text_rating"), "text_rating");
        $text_options = array(
            "ci" => $this->lng->txt("cloze_textgap_case_insensitive"),
            "cs" => $this->lng->txt("cloze_textgap_case_sensitive"),
            "l1" => sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "1"),
            "l2" => sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "2"),
            "l3" => sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "3"),
            "l4" => sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "4"),
            "l5" => sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "5")
        );
        $textrating->setOptions($text_options);
        $textrating->setValue($this->object->getTextRating());
        $form->addItem($textrating);

        return $form;
    }

    public function populateAnswerSpecificFormPart(\ilPropertyFormGUI $form) : ilPropertyFormGUI
    {
        $scoringMode = new ilRadioGroupInputGUI(
            $this->lng->txt('essay_scoring_mode'),
            'scoring_mode'
        );

        $scoringOptionNone = new ilRadioOption(
            $this->lng->txt('essay_scoring_mode_without_keywords'),
            'non',
            $this->lng->txt(
                'essay_scoring_mode_without_keywords_desc'
            )
        );
        $scoringOptionAnyKeyword = new ilRadioOption(
            $this->lng->txt('essay_scoring_mode_keyword_relation_any'),
            'any',
            $this->lng->txt(
                'essay_scoring_mode_keyword_relation_any_desc'
            )
        );
        $scoringOptionAllKeyword = new ilRadioOption(
            $this->lng->txt('essay_scoring_mode_keyword_relation_all'),
            'all',
            $this->lng->txt(
                'essay_scoring_mode_keyword_relation_all_desc'
            )
        );
        $scoringOptionOneKeyword = new ilRadioOption(
            $this->lng->txt('essay_scoring_mode_keyword_relation_one'),
            'one',
            $this->lng->txt(
                'essay_scoring_mode_keyword_relation_one_desc'
            )
        );

        $scoringMode->addOption($scoringOptionNone);
        $scoringMode->addOption($scoringOptionAnyKeyword);
        $scoringMode->addOption($scoringOptionAllKeyword);
        $scoringMode->addOption($scoringOptionOneKeyword);
        $scoringMode->setRequired(true);
        $scoringMode->setValue(
            strlen($this->object->getKeywordRelation()) ? $this->object->getKeywordRelation(
                                ) : 'non'
        );

        if ($this->object->getAnswerCount() == 0) {
            $this->object->addAnswer("", 1, 0, 0);
        }
        require_once "./Modules/TestQuestionPool/classes/class.ilEssayKeywordWizardInputGUI.php";

        // Without Keywords
        $nonKeywordPoints = new ilNumberInputGUI($this->lng->txt("points"), "non_keyword_points");
        $nonKeywordPoints->allowDecimals(true);
        $nonKeywordPoints->setValue($this->object->getPoints());
        $nonKeywordPoints->setRequired(true);
        $nonKeywordPoints->setSize(3);
        $nonKeywordPoints->setMinValue(0.0);
        $nonKeywordPoints->setMinvalueShouldBeGreater(true);
        $scoringOptionNone->addSubItem($nonKeywordPoints);

        // Any Keyword
        $anyKeyword = new ilEssayKeywordWizardInputGUI($this->lng->txt("answers"), "any_keyword");
        $anyKeyword->setRequired(true);
        $anyKeyword->setQuestionObject($this->object);
        $anyKeyword->setSingleline(true);
        $anyKeyword->setValues($this->object->getAnswers());
        $scoringOptionAnyKeyword->addSubItem($anyKeyword);

        // All Keywords
        $allKeyword = new ilTextWizardInputGUI($this->lng->txt("answers"), "all_keyword");
        $allKeyword->setRequired(true);
        //$allKeyword->setQuestionObject($this->object);
        //$allKeyword->setSingleline(TRUE);
        $allKeyword->setValues(self::buildAnswerTextOnlyArray($this->object->getAnswers()));
        $scoringOptionAllKeyword->addSubItem($allKeyword);
        $allKeywordPoints = new ilNumberInputGUI($this->lng->txt("points"), "all_keyword_points");
        $allKeywordPoints->allowDecimals(true);
        $allKeywordPoints->setValue($this->object->getPoints());
        $allKeywordPoints->setRequired(true);
        $allKeywordPoints->setSize(3);
        $allKeywordPoints->setMinValue(0.0);
        $allKeywordPoints->setMinvalueShouldBeGreater(true);
        $scoringOptionAllKeyword->addSubItem($allKeywordPoints);

        // One Keywords
        $oneKeyword = new ilTextWizardInputGUI($this->lng->txt("answers"), "one_keyword");
        $oneKeyword->setRequired(true);
        //$oneKeyword->setQuestionObject($this->object);
        //$oneKeyword->setSingleline(TRUE);
        $oneKeyword->setValues(self::buildAnswerTextOnlyArray($this->object->getAnswers()));
        $scoringOptionOneKeyword->addSubItem($oneKeyword);
        $oneKeywordPoints = new ilNumberInputGUI($this->lng->txt("points"), "one_keyword_points");
        $oneKeywordPoints->allowDecimals(true);
        $oneKeywordPoints->setValue($this->object->getPoints());
        $oneKeywordPoints->setRequired(true);
        $oneKeywordPoints->setSize(3);
        $oneKeywordPoints->setMinValue(0.0);
        $oneKeywordPoints->setMinvalueShouldBeGreater(true);
        $scoringOptionOneKeyword->addSubItem($oneKeywordPoints);

        $form->addItem($scoringMode);
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
    public function getAfterParticipationSuppressionAnswerPostVars() : array
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
    public function getAfterParticipationSuppressionQuestionPostVars() : array
    {
        return array();
    }

    /**
     * Returns an html string containing a question specific representation of the answers so far
     * given in the test for use in the right column in the scoring adjustment user interface.
     * @param array $relevant_answers
     * @return string
     */
    public function getAggregatedAnswersView(array $relevant_answers) : string
    {
        return ''; //print_r($relevant_answers,true);
    }
    
    public function isAnswerFreuqencyStatisticSupported() : bool
    {
        return false;
    }
    
    public function getAnswersFrequency($relevantAnswers, $questionIndex) : array
    {
        return array();
    }
    
    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form) : void
    {
        $this->populateQuestionSpecificFormPart($form);
        $this->populateAnswerSpecificFormPart($form);
        
        $form->removeItemByPostVar('maxchars');
    }
    
    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form) : void
    {
        $this->writeQuestionSpecificPostData($form);
        $this->writeAnswerSpecificPostData($form);
    }
}
