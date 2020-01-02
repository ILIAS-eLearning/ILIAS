<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultiOptionQuestionFeedback.php';
require_once 'Services/Randomization/classes/class.ilArrayElementOrderKeeper.php';

/**
 * feedback class for assClozeTest questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssClozeTestFeedback extends ilAssMultiOptionQuestionFeedback
{
    /**
     * constants for different feedback modes (per gap or per gap-answers/options)
     */
    const FB_MODE_GAP_QUESTION = 'gapQuestion';
    const FB_MODE_GAP_ANSWERS = 'gapAnswers';
    
    /**
     * constants for answer indexes in case of FB_MODE_GAP_ANSWERS
     */
    const FB_TEXT_GAP_EMPTY_INDEX = -1;
    const FB_TEXT_GAP_NOMATCH_INDEX = -2; // indexes for preset answers: 0 - n
    const FB_SELECT_GAP_EMPTY_INDEX = -1; // indexes for given select options: 0 - n
    const FB_NUMERIC_GAP_EMPTY_INDEX = -1;
    const FB_NUMERIC_GAP_VALUE_HIT_INDEX = 0;
    const FB_NUMERIC_GAP_RANGE_HIT_INDEX = 1;
    const FB_NUMERIC_GAP_TOO_LOW_INDEX = 2;
    const FB_NUMERIC_GAP_TOO_HIGH_INDEX = 3;
    
    const SINGLE_GAP_FB_ANSWER_INDEX = -10;
    
    /**
     * object instance of current question
     *
     * @access protected
     * @var assClozeTest
     */
    protected $questionOBJ = null;
    
    /**
     * returns the answer options mapped by answer index
     * (overwrites parent method from ilAssMultiOptionQuestionFeedback)
     *
     * @return assClozeGap[]
     */
    protected function getGapsByIndex()
    {
        return $this->questionOBJ->gaps;
    }
    
    /**
     * @return boolean $isSaveableInPageObjectEditingMode
     */
    public function isSaveableInPageObjectEditingMode()
    {
        return true;
    }
    
    /**
     * builds an answer option label from given (mixed type) index and answer
     * (overwrites parent method from ilAssMultiOptionQuestionFeedback)
     *
     * @access protected
     * @param integer $indexgapIndex
     * @param assClozeGap $gap
     * @return string $answerOptionLabel
     */
    protected function buildGapFeedbackLabel($gapIndex, $gap)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $answers = array();
        
        foreach ($gap->getItems(new ilArrayElementOrderKeeper()) as $item) {
            $answers[] = '"' . $item->getAnswertext() . '"';
        }
        
        $answers = implode(' / ', $answers);
        
        $label = sprintf(
            $DIC->language()->txt('ass_cloze_gap_fb_gap_label'),
            $gapIndex + 1,
            $answers
        );
        
        return $label;
    }
    
    /**
     * @param integer $gapIndex
     * @param assAnswerCloze $item
     * @return string
     */
    protected function buildTextGapGivenAnswerFeedbackLabel($gapIndex, $item)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        return sprintf(
            $DIC->language()->txt('ass_cloze_gap_fb_txt_match_label'),
            $gapIndex + 1,
            $item->getAnswertext()
        );
    }
    
    /**
     * @param integer $gapIndex
     * @return string
     */
    protected function buildTextGapWrongAnswerFeedbackLabel($gapIndex)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        return sprintf($DIC->language()->txt('ass_cloze_gap_fb_txt_nomatch_label'), $gapIndex + 1);
    }
    
    /**
     * @param integer $gapIndex
     * @return string
     */
    protected function buildTextGapEmptyFeedbackLabel($gapIndex)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        return sprintf($DIC->language()->txt('ass_cloze_gap_fb_txt_empty_label'), $gapIndex + 1);
    }
    
    /**
     * @param integer $gapIndex
     * @param assAnswerCloze $item
     * @return string
     */
    protected function buildSelectGapOptionFeedbackLabel($gapIndex, $item)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        return sprintf(
            $DIC->language()->txt('ass_cloze_gap_fb_sel_opt_label'),
            $gapIndex + 1,
            $item->getAnswertext()
        );
    }
    
    /**
     * @param integer $gapIndex
     * @return string
     */
    protected function buildSelectGapEmptyFeedbackLabel($gapIndex)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        return sprintf($DIC->language()->txt('ass_cloze_gap_fb_sel_empty_label'), $gapIndex + 1);
    }
    
    /**
     * @param integer $gapIndex
     * @return string
     */
    protected function buildNumericGapValueHitFeedbackLabel($gapIndex)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        return sprintf($DIC->language()->txt('ass_cloze_gap_fb_num_valuehit_label'), $gapIndex + 1);
    }
    
    /**
     * @param integer $gapIndex
     * @return string
     */
    protected function buildNumericGapRangeHitFeedbackLabel($gapIndex)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        return sprintf($DIC->language()->txt('ass_cloze_gap_fb_num_rangehit_label'), $gapIndex + 1);
    }
    
    /**
     * @param integer $gapIndex
     * @return string
     */
    protected function buildNumericGapTooLowFeedbackLabel($gapIndex)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        return sprintf($DIC->language()->txt('ass_cloze_gap_fb_num_toolow_label'), $gapIndex + 1);
    }
    
    /**
     * @param integer $gapIndex
     * @return string
     */
    protected function buildNumericGapTooHighFeedbackLabel($gapIndex)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        return sprintf($DIC->language()->txt('ass_cloze_gap_fb_num_toohigh_label'), $gapIndex + 1);
    }
    
    /**
     * @param integer $gapIndex
     * @return string
     */
    protected function buildNumericGapEmptyFeedbackLabel($gapIndex)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        return sprintf($DIC->language()->txt('ass_cloze_gap_fb_num_empty_label'), $gapIndex + 1);
    }
    
    public function completeSpecificFormProperties(ilPropertyFormGUI $form)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if (!$this->questionOBJ->getSelfAssessmentEditingMode()) {
            $header = new ilFormSectionHeaderGUI();
            $header->setTitle($this->lng->txt('feedback_answers'));
            $form->addItem($header);
            
            $feedbackMode = new ilRadioGroupInputGUI(
                $DIC->language()->txt('ass_cloze_fb_mode'),
                'feedback_mode'
            );
            $feedbackMode->setRequired(true);
            $form->addItem($feedbackMode);
            
            $fbModeGapQuestion = new ilRadioOption(
                $DIC->language()->txt('ass_cloze_fb_mode_gap_qst'),
                self::FB_MODE_GAP_QUESTION,
                $DIC->language()->txt('ass_cloze_fb_mode_gap_qst_info')
            );
            $this->completeFormPropsForFeedbackModeGapQuestion($fbModeGapQuestion);
            $feedbackMode->addOption($fbModeGapQuestion);
            
            $fbModeGapAnswers = new ilRadioOption(
                $DIC->language()->txt('ass_cloze_fb_mode_gap_answ'),
                self::FB_MODE_GAP_ANSWERS,
                $DIC->language()->txt('ass_cloze_fb_mode_gap_answ_info')
            );
            $this->completeFormPropsForFeedbackModeGapAnswers($fbModeGapAnswers);
            $feedbackMode->addOption($fbModeGapAnswers);
        }
    }
    
    protected function completeFormPropsForFeedbackModeGapQuestion(ilRadioOption $fbModeOpt)
    {
        foreach ($this->getGapsByIndex() as $gapIndex => $gap) {
            $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
                $this->buildGapFeedbackLabel($gapIndex, $gap),
                true
            );
            
            $fbModeOpt->addSubItem($this->buildFeedbackContentFormProperty(
                $propertyLabel,
                $this->buildPostVarForFbFieldPerGapQuestion($gapIndex),
                $this->questionOBJ->isAdditionalContentEditingModePageObject()
            ));
        }
    }
    
    protected function completeFormPropsForFeedbackModeGapAnswers(ilRadioOption $fbModeOpt)
    {
        foreach ($this->getGapsByIndex() as $gapIndex => $gap) {
            switch ($gap->getType()) {
                case assClozeGap::TYPE_TEXT:
                    
                    $this->completeFbPropsForTextGap($fbModeOpt, $gap, $gapIndex);
                    break;
                
                case assClozeGap::TYPE_SELECT:
                    
                    $this->completeFbPropsForSelectGap($fbModeOpt, $gap, $gapIndex);
                    break;
                    
                case assClozeGap::TYPE_NUMERIC:
                    
                    $this->completeFbPropsForNumericGap($fbModeOpt, $gapIndex, $gap);
                    break;
            }
        }
    }
    
    /**
     * @param ilRadioOption $fbModeOpt
     * @param assClozeGap $gap
     * @param integer $gapIndex
     */
    protected function completeFbPropsForTextGap(ilRadioOption $fbModeOpt, assClozeGap $gap, $gapIndex)
    {
        foreach ($gap->getItems(new ilArrayElementOrderKeeper()) as $answerIndex => $item) {
            $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
                $this->buildTextGapGivenAnswerFeedbackLabel($gapIndex, $item),
                true
            );
            
            $propertyPostVar = "feedback_answer_{$gapIndex}_{$answerIndex}";
            
            $fbModeOpt->addSubItem($this->buildFeedbackContentFormProperty(
                $propertyLabel,
                $propertyPostVar,
                $this->questionOBJ->isAdditionalContentEditingModePageObject()
            ));
        }
        
        $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
            $this->buildTextGapWrongAnswerFeedbackLabel($gapIndex),
            true
        );
        
        $propertyPostVar = "feedback_answer_{$gapIndex}_" . self::FB_TEXT_GAP_NOMATCH_INDEX;
        
        $fbModeOpt->addSubItem($this->buildFeedbackContentFormProperty(
            $propertyLabel,
            $propertyPostVar,
            $this->questionOBJ->isAdditionalContentEditingModePageObject()
        ));
        
        $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
            $this->buildTextGapEmptyFeedbackLabel($gapIndex),
            true
        );
        
        $propertyPostVar = "feedback_answer_{$gapIndex}_" . self::FB_TEXT_GAP_EMPTY_INDEX;
        
        $fbModeOpt->addSubItem($this->buildFeedbackContentFormProperty(
            $propertyLabel,
            $propertyPostVar,
            $this->questionOBJ->isAdditionalContentEditingModePageObject()
        ));
    }
    
    /**
     * @param ilRadioOption $fbModeOpt
     * @param assClozeGap $gap
     * @param integer $gapIndex
     */
    protected function completeFbPropsForSelectGap(ilRadioOption $fbModeOpt, assClozeGap $gap, $gapIndex)
    {
        foreach ($gap->getItems(new ilArrayElementOrderKeeper()) as $optIndex => $item) {
            $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
                $this->buildSelectGapOptionFeedbackLabel($gapIndex, $item),
                true
            );
            
            $propertyPostVar = "feedback_answer_{$gapIndex}_{$optIndex}";
            
            $fbModeOpt->addSubItem($this->buildFeedbackContentFormProperty(
                $propertyLabel,
                $propertyPostVar,
                $this->questionOBJ->isAdditionalContentEditingModePageObject()
            ));
        }
        
        $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
            $this->buildSelectGapEmptyFeedbackLabel($gapIndex),
            true
        );
        
        $propertyPostVar = "feedback_answer_{$gapIndex}_" . self::FB_SELECT_GAP_EMPTY_INDEX;
        
        $fbModeOpt->addSubItem($this->buildFeedbackContentFormProperty(
            $propertyLabel,
            $propertyPostVar,
            $this->questionOBJ->isAdditionalContentEditingModePageObject()
        ));
    }
    
    /**
     * @param ilRadioOption $fbModeOpt
     * @param assClozeGap $gap
     * @param integer $gapIndex
     */
    protected function completeFbPropsForNumericGap(ilRadioOption $fbModeOpt, $gapIndex, assClozeGap $gap)
    {
        $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
            $this->buildNumericGapValueHitFeedbackLabel($gapIndex),
            true
        );
        
        $propertyPostVar = "feedback_answer_{$gapIndex}_" . self::FB_NUMERIC_GAP_VALUE_HIT_INDEX;
        
        $fbModeOpt->addSubItem($this->buildFeedbackContentFormProperty(
            $propertyLabel,
            $propertyPostVar,
            $this->questionOBJ->isAdditionalContentEditingModePageObject()
        ));
        
        if ($gap->numericRangeExists()) {
            $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
                $this->buildNumericGapRangeHitFeedbackLabel($gapIndex),
                true
            );
            
            $propertyPostVar = "feedback_answer_{$gapIndex}_" . self::FB_NUMERIC_GAP_RANGE_HIT_INDEX;
            
            $fbModeOpt->addSubItem($this->buildFeedbackContentFormProperty(
                $propertyLabel,
                $propertyPostVar,
                $this->questionOBJ->isAdditionalContentEditingModePageObject()
            ));
        }
        
        $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
            $this->buildNumericGapTooLowFeedbackLabel($gapIndex),
            true
        );
        
        $propertyPostVar = "feedback_answer_{$gapIndex}_" . self::FB_NUMERIC_GAP_TOO_LOW_INDEX;
        
        $fbModeOpt->addSubItem($this->buildFeedbackContentFormProperty(
            $propertyLabel,
            $propertyPostVar,
            $this->questionOBJ->isAdditionalContentEditingModePageObject()
        ));
        
        $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
            $this->buildNumericGapTooHighFeedbackLabel($gapIndex),
            true
        );
        
        $propertyPostVar = "feedback_answer_{$gapIndex}_" . self::FB_NUMERIC_GAP_TOO_HIGH_INDEX;
        
        $fbModeOpt->addSubItem($this->buildFeedbackContentFormProperty(
            $propertyLabel,
            $propertyPostVar,
            $this->questionOBJ->isAdditionalContentEditingModePageObject()
        ));
        
        $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
            $this->buildNumericGapEmptyFeedbackLabel($gapIndex),
            true
        );
        
        $propertyPostVar = "feedback_answer_{$gapIndex}_" . self::FB_NUMERIC_GAP_EMPTY_INDEX;
        
        $fbModeOpt->addSubItem($this->buildFeedbackContentFormProperty(
            $propertyLabel,
            $propertyPostVar,
            $this->questionOBJ->isAdditionalContentEditingModePageObject()
        ));
    }
    
    public function initSpecificFormProperties(ilPropertyFormGUI $form)
    {
        if (!$this->questionOBJ->getSelfAssessmentEditingMode()) {
            /* @var ilRadioGroupInputGUI $fbMode */
            $fbMode = $form->getItemByPostVar('feedback_mode');
            $fbMode->setValue($this->questionOBJ->getFeedbackMode());
            
            if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
                $this->initFeedbackFieldsPerGapQuestion($form);
                $this->initFeedbackFieldsPerGapAnswers($form);
            } else {
                switch ($this->questionOBJ->getFeedbackMode()) {
                    case self::FB_MODE_GAP_QUESTION:
                        
                        $this->initFeedbackFieldsPerGapQuestion($form);
                        break;
                    
                    case self::FB_MODE_GAP_ANSWERS:
                        
                        $this->initFeedbackFieldsPerGapAnswers($form);
                        break;
                }
            }
        }
    }
    
    protected function initFeedbackFieldsPerGapQuestion(ilPropertyFormGUI $form)
    {
        foreach ($this->getGapsByIndex() as $gapIndex => $gap) {
            $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, self::SINGLE_GAP_FB_ANSWER_INDEX);
            $form->getItemByPostVar($this->buildPostVarForFbFieldPerGapQuestion($gapIndex))->setValue($value);
        }
    }
    
    protected function initFeedbackFieldsPerGapAnswers(ilPropertyFormGUI $form)
    {
        foreach ($this->getGapsByIndex() as $gapIndex => $gap) {
            switch ($gap->getType()) {
                case assClozeGap::TYPE_TEXT:
                    
                    $this->initFbPropsForTextGap($form, $gap, $gapIndex);
                    break;
                
                case assClozeGap::TYPE_SELECT:
                    
                    $this->initFbPropsForSelectGap($form, $gap, $gapIndex);
                    break;
                
                case assClozeGap::TYPE_NUMERIC:
                    
                    $this->initFbPropsForNumericGap($form, $gapIndex, $gap);
                    break;
            }
        }
    }
    
    protected function initFbPropsForTextGap(ilPropertyFormGUI $form, assClozeGap $gap, $gapIndex)
    {
        foreach ($gap->getItems(new ilArrayElementOrderKeeper()) as $answerIndex => $item) {
            $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, $answerIndex);
            $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, $answerIndex);
            $form->getItemByPostVar($postVar)->setValue($value);
        }
        
        $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, self::FB_TEXT_GAP_NOMATCH_INDEX);
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_TEXT_GAP_NOMATCH_INDEX);
        $form->getItemByPostVar($postVar)->setValue($value);
        
        $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, self::FB_TEXT_GAP_EMPTY_INDEX);
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_TEXT_GAP_EMPTY_INDEX);
        $form->getItemByPostVar($postVar)->setValue($value);
    }
    
    protected function initFbPropsForSelectGap(ilPropertyFormGUI $form, assClozeGap $gap, $gapIndex)
    {
        foreach ($gap->getItems(new ilArrayElementOrderKeeper()) as $optIndex => $item) {
            $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, $optIndex);
            $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, $optIndex);
            $form->getItemByPostVar($postVar)->setValue($value);
        }
        
        $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, self::FB_SELECT_GAP_EMPTY_INDEX);
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_SELECT_GAP_EMPTY_INDEX);
        $form->getItemByPostVar($postVar)->setValue($value);
    }
    
    protected function initFbPropsForNumericGap(ilPropertyFormGUI $form, $gapIndex, assClozeGap $gap)
    {
        $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, self::FB_NUMERIC_GAP_VALUE_HIT_INDEX);
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_NUMERIC_GAP_VALUE_HIT_INDEX);
        $form->getItemByPostVar($postVar)->setValue($value);

        if ($gap->numericRangeExists()) {
            $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, self::FB_NUMERIC_GAP_RANGE_HIT_INDEX);
            $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_NUMERIC_GAP_RANGE_HIT_INDEX);
            $form->getItemByPostVar($postVar)->setValue($value);
        }

        $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, self::FB_NUMERIC_GAP_TOO_LOW_INDEX);
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_NUMERIC_GAP_TOO_LOW_INDEX);
        $form->getItemByPostVar($postVar)->setValue($value);

        $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, self::FB_NUMERIC_GAP_TOO_HIGH_INDEX);
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_NUMERIC_GAP_TOO_HIGH_INDEX);
        $form->getItemByPostVar($postVar)->setValue($value);

        $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, self::FB_NUMERIC_GAP_EMPTY_INDEX);
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_NUMERIC_GAP_EMPTY_INDEX);
        $form->getItemByPostVar($postVar)->setValue($value);
    }
    
    public function saveSpecificFormProperties(ilPropertyFormGUI $form)
    {
        if (!$this->questionOBJ->getSelfAssessmentEditingMode()) {
            $fbMode = $form->getItemByPostVar('feedback_mode')->getValue();
            
            if ($fbMode != $this->questionOBJ->getFeedbackMode()) {
                $this->cleanupSpecificAnswerFeedbacks($this->questionOBJ->getFeedbackMode());
            }
            
            $this->saveSpecificFeedbackMode($this->questionOBJ->getId(), $fbMode);
            
            switch ($this->questionOBJ->getFeedbackMode()) {
                case self::FB_MODE_GAP_QUESTION:
                    
                    $this->saveFeedbackFieldsPerGapQuestion($form);
                    break;
                
                case self::FB_MODE_GAP_ANSWERS:
                    
                    $this->saveFeedbackFieldsPerGapAnswers($form);
                    break;
            }
        }
    }
    
    protected function saveFeedbackFieldsPerGapQuestion(ilPropertyFormGUI $form)
    {
        foreach ($this->getGapsByIndex() as $gapIndex => $gap) {
            $postVar = $this->buildPostVarForFbFieldPerGapQuestion($gapIndex);
            $value = $form->getItemByPostVar($postVar)->getValue();
            
            $this->saveSpecificAnswerFeedbackContent(
                $this->questionOBJ->getId(),
                $gapIndex,
                self::SINGLE_GAP_FB_ANSWER_INDEX,
                $value
            );
        }
    }
    
    protected function saveFeedbackFieldsPerGapAnswers(ilPropertyFormGUI $form)
    {
        foreach ($this->getGapsByIndex() as $gapIndex => $gap) {
            switch ($gap->getType()) {
                case assClozeGap::TYPE_TEXT:
                    
                    $this->saveFbPropsForTextGap($form, $gap, $gapIndex);
                    break;
                
                case assClozeGap::TYPE_SELECT:
                    
                    $this->saveFbPropsForSelectGap($form, $gap, $gapIndex);
                    break;
                
                case assClozeGap::TYPE_NUMERIC:
                    
                    $this->saveFbPropsForNumericGap($form, $gapIndex, $gap);
                    break;
            }
        }
    }
    
    protected function saveFbPropsForTextGap(ilPropertyFormGUI $form, assClozeGap $gap, $gapIndex)
    {
        foreach ($gap->getItems(new ilArrayElementOrderKeeper()) as $answerIndex => $item) {
            $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, $answerIndex);
            $value = $form->getItemByPostVar($postVar)->getValue();
            $this->saveSpecificAnswerFeedbackContent(
                $this->questionOBJ->getId(),
                $gapIndex,
                $answerIndex,
                $value
            );
        }
        
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_TEXT_GAP_NOMATCH_INDEX);
        $value = $form->getItemByPostVar($postVar)->getValue();
        $this->saveSpecificAnswerFeedbackContent(
            $this->questionOBJ->getId(),
            $gapIndex,
            self::FB_TEXT_GAP_NOMATCH_INDEX,
            $value
        );
        
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_TEXT_GAP_EMPTY_INDEX);
        $value = $form->getItemByPostVar($postVar)->getValue();
        $this->saveSpecificAnswerFeedbackContent(
            $this->questionOBJ->getId(),
            $gapIndex,
            self::FB_TEXT_GAP_EMPTY_INDEX,
            $value
        );
    }
    
    protected function saveFbPropsForSelectGap(ilPropertyFormGUI $form, assClozeGap $gap, $gapIndex)
    {
        foreach ($gap->getItems(new ilArrayElementOrderKeeper()) as $optIndex => $item) {
            $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, $optIndex);
            $value = $form->getItemByPostVar($postVar)->getValue();
            $this->saveSpecificAnswerFeedbackContent(
                $this->questionOBJ->getId(),
                $gapIndex,
                $optIndex,
                $value
            );
        }
        
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_SELECT_GAP_EMPTY_INDEX);
        $value = $form->getItemByPostVar($postVar)->getValue();
        $this->saveSpecificAnswerFeedbackContent(
            $this->questionOBJ->getId(),
            $gapIndex,
            self::FB_SELECT_GAP_EMPTY_INDEX,
            $value
        );
    }
    
    protected function saveFbPropsForNumericGap(ilPropertyFormGUI $form, $gapIndex, assClozeGap $gap)
    {
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_NUMERIC_GAP_VALUE_HIT_INDEX);
        $value = $form->getItemByPostVar($postVar)->getValue();
        $this->saveSpecificAnswerFeedbackContent(
            $this->questionOBJ->getId(),
            $gapIndex,
            self::FB_NUMERIC_GAP_VALUE_HIT_INDEX,
            $value
        );
        
        if ($gap->numericRangeExists()) {
            $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_NUMERIC_GAP_RANGE_HIT_INDEX);
            $value = $form->getItemByPostVar($postVar)->getValue();
            $this->saveSpecificAnswerFeedbackContent(
                $this->questionOBJ->getId(),
                $gapIndex,
                self::FB_NUMERIC_GAP_RANGE_HIT_INDEX,
                $value
            );
        }
        
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_NUMERIC_GAP_TOO_LOW_INDEX);
        $value = $form->getItemByPostVar($postVar)->getValue();
        $this->saveSpecificAnswerFeedbackContent(
            $this->questionOBJ->getId(),
            $gapIndex,
            self::FB_NUMERIC_GAP_TOO_LOW_INDEX,
            $value
        );
        
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_NUMERIC_GAP_TOO_HIGH_INDEX);
        $value = $form->getItemByPostVar($postVar)->getValue();
        $this->saveSpecificAnswerFeedbackContent(
            $this->questionOBJ->getId(),
            $gapIndex,
            self::FB_NUMERIC_GAP_TOO_HIGH_INDEX,
            $value
        );
        
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_NUMERIC_GAP_EMPTY_INDEX);
        $value = $form->getItemByPostVar($postVar)->getValue();
        $this->saveSpecificAnswerFeedbackContent(
            $this->questionOBJ->getId(),
            $gapIndex,
            self::FB_NUMERIC_GAP_EMPTY_INDEX,
            $value
        );
    }

    /**
     * duplicates the SPECIFIC feedback relating to the given original question id
     * and saves it for the given duplicate question id
     *
     * (overwrites the method from parent class, because of individual setting)
     *
     * @access protected
     * @param integer $originalQuestionId
     * @param integer $duplicateQuestionId
     */
    protected function duplicateSpecificFeedback($originalQuestionId, $duplicateQuestionId)
    {
        // sync specific feedback setting to duplicated question
        
        $this->syncSpecificFeedbackSetting($originalQuestionId, $duplicateQuestionId);
        
        parent::duplicateSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }
    
    private function syncSpecificFeedbackSetting($sourceQuestionId, $targetQuestionId)
    {
        $res = $this->db->queryF(
            "SELECT feedback_mode FROM {$this->questionOBJ->getAdditionalTableName()} WHERE question_fi = %s",
            array('integer'),
            array($sourceQuestionId)
        );
        
        $row = $this->db->fetchAssoc($res);
        
        $this->db->update(
            $this->questionOBJ->getAdditionalTableName(),
            array( 'feedback_mode' => array('text', $row['feedback_mode']) ),
            array( 'question_fi' => array('integer', $targetQuestionId) )
        );
    }
    
    protected function syncSpecificFeedback($originalQuestionId, $duplicateQuestionId)
    {
        $this->syncSpecificFeedbackSetting($originalQuestionId, $duplicateQuestionId);
        parent::syncSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }
    
    /**
     * saves the given specific feedback mode for the given question id to the db.
     * (It's stored to dataset of question itself)
 * @param integer $questionId
     * @param string $feedbackMode
     */
    protected function saveSpecificFeedbackMode($questionId, $feedbackMode)
    {
        $this->questionOBJ->setFeedbackMode($feedbackMode);
        
        $this->db->update(
            $this->questionOBJ->getAdditionalTableName(),
            array('feedback_mode' => array('text', $feedbackMode)),
            array('question_fi' => array('integer', $questionId))
        );
    }
    
    /**
     * @param integer $gapIndex
     * @return string
     */
    protected function buildPostVarForFbFieldPerGapQuestion($gapIndex)
    {
        return "feedback_answer_{$gapIndex}";
    }
    
    /**
     * @param integer $gapIndex
     * @param integer $answerIndex
     * @return string
     */
    protected function buildPostVarForFbFieldPerGapAnswers($gapIndex, $answerIndex)
    {
        return "feedback_answer_{$gapIndex}_{$answerIndex}";
    }
    
    /**
     * @param $gapIndex
     * @param $answerIndex
     * @return mixed|string
     */
    protected function getSpecificAnswerFeedbackFormValue($gapIndex, $answerIndex)
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $pageObjectId = $this->getSpecificAnswerFeedbackPageObjectId(
                $this->questionOBJ->getId(),
                $gapIndex,
                $answerIndex
            );
            
            $value = $this->getPageObjectNonEditableValueHTML(
                $this->getSpecificAnswerFeedbackPageObjectType(),
                $pageObjectId
            );
        } else {
            $value = $this->questionOBJ->prepareTextareaOutput(
                $this->getSpecificAnswerFeedbackContent($this->questionOBJ->getId(), $gapIndex, $answerIndex)
            );
        }
        
        return $value;
    }
    
    protected function cleanupSpecificAnswerFeedbacks($fbMode)
    {
        switch ($fbMode) {
            case self::FB_MODE_GAP_QUESTION:
                $feedbackIds = $this->fetchFeedbackIdsForGapQuestionMode();
                break;
                
            case self::FB_MODE_GAP_ANSWERS:
                $feedbackIds = $this->fetchFeedbackIdsForGapAnswersMode();
                break;
                
            default: $feedbackIds = array();
        }
        
        $this->deleteSpecificAnswerFeedbacksByIds($feedbackIds);
    }
    
    protected function fetchFeedbackIdsForGapQuestionMode()
    {
        require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssSpecificFeedbackIdentifierList.php';
        $feedbackIdentifiers = new ilAssSpecificFeedbackIdentifierList();
        $feedbackIdentifiers->load($this->questionOBJ->getId());
        
        $feedbackIds = array();
        
        foreach ($feedbackIdentifiers as $identifier) {
            if ($identifier->getAnswerIndex() != self::SINGLE_GAP_FB_ANSWER_INDEX) {
                continue;
            }
            
            $feedbackIds[] = $identifier->getFeedbackId();
        }
        
        return $feedbackIds;
    }
    
    protected function fetchFeedbackIdsForGapAnswersMode()
    {
        require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssSpecificFeedbackIdentifierList.php';
        $feedbackIdentifiers = new ilAssSpecificFeedbackIdentifierList();
        $feedbackIdentifiers->load($this->questionOBJ->getId());
        
        $feedbackIds = array();
        
        foreach ($feedbackIdentifiers as $identifier) {
            if ($identifier->getAnswerIndex() == self::SINGLE_GAP_FB_ANSWER_INDEX) {
                continue;
            }
            
            $feedbackIds[] = $identifier->getFeedbackId();
        }
        
        return $feedbackIds;
    }
    
    protected function deleteSpecificAnswerFeedbacksByIds($feedbackIds)
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            foreach ($feedbackIds as $fbId) {
                $this->ensurePageObjectDeleted($this->getSpecificAnswerFeedbackPageObjectType(), $fbId);
            }
        }
        
        $IN_feedbackIds = $this->db->in('feedback_id', $feedbackIds, false, 'integer');
        $this->db->manipulate("DELETE FROM {$this->getSpecificFeedbackTableName()} WHERE {$IN_feedbackIds}");
    }
    
    public function determineTestOutputGapFeedback($gapIndex, $answerIndex)
    {
        if ($this->questionOBJ->getFeedbackMode() == self::FB_MODE_GAP_QUESTION) {
            return $this->getSpecificAnswerFeedbackTestPresentation(
                $this->questionOBJ->getId(),
                $gapIndex,
                self::SINGLE_GAP_FB_ANSWER_INDEX
            );
        }
        
        return $this->getSpecificAnswerFeedbackTestPresentation($this->questionOBJ->getId(), $gapIndex, $answerIndex);
    }
    
    public function determineAnswerIndexForAnswerValue(assClozeGap $gap, $answerValue)
    {
        switch ($gap->getType()) {
            case CLOZE_TEXT:
                
                if (!strlen($answerValue)) {
                    return self::FB_TEXT_GAP_EMPTY_INDEX;
                }
                
                $items = $gap->getItems(new ilArrayElementOrderKeeper());
                
                foreach ($items as $answerIndex => $answer) {
                    /* @var assAnswerCloze $answer */
                    
                    if ($answer->getAnswertext() == $answerValue) {
                        return $answerIndex;
                    }
                }
                
                return self::FB_TEXT_GAP_NOMATCH_INDEX;
            
            case CLOZE_SELECT:
                
                if (strlen($answerValue)) {
                    return $answerValue;
                }
                
                return self::FB_SELECT_GAP_EMPTY_INDEX;
            
            case CLOZE_NUMERIC:
                
                if (!strlen($answerValue)) {
                    return self::FB_NUMERIC_GAP_EMPTY_INDEX;
                }
                
                /* @var assAnswerCloze $item */
                
                $item = current($gap->getItems(new ilArrayElementOrderKeeper()));
                
                if ($answerValue == $item->getAnswertext()) {
                    return self::FB_NUMERIC_GAP_VALUE_HIT_INDEX;
                }
                
                require_once 'Services/Math/classes/class.EvalMath.php';
                $math = new EvalMath();
                
                $item = $gap->getItem(0);
                $lowerBound = $math->evaluate($item->getLowerBound());
                $upperBound = $math->evaluate($item->getUpperBound());
                $preciseValue = $math->evaluate($item->getAnswertext());
                
                $solutionValue = $math->evaluate($answerValue);
                
                if ($solutionValue == $preciseValue) {
                    return self::FB_NUMERIC_GAP_VALUE_HIT_INDEX;
                }
                
                if ($solutionValue >= $lowerBound && $solutionValue <= $upperBound) {
                    return self::FB_NUMERIC_GAP_RANGE_HIT_INDEX;
                }
                
                if ($solutionValue < $lowerBound) {
                    return self::FB_NUMERIC_GAP_TOO_LOW_INDEX;
                }
                
                if ($solutionValue > $upperBound) {
                    return self::FB_NUMERIC_GAP_TOO_HIGH_INDEX;
                }
        }
    }
}
