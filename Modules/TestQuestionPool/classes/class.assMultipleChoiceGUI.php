<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Multiple choice question GUI representation
 *
 * The assMultipleChoiceGUI class encapsulates the GUI representation
 * for multiple choice questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 * @ilCtrl_Calls assMultipleChoiceGUI: ilFormPropertyDispatchGUI
 */
class assMultipleChoiceGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
    public $choiceKeys;

    /**
    * assMultipleChoiceGUI constructor
    *
    * The constructor takes possible arguments an creates an instance of the assMultipleChoiceGUI object.
    *
    * @param integer $id The database id of a multiple choice question object
    * @access public
    */
    public function __construct($id = -1)
    {
        parent::__construct();
        include_once "./Modules/TestQuestionPool/classes/class.assMultipleChoice.php";
        $this->object = new assMultipleChoice();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }

    /**
     * @return bool
     */
    public function hasInlineFeedback()
    {
        return $this->object->feedbackOBJ->isSpecificAnswerFeedbackAvailable($this->object->getId());
    }

    /**
     * {@inheritdoc}
     */
    protected function writePostData($always = false)
    {
        $hasErrors = (!$always) ? $this->editQuestion(true) : false;
        if (!$hasErrors) {
            $form = $this->buildEditForm();
            $form->setValuesByPost();
            require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
            $this->writeQuestionGenericPostData();
            $this->writeQuestionSpecificPostData($form);
            $this->writeAnswerSpecificPostData($form);
            $this->saveTaxonomyAssignments();
            return 0;
        }
        return 1;
    }

    /**
     * Get the single/multiline editing of answers
     * - The settings of an already saved question is preferred
     * - A new question will use the setting of the last edited question by the user
     * @param bool	$checkonly	get the setting for checking a POST
     * @return bool
     */
    protected function getEditAnswersSingleLine($checkonly = false)
    {
        if ($checkonly) {
            // form posting is checked
            return ($_POST['types'] == 0) ? true : false;
        }

        $lastChange = $this->object->getLastChange();
        if (empty($lastChange) && !isset($_POST['types'])) {
            // a new question is edited
            return $this->object->getMultilineAnswerSetting() ? false : true;
        } else {
            // a saved question is edited
            return $this->object->isSingleline;
        }
    }

    /**
     * Creates an output of the edit form for the question
     *
     * @param bool $checkonly
     *
     * @return bool
     */
    public function editQuestion($checkonly = false)
    {
        $save = $this->isSaveCommand();
        $this->getQuestionTemplate();

        $form = $this->buildEditForm();

        $isSingleline = $this->getEditAnswersSingleLine($checkonly);
        if ($isSingleline) {
            $form->setMultipart(true);
        } else {
            $form->setMultipart(false);
        }

        $errors = false;

        if ($save) {
            $form->getItemByPostVar('selection_limit')->setMaxValue(count((array) $_POST['choice']['answer']));

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

    public function addBasicQuestionFormProperties($form)
    {
        parent::addBasicQuestionFormProperties($form);
        $form->getItemByPostVar('question')->setInitialRteWidth('100');
    }

    /**
     * Upload an image
     */
    public function uploadchoice()
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['uploadchoice']);
        $this->editQuestion();
    }

    /**
     * Remove an image
     */
    public function removeimagechoice()
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['removeimagechoice']);
        $filename = $_POST['choice']['imagename'][$position];
        $this->object->removeAnswerImage($position);
        $this->editQuestion();
    }

    /**
     * Add a new answer
     */
    public function addchoice()
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['addchoice']);
        $this->object->addAnswer("", 0, 0, $position + 1);
        $this->editQuestion();
    }

    /**
     * Remove an answer
     */
    public function removechoice()
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['removechoice']);
        $this->object->deleteAnswer($position);
        $this->editQuestion();
    }

    /**
     * Question type specific support of intermediate solution output
     * The function getSolutionOutput respects getUseIntermediateSolution()
     * @return bool
     */
    public function supportsIntermediateSolutionOutput()
    {
        return true;
    }

    /**
     * Get the question solution output
     *
     * @param integer $active_id             The active user id
     * @param integer $pass                  The test pass
     * @param boolean $graphicalOutput       Show visual feedback for right/wrong answers
     * @param boolean $result_output         Show the reached points for parts of the question
     * @param boolean $show_question_only    Show the question without the ILIAS content around
     * @param boolean $show_feedback         Show the question feedback
     * @param boolean $show_correct_solution Show the correct solution instead of the user solution
     * @param boolean $show_manual_scoring   Show specific information for the manual scoring output
     * @param bool    $show_question_text
     *
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
    ) {
        // shuffle output
        $keys = $this->getChoiceKeys();

        // get the solution of the user for the active pass or from the last pass if allowed
        $user_solution = array();
        if (($active_id > 0) && (!$show_correct_solution)) {
            $solutions = $this->object->getSolutionValues($active_id, $pass, !$this->getUseIntermediateSolution());
            foreach ($solutions as $idx => $solution_value) {
                array_push($user_solution, $solution_value["value1"]);
            }
        } else {
            // take the correct solution instead of the user solution
            foreach ($this->object->answers as $index => $answer) {
                $points_checked = $answer->getPointsChecked();
                $points_unchecked = $answer->getPointsUnchecked();
                if ($points_checked > $points_unchecked) {
                    if ($points_checked > 0) {
                        array_push($user_solution, $index);
                    }
                }
            }
        }

        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_mc_mr_output_solution.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        foreach ($keys as $answer_id) {
            $answer = $this->object->answers[$answer_id];
            if (($active_id > 0) && (!$show_correct_solution)) {
                if ($graphicalOutput) {
                    // output of ok/not ok icons for user entered solutions
                    $ok = false;
                    $checked = false;
                    foreach ($user_solution as $mc_solution) {
                        if (strcmp($mc_solution, $answer_id) == 0) {
                            $checked = true;
                        }
                    }
                    if ($checked) {
                        if ($answer->getPointsChecked() > $answer->getPointsUnchecked()) {
                            $ok = true;
                        } else {
                            $ok = false;
                        }
                    } else {
                        if ($answer->getPointsChecked() > $answer->getPointsUnchecked()) {
                            $ok = false;
                        } else {
                            $ok = true;
                        }
                    }
                    if ($ok) {
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
            if (strlen($answer->getImage())) {
                $template->setCurrentBlock("answer_image");
                if ($this->object->getThumbSize()) {
                    $template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $answer->getImage());
                } else {
                    $template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $answer->getImage());
                }
                $alt = $answer->getImage();
                if (strlen($answer->getAnswertext())) {
                    $alt = $answer->getAnswertext();
                }
                $alt = preg_replace("/<[^>]*?>/", "", $alt);
                $template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
                $template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
                $template->parseCurrentBlock();
            }

            if ($show_feedback) {
                if ($this->object->getSpecificFeedbackSetting() == 2) {
                    foreach ($user_solution as $mc_solution) {
                        if (strcmp($mc_solution, $answer_id) == 0) {
                            $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                                $this->object->getId(),
                                0,
                                $answer_id
                            );
                            if (strlen($fb)) {
                                $template->setCurrentBlock("feedback");
                                $template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($fb, true));
                                $template->parseCurrentBlock();
                            }
                        }
                    }
                }

                if ($this->object->getSpecificFeedbackSetting() == 1) {
                    $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                        $this->object->getId(),
                        0,
                        $answer_id
                    );
                    if (strlen($fb)) {
                        $template->setCurrentBlock("feedback");
                        $template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($fb, true));
                        $template->parseCurrentBlock();
                    }
                }

                if ($this->object->getSpecificFeedbackSetting() == 3) {
                    $answer = $this->object->getAnswer($answer_id);

                    if ($answer->getPoints() > 0) {
                        $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                            $this->object->getId(),
                            0,
                            $answer_id
                        );
                        if (strlen($fb)) {
                            $template->setCurrentBlock("feedback");
                            $template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($fb, true));
                            $template->parseCurrentBlock();
                        }
                    }
                }
            }
            $template->setCurrentBlock("answer_row");
            $template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), true));
            $checked = false;
            if ($result_output) {
                $pointschecked = $this->object->answers[$answer_id]->getPointsChecked();
                $pointsunchecked = $this->object->answers[$answer_id]->getPointsUnchecked();
                $resulttextchecked = ($pointschecked == 1) || ($pointschecked == -1) ? "%s " . $this->lng->txt("point") : "%s " . $this->lng->txt("points");
                $resulttextunchecked = ($pointsunchecked == 1) || ($pointsunchecked == -1) ? "%s " . $this->lng->txt("point") : "%s " . $this->lng->txt("points");
                $template->setVariable("RESULT_OUTPUT", sprintf("(" . $this->lng->txt("checkbox_checked") . " = $resulttextchecked, " . $this->lng->txt("checkbox_unchecked") . " = $resulttextunchecked)", $pointschecked, $pointsunchecked));
            }
            foreach ($user_solution as $mc_solution) {
                if (strcmp($mc_solution, $answer_id) == 0) {
                    if ($this->renderPurposeSupportsFormHtml() || $this->isRenderPurposePrintPdf()) {
                        $template->setVariable("SOLUTION_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_checked.png")));
                        $template->setVariable("SOLUTION_ALT", $this->lng->txt("checked"));
                    } else {
                        $template->setVariable('QID', $this->object->getId());
                        $template->setVariable('SUFFIX', $show_correct_solution ? 'bestsolution' : 'usersolution');
                        $template->setVariable('SOLUTION_VALUE', $answer_id);
                        $template->setVariable('SOLUTION_CHECKED', 'checked');
                    }
                    $checked = true;
                }
            }
            if (!$checked) {
                if ($this->renderPurposeSupportsFormHtml() || $this->isRenderPurposePrintPdf()) {
                    $template->setVariable("SOLUTION_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.png")));
                    $template->setVariable("SOLUTION_ALT", $this->lng->txt("unchecked"));
                } else {
                    $template->setVariable('QID', $this->object->getId());
                    $template->setVariable('SUFFIX', $show_correct_solution ? 'bestsolution' : 'usersolution');
                    $template->setVariable('SOLUTION_VALUE', $answer_id);
                }
            }
            $template->parseCurrentBlock();
        }
        $questiontext = $this->object->getQuestionForHTMLOutput();
        if ($show_feedback && $this->hasInlineFeedback()) {
            $questiontext .= $this->buildFocusAnchorHtml();
        }
        if ($show_question_text == true) {
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
        $user_solution = is_object($this->getPreviewSession()) ? (array) $this->getPreviewSession()->getParticipantsSolution() : array();
        // shuffle output
        $keys = $this->getChoiceKeys();

        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_mc_mr_output.html", true, true, "Modules/TestQuestionPool");
        foreach ($keys as $answer_id) {
            $answer = $this->object->answers[$answer_id];
            if (strlen($answer->getImage())) {
                if ($this->object->getThumbSize()) {
                    $template->setCurrentBlock("preview");
                    $template->setVariable("URL_PREVIEW", $this->object->getImagePathWeb() . $answer->getImage());
                    $template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
                    $template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
                    $template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $answer->getImage());
                    list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getImage());
                    $alt = $answer->getImage();
                    if (strlen($answer->getAnswertext())) {
                        $alt = $answer->getAnswertext();
                    }
                    $alt = preg_replace("/<[^>]*?>/", "", $alt);
                    $template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
                    $template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
                    $template->parseCurrentBlock();
                } else {
                    $template->setCurrentBlock("answer_image");
                    $template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $answer->getImage());
                    list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getImage());
                    $alt = $answer->getImage();
                    if (strlen($answer->getAnswertext())) {
                        $alt = $answer->getAnswertext();
                    }
                    $alt = preg_replace("/<[^>]*?>/", "", $alt);
                    $template->setVariable("ATTR", $attr);
                    $template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
                    $template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
                    $template->parseCurrentBlock();
                }
            }

            if ($showInlineFeedback) {
                $this->populateSpecificFeedbackInline($user_solution, $answer_id, $template);
            }

            $template->setCurrentBlock("answer_row");
            $template->setVariable("QID", $this->object->getId());
            $template->setVariable("ANSWER_ID", $answer_id);
            $template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), true));
            foreach ($user_solution as $mc_solution) {
                if (strcmp($mc_solution, $answer_id) == 0) {
                    $template->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
                }
            }
            $template->parseCurrentBlock();
        }
        if ($this->object->getSelectionLimit()) {
            $template->setVariable('SELECTION_LIMIT_HINT', sprintf(
                $this->lng->txt('ass_mc_sel_lim_hint'),
                $this->object->getSelectionLimit(),
                $this->object->getAnswerCount()
            ));

            $template->setVariable('SELECTION_LIMIT_VALUE', $this->object->getSelectionLimit());
        } else {
            $template->setVariable('SELECTION_LIMIT_VALUE', 'null');
        }
        $template->setVariable("QUESTION_ID", $this->object->getId());
        $questiontext = $this->object->getQuestionForHTMLOutput();
        if ($showInlineFeedback && $this->hasInlineFeedback()) {
            $questiontext .= $this->buildFocusAnchorHtml();
        }
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        $questionoutput = $template->get();
        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        return $questionoutput;
    }

    /**
     * @param integer		$active_id
     * @param integer|null	$pass
     * @param bool			$is_postponed
     * @param bool			$use_post_solutions
     * @param bool			$show_feedback
     *
     * @return string
     */
    public function getTestOutput(
        $active_id,
        // hey: prevPassSolutions - will be always available from now on
        $pass,
        // hey.
        $is_postponed = false,
        $use_post_solutions = false,
        $show_feedback = false
    ) {
        // shuffle output
        $keys = $this->getChoiceKeys();

        // get the solution of the user for the active pass or from the last pass if allowed
        $user_solution = array();
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
            foreach ($solutions as $idx => $solution_value) {
                // fau: testNav - don't add the dummy entry for 'none of the above' to the user options
                if ($solution_value["value1"] == 'mc_none_above') {
                    $this->setUseEmptySolutionInputChecked(true);
                    continue;
                }

                $user_solution[] = $solution_value["value1"];
                // fau.
            }

            if (empty($user_solution) && $this->object->getTestPresentationConfig()->isWorkedThrough()) {
                $this->setUseEmptySolutionInputChecked(true);
            }
        }
        // generate the question output
        $this->tpl->addJavaScript('Modules/TestQuestionPool/js/ilAssMultipleChoice.js');
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_mc_mr_output.html", true, true, "Modules/TestQuestionPool");
        foreach ($keys as $answer_id) {
            $answer = $this->object->answers[$answer_id];
            if (strlen($answer->getImage())) {
                if ($this->object->getThumbSize()) {
                    $template->setCurrentBlock("preview");
                    $template->setVariable("URL_PREVIEW", $this->object->getImagePathWeb() . $answer->getImage());
                    $template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
                    $template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
                    $template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $answer->getImage());
                    list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getImage());
                    $alt = $answer->getImage();
                    if (strlen($answer->getAnswertext())) {
                        $alt = $answer->getAnswertext();
                    }
                    $alt = preg_replace("/<[^>]*?>/", "", $alt);
                    $template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
                    $template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
                    $template->parseCurrentBlock();
                } else {
                    $template->setCurrentBlock("answer_image");
                    $template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $answer->getImage());
                    list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getImage());
                    $alt = $answer->getImage();
                    if (strlen($answer->getAnswertext())) {
                        $alt = $answer->getAnswertext();
                    }
                    $alt = preg_replace("/<[^>]*?>/", "", $alt);
                    $template->setVariable("ATTR", $attr);
                    $template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
                    $template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
                    $template->parseCurrentBlock();
                }
            }

            if ($show_feedback) {
                $this->populateSpecificFeedbackInline($user_solution, $answer_id, $template);
            }

            $template->setCurrentBlock("answer_row");
            $template->setVariable("QID", $this->object->getId());
            $template->setVariable("ANSWER_ID", $answer_id);
            $template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), true));
            foreach ($user_solution as $mc_solution) {
                if (strcmp($mc_solution, $answer_id) == 0) {
                    $template->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
                }
            }
            $template->parseCurrentBlock();
        }

        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $template->setVariable("QUESTION_ID", $this->object->getId());
        if ($this->object->getSelectionLimit()) {
            $template->setVariable('SELECTION_LIMIT_HINT', sprintf(
                $this->lng->txt('ass_mc_sel_lim_hint'),
                $this->object->getSelectionLimit(),
                $this->object->getAnswerCount()
            ));

            $template->setVariable('SELECTION_LIMIT_VALUE', $this->object->getSelectionLimit());
        } else {
            $template->setVariable('SELECTION_LIMIT_VALUE', 'null');
        }
        $questionoutput = $template->get();
        $pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput, $show_feedback);
        return $pageoutput;
    }

    protected $useEmptySolutionInputChecked = false;

    public function isUseEmptySolutionInputChecked()
    {
        return $this->useEmptySolutionInputChecked;
    }

    public function setUseEmptySolutionInputChecked($useEmptySolutionInputChecked)
    {
        $this->useEmptySolutionInputChecked = $useEmptySolutionInputChecked;
    }

    protected function getUseUnchangedAnswerCheckboxHtml()
    {
        // hey: prevPassSolutions - use abstracted template to share with other purposes of this kind
        $this->tpl->addJavaScript('Modules/TestQuestionPool/js/ilAssMultipleChoice.js');

        $tpl = new ilTemplate('tpl.tst_question_additional_behaviour_checkbox.html', true, true, 'Modules/TestQuestionPool');

        // HEY: affects next if (!) /// noneAboveChecked repaired but disabled because the checked input ..
        if (false) { // .. makes the qstEditController initialize the "edit" instead of the "answered" state
        if ($this->isUseEmptySolutionInputChecked()) {
            $tpl->setCurrentBlock('checked');
            $tpl->touchBlock('checked');
            $tpl->parseCurrentBlock();
        }
        }

        $tpl->setCurrentBlock('checkbox');
        $tpl->setVariable('TXT_FORCE_FORM_DIFF_LABEL', $this->object->getTestPresentationConfig()->getUseUnchangedAnswerLabel());
        $tpl->parseCurrentBlock();
        // hey.
        return $tpl->get();
    }

    public function getPresentationJavascripts()
    {
        return array('Modules/TestQuestionPool/js/ilAssMultipleChoice.js');
    }

    /**
     * Create the key index numbers for the array of choices
     *
     * @return array
     */
    public function getChoiceKeys()
    {
        $choiceKeys = array_keys($this->object->answers);

        if ($this->object->getShuffle()) {
            $choiceKeys = $this->object->getShuffler()->shuffle($choiceKeys);
        }

        return $choiceKeys;
    }

    public function getSpecificFeedbackOutput($userSolution)
    {
        // No return value, this question type supports inline specific feedback.
        $output = "";
        return $this->object->prepareTextareaOutput($output, true);
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
    {
        $this->object->setShuffle($_POST["shuffle"]);

        $selectionLimit = (int) $form->getItemByPostVar('selection_limit')->getValue();
        $this->object->setSelectionLimit($selectionLimit > 0 ? $selectionLimit : null);

        $this->object->setSpecificFeedbackSetting($_POST['feedback_setting']);

        $this->object->setMultilineAnswerSetting($_POST["types"]);
        if (is_array($_POST['choice']['imagename']) && $_POST["types"] == 1) {
            $this->object->isSingleline = true;
            ilUtil::sendInfo($this->lng->txt('info_answer_type_change'), true);
        } else {
            $this->object->isSingleline = ($_POST["types"] == 0) ? true : false;
        }
        $this->object->setThumbSize((strlen($_POST["thumb_size"])) ? $_POST["thumb_size"] : "");
    }

    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form)
    {
        // Delete all existing answers and create new answers from the form data
        $this->object->flushAnswers();
        if ($this->object->isSingleline) {
            foreach ($_POST['choice']['answer'] as $index => $answertext) {
                $answertext = ilUtil::secureString(htmlentities($answertext));
                $picturefile = $_POST['choice']['imagename'][$index];
                $file_org_name = $_FILES['choice']['name']['image'][$index];
                $file_temp_name = $_FILES['choice']['tmp_name']['image'][$index];

                if (strlen($file_temp_name)) {
                    // check suffix
                    $suffix = strtolower(array_pop(explode(".", $file_org_name)));
                    if (in_array($suffix, array( "jpg", "jpeg", "png", "gif" ))) {
                        // upload image
                        $filename = $this->object->buildHashedImageFilename($file_org_name);
                        if ($this->object->setImageFile($filename, $file_temp_name) == 0) {
                            $picturefile = $filename;
                        }
                    }
                }
                $this->object->addAnswer(
                    $answertext,
                    $_POST['choice']['points'][$index],
                    $_POST['choice']['points_unchecked'][$index],
                    $index,
                    $picturefile,
                    $_POST['choice']['answer_id'][$index]
                );
            }
        } else {
            foreach ($_POST['choice']['answer'] as $index => $answer) {
                $answertext = $answer;
                $this->object->addAnswer(
                    $answertext,
                    $_POST['choice']['points'][$index],
                    $_POST['choice']['points_unchecked'][$index],
                    $index,
                    "",
                    $_POST['choice']['answer_id'][$index]
                );
            }
        }
    }

    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form)
    {
        // shuffle
        $shuffle = new ilCheckboxInputGUI($this->lng->txt("shuffle_answers"), "shuffle");
        $shuffle->setValue(1);
        $shuffle->setChecked($this->object->getShuffle());
        $shuffle->setRequired(false);
        $form->addItem($shuffle);

        require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
        $selLim = new ilNumberInputGUI($this->lng->txt('ass_mc_sel_lim_setting'), 'selection_limit');
        $selLim->setInfo($this->lng->txt('ass_mc_sel_lim_setting_desc'));
        $selLim->setSize(2);
        $selLim->setRequired(false);
        $selLim->allowDecimals(false);
        $selLim->setMinvalueShouldBeGreater(false);
        $selLim->setMaxvalueShouldBeLess(false);
        $selLim->setMinValue(1);
        $selLim->setMaxValue($this->object->getAnswerCount());
        $selLim->setValue($this->object->getSelectionLimit());
        $form->addItem($selLim);

        if ($this->object->getId()) {
            $hidden = new ilHiddenInputGUI("", "ID");
            $hidden->setValue($this->object->getId());
            $form->addItem($hidden);
        }

        $isSingleline = $this->getEditAnswersSingleLine();

        if (!$this->object->getSelfAssessmentEditingMode()) {
            // Answer types
            $types = new ilSelectInputGUI($this->lng->txt("answer_types"), "types");
            $types->setRequired(false);
            $types->setValue(($isSingleline) ? 0 : 1);
            $types->setOptions(
                array(
                                    0 => $this->lng->txt('answers_singleline'),
                                    1 => $this->lng->txt('answers_multiline'),
                                )
            );
            $form->addItem($types);
        }

        if ($isSingleline) {
            // thumb size
            $thumb_size = new ilNumberInputGUI($this->lng->txt("thumb_size"), "thumb_size");
            $thumb_size->setSuffix($this->lng->txt("thumb_size_unit_pixel"));
            $thumb_size->setMinValue(20);
            $thumb_size->setDecimals(0);
            $thumb_size->setSize(6);
            $thumb_size->setInfo($this->lng->txt('thumb_size_info'));
            $thumb_size->setValue($this->object->getThumbSize());
            $thumb_size->setRequired(false);
            $form->addItem($thumb_size);
            return $isSingleline;
        }
        return $isSingleline;
    }

    public function populateAnswerSpecificFormPart(\ilPropertyFormGUI $form)
    {
        // Choices
        include_once "./Modules/TestQuestionPool/classes/class.ilMultipleChoiceWizardInputGUI.php";
        $choices = new ilMultipleChoiceWizardInputGUI($this->lng->txt("answers"), "choice");
        $choices->setRequired(true);
        $choices->setQuestionObject($this->object);
        $isSingleline = $this->getEditAnswersSingleLine();
        $choices->setSingleline($isSingleline);
        $choices->setAllowMove(false);
        if ($this->object->getSelfAssessmentEditingMode()) {
            $choices->setSize(40);
        }
        $choices->setMaxLength(800);
        if ($this->object->getAnswerCount() == 0) {
            $this->object->addAnswer("", 0, 0, 0);
        }
        $choices->setValues(array_map(
            function (ASS_AnswerMultipleResponseImage $value) {
                $value->setAnswerText(html_entity_decode($value->getAnswerText()));
                return $value;
            },
            $this->object->getAnswers()
        ));
        $form->addItem($choices);
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
            $this->aggregateAnswers($relevant_answers, $this->object->getAnswers())
        )->get();
    }

    public function aggregateAnswers($relevant_answers_chosen, $answers_defined_on_question)
    {
        $aggregate = array();
        foreach ($answers_defined_on_question as $answer) {
            $aggregated_info_for_answer = array();
            $aggregated_info_for_answer['answertext'] = $answer->getAnswerText();
            $aggregated_info_for_answer['count_checked'] = 0;

            foreach ($relevant_answers_chosen as $relevant_answer) {
                if ($relevant_answer['value1'] == $answer->getOrder()) {
                    $aggregated_info_for_answer['count_checked']++;
                }
            }
            $aggregated_info_for_answer['count_unchecked'] =
                ceil(count($relevant_answers_chosen) / count($answers_defined_on_question))
                - $aggregated_info_for_answer['count_checked'];

            $aggregate[] = $aggregated_info_for_answer;
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

        $tpl->setCurrentBlock('headercell');
        $tpl->setVariable('HEADER', $this->lng->txt('tst_answer_aggr_answer_header'));
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock('headercell');
        $tpl->setVariable('HEADER', $this->lng->txt('tst_answer_aggr_frequency_header'));
        $tpl->parseCurrentBlock();

        foreach ($aggregate as $line_data) {
            $tpl->setCurrentBlock('aggregaterow');
            $tpl->setVariable('OPTION', $line_data['answertext']);
            $tpl->setVariable('COUNT', $line_data['count_checked']);
            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }

    /**
     * @param $user_solution
     * @param $answer_id
     * @param $template
     * @return array
     */
    private function populateSpecificFeedbackInline($user_solution, $answer_id, $template)
    {
        if ($this->object->getSpecificFeedbackSetting() == 2) {
            foreach ($user_solution as $mc_solution) {
                if (strcmp($mc_solution, $answer_id) == 0) {
                    $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation($this->object->getId(), 0, $answer_id);
                    if (strlen($fb)) {
                        $template->setCurrentBlock("feedback");
                        $template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($fb, true));
                        $template->parseCurrentBlock();
                    }
                }
            }
        }

        if ($this->object->getSpecificFeedbackSetting() == 1) {
            $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation($this->object->getId(), 0, $answer_id);
            if (strlen($fb)) {
                $template->setCurrentBlock("feedback");
                $template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($fb, true));
                $template->parseCurrentBlock();
            }
        }

        if ($this->object->getSpecificFeedbackSetting() == 3) {
            $answer = $this->object->getAnswer($answer_id);

            if ($answer->getPoints() > 0) {
                $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation($this->object->getId(), 0, $answer_id);
                if (strlen($fb)) {
                    $template->setCurrentBlock("feedback");
                    $template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($fb, true));
                    $template->parseCurrentBlock();
                }
            }
        }
    }

    // fau: testNav - new functions setWithNoneAbove() and setIsAnswered()
    // moved functionality to ilTestQuestionPresentationConfig
    // fau.

    /**
     * @return ilPropertyFormGUI
     */
    protected function buildEditForm()
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setTableWidth("100%");
        $form->setId("assmultiplechoice");

        // title, author, description, question, working time (assessment mode)
        $this->addBasicQuestionFormProperties($form);
        $this->populateQuestionSpecificFormPart($form);
        $this->populateAnswerSpecificFormPart($form);
        $this->populateTaxonomyFormSection($form);
        $this->addQuestionFormCommandButtons($form);
        return $form;
    }

    public function getAnswersFrequency($relevantAnswers, $questionIndex)
    {
        $agg = $this->aggregateAnswers($relevantAnswers, $this->object->getAnswers());

        $answers = array();

        foreach ($agg as $ans) {
            $answers[] = array(
                'answer' => $ans['answertext'],
                'frequency' => $ans['count_checked']
            );
        }

        return $answers;
    }

    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssMultipleChoiceCorrectionsInputGUI.php';
        $choices = new ilAssMultipleChoiceCorrectionsInputGUI($this->lng->txt("answers"), "choice");
        $choices->setRequired(true);
        $choices->setQuestionObject($this->object);
        $choices->setValues($this->object->getAnswers());
        $form->addItem($choices);
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        $pointsChecked = $form->getInput('choice')['points'];
        $pointsUnchecked = $form->getInput('choice')['points_unchecked'];

        foreach ($this->object->getAnswers() as $index => $answer) {
            /* @var ASS_AnswerMultipleResponseImage $answer */
            $answer->setPointsChecked((float) $pointsChecked[$index]);
            $answer->setPointsUnchecked((float) $pointsUnchecked[$index]);
        }
    }
}
