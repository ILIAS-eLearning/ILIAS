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
 * Ordering question GUI representation
 *
 * The assOrderingQuestionGUI class encapsulates the GUI representation for ordering questions.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 * @author  Nils Haagen <nils.haagen@concepts-and-training.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 * @ilCtrl_Calls assOrderingQuestionGUI: ilFormPropertyDispatchGUI
 */
class assOrderingQuestionGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
    public const CMD_EDIT_NESTING = 'editNesting';
    public const CMD_SAVE_NESTING = 'saveNesting';
    public const CMD_SWITCH_TO_TERMS = 'changeToText';
    public const CMD_SWITCH_TO_PICTURESS = 'changeToPictures';

    public const TAB_EDIT_QUESTION = 'edit_question';
    public const TAB_EDIT_NESTING = 'edit_nesting';

    public const F_USE_NESTED = 'nested_answers';
    public const F_NESTED_ORDER = 'order_elems';
    public const F_NESTED_ORDER_ORDER = 'content';
    public const F_NESTED_ORDER_INDENT = 'indentation';
    public const F_NESTED_IDENTIFIER_PREFIX = ilIdentifiedMultiValuesJsPositionIndexRemover::IDENTIFIER_INDICATOR_PREFIX;

    public assQuestion $object;

    public $old_ordering_depth = [];
    public $leveled_ordering = [];

    /**
     * assOrderingQuestionGUI constructor
     *
     * The constructor takes possible arguments an creates an instance of the assOrderingQuestionGUI object.
     *
     * @param integer $id The database id of a ordering question object
     */
    public function __construct($id = -1)
    {
        parent::__construct();
        include_once "./Modules/TestQuestionPool/classes/class.assOrderingQuestion.php";
        $this->object = new assOrderingQuestion();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }

    public function changeToPictures(): void
    {
        $this->object->setContentType($this->object::OQ_CT_PICTURES);
        $this->object->saveToDb();

        $values = $this->request->getParsedBody();
        $values['thumb_geometry'] = $this->object->getThumbSize();
        $this->buildEditFormAfterTypeChange($values);
    }

    public function changeToText(): void
    {
        $ordering_element_list = $this->object->getOrderingElementList();
        foreach ($ordering_element_list as $element) {
            $this->object->dropImageFile($element->getContent());
        }

        $this->object->setContentType($this->object::OQ_CT_TERMS);
        $this->object->saveToDb();

        $this->buildEditFormAfterTypeChange($this->request->getParsedBody());
    }

    private function buildEditFormAfterTypeChange(array $values): void
    {
        $form = $this->buildEditForm();

        $ordering_element_list = $this->object->getOrderingElementList();
        $ordering_element_list->resetElements();

        $values[assOrderingQuestion::ORDERING_ELEMENT_FORM_FIELD_POSTVAR] = [];
        $form->setValuesByArray($values);
        $form->getItemByPostVar(assOrderingQuestion::ORDERING_ELEMENT_FORM_FIELD_POSTVAR)->setElementList($ordering_element_list);
        $this->renderEditForm($form);
        $this->addEditSubtabs();
    }

    public function saveNesting()
    {
        $form = $this->buildNestingForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $post = $this->request->raw(self::F_NESTED_ORDER);
            $list = $this->object->getOrderingElementList();

            $ordered = [];
            foreach (array_keys($post[self::F_NESTED_ORDER_ORDER]) as $idx => $identifier) {
                $element_identifier = str_replace(self::F_NESTED_IDENTIFIER_PREFIX, '', $identifier);
                $element = $list->getElementByRandomIdentifier($element_identifier);

                $ordered[] = $element
                    ->withPosition($idx)
                    ->withIndentation($post[self::F_NESTED_ORDER_INDENT][$identifier]);
            }

            $list = $list->withElements($ordered);
            $this->object->setOrderingElementList($list);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        } else {
            $this->tpl->setOnScreenMessage('error', $this->lng->txt('form_input_not_valid'), true);
        }

        $this->editNesting();
    }


    public function removeElementImage()
    {
        $this->uploadElementImage();
    }

    public function uploadElementImage(): void
    {
        $form = $this->buildEditForm();
        $form->setValuesByPost();

        $submitted_list = $this->fetchSolutionListFromSubmittedForm($form);

        $elements = [];
        foreach ($submitted_list->getElements() as $submitted_element) {
            if ($submitted_element->isImageUploadAvailable()) {
                $filename = $this->object->storeImageFile(
                    $submitted_element->getUploadImageFile(),
                    $submitted_element->getUploadImageName()
                );

                if (is_null($filename)) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('file_no_valid_file_type'));
                } else {
                    $submitted_element = $submitted_element->withContent($filename);
                }
            }

            if ($submitted_element->isImageRemovalRequest()) {
                $this->object->dropImageFile($submitted_element->getContent());
                $submitted_element = $submitted_element->withContent('');
            }

            $elements[] = $submitted_element;
        }

        $list = $this->object->getOrderingElementList()->withElements($elements);
        $this->object->setOrderingElementList($list);

        $this->writeQuestionGenericPostData();
        $this->writeQuestionSpecificPostData($form);

        $this->editQuestion();
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form): void
    {
        $thumb_size = $this->request->int('thumb_geometry');
        if ($thumb_size !== 0
            && $thumb_size !== $this->object->getThumbSize()) {
            $this->object->setThumbSize($thumb_size);
            $this->updateImageFiles();
        }

        $points = (float) str_replace(',', '.', $this->request->raw('points'));

        $this->object->setPoints($points);

        $use_nested = $this->request->raw(self::F_USE_NESTED) === '1';
        $this->object->setNestingType($use_nested);
    }


    protected function fetchSolutionListFromSubmittedForm(ilPropertyFormGUI $form): ilAssOrderingElementList
    {
        $list = $form->getItemByPostVar(assOrderingQuestion::ORDERING_ELEMENT_FORM_FIELD_POSTVAR)
            ->getElementList($this->object->getId());

        $use_nested = $this->request->raw(self::F_USE_NESTED) === "1";

        if ($use_nested) {
            $existing_list = $this->object->getOrderingElementList();

            $nu = [];
            $parent_indent = -1;
            foreach ($list->getElements() as $element) {
                $element = $list->ensureValidIdentifiers($element);

                if ($existing = $existing_list->getElementByRandomIdentifier($element->getRandomIdentifier())) {
                    if ($existing->getIndentation() == $parent_indent + 1) {
                        $element = $element
                            ->withIndentation($existing->getIndentation());
                    }
                }
                $parent_indent = $element->getIndentation();
                $nu[] = $element;
            }
            $list = $list->withElements($nu);
        }

        return $list;
    }

    protected function updateImageFiles(): void
    {
        $element_list = $this->object->getOrderingElementList();
        $elements = [];
        foreach ($element_list->getElements() as $element) {
            if ($element->getContent() === '') {
                continue;
            }
            $filename = $this->object->updateImageFile(
                $element->getContent()
            );

            $elements[] = $element->withContent($filename);
        }

        $list = $this->object->getOrderingElementList()->withElements($elements);
        $this->object->setOrderingElementList($list);
    }

    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form): void
    {
        $list = $this->fetchSolutionListFromSubmittedForm($form);
        $this->object->setOrderingElementList($list);
        return;
    }

    public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('oq_header_ordering_elements'));
        $form->addItem($header);

        $orderingElementInput = $this->object->buildOrderingElementInputGui();
        $orderingElementInput->setStylingDisabled($this->isRenderPurposePrintPdf());
        $this->object->initOrderingElementAuthoringProperties($orderingElementInput);

        $list = $this->object->getOrderingElementList();
        $orderingElementInput->setElementList($list);
        $form->addItem($orderingElementInput);

        return $form;
    }

    public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        if ($this->object->isImageOrderingType()) {
            $thumb_size = new ilNumberInputGUI($this->lng->txt('thumb_size'), 'thumb_geometry');
            $thumb_size->setValue($this->object->getThumbSize());
            $thumb_size->setRequired(true);
            $thumb_size->setMaxLength(6);
            $thumb_size->setMinValue($this->object->getMinimumThumbSize());
            $thumb_size->setSize(6);
            $thumb_size->setInfo($this->lng->txt('thumb_size_info'));
            $form->addItem($thumb_size);
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

        $nested_answers = new ilSelectInputGUI(
            $this->lng->txt('qst_use_nested_answers'),
            self::F_USE_NESTED
        );
        $nested_answers_options = [
            0 => $this->lng->txt('qst_nested_nested_answers_off'),
            1 => $this->lng->txt('qst_nested_nested_answers_on')
        ];
        $nested_answers->setOptions($nested_answers_options);
        $nested_answers->setValue($this->object->isOrderingTypeNested());
        $form->addItem($nested_answers);

        return $form;
    }

    /**
    * {@inheritdoc}
    *
    * parent::save calls this->writePostData.
    * afterwards, object->saveToDb is called.
    */
    protected function writePostData(bool $always = false): int
    {
        $form = $this->buildEditForm();
        $form->setValuesByPost();

        if (!$form->checkInput()) {
            $this->renderEditForm($form);
            $this->addEditSubtabs(self::TAB_EDIT_QUESTION);
            return 1; // return 1 = something went wrong, no saving happened
        }

        $this->saveTaxonomyAssignments();
        $this->writeQuestionGenericPostData();
        $this->writeAnswerSpecificPostData($form);
        $this->writeQuestionSpecificPostData($form);

        return 0; // return 0 = all fine, was saved either forced or validated
    }

    protected function addEditSubtabs($active = self::TAB_EDIT_QUESTION)
    {
        $tabs = $this->getTabs();
        $tabs->addSubTab(
            self::TAB_EDIT_QUESTION,
            $this->lng->txt('edit_question'),
            $this->ctrl->getLinkTarget($this, 'editQuestion')
        );
        if ($this->object->isOrderingTypeNested()) {
            $tabs->addSubTab(
                self::TAB_EDIT_NESTING,
                $this->lng->txt('tab_nest_answers'),
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT_NESTING)
            );
        }
        $tabs->setTabActive('edit_question');
        $tabs->setSubTabActive($active);
    }

    public function editQuestion($checkonly = false): void
    {
        $this->renderEditForm($this->buildEditForm());
        $this->addEditSubtabs(self::TAB_EDIT_QUESTION);
    }

    public function editNesting()
    {
        $this->renderEditForm($this->buildNestingForm());
        $this->addEditSubtabs(self::TAB_EDIT_NESTING);
        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
    }

    protected function buildEditForm(): ilAssOrderingQuestionAuthoringFormGUI
    {
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingQuestionAuthoringFormGUI.php';
        $form = new ilAssOrderingQuestionAuthoringFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setMultipart($this->object->isImageOrderingType());
        $form->setTableWidth("100%");
        $form->setId("ordering");

        $this->addBasicQuestionFormProperties($form);
        $this->populateQuestionSpecificFormPart($form);
        $this->populateAnswerSpecificFormPart($form);
        $this->populateTaxonomyFormSection($form);

        $form->addSpecificOrderingQuestionCommandButtons($this->object);
        $form->addGenericAssessmentQuestionCommandButtons($this->object);

        return $form;
    }

    protected function buildNestingForm()
    {
        $form = new ilAssOrderingQuestionAuthoringFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->outQuestionType());
        $form->setTableWidth("100%");

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('oq_header_ordering_elements'));
        $form->addItem($header);

        $orderingElementInput = $this->object->buildNestedOrderingElementInputGui();
        $orderingElementInput->setStylingDisabled($this->isRenderPurposePrintPdf());

        $this->object->initOrderingElementAuthoringProperties($orderingElementInput);

        $list =  $this->object->getOrderingElementList();
        foreach ($list->getElements() as $element) {
            $element = $list->ensureValidIdentifiers($element);
        }

        $orderingElementInput->setElementList($list);

        $form->addItem($orderingElementInput);
        $form->addCommandButton(self::CMD_SAVE_NESTING, $this->lng->txt("save"));
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
     * @param integer $active_id             The active user id
     * @param integer $pass                  The test pass
     * @param boolean $graphicalOutput       Show visual feedback for right/wrong answers
     * @param boolean $result_output         Show the reached points for parts of the question
     * @param boolean $show_question_only    Show the question without the ILIAS content around
     * @param boolean $show_feedback         Show the question feedback
     * @param boolean $show_correct_solution Show the correct solution instead of the user solution
     * @param boolean $show_manual_scoring   Show specific information for the manual scoring output
     * @param bool    $show_question_text
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
        $solutionOrderingList = $this->object->getOrderingElementListForSolutionOutput(
            $show_correct_solution,
            $active_id,
            $pass
        );

        $answers_gui = $this->object->buildNestedOrderingElementInputGui();

        if ($show_correct_solution) {
            $answers_gui->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_CORRECT_SOLUTION_PRESENTATION);
        } else {
            $answers_gui->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_USER_SOLUTION_PRESENTATION);
        }

        $answers_gui->setInteractionEnabled(false);
        $answers_gui->setElementList($solutionOrderingList);
        if ($graphicalOutput) {
            $answers_gui->setShowCorrectnessIconsEnabled(true);
        }
        $answers_gui->setCorrectnessTrueElementList(
            $solutionOrderingList->getParityTrueElementList($this->object->getOrderingElementList())
        );

        $solution_html = $answers_gui->getHTML();

        $template = new ilTemplate("tpl.il_as_qpl_nested_ordering_output_solution.html", true, true, "Modules/TestQuestionPool");
        $template->setVariable('SOLUTION_OUTPUT', $solution_html);
        if ($show_question_text == true) {
            $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        }
        $questionoutput = $template->get();

        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        $solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

        if ($show_feedback) {
            $feedback = '';

            if (!$this->isTestPresentationContext()) {
                $fb = $this->getGenericFeedbackOutput((int) $active_id, $pass);
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
        }

        if ($show_question_only) {
            return $solutiontemplate->get();
        }

        return $this->getILIASPage($solutiontemplate->get());

        // is this template still in use? it is not used at this point any longer!
        // $template = new ilTemplate("tpl.il_as_qpl_ordering_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
    }

    public function getPreview($show_question_only = false, $showInlineFeedback = false): string
    {
        if ($this->getPreviewSession() && $this->getPreviewSession()->hasParticipantSolution()) {
            $solutionOrderingElementList = unserialize(
                $this->getPreviewSession()->getParticipantsSolution(),
                ["allowed_classes" => true]
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

        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());

        if ($show_question_only) {
            return $template->get();
        }

        return $this->getILIASPage($template->get());
    }

    public function getPresentationJavascripts(): array
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $files = [];

        if ($DIC->http()->agent()->isMobile() || $DIC->http()->agent()->isIpad()) {
            $files[] = './node_modules/@andxor/jquery-ui-touch-punch-fix/jquery.ui.touch-punch.js';
        }

        return $files;
    }

    // hey: prevPassSolutions - pass will be always available from now on
    public function getTestOutput($activeId, $pass, $isPostponed = false, $userSolutionPost = false, $inlineFeedback = false): string
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

        $template->setVariable('QUESTIONTEXT', $this->object->getQuestionForHTMLOutput());

        $pageoutput = $this->outQuestionPage('', $isPostponed, $activeId, $template->get());

        return $pageoutput;
    }

    protected function isInteractivePresentation(): bool
    {
        if ($this->isRenderPurposePlayback()) {
            return true;
        }

        if ($this->isRenderPurposeDemoplay()) {
            return true;
        }

        return false;
    }

    protected function getTabs(): ilTabsGUI
    {
        global $DIC;
        return $DIC['ilTabs'];
    }

    public function getSpecificFeedbackOutput(array $userSolution): string
    {
        return '';
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

    /**
     * Returns an html string containing a question specific representation of the answers so far
     * given in the test for use in the right column in the scoring adjustment user interface.
     * @param array $relevant_answers
     * @return string
     */
    public function getAggregatedAnswersView(array $relevant_answers): string
    {
        $aggView = $this->aggregateAnswers(
            $relevant_answers,
            $this->object->getOrderingElementList()
        );

        return  $this->renderAggregateView($aggView)->get();
    }

    public function aggregateAnswers($relevant_answers_chosen, $answers_defined_on_question): array
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
    public function renderAggregateView($aggregate): ilTemplate
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

    protected function getAnswerStatisticOrderingElementHtml(ilAssOrderingElement $element): ?string
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

    protected function getAnswerStatisticOrderingVariantHtml(ilAssOrderingElementList $list): string
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

    public function getAnswersFrequency($relevantAnswers, $questionIndex): array
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
    public function prepareReprintableCorrectionsForm(ilPropertyFormGUI $form): void
    {
        $orderingInput = $form->getItemByPostVar(assOrderingQuestion::ORDERING_ELEMENT_FORM_FIELD_POSTVAR);
        $orderingInput->prepareReprintable($this->object);
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form): void
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
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form): void
    {
        $this->object->setPoints((float) str_replace(',', '.', $form->getInput('points')));

        $submittedElementList = $this->fetchSolutionListFromSubmittedForm($form);

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
