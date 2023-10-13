<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';

/**
 * Cloze test question GUI representation
 *
 * The assClozeTestGUI class encapsulates the GUI representation
 * for cloze test questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup 	ModulesTestQuestionPool
 * @ilCtrl_Calls assClozeTestGUI: ilFormPropertyDispatchGUI
 */
class assClozeTestGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
    const OLD_CLOZE_TEST_UI = false;

    /**
    * A temporary variable to store gap indexes of ilCtrl commands in the getCommand method
    */
    private $gapIndex;

    /**
    * assClozeTestGUI constructor
    *
    * @param integer $id The database id of a image map question object
    */
    public function __construct($id = -1)
    {
        parent::__construct();
        include_once "./Modules/TestQuestionPool/classes/class.assClozeTest.php";
        $this->object = new assClozeTest();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }

    public function getCommand($cmd)
    {
        if (preg_match("/^(removegap|addgap)_(\d+)$/", $cmd, $matches)) {
            $cmd = $matches[1];
            $this->gapIndex = $matches[2];
        }
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
            $_POST['cloze_text'] = $this->removeIndizesFromGapText($_POST['cloze_text']);
            $this->object->setQuestion($_POST['question']);

            $this->writeQuestionGenericPostData();
            $this->object->setClozeText($_POST["cloze_text"]);
            $this->writeQuestionSpecificPostData(new ilPropertyFormGUI());
            //$this->object->flushGaps();
            $this->writeAnswerSpecificPostData(new ilPropertyFormGUI());
            $this->saveTaxonomyAssignments();
            return 0;
        }

        $_POST['cloze_text'] = $this->applyIndizesToGapText($_POST['cloze_text']);
        return 1;
    }

    public function writeAnswerSpecificPostData(ilPropertyFormGUI $form)
    {
        if (is_array($_POST['gap'])) {
            if ($this->ctrl->getCmd() != 'createGaps') {
                $this->object->clearGapAnswers();
            }

            foreach ($_POST['gap'] as $idx => $hidden) {
                $clozetype = $_POST['clozetype_' . $idx];

                $this->object->setGapType($idx, $clozetype);

                switch ($clozetype) {
                    case CLOZE_TEXT:

                        $this->object->setGapShuffle($idx, 0);

                        if ($this->ctrl->getCmd() != 'createGaps') {
                            if (is_array($_POST['gap_' . $idx]['answer'])) {
                                foreach ($_POST['gap_' . $idx]['answer'] as $order => $value) {
                                    $this->object->addGapAnswer($idx, $order, $value);
                                }
                            } else {
                                $this->object->addGapAnswer($idx, 0, '');
                            }
                        }

                        if (is_array($_POST['gap_' . $idx]['points'])) {
                            foreach ($_POST['gap_' . $idx]['points'] as $order => $value) {
                                $this->object->setGapAnswerPoints($idx, $order, $value);
                            }
                        }

                        if (array_key_exists('gap_' . $idx . '_gapsize', $_POST)) {
                            $this->object->setGapSize($idx, $order, $_POST['gap_' . $idx . '_gapsize']);
                        }

                        break;

                    case CLOZE_SELECT:

                        $this->object->setGapShuffle($idx, (int) (isset($_POST["shuffle_$idx"]) && $_POST["shuffle_$idx"]));

                        if ($this->ctrl->getCmd() != 'createGaps') {
                            if (is_array($_POST['gap_' . $idx]['answer'])) {
                                foreach ($_POST['gap_' . $idx]['answer'] as $order => $value) {
                                    $this->object->addGapAnswer($idx, $order, $value);
                                }
                            } else {
                                $this->object->addGapAnswer($idx, 0, '');
                            }
                        }

                        if (is_array($_POST['gap_' . $idx]['points'])) {
                            foreach ($_POST['gap_' . $idx]['points'] as $order => $value) {
                                $this->object->setGapAnswerPoints($idx, $order, $value);
                            }
                        }
                        break;

                    case CLOZE_NUMERIC:

                        $this->object->setGapShuffle($idx, 0);

                        $gap = $this->object->getGap($idx);
                        if (!$gap) {
                            break;
                        }

                        $this->object->getGap($idx)->clearItems();

                        if (array_key_exists('gap_' . $idx . '_numeric', $_POST)) {
                            if ($this->ctrl->getCmd() != 'createGaps') {
                                $this->object->addGapAnswer(
                                    $idx,
                                    0,
                                    str_replace(",", ".", $_POST['gap_' . $idx . '_numeric'])
                                );
                            }

                            $this->object->setGapAnswerLowerBound(
                                $idx,
                                0,
                                str_replace(",", ".", $_POST['gap_' . $idx . '_numeric_lower'])
                            );

                            $this->object->setGapAnswerUpperBound(
                                $idx,
                                0,
                                str_replace(",", ".", $_POST['gap_' . $idx . '_numeric_upper'])
                            );

                            $this->object->setGapAnswerPoints($idx, 0, $_POST['gap_' . $idx . '_numeric_points']);
                        } else {
                            if ($this->ctrl->getCmd() != 'createGaps') {
                                $this->object->addGapAnswer($idx, 0, '');
                            }

                            $this->object->setGapAnswerLowerBound($idx, 0, '');

                            $this->object->setGapAnswerUpperBound($idx, 0, '');
                        }

                        if (array_key_exists('gap_' . $idx . '_gapsize', $_POST)) {
                            $this->object->setGapSize($idx, $order, $_POST['gap_' . $idx . '_gapsize']);
                        }
                        break;
                }
                $assClozeGapCombinationObject = new assClozeGapCombination();
                $assClozeGapCombinationObject->clearGapCombinationsFromDb($this->object->getId());
                if (
                    isset($_POST['gap_combination']) &&
                    is_array($_POST['gap_combination']) &&
                    count($_POST['gap_combination']) > 0
                ) {
                    $assClozeGapCombinationObject->saveGapCombinationToDb(
                        $this->object->getId(),
                        $_POST['gap_combination'],
                        $_POST['gap_combination_values']
                    );
                }
            }
            if ($this->ctrl->getCmd() != 'createGaps') {
                $this->object->updateClozeTextFromGaps();
            }
        }
    }

    public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
    {
        $this->object->setClozeText($_POST['cloze_text']);
        $this->object->setTextgapRating($_POST["textgap_rating"]);
        $this->object->setIdenticalScoring($_POST["identical_scoring"]);
        $this->object->setFixedTextLength($_POST["fixedTextLength"]);
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
        $form->setId("assclozetest");

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
            $form->setValuesByPost(); 	// again, because checkInput now performs the whole stripSlashes handling and we
            // need this if we don't want to have duplication of backslashes
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
        // title
        $title = new ilTextInputGUI($this->lng->txt("title"), "title");
        $title->setMaxLength(100);
        $title->setValue($this->object->getTitle());
        $title->setRequired(true);
        $form->addItem($title);

        if (!$this->object->getSelfAssessmentEditingMode()) {
            // author
            $author = new ilTextInputGUI($this->lng->txt("author"), "author");
            $author->setValue($this->object->getAuthor());
            $author->setRequired(true);
            $form->addItem($author);

            // description
            $description = new ilTextInputGUI($this->lng->txt("description"), "comment");
            $description->setValue($this->object->getComment());
            $description->setRequired(false);
            $form->addItem($description);
        } else {
            // author as hidden field
            $hi = new ilHiddenInputGUI("author");
            $author = ilUtil::prepareFormOutput($this->object->getAuthor());
            if (trim($author) == "") {
                $author = "-";
            }
            $hi->setValue($author);
            $form->addItem($hi);
        }

        // lifecycle
        $lifecycle = new ilSelectInputGUI($this->lng->txt('qst_lifecycle'), 'lifecycle');
        $lifecycle->setOptions($this->object->getLifecycle()->getSelectOptions($this->lng));
        $lifecycle->setValue($this->object->getLifecycle()->getIdentifier());
        $form->addItem($lifecycle);

        // questiontext
        $question = new ilTextAreaInputGUI($this->lng->txt("question"), "question");
        $question->setValue($this->object->getQuestion());
        $question->setRequired(true);
        $question->setRows(10);
        $question->setCols(80);
        if (!$this->object->getSelfAssessmentEditingMode()) {
            if ($this->object->getAdditionalContentEditingMode() == assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE) {
                $question->setUseRte(true);
                include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
                $question->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
                $question->addPlugin("latex");
                $question->addButton("latex");
                $question->addButton("pastelatex");
                $question->setRTESupport($this->object->getId(), "qpl", "assessment");
            }
        } else {
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssSelfAssessmentQuestionFormatter.php';
            $question->setRteTags(ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
            $question->setUseTagsForRteOnly(false);
        }
        $form->addItem($question);

        if (!$this->object->getSelfAssessmentEditingMode()) {
            // duration
            $duration = new ilDurationInputGUI($this->lng->txt("working_time"), "Estimated");
            $duration->setShowHours(true);
            $duration->setShowMinutes(true);
            $duration->setShowSeconds(true);
            $ewt = $this->object->getEstimatedWorkingTime();
            $duration->setHours($ewt["h"]);
            $duration->setMinutes($ewt["m"]);
            $duration->setSeconds($ewt["s"]);
            $duration->setRequired(false);
            $form->addItem($duration);
        } else {
            // number of tries
            if (strlen($this->object->getNrOfTries())) {
                $nr_tries = $this->object->getNrOfTries();
            } else {
                $nr_tries = $this->object->getDefaultNrOfTries();
            }
            /*if ($nr_tries <= 0)
            {
                $nr_tries = 1;
            }*/

            if ($nr_tries < 0) {
                $nr_tries = 0;
            }

            $ni = new ilNumberInputGUI($this->lng->txt("qst_nr_of_tries"), "nr_of_tries");
            $ni->setValue($nr_tries);
            //$ni->setMinValue(1);
            $ni->setMinValue(0);
            $ni->setSize(5);
            $ni->setMaxLength(5);
            $ni->setRequired(true);
            $form->addItem($ni);
        }
    }

    public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form)
    {
        // cloze text
        $cloze_text = new ilTextAreaInputGUI($this->lng->txt("cloze_text"), 'cloze_text');
        $cloze_text->setRequired(true);
        $cloze_text->setValue($this->applyIndizesToGapText($this->object->getClozeText()));
        $cloze_text->setInfo($this->lng->txt("close_text_hint"));
        $cloze_text->setRows(10);
        $cloze_text->setCols(80);
        if (!$this->object->getSelfAssessmentEditingMode()) {
            if ($this->object->getAdditionalContentEditingMode() == assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_RTE) {
                $cloze_text->setUseRte(true);
                include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
                $cloze_text->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
                $cloze_text->addPlugin("latex");
                $cloze_text->addButton("latex");
                $cloze_text->addButton("pastelatex");
            }
        } else {
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssSelfAssessmentQuestionFormatter.php';
            $cloze_text->setRteTags(ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
            $cloze_text->setUseTagsForRteOnly(false);
        }
        $cloze_text->setRTESupport($this->object->getId(), "qpl", "assessment");
        $form->addItem($cloze_text);

        $tpl = new ilTemplate("tpl.il_as_qpl_cloze_gap_button_code.html", true, true, "Modules/TestQuestionPool");

        $button = new ilCustomInputGUI('&nbsp;', '');
        require_once 'Services/UIComponent/SplitButton/classes/class.ilSplitButtonGUI.php';
        require_once 'Services/UIComponent/Button/classes/class.ilJsLinkButton.php';
        $action_button = ilSplitButtonGUI::getInstance();

        $sb_text_gap = ilJsLinkButton::getInstance();
        $sb_text_gap->setCaption('text_gap');
        $sb_text_gap->setName('gapbutton');
        $sb_text_gap->setId('gaptrigger_text');
        $action_button->setDefaultButton($sb_text_gap);

        $sb_sel_gap = ilJsLinkButton::getInstance();
        $sb_sel_gap->setCaption('select_gap');
        $sb_sel_gap->setName('gapbutton_select');
        $sb_sel_gap->setId('gaptrigger_select');
        $action_button->addMenuItem(new ilButtonToSplitButtonMenuItemAdapter($sb_sel_gap));

        $sb_num_gap = ilJsLinkButton::getInstance();
        $sb_num_gap->setCaption('numeric_gap');
        $sb_num_gap->setName('gapbutton_numeric');
        $sb_num_gap->setId('gaptrigger_numeric');
        $action_button->addMenuItem(new ilButtonToSplitButtonMenuItemAdapter($sb_num_gap));

        $tpl->setVariable('BUTTON', $action_button->render());
        $tpl->parseCurrentBlock();

        $button->setHtml($tpl->get());
        $form->addItem($button);

        // text rating
        if (!$this->object->getSelfAssessmentEditingMode()) {
            $textrating = new ilSelectInputGUI($this->lng->txt("text_rating"), "textgap_rating");
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
            $textrating->setValue($this->object->getTextgapRating());
            $form->addItem($textrating);

            // text field length
            $fixedTextLength = new ilNumberInputGUI($this->lng->txt("cloze_fixed_textlength"), "fixedTextLength");
            $ftl = $this->object->getFixedTextLength();

            $fixedTextLength->setValue($ftl > 0 ? $ftl : '');
            $fixedTextLength->setMinValue(0);
            $fixedTextLength->setSize(3);
            $fixedTextLength->setMaxLength(6);
            $fixedTextLength->setInfo($this->lng->txt('cloze_fixed_textlength_description'));
            $fixedTextLength->setRequired(false);
            $form->addItem($fixedTextLength);

            // identical scoring
            $identical_scoring = new ilCheckboxInputGUI($this->lng->txt("identical_scoring"), "identical_scoring");
            $identical_scoring->setValue(1);
            $identical_scoring->setChecked($this->object->getIdenticalScoring());
            $identical_scoring->setInfo($this->lng->txt('identical_scoring_desc'));
            $identical_scoring->setRequired(false);
            $form->addItem($identical_scoring);
        }
        return $form;
    }

    public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form)
    {
        if (self::OLD_CLOZE_TEST_UI) {
            for ($gapCounter = 0; $gapCounter < $this->object->getGapCount(); $gapCounter++) {
                $this->populateGapFormPart($form, $gapCounter);
            }
            return $form;
        } else {
            require_once 'Modules/TestQuestionPool/classes/Form/class.ilClozeGapInputBuilderGUI.php';
            $json = $this->populateJSON();
            $assClozeGapCombinationObject = new assClozeGapCombination();
            $combination_exists = $assClozeGapCombinationObject->combinationExistsForQid($this->object->id);
            if ($combination_exists) {
                $combinations = $assClozeGapCombinationObject->loadFromDb($this->object->id);
            }
            $new_builder = new ilClozeGapInputBuilderGUI();
            $header = new ilFormSectionHeaderGUI();
            $form->addItem($header);
            $new_builder->setValueByArray($json);
            $new_builder->setValueCombinationFromDb($combinations);
            $form->addItem($new_builder);
            return $form;
        }
    }

    protected function populateJSON()
    {
        $gap = $this->object->getGaps();
        $array = array();
        if ($gap == null) {
            return $array;
        }
        $translate_type = array('text','select','numeric');
        $i = 0;
        foreach ($gap as $content) {
            $shuffle = false;
            $value = $content->getItemsRaw();
            $items = array();
            for ($j = 0;$j < count($value);$j++) {
                if ($content->isNumericGap()) {
                    $items[$j] = array(
                        'answer' => $value[$j]->getAnswerText(),
                        'lower' => $value[$j]->getLowerBound(),
                        'upper' => $value[$j]->getUpperBound(),
                        'points' => $value[$j]->getPoints(),
                        'error' => false
                    );
                } else {
                    $items[$j] = array(
                        'answer' => $this->escapeTemplatePlaceholders($value[$j]->getAnswerText()),
                        'points' => $value[$j]->getPoints(),
                        'error' => false
                    );

                    if ($content->isSelectGap()) {
                        $shuffle = $content->getShuffle();
                    }
                }
            }
            $answers[$i] = array(
            'type' => $translate_type[$content->getType()] ,
            'values' => $items ,
            'shuffle' => $shuffle,
            'text_field_length' => $content->getGapSize() > 0 ? $content->getGapSize() :  '',
            'used_in_gap_combination' => true);
            $i++;
        }
        return $answers;
    }
    /**
     * Populates a gap form-part.
     *
     * This includes: A section header with the according gap-ordinal, the type select-box.
     * Furthermore, this method calls the gap-type-specific methods for their contents.
     *
     * @param $form	 		ilPropertyFormGUI	Reference to the form, that receives the point.
     * @param $gapCounter	integer				Ordinal number of the gap in the sequence of gaps
     *
     * @return ilPropertyFormGUI
     */
    protected function populateGapFormPart($form, $gapCounter)
    {
        $gap = $this->object->getGap($gapCounter);

        if ($gap == null) {
            return $form;
        }

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("gap") . " " . ($gapCounter + 1));
        $form->addItem($header);

        $gapcounter = new ilHiddenInputGUI("gap[$gapCounter]");
        $gapcounter->setValue($gapCounter);
        $form->addItem($gapcounter);

        $gaptype = new ilSelectInputGUI($this->lng->txt('type'), "clozetype_$gapCounter");
        $options = array(
            0 => $this->lng->txt("text_gap"),
            1 => $this->lng->txt("select_gap"),
            2 => $this->lng->txt("numeric_gap")
        );
        $gaptype->setOptions($options);
        $gaptype->setValue($gap->getType());
        $form->addItem($gaptype);

        if ($gap->getType() == CLOZE_TEXT) {
            $this->populateGapSizeFormPart($form, $gap, $gapCounter);

            if (count($gap->getItemsRaw()) == 0) {
                $gap->addItem(new assAnswerCloze("", 0, 0));
            }
            $this->populateTextGapFormPart($form, $gap, $gapCounter);
        } elseif ($gap->getType() == CLOZE_SELECT) {
            if (count($gap->getItemsRaw()) == 0) {
                $gap->addItem(new assAnswerCloze("", 0, 0));
            }
            $this->populateSelectGapFormPart($form, $gap, $gapCounter);
        } elseif ($gap->getType() == CLOZE_NUMERIC) {
            $this->populateGapSizeFormPart($form, $gap, $gapCounter);

            if (count($gap->getItemsRaw()) == 0) {
                $gap->addItem(new assAnswerCloze("", 0, 0));
            }
            foreach ($gap->getItemsRaw() as $item) {
                $this->populateNumericGapFormPart($form, $item, $gapCounter);
            }
        }
        return $form;
    }

    /**
     * @param $form			ilPropertyFormGUI	Reference to the form, that receives the point.
     * @param $gap			mixed				Raw text gap item.
     * @param $gapCounter	integer				Ordinal number of the gap in the sequence of gaps
     */
    protected function populateGapSizeFormPart($form, $gap, $gapCounter)
    {
        $gapSizeFormItem = new ilNumberInputGUI($this->lng->txt('cloze_fixed_textlength'), "gap_" . $gapCounter . '_gapsize');

        $gapSizeFormItem->allowDecimals(false);
        $gapSizeFormItem->setMinValue(0);
        $gapSizeFormItem->setSize(3);
        $gapSizeFormItem->setMaxLength(6);
        $gapSizeFormItem->setInfo($this->lng->txt('cloze_gap_size_info'));
        $gapSizeFormItem->setValue($gap->getGapSize());
        $form->addItem($gapSizeFormItem);

        return $form;
    }

    /**
     * Populates the form-part for a select gap.
     *
     * This includes: The AnswerWizardGUI for the individual select items and points as well as
     * the the checkbox for the shuffle option.
     *
     * @param $form			ilPropertyFormGUI	Reference to the form, that receives the point.
     * @param $gap			mixed				Raw text gap item.
     * @param $gapCounter	integer				Ordinal number of the gap in the sequence of gaps
     *
     * @return ilPropertyFormGUI
     */
    protected function populateSelectGapFormPart($form, $gap, $gapCounter)
    {
        include_once "./Modules/TestQuestionPool/classes/class.ilAnswerWizardInputGUI.php";
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
        $values = new ilAnswerWizardInputGUI($this->lng->txt("values"), "gap_" . $gapCounter . "");
        $values->setRequired(true);
        $values->setQuestionObject($this->object);
        $values->setSingleline(true);
        $values->setAllowMove(false);

        $values->setValues($gap->getItemsRaw());
        $form->addItem($values);

        // shuffle
        $shuffle = new ilCheckboxInputGUI($this->lng->txt("shuffle_answers"), "shuffle_" . $gapCounter . "");
        $shuffle->setValue(1);
        $shuffle->setChecked($gap->getShuffle());
        $shuffle->setRequired(false);
        $form->addItem($shuffle);
        return $form;
    }

    /**
     * Populates the form-part for a text gap.
     *
     * This includes: The AnswerWizardGUI for the individual text answers and points.
     *
     * @param $form			ilPropertyFormGUI	Reference to the form, that receives the point.
     * @param $gap			mixed				Raw text gap item.
     * @param $gapCounter	integer				Ordinal number of the gap in the sequence of gaps
     *
     * @return ilPropertyFormGUI
     */
    protected function populateTextGapFormPart($form, $gap, $gapCounter)
    {
        // Choices
        include_once "./Modules/TestQuestionPool/classes/class.ilAnswerWizardInputGUI.php";
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
        $values = new ilAnswerWizardInputGUI($this->lng->txt("values"), "gap_" . $gapCounter . "");
        $values->setRequired(true);
        $values->setQuestionObject($this->object);
        $values->setSingleline(true);
        $values->setAllowMove(false);
        $values->setValues($gap->getItemsRaw());
        $form->addItem($values);

        if ($this->object->getFixedTextLength() > 0) {
            $values->setSize($this->object->getFixedTextLength());
            $values->setMaxLength($this->object->getFixedTextLength());
        }

        return $form;
    }

    /**
     * Populates the form-part for a numeric gap.
     *
     * This includes: The type selector, value, lower bound, upper bound and points.
     *
     * @param $form			ilPropertyFormGUI	Reference to the form, that receives the point.
     * @param $gap			mixed				Raw numeric gap item.
     * @param $gapCounter	integer				Ordinal number of the gap in the sequence of gaps.
     *
     * @return ilPropertyFormGUI
     */
    protected function populateNumericGapFormPart($form, $gap, $gapCounter)
    {
        // #8944: the js-based ouput in self-assessment cannot support formulas
        if (!$this->object->getSelfAssessmentEditingMode()) {
            $value = new ilFormulaInputGUI($this->lng->txt('value'), "gap_" . $gapCounter . "_numeric");
            $value->setInlineStyle('text-align: right;');

            $lowerbound = new ilFormulaInputGUI($this->lng->txt('range_lower_limit'), "gap_" . $gapCounter . "_numeric_lower");
            $lowerbound->setInlineStyle('text-align: right;');

            $upperbound = new ilFormulaInputGUI($this->lng->txt('range_upper_limit'), "gap_" . $gapCounter . "_numeric_upper");
            $upperbound->setInlineStyle('text-align: right;');
        } else {
            $value = new ilNumberInputGUI($this->lng->txt('value'), "gap_" . $gapCounter . "_numeric");
            $value->allowDecimals(true);

            $lowerbound = new ilNumberInputGUI($this->lng->txt('range_lower_limit'), "gap_" . $gapCounter . "_numeric_lower");
            $lowerbound->allowDecimals(true);

            $upperbound = new ilNumberInputGUI($this->lng->txt('range_upper_limit'), "gap_" . $gapCounter . "_numeric_upper");
            $upperbound->allowDecimals(true);
        }

        $value->setSize(10);
        $value->setValue(ilUtil::prepareFormOutput($gap->getAnswertext()));
        $value->setRequired(true);
        $form->addItem($value);

        $lowerbound->setSize(10);
        $lowerbound->setRequired(true);
        $lowerbound->setValue(ilUtil::prepareFormOutput($gap->getLowerBound()));
        $form->addItem($lowerbound);

        $upperbound->setSize(10);
        $upperbound->setRequired(true);
        $upperbound->setValue(ilUtil::prepareFormOutput($gap->getUpperBound()));
        $form->addItem($upperbound);

        if ($this->object->getFixedTextLength() > 0) {
            $value->setSize($this->object->getFixedTextLength());
            $value->setMaxLength($this->object->getFixedTextLength());
            $lowerbound->setSize($this->object->getFixedTextLength());
            $lowerbound->setMaxLength($this->object->getFixedTextLength());
            $upperbound->setSize($this->object->getFixedTextLength());
            $upperbound->setMaxLength($this->object->getFixedTextLength());
        }

        $points = new ilNumberInputGUI($this->lng->txt('points'), "gap_" . $gapCounter . "_numeric_points");
        $points->allowDecimals(true);
        $points->setSize(3);
        $points->setRequired(true);
        $points->setValue(ilUtil::prepareFormOutput($gap->getPoints()));
        $form->addItem($points);
        return $form;
    }

    /**
    * Create gaps from cloze text
    */
    public function createGaps()
    {
        $this->writePostData(true);
        $this->object->saveToDb();
        $this->editQuestion();
    }

    /**
    * Remove a gap answer
    */
    public function removegap()
    {
        $this->writePostData(true);
        $this->object->deleteAnswerText($this->gapIndex, key($_POST['cmd']['removegap_' . $this->gapIndex]));
        $this->editQuestion();
    }

    /**
    * Add a gap answer
    */
    public function addgap()
    {
        $this->writePostData(true);
        $this->object->addGapAnswer($this->gapIndex, key($_POST['cmd']['addgap_' . $this->gapIndex]) + 1, "");
        $this->editQuestion();
    }

    /**
     * Creates a preview output of the question
     *
     * @param bool $show_question_only
     *
     * @return string HTML code which contains the preview output of the question
     * @access public
     */
    public function getPreview($show_question_only = false, $showInlineFeedback = false)
    {
        $user_solution = is_object($this->getPreviewSession()) ? (array) $this->getPreviewSession()->getParticipantsSolution() : array();

        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_cloze_question_output.html", true, true, "Modules/TestQuestionPool");
        $output = $this->object->getClozeTextForHTMLOutput();
        foreach ($this->object->getGaps() as $gap_index => $gap) {
            switch ($gap->getType()) {
                case CLOZE_TEXT:
                    $gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_text.html", true, true, "Modules/TestQuestionPool");

                    $gap_size = $gap->getGapSize() > 0 ? $gap->getGapSize() : $this->object->getFixedTextLength();
                    if ($gap_size > 0) {
                        $gaptemplate->setCurrentBlock('size_and_maxlength');
                        $gaptemplate->setVariable("TEXT_GAP_SIZE", $gap_size);
                        $gaptemplate->parseCurrentBlock();
                    }
                    $gaptemplate->setVariable("GAP_COUNTER", $gap_index);
                    foreach ($user_solution as $val1 => $val2) {
                        if (strcmp($val1, $gap_index) == 0) {
                            $gaptemplate->setVariable("VALUE_GAP", " value=\"" . ilUtil::prepareFormOutput($val2) . "\"");
                        }
                    }
                    // fau: fixGapReplace - use replace function
                    $output = $this->object->replaceFirstGap($output, $gaptemplate->get());
// fau.
                    break;
                case CLOZE_SELECT:
                    $gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_select.html", true, true, "Modules/TestQuestionPool");
                    foreach ($gap->getItems($this->object->getShuffler(), $gap_index) as $item) {
                        $gaptemplate->setCurrentBlock("select_gap_option");
                        $gaptemplate->setVariable("SELECT_GAP_VALUE", $item->getOrder());
                        $gaptemplate->setVariable("SELECT_GAP_TEXT", ilUtil::prepareFormOutput($item->getAnswerText()));
                        foreach ($user_solution as $val1 => $val2) {
                            if (strcmp($val1, $gap_index) == 0) {
                                if (strcmp($val2, $item->getOrder()) == 0) {
                                    $gaptemplate->setVariable("SELECT_GAP_SELECTED", " selected=\"selected\"");
                                }
                            }
                        }
                        $gaptemplate->parseCurrentBlock();
                    }
                    $gaptemplate->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
                    $gaptemplate->setVariable("GAP_COUNTER", $gap_index);// fau: fixGapReplace - use replace function
                    $output = $this->object->replaceFirstGap($output, $gaptemplate->get());
// fau.
                    break;
                case CLOZE_NUMERIC:
                    $gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_numeric.html", true, true, "Modules/TestQuestionPool");
                    $gap_size = $gap->getGapSize() > 0 ? $gap->getGapSize() : $this->object->getFixedTextLength();
                    if ($gap_size > 0) {
                        $gaptemplate->setCurrentBlock('size_and_maxlength');
                        $gaptemplate->setVariable("TEXT_GAP_SIZE", $gap_size);
                        $gaptemplate->parseCurrentBlock();
                    }
                    $gaptemplate->setVariable("GAP_COUNTER", $gap_index);
                    foreach ($user_solution as $val1 => $val2) {
                        if (strcmp($val1, $gap_index) == 0) {
                            $gaptemplate->setVariable("VALUE_GAP", " value=\"" . ilUtil::prepareFormOutput($val2) . "\"");
                        }
                    }
                    // fau: fixGapReplace - use replace function
                    $output = $this->object->replaceFirstGap($output, $gaptemplate->get());
// fau.
                    break;
            }
        }
        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $template->setVariable("CLOZETEXT", $this->object->prepareTextareaOutput($output, true));
        $questionoutput = $template->get();
        if (!$show_question_only) {
            // get page object output
            $questionoutput = $this->getILIASPage($questionoutput);
        }
        return $questionoutput;
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
        // get the solution of the user for the active pass or from the last pass if allowed
        $user_solution = array();
        if (($active_id > 0) && (!$show_correct_solution)) {
            // get the solutions of a user
            $user_solution = $this->object->getSolutionValues($active_id, $pass, !$this->getUseIntermediateSolution());
            if (!is_array($user_solution)) {
                $user_solution = array();
            }
        }

        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_cloze_question_output_solution.html", true, true, "Modules/TestQuestionPool");
        $output = $this->object->getClozeTextForHTMLOutput();
        $assClozeGapCombinationObject = new assClozeGapCombination();
        $check_for_gap_combinations = $assClozeGapCombinationObject->loadFromDb($this->object->getId());

        foreach ($this->object->getGaps() as $gap_index => $gap) {
            $gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_output_solution_gap.html", true, true, "Modules/TestQuestionPool");
            $found = array();
            foreach ($user_solution as $solutionarray) {
                if ($solutionarray["value1"] == $gap_index) {
                    $found = $solutionarray;
                }
            }

            if ($active_id) {
                if ($graphicalOutput) {
                    // output of ok/not ok icons for user entered solutions
                    $details = $this->object->calculateReachedPoints($active_id, $pass, true, true);
                    $check = $details[$gap_index];

                    if (count($check_for_gap_combinations) != 0) {
                        $gaps_used_in_combination = $assClozeGapCombinationObject->getGapsWhichAreUsedInCombination($this->object->getId());
                        $custom_user_solution = array();
                        if (array_key_exists($gap_index, $gaps_used_in_combination)) {
                            $combination_id = $gaps_used_in_combination[$gap_index];
                            foreach ($gaps_used_in_combination as $key => $value) {
                                $a = 0;
                                if ($value == $combination_id) {
                                    foreach ($user_solution as $solution_key => $solution_value) {
                                        if ($solution_value['value1'] == $key) {
                                            $result_row = array();
                                            $result_row['gap_id'] = $solution_value['value1'];
                                            $result_row['value'] = $solution_value['value2'];
                                            array_push($custom_user_solution, $result_row);
                                        }
                                    }
                                }
                            }
                            $points_array = $this->object->calculateCombinationResult($custom_user_solution);
                            $max_combination_points = $assClozeGapCombinationObject->getMaxPointsForCombination($this->object->getId(), $combination_id);
                            if ($points_array[0] == $max_combination_points) {
                                $gaptemplate->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
                                $gaptemplate->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
                            } elseif ($points_array[0] > 0) {
                                $gaptemplate->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.svg"));
                                $gaptemplate->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
                            } else {
                                $gaptemplate->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
                                $gaptemplate->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
                            }
                        } else {
                            if ($check["best"]) {
                                $gaptemplate->setCurrentBlock("icon_ok");
                                $gaptemplate->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
                                $gaptemplate->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
                                $gaptemplate->parseCurrentBlock();
                            } else {
                                $gaptemplate->setCurrentBlock("icon_not_ok");
                                if ($check["positive"]) {
                                    $gaptemplate->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.svg"));
                                    $gaptemplate->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
                                } else {
                                    $gaptemplate->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
                                    $gaptemplate->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
                                }
                                $gaptemplate->parseCurrentBlock();
                            }
                        }
                    } else {
                        if ($check["best"]) {
                            $gaptemplate->setCurrentBlock("icon_ok");
                            $gaptemplate->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
                            $gaptemplate->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
                            $gaptemplate->parseCurrentBlock();
                        } else {
                            $gaptemplate->setCurrentBlock("icon_not_ok");
                            if ($check["positive"]) {
                                $gaptemplate->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.svg"));
                                $gaptemplate->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
                            } else {
                                $gaptemplate->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
                                $gaptemplate->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
                            }
                            $gaptemplate->parseCurrentBlock();
                        }
                    }
                }
            }
            $combination = null;
            switch ($gap->getType()) {
                case CLOZE_TEXT:
                    $solutiontext = "";
                    if (($active_id > 0) && (!$show_correct_solution)) {
                        if ((count($found) == 0) || (strlen(trim($found["value2"])) == 0)) {
                            for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++) {
                                $solutiontext .= "&nbsp;";
                            }
                        } else {
                            $solutiontext = ilUtil::prepareFormOutput($found["value2"]);
                        }
                    } else {
                        $solutiontext = $this-> getBestSolutionText($gap, $gap_index, $check_for_gap_combinations);
                    }
                    $this->populateSolutiontextToGapTpl($gaptemplate, $gap, $solutiontext);
                    // fau: fixGapReplace - use replace function
                    $output = $this->object->replaceFirstGap($output, $gaptemplate->get());
// fau.
                    break;
                case CLOZE_SELECT:
                    $solutiontext = "";
                    if (($active_id > 0) && (!$show_correct_solution)) {
                        if ((count($found) == 0) || (strlen(trim($found["value2"])) == 0)) {
                            for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++) {
                                $solutiontext .= "&nbsp;";
                            }
                        } else {
                            $item = $gap->getItem($found["value2"]);
                            if (is_object($item)) {
                                $solutiontext = ilUtil::prepareFormOutput($item->getAnswertext());
                            } else {
                                for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++) {
                                    $solutiontext .= "&nbsp;";
                                }
                            }
                        }
                    } else {
                        $solutiontext = $this-> getBestSolutionText($gap, $gap_index, $check_for_gap_combinations);
                    }
                    $this->populateSolutiontextToGapTpl($gaptemplate, $gap, $solutiontext);
                    // fau: fixGapReplace - use replace function
                    $output = $this->object->replaceFirstGap($output, $gaptemplate->get());
