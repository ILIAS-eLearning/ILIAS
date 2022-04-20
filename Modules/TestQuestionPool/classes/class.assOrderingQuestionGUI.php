<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Ordering question GUI representation
 *
 * The assOrderingQuestionGUI class encapsulates the GUI representation for ordering questions.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 * @ilCtrl_Calls assOrderingQuestionGUI: ilFormPropertyDispatchGUI
 */
class assOrderingQuestionGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
    /**
     * @var assOrderingQuestion
     */
    public $object;
    
    public $old_ordering_depth = array();
    public $leveled_ordering = array();

    /**
     * @var bool
     */
    private $clearAnswersOnWritingPostDataEnabled;
    
    /**
     * assOrderingQuestionGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the assOrderingQuestionGUI object.
     *
     * @param integer $id The database id of a ordering question object
     *
     * @return assOrderingQuestionGUI
     */
    public function __construct($id = -1)
    {
        parent::__construct();
        include_once "./Modules/TestQuestionPool/classes/class.assOrderingQuestion.php";
        $this->object = new assOrderingQuestion();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
        $this->clearAnswersOnWritingPostDataEnabled = false;
    }

    /**
     * @param boolean $clearAnswersOnWritingPostDataEnabled
     */
    public function setClearAnswersOnWritingPostDataEnabled($clearAnswersOnWritingPostDataEnabled)
    {
        $this->clearAnswersOnWritingPostDataEnabled = $clearAnswersOnWritingPostDataEnabled;
    }

    /**
     * @return boolean
     */
    public function isClearAnswersOnWritingPostDataEnabled()
    {
        return $this->clearAnswersOnWritingPostDataEnabled;
    }

    public function changeToPictures()
    {
        if ($this->object->getOrderingType() != OQ_NESTED_PICTURES && $this->object->getOrderingType() != OQ_PICTURES) {
            $this->setClearAnswersOnWritingPostDataEnabled(true);
        }
        
        $form = $this->buildEditForm();
        $form->setValuesByPost();
        $this->persistAuthoringForm($form);
        
        $this->object->setOrderingType(OQ_PICTURES);
        $this->object->saveToDb();
        
        $form->ensureReprintableFormStructure($this->object);
        $this->renderEditForm($form);
    }

    public function changeToText()
    {
        if ($this->object->getOrderingType() != OQ_NESTED_TERMS && $this->object->getOrderingType() != OQ_TERMS) {
            $this->setClearAnswersOnWritingPostDataEnabled(true);
        }
        
        $form = $this->buildEditForm();
        $form->setValuesByPost();
        $this->persistAuthoringForm($form);

        $this->object->setOrderingType(OQ_TERMS);
        $this->object->saveToDb();
        
        $form->ensureReprintableFormStructure($this->object);
        $this->renderEditForm($form);
    }

    public function orderNestedTerms()
    {
        $this->writePostData(true);
        $this->object->setOrderingType(OQ_NESTED_TERMS);
        $this->object->saveToDb();
        
        $this->renderEditForm($this->buildEditForm());
    }

    public function orderNestedPictures()
    {
        $this->writePostData(true);
        $this->object->setOrderingType(OQ_NESTED_PICTURES);
        $this->object->saveToDb();
        
        $this->renderEditForm($this->buildEditForm());
    }
    
    public function removeElementImage()
    {
        $orderingInput = $this->object->buildOrderingImagesInputGui();
        $this->object->initOrderingElementAuthoringProperties($orderingInput);

        $form = $this->buildEditForm();
        $form->replaceFormItemByPostVar($orderingInput);
        $form->setValuesByPost();
        
        $replacementElemList = ilAssOrderingElementList::buildInstance(
            $this->object->getId(),
            array()
        );
        
        $storedElementList = $this->object->getOrderingElementList();
        
        foreach ($orderingInput->getElementList($this->object->getId()) as $submittedElement) {
            if ($submittedElement->isImageRemovalRequest()) {
                if ($this->object->isImageFileStored($submittedElement->getContent())) {
                    $this->object->dropImageFile($submittedElement->getContent());
                }
                
                $submittedElement->setContent(null);
            }
            
            if ($storedElementList->elementExistByRandomIdentifier($submittedElement->getRandomIdentifier())) {
                $storedElement = $storedElementList->getElementByRandomIdentifier(
                    $submittedElement->getRandomIdentifier()
                );
                
                $submittedElement->setSolutionIdentifier($storedElement->getSolutionIdentifier());
                $submittedElement->setIndentation($storedElement->getIndentation());
            }
            
            $replacementElemList->addElement($submittedElement);
        }
        
        $replacementElemList->saveToDb();
        
        $orderingInput->setElementList($replacementElemList);
        $this->renderEditForm($form);
    }

    public function uploadElementImage()
    {
        $orderingInput = $this->object->buildOrderingImagesInputGui();
        $this->object->initOrderingElementAuthoringProperties($orderingInput);
        
        $form = $this->buildEditForm();
        $form->replaceFormItemByPostVar($orderingInput);
        $form->setValuesByPost();
        
        if (!$orderingInput->checkInput()) {
            ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
        }
        
        $this->writeAnswerSpecificPostData($form);
        $this->object->getOrderingElementList()->saveToDb();
        $orderingInput->setElementList($this->object->getOrderingElementList());
        
        $this->renderEditForm($form);
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
    {
        $this->object->setThumbGeometry($_POST["thumb_geometry"]);
       // $this->object->setElementHeight($_POST["element_height"]);
        //$this->object->setOrderingType( $_POST["ordering_type"] );
        $this->object->setPoints($_POST["points"]);
    }

    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form)
    {
        if (!is_array($_POST[assOrderingQuestion::ORDERING_ELEMENT_FORM_FIELD_POSTVAR])) {
            throw new ilTestQuestionPoolException('form submit request missing the form submit!?');
        }
        
        #$submittedElementList = $this->object->fetchSolutionListFromFormSubmissionData($_POST);
        $submittedElementList = $this->object->fetchSolutionListFromSubmittedForm($form);
        
        $replacementElementList = new ilAssOrderingElementList();
        $replacementElementList->setQuestionId($this->object->getId());
        
        $currentElementList = $this->object->getOrderingElementList();
        
        foreach ($submittedElementList as $submittedElement) {
            if ($this->object->hasOrderingTypeUploadSupport()) {
                if ($submittedElement->isImageUploadAvailable()) {
                    $suffix = strtolower(array_pop(explode(".", $submittedElement->getUploadImageName())));
                    if (in_array($suffix, array("jpg", "jpeg", "png", "gif"))) {
                        $submittedElement->setUploadImageName($this->object->buildHashedImageFilename(
                            $submittedElement->getUploadImageName(),
                            true
                        ));
                        
                        $wasImageFileStored = $this->object->storeImageFile(
                            $submittedElement->getUploadImageFile(),
                            $submittedElement->getUploadImageName()
                        );
                        
                        if ($wasImageFileStored) {
                            if ($this->object->isImageFileStored($submittedElement->getContent())) {
                                $this->object->dropImageFile($submittedElement->getContent());
                            }

                            $submittedElement->setContent($submittedElement->getUploadImageName());
                        }
                    }
                }
            }
            
            if ($currentElementList->elementExistByRandomIdentifier($submittedElement->getRandomIdentifier())) {
                $storedElement = $currentElementList->getElementByRandomIdentifier(
                    $submittedElement->getRandomIdentifier()
                );
                
                $submittedElement->setSolutionIdentifier($storedElement->getSolutionIdentifier());
                
                if ($this->isAdjustmentEditContext() || $this->object->isOrderingTypeNested()) {
                    $submittedElement->setContent($storedElement->getContent());
                }
                
                if (!$this->object->isOrderingTypeNested()) {
                    $submittedElement->setIndentation($storedElement->getIndentation());
                }
                
                if ($this->object->isImageReplaced($submittedElement, $storedElement)) {
                    $this->object->dropImageFile($storedElement->getContent());
                }
            }
            
            $replacementElementList->addElement($submittedElement);
        }
        
        if ($this->object->isImageOrderingType()) {
            $this->object->handleThumbnailCreation($replacementElementList);
        }
        
        if ($this->isClearAnswersOnWritingPostDataEnabled()) {
            $replacementElementList->clearElementContents();
        }
        
        if ($this->object->hasOrderingTypeUploadSupport()) {
            $obsoleteElementList = $currentElementList->getDifferenceElementList($replacementElementList);
            
            foreach ($obsoleteElementList as $obsoleteElement) {
                $this->object->dropImageFile($obsoleteElement->getContent());
            }
        }
        
        $this->object->setOrderingElementList($replacementElementList);
    }

    public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form)
    {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('oq_header_ordering_elements'));
        $form->addItem($header);
        
        if ($this->isAdjustmentEditContext()) {
            $orderingElementInput = $this->object->buildNestedOrderingElementInputGui();
        } else {
            $orderingElementInput = $this->object->buildOrderingElementInputGui();
        }
        
        $orderingElementInput->setStylingDisabled($this->isRenderPurposePrintPdf());
        $this->object->initOrderingElementAuthoringProperties($orderingElementInput);
        
        $orderingElementInput->setElementList($this->object->getOrderingElementList());
        
        $form->addItem($orderingElementInput);

        return $form;
    }
    
    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form)
    {
        if ($this->object->isImageOrderingType()) {
            $geometry = new ilNumberInputGUI($this->lng->txt("thumb_geometry"), "thumb_geometry");
            $geometry->setValue($this->object->getThumbGeometry());
            $geometry->setRequired(true);
            $geometry->setMaxLength(6);
            $geometry->setMinValue(20);
            $geometry->setSize(6);
            $geometry->setInfo($this->lng->txt("thumb_geometry_info"));
            $form->addItem($geometry);
        }

        // points
        $points = new ilNumberInputGUI($this->lng->txt("points"), "points");
        $points->allowDecimals(true);
        $points->setValue($this->object->getPoints());
        $points->setRequired(true);
        $points->setSize(3);
        $points->setMinValue(0);
        $points->setMinvalueShouldBeGreater(true);
        $form->addItem($points);
        
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    protected function writePostData($forceSaving = false)
    {
        $savingAllowed = true; // assume saving allowed first
        
        if (!$forceSaving) {
            // this case seems to be a regular save call, so we consider
            // the validation result for the decision of saving as well
            
            // inits {this->editForm} and performs validation
            $form = $this->buildEditForm();
            $form->setValuesByPost(); // manipulation and distribution of values
            
            if (!$form->checkInput()) { // manipulations regular style input propeties
                $form->prepareValuesReprintable($this->object);
                $this->renderEditForm($form);
                
                // consequence of vaidation
                $savingAllowed = false;
            }
        } elseif (!$this->isSaveCommand()) {
            // this case handles form workflow actions like the mode/view switching requests,
            // so saving must not be skipped, even for inputs invalid by business rules
            
            $form = $this->buildEditForm();
            $form->setValuesByPost(); // manipulation and distribution of values
            $form->checkInput(); // manipulations regular style input propeties
        }
        
        if ($savingAllowed) {
            $this->persistAuthoringForm($form);
            
            return 0; // return 0 = all fine, was saved either forced or validated
        }
        
        return 1; // return 1 = something went wrong, no saving happened
    }
    
    /**
     * Creates an output of the edit form for the question
     */
    public function editQuestion($checkonly = false)
    {
        $this->renderEditForm($this->buildEditForm());
    }
    
    /**
     * @return ilAssOrderingQuestionAuthoringFormGUI
     */
    protected function buildEditForm()
    {
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingQuestionAuthoringFormGUI.php';
        $form = new ilAssOrderingQuestionAuthoringFormGUI();
        $this->editForm = $form;
        
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setMultipart(($this->object->getOrderingType() == OQ_PICTURES) ? true : false);
        $form->setTableWidth("100%");
        $form->setId("ordering");
        // title, author, description, question, working time (assessment mode)
        $this->addBasicQuestionFormProperties($form);
        $this->populateQuestionSpecificFormPart($form);
        $this->populateAnswerSpecificFormPart($form);
        
        $this->populateTaxonomyFormSection($form);
        
        $form->addSpecificOrderingQuestionCommandButtons($this->object);
        $form->addGenericAssessmentQuestionCommandButtons($this->object);
        
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
        $forceCorrectSolution = false,
        $show_manual_scoring = false,
        $show_question_text = true
    ) {
        $solutionOrderingList = $this->object->getOrderingElementListForSolutionOutput(
            $forceCorrectSolution,
            $active_id,
            $pass,
            $this->getUseIntermediateSolution()
        );

        $answers_gui = $this->object->buildNestedOrderingElementInputGui();
        
        if ($forceCorrectSolution) {
            $answers_gui->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_CORRECT_SOLUTION_PRESENTATION);
        } else {
            $answers_gui->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_USER_SOLUTION_PRESENTATION);
        }
        
        $answers_gui->setInteractionEnabled(false);
        
        $answers_gui->setElementList($solutionOrderingList);
        
        $answers_gui->setCorrectnessTrueElementList(
            $solutionOrderingList->getParityTrueElementList($this->object->getOrderingElementList())
        );
        
        $solution_html = $answers_gui->getHTML();
    
        $template = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_solution.html", true, true, "Modules/TestQuestionPool");
        $template->setVariable('SOLUTION_OUTPUT', $solution_html);
        if ($show_question_text == true) {
            $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), true));
        }
        $questionoutput = $template->get();
    
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

        if ($show_feedback) {
            $feedback = '';
            
            if (!$this->isTestPresentationContext()) {
                $fb = $this->getGenericFeedbackOutput($active_id, $pass);
                $feedback .= strlen($fb) ? $fb : '';
            }
            
            $fb = $this->getSpecificFeedbackOutput(array());
            $feedback .= strlen($fb) ? $fb : '';
            
            if (strlen($feedback)) {
                $cssClass = (
                    $this->hasCorrectSolution($active_id, $pass) ?
                    ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT : ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG
                );
                
                $solutiontemplate->setVariable("ILC_FB_CSS_CLASS", $cssClass);
                $solutiontemplate->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($feedback, true));
            }
        }

        if ($show_question_only) {
            return $solutiontemplate->get();
        }
    
        return $this->getILIASPage($solutiontemplate->get());
        
        // is this template still in use? it is not used at this point any longer!
        // $template = new ilTemplate("tpl.il_as_qpl_ordering_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
    }
    
    public function getPreview($show_question_only = false, $showInlineFeedback = false)
    {
        if ($this->getPreviewSession() && $this->getPreviewSession()->hasParticipantSolution()) {
            $solutionOrderingElementList = unserialize(
                $this->getPreviewSession()->getParticipantsSolution()
            );
        } else {
            $solutionOrderingElementList = $this->object->getShuffledOrderingElementList();
        }
        
        $answers = $this->object->buildNestedOrderingElementInputGui();
        $answers->setNestingEnabled($this->object->isOrderingTypeNested());
        $answers->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_QUESTION_PREVIEW);
        $answers->setInteractionEnabled($this->isInteractivePresentation());
        $answers->setElementList($solutionOrderingElementList);
        
        $template = new ilTemplate("tpl.il_as_qpl_ordering_output.html", true, true, "Modules/TestQuestionPool");
        
        $template->setCurrentBlock('nested_ordering_output');
        $template->setVariable('NESTED_ORDERING', $answers->getHTML());
        $template->parseCurrentBlock();
        
        $template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), true));
        
        if ($show_question_only) {
            return $template->get();
        }
        
        return $this->getILIASPage($template->get());
        
        //$this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/ordering.js");
    }
    
    public function getPresentationJavascripts()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $files = array();
        
        if ($DIC['ilBrowser']->isMobile() || $DIC['ilBrowser']->isIpad()) {
            $files[] = './node_modules/@andxor/jquery-ui-touch-punch-fix/jquery.ui.touch-punch.js';
        }
        
        return $files;
    }
    
    // hey: prevPassSolutions - pass will be always available from now on
    public function getTestOutput($activeId, $pass, $isPostponed = false, $userSolutionPost = false, $inlineFeedback = false)
    // hey.
    {
        // hey: prevPassSolutions - fixed variable type, makes phpstorm stop crying
        $userSolutionPost = is_array($userSolutionPost) ? $userSolutionPost : array();
        // hey.
        
        $orderingGUI = $this->object->buildNestedOrderingElementInputGui();
        $orderingGUI->setNestingEnabled($this->object->isOrderingTypeNested());
        
        $solutionOrderingElementList = $this->object->getSolutionOrderingElementListForTestOutput(
            $orderingGUI,
            $userSolutionPost,
            $activeId,
            $pass
        );
        
        $template = new ilTemplate('tpl.il_as_qpl_ordering_output.html', true, true, 'Modules/TestQuestionPool');
        
        $orderingGUI->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_USER_SOLUTION_SUBMISSION);
        $orderingGUI->setElementList($solutionOrderingElementList);
        
        $template->setCurrentBlock('nested_ordering_output');
        $template->setVariable('NESTED_ORDERING', $orderingGUI->getHTML());
        $template->parseCurrentBlock();

        $template->setVariable('QUESTIONTEXT', $this->object->prepareTextareaOutput($this->object->getQuestion(), true));

        $pageoutput = $this->outQuestionPage('', $isPostponed, $activeId, $template->get());

        return $pageoutput;
    }
    
    protected function isInteractivePresentation()
    {
        if ($this->isRenderPurposePlayback()) {
            return true;
        }
        
        if ($this->isRenderPurposeDemoplay()) {
            return true;
        }
        
        return false;
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
        if (!$this->object->feedbackOBJ->specificAnswerFeedbackExists()) {
            return '';
        }

        $tpl = new ilTemplate('tpl.il_as_qpl_ordering_elem_fb.html', true, true, 'Modules/TestQuestionPool');
        
        foreach ($this->object->getOrderingElementList() as $element) {
            $feedback = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
                $this->object->getId(),
                0,
                $element->getPosition()
            );
            
            if ($this->object->isImageOrderingType()) {
                $imgSrc = $this->object->getImagePathWeb() . $element->getContent();
                $tpl->setCurrentBlock('image');
                $tpl->setVariable('IMG_SRC', $imgSrc);
            } else {
                $tpl->setCurrentBlock('text');
            }
            $tpl->setVariable('CONTENT', $element->getContent());
            $tpl->parseCurrentBlock();
            
            $tpl->setCurrentBlock('element');
            $tpl->setVariable('FEEDBACK', $feedback);
            $tpl->parseCurrentBlock();
        }

        return $this->object->prepareTextareaOutput($tpl->get(), true);
    }
    
    /**
     * @param $form
     * @throws ilTestQuestionPoolException
     */
    protected function persistAuthoringForm($form)
    {
        $this->writeQuestionGenericPostData();
        $this->writeQuestionSpecificPostData($form);
        $this->writeAnswerSpecificPostData($form);
        $this->saveTaxonomyAssignments();
    }
    
    private function getOldLeveledOrdering()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            'SELECT depth FROM qpl_a_ordering WHERE question_fi = %s ORDER BY solution_key ASC',
            array('integer'),
            array($this->object->getId())
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            $this->old_ordering_depth[] = $row['depth'];
        }
        return $this->old_ordering_depth;
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
        $aggView = $this->aggregateAnswers(
            $relevant_answers,
            $this->object->getOrderingElementList()
        );
        
        return  $this->renderAggregateView($aggView)->get();
    }

    public function aggregateAnswers($relevant_answers_chosen, $answers_defined_on_question)
    {
        $passdata = array(); // Regroup answers into units of passes.
        foreach ($relevant_answers_chosen as $answer_chosen) {
            $passdata[$answer_chosen['active_fi'] . '-' . $answer_chosen['pass']][$answer_chosen['value2']] = $answer_chosen['value1'];
        }
        
        $variants = array(); // Determine unique variants.
        foreach ($passdata as $key => $data) {
            $hash = md5(implode('-', $data));
            $value_set = false;
            foreach ($variants as $vkey => $variant) {
                if ($variant['hash'] == $hash) {
                    $variant['count']++;
                    $value_set = true;
                }
            }
            if (!$value_set) {
                $variants[$key]['hash'] = $hash;
                $variants[$key]['count'] = 1;
            }
        }

        $aggregate = array(); // Render aggregate from variant.
        foreach ($variants as $key => $variant_entry) {
            $variant = $passdata[$key];
            
            foreach ($variant as $variant_key => $variant_line) {
                $i = 0;
                $aggregated_info_for_answer['count'] = $variant_entry['count'];
                foreach ($answers_defined_on_question as $element) {
                    $i++;
                    
                    if ($this->object->isImageOrderingType()) {
                        $element->setImageThumbnailPrefix($this->object->getThumbPrefix());
                        $element->setImagePathWeb($this->object->getImagePathWeb());
                        $element->setImagePathFs($this->object->getImagePath());
                        
                        $src = $element->getPresentationImageUrl();
                        $alt = $element->getContent();
                        $content = "<img src='{$src}' alt='{$alt}' title='{$alt}'/>";
                    } else {
                        $content = $element->getContent();
                    }
                    
                    $aggregated_info_for_answer[$i . ' - ' . $content]
                        = $passdata[$key][$i];
                }
            }
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

        foreach ($aggregate as $line_data) {
            $tpl->setCurrentBlock('aggregaterow');
            $count = array_shift($line_data);
            $html = '<ul>';
            foreach ($line_data as $key => $line) {
                $html .= '<li>' . ++$line . '&nbsp;-&nbsp;' . $key . '</li>';
            }
            $html .= '</ul>';
            $tpl->setVariable('COUNT', $count);
            $tpl->setVariable('OPTION', $html);

            $tpl->parseCurrentBlock();
        }
        return $tpl;
    }
    
    protected function getAnswerStatisticOrderingElementHtml(ilAssOrderingElement $element)
    {
        if ($this->object->isImageOrderingType()) {
            $element->setImageThumbnailPrefix($this->object->getThumbPrefix());
            $element->setImagePathWeb($this->object->getImagePathWeb());
            $element->setImagePathFs($this->object->getImagePath());
            
            $src = $element->getPresentationImageUrl();
            $alt = $element->getContent();
            $content = "<img src='{$src}' alt='{$alt}' title='{$alt}'/>";
        } else {
            $content = $element->getContent();
        }
        
        return $content;
    }
    
    protected function getAnswerStatisticOrderingVariantHtml(ilAssOrderingElementList $list)
    {
        $html = '<ul>';
        
        $lastIndent = 0;
        $firstElem = true;
        
        foreach ($list as $elem) {
            if ($elem->getIndentation() > $lastIndent) {
                $html .= '<ul><li>';
            } elseif ($elem->getIndentation() < $lastIndent) {
                $html .= '</li></ul><li>';
            } elseif (!$firstElem) {
                $html .= '</li><li>';
            } else {
                $html .= '<li>';
            }

            $html .= $this->getAnswerStatisticOrderingElementHtml($elem);
            
            $firstElem = false;
            $lastIndent = $elem->getIndentation();
        }
        
        $html .= '</li>';
        
        for ($i = $lastIndent; $i > 0; $i--) {
            $html .= '</ul></li>';
        }
        
        $html .= '</ul>';
        
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
        
        $solutionLists = array();
        
        foreach ($answersByActiveAndPass as $indexedSolutions) {
            $solutionLists[] = $this->object->getSolutionOrderingElementList($indexedSolutions);
        }
        
        /* @var ilAssOrderingElementList[] $answers */
        $answers = array();
        
        foreach ($solutionLists as $orderingElementList) {
            $hash = $orderingElementList->getHash();
            
            if (!isset($answers[$hash])) {
                $variantHtml = $this->getAnswerStatisticOrderingVariantHtml(
                    $orderingElementList
                );
                
                $answers[$hash] = array(
                    'answer' => $variantHtml, 'frequency' => 0
                );
            }
            
            $answers[$hash]['frequency']++;
        }
        
        return array_values($answers);
    }
    
    /**
     * @param ilPropertyFormGUI $form
     */
    public function prepareReprintableCorrectionsForm(ilPropertyFormGUI $form)
    {
        $orderingInput = $form->getItemByPostVar(assOrderingQuestion::ORDERING_ELEMENT_FORM_FIELD_POSTVAR);
        $orderingInput->prepareReprintable($this->object);
    }
    
    /**
     * @param ilPropertyFormGUI $form
     */
    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        $points = new ilNumberInputGUI($this->lng->txt("points"), "points");
        $points->allowDecimals(true);
        $points->setValue($this->object->getPoints());
        $points->setRequired(true);
        $points->setSize(3);
        $points->setMinValue(0);
        $points->setMinvalueShouldBeGreater(true);
        $form->addItem($points);

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('oq_header_ordering_elements'));
        $form->addItem($header);
        
        $orderingElementInput = $this->object->buildNestedOrderingElementInputGui();
        
        $this->object->initOrderingElementAuthoringProperties($orderingElementInput);
        
        $orderingElementInput->setElementList($this->object->getOrderingElementList());
        
        $form->addItem($orderingElementInput);
    }
    
    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        $this->object->setPoints((float) $form->getInput('points'));
        
        $submittedElementList = $this->object->fetchSolutionListFromSubmittedForm($form);
        
        $curElementList = $this->object->getOrderingElementList();
        
        $newElementList = new ilAssOrderingElementList();
        $newElementList->setQuestionId($this->object->getId());
        
        foreach ($submittedElementList as $submittedElement) {
            if (!$curElementList->elementExistByRandomIdentifier($submittedElement->getRandomIdentifier())) {
                continue;
            }
            
            $curElement = $curElementList->getElementByRandomIdentifier($submittedElement->getRandomIdentifier());
            
            $curElement->setPosition($submittedElement->getPosition());
                
            if ($this->object->isOrderingTypeNested()) {
                $curElement->setIndentation($submittedElement->getIndentation());
            }
            
            $newElementList->addElement($curElement);
        }
        
        $this->object->setOrderingElementList($newElementList);
    }
}
