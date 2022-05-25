<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Matching question GUI representation
 *
 * The assMatchingQuestionGUI class encapsulates the GUI representation
 * for matching questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 * @ilCtrl_Calls assMatchingQuestionGUI: ilFormPropertyDispatchGUI
 */
class assMatchingQuestionGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
    /**
     * assMatchingQuestionGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the assMatchingQuestionGUI object.
     *
     * @param integer $id The database id of a image map question object
     * @param integer $id The database id of a image map question object
     *
     * @return \assMatchingQuestionGUI
     */
    public function __construct($id = -1)
    {
        parent::__construct();
        include_once "./Modules/TestQuestionPool/classes/class.assMatchingQuestion.php";
        $this->object = new assMatchingQuestion();
        $this->setErrorMessage($this->lng->txt("msg_form_save_error"));
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
            if ($this->validateUploadSubforms()) {
                $this->writeAnswerSpecificPostData(new ilPropertyFormGUI());
            }
            $this->saveTaxonomyAssignments();
            return 0;
        }
        return 1;
    }

    public function validateUploadSubforms()
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setMultipart(true);
        $form->setTableWidth("100%");
        $form->setId("matching");

        $this->populateAnswerSpecificFormPart($form);

        $form->setValuesByPost();

        return $form->checkInput();
    }

    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form)
    {
        // Delete all existing answers and create new answers from the form data
        $this->object->flushMatchingPairs();
        $this->object->flushTerms();
        $this->object->flushDefinitions();

        // add terms
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php';
        foreach ($_POST['terms']['answer'] as $index => $answer) {
            $filename = $_POST['terms']['imagename'][$index];
            if (strlen($_FILES['terms']['name']['image'][$index])) {
                // upload the new file
                $name = $_FILES['terms']['name']['image'][$index];
                if ($this->object->setImageFile(
                    $_FILES['terms']['tmp_name']['image'][$index],
                    $this->object->getEncryptedFilename($name)
                )
                ) {
                    $filename = $this->object->getEncryptedFilename($name);
                } else {
                    $filename = "";
                }
            }
            $this->object->addTerm(
                new assAnswerMatchingTerm($answer, $filename, $_POST['terms']['identifier'][$index])
            );
        }
        // add definitions
        require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php';
        foreach ($_POST['definitions']['answer'] as $index => $answer) {
            $filename = $_POST['definitions']['imagename'][$index];
            if (strlen($_FILES['definitions']['name']['image'][$index])) {
                // upload the new file
                $name = $_FILES['definitions']['name']['image'][$index];
                if ($this->object->setImageFile(
                    $_FILES['definitions']['tmp_name']['image'][$index],
                    $this->object->getEncryptedFilename($name)
                )
                ) {
                    $filename = $this->object->getEncryptedFilename($name);
                } else {
                    $filename = "";
                }
            }
            $this->object->addDefinition(
                new assAnswerMatchingDefinition($answer, $filename, $_POST['definitions']['identifier'][$index])
            );
        }

        // add matching pairs
        if (is_array($_POST['pairs']['points'])) {
            require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';
            foreach ($_POST['pairs']['points'] as $index => $points) {
                $term_id = $_POST['pairs']['term'][$index];
                $definition_id = $_POST['pairs']['definition'][$index];
                $this->object->addMatchingPair(
                    $this->object->getTermWithIdentifier($term_id),
                    $this->object->getDefinitionWithIdentifier($definition_id),
                    $points
                );
            }
        }
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
    {
        if (!$this->object->getSelfAssessmentEditingMode()) {
            $this->object->setShuffle($_POST["shuffle"]);
        } else {
            $this->object->setShuffle(1);
        }
        $this->object->setThumbGeometry($_POST["thumb_geometry"]);
        $this->object->setMatchingMode($_POST['matching_mode']);
    }

    public function uploadterms()
    {
        $this->writePostData(true);
        $this->editQuestion();
    }

    public function removeimageterms()
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['removeimageterms']);
        $this->object->removeTermImage($position);
        $this->editQuestion();
    }

    public function uploaddefinitions()
    {
        $this->writePostData(true);
        $this->editQuestion();
    }

    public function removeimagedefinitions()
    {
        $this->writePostData(true);
        $position = key($_POST['cmd']['removeimagedefinitions']);
        $this->object->removeDefinitionImage($position);
        $this->editQuestion();
    }

    public function addterms()
    {
        $this->writePostData();
        $position = key($_POST["cmd"]["addterms"]);
        $this->object->insertTerm($position + 1);
        $this->editQuestion();
    }

    public function removeterms()
    {
        $this->writePostData();
        $position = key($_POST["cmd"]["removeterms"]);
        $this->object->deleteTerm($position);
        $this->editQuestion();
    }

    public function adddefinitions()
    {
        $this->writePostData();
        $position = key($_POST["cmd"]["adddefinitions"]);
        $this->object->insertDefinition($position + 1);
        $this->editQuestion();
    }

    public function removedefinitions()
    {
        $this->writePostData();
        $position = key($_POST["cmd"]["removedefinitions"]);
        $this->object->deleteDefinition($position);
        $this->editQuestion();
    }

    public function addpairs()
    {
        $this->writePostData();
        $position = key($_POST["cmd"]["addpairs"]);
        $this->object->insertMatchingPair($position + 1);
        $this->editQuestion();
    }

    public function removepairs()
    {
        $this->writePostData();
        $position = key($_POST["cmd"]["removepairs"]);
        $this->object->deleteMatchingPair($position);
        $this->editQuestion();
    }

    public function editQuestion($checkonly = false)
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
        $form->setId("matching");


        // title, author, description, question, working time (assessment mode)
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
            if (!$errors && !$this->isValidTermAndDefinitionAmount($form) && !$this->object->getSelfAssessmentEditingMode()) {
                $errors = true;
                $terms = $form->getItemByPostVar('terms');
                $terms->setAlert($this->lng->txt("msg_number_of_terms_too_low"));
                ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
            }
            if ($errors) {
                $checkonly = false;
            }
        }

        if (!$checkonly) {
            $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
        }
        return $errors;
    }

    private function isDefImgUploadCommand()
    {
        return $this->ctrl->getCmd() == 'uploaddefinitions';
    }

    private function isTermImgUploadCommand()
    {
        return $this->ctrl->getCmd() == 'uploadterms';
    }

    /**
     * for mode 1:1 terms count must not be less than definitions count
     * for mode n:n this limitation is cancelled
     *
     * @param ilPropertyFormGUI $form
     * @return bool
     */
    private function isValidTermAndDefinitionAmount(ilPropertyFormGUI $form)
    {
        $matchingMode = $form->getItemByPostVar('matching_mode')->getValue();

        if ($matchingMode == assMatchingQuestion::MATCHING_MODE_N_ON_N) {
            return true;
        }

        $numTerms = count($form->getItemByPostVar('terms')->getValues());
        $numDefinitions = count($form->getItemByPostVar('definitions')->getValues());

        if ($numTerms >= $numDefinitions) {
            return true;
        }

        return false;
    }

    public function populateAnswerSpecificFormPart(\ilPropertyFormGUI $form)
    {
        // Definitions
        include_once "./Modules/TestQuestionPool/classes/class.ilMatchingWizardInputGUI.php";
        $definitions = new ilMatchingWizardInputGUI($this->lng->txt("definitions"), "definitions");
        if ($this->object->getSelfAssessmentEditingMode()) {
            $definitions->setHideImages(true);
        }
        
        $definitions->setRequired(true);
        $definitions->setQuestionObject($this->object);
        $definitions->setTextName($this->lng->txt('definition_text'));
        $definitions->setImageName($this->lng->txt('definition_image'));
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
        if (!count($this->object->getDefinitions())) {
            $this->object->addDefinition(new assAnswerMatchingDefinition());
        }
        $definitionvalues = $this->object->getDefinitions();
        $definitions->setValues($definitionvalues);
        if ($this->isDefImgUploadCommand()) {
            $definitions->checkInput();
        }
        $form->addItem($definitions);

        // Terms
        include_once "./Modules/TestQuestionPool/classes/class.ilMatchingWizardInputGUI.php";
        $terms = new ilMatchingWizardInputGUI($this->lng->txt("terms"), "terms");
        if ($this->object->getSelfAssessmentEditingMode()) {
            $terms->setHideImages(true);
        }
        $terms->setRequired(true);
        $terms->setQuestionObject($this->object);
        $terms->setTextName($this->lng->txt('term_text'));
        $terms->setImageName($this->lng->txt('term_image'));
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
        if (!count($this->object->getTerms())) {
            $this->object->addTerm(new assAnswerMatchingTerm());
        }
        $termvalues = $this->object->getTerms();
        $terms->setValues($termvalues);
        if ($this->isTermImgUploadCommand()) {
            $terms->checkInput();
        }
        $form->addItem($terms);

        // Matching Pairs
        include_once "./Modules/TestQuestionPool/classes/class.ilMatchingPairWizardInputGUI.php";
        $pairs = new ilMatchingPairWizardInputGUI($this->lng->txt('matching_pairs'), 'pairs');
        $pairs->setRequired(true);
        $pairs->setTerms($this->object->getTerms());
        $pairs->setDefinitions($this->object->getDefinitions());
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php";
        if (count($this->object->getMatchingPairs()) == 0) {
            $this->object->addMatchingPair(new assAnswerMatchingPair($termvalues[0], $definitionvalues[0], 0));
        }
        $pairs->setPairs($this->object->getMatchingPairs());
        $form->addItem($pairs);

        return $form;
    }

    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form)
    {
        // Edit mode
        $hidden = new ilHiddenInputGUI("matching_type");
        $hidden->setValue($matchingtype);
        $form->addItem($hidden);

        if (!$this->object->getSelfAssessmentEditingMode()) {
            // shuffle
            $shuffle = new ilSelectInputGUI($this->lng->txt("shuffle_answers"), "shuffle");
            $shuffle_options = array(
                0 => $this->lng->txt("no"),
                1 => $this->lng->txt("matching_shuffle_terms_definitions"),
                2 => $this->lng->txt("matching_shuffle_terms"),
                3 => $this->lng->txt("matching_shuffle_definitions")
            );
            $shuffle->setOptions($shuffle_options);
            $shuffle->setValue($this->object->getShuffle() != null ? $this->object->getShuffle() : 1);
            $shuffle->setRequired(false);
            $form->addItem($shuffle);

            $geometry = new ilNumberInputGUI($this->lng->txt("thumb_geometry"), "thumb_geometry");
            $geometry->setValue($this->object->getThumbGeometry());
            $geometry->setRequired(true);
            $geometry->setMaxLength(6);
            $geometry->setMinValue(20);
            $geometry->setSize(6);
            $geometry->setInfo($this->lng->txt("thumb_geometry_info"));
            $form->addItem($geometry);
        }

        // Matching Mode
        $mode = new ilRadioGroupInputGUI($this->lng->txt('qpl_qst_inp_matching_mode'), 'matching_mode');
        $mode->setRequired(true);
        
        $modeONEonONE = new ilRadioOption(
            $this->lng->txt('qpl_qst_inp_matching_mode_one_on_one'),
            assMatchingQuestion::MATCHING_MODE_1_ON_1
        );
        $mode->addOption($modeONEonONE);

        $modeALLonALL = new ilRadioOption(
            $this->lng->txt('qpl_qst_inp_matching_mode_all_on_all'),
            assMatchingQuestion::MATCHING_MODE_N_ON_N
        );
        $mode->addOption($modeALLonALL);

        $mode->setValue($this->object->getMatchingMode());

        $form->addItem($mode);
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
        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_matching_output_solution.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        
        $solutions = array();
        if (($active_id > 0) && (!$show_correct_solution)) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $solutions = $this->object->getSolutionValues($active_id, $pass, !$this->getUseIntermediateSolution());
            $solution_script .= "";
        } else {
            foreach ($this->object->getMaximumScoringMatchingPairs() as $pair) {
                $solutions[] = array(
                    "value1" => $pair->term->identifier,
                    "value2" => $pair->definition->identifier,
                    'points' => $pair->points
                );
            }
        }

        $i = 0;
        
        foreach ($solutions as $solution) {
            $definition = $this->object->getDefinitionWithIdentifier($solution['value2']);
            $term = $this->object->getTermWithIdentifier($solution['value1']);
            $points = $solution['points'];

            if (is_object($definition)) {
                if (strlen($definition->picture)) {
                    if (strlen($definition->text)) {
                        $template->setCurrentBlock('definition_image_text');
                        $template->setVariable("TEXT_DEFINITION", ilUtil::prepareFormOutput($definition->text));
                        $template->parseCurrentBlock();
                    }
                    
                    $answerImageSrc = ilWACSignedPath::signFile(
                        $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $definition->picture
                    );
                    
                    $template->setCurrentBlock('definition_image');
                    $template->setVariable('ANSWER_IMAGE_URL', $answerImageSrc);
                    $template->setVariable('ANSWER_IMAGE_ALT', (strlen($definition->text)) ? ilUtil::prepareFormOutput($definition->text) : ilUtil::prepareFormOutput($definition->picture));
                    $template->setVariable('ANSWER_IMAGE_TITLE', (strlen($definition->text)) ? ilUtil::prepareFormOutput($definition->text) : ilUtil::prepareFormOutput($definition->picture));
                    $template->setVariable('URL_PREVIEW', $this->object->getImagePathWeb() . $definition->picture);
                    $template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
                    $template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
                    $template->parseCurrentBlock();
                } else {
                    $template->setCurrentBlock('definition_text');
                    $template->setVariable("DEFINITION", $this->object->prepareTextareaOutput($definition->text, true));
                    $template->parseCurrentBlock();
                }
            }
            if (is_object($term)) {
                if (strlen($term->picture)) {
                    if (strlen($term->text)) {
                        $template->setCurrentBlock('term_image_text');
                        $template->setVariable("TEXT_TERM", ilUtil::prepareFormOutput($term->text));
                        $template->parseCurrentBlock();
                    }

                    $answerImageSrc = ilWACSignedPath::signFile(
                        $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $term->picture
                    );
                    
                    $template->setCurrentBlock('term_image');
                    $template->setVariable('ANSWER_IMAGE_URL', $answerImageSrc);
                    $template->setVariable('ANSWER_IMAGE_ALT', (strlen($term->text)) ? ilUtil::prepareFormOutput($term->text) : ilUtil::prepareFormOutput($term->picture));
                    $template->setVariable('ANSWER_IMAGE_TITLE', (strlen($term->text)) ? ilUtil::prepareFormOutput($term->text) : ilUtil::prepareFormOutput($term->picture));
                    $template->setVariable('URL_PREVIEW', $this->object->getImagePathWeb() . $term->picture);
                    $template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
                    $template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
                    $template->parseCurrentBlock();
                } else {
                    $template->setCurrentBlock('term_text');
                    $template->setVariable("TERM", $this->object->prepareTextareaOutput($term->text, true));
                    $template->parseCurrentBlock();
                }
                $i++;
            }
            if (($active_id > 0) && (!$show_correct_solution)) {
                if ($graphicalOutput) {
                    // output of ok/not ok icons for user entered solutions
                    $ok = false;
                    foreach ($this->object->getMatchingPairs() as $pair) {
                        if ($this->isCorrectMatching($pair, $definition, $term)) {
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

            if ($result_output) {
                $resulttext = ($points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")";
                $template->setCurrentBlock("result_output");
                $template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
                $template->parseCurrentBlock();
            }

            $template->setCurrentBlock("row");
            $template->setVariable("TEXT_MATCHES", $this->lng->txt("matches"));
            $template->parseCurrentBlock();
        }

        $questiontext = $this->object->getQuestion();
        if ($show_question_text == true) {
            $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, true));
        }
        
        $questionoutput = $template->get();
        
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
        
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        if ($DIC['ilBrowser']->isMobile() || $DIC['ilBrowser']->isIpad()) {
            require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
            iljQueryUtil::initjQuery();
            iljQueryUtil::initjQueryUI();
            $this->tpl->addJavaScript('./node_modules/@andxor/jquery-ui-touch-punch-fix/jquery.ui.touch-punch.js');
        }
        $this->tpl->addJavaScript('Modules/TestQuestionPool/js/ilMatchingQuestion.js');
        $this->tpl->addCss(ilUtil::getStyleSheetLocation('output', 'test_javascript.css', 'Modules/TestQuestionPool'));

        $template = new ilTemplate("tpl.il_as_qpl_matching_output.html", true, true, "Modules/TestQuestionPool");

        foreach ($solutions as $defId => $terms) {
            foreach ($terms as $termId) {
                $template->setCurrentBlock("matching_data");
                $template->setVariable("DEFINITION_ID", $defId);
                $template->setVariable("TERM_ID", $termId);
                $template->parseCurrentBlock();
            }
        }
        
        // shuffle output
        $terms = $this->object->getTerms();
        $definitions = $this->object->getDefinitions();
        switch ($this->object->getShuffle()) {
            case 1:
                $seed = $this->object->getShuffler()->getSeed();
                $this->object->getShuffler()->setSeed($seed . '1');
                $terms = $this->object->getShuffler()->shuffle($terms);
                $this->object->getShuffler()->setSeed($seed . '2');
                $definitions = $this->object->getShuffler()->shuffle($definitions);
                $this->object->getShuffler()->setSeed($seed);
                break;
            case 2:
                $terms = $this->object->getShuffler()->shuffle($terms);
                break;
            case 3:
                $definitions = $this->object->getShuffler()->shuffle($definitions);
                break;
        }

        // create definitions
        $counter = 0;
        foreach ($definitions as $definition) {
            if (strlen($definition->picture)) {
                $template->setCurrentBlock("definition_picture");
                $template->setVariable("DEFINITION_ID", $definition->identifier);
                $template->setVariable("IMAGE_HREF", $this->object->getImagePathWeb() . $definition->picture);
                $thumbweb = $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $definition->picture;
                $thumb = $this->object->getImagePath() . $this->object->getThumbPrefix() . $definition->picture;
                if (!@file_exists($thumb)) {
                    $this->object->rebuildThumbnails();
                }
                $template->setVariable("THUMBNAIL_HREF", $thumbweb);
                $template->setVariable("THUMB_ALT", $this->lng->txt("image"));
                $template->setVariable("THUMB_TITLE", $this->lng->txt("image"));
                $template->setVariable("TEXT_DEFINITION", (strlen($definition->text)) ? $this->object->prepareTextareaOutput($definition->text, true, true) : '');
                $template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
                $template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
                $template->parseCurrentBlock();
            } else {
                $template->setCurrentBlock("definition_text");
                $template->setVariable("DEFINITION", $this->object->prepareTextareaOutput($definition->text, true, true));
                $template->parseCurrentBlock();
            }

            $template->setCurrentBlock("droparea");
            $template->setVariable("ID_DROPAREA", $definition->identifier);
            $template->setVariable("QUESTION_ID", $this->object->getId());
            $template->parseCurrentBlock();

            $template->setCurrentBlock("definition_data");
            $template->setVariable("DEFINITION_ID", $definition->identifier);
            $template->parseCurrentBlock();
        }

        // create terms
        $counter = 0;
        foreach ($terms as $term) {
            if (strlen($term->picture)) {
                $template->setCurrentBlock("term_picture");
                $template->setVariable("TERM_ID", $term->identifier);
                $template->setVariable("IMAGE_HREF", $this->object->getImagePathWeb() . $term->picture);
                $thumbweb = $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $term->picture;
                $thumb = $this->object->getImagePath() . $this->object->getThumbPrefix() . $term->picture;
                if (!@file_exists($thumb)) {
                    $this->object->rebuildThumbnails();
                }
                $template->setVariable("THUMBNAIL_HREF", $thumbweb);
                $template->setVariable("THUMB_ALT", $this->lng->txt("image"));
                $template->setVariable("THUMB_TITLE", $this->lng->txt("image"));
                $template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
                $template->setVariable("TEXT_TERM", (strlen($term->text)) ? $this->object->prepareTextareaOutput($term->text, true, true) : '');
                $template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
                $template->parseCurrentBlock();
            } else {
                $template->setCurrentBlock("term_text");
                $template->setVariable("TERM_TEXT", $this->object->prepareTextareaOutput($term->text, true, true));
                $template->parseCurrentBlock();
            }
            $template->setCurrentBlock("draggable");
            $template->setVariable("ID_DRAGGABLE", $term->identifier);
            $template->parseCurrentBlock();

            $template->setCurrentBlock("term_data");
            $template->setVariable("TERM_ID", $term->identifier);
            $template->parseCurrentBlock();
        }

        $template->setVariable('MATCHING_MODE', $this->object->getMatchingMode());

        $template->setVariable("RESET_BUTTON", $this->lng->txt("reset_terms"));

        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), true));

        $questionoutput = $template->get();

        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }

        return $questionoutput;
    }

    /**
     * @param array $solution
     * @param assAnswerMatchingDefinition[] $definitions
     * @return array
     */
    protected function sortDefinitionsBySolution(array $solution, array $definitions)
    {
        $neworder = array();
        $handled_defintions = array();
        foreach ($solution as $solution_values) {
            $id = $solution_values['value2'];
            if (!isset($handled_defintions[$id])) {
                $neworder[] = $this->object->getDefinitionWithIdentifier($id);
                $handled_defintions[$id] = $id;
            }
        }

        foreach ($definitions as $definition) {
            /**
             * @var $definition assAnswerMatchingDefinition
             */
            if (!isset($handled_defintions[$definition->identifier])) {
                $neworder[] = $definition;
            }
        }

        return $neworder;
    }
    
    public function getPresentationJavascripts()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $files = array();
        
        if ($DIC['ilBrowser']->isMobile() || $DIC['ilBrowser']->isIpad()) {
            $files[] = './node_modules/@andxor/jquery-ui-touch-punch-fix/jquery.ui.touch-punch.js';
        }
        
        $files[] = 'Modules/TestQuestionPool/js/ilMatchingQuestion.js';
        
        return $files;
    }

    // hey: prevPassSolutions - pass will be always available from now on
    public function getTestOutput($active_id, $pass, $is_postponed = false, $user_post_solution = false, $inlineFeedback = false)
    // hey.
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        if ($DIC['ilBrowser']->isMobile() || $DIC['ilBrowser']->isIpad()) {
            require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
            iljQueryUtil::initjQuery();
            iljQueryUtil::initjQueryUI();
            $this->tpl->addJavaScript('./node_modules/@andxor/jquery-ui-touch-punch-fix/jquery.ui.touch-punch.js');
        }
        $this->tpl->addJavaScript('Modules/TestQuestionPool/js/ilMatchingQuestion.js');
        $this->tpl->addCss(ilUtil::getStyleSheetLocation('output', 'test_javascript.css', 'Modules/TestQuestionPool'));

        $template = new ilTemplate("tpl.il_as_qpl_matching_output.html", true, true, "Modules/TestQuestionPool");

        if ($active_id) {
            // hey: prevPassSolutions - obsolete due to central check
            #$solutions = NULL;
            #include_once "./Modules/Test/classes/class.ilObjTest.php";
            #if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
            #{
            #	if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
            #}
            // hey.
            if (is_array($user_post_solution)) {
                $solutions = array();
                foreach ($user_post_solution['matching'][$this->object->getId()] as $definition => $term) {
                    array_push($solutions, array("value1" => $term, "value2" => $definition));
                }
            } else {
                // hey: prevPassSolutions - obsolete due to central check
                $solutions = $this->object->getTestOutputSolutions($active_id, $pass);
                // hey.
            }

            $counter = 0;
            foreach ($solutions as $idx => $solution_value) {
                if (($solution_value["value2"] > -1) && ($solution_value["value1"] > -1)) {
                    $template->setCurrentBlock("matching_data");
                    $template->setVariable("TERM_ID", $solution_value["value1"]);
                    $template->setVariable("DEFINITION_ID", $solution_value["value2"]);
                    $template->parseCurrentBlock();
                }

                $counter++;
            }
        }

        $terms = $this->object->getTerms();
        $definitions = $this->object->getDefinitions();
        switch ($this->object->getShuffle()) {
            case 1:
                $seed = $this->object->getShuffler()->getSeed();
                $this->object->getShuffler()->setSeed($seed . '1');
                $terms = $this->object->getShuffler()->shuffle($terms);
                if (count($solutions)) {
                    $definitions = $this->sortDefinitionsBySolution($solutions, $definitions);
                } else {
                    $this->object->getShuffler()->setSeed($seed . '2');
                    $definitions = $this->object->getShuffler()->shuffle($definitions);
                }
                $this->object->getShuffler()->setSeed($seed);
                break;
            case 2:
                $terms = $this->object->getShuffler()->shuffle($terms);
                break;
            case 3:
                if (count($solutions)) {
                    $definitions = $this->sortDefinitionsBySolution($solutions, $definitions);
                } else {
                    $definitions = $this->object->getShuffler()->shuffle($definitions);
                }
                break;
        }

        // create definitions
        $counter = 0;
        foreach ($definitions as $definition) {
            if (strlen($definition->picture)) {
                $template->setCurrentBlock("definition_picture");
                $template->setVariable("DEFINITION_ID", $definition->identifier);
                $template->setVariable("IMAGE_HREF", $this->object->getImagePathWeb() . $definition->picture);
                $thumbweb = $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $definition->picture;
                $thumb = $this->object->getImagePath() . $this->object->getThumbPrefix() . $definition->picture;
                if (!@file_exists($thumb)) {
                    $this->object->rebuildThumbnails();
                }
                $template->setVariable("THUMBNAIL_HREF", $thumbweb);
                $template->setVariable("THUMB_ALT", $this->lng->txt("image"));
                $template->setVariable("THUMB_TITLE", $this->lng->txt("image"));
                $template->setVariable("TEXT_DEFINITION", (strlen($definition->text)) ? $this->object->prepareTextareaOutput($definition->text, true, true) : '');
                $template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
                $template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
                $template->parseCurrentBlock();
            } else {
                $template->setCurrentBlock("definition_text");
                $template->setVariable("DEFINITION", $this->object->prepareTextareaOutput($definition->text, true, true));
                $template->parseCurrentBlock();
            }

            $template->setCurrentBlock("droparea");
            $template->setVariable("ID_DROPAREA", $definition->identifier);
            $template->setVariable("QUESTION_ID", $this->object->getId());
            $template->parseCurrentBlock();

            $template->setCurrentBlock("definition_data");
            $template->setVariable("DEFINITION_ID", $definition->identifier);
            $template->parseCurrentBlock();
        }

        // create terms
        $counter = 0;
        foreach ($terms as $term) {
            if (strlen($term->picture)) {
                $template->setCurrentBlock("term_picture");
                $template->setVariable("TERM_ID", $term->identifier);
                $template->setVariable("IMAGE_HREF", $this->object->getImagePathWeb() . $term->picture);
                $thumbweb = $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $term->picture;
                $thumb = $this->object->getImagePath() . $this->object->getThumbPrefix() . $term->picture;
                if (!@file_exists($thumb)) {
                    $this->object->rebuildThumbnails();
                }
                $template->setVariable("THUMBNAIL_HREF", $thumbweb);
                $template->setVariable("THUMB_ALT", $this->lng->txt("image"));
                $template->setVariable("THUMB_TITLE", $this->lng->txt("image"));
                $template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
                $template->setVariable("TEXT_TERM", (strlen($term->text)) ? $this->object->prepareTextareaOutput($term->text, true, true) : '');
                $template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
                $template->parseCurrentBlock();
            } else {
                $template->setCurrentBlock("term_text");
                $template->setVariable("TERM_TEXT", $this->object->prepareTextareaOutput($term->text, true, true));
                $template->parseCurrentBlock();
            }
            $template->setCurrentBlock("draggable");
            $template->setVariable("ID_DRAGGABLE", $term->identifier);
            $template->parseCurrentBlock();

            $template->setCurrentBlock('term_data');
            $template->setVariable('TERM_ID', $term->identifier);
            $template->parseCurrentBlock();
        }

        $template->setVariable('MATCHING_MODE', $this->object->getMatchingMode());

        $template->setVariable("RESET_BUTTON", $this->lng->txt("reset_terms"));

        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), true));

        return $this->outQuestionPage("", $is_postponed, $active_id, $template->get());
    }

    /**
    * check input fields
    */
    public function checkInput()
    {
        if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"])) {
            return false;
        }
        return true;
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
            $this->addTab_Question($ilTabs);
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
        $matches = array_values($this->object->matchingpairs);

        if (!$this->object->feedbackOBJ->specificAnswerFeedbackExists()) {
            return '';
        }
        
        $feedback = '<table class="test_specific_feedback"><tbody>';

        foreach ($matches as $idx => $ans) {
            if (!isset($userSolution[$ans->definition->identifier])) {
                continue;
            }
            
            if (!is_array($userSolution[$ans->definition->identifier])) {
                continue;
            }
            
            if (!in_array($ans->term->identifier, $userSolution[$ans->definition->identifier])) {
                continue;
            }
            
            $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                $this->object->getId(),
                0,
                $idx
            );
            $feedback .= '<tr><td>"' . $ans->definition->text . '"&nbsp;' . $this->lng->txt("matches") . '&nbsp;"';
            $feedback .= $ans->term->text . '"</td><td>';
            $feedback .= $fb . '</td> </tr>';
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
        return ''; //print_r($relevant_answers,true);
    }
    
    private function isCorrectMatching($pair, $definition, $term)
    {
        if (!($pair->points > 0)) {
            return false;
        }
        
        if (!is_object($term)) {
            return false;
        }

        if ($pair->definition->identifier != $definition->identifier) {
            return false;
        }

        if ($pair->term->identifier != $term->identifier) {
            return false;
        }
        
        return true;
    }
    
    protected function getAnswerStatisticImageHtml($picture)
    {
        $thumbweb = $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $picture;
        return '<img src="' . $thumbweb . '" alt="' . $picture . '" title="' . $picture . '"/>';
    }
    
    protected function getAnswerStatisticMatchingElemHtml($elem)
    {
        $html = '';
        
        if (strlen($elem->text)) {
            $html .= $elem->text;
        }
        
        if (strlen($elem->picture)) {
            $html .= $this->getAnswerStatisticImageHtml($elem->picture);
        }

        return $html;
    }
    
    public function getAnswersFrequency($relevantAnswers, $questionIndex)
    {
        $answersByActiveAndPass = array();
        
        foreach ($relevantAnswers as $row) {
            $key = $row['active_fi'] . ':' . $row['pass'];
            
            if (!isset($answersByActiveAndPass[$key])) {
                $answersByActiveAndPass[$key] = array();
            }
            
            $answersByActiveAndPass[$key][$row['value1']] = $row['value2'];
        }

        $answers = array();
        
        foreach ($answersByActiveAndPass as $key => $matchingPairs) {
            foreach ($matchingPairs as $termId => $defId) {
                $hash = md5($termId . ':' . $defId);
                
                if (!isset($answers[$hash])) {
                    $termHtml = $this->getAnswerStatisticMatchingElemHtml(
                        $this->object->getTermWithIdentifier($termId)
                    );
                    
                    $defHtml = $this->getAnswerStatisticMatchingElemHtml(
                        $this->object->getDefinitionWithIdentifier($defId)
                    );
                    
                    $answers[$hash] = array(
                        'answer' => $termHtml . $defHtml,
                        'term' => $termHtml,
                        'definition' => $defHtml,
                        'frequency' => 0
                    );
                }
                
                $answers[$hash]['frequency']++;
            }
        }
        
        return $answers;
    }
    
    /**
     * @param $parentGui
     * @param $parentCmd
     * @param $relevantAnswers
     * @param $questionIndex
     * @return ilMatchingQuestionAnswerFreqStatTableGUI
     */
    public function getAnswerFrequencyTableGUI($parentGui, $parentCmd, $relevantAnswers, $questionIndex)
    {
        require_once 'Modules/TestQuestionPool/classes/tables/class.ilMatchingQuestionAnswerFreqStatTableGUI.php';
        
        $table = new ilMatchingQuestionAnswerFreqStatTableGUI($parentGui, $parentCmd, $this->object);
        $table->setQuestionIndex($questionIndex);
        $table->setData($this->getAnswersFrequency($relevantAnswers, $questionIndex));
        $table->initColumns();
        
        return $table;
    }
    
    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssMatchingPairCorrectionsInputGUI.php';
        $pairs = new ilAssMatchingPairCorrectionsInputGUI($this->lng->txt('matching_pairs'), 'pairs');
        $pairs->setRequired(true);
        $pairs->setTerms($this->object->getTerms());
        $pairs->setDefinitions($this->object->getDefinitions());
        $pairs->setPairs($this->object->getMatchingPairs());
        $form->addItem($pairs);
    }
    
    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        $pairs = $form->getItemByPostVar('pairs')->getPairs();
        
        foreach ($this->object->getMatchingPairs() as $idx => $matchingPair) {
            $matchingPair->points = (float) $pairs[$idx]->points;
        }
    }
}