// fau.
                    break;
                case CLOZE_NUMERIC:
                    $solutiontext = "";
                    if (($active_id > 0) && (!$show_correct_solution)) {
                        if ((count($found) == 0) || (strlen(trim($found["value2"])) == 0)) {
                            for ($chars = 0; $chars < $gap->getMaxWidth(); $chars++) {
                                $solutiontext .= "&nbsp;";
                            }
                        } else {
                            $solutiontext = ilUtil::prepareFormOutput($found["value2"]);
                        }
                    } else {
                        $solutiontext = $this-> getBestSolutionText($gap, $gap_index, $check_for_gap_combinations);
                    }
                    $this->populateSolutiontextToGapTpl($gaptemplate, $gap, $solutiontext);
                    // fau: fixGapReplace - use replace function
                    $output = $this->object->replaceFirstGap($output, $gaptemplate->get());
// fau.
                    break;
            }
        }

        if ($show_question_text) {
            $template->setVariable(
                "QUESTIONTEXT",
                $this->object->getQuestionForHTMLOutput()
            );
        }

        $template->setVariable("CLOZETEXT", $this->object->prepareTextareaOutput($output, true));
        // generate the question output
        $solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", true, true, "Modules/TestQuestionPool");
        $questionoutput = $template->get();

        $feedback = '';
        if ($show_feedback) {
            if (!$this->isTestPresentationContext()) {
                $fb = $this->getGenericFeedbackOutput($active_id, $pass);
                $feedback .= strlen($fb) ? $fb : '';
            }

            $fb = $this->getSpecificFeedbackOutput(
                $this->object->fetchIndexedValuesFromValuePairs($user_solution)
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
     * @param assClozeGap $gap
     * @param $gap_index
     * @param $gap_combinations
     * @return string
     */
    protected function getBestSolutionText($gap, $gap_index, $gap_combinations)
    {
        $combination = null;
        foreach ((array) $gap_combinations as $combiGapSolRow) {
            if ($combiGapSolRow['gap_fi'] == $gap_index && $combiGapSolRow['best_solution']) {
                $combination = $combiGapSolRow;
                break;
            }
        }
        $best_solution_text = ilUtil::prepareFormOutput($gap->getBestSolutionOutput(
            $this->object->getShuffler(),
            $combination
        ));
        return $best_solution_text;
    }

    public function getAnswerFeedbackOutput($active_id, $pass)
    {
        include_once "./Modules/Test/classes/class.ilObjTest.php";
        $manual_feedback = ilObjTest::getManualFeedback($active_id, $this->object->getId(), $pass);
        if (strlen($manual_feedback)) {
            return $manual_feedback;
        }
        $correct_feedback = $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), true);
        $incorrect_feedback = $this->object->feedbackOBJ->getGenericFeedbackTestPresentation($this->object->getId(), false);
        if (strlen($correct_feedback . $incorrect_feedback)) {
            $reached_points = $this->object->calculateReachedPoints($active_id, $pass);
            $max_points = $this->object->getMaximumPoints();
            if ($reached_points == $max_points) {
                $output .= $correct_feedback;
            } else {
                $output .= $incorrect_feedback;
            }
        }
        $test = new ilObjTest($this->object->active_id);
        return $this->object->prepareTextareaOutput($output, true);
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
        // get the solution of the user for the active pass or from the last pass if allowed
        $user_solution = array();
        if ($use_post_solutions !== false) {
            $indexedSolution = $this->object->fetchSolutionSubmit($use_post_solutions);
            $user_solution = $this->object->fetchValuePairsFromIndexedValues($indexedSolution);
        } elseif ($active_id) {
            // hey: prevPassSolutions - obsolete due to central check
            #include_once "./Modules/Test/classes/class.ilObjTest.php";
            #if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
            #{
            #	if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
            #}
            $user_solution = $this->object->getTestOutputSolutions($active_id, $pass);
            // hey.
            if (!is_array($user_solution)) {
                $user_solution = array();
            }
        }

        // generate the question output
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.il_as_qpl_cloze_question_output.html", true, true, "Modules/TestQuestionPool");
        $output = $this->object->getClozeTextForHTMLOutput();
        foreach ($this->object->getGaps() as $gap_index => $gap) {
            switch ($gap->getType()) {
                case CLOZE_TEXT:
                    $gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_text.html", true, true, "Modules/TestQuestionPool");
                    $gap_size = $gap->getGapSize() > 0 ? $gap->getGapSize() : $this->object->getFixedTextLength();

                    if ($gap_size > 0) {
                        $gaptemplate->setCurrentBlock('size_and_maxlength');
                        $gaptemplate->setVariable("TEXT_GAP_SIZE", $gap_size);
                        $gaptemplate->parseCurrentBlock();
                    }

                    $gaptemplate->setVariable("GAP_COUNTER", $gap_index);
                    foreach ($user_solution as $solution) {
                        if (strcmp($solution["value1"], $gap_index) == 0) {
                            $gaptemplate->setVariable("VALUE_GAP", " value=\"" . ilUtil::prepareFormOutput($solution["value2"]) . "\"");
                        }
                    }
                    // fau: fixGapReplace - use replace function
                    $output = $this->object->replaceFirstGap($output, $gaptemplate->get());
// fau.
                    break;
                case CLOZE_SELECT:
                    $gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_select.html", true, true, "Modules/TestQuestionPool");
                    foreach ($gap->getItems($this->object->getShuffler(), $gap_index) as $item) {
                        $gaptemplate->setCurrentBlock("select_gap_option");
                        $gaptemplate->setVariable("SELECT_GAP_VALUE", $item->getOrder());
                        $gaptemplate->setVariable("SELECT_GAP_TEXT", ilUtil::prepareFormOutput($item->getAnswerText()));
                        foreach ($user_solution as $solution) {
                            if (strcmp($solution["value1"], $gap_index) == 0) {
                                if (strcmp($solution["value2"], $item->getOrder()) == 0) {
                                    $gaptemplate->setVariable("SELECT_GAP_SELECTED", " selected=\"selected\"");
                                }
                            }
                        }
                        $gaptemplate->parseCurrentBlock();
                    }
                    $gaptemplate->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
                    $gaptemplate->setVariable("GAP_COUNTER", $gap_index);// fau: fixGapReplace - use replace function
                    $output = $this->object->replaceFirstGap($output, $gaptemplate->get());
// fau.
                    break;
                case CLOZE_NUMERIC:
                    $gaptemplate = new ilTemplate("tpl.il_as_qpl_cloze_question_gap_numeric.html", true, true, "Modules/TestQuestionPool");
                    $gap_size = $gap->getGapSize() > 0 ? $gap->getGapSize() : $this->object->getFixedTextLength();
                    if ($gap_size > 0) {
                        $gaptemplate->setCurrentBlock('size_and_maxlength');
                        $gaptemplate->setVariable("TEXT_GAP_SIZE", $gap_size);
                        $gaptemplate->parseCurrentBlock();
                    }

                    $gaptemplate->setVariable("GAP_COUNTER", $gap_index);
                    foreach ($user_solution as $solution) {
                        if (strcmp($solution["value1"], $gap_index) == 0) {
                            $gaptemplate->setVariable("VALUE_GAP", " value=\"" . ilUtil::prepareFormOutput($solution["value2"]) . "\"");
                        }
                    }
                    // fau: fixGapReplace - use replace function
                    $output = $this->object->replaceFirstGap($output, $gaptemplate->get());
// fau.
                    break;
            }
        }

        $template->setVariable("QUESTIONTEXT", $this->object->getQuestionForHTMLOutput());
        $template->setVariable("CLOZETEXT", $this->object->prepareTextareaOutput($output, true));
        $questionoutput = $template->get();
        $pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
        return $pageoutput;
    }

    public function getSpecificFeedbackOutput($userSolution)
    {
        if (!$this->object->feedbackOBJ->specificAnswerFeedbackExists()) {
            return '';
        }

        global $DIC;
        $lng = $DIC['lng'];

        $feedback = '<table class="test_specific_feedback"><tbody>';

        foreach ($this->object->gaps as $gapIndex => $gap) {
            $answerValue = $this->object->fetchAnswerValueForGap($userSolution, $gapIndex);
            $answerIndex = $this->object->feedbackOBJ->determineAnswerIndexForAnswerValue($gap, $answerValue);
            $fb = $this->object->feedbackOBJ->determineTestOutputGapFeedback($gapIndex, $answerIndex);

            $caption = $lng->txt('gap') . ' ' . ($gapIndex + 1) . ': ';
            $feedback .= '<tr><td>';
            $feedback .= $caption . '</td><td>';
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

        $html = '<div>';
        $i = 0;
        foreach ($this->object->getGaps() as $gap_index => $gap) {
            if ($gap->type == CLOZE_SELECT) {
                $html .= '<p>Gap ' . ($i + 1) . ' - SELECT</p>';
                $html .= '<ul>';
                $j = 0;
                foreach ($gap->getItems($this->object->getShuffler(), $gap_index) as $gap_item) {
                    $aggregate = $aggregation[$i];
                    $html .= '<li>' . $gap_item->getAnswerText() . ' - ' . ($aggregate[$j] ? $aggregate[$j] : 0) . '</li>';
                    $j++;
                }
                $html .= '</ul>';
            }

            if ($gap->type == CLOZE_TEXT) {
                $present_elements = array();
                foreach ($gap->getItems(new ilArrayElementShuffler()) as $item) {
                    /** @var assAnswerCloze $item */
                    $present_elements[] = $item->getAnswertext();
                }

                $html .= '<p>Gap ' . ($i + 1) . ' - TEXT</p>';
                $html .= '<ul>';
                $aggregate = (array) $aggregation[$i];
                foreach ($aggregate as $answer => $count) {
                    $show_mover = '';
                    if (in_array($answer, $present_elements)) {
                        $show_mover = ' style="display: none;" ';
                    }

                    $html .= '<li>' . $answer . ' - ' . $count
                        . '&nbsp;<button class="clone_fields_add btn btn-link" ' . $show_mover . ' data-answer="' . $answer . '" name="add_gap_' . $i . '_0">
						<span class="sr-only"></span><span class="glyphicon glyphicon-plus"></span></button>
						</li>';
                }
                $html .= '</ul>';
            }

            if ($gap->type == CLOZE_NUMERIC) {
                $html .= '<p>Gap ' . ($i + 1) . ' - NUMERIC</p>';
                $html .= '<ul>';
                $j = 0;
                foreach ($gap->getItems($this->object->getShuffler(), $gap_index) as $gap_item) {
                    $aggregate = (array) $aggregation[$i];
                    foreach ($aggregate as $answer => $count) {
                        $html .= '<li>' . $answer . ' - ' . $count . '</li>';
                    }
                    $j++;
                }
                $html .= '</ul>';
            }
            $i++;
            $html .= '<hr />';
        }

        $html .= '</div>';
        return $html;
    }

    public function applyIndizesToGapText($question_text)
    {
        $parts = explode('[gap', $question_text);
        $i = 0;
        $question_text = '';
        foreach ($parts as $part) {
            if ($i == 0) {
                $question_text .= $part;
            } else {
                $question_text .= '[gap ' . $i . $part;
            }
            $i++;
        }
        return $question_text;
    }

    public function removeIndizesFromGapText($question_text)
    {
        $parts = preg_split('/\[gap \d*\]/', $question_text);
        $question_text = implode('[gap]', $parts);
        return $question_text;
    }

    /**
     * @param $gaptemplate
     * @param $solutiontext
     */
    private function populateSolutiontextToGapTpl($gaptemplate, $gap, $solutiontext)
    {
        if ($this->renderPurposeSupportsFormHtml() || $this->isRenderPurposePrintPdf()) {
            $gaptemplate->setCurrentBlock('gap_span');
            $gaptemplate->setVariable('SPAN_SOLUTION', $solutiontext);
        } elseif ($gap->getType() == CLOZE_SELECT) {
            $gaptemplate->setCurrentBlock('gap_select');
            $gaptemplate->setVariable('SELECT_SOLUTION', $solutiontext);
        } else {
            $gap_size = $gap->getGapSize() > 0 ? $gap->getGapSize() : $this->object->getFixedTextLength();

            if ($gap_size > 0) {
                $gaptemplate->setCurrentBlock('gap_size');
                $gaptemplate->setVariable("GAP_SIZE", $gap_size);
                $gaptemplate->parseCurrentBlock();
            }

            $gaptemplate->setCurrentBlock('gap_input');
            $gaptemplate->setVariable('INPUT_SOLUTION', $solutiontext);
        }


        $gaptemplate->parseCurrentBlock();
    }

    protected function hasAddAnswerAction($relevantAnswers, $questionIndex)
    {
        foreach ($this->getAnswersFrequency($relevantAnswers, $questionIndex) as $answer) {
            if (isset($answer['actions'])) {
                return true;
            }
        }

        return false;
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
            $DIC->language()->txt('gap') . ' ' . ($questionIndex + 1)
        ));

        if ($this->hasAddAnswerAction($relevantAnswers, $questionIndex)) {
            $table->addColumn('', '', '200');
        }

        return $table;
    }

    public function getSubQuestionsIndex()
    {
        return array_keys($this->object->getGaps());
    }

    protected function getAnswerTextLabel($gapIndex, $answer)
    {
        $gap = $this->object->getGap($gapIndex);

        switch ($gap->type) {
            case CLOZE_NUMERIC:
            case CLOZE_TEXT:
                return $answer;

            case CLOZE_SELECT:

                $items = $gap->getItems(new ilArrayElementOrderKeeper());
                return $items[$answer]->getAnswertext();
        }
    }

    protected function completeAddAnswerAction($answers, $questionIndex)
    {
        $gap = $this->object->getGap($questionIndex);

        if ($gap->type != CLOZE_TEXT) {
            return $answers;
        }

        foreach ($answers as $key => $ans) {
            $found = false;

            foreach ($gap->getItems(new ilArrayElementOrderKeeper()) as $item) {
                if ($ans['answer'] !== $item->getAnswerText()) {
                    continue;
                }

                $found = true;
                break;
            }

            if (!$found) {
                $answers[$key]['addable'] = true;
            }
        }

        return $answers;
    }

    public function getAnswersFrequency($relevant_answers, $questionIndex)
    {
        $answers = array();

        foreach ($relevant_answers as $row) {
            if ($row['value1'] != $questionIndex) {
                continue;
            }

            if (!isset($answers[$row['value2']])) {
                $label = $this->getAnswerTextLabel($row['value1'], $row['value2']);

                $answers[$row['value2']] = array(
                    'answer' => $label, 'frequency' => 0
                );
            }

            $answers[$row['value2']]['frequency']++;
        }

        $answers = $this->completeAddAnswerAction($answers, $questionIndex);

        return $answers;
    }

    protected function isUsedInCombinations($gapIndex)
    {
        foreach ($this->object->getGapCombinations() as $combination) {
            if ($combination['gap_fi'] != $gapIndex) {
                continue;
            }

            return true;
        }

        return false;
    }

    protected function getGapCombinations()
    {
        $combinations = array();

        foreach ($this->object->getGapCombinations() as $c) {
            if (!isset($combinations[$c['cid']])) {
                $combinations[$c['cid']] = array();
            }

            if (!isset($combinations[$c['cid']][$c['row_id']])) {
                $combinations[$c['cid']][$c['row_id']] = array(
                    'gaps' => array(), 'points' => $c['points'],
                );
            }

            if (!isset($combinations[$c['cid']][$c['row_id']]['gaps'][$c['gap_fi']])) {
                $combinations[$c['cid']][$c['row_id']]['gaps'][$c['gap_fi']] = array();
            }

            $combinations[$c['cid']][$c['row_id']]['gaps'][$c['gap_fi']] = $c['answer'];
        }

        return $combinations;
    }

    public function populateCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        foreach ($this->object->getGaps() as $gapIndex => $gap) {
            $this->populateGapCorrectionFormProperties(
                $form,
                $gap,
                $gapIndex,
                $this->isUsedInCombinations($gapIndex)
            );
        }

        if ($this->object->getGapCombinationsExists()) {
            foreach ($this->getGapCombinations() as $combiIndex => $gapCombination) {
                $this->populateGapCombinationCorrectionFormProperty($form, $gapCombination, $combiIndex);
            }
        }
    }

    protected function populateGapCombinationCorrectionFormProperty(ilPropertyFormGUI $form, $gapCombi, $combiIndex)
    {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle("Gap Combination " . ($combiIndex + 1));
        $form->addItem($header);

        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssClozeTestCombinationVariantsInputGUI.php';
        $inp = new ilAssClozeTestCombinationVariantsInputGUI('Answers', 'combination_' . $combiIndex);
        $inp->setValues($gapCombi);
        $form->addItem($inp);
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param assClozeGap $gap
     * @param integer $gapIndex
     */
    protected function populateGapCorrectionFormProperties($form, $gap, $gapIndex, $hidePoints)
    {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("gap") . " " . ($gapIndex + 1));
        $form->addItem($header);

        if ($gap->getType() == CLOZE_TEXT || $gap->getType() == CLOZE_SELECT) {
            $this->populateTextOrSelectGapCorrectionFormProperty($form, $gap, $gapIndex, $hidePoints);
        } elseif ($gap->getType() == CLOZE_NUMERIC) {
            foreach ($gap->getItemsRaw() as $item) {
                $this->populateNumericGapCorrectionFormProperty($form, $item, $gapIndex, $hidePoints);
            }
        }
    }

    protected function populateTextOrSelectGapCorrectionFormProperty($form, $gap, $gapIndex, $hidePoints)
    {
        require_once "Modules/TestQuestionPool/classes/forms/class.ilAssAnswerCorrectionsInputGUI.php";
        $values = new ilAssAnswerCorrectionsInputGUI($this->lng->txt("values"), "gap_" . $gapIndex);
        $values->setHidePointsEnabled($hidePoints);
        $values->setRequired(true);
        $values->setQuestionObject($this->object);
        $values->setValues($gap->getItemsRaw());
        $form->addItem($values);
    }

    protected function populateNumericGapCorrectionFormProperty($form, $item, $gapIndex, $hidePoints)
    {
        $value = new ilNumberInputGUI($this->lng->txt('value'), "gap_" . $gapIndex . "_numeric");
        $value->allowDecimals(true);
        $value->setSize(10);
        $value->setValue(ilUtil::prepareFormOutput($item->getAnswertext()));
        $value->setRequired(true);
        $form->addItem($value);

        $lowerbound = new ilNumberInputGUI($this->lng->txt('range_lower_limit'), "gap_" . $gapIndex . "_numeric_lower");
        $lowerbound->allowDecimals(true);
        $lowerbound->setSize(10);
        $lowerbound->setRequired(true);
        $lowerbound->setValue(ilUtil::prepareFormOutput($item->getLowerBound()));
        $form->addItem($lowerbound);

        $upperbound = new ilNumberInputGUI($this->lng->txt('range_upper_limit'), "gap_" . $gapIndex . "_numeric_upper");
        $upperbound->allowDecimals(true);
        $upperbound->setSize(10);
        $upperbound->setRequired(true);
        $upperbound->setValue(ilUtil::prepareFormOutput($item->getUpperBound()));
        $form->addItem($upperbound);

        if (!$hidePoints) {
            $points = new ilNumberInputGUI($this->lng->txt('points'), "gap_" . $gapIndex . "_numeric_points");
            $points->allowDecimals(true);
            $points->setSize(3);
            $points->setRequired(true);
            $points->setValue(ilUtil::prepareFormOutput($item->getPoints()));
            $form->addItem($points);
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    public function saveCorrectionsFormProperties(ilPropertyFormGUI $form)
    {
        foreach ($this->object->getGaps() as $gapIndex => $gap) {
            if ($this->isUsedInCombinations($gapIndex)) {
                continue;
            }

            $this->saveGapCorrectionFormProperty($form, $gap, $gapIndex);
        }

        if ($this->object->getGapCombinationsExists()) {
            $this->saveGapCombinationCorrectionFormProperties($form);
        }
    }

    protected function saveGapCorrectionFormProperty(ilPropertyFormGUI $form, assClozeGap $gap, $gapIndex)
    {
        if ($gap->getType() == CLOZE_TEXT || $gap->getType() == CLOZE_SELECT) {
            $this->saveTextOrSelectGapCorrectionFormProperty($form, $gap, $gapIndex);
        } elseif ($gap->getType() == CLOZE_NUMERIC) {
            foreach ($gap->getItemsRaw() as $item) {
                $this->saveNumericGapCorrectionFormProperty($form, $item, $gapIndex);
            }
        }
    }

    protected function saveTextOrSelectGapCorrectionFormProperty(ilPropertyFormGUI $form, assClozeGap $gap, $gapIndex)
    {
        $answers = $form->getItemByPostVar('gap_' . $gapIndex)->getValues();

        foreach ($gap->getItemsRaw() as $index => $item) {
            $item->setPoints((float) $answers[$index]->getPoints());
        }
    }

    protected function saveNumericGapCorrectionFormProperty(ilPropertyFormGUI $form, assAnswerCloze $item, $gapIndex)
    {
        $item->setAnswertext($form->getInput('gap_' . $gapIndex . '_numeric'));
        $item->setLowerBound($form->getInput('gap_' . $gapIndex . '_numeric_lower'));
        $item->setUpperBound($form->getInput('gap_' . $gapIndex . '_numeric_upper'));
        $item->setPoints($form->getInput('gap_' . $gapIndex . '_numeric_points'));
    }

    protected function saveGapCombinationCorrectionFormProperties(ilPropertyFormGUI $form)
    {
        // please dont ask (!) -.-

        $combinationPoints = array('points' => array(), 'select' => array());
        $combinationValues = array();

        foreach ($this->getGapCombinations() as $combiId => $combi) {
            $values = $form->getItemByPostVar('combination_' . $combiId)->getValues();

            if (!isset($combinationPoints['points'][$combiId])) {
                $combinationPoints['points'][$combiId] = array();
                $combinationPoints['select'][$combiId] = array();
                $combinationValues[$combiId] = array();
            }

            foreach ($combi as $varId => $variant) {
                $combinationPoints['points'][$combiId][$varId] = (float) $values[$varId]['points'];
                $combinationPoints['select'][$combiId] = array_keys($values[$varId]['gaps']);
                $combinationValues[$combiId][$varId] = array_values($values[$varId]['gaps']);
            }
        }

        $combinationPoints = $combinationPoints;
        $combinationValues = $combinationValues;

        $assClozeGapCombinationObject = new assClozeGapCombination();
        $assClozeGapCombinationObject->clearGapCombinationsFromDb($this->object->getId());

        $assClozeGapCombinationObject->saveGapCombinationToDb(
            $this->object->getId(),
            $combinationPoints,
            $combinationValues
        );
    }
}
