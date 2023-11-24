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

include_once './Modules/Test/classes/inc.AssessmentConstants.php';


/**
 * Image map question GUI representation
 *
 * The assImagemapQuestionGUI class encapsulates the GUI representation
 * for image map questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 * @ingroup ModulesTestQuestionPool
 * @ilCtrl_Calls assImagemapQuestionGUI: ilPropertyFormGUI, ilFormPropertyDispatchGUI
 */
class assImagemapQuestionGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
    private $linecolor;

    /**
     * assImagemapQuestionGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the assImagemapQuestionGUI object.
     *
     * @param integer $id The database id of a image map question object.
     */
    public function __construct($id = -1)
    {
        parent::__construct();
        $this->object = new assImagemapQuestion();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
        $assessmentSetting = new ilSetting("assessment");
        $this->linecolor = (strlen($assessmentSetting->get("imap_line_color"))) ? "#" . $assessmentSetting->get("imap_line_color") : "#FF0000";
    }

    public function getCommand($cmd)
    {
        if (isset($_POST["imagemap"]) ||
        isset($_POST["imagemap_x"]) ||
        isset($_POST["imagemap_y"])) {
            $this->ctrl->setCmd("getCoords");
            $cmd = "getCoords";
        }

        return $cmd;
    }

    protected function deleteImage(): void
    {
        $this->object->deleteImage();
        $this->object->saveToDb();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'editQuestion');
    }

    /**
     * {@inheritdoc}
     */
    protected function writePostData(bool $always = false): int
    {
        $form = $this->buildEditForm();
        $form->setValuesByPost();

        if (!$always && !$form->checkInput()) {
            $this->editQuestion($form);
            return 1;
        }

        $this->writeQuestionGenericPostData();
        $this->writeQuestionSpecificPostData($form);
        $this->writeAnswerSpecificPostData($form);
        $this->saveTaxonomyAssignments();

        return 0;
    }

    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form): void
    {
        if ($this->ctrl->getCmd() != 'deleteImage') {
            $this->object->flushAnswers();
            if (isset($_POST['image']) && is_array($_POST['image']) && is_array($_POST['image']['coords']['name'])) {
                foreach ($_POST['image']['coords']['name'] as $idx => $name) {
                    if ($this->object->getIsMultipleChoice() && isset($_POST['image']['coords']['points_unchecked'])) {
                        $pointsUnchecked = $_POST['image']['coords']['points_unchecked'][$idx];
                    } else {
                        $pointsUnchecked = 0.0;
                    }

                    $this->object->addAnswer(
                        $name,
                        $_POST['image']['coords']['points'][$idx],
                        $idx,
                        $_POST['image']['coords']['coords'][$idx],
                        $_POST['image']['coords']['shape'][$idx],
                        $pointsUnchecked
                    );
                }
            }

            if (strlen($_FILES['imagemapfile']['tmp_name'])) {
                if ($this->object->getSelfAssessmentEditingMode() && $this->object->getId() < 1) {
                    $this->object->createNewQuestion();
                }

                $this->object->uploadImagemap($form->getItemByPostVar('imagemapfile')->getShapes());
            }
        }
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form): void
    {
        if ($this->ctrl->getCmd() != 'deleteImage') {
            if (strlen($_FILES['image']['tmp_name']) == 0) {
                $this->object->setImageFilename($_POST["image_name"]);
            }
        }
        if (strlen($_FILES['image']['tmp_name'])) {
            if ($this->object->getSelfAssessmentEditingMode() && $this->object->getId() < 1) {
                $this->object->createNewQuestion();
            }
            $this->object->setImageFilename($_FILES['image']['name'], $_FILES['image']['tmp_name']);
        }

        $this->object->setIsMultipleChoice($_POST['is_multiple_choice'] == assImagemapQuestion::MODE_MULTIPLE_CHOICE);
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function buildEditForm(): ilPropertyFormGUI
    {
        $form = $this->buildBasicEditFormObject();

        $this->addBasicQuestionFormProperties($form);
        $this->populateQuestionSpecificFormPart($form);
        $this->populateTaxonomyFormSection($form);
        $this->addQuestionFormCommandButtons($form);

        return $form;
    }

    /**
     * @param ilPropertyFormGUI|null $form
     */
    public function editQuestion(ilPropertyFormGUI $form = null): void
    {
        if (null === $form) {
            $form = $this->buildEditForm();
        }

        $this->getQuestionTemplate();

        $this->tpl->setVariable('QUESTION_DATA', $this->ctrl->getHTML($form));
    }

    public function populateAnswerSpecificFormPart(\ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        return $form; // Nothing to do here since selectable areas are handled in question-specific-form part
        // due to their immediate dependency to the image. I decide to not break up the interfaces
        // more just to support this very rare case. tl;dr: See the issue, ignore it.
    }

    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        $radioGroup = new ilRadioGroupInputGUI($this->lng->txt('tst_imap_qst_mode'), 'is_multiple_choice');
        $radioGroup->setValue((string) ((int) ($this->object->getIsMultipleChoice())));
        $modeSingleChoice = new ilRadioOption(
            $this->lng->txt('tst_imap_qst_mode_sc'),
            (string) assImagemapQuestion::MODE_SINGLE_CHOICE
        );
        $modeMultipleChoice = new ilRadioOption(
            $this->lng->txt('tst_imap_qst_mode_mc'),
            (string) assImagemapQuestion::MODE_MULTIPLE_CHOICE
        );
        $radioGroup->addOption($modeSingleChoice);
        $radioGroup->addOption($modeMultipleChoice);
        $form->addItem($radioGroup);

        $image = new ilImagemapFileInputGUI($this->lng->txt('image'), 'image');
        $image->setPointsUncheckedFieldEnabled($this->object->getIsMultipleChoice());
        $image->setRequired(true);

        if (strlen($this->object->getImageFilename())) {
            $image->setImage($this->object->getImagePathWeb() . $this->object->getImageFilename());
            $image->setValue($this->object->getImageFilename());
            $image->setAreas($this->object->getAnswers());
            $assessmentSetting = new ilSetting("assessment");
            $linecolor = (strlen(
                $assessmentSetting->get("imap_line_color")
            )) ? "\"#" . $assessmentSetting->get("imap_line_color") . "\"" : "\"#FF0000\"";
            $image->setLineColor($linecolor);
            $image->setImagePath($this->object->getImagePath());
            $image->setImagePathWeb($this->object->getImagePathWeb());
        }
        $form->addItem($image);

        $imagemapfile = new ilHtmlImageMapFileInputGUI($this->lng->txt('add_imagemap'), 'imagemapfile');
        $imagemapfile->setRequired(false);
        $form->addItem($imagemapfile);
        return $form;
    }

    public function addRect(): void
    {
        $this->areaEditor('rect');
    }

    public function addCircle(): void
    {
        $this->areaEditor('circle');
    }

    public function addPoly(): void
    {
        $this->areaEditor('poly');
    }

    /**
    * Saves a shape of the area editor
    */
    public function saveShape(): void
    {
        $coords = "";
        switch ($_POST["shape"]) {
            case assImagemapQuestion::AVAILABLE_SHAPES['RECT']:
                $coords = join(",", $_POST['image']['mapcoords']);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_rect_added'), true);
                break;
            case assImagemapQuestion::AVAILABLE_SHAPES['CIRCLE']:
                if (preg_match("/(\d+)\s*,\s*(\d+)\s+(\d+)\s*,\s*(\d+)/", $_POST['image']['mapcoords'][0] . " " . $_POST['image']['mapcoords'][1], $matches)) {
                    $coords = "$matches[1],$matches[2]," . (int) sqrt((($matches[3] - $matches[1]) * ($matches[3] - $matches[1])) + (($matches[4] - $matches[2]) * ($matches[4] - $matches[2])));
                }
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_circle_added'), true);
                break;
            case assImagemapQuestion::AVAILABLE_SHAPES['POLY']:
                $coords = join(",", $_POST['image']['mapcoords']);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_poly_added'), true);
                break;
        }
        $this->object->addAnswer($_POST["shapetitle"], 0, count($this->object->getAnswers()), $coords, $_POST["shape"]);
        $this->object->saveToDb();
        $this->ctrl->redirect($this, 'editQuestion');
    }

    public function areaEditor($shape = ''): void
    {
        $shape = (strlen($shape)) ? $shape : $_POST['shape'];

        $this->getQuestionTemplate();

        $editorTpl = new ilTemplate('tpl.il_as_qpl_imagemap_question.html', true, true, 'Modules/TestQuestionPool');

        $coords = array();
        $mapcoords = $this->request->raw('image');
        if ($mapcoords != null && isset($mapcoords['mapcoords']) && is_array($mapcoords['mapcoords'])) {
            foreach ($mapcoords['mapcoords'] as $value) {
                array_push($coords, $value);
            }
        }
        $cmd = $this->request->raw('cmd');
        if ($cmd != null && array_key_exists('areaEditor', $cmd) && is_array($cmd['areaEditor']['image'])) {
            array_push($coords, $cmd['areaEditor']['image'][0] . "," . $cmd['areaEditor']['image'][1]);
        }
        foreach ($coords as $value) {
            $editorTpl->setCurrentBlock("hidden");
            $editorTpl->setVariable("HIDDEN_NAME", 'image[mapcoords][]');
            $editorTpl->setVariable("HIDDEN_VALUE", $value);
            $editorTpl->parseCurrentBlock();
        }

        $editorTpl->setCurrentBlock("hidden");
        $editorTpl->setVariable("HIDDEN_NAME", 'shape');
        $editorTpl->setVariable("HIDDEN_VALUE", $shape);
        $editorTpl->parseCurrentBlock();

        $preview = new ilImagemapPreview($this->object->getImagePath() . $this->object->getImageFilename());
        foreach ($this->object->answers as $index => $answer) {
            $preview->addArea($index, $answer->getArea(), $answer->getCoords(), $answer->getAnswertext(), "", "", true, $this->linecolor);
        }
        $hidearea = false;
        $disabled_save = " disabled=\"disabled\"";
        $c = "";
        switch ($shape) {
            case "rect":
                if (count($coords) == 0) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt("rectangle_click_tl_corner"));
                } elseif (count($coords) == 1) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt("rectangle_click_br_corner"));
                    $preview->addPoint($preview->getAreaCount(), join(",", $coords), true, "blue");
                } elseif (count($coords) == 2) {
                    $c = join(",", $coords);
                    $hidearea = true;
                    $disabled_save = "";
                }
                break;
            case "circle":
                if (count($coords) == 0) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt("circle_click_center"));
                } elseif (count($coords) == 1) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt("circle_click_circle"));
                    $preview->addPoint($preview->getAreaCount(), join(",", $coords), true, "blue");
                } elseif (count($coords) == 2) {
                    if (preg_match("/(\d+)\s*,\s*(\d+)\s+(\d+)\s*,\s*(\d+)/", $coords[0] . " " . $coords[1], $matches)) {
                        $c = "$matches[1],$matches[2]," . (int) sqrt((($matches[3] - $matches[1]) * ($matches[3] - $matches[1])) + (($matches[4] - $matches[2]) * ($matches[4] - $matches[2])));
                    }
                    $hidearea = true;
                    $disabled_save = "";
                }
                break;
            case "poly":
                if (count($coords) == 0) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt("polygon_click_starting_point"));
                } elseif (count($coords) == 1) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt("polygon_click_next_point"));
                    $preview->addPoint($preview->getAreaCount(), implode(",", $coords), true, "blue");
                } elseif (count($coords) > 1) {
                    $this->tpl->setOnScreenMessage('info', $this->lng->txt("polygon_click_next_or_save"));
                    $disabled_save = "";
                    $c = implode(",", $coords);
                }
                break;
        }
        if (strlen($c)) {
            $preview->addArea($preview->getAreaCount(), $shape, $c, $_POST["shapetitle"] ?? '', "", "", true, "blue");
        }
        $preview->createPreview();
        $imagepath = $this->object->getImagePathWeb() . $preview->getPreviewFilename($this->object->getImagePath(), $this->object->getImageFilename()) . "?img=" . time();
        if (!$hidearea) {
            $editorTpl->setCurrentBlock("maparea");
            $editorTpl->setVariable("IMAGE_SOURCE", "$imagepath");
            $editorTpl->setVariable("IMAGEMAP_NAME", "image");
            $editorTpl->parseCurrentBlock();
        } else {
            $editorTpl->setCurrentBlock("imagearea");
            $editorTpl->setVariable("IMAGE_SOURCE", "$imagepath");
            $editorTpl->setVariable("ALT_IMAGE", $this->lng->txt("imagemap"));
            $editorTpl->parseCurrentBlock();
        }

        if (isset($_POST['shapetitle']) && $_POST['shapetitle'] != '') {
            $editorTpl->setCurrentBlock("shapetitle");
            $editorTpl->setVariable("VALUE_SHAPETITLE", $_POST["shapetitle"]);
            $editorTpl->parseCurrentBlock();
        }

        $editorTpl->setVariable("TEXT_IMAGEMAP", $this->lng->txt("imagemap"));
        $editorTpl->setVariable("TEXT_SHAPETITLE", $this->lng->txt("ass_imap_hint"));
        $editorTpl->setVariable("CANCEL", $this->lng->txt("cancel"));
        $editorTpl->setVariable("SAVE", $this->lng->txt("save"));
        $editorTpl->setVariable("DISABLED_SAVE", $disabled_save);
        switch ($shape) {
            case "rect":
                $editorTpl->setVariable("FORMACTION", $this->ctrl->getFormaction($this, 'addRect'));
                break;
            case 'circle':
                $editorTpl->setVariable("FORMACTION", $this->ctrl->getFormaction($this, 'addCircle'));
                break;
            case 'poly':
                $editorTpl->setVariable("FORMACTION", $this->ctrl->getFormaction($this, 'addPoly'));
                break;
        }

        $this->tpl->setVariable('QUESTION_DATA', $editorTpl->get());
    }

    public function back(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_cancel'), true);
        $this->ctrl->redirect($this, 'editQuestion');
    }

    // hey: prevPassSolutions - max solution pass determinations allready done, pass passed for sure
    // hey: fixedIdentifier - changed access to passed param (lower-/uppercase issues)
    protected function completeTestOutputFormAction($formAction, $active_id, $pass)
    {
        $info = $this->object->getTestOutputSolutions($active_id, $pass);

        if (count($info)) {
            if (strcmp($info[0]["value1"], "") != 0) {
                $formAction .= "&selImage=" . $info[0]["value1"];
            }
        }

        return $formAction;
    }
    // hey.
    // hey.

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
        $imagepath = $this->object->getImagePathWeb() . $this->object->getImageFilename();
        $solutions = array();
        if (($active_id > 0) && (!$show_correct_solution)) {
            $solutions = $this->object->getSolutionValues($active_id, $pass);
        } else {
            if (!$this->object->getIsMultipleChoice()) {
                $found_index = -1;
                $max_points = 0;
                foreach ($this->object->answers as $index => $answer) {
                    if ($answer->getPoints() > $max_points) {
                        $max_points = $answer->getPoints();
                        $found_index = $index;
                    }
                }
                array_push($solutions, array("value1" => $found_index));
            } else {
                // take the correct solution instead of the user solution
                foreach ($this->object->answers as $index => $answer) {
                    $points_checked = $answer->getPoints();
                    $points_unchecked = $answer->getPointsUnchecked();
                    if ($points_checked > $points_unchecked) {
                        if ($points_checked > 0) {
                            array_push($solutions, array("value1" => $index));
                        }
                    }
                }
            }
        }
        $solution_id = -1;
        if (is_array($solutions)) {
            $preview = new ilImagemapPreview($this->object->getImagePath() . $this->object->getImageFilename());
            foreach ($solutions as $idx => $solution_value) {
                $value1 = $solution_value["value1"];
                if (
                    strcmp($value1, '') === 0 ||
                    !isset($this->object->answers[$value1])
                ) {
                    continue;
                }

                /** @var ASS_AnswerImagemap $shape */
                $shape = $this->object->answers[$value1];
                $preview->addArea(
                    $value1,
                    $shape->getArea(),
                    $shape->getCoords(),
                    $shape->getAnswertext(),
                    '',
                    '',
                    true,
                    $this->linecolor
                );

                $solution_id = $value1;
            }
            $preview->createPreview();
            $imagepath = implode('', [
                $this->object->getImagePathWeb(),
                $preview->getPreviewFilename(
                    $this->object->getImagePath(),
                    $this->object->getImageFilename()
                ),
            ]);
        }

        // generate the question output
        $template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output_solution.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        if ($show_question_text == true) {
            $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        }

        $template->setVariable("IMG_SRC", ilWACSignedPath::signFile($imagepath));
        $template->setVariable("IMG_ALT", $this->lng->txt("imagemap"));
        $template->setVariable("IMG_TITLE", $this->lng->txt("imagemap"));
        if (($active_id > 0) && (!$show_correct_solution)) {
            if ($graphicalOutput) {
                $correctness_icon = $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_NOT_OK);
                $reached_points = $this->object->getReachedPoints($active_id, $pass);
                if ($reached_points == $this->object->getMaximumPoints()) {
                    $correctness_icon = $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_OK);
                }

                if ($reached_points > 0) {
                    $correctness_icon = $this->generateCorrectnessIconsForCorrectness(self::CORRECTNESS_MOSTLY_OK);
                }
                $template->setCurrentBlock("icon_ok");
                $template->setVariable("ICON_OK", $correctness_icon);
                $template->parseCurrentBlock();
            }
        }

        if ($show_feedback) {
            $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                $this->object->getId(),
                0,
                $solution_id
            );

            if (strlen($fb)) {
                $template->setCurrentBlock("feedback");
                $template->setVariable("FEEDBACK", $fb);
                $template->parseCurrentBlock();
            }
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
        if (is_object($this->getPreviewSession())) {
            $user_solution = array();

            if (is_array($this->getPreviewSession()->getParticipantsSolution())) {
                $user_solution = array_values($this->getPreviewSession()->getParticipantsSolution());
            }

            $preview = new ilImagemapPreview($this->object->getImagePath() . $this->object->getImageFilename());
            foreach ($user_solution as $idx => $solution_value) {
                if (strcmp($solution_value, "") != 0) {
                    $preview->addArea($solution_value, $this->object->answers[$solution_value]->getArea(), $this->object->answers[$solution_value]->getCoords(), $this->object->answers[$solution_value]->getAnswertext(), "", "", true, $this->linecolor);
                }
            }
            $preview->createPreview();
            $imagepath = $this->object->getImagePathWeb() . $preview->getPreviewFilename($this->object->getImagePath(), $this->object->getImageFilename());
        } else {
            $user_solution = array();
            $imagepath = $this->object->getImagePathWeb() . $this->object->getImageFilename();
        }

        // generate the question output
        $template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output.html", true, true, "Modules/TestQuestionPool");

        if ($this->getQuestionActionCmd() && !is_null($this->getTargetGuiClass())) {
            $hrefArea = $this->ctrl->getLinkTargetByClass($this->getTargetGuiClass(), $this->getQuestionActionCmd());
        } else {
            $hrefArea = null;
        }

        foreach ($this->object->answers as $answer_id => $answer) {
            $parameter = "&amp;selImage=$answer_id";
            if (is_array($user_solution) && in_array($answer_id, $user_solution)) {
                $parameter = "&amp;remImage=$answer_id";
            }

            if ($hrefArea) {
                $template->setCurrentBlock("imagemap_area_href");
                $template->setVariable("HREF_AREA", $hrefArea . $parameter);
                $template->parseCurrentBlock();
            }

            $template->setCurrentBlock("imagemap_area");
            $template->setVariable("SHAPE", $answer->getArea());
            $template->setVariable("COORDS", $answer->getCoords());
            $template->setVariable("ALT", ilLegacyFormElementsUtil::prepareFormOutput($answer->getAnswertext()));
            $template->setVariable("TITLE", ilLegacyFormElementsUtil::prepareFormOutput($answer->getAnswertext()));
            $template->parseCurrentBlock();
        }
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $template->setVariable("IMG_SRC", ilWACSignedPath::signFile($imagepath));
        $template->setVariable("IMG_ALT", $this->lng->txt("imagemap"));
        $template->setVariable("IMG_TITLE", $this->lng->txt("imagemap"));
        $questionoutput = $template->get();
        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        return $questionoutput;
    }

    // hey: prevPassSolutions - pass will be always available from now on
    public function getTestOutput($active_id, $pass, $is_postponed = false, $use_post_solutions = false, $show_feedback = false): string
    // hey.
    {
        if ($active_id) {
            $solutions = $this->object->getTestOutputSolutions($active_id, $pass);
            // hey.

            $userSelection = array();
            $selectionIndex = 0;

            $preview = new ilImagemapPreview($this->object->getImagePath() . $this->object->getImageFilename());

            foreach ($solutions as $idx => $solution_value) {
                if (strlen($solution_value["value1"])) {
                    $preview->addArea($solution_value["value1"], $this->object->answers[$solution_value["value1"]]->getArea(), $this->object->answers[$solution_value["value1"]]->getCoords(), $this->object->answers[$solution_value["value1"]]->getAnswertext(), "", "", true, $this->linecolor);
                    $userSelection[$selectionIndex] = $solution_value["value1"];

                    $selectionIndex = $this->object->getIsMultipleChoice() ? ++$selectionIndex : $selectionIndex;
                }
            }

            $preview->createPreview();

            $imagepath = $this->object->getImagePathWeb() . $preview->getPreviewFilename($this->object->getImagePath(), $this->object->getImageFilename());
        } else {
            $imagepath = $this->object->getImagePathWeb() . $this->object->getImageFilename();
        }

        // generate the question output
        $template = new ilTemplate("tpl.il_as_qpl_imagemap_question_output.html", true, true, "Modules/TestQuestionPool");
        $this->ctrl->setParameterByClass($this->getTargetGuiClass(), "formtimestamp", time());
        $hrefArea = $this->ctrl->getLinkTargetByClass($this->getTargetGuiClass(), $this->getQuestionActionCmd());
        foreach ($this->object->answers as $answer_id => $answer) {
            $template->setCurrentBlock("imagemap_area");
            $template->setVariable("HREF_AREA", $this->buildAreaLinkTarget($userSelection, $answer_id));
            $template->setVariable("SHAPE", $answer->getArea());
            $template->setVariable("COORDS", $answer->getCoords());
            $template->setVariable("ALT", ilLegacyFormElementsUtil::prepareFormOutput($answer->getAnswertext()));
            $template->setVariable("TITLE", ilLegacyFormElementsUtil::prepareFormOutput($answer->getAnswertext()));
            $template->parseCurrentBlock();
            if ($show_feedback) {
                if (!$this->object->getIsMultipleChoice() && count($userSelection) && current($userSelection) == $answer_id) {
                    $feedback = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                        $this->object->getId(),
                        0,
                        $answer_id
                    );
                    if (strlen($feedback)) {
                        $template->setCurrentBlock("feedback");
                        $template->setVariable("FEEDBACK", $feedback);
                        $template->parseCurrentBlock();
                    }
                }
            }
        }
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $template->setVariable("IMG_SRC", ilWACSignedPath::signFile($imagepath));
        $template->setVariable("IMG_ALT", $this->lng->txt("imagemap"));
        $template->setVariable("IMG_TITLE", $this->lng->txt("imagemap"));
        $questionoutput = $template->get();
        $pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
        return $pageoutput;
    }

    // hey: prevPassSolutions - fixed confusing handling of not reusing, but modifying the previous solution
    protected function buildAreaLinkTarget($currentSelection, $areaIndex): string
    {
        $href = $this->ctrl->getLinkTargetByClass(
            $this->getTargetGuiClass(),
            $this->getQuestionActionCmd()
        );

        $href = ilUtil::appendUrlParameterString(
            $href,
            $this->buildSelectionParameter($currentSelection, $areaIndex)
        );

        return $href;
    }

    protected function buildSelectionParameter($currentSelection, $areaIndex = null): string
    {
        if ($this->object->getTestPresentationConfig()->isSolutionInitiallyPrefilled()) {
            $reuseSelection = array();

            if ($areaIndex === null) {
                $reuseSelection = $currentSelection;
            } elseif ($this->object->getIsMultipleChoice()) {
                if (!in_array($areaIndex, $currentSelection)) {
                    $reuseSelection[] = $areaIndex;
                }

                foreach (array_diff($currentSelection, array($areaIndex)) as $otherSelectedArea) {
                    $reuseSelection[] = $otherSelectedArea;
                }
            } else {
                $reuseSelection[] = $areaIndex;
            }

            $selection = assQuestion::implodeKeyValues($reuseSelection);
            $action = 'reuseSelection';
        } elseif ($areaIndex !== null) {
            if (!$this->object->getIsMultipleChoice() || !in_array($areaIndex, $currentSelection)) {
                $areaAction = 'selImage';
            } else {
                $areaAction = 'remImage';
            }

            $selection = $areaIndex;
            $action = $areaAction;
        } else {
            return '';
        }

        return "{$action}={$selection}";
    }

    public function getSpecificFeedbackOutput(array $userSolution): string
    {
        if (!$this->object->feedbackOBJ->specificAnswerFeedbackExists()) {
            return '';
        }

        $output = '<table class="test_specific_feedback"><tbody>';

        foreach ($this->object->getAnswers() as $idx => $answer) {
            $feedback = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                $this->object->getId(),
                0,
                $idx
            );

            $output .= "<tr><td>{$answer->getAnswerText()}</td><td>{$feedback}</td></tr>";
        }

        $output .= '</tbody></table>';

        return $this->object->prepareTextareaOutput($output, true);
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

    protected function renderAggregateView($answeringFequencies): string
    {
        $tpl = new ilTemplate('tpl.il_as_aggregated_answers_table.html', true, true, "Modules/TestQuestionPool");

        $tpl->setCurrentBlock('headercell');
        $tpl->setVariable('HEADER', $this->lng->txt('tst_answer_aggr_answer_header'));
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock('headercell');
        $tpl->setVariable('HEADER', $this->lng->txt('tst_answer_aggr_frequency_header'));
        $tpl->parseCurrentBlock();

        foreach ($answeringFequencies as $answerIndex => $answeringFrequency) {
            $tpl->setCurrentBlock('aggregaterow');
            $tpl->setVariable('OPTION', $this->object->getAnswer($answerIndex)->getAnswerText());
            $tpl->setVariable('COUNT', $answeringFrequency);
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    protected function aggregateAnswers($givenSolutionRows, $existingAnswerOptions): array
    {
        $answeringFequencies = array();

        foreach ($existingAnswerOptions as $answerIndex => $answerOption) {
            $answeringFequencies[$answerIndex] = 0;
        }

        foreach ($givenSolutionRows as $solutionRow) {
            $answeringFequencies[$solutionRow['value1']]++;
        }

        return $answeringFequencies;
    }

    /**
     * Returns an html string containing a question specific representation of the answers so far
     * given in the test for use in the right column in the scoring adjustment user interface.
     * @param array $relevant_answers
     * @return string
     */
    public function getAggregatedAnswersView(array $relevant_answers): string
    {
        return $this->renderAggregateView(
            $this->aggregateAnswers($relevant_answers, $this->object->getAnswers())
        );
    }

    protected function getPreviousSolutionConfirmationCheckboxHtml(): string
    {
        if (!count($this->object->currentSolution)) {
            return '';
        }

        $button = ilLinkButton::getInstance();
        $button->setCaption('use_previous_solution');

        $button->setUrl(ilUtil::appendUrlParameterString(
            $this->ctrl->getLinkTargetByClass($this->getTargetGuiClass(), $this->getQuestionActionCmd()),
            $this->buildSelectionParameter($this->object->currentSolution, null)
        ));

        $tpl = new ilTemplate('tpl.tst_question_additional_behaviour_checkbox.html', true, true, 'Modules/TestQuestionPool');
        $tpl->setVariable('BUTTON', $button->render());

        return $tpl->get();
    }

    public function getAnswersFrequency($relevantAnswers, $questionIndex): array
    {
        $agg = $this->aggregateAnswers($relevantAnswers, $this->object->getAnswers());

        $answers = array();

        foreach ($this->object->getAnswers() as $answerIndex => $ans) {
            $answers[] = array(
                'answer' => $ans->getAnswerText(),
                'frequency' => $agg[$answerIndex]
            );
        }

        return $answers;
    }

    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        $image = new ilImagemapCorrectionsInputGUI($this->lng->txt('image'), 'image');
        $image->setPointsUncheckedFieldEnabled($this->object->getIsMultipleChoice());
        $image->setRequired(true);

        if (strlen($this->object->getImageFilename())) {
            $image->setImage($this->object->getImagePathWeb() . $this->object->getImageFilename());
            $image->setValue($this->object->getImageFilename());
            $image->setAreas($this->object->getAnswers());
            $assessmentSetting = new ilSetting("assessment");
            $linecolor = (strlen(
                $assessmentSetting->get("imap_line_color")
            )) ? "\"#" . $assessmentSetting->get("imap_line_color") . "\"" : "\"#FF0000\"";
            $image->setLineColor($linecolor);
            $image->setImagePath($this->object->getImagePath());
            $image->setImagePathWeb($this->object->getImagePathWeb());
        }
        $form->addItem($image);
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        $areas = $form->getItemByPostVar('image')->getAreas();

        foreach ($this->object->getAnswers() as $index => $answer) {
            if ($this->object->getIsMultipleChoice()) {
                $answer->setPointsUnchecked((float) $areas[$index]->getPointsUnchecked());
            }

            $answer->setPoints((float) $areas[$index]->getPoints());
        }
    }
}
