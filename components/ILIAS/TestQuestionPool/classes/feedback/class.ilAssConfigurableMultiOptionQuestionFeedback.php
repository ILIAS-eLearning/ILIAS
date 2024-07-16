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
 * abstract parent feedback class for question types
 * with multiple answer options (mc, sc, ...)
 * and configurable display behaviour
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @abstract
 */
abstract class ilAssConfigurableMultiOptionQuestionFeedback extends ilAssMultiOptionQuestionFeedback
{
    public const FEEDBACK_SETTING_ALL = 1;
    public const FEEDBACK_SETTING_CHECKED = 2;
    public const FEEDBACK_SETTING_CORRECT = 3;

    abstract protected function getSpecificQuestionTableName(): string;

    public function completeSpecificFormProperties(ilPropertyFormGUI $form): void
    {
        if (!$this->questionOBJ->getSelfAssessmentEditingMode()) {
            $header = new ilFormSectionHeaderGUI();
            $header->setTitle($this->lng->txt('feedback_answers'));
            $form->addItem($header);

            $feedback = new ilRadioGroupInputGUI($this->lng->txt('feedback_setting'), 'feedback_setting');
            $feedback->addOption(
                new ilRadioOption($this->lng->txt('feedback_all'), self::FEEDBACK_SETTING_ALL)
            );
            $feedback->addOption(
                new ilRadioOption($this->lng->txt('feedback_checked'), self::FEEDBACK_SETTING_CHECKED)
            );
            $feedback->addOption(
                new ilRadioOption($this->lng->txt($this->questionOBJ->getSpecificFeedbackAllCorrectOptionLabel()), self::FEEDBACK_SETTING_CORRECT)
            );

            $feedback->setRequired(true);
            $form->addItem($feedback);

            foreach ($this->getAnswerOptionsByAnswerIndex() as $index => $answer) {
                $propertyLabel = ilLegacyFormElementsUtil::prepareTextareaOutput(
                    $this->buildAnswerOptionLabel($index, $answer),
                    true
                );

                $propertyPostVar = "feedback_answer_$index";

                $form->addItem($this->buildFeedbackContentFormProperty(
                    $propertyLabel,
                    $propertyPostVar,
                    $this->questionOBJ->isAdditionalContentEditingModePageObject()
                ));
            }
        }
    }

    public function initSpecificFormProperties(ilPropertyFormGUI $form): void
    {
        if (!$this->questionOBJ->getSelfAssessmentEditingMode()) {
            $form->getItemByPostVar('feedback_setting')->setValue(
                $this->questionOBJ->getSpecificFeedbackSetting()
            );

            foreach ($this->getAnswerOptionsByAnswerIndex() as $index => $answer) {
                if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
                    $value = $this->getPageObjectNonEditableValueHTML(
                        $this->getSpecificAnswerFeedbackPageObjectType(),
                        $this->getSpecificAnswerFeedbackPageObjectId($this->questionOBJ->getId(), 0, $index)
                    );
                } else {
                    $value = ilLegacyFormElementsUtil::prepareTextareaOutput(
                        $this->getSpecificAnswerFeedbackContent($this->questionOBJ->getId(), 0, $index)
                    );
                }

                $form->getItemByPostVar("feedback_answer_$index")->setValue($value);
            }
        }
    }

    public function saveSpecificFormProperties(ilPropertyFormGUI $form): void
    {
        $feedback_setting = $form->getInput('feedback_setting');

        /* sk 03.03.2023: This avoids Problems with questions in Learning Module
         * See: https://mantis.ilias.de/view.php?id=34724
         */
        if ($feedback_setting === '') {
            return;
        }

        $this->saveSpecificFeedbackSetting($this->questionOBJ->getId(), (int) $feedback_setting);

        if (!$this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            foreach ($this->getAnswerOptionsByAnswerIndex() as $index => $answer) {
                $this->saveSpecificAnswerFeedbackContent(
                    $this->questionOBJ->getId(),
                    0,
                    $index,
                    (string) ($form->getInput("feedback_answer_$index") ?? '')
                );
            }
        }
    }

    /**
     * returns the fact that the feedback editing form is saveable in page object editing mode,
     * because this question type has additional feedback settings
     */
    public function isSaveableInPageObjectEditingMode(): bool
    {
        return true;
    }

    /**
     * saves the given specific feedback setting for the given question id to the db.
     * (It#s stored to dataset of question itself)
     */
    public function saveSpecificFeedbackSetting(int $questionId, int $specificFeedbackSetting): void
    {
        $this->db->update(
            $this->getSpecificQuestionTableName(),
            ['feedback_setting' => ['integer', $specificFeedbackSetting]],
            ['question_fi' => ['integer', $questionId]]
        );
    }

    protected function cloneSpecificFeedback(int $source_question_id, int $target_question_id): void
    {
        $this->cloneSpecificFeedbackSetting($source_question_id, $target_question_id);
        parent::cloneSpecificFeedback($source_question_id, $target_question_id);
    }

    private function cloneSpecificFeedbackSetting(int $source_question_id, int $target_question_id): void
    {
        $res = $this->db->queryF(
            "SELECT feedback_setting FROM {$this->getSpecificQuestionTableName()} WHERE question_fi = %s",
            ['integer'],
            [$source_question_id]
        );

        $row = $this->db->fetchAssoc($res);

        if ($this->db->numRows($res) < 1) {
            return;
        }

        $this->db->update(
            $this->getSpecificQuestionTableName(),
            [ 'feedback_setting' => ['integer', $row['feedback_setting']] ],
            [ 'question_fi' => ['integer', $target_question_id] ]
        );
    }
}
