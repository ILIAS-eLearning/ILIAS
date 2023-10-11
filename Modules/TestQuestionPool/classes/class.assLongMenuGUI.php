<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
include_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 *
 * @ilCtrl_Calls assLongMenuGUI: ilPropertyFormGUI, ilFormPropertyDispatchGUI
 */
class assLongMenuGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable
{
    private $rbacsystem;
    private $ilTabs;
    public $lng;

    public function __construct($id = -1)
    {
        parent::__construct();
        include_once './Modules/TestQuestionPool/classes/class.assLongMenu.php';
        $this->object = new assLongMenu();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        $this->rbacsystem = $rbacsystem;
        $this->ilTabs = $ilTabs;
        $this->lng = $lng;
    }

    /**
     * @param $active_id
     * @param $pass
     * @return array
     */
    protected function getUserSolution($active_id, $pass)
    {
        $user_solution = array();
        if ($active_id) {
            // hey: prevPassSolutions - obsolete due to central check
            #$solutions = NULL;
            #include_once "./Modules/Test/classes/class.ilObjTest.php";
            #if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
            #{
            #	if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
            #}
            $solutions = $this->object->getSolutionValues($active_id, $pass, !$this->getUseIntermediateSolution());
            //$solutions = $this->object->getTestOutputSolutions($active_id, $pass);
            // hey.
            foreach ($solutions as $idx => $solution_value) {
                $user_solution[$solution_value["value1"]] = $solution_value["value2"];
            }
            return $user_solution;
        }
        return $user_solution;
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
        $form = $this->buildEditForm();
        $form->setValuesByPost();
        $check = $form->checkInput() && $this->verifyAnswerOptions();

        $this->writeQuestionGenericPostData();
        $this->writeQuestionSpecificPostData($form);
        $custom_check = $this->object->checkQuestionCustomPart($form);
        if (!$check || !$custom_check) {
            if (!$custom_check) {
                ilUtil::sendFailure($this->lng->txt("form_input_not_valid"));
            }
            $this->editQuestion($form);
            return 1;
        }
        $this->saveTaxonomyAssignments();
        return 0;
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
    {
        $this->object->setLongMenuTextValue(ilUtil::stripSlashesRecursive($_POST['longmenu_text']));
        $this->object->setAnswers($this->trimArrayRecursive(json_decode(ilUtil::stripSlashesRecursive($_POST['hidden_text_files']))));
        $this->object->setCorrectAnswers($this->trimArrayRecursive(json_decode(ilUtil::stripSlashesRecursive($_POST['hidden_correct_answers']))));
        $this->object->setAnswerType(ilUtil::stripSlashesRecursive($_POST['long_menu_type']));
        $this->object->setQuestion($_POST['question']);
        $this->object->setLongMenuTextValue($_POST["longmenu_text"]);
        $this->object->setMinAutoComplete((int) $_POST["min_auto_complete"]);
        $this->object->setIdenticalScoring((int) $_POST["identical_scoring"]);
        $this->saveTaxonomyAssignments();
    }

    private function verifyAnswerOptions() : bool
    {
        $longmenu_text = $_POST['longmenu_text'] ?? '';
        $hidden_text_files = $_POST['hidden_text_files'] ?? '';
        $answer_options_from_text = preg_split(
            "/\\[" . assLongMenu::GAP_PLACEHOLDER . " (\\d+)\\]/",
            $longmenu_text
        );
        $answer_options_from_files = json_decode(ilUtil::stripSlashes($hidden_text_files));
        if (count($answer_options_from_text) - 1 !== count($answer_options_from_files)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('longmenu_answeroptions_differ'));
            return false;
        }
        return true;
    }

