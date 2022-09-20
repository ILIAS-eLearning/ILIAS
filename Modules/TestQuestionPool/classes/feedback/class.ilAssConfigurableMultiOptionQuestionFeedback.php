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

require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssMultiOptionQuestionFeedback.php';

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

            require_once './Services/Form/classes/class.ilRadioGroupInputGUI.php';
            require_once './Services/Form/classes/class.ilRadioOption.php';

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
                $propertyLabel = $this->questionOBJ->prepareTextareaOutput(
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
                    $value = $this->questionOBJ->prepareTextareaOutput(
                        $this->getSpecificAnswerFeedbackContent($this->questionOBJ->getId(), 0, $index)
                    );
                }

                $form->getItemByPostVar("feedback_answer_$index")->setValue($value);
            }
        }
    }

    public function saveSpecificFormProperties(ilPropertyFormGUI $form): void
    {
        $this->saveSpecificFeedbackSetting($this->questionOBJ->getId(), $form->getInput('feedback_setting'));

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
            array('feedback_setting' => array('integer', $specificFeedbackSetting)),
            array('question_fi' => array('integer', $questionId))
        );
    }

    protected function duplicateSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId): void
    {
        $this->syncSpecificFeedbackSetting($originalQuestionId, $duplicateQuestionId);
        parent::duplicateSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }

    protected function syncSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId): void
    {
        $this->syncSpecificFeedbackSetting($duplicateQuestionId, $originalQuestionId);
        parent::syncSpecificFeedback($originalQuestionId, $duplicateQuestionId);
    }

    private function syncSpecificFeedbackSetting(int $sourceQuestionId, int $targetQuestionId): void
    {
        $res = $this->db->queryF(
            "SELECT feedback_setting FROM {$this->getSpecificQuestionTableName()} WHERE question_fi = %s",
            array('integer'),
            array($sourceQuestionId)
        );

        $row = $this->db->fetchAssoc($res);

        $this->db->update(
            $this->getSpecificQuestionTableName(),
            array( 'feedback_setting' => array('integer', $row['feedback_setting']) ),
            array( 'question_fi' => array('integer', $targetQuestionId) )
        );
    }
}
