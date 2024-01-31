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
 * The assFileUploadGUI class encapsulates the GUI representation for file upload questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 *
 * @ilctrl_iscalledby assFileUploadGUI: ilObjQuestionPoolGUI
 * @ilCtrl_Calls assFileUploadGUI: ilFormPropertyDispatchGUI
 */
class assFileUploadGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable
{
    public const REUSE_FILES_TBL_POSTVAR = 'reusefiles';
    public const REUSE_FILES_LANGVAR = 'ass_file_upload_reuse_btn';
    public const REUSE_FILES_ACTION = 'reuse';
    public const DELETE_FILES_TBL_POSTVAR = 'deletefiles';
    public const DELETE_FILES_LANGVAR = 'delete';
    public const DELETE_FILES_ACTION = 'delete';
    private const HANDLE_FILE_UPLOAD = 'handleFileUpload';

    /**
     * assFileUploadGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the assFileUploadGUI object.
     *
     * @param integer $id The database id of a single choice question object
     *
     */
    public function __construct($id = -1)
    {
        parent::__construct();

        $this->object = new assFileUpload();
        $this->setErrorMessage($this->lng->txt("msg_form_save_error"));
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
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
            $this->saveTaxonomyAssignments();
            return 0;
        }
        return 1;
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form): void
    {
        $this->object->setPoints((float) str_replace(',', '.', $_POST["points"]));
        $this->object->setMaxSize($this->request->int('maxsize') !== 0 ? $this->request->int('maxsize') : null);
        $this->object->setAllowedExtensions($_POST["allowedextensions"] ?? '');
        $this->object->setCompletionBySubmission(isset($_POST['completion_by_submission']) && $_POST['completion_by_submission'] == 1);
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

        $form = new ilPropertyFormGUI();
        $this->editForm = $form;

        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setMultipart(false);
        $form->setTableWidth("100%");
        $form->setId("assfileupload");

        $this->addBasicQuestionFormProperties($form);
        $this->populateQuestionSpecificFormPart($form);

        $this->populateTaxonomyFormSection($form);
        $this->addQuestionFormCommandButtons($form);

        $errors = false;

        if ($save) {
            $form->setValuesByPost();
            $errors = !$form->checkInput();
            $form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling and
            // we need this if we don't want to have duplication of backslashes
            if ($errors) {
                $checkonly = false;
            }
        }

        if (!$checkonly) {
            $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
        }
        return $errors;
    }

    public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        $maxsize = new ilNumberInputGUI($this->lng->txt("maxsize"), "maxsize");
        $maxsize->allowDecimals(false);
        $maxsize->setValue($this->object->getMaxSize() > 0 ? (string) $this->object->getMaxSize() : null);
        $maxsize->setInfo($this->lng->txt("maxsize_info"));
        $maxsize->setSize(10);
        $maxsize->setMinValue(0);
        $maxsize->setMaxValue((float) $this->object->determineMaxFilesize());
        $maxsize->setRequired(false);
        $form->addItem($maxsize);

        $allowedextensions = new ilTextInputGUI($this->lng->txt("allowedextensions"), "allowedextensions");
        $allowedextensions->setInfo($this->lng->txt("allowedextensions_info"));
        $allowedextensions->setValue($this->object->getAllowedExtensions());
        $allowedextensions->setRequired(false);
        $form->addItem($allowedextensions);

        $points = new ilNumberInputGUI($this->lng->txt("points"), "points");
        $points->allowDecimals(true);
        $points->setValue(
            is_numeric($this->object->getPoints()) && $this->object->getPoints(
            ) >= 0 ? $this->object->getPoints() : ''
        );
        $points->setRequired(true);
        $points->setSize(3);
        $points->setMinValue(0.0);
        $points->setMinvalueShouldBeGreater(false);
        $form->addItem($points);

        $subcompl = new ilCheckboxInputGUI($this->lng->txt(
            'ass_completion_by_submission'
        ), 'completion_by_submission');
        $subcompl->setInfo($this->lng->txt('ass_completion_by_submission_info'));
        $subcompl->setValue('1');
        $subcompl->setChecked($this->object->isCompletionBySubmissionEnabled());
        $form->addItem($subcompl);
        return $form;
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
        $template = new ilTemplate("tpl.il_as_qpl_fileupload_output_solution.html", true, true, "Modules/TestQuestionPool");

        $solutionvalue = "";
        if (($active_id > 0) && (!$show_correct_solution)) {
            $solutions = $this->object->getSolutionValues($active_id, $pass);

            $files = ($show_manual_scoring) ? $this->object->getUploadedFilesForWeb($active_id, $pass) : $this->object->getUploadedFiles($active_id, $pass);
            $table_gui = new assFileUploadFileTableGUI($this, 'gotoquestion');
            $table_gui->setTitle(
                $this->lng->txt('already_delivered_files'),
                'standard/icon_file.svg',
                $this->lng->txt('already_delivered_files')
            );
            $table_gui->setData($files);
            $table_gui->setRowTemplate("tpl.il_as_qpl_fileupload_file_view_row.html", "Modules/TestQuestionPool");
            $table_gui->setSelectAllCheckbox("");
            $table_gui->disable('numinfo');
            $template->setCurrentBlock("files");
            $template->setVariable('FILES', $table_gui->getHTML());
            $template->parseCurrentBlock();
        }

        if ($this->object->getAllowedExtensions() === '') {
            $template->setCurrentBlock("allowed_extensions");
            $template->setVariable("TXT_ALLOWED_EXTENSIONS", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt("allowedextensions") . ": " . $this->object->getAllowedExtensions()));
            $template->parseCurrentBlock();
        }
        $template->setVariable("CMD_UPLOAD", self::HANDLE_FILE_UPLOAD);
        $template->setVariable("TEXT_UPLOAD", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt('upload')));
        $template->setVariable("TXT_UPLOAD_FILE", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt('file_add')));
        $template->setVariable("TXT_MAX_SIZE", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt('file_notice') . " " . $this->object->getMaxFilesizeAsString()));

        if (($active_id > 0) && (!$show_correct_solution)) {
            $reached_points = $this->object->getReachedPoints($active_id, $pass);
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
        $questionoutput = $template->get();
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        $feedback = ($show_feedback && !$this->isTestPresentationContext()) ? $this->getGenericFeedbackOutput((int) $active_id, $pass) : "";
        if (strlen($feedback)) {
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
        $template = new ilTemplate("tpl.il_as_qpl_fileupload_output.html", true, true, "Modules/TestQuestionPool");

        if (is_object($this->getPreviewSession())) {
            $files = $this->object->getPreviewFileUploads($this->getPreviewSession());
            $table_gui = new assFileUploadFileTableGUI(null, $this->getQuestionActionCmd(), 'ilAssQuestionPreview');
            $table_gui->setTitle(
                $this->lng->txt('already_delivered_files'),
                'standard/icon_file.svg',
                $this->lng->txt('already_delivered_files')
            );
            $table_gui->setData($files);
            // hey: prevPassSolutions - support file reuse with table

            list($lang_var, $cmd) = $this->getCommandButtonLangVarAndAction();
            $table_gui->initCommand(
                $lang_var,
                $cmd,
                assFileUpload::DELETE_FILES_TBL_POSTVAR
            );
            // hey.
            $template->setCurrentBlock("files");
            $template->setVariable('FILES', $table_gui->getHTML());
            $template->parseCurrentBlock();
        }

        if (strlen($this->object->getAllowedExtensions())) {
            $template->setCurrentBlock("allowed_extensions");
            $template->setVariable("TXT_ALLOWED_EXTENSIONS", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt("allowedextensions") . ": " . $this->object->getAllowedExtensions()));
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $template->setVariable("CMD_UPLOAD", $this->getQuestionActionCmd());
        $template->setVariable("TEXT_UPLOAD", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt('upload')));
        $template->setVariable("TXT_UPLOAD_FILE", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt('file_add')));
        $template->setVariable("TXT_MAX_SIZE", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt('file_notice') . " " . $this->object->getMaxFilesizeAsString()));

        $questionoutput = $template->get();
        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        return $questionoutput;
    }

    public function getTestOutput($active_id, $pass, $is_postponed = false, $use_post_solutions = false, $show_feedback = false): string
    {
        // generate the question output
        $template = new ilTemplate("tpl.il_as_qpl_fileupload_output.html", true, true, "Modules/TestQuestionPool");

        if ($active_id) {
            $files = $this->object->getTestOutputSolutions($active_id, $pass);
            // hey.
            $table_gui = new assFileUploadFileTableGUI(null, $this->getQuestionActionCmd());
            $table_gui->setTitle(
                $this->lng->txt('already_delivered_files'),
                'standard/icon_file.svg',
                $this->lng->txt('already_delivered_files')
            );
            $table_gui->setData($files);

            list($lang_var, $cmd) = $this->getCommandButtonLangVarAndAction();
            $table_gui->initCommand(
                $lang_var,
                $cmd,
                $this->getTestPresentationFileTablePostVar()
            );
            // hey.
            $template->setCurrentBlock("files");
            $template->setVariable('FILES', $table_gui->getHTML());
            $template->parseCurrentBlock();
        }

        if (strlen($this->object->getAllowedExtensions())) {
            $template->setCurrentBlock("allowed_extensions");
            $template->setVariable("TXT_ALLOWED_EXTENSIONS", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt("allowedextensions") . ": " . $this->object->getAllowedExtensions()));
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $template->setVariable("CMD_UPLOAD", self::HANDLE_FILE_UPLOAD);
        $template->setVariable("TEXT_UPLOAD", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt('upload')));
        $template->setVariable("TXT_UPLOAD_FILE", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt('file_add')));
        $template->setVariable("TXT_MAX_SIZE", ilLegacyFormElementsUtil::prepareTextareaOutput($this->lng->txt('file_notice') . " " . $this->object->getMaxFilesizeAsString()));

        $questionoutput = $template->get();
        //if (!$show_question_only) {
        // get page object output
        $questionoutput = $this->getILIASPage($questionoutput);
        //}
        $questionoutput = $template->get();
        $pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
        return $pageoutput;
    }

    private function getCommandButtonLangVarAndAction(): array
    {
        if ($this->object->getTestPresentationConfig()->isSolutionInitiallyPrefilled()) {
            return [self::REUSE_FILES_LANGVAR, self::REUSE_FILES_ACTION];
        }
        return [self::DELETE_FILES_LANGVAR, self::DELETE_FILES_ACTION];
    }

    public function getSpecificFeedbackOutput(array $userSolution): string
    {
        $output = "";
        return ilLegacyFormElementsUtil::prepareTextareaOutput($output, true);
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
        // Empty implementation here since a feasible way to aggregate answer is not known.
        return ''; //print_r($relevant_answers,true);
    }

    protected function getTestPresentationFileTablePostVar(): string
    {
        if ($this->object->getTestPresentationConfig()->isSolutionInitiallyPrefilled()) {
            return assFileUpload::REUSE_FILES_TBL_POSTVAR;
        }

        return assFileUpload::DELETE_FILES_TBL_POSTVAR;
    }
    // hey.

    // hey: prevPassSolutions - overwrite common prevPassSolution-Checkbox
    protected function getPreviousSolutionProvidedMessage(): string
    {
        return $this->lng->txt('use_previous_solution_advice_file_upload');
    }

    protected function getPreviousSolutionConfirmationCheckboxHtml(): string
    {
        return '';
    }
    // hey.

    public function getFormEncodingType(): string
    {
        return self::FORM_ENCODING_MULTIPART;
    }

    public function isAnswerFreuqencyStatisticSupported(): bool
    {
        return false;
    }

    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        // points
        $points = new ilNumberInputGUI($this->lng->txt("points"), "points");
        $points->allowDecimals(true);
        $points->setValue(
            is_numeric($this->object->getPoints()) && $this->object->getPoints(
            ) >= 0 ? $this->object->getPoints() : ''
        );
        $points->setRequired(true);
        $points->setSize(3);
        $points->setMinValue(0.0);
        $points->setMinvalueShouldBeGreater(false);
        $form->addItem($points);

        $subcompl = new ilCheckboxInputGUI($this->lng->txt(
            'ass_completion_by_submission'
        ), 'completion_by_submission');
        $subcompl->setInfo($this->lng->txt('ass_completion_by_submission_info'));
        $subcompl->setValue(1);
        $subcompl->setChecked($this->object->isCompletionBySubmissionEnabled());
        $form->addItem($subcompl);
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        $this->object->setPoints((float) str_replace(',', '.', $form->getInput('points')));
        $this->object->setCompletionBySubmission((bool) $form->getInput('completion_by_submission'));
    }
}
