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
 * Single choice question GUI representation
 *
 * The assSingleChoiceGUI class encapsulates the GUI representation for single choice questions.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup components\ILIASTestQuestionPool
 * @ilCtrl_Calls assSingleChoiceGUI: ilFormPropertyDispatchGUI
 */
class assSingleChoiceGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
    private bool $rebuild_thumbnails = false;
    /**
     * assSingleChoiceGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the assSingleChoiceGUI object.
     *
     * @param integer $id The database id of a single choice question object
     */
    public function __construct(int $id = -1)
    {
        parent::__construct();
        $this->object = new assSingleChoice();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }

    /**
     * @return bool
     */
    public function hasInlineFeedback(): bool
    {
        return $this->object->feedbackOBJ->isSpecificAnswerFeedbackAvailable($this->object->getId());
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

    /**
     * Get the single/multiline editing of answers
     * - The settings of an already saved question is preferred
     * - A new question will use the setting of the last edited question by the user
     *
     * @param bool	$checkonly	get the setting for checking a POST
     *
     * @return bool
     *
     * @deprecated Deprecated since ILIAS 8.14, will be removed in ILIAS 10.0. Use assSingleChoice::isSingleline() instead.
     *             The new method name may be subject to change.
     *             The method will be removed due to its redundant nature and because this method implements specific
     *             behaviour only for this question type. In order to maintain consistency and avoid unnecessary complexity
     *             in the codebase, it's beneficial to remove such specific behaviors that are not shared across different
     *             question types.
     */
    protected function getEditAnswersSingleLine($checkonly = false): bool
    {
        if ($checkonly) {
            return $this->request->int('types') === 0;
        }

        if (empty($this->object->getLastChange())
            && !$this->request->isset('types')) {
            // a new question is edited
            return $this->object->getMultilineAnswerSetting() === 0;
        }
        // a saved question is edited
        return $this->object->isSingleline();
    }

    public function editQuestion(
        bool $checkonly = false,
        ?bool $is_save_cmd = null
    ): bool {
        $save = $is_save_cmd ?? $this->isSaveCommand();

        $form = new ilPropertyFormGUI();
        $this->editForm = $form;

        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $is_singleline = $this->getEditAnswersSingleLine($checkonly);
        if ($is_singleline) {
            $form->setMultipart(true);
        } else {
            $form->setMultipart(false);
        }
        $form->setTableWidth("100%");
        $form->setId("asssinglechoice");

        $this->addBasicQuestionFormProperties($form);
        $this->populateQuestionSpecificFormPart($form, $is_singleline);
        $this->populateAnswerSpecificFormPart($form, $is_singleline);


        $this->populateTaxonomyFormSection($form);

        $this->addQuestionFormCommandButtons($form);

        $errors = false;

        if ($save) {
            foreach ($this->request->getParsedBody() as $key => $value) {
                $item = $form->getItemByPostVar($key);
                if ($item !== null) {
                    switch (get_class($item)) {
                        case 'ilDurationInputGUI':
                            $item->setHours($value['hh']);
                            $item->setMinutes($value['mm']);
                            $item->setSeconds($value['ss']);
                            break;
                        default:
                            $item->setValue($value);
                    }
                }
            }

            $errors = !$form->checkInput();
            foreach ($this->request->getParsedBody() as $key => $value) {
                $item = $form->getItemByPostVar($key);
                if ($item !== null) {
                    switch (get_class($item)) {
                        case 'ilDurationInputGUI':
                            $item->setHours($value['hh']);
                            $item->setMinutes($value['mm']);
                            $item->setSeconds($value['ss']);
                            break;
                        default:
                            $item->setValue($value);
                    }
                }
            } // again, because checkInput now performs the whole stripSlashes handling and we need this if we don't want to have duplication of backslashes

            if ($errors) {
                $checkonly = false;
            }
        }

        if (!$checkonly) {
            $this->renderEditForm($form);
        }
        return $errors;
    }

    /**
    * Upload an image
    */
    public function uploadchoice(): void
    {
        $this->writePostData(true);
        $this->editQuestion();
    }

    public function removeimagechoice(): void
    {
        $this->writePostData(true);
        $position = key($this->request->raw('cmd')['removeimagechoice']);
        $this->object->removeAnswerImage($position);
        $this->editQuestion();
    }

    public function getSolutionOutput(
        int $active_id,
        ?int $pass = null,
        bool $graphical_output = false,
        bool $result_output = false,
        bool $show_question_only = true,
        bool $show_feedback = false,
        bool $show_correct_solution = false,
        bool $show_manual_scoring = false,
        bool $show_question_text = true,
        bool $show_inline_feedback = true
    ): string {
        $keys = $this->getChoiceKeys();
        $user_solution = "";
        if (($active_id > 0) && (!$show_correct_solution)) {
            $solutions = $this->object->getSolutionValues($active_id, $pass);
            foreach ($solutions as $idx => $solution_value) {
                $user_solution = $solution_value["value1"];
            }
        } else {
            $found_index = -1;
            $max_points = 0;
            foreach ($this->object->answers as $index => $answer) {
                if ($answer->getPoints() > $max_points) {
                    $max_points = $answer->getPoints();
                    $found_index = $index;
                }
            }
            $user_solution = $found_index;
        }

        $template = new ilTemplate("tpl.il_as_qpl_mc_sr_output_solution.html", true, true, "components/ILIAS/TestQuestionPool");
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "components/ILIAS/TestQuestionPool");
        foreach ($keys as $answer_id) {
            $answer = $this->object->answers[$answer_id];
            if (($active_id > 0) && (!$show_correct_solution)) {
                if ($graphical_output) {
                    $correctness_icon = $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_NOT_OK);

                    if (strcmp($user_solution, $answer_id) == 0) {
                        if ($answer->getPoints() == $this->object->getMaximumPoints()) {
                            $correctness_icon = $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_OK);
                        } elseif ($answer->getPoints() > 0) {
                            $correctness_icon = $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_MOSTLY_OK);
                        }
                    }
                    $template->setCurrentBlock("icon_ok");
                    $template->setVariable("ICON_OK", $correctness_icon);
                    $template->parseCurrentBlock();
                }
            }
            if ($answer->hasImage()) {
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
                $template->setVariable("ANSWER_IMAGE_ALT", ilLegacyFormElementsUtil::prepareFormOutput($alt));
                $template->setVariable("ANSWER_IMAGE_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($alt));
                $template->parseCurrentBlock();
            }

            if (($show_feedback || !$this->isTestPresentationContext()) && $show_inline_feedback) {
                $this->populateInlineFeedback($template, $answer_id, $user_solution);
            }
            $template->setCurrentBlock("answer_row");
            $template->setVariable("ANSWER_TEXT", ilLegacyFormElementsUtil::prepareTextareaOutput($answer->getAnswertext(), true));

            if ($this->renderPurposeSupportsFormHtml() || $this->isRenderPurposePrintPdf()) {
                if (strcmp($user_solution, $answer_id) == 0) {
                    $template->setVariable("SOLUTION_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("object/radiobutton_checked.png")));
                    $template->setVariable("SOLUTION_ALT", $this->lng->txt("checked"));
                } else {
                    $template->setVariable("SOLUTION_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("object/radiobutton_unchecked.png")));
                    $template->setVariable("SOLUTION_ALT", $this->lng->txt("unchecked"));
                }
            } else {
                $template->setVariable('QID', $this->object->getId());
                $template->setVariable('SUFFIX', $show_correct_solution ? 'bestsolution' : 'usersolution');
                $template->setVariable('SOLUTION_VALUE', $answer_id);
                if (strcmp($user_solution, $answer_id) == 0) {
                    $template->setVariable('SOLUTION_CHECKED', 'checked');
                }
            }

            if ($result_output) {
                $points = $this->object->answers[$answer_id]->getPoints();
                $resulttext = ($points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")";
                $template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
            }
            $template->parseCurrentBlock();
        }

        $questiontext = $this->object->getQuestionForHTMLOutput();
        if ($show_inline_feedback && $this->hasInlineFeedback()) {
            $questiontext .= $this->buildFocusAnchorHtml();
        }
        if ($show_question_text === true) {
            $template->setVariable("QUESTIONTEXT", ilLegacyFormElementsUtil::prepareTextareaOutput($questiontext, true));
        }
        $questionoutput = $template->get();
        $feedback = ($show_feedback && !$this->isTestPresentationContext()) ? $this->getGenericFeedbackOutput((int) $active_id, $pass) : "";
        if ($feedback !== '') {
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

    public function getPreview(
        bool $show_question_only = false,
        bool $show_inline_feedback = false
    ): string {
        $keys = $this->getChoiceKeys();

        $template = new ilTemplate("tpl.il_as_qpl_mc_sr_output.html", true, true, "components/ILIAS/TestQuestionPool");
        foreach ($keys as $answer_id) {
            $answer = $this->object->answers[$answer_id];
            if ($answer->hasImage()) {
                if ($this->object->getThumbSize()) {
                    $template->setCurrentBlock("preview");
                    $template->setVariable("URL_PREVIEW", $this->object->getImagePathWeb() . $answer->getImage());
                    $template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
                    $template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('media/enlarge.svg'));
                    $template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $answer->getImage());
                    list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getImage());
                    $alt = $answer->getImage();
                    if (strlen($answer->getAnswertext())) {
                        $alt = $answer->getAnswertext();
                    }
                    $alt = preg_replace("/<[^>]*?>/", "", $alt);
                    $template->setVariable("ANSWER_IMAGE_ALT", ilLegacyFormElementsUtil::prepareFormOutput($alt));
                    $template->setVariable("ANSWER_IMAGE_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($alt));
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
                    $template->setVariable("ANSWER_IMAGE_ALT", ilLegacyFormElementsUtil::prepareFormOutput($alt));
                    $template->setVariable("ANSWER_IMAGE_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($alt));
                    $template->parseCurrentBlock();
                }
            }
            if ($show_inline_feedback && is_object($this->getPreviewSession())) {
                $this->populateInlineFeedback($template, $answer_id, $this->getPreviewSession()->getParticipantsSolution());
            }
            $template->setCurrentBlock("answer_row");
            $template->setVariable("QID", $this->object->getId() . 'ID');
            $template->setVariable("ANSWER_ID", $answer_id);
            $template->setVariable("ANSWER_TEXT", ilLegacyFormElementsUtil::prepareTextareaOutput($answer->getAnswertext(), true));

            if (is_object($this->getPreviewSession())) {
                $user_solution = $this->getPreviewSession()->getParticipantsSolution() ?? '';
                if (strcmp($user_solution, $answer_id) == 0) {
                    $template->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
                }
            }

            $template->parseCurrentBlock();
        }
        $questiontext = $this->object->getQuestionForHTMLOutput();
        if ($show_inline_feedback && $this->hasInlineFeedback()) {
            $questiontext .= $this->buildFocusAnchorHtml();
        }
        $template->setVariable("QUESTIONTEXT", ilLegacyFormElementsUtil::prepareTextareaOutput($questiontext, true));
        $questionoutput = $template->get();
        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        return $questionoutput;
    }

    public function getTestOutput(
        int $active_id,
        int $pass,
        bool $is_question_postponed = false,
        array|bool $user_post_solutions = false,
        bool $show_specific_inline_feedback = false
    ): string {
        $keys = $this->getChoiceKeys();

        // get the solution of the user for the active pass or from the last pass if allowed
        $user_solution = "";
        if ($active_id) {
            $solutions = $this->object->getTestOutputSolutions($active_id, $pass);
            foreach ($solutions as $idx => $solution_value) {
                $user_solution = $solution_value["value1"];
            }
        }

        $template = new ilTemplate("tpl.il_as_qpl_mc_sr_output.html", true, true, "components/ILIAS/TestQuestionPool");
        foreach ($keys as $answer_id) {
            $answer = $this->object->answers[$answer_id];
            if ($answer->hasImage()) {
                if ($this->object->getThumbSize()) {
                    $template->setCurrentBlock("preview");
                    $template->setVariable("URL_PREVIEW", $this->object->getImagePathWeb() . $answer->getImage());
                    $template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
                    $template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('media/enlarge.svg'));
                    $template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $answer->getImage());
                    list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getImage());
                    $alt = $answer->getImage();
                    if (strlen($answer->getAnswertext())) {
                        $alt = $answer->getAnswertext();
                    }
                    $alt = preg_replace("/<[^>]*?>/", "", $alt);
                    $template->setVariable("ANSWER_IMAGE_ALT", ilLegacyFormElementsUtil::prepareFormOutput($alt));
                    $template->setVariable("ANSWER_IMAGE_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($alt));
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
                    $template->setVariable("ANSWER_IMAGE_ALT", ilLegacyFormElementsUtil::prepareFormOutput($alt));
                    $template->setVariable("ANSWER_IMAGE_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($alt));
                    $template->parseCurrentBlock();
                }
            }
            if ($show_specific_inline_feedback) {
                $feedback_output_required = false;

                switch ($this->object->getSpecificFeedbackSetting()) {
                    case 1:
                        $feedback_output_required = true;
                        break;

                    case 2:
                        if ($user_solution === $answer_id) {
                            $feedback_output_required = true;
                        }
                        break;

                    case 3:
                        if ($this->object->getAnswer($answer_id)->getPoints() > 0) {
                            $feedback_output_required = true;
                        }
                        break;
                }

                if ($feedback_output_required) {
                    $feedback = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                        $this->object->getId(),
                        0,
                        $answer_id
                    );
                    if ($feedback !== '') {
                        $template->setCurrentBlock("feedback");
                        $template->setVariable("FEEDBACK", ilLegacyFormElementsUtil::prepareTextareaOutput($feedback, true));
                        $template->parseCurrentBlock();
                    }
                }
            }
            $template->setCurrentBlock("answer_row");
            $template->setVariable("ANSWER_ID", $answer_id);
            $template->setVariable("ANSWER_TEXT", ilLegacyFormElementsUtil::prepareTextareaOutput($answer->getAnswertext(), true));
            if (strcmp($user_solution, $answer_id) == 0) {
                $template->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
            }
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $questionoutput = $template->get();
        $pageoutput = $this->outQuestionPage("", $is_question_postponed, $active_id, $questionoutput, $show_specific_inline_feedback);
        return $pageoutput;
    }

    /*
     * Create the key index numbers for the array of choices
     *
     * @return array
     */
    public function getChoiceKeys()
    {
        $choice_keys = array_keys($this->object->answers);

        if ($this->object->getShuffle()) {
            $choice_keys = $this->object->getShuffler()->transform($choice_keys);
        }

        return $choice_keys;
    }

    public function getSpecificFeedbackOutput(array $user_solution): string
    {
        // No return value, this question type supports inline specific feedback.
        $output = "";
        return ilLegacyFormElementsUtil::prepareTextareaOutput($output, true);
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form): void
    {
        $types = $_POST["types"] ?? '0';

        $this->object->setShuffle($_POST["shuffle"] ?? '0');
        $this->object->setMultilineAnswerSetting($types);

        if (isset($_POST['choice']) && isset($_POST['choice']['imagename']) && is_array($_POST['choice']['imagename']) && $types === '1') {
            $this->object->setIsSingleline(true);
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('info_answer_type_change'), true);
        } else {
            $this->object->setIsSingleline($types === '0' ? true : false);
        }
        if (isset($_POST["thumb_size"])
            && (int) $_POST["thumb_size"] !== $this->object->getThumbSize()) {
            $this->object->setThumbSize((int) $_POST["thumb_size"]);
            $this->rebuild_thumbnails = true;
        }
    }

    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form, bool $is_singleline = false): ilPropertyFormGUI
    {
        // shuffle
        $shuffle = new ilCheckboxInputGUI($this->lng->txt("shuffle_answers"), "shuffle");
        $shuffle->setValue(1);
        $shuffle->setChecked($this->object->getShuffle());
        $shuffle->setRequired(false);
        $form->addItem($shuffle);

        if (!$this->object->getSelfAssessmentEditingMode()) {
            // Answer types
            $types = new ilSelectInputGUI($this->lng->txt("answer_types"), "types");
            $types->setRequired(false);
            $types->setOptions(
                [
                                    0 => $this->lng->txt('answers_singleline'),
                                    1 => $this->lng->txt('answers_multiline'),
                                ]
            );
            $types->setValue($is_singleline ? 0 : 1);
            $form->addItem($types);
        }

        if ($is_singleline) {
            // thumb size
            $thumb_size = new ilNumberInputGUI($this->lng->txt("thumb_size"), "thumb_size");
            $thumb_size->setSuffix($this->lng->txt("thumb_size_unit_pixel"));
            $thumb_size->setMinValue($this->object->getMinimumThumbSize());
            $thumb_size->setDecimals(0);
            $thumb_size->setSize(6);
            $thumb_size->setInfo($this->lng->txt('thumb_size_info'));
            $thumb_size->setValue($this->object->getThumbSize());
            $thumb_size->setRequired(true);
        } else {
            $thumb_size = new ilHiddenInputGUI('thumb_size');
            $thumb_size->setValue($this->object->getThumbSize());
        }
        $form->addItem($thumb_size);

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
    public function getAfterParticipationSuppressionQuestionPostVars(): array
    {
        return [];
    }

    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form): void
    {
        // Delete all existing answers and create new answers from the form data
        $this->object->flushAnswers();
        $choice = $this->request->raw('choice') ?? [];
        if($this->object->hasAnswerTypeChanged() || !$this->object->isSingleline()) {
            $choice = $this->cleanupAnswerText(
                $this->request->raw('choice') ?? [],
                $this->object->isSingleline() === false
            );
        }

        if ($choice === []) {
            return;
        }

        if (!$this->object->isSingleline()) {
            foreach ($choice['answer'] as $index => $answer) {
                $answertext = htmlentities($answer);
                $this->object->addAnswer(
                    $answertext,
                    $choice['points'][$index],
                    $index,
                    null,
                    $choice['answer_id'][$index]
                );
            }

            return;
        }

        foreach ($choice['answer'] as $index => $answertext) {
            $answertext = htmlentities($answertext);
            $picturefile = $choice['imagename'][$index] ?? null;
            $file_org_name = $_FILES['choice']['name']['image'][$index] ?? '';
            $file_temp_name = $_FILES['choice']['tmp_name']['image'][$index] ?? '';

            if ($file_temp_name !== '') {
                // check suffix
                $file_name_parts = explode(".", $file_org_name);
                $suffix = strtolower(array_pop($file_name_parts));
                if (in_array($suffix, ["jpg", "jpeg", "png", "gif"])) {
                    // upload image
                    $filename = $this->object->buildHashedImageFilename($file_org_name);
                    if ($this->object->setImageFile($filename, $file_temp_name) == 0) {
                        $picturefile = $filename;
                    }
                }
            }

            $points = (float) str_replace(',', '.', $choice['points'][$index]);
            $this->object->addAnswer(
                $answertext,
                $points,
                $index,
                $picturefile,
                $choice['answer_id'][$index]
            );
        }

        if ($this->rebuild_thumbnails) {
            $this->object->setAnswers(
                $this->object->rebuildThumbnails(
                    $this->object->isSingleline(),
                    $this->object->getThumbSize(),
                    $this->object->getImagePath(),
                    $this->object->getAnswers()
                )
            );
        }
    }

    public function populateAnswerSpecificFormPart(\ilPropertyFormGUI $form, bool $is_singleline = false): ilPropertyFormGUI
    {
        $choices = new ilSingleChoiceWizardInputGUI($this->lng->txt("answers"), "choice");
        $choices->setRequired(true);
        $choices->setQuestionObject($this->object);
        $choices->setSingleline($is_singleline);
        $choices->setAllowMove(false);
        if ($this->object->getSelfAssessmentEditingMode()) {
            $choices->setSize(40);
        }
        $choices->setMaxLength(800);
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
        return  $this->renderAggregateView(
            $this->aggregateAnswers($relevant_answers, $this->object->getAnswers())
        )->get();
    }

    public function aggregateAnswers($relevant_answers_chosen, $answers_defined_on_question): array
    {
        $aggregate = [];
        foreach ($answers_defined_on_question as $answer) {
            $aggregated_info_for_answer = [];
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
    public function renderAggregateView($aggregate): ilTemplate
    {
        $tpl = new ilTemplate('tpl.il_as_aggregated_answers_table.html', true, true, "components/ILIAS/TestQuestionPool");

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

    private function populateInlineFeedback($template, $answer_id, $user_solution): void
    {
        $feedbackOutputRequired = false;

        switch ($this->object->getSpecificFeedbackSetting()) {
            case 1:
                $feedbackOutputRequired = true;
                break;

            case 2:
                if (strcmp((string) $user_solution, $answer_id) == 0) {
                    $feedbackOutputRequired = true;
                }
                break;

            case 3:
                if ($this->object->getAnswer($answer_id)->getPoints() > 0) {
                    $feedbackOutputRequired = true;
                }
                break;
        }

        if ($feedbackOutputRequired) {
            $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation($this->object->getId(), 0, $answer_id);
            if (strlen($fb)) {
                $template->setCurrentBlock("feedback");
                $template->setVariable("FEEDBACK", ilLegacyFormElementsUtil::prepareTextareaOutput($fb, true));
                $template->parseCurrentBlock();
            }
        }
    }

    public function getAnswersFrequency($relevantAnswers, $questionIndex): array
    {
        $agg = $this->aggregateAnswers($relevantAnswers, $this->object->getAnswers());

        $answers = [];

        foreach ($agg as $ans) {
            $answers[] = [
                'answer' => $ans['answertext'],
                'frequency' => $ans['count_checked']
            ];
        }

        return $answers;
    }

    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        $choices = new ilAssSingleChoiceCorrectionsInputGUI($this->lng->txt("answers"), "choice");
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
        $input = $form->getItemByPostVar('choice');
        $values = $input->getValues();

        foreach ($this->object->getAnswers() as $index => $answer) {
            /* @var ASS_AnswerMultipleResponseImage $answer */
            $points = (float) str_replace(',', '.', $values[$index]->getPoints());
            $answer->setPoints($points);
        }
    }
}