    protected function trimArrayRecursive(array $data)
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = $this->trimArrayRecursive($v);
            } else {
                $data[$k] = trim($v);
            }
        }
        return $data;
    }

    protected function editQuestion(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->buildEditForm();
        }

        $this->getQuestionTemplate();

        $this->tpl->setVariable("QUESTION_DATA", $this->ctrl->getHTML($form));
    }
    /**
     * @return ilPropertyFormGUI
     */
    protected function buildEditForm()
    {
        $form = $this->buildBasicEditFormObject();

        $this->addQuestionFormCommandButtons($form);

        $this->addBasicQuestionFormProperties($form);

        $this->populateQuestionSpecificFormPart($form);
        $this->populateAnswerSpecificFormPart($form);

        $this->populateTaxonomyFormSection($form);

        return $form;
    }
    /**
     * @param ilPropertyFormGUI $form
     * @return ilPropertyFormGUI
     */
    public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form)
    {
        $long_menu_text = new ilTextAreaInputGUI($this->lng->txt("longmenu_text"), 'longmenu_text');
        $long_menu_text->setRequired(true);
        //$long_menu_text->setInfo($this->lng->txt("longmenu_hint"));
        $long_menu_text->setRows(10);
        $long_menu_text->setCols(80);
        if (!$this->object->getSelfAssessmentEditingMode()) {
            if ($this->object->getAdditionalContentEditingMode() == assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE) {
                include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
                $long_menu_text->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
                $long_menu_text->addPlugin("latex");
                $long_menu_text->addButton("latex");
                $long_menu_text->addButton("pastelatex");
                $long_menu_text->setRTESupport($this->object->getId(), "qpl", "assessment");
                $long_menu_text->setUseRte(true);
            }
        } else {
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssSelfAssessmentQuestionFormatter.php';
            $long_menu_text->setRteTags(ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
            $long_menu_text->setUseTagsForRteOnly(false);
        }

        $long_menu_text->setValue($this->object->getLongMenuTextValue());
        $form->addItem($long_menu_text);

        $tpl = new ilTemplate("tpl.il_as_qpl_longmenu_question_gap_button_code.html", true, true, "Modules/TestQuestionPool");
        $tpl->setVariable('INSERT_GAP', $this->lng->txt('insert_gap'));
        $tpl->parseCurrentBlock();
        $button = new ilCustomInputGUI('&nbsp;', '');
        $button->setHtml($tpl->get());
        $form->addItem($button);

        require_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
        $modal = ilModalGUI::getInstance();
        $modal->setHeading('');
        $modal->setId("ilGapModal");
        //$modal->setBackdrop(ilModalGUI::BACKDROP_OFF);
        $modal->setBody('');

        $min_auto_complete = new ilNumberInputGUI($this->lng->txt("min_auto_complete"), 'min_auto_complete');

        $auto_complete = $this->object->getMinAutoComplete();
        if ($auto_complete == 0) {
            $auto_complete = assLongMenu::MIN_LENGTH_AUTOCOMPLETE;
        }
        $min_auto_complete->setValue($auto_complete);
        $min_auto_complete->setMinValue(1);
        $min_auto_complete->setMaxValue(99);
        $min_auto_complete->setSize(5);
        $form->addItem($min_auto_complete);
        // identical scoring
        $identical_scoring = new ilCheckboxInputGUI($this->lng->txt("identical_scoring"), "identical_scoring");
        $identical_scoring->setValue(1);
        $identical_scoring->setChecked($this->object->getIdenticalScoring());
        $identical_scoring->setInfo($this->lng->txt('identical_scoring_desc'));
        $identical_scoring->setRequired(false);
        $form->addItem($identical_scoring);
        $hidden_text = new ilHiddenInputGUI('hidden_text_files');
        $form->addItem($hidden_text);

        $hidden_correct = new ilHiddenInputGUI('hidden_correct_answers');
        $form->addItem($hidden_correct);

        $long_menu_language = [
            'edit' => '[' . $this->lng->txt('edit') . ']',
            'type' => $this->lng->txt('type'),
            'answers' => $this->lng->txt('answers'),
            'answer_options' => $this->lng->txt('answer_options'),
            'correct_answers' => $this->lng->txt('correct_answers'),
            'add_answers' => '[' . $this->lng->txt('add_answers') . ']',
            'manual_editing' => $this->lng->txt('manual_editing')
        ];

        $question_parts = [
            'list' => json_decode($this->object->getJsonStructure()),
            'gap_placeholder' => assLongMenu::GAP_PLACEHOLDER,
            'last_updated_element' => 0,
            'replacement_word' => '',
            'filereader_usable' => false,
            'max_input_fields' => assLongMenu::MAX_INPUT_FIELDS
        ];
        $answers = $this->object->getAnswersObject();

        if (is_array($_POST) && array_key_exists('hidden_text_files', $_POST)) {
            $question_parts['list'] = json_decode($_POST['hidden_correct_answers']);
            $answers = $_POST['hidden_text_files'];
        }

        $this->tpl->addJavaScript('./Modules/TestQuestionPool/templates/default/longMenuQuestionGapBuilder.js');
        $this->tpl->addJavaScript('./Modules/TestQuestionPool/templates/default/longMenuQuestion.js');
        $tpl = new ilTemplate("tpl.il_as_qpl_longmenu_question_gap.html", true, true, "Modules/TestQuestionPool");
        $tpl->setVariable('SELECT_BOX', $this->lng->txt('insert_gap'));
        $tpl->setVariable("SELECT", $this->lng->txt('answers_select'));
        $tpl->setVariable("TEXT", $this->lng->txt('answers_text_box'));
        $tpl->setVariable("POINTS", $this->lng->txt('points'));
        $tpl->setVariable("INFO_TEXT_UPLOAD", $this->lng->txt('info_text_upload'));
        $tpl->setVariable("TXT_BROWSE", $this->lng->txt('select_file'));
        $tpl->setVariable('POINTS_ERROR', $this->lng->txt('enter_enough_positive_points'));
        $tpl->setVariable('AUTOCOMPLETE_ERROR', $this->lng->txt('autocomplete_error'));
        $tpl->setVariable('MISSING_VALUE', $this->lng->txt('msg_input_is_required'));
        $tpl->setVariable('SAVE', $this->lng->txt('save'));
        $tpl->setVariable('CANCEL', $this->lng->txt('cancel'));
        $tag_input = new ilTagInputGUI();
        $tag_input->setTypeAhead(true);
        $tag_input->setPostVar('taggable');
        $tag_input->setJsSelfInit(false);
        $tag_input->setTypeAheadMinLength(1);
        $tpl->setVariable("TAGGING_PROTOTYPE", $tag_input->render(''));

        $tpl->setVariable("MY_MODAL", $modal->getHTML());

        $tpl->parseCurrentBlock();
        $this->tpl->addOnLoadCode('longMenuQuestion.Init(' .
            json_encode($long_menu_language) . ', ' .
            json_encode($question_parts) . ', ' .
            $answers . ');');
        $button = new ilCustomInputGUI('&nbsp;', '');
        $button->setHtml($tpl->get());
        $form->addItem($button);
        return $form;
    }

    /**
     * @param ilPropertyFormGUI $form
     * @return ilPropertyFormGUI
     */
    public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form)
    {
        return $form;
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
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_longmenu_question_output_solution.html", true, true, "Modules/TestQuestionPool");

        if ($show_question_text) {
            $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        }
        if (($active_id > 0) && (!$show_correct_solution)) {
            $correct_solution = $this->getUserSolution($active_id, $pass);
        } else {
            $correct_solution = $this->object->getCorrectAnswersForQuestionSolution($this->object->getId());
        }
        $template->setVariable('LONGMENU_TEXT_SOLUTION', $this->getLongMenuTextWithInputFieldsInsteadOfGaps($correct_solution, true, $graphicalOutput));
        $solution_template = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        $question_output = $template->get();
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

            $solution_template->setVariable("ILC_FB_CSS_CLASS", $cssClass);
            $solution_template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($feedback, true));
        }

        $solution_template->setVariable("SOLUTION_OUTPUT", $question_output);

        $solution_output = $solution_template->get();

        if (!$show_question_only) {
            $solution_output = $this->getILIASPage($solution_output);
        }

        return $solution_output;
    }

    public function getPreview($show_question_only = false, $showInlineFeedback = false)
    {
        $user_solution = is_object($this->getPreviewSession()) ? (array) $this->getPreviewSession()->getParticipantsSolution() : array();
        $user_solution = array_values($user_solution);

        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_longmenu_question_output.html", true, true, "Modules/TestQuestionPool");

        $question_text = $this->object->getQuestion();
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $template->setVariable("ANSWER_OPTIONS_JSON", json_encode($this->object->getAvailableAnswerOptions()));
        $template->setVariable('AUTOCOMPLETE_LENGTH', $this->object->getMinAutoComplete());
        $template->setVariable('LONGMENU_TEXT', $this->getLongMenuTextWithInputFieldsInsteadOfGaps($user_solution));

        $question_output = $template->get();
        if (!$show_question_only) {
            $question_output = $this->getILIASPage($question_output);
        }
        return $question_output;
    }



    public function getTestOutput(
        $active_id,
                        // hey: prevPassSolutions - will be always available from now on
                           $pass,
                        // hey.
                           $is_postponed = false,
        $use_post_solutions = false,
        $show_feedback = false
    ) {
        $user_solution = array();
        if ($active_id) {
            $solutions = null;
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            if (!ilObjTest::_getUsePreviousAnswers($active_id, true)) {
                if (is_null($pass)) {
                    $pass = ilObjTest::_getPass($active_id);
                }
            }
            $solutions = $this->object->getUserSolutionPreferingIntermediate($active_id, $pass);
            foreach ($solutions as $idx => $solution_value) {
                $user_solution[$solution_value["value1"]] = $solution_value["value2"];
            }
        }

        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_longmenu_question_output.html", true, true, "Modules/TestQuestionPool");

        $question_text = $this->object->getQuestion();
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $template->setVariable("ANSWER_OPTIONS_JSON", json_encode($this->object->getAvailableAnswerOptions()));
        $template->setVariable('LONGMENU_TEXT', $this->getLongMenuTextWithInputFieldsInsteadOfGaps($user_solution));
        $template->setVariable('AUTOCOMPLETE_LENGTH', $this->object->getMinAutoComplete());
        $question_output = $template->get();
        $page_output = $this->outQuestionPage("", $is_postponed, $active_id, $question_output);
        return $page_output;
    }

    public function getSpecificFeedbackOutput($userSolution)
    {
        if (!$this->object->feedbackOBJ->specificAnswerFeedbackExists()) {
            return '';
        }

        $feedback = '<table class="test_specific_feedback"><tbody>';
        $gaps = $this->object->getCorrectAnswers();
        foreach ($gaps as $index => $answer) {
            $caption = assLongMenu::GAP_PLACEHOLDER . ' ';
            $caption .= $index + 1 . ': ';

            $feedback .= '<tr><td>';

            $feedback .= $caption . '</td><td>';
            $feedback .= $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                $this->object->getId(),
                0,
                $index
            ) . '</td> </tr>';
        }
        $feedback .= '</tbody></table>';
        return $this->object->prepareTextareaOutput($feedback, true);
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
        $overview = array();
        $aggregation = array();
        foreach ($relevant_answers as $answer) {
            $overview[$answer['active_fi']][$answer['pass']][$answer['value1']] = $answer['value2'];
        }

        foreach ($overview as $active) {
            foreach ($active as $answer) {
                foreach ($answer as $option => $value) {
                    $aggregation[$option][$value] = $aggregation[$option][$value] + 1;
                }
            }
        }
        $tpl = new ilTemplate('tpl.il_as_aggregated_longmenu_question_answers_table.html', true, true, "Modules/TestQuestionPool");
        $json = json_decode($this->object->getJsonStructure());
        foreach ($json as $key => $value) {
            $tpl->setVariable('TITLE', 'Longmenu ' . ($key + 1));
            if (array_key_exists($key, $aggregation)) {
                $aggregate = $aggregation[$key];
                foreach ($aggregate as $answer => $counts) {
                    $tpl->setVariable('OPTION', $answer);
                    $tpl->setVariable('COUNT', $counts);
                    $tpl->parseCurrentBlock();
                }
            }
        }

        return $tpl->get();
    }

    public function getLongMenuTextWithInputFieldsInsteadOfGaps($user_solution = array(), $solution = false, $graphical = false)
    {
        $return_value = '';
        $text_array = preg_split("/\\[" . assLongMenu::GAP_PLACEHOLDER . " (\\d+)\\]/", $this->object->getLongMenuTextValue());
        $correct_answers = $this->object->getCorrectAnswers();
        $answers = $this->object->getAnswers();
        foreach ($text_array as $key => $value) {
            $answer_is_correct = false;
            $user_value = '';
            $return_value .= $this->object->prepareTextareaOutput($value, true);
            if ($key < sizeof($text_array) - 1) {
                if (!array_key_exists($key, $correct_answers)) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('longmenu_answeroptions_differ'));
                    continue;
                }
                if ($correct_answers[$key][2] == assLongMenu::ANSWER_TYPE_TEXT_VAL) {
                    if (array_key_exists($key, $user_solution)) {
                        $user_value = $user_solution[$key];
                        if (in_array($user_value, $correct_answers[$key][0])) {
                            $answer_is_correct = true;
                        }
                    }

                    $return_value .= $this->getTextGapTemplate($key, $user_value, $solution, $answer_is_correct, $graphical);
                } elseif ($correct_answers[$key][2] == assLongMenu::ANSWER_TYPE_SELECT_VAL) {
                    if (array_key_exists($key, $user_solution)) {
                        $user_value = $user_solution[$key];
                        if (in_array($user_value, $correct_answers[$key][0])) {
                            $answer_is_correct = true;
                        }
                    }
                    $return_value .= $this->getSelectGapTemplate($key, $answers[$key], $user_value, $solution, $answer_is_correct, $graphical);
                }
            }
        }
        return $return_value;
    }

    private function getTextGapTemplate($key, $value, $solution, $ok = false, $graphical = false)
    {
        $tpl = new ilTemplate("tpl.il_as_qpl_longmenu_question_text_gap.html", true, true, "Modules/TestQuestionPool");
        if ($solution) {
            $tpl->setVariable('DISABLED', 'disabled');
            $tpl->setVariable('JS_IGNORE', '_ignore');
            if ($graphical) {
                if ($ok) {
                    $tpl->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
                    $tpl->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
                } else {
                    $tpl->setVariable("ICON_OK", ilUtil::getImagePath("icon_not_ok.svg"));
                    $tpl->setVariable("TEXT_OK", $this->lng->txt("answer_is_wrong"));
                }
            }
        }
        $tpl->setVariable('VALUE', $value);
        $tpl->setVariable('KEY', $key);

        return $tpl->get();
    }

    private function getSelectGapTemplate($key, $answers, $user_value, $solution, $ok = false, $graphical = false)
    {
        $tpl = new ilTemplate("tpl.il_as_qpl_longmenu_question_select_gap.html", true, true, "Modules/TestQuestionPool");
        $tpl->setVariable('KEY', $key);
        if ($solution) {
            $tpl->setVariable('DISABLED', 'disabled');
            $tpl->setVariable('JS_IGNORE', '_ignore');
            $tpl->setCurrentBlock('best_solution');
            if ($user_value == -1) {
                $tpl->setVariable("SOLUTION", $this->lng->txt("please_select"));
            } else {
                $tpl->setVariable('SOLUTION', $user_value);
            }
            if ($graphical) {
                if ($ok) {
                    $tpl->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
                    $tpl->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
                } else {
                    $tpl->setVariable("ICON_OK", ilUtil::getImagePath("icon_not_ok.svg"));
                    $tpl->setVariable("TEXT_OK", $this->lng->txt("answer_is_wrong"));
                }
            }
            $tpl->parseCurrentBlock();
        } else {
            $tpl->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
            foreach ($answers as $value) {
                $tpl->setCurrentBlock('select_option');
                $tpl->setVariable('VALUE', $value);
                if ($value == $user_value) {
                    $tpl->setVariable('SELECTED', 'selected');
                }
                $tpl->parseCurrentBlock();
            }
        }
        return $tpl->get();
    }

    public function getSubQuestionsIndex()
    {
        return array_keys($this->object->getAnswers());
    }

    public function getAnswersFrequency($relevant_answers, $questionIndex)
    {
        $answers = [];

        foreach ($relevant_answers as $row) {
            if ($row['value1'] != $questionIndex) {
                continue;
            }

            if (!isset($answers[$row['value2']])) {
                //$label = $this->getAnswerTextLabel($row['value1'], $row['value2']);

                $answers[$row['value2']] = array(
                    'answer' => $row['value2'], 'frequency' => 0
                );
            }

            $answers[$row['value2']]['frequency']++;
        }

        return $answers;
    }

    public function getAnswerFrequencyTableGUI($parentGui, $parentCmd, $relevantAnswers, $questionIndex)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $table = parent::getAnswerFrequencyTableGUI(
            $parentGui,
            $parentCmd,
            $relevantAnswers,
            $questionIndex
        );

        $table->setTitle(sprintf(
            $DIC->language()->txt('tst_corrections_answers_tbl_subindex'),
            $DIC->language()->txt('longmenu') . ' ' . ($questionIndex + 1)
        ));

        return $table;
    }

    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        $correctAnswers = $this->object->getCorrectAnswers();

        foreach ($this->object->getAnswers() as $lmIndex => $lm) {
            $lmValues = array(
                'answers_all' => array(0 => $lm),
                'answers_all_count' => count($lm),
                'answers_correct' => $correctAnswers[$lmIndex][0]
            );

            $lmPoints = $correctAnswers[$lmIndex][1];

            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->lng->txt('longmenu') . ' ' . ($lmIndex + 1));
            $form->addItem($section);

            $lmInput = new ilAssLongmenuCorrectionsInputGUI(
                $this->lng->txt('answers'),
                'longmenu_' . $lmIndex
            );

            $lmInput->setRequired(true);

            $lmInput->setValues($lmValues);

            $form->addItem($lmInput);

            $pointsInp = new ilNumberInputGUI($this->lng->txt("points"), 'points_' . $lmIndex);
            $pointsInp->setRequired(true);
            $pointsInp->allowDecimals(true);
            $pointsInp->setSize(4);
            $pointsInp->setMinValue(0);
            $pointsInp->setMinvalueShouldBeGreater(false);
            $pointsInp->setValue($lmPoints);
            $form->addItem($pointsInp);
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        $correctAnswers = $this->object->getCorrectAnswers();

        foreach ($this->object->getAnswers() as $lmIndex => $lm) {
            $pointsInput = (float) $form->getInput('points_' . $lmIndex);
            $correctAnswersInput = (array) $form->getInput('longmenu_' . $lmIndex . '_tags');

            foreach ($correctAnswersInput as $idx => $answer) {
                if (in_array($answer, $lm)) {
                    continue;
                }

                unset($correctAnswersInput[$idx]);
            }

            $correctAnswersInput = array_values($correctAnswersInput);

            $correctAnswers[$lmIndex][0] = $correctAnswersInput;
            $correctAnswers[$lmIndex][1] = $pointsInput;
        }

        $this->object->setCorrectAnswers($correctAnswers);
    }
}
