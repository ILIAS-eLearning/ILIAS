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

use ILIAS\Refinery\Random\Group as RandomGroup;

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
    public const FB_MODE_GAP_QUESTION = 'gapQuestion';
    public const FB_MODE_GAP_ANSWERS = 'gapAnswers';

    /**
     * constants for answer indexes in case of FB_MODE_GAP_ANSWERS
     */
    public const FB_TEXT_GAP_EMPTY_INDEX = -1;
    public const FB_TEXT_GAP_NOMATCH_INDEX = -2; // indexes for preset answers: 0 - n
    public const FB_SELECT_GAP_EMPTY_INDEX = -1; // indexes for given select options: 0 - n
    public const FB_NUMERIC_GAP_EMPTY_INDEX = -1;
    public const FB_NUMERIC_GAP_VALUE_HIT_INDEX = 0;
    public const FB_NUMERIC_GAP_RANGE_HIT_INDEX = 1;
    public const FB_NUMERIC_GAP_TOO_LOW_INDEX = 2;
    public const FB_NUMERIC_GAP_TOO_HIGH_INDEX = 3;

    public const SINGLE_GAP_FB_ANSWER_INDEX = -10;

    public function isSaveableInPageObjectEditingMode(): bool
    {
        return true;
    }

    /**
     * builds an answer option label from given (mixed type) index and answer
     * (overwrites parent method from ilAssMultiOptionQuestionFeedback)
     */
    protected function buildGapFeedbackLabel(int $gapIndex, assClozeGap $gap): string
    {
        $answers = array();

        foreach ($gap->getItems($this->randomGroup()->dontShuffle()) as $item) {
            $answers[] = '"' . $item->getAnswertext() . '"';
        }

        $answers = implode(' / ', $answers);

        return sprintf(
            $this->lng->txt('ass_cloze_gap_fb_gap_label'),
            $gapIndex + 1,
            $answers
        );
    }

    protected function buildTextGapGivenAnswerFeedbackLabel(int $gapIndex, assAnswerCloze $item): string
    {
        return sprintf(
            $this->lng->txt('ass_cloze_gap_fb_txt_match_label'),
            $gapIndex + 1,
            $item->getAnswertext()
        );
    }

    protected function buildTextGapWrongAnswerFeedbackLabel(int $gapIndex): string
    {
        return sprintf($this->lng->txt('ass_cloze_gap_fb_txt_nomatch_label'), $gapIndex + 1);
    }

    protected function buildTextGapEmptyFeedbackLabel(int $gapIndex): string
    {
        return sprintf($this->lng->txt('ass_cloze_gap_fb_txt_empty_label'), $gapIndex + 1);
    }

    protected function buildSelectGapOptionFeedbackLabel(int $gapIndex, assAnswerCloze $item): string
    {
        return sprintf(
            $this->lng->txt('ass_cloze_gap_fb_sel_opt_label'),
            $gapIndex + 1,
            $item->getAnswertext()
        );
    }

    protected function buildSelectGapEmptyFeedbackLabel(int $gapIndex): string
    {
        return sprintf($this->lng->txt('ass_cloze_gap_fb_sel_empty_label'), $gapIndex + 1);
    }

    protected function buildNumericGapValueHitFeedbackLabel(int $gapIndex): string
    {
        return sprintf($this->lng->txt('ass_cloze_gap_fb_num_valuehit_label'), $gapIndex + 1);
    }

    protected function buildNumericGapRangeHitFeedbackLabel(int $gapIndex): string
    {
        return sprintf($this->lng->txt('ass_cloze_gap_fb_num_rangehit_label'), $gapIndex + 1);
    }

    protected function buildNumericGapTooLowFeedbackLabel(int $gapIndex): string
    {
        return sprintf($this->lng->txt('ass_cloze_gap_fb_num_toolow_label'), $gapIndex + 1);
    }

    protected function buildNumericGapTooHighFeedbackLabel(int $gapIndex): string
    {
        return sprintf($this->lng->txt('ass_cloze_gap_fb_num_toohigh_label'), $gapIndex + 1);
    }

    protected function buildNumericGapEmptyFeedbackLabel(int $gapIndex): string
    {
        return sprintf($this->lng->txt('ass_cloze_gap_fb_num_empty_label'), $gapIndex + 1);
    }

    public function completeSpecificFormProperties(ilPropertyFormGUI $form): void
    {
        if (!$this->questionOBJ->getSelfAssessmentEditingMode()) {
            $header = new ilFormSectionHeaderGUI();
            $header->setTitle($this->lng->txt('feedback_answers'));
            $form->addItem($header);

            $feedbackMode = new ilRadioGroupInputGUI(
                $this->lng->txt('ass_cloze_fb_mode'),
                'feedback_mode'
            );
            $feedbackMode->setRequired(true);
            $form->addItem($feedbackMode);

            $fbModeGapQuestion = new ilRadioOption(
                $this->lng->txt('ass_cloze_fb_mode_gap_qst'),
                self::FB_MODE_GAP_QUESTION,
                $this->lng->txt('ass_cloze_fb_mode_gap_qst_info')
            );
            $this->completeFormPropsForFeedbackModeGapQuestion($fbModeGapQuestion);
            $feedbackMode->addOption($fbModeGapQuestion);

            $fbModeGapAnswers = new ilRadioOption(
                $this->lng->txt('ass_cloze_fb_mode_gap_answ'),
                self::FB_MODE_GAP_ANSWERS,
                $this->lng->txt('ass_cloze_fb_mode_gap_answ_info')
            );
            $this->completeFormPropsForFeedbackModeGapAnswers($fbModeGapAnswers);
            $feedbackMode->addOption($fbModeGapAnswers);
        }
    }

    protected function completeFormPropsForFeedbackModeGapQuestion(ilRadioOption $fbModeOpt): void
    {
        foreach ($this->questionOBJ->getGaps() as $gapIndex => $gap) {
            $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
                $this->buildGapFeedbackLabel($gapIndex, $gap),
                true
            );

            $fbModeOpt->addSubItem(
                $this->buildFeedbackContentFormProperty(
                    $propertyLabel,
                    $this->buildPostVarForFbFieldPerGapQuestion($gapIndex),
                    $this->questionOBJ->isAdditionalContentEditingModePageObject()
                )
            );
        }
    }

    protected function completeFormPropsForFeedbackModeGapAnswers(ilRadioOption $fbModeOpt): void
    {
        foreach ($this->questionOBJ->getGaps() as $gapIndex => $gap) {
            switch ($gap->getType()) {
                case assClozeGap::TYPE_TEXT:

                    $this->completeFbPropsForTextGap($fbModeOpt, $gap, $gapIndex);
                    break;

                case assClozeGap::TYPE_SELECT:

                    $this->completeFbPropsForSelectGap($fbModeOpt, $gap, $gapIndex);
                    break;

                case assClozeGap::TYPE_NUMERIC:

                    $this->completeFbPropsForNumericGap($fbModeOpt, $gap, $gapIndex);
                    break;
            }
        }
    }

    protected function completeFbPropsForTextGap(ilRadioOption $fbModeOpt, assClozeGap $gap, int $gapIndex): void
    {
        foreach ($gap->getItems($this->randomGroup()->dontShuffle()) as $answerIndex => $item) {
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

    protected function completeFbPropsForSelectGap(ilRadioOption $fbModeOpt, assClozeGap $gap, int $gapIndex): void
    {
        foreach ($gap->getItems($this->randomGroup()->dontShuffle()) as $optIndex => $item) {
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
    protected function completeFbPropsForNumericGap(ilRadioOption $fbModeOpt, assClozeGap $gap, int $gapIndex): void
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

    public function initSpecificFormProperties(ilPropertyFormGUI $form): void
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

    protected function initFeedbackFieldsPerGapQuestion(ilPropertyFormGUI $form): void
    {
        foreach ($this->questionOBJ->getGaps() as $gapIndex => $gap) {
            $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, self::SINGLE_GAP_FB_ANSWER_INDEX);
            $form->getItemByPostVar($this->buildPostVarForFbFieldPerGapQuestion($gapIndex))->setValue($value);
        }
    }

    protected function initFeedbackFieldsPerGapAnswers(ilPropertyFormGUI $form): void
    {
        foreach ($this->questionOBJ->getGaps() as $gapIndex => $gap) {
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

    protected function initFbPropsForTextGap(ilPropertyFormGUI $form, assClozeGap $gap, int $gapIndex): void
    {
        foreach ($gap->getItems($this->randomGroup()->dontShuffle()) as $answerIndex => $item) {
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

    protected function initFbPropsForSelectGap(ilPropertyFormGUI $form, assClozeGap $gap, int $gapIndex): void
    {
        foreach ($gap->getItems($this->randomGroup()->dontShuffle()) as $optIndex => $item) {
            $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, $optIndex);
            $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, $optIndex);
            $form->getItemByPostVar($postVar)->setValue($value);
        }

        $value = $this->getSpecificAnswerFeedbackFormValue($gapIndex, self::FB_SELECT_GAP_EMPTY_INDEX);
        $postVar = $this->buildPostVarForFbFieldPerGapAnswers($gapIndex, self::FB_SELECT_GAP_EMPTY_INDEX);
        $form->getItemByPostVar($postVar)->setValue($value);
    }

    protected function initFbPropsForNumericGap(ilPropertyFormGUI $form, int $gapIndex, assClozeGap $gap): void
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

    public function saveSpecificFormProperties(ilPropertyFormGUI $form): void
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

    protected function saveFeedbackFieldsPerGapQuestion(ilPropertyFormGUI $form): void
    {
        foreach ($this->questionOBJ->getGaps() as $gapIndex => $gap) {
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

    protected function saveFeedbackFieldsPerGapAnswers(ilPropertyFormGUI $form): void
    {
        foreach ($this->questionOBJ->getGaps() as $gapIndex => $gap) {
            switch ($gap->getType()) {
                case assClozeGap::TYPE_TEXT:

                    $this->saveFbPropsForTextGap($form, $gap, $gapIndex);
                    break;

                case assClozeGap::TYPE_SELECT:

                    $this->saveFbPropsForSelectGap($form, $gap, $gapIndex);
                    break;

                case assClozeGap::TYPE_NUMERIC:

                    $this->saveFbPropsForNumericGap($form, $gap, $gapIndex);
                    break;
            }
        }
    }

    protected function saveFbPropsForTextGap(ilPropertyFormGUI $form, assClozeGap $gap, int $gapIndex): void
    {
        foreach ($gap->getItems($this->randomGroup()->dontShuffle()) as $answerIndex => $item) {
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

    protected function saveFbPropsForSelectGap(ilPropertyFormGUI $form, assClozeGap $gap, int $gapIndex): void
    {
        foreach ($gap->getItems($this->randomGroup()->dontShuffle()) as $optIndex => $item) {
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

    protected function saveFbPropsForNumericGap(ilPropertyFormGUI $form, assClozeGap $gap, int $gapIndex): void
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

    protected function duplicateSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId): void
    {
        $this->syncSpecificFeedbackSetting($originalQuestionId, $duplicateQuestionId);

        parent::duplicateSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }

    private function syncSpecificFeedbackSetting(int $sourceQuestionId, int $targetQuestionId): void
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

    protected function syncSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId): void
    {
        $this->syncSpecificFeedbackSetting($originalQuestionId, $duplicateQuestionId);
        parent::syncSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }

    /**
     * saves the given specific feedback mode for the given question id to the db.
     * (It's stored to dataset of question itself)
     */
    protected function saveSpecificFeedbackMode(int $questionId, string $feedbackMode): void
    {
        $this->questionOBJ->setFeedbackMode($feedbackMode);

        $this->db->update(
            $this->questionOBJ->getAdditionalTableName(),
            array('feedback_mode' => array('text', $feedbackMode)),
            array('question_fi' => array('integer', $questionId))
        );
    }

    protected function buildPostVarForFbFieldPerGapQuestion(int $gapIndex): string
    {
        return "feedback_answer_{$gapIndex}";
    }

    protected function buildPostVarForFbFieldPerGapAnswers(int $gapIndex, int $answerIndex): string
    {
        return "feedback_answer_{$gapIndex}_{$answerIndex}";
    }

    protected function getSpecificAnswerFeedbackFormValue(int $gapIndex, int $answerIndex): string
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

    protected function cleanupSpecificAnswerFeedbacks(string $fbMode): void
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

    /**
     * @return int[]
     */
    protected function fetchFeedbackIdsForGapQuestionMode(): array
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

    /**
     * @return int[]
     */
    protected function fetchFeedbackIdsForGapAnswersMode(): array
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

    /**
     * @param int[] $feedbackIds
     */
    protected function deleteSpecificAnswerFeedbacksByIds(array $feedbackIds): void
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            foreach ($feedbackIds as $fbId) {
                $this->ensurePageObjectDeleted($this->getSpecificAnswerFeedbackPageObjectType(), $fbId);
            }
        }

        $IN_feedbackIds = $this->db->in('feedback_id', $feedbackIds, false, 'integer');
        $this->db->manipulate("DELETE FROM {$this->getSpecificFeedbackTableName()} WHERE {$IN_feedbackIds}");
    }

    public function determineTestOutputGapFeedback(int $gapIndex, int $answerIndex): string
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

    public function determineAnswerIndexForAnswerValue(assClozeGap $gap, int $answerValue): int
    {
        switch ($gap->getType()) {
            case CLOZE_TEXT:

                if (!strlen($answerValue)) {
                    return self::FB_TEXT_GAP_EMPTY_INDEX;
                }

                $items = $gap->getItems($this->randomGroup()->dontShuffle());

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

            default:
            case CLOZE_NUMERIC:

                if (!strlen($answerValue)) {
                    return self::FB_NUMERIC_GAP_EMPTY_INDEX;
                }

                /* @var assAnswerCloze $item */

                $item = current($gap->getItems($this->randomGroup()->dontShuffle()));

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
                } elseif ($solutionValue >= $lowerBound && $solutionValue <= $upperBound) {
                    return self::FB_NUMERIC_GAP_RANGE_HIT_INDEX;
                } elseif ($solutionValue < $lowerBound) {
                    return self::FB_NUMERIC_GAP_TOO_LOW_INDEX;
                }

                //  if ($solutionValue > $upperBound) {
                    return self::FB_NUMERIC_GAP_TOO_HIGH_INDEX;
                //}
        }
    }

    private function randomGroup(): RandomGroup
    {
        global $DIC;

        return $DIC->refinery()->random();
    }
}
