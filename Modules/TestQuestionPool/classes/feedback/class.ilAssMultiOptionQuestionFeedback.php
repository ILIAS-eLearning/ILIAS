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
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @abstract
 */
abstract class ilAssMultiOptionQuestionFeedback extends ilAssQuestionFeedback
{
    /**
     * table name for specific feedback
     */
    public const TABLE_NAME_SPECIFIC_FEEDBACK = 'qpl_fb_specific';

    /**
     * returns the html of SPECIFIC feedback for the given question id
     * and answer index for test presentation
     */
    public function getSpecificAnswerFeedbackTestPresentation(int $question_id, int $question_index, int $answer_index): string
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            return $this->cleanupPageContent(
                $this->getPageObjectContent(
                    $this->getSpecificAnswerFeedbackPageObjectType(),
                    $this->getSpecificAnswerFeedbackPageObjectId($question_id, $question_index, $answer_index)
                )
            );
        }

        return $this->getSpecificAnswerFeedbackContent(
            $question_id,
            $question_index,
            $answer_index
        );
    }

    /**
     * completes a given form object with the specific form properties
     * required by this question type
     */
    public function completeSpecificFormProperties(ilPropertyFormGUI $form): void
    {
        if (!$this->questionOBJ->getSelfAssessmentEditingMode()) {
            $header = new ilFormSectionHeaderGUI();
            $header->setTitle($this->lng->txt('feedback_answers'));
            $form->addItem($header);

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

    /**
     * initialises a given form object's specific form properties
     * relating to this question type
     */
    public function initSpecificFormProperties(ilPropertyFormGUI $form): void
    {
        if (!$this->questionOBJ->getSelfAssessmentEditingMode()) {
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

    public function getSpecificAnswerFeedbackContent(int $question_id, int $question_index, int $answer_index): string
    {
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getSpecificFeedbackTableName()}
					WHERE question_fi = %s AND question = %s AND answer = %s",
            ['integer', 'integer', 'integer'],
            [$question_id, $question_index, $answer_index]
        );

        $feedback_content = '';

        if ($this->db->numRows($res) > 0) {
            $row = $this->db->fetchAssoc($res);
            $feedback_content = ilRTE::_replaceMediaObjectImageSrc(
                $this->questionOBJ->getHtmlQuestionContentPurifier()->purify($row['feedback'] ?? ''),
                1
            );
        }

        return $feedback_content;
    }

    public function getAllSpecificAnswerFeedbackContents(int $question_id): string
    {
        $res = $this->db->queryF(
            "SELECT feedback FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );

        $allFeedbackContents = '';

        while ($row = $this->db->fetchAssoc($res)) {
            $allFeedbackContents .= ilRTE::_replaceMediaObjectImageSrc($row['feedback'] ?? '', 1);
        }

        return $allFeedbackContents;
    }

    public function getAllSpecificAnswerPageEditorFeedbackContents(int $question_id): string
    {
        $feedback_identifiers = new ilAssSpecificFeedbackIdentifierList();
        $feedback_identifiers->load($question_id);

        $all_feedback_content = '';
        foreach ($feedback_identifiers as $identifier) {
            $feedback_content = $this->getPageObjectContent(
                $this->getSpecificAnswerFeedbackPageObjectType(),
                $identifier->getFeedbackId()
            );
            $all_feedback_content .= $this->cleanupPageContent($feedback_content);
        }

        return $all_feedback_content;
    }

    public function saveSpecificAnswerFeedbackContent(int $question_id, int $question_index, int $answer_index, string $feedback_content): int
    {
        if ($feedback_content !== '') {
            $feedback_content = ilRTE::_replaceMediaObjectImageSrc(
                $this->questionOBJ->getHtmlQuestionContentPurifier()->purify($feedback_content),
                0
            );
        }

        $feedback_id = $this->getSpecificAnswerFeedbackId($question_id, $question_index, $answer_index);

        if ($feedback_id !== -1) {
            $this->db->update(
                $this->getSpecificFeedbackTableName(),
                [
                    'feedback' => ['text', $feedback_content],
                    'tstamp' => ['integer', time()]
                ],
                [
                    'feedback_id' => ['integer', $feedback_id],
                ]
            );
        } else {
            $feedback_id = $this->db->nextId($this->getSpecificFeedbackTableName());

            $this->db->insert($this->getSpecificFeedbackTableName(), [
                'feedback_id' => ['integer', $feedback_id],
                'question_fi' => ['integer', $question_id],
                'question' => ['integer', $question_index],
                'answer' => ['integer', $answer_index],
                'feedback' => ['text', $feedback_content],
                'tstamp' => ['integer', time()]
            ]);
        }

        return $feedback_id;
    }

    public function deleteSpecificAnswerFeedbacks(int $question_id, bool $is_additional_content_editing_mode_page_object): void
    {
        if ($is_additional_content_editing_mode_page_object) {
            $feedback_identifiers = new ilAssSpecificFeedbackIdentifierList();
            $feedback_identifiers->load($question_id);

            foreach ($feedback_identifiers as $identifier) {
                $this->ensurePageObjectDeleted(
                    $this->getSpecificAnswerFeedbackPageObjectType(),
                    $identifier->getFeedbackId()
                );
            }
        }

        $this->db->manipulateF(
            "DELETE FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );
    }

    protected function duplicateSpecificFeedback(int $original_question_id, int $duplicate_question_id): void
    {
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            ['integer'],
            [$original_question_id]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $next_id = $this->db->nextId($this->getSpecificFeedbackTableName());

            $this->db->insert($this->getSpecificFeedbackTableName(), [
                'feedback_id' => ['integer', $next_id],
                'question_fi' => ['integer', $duplicate_question_id],
                'question' => ['integer', $row['question']],
                'answer' => ['integer', $row['answer']],
                'feedback' => ['text', $row['feedback']],
                'tstamp' => ['integer', time()]
            ]);

            if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
                $pageObjectType = $this->getSpecificAnswerFeedbackPageObjectType();
                $this->duplicatePageObject($pageObjectType, $row['feedback_id'], $next_id, $duplicate_question_id);
            }
        }
    }

    protected function syncSpecificFeedback(int $original_question_id, int $duplicate_question_id): void
    {
        // delete specific feedback of the original
        $this->db->manipulateF(
            "DELETE FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            ['integer'],
            [$original_question_id]
        );

        // get specific feedback of the actual question
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            ['integer'],
            [$duplicate_question_id]
        );

        // save specific feedback to the original
        while ($row = $this->db->fetchAssoc($res)) {
            $next_id = $this->db->nextId($this->getSpecificFeedbackTableName());

            $this->db->insert($this->getSpecificFeedbackTableName(), [
                'feedback_id' => ['integer', $next_id],
                'question_fi' => ['integer', $original_question_id],
                'question' => ['integer', $row['question']],
                'answer' => ['integer', $row['answer']],
                'feedback' => ['text', $row['feedback']],
                'tstamp' => ['integer', time()]
            ]);
        }
    }

    final protected function getSpecificAnswerFeedbackId(int $question_id, int $question_index, int $answer_index): int
    {
        $res = $this->db->queryF(
            "SELECT feedback_id FROM {$this->getSpecificFeedbackTableName()}
					WHERE question_fi = %s AND question = %s AND answer = %s",
            ['integer', 'integer', 'integer'],
            [$question_id, $question_index, $answer_index]
        );

        $row = $this->db->fetchAssoc($res);
        return $row['feedback_id'] ?? -1;
    }

    /**
     *
     * @param array<int> $feedback_ids
     * @return array<int, string>
     */
    protected function getSpecificFeedbackContentForFeedbackIds(array $feedback_ids): array
    {
        $res = $this->db->query(
            "SELECT feedback_id, feedback FROM {$this->getSpecificFeedbackTableName()} WHERE "
                . $this->db->in('feedback_id', $feedback_ids, false, ilDBConstants::T_INTEGER)
        );

        $content = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $content[$row['feedback_id']] = $row['feedback'];
        }
        return $content;
    }

    protected function isSpecificAnswerFeedbackId(int $feedback_id): bool
    {
        $row = $this->db->fetchAssoc($this->db->queryF(
            "SELECT COUNT(feedback_id) cnt FROM {$this->getSpecificFeedbackTableName()}
					WHERE question_fi = %s AND feedback_id = %s",
            ['integer', 'integer'],
            [$this->questionOBJ->getId(), $feedback_id]
        ));

        return (bool) $row['cnt'];
    }

    final protected function getSpecificFeedbackTableName(): string
    {
        return self::TABLE_NAME_SPECIFIC_FEEDBACK;
    }

    public function getAnswerOptionsByAnswerIndex(): array
    {
        return $this->questionOBJ->getAnswers();
    }

    protected function buildAnswerOptionLabel(int $index, $answer): string
    {
        return $answer->getAnswertext();
    }

    /**
     * returns a useable page object id for specific answer feedback page objects
     * for the given question id and answer index
     * (using the id sequence of non page object specific answer feedback)
     */
    final protected function getSpecificAnswerFeedbackPageObjectId(int $question_id, int $question_index, int $answer_index): int
    {
        $page_object_id = $this->getSpecificAnswerFeedbackId($question_id, $question_index, $answer_index);

        if ($page_object_id === -1) {
            $page_object_id = $this->saveSpecificAnswerFeedbackContent($question_id, $question_index, $answer_index, '');
        }

        return $page_object_id;
    }

    public function getSpecificAnswerFeedbackExportPresentation(int $question_id, int $question_index, int $answer_index): string
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            return $this->getPageObjectXML(
                $this->getSpecificAnswerFeedbackPageObjectType(),
                $this->getSpecificAnswerFeedbackPageObjectId($question_id, $question_index, $answer_index)
            );
        }

        return $this->getSpecificAnswerFeedbackContent(
            $question_id,
            $question_index,
            $answer_index
        );
    }

    public function importSpecificAnswerFeedback(int $question_id, int $question_index, int $answer_index, string $feedback_content): void
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $page_object_id = $this->getSpecificAnswerFeedbackPageObjectId($question_id, $question_index, $answer_index);
            $pageObjectType = $this->getSpecificAnswerFeedbackPageObjectType();

            $this->createPageObject($pageObjectType, $page_object_id, $feedback_content);
        } else {
            $this->saveSpecificAnswerFeedbackContent($question_id, $question_index, $answer_index, $feedback_content);
        }
    }

    public function specificAnswerFeedbackExists(): bool
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $all_feedback_content = $this->getAllSpecificAnswerPageEditorFeedbackContents($this->questionOBJ->getId());
        } else {
            $all_feedback_content = $this->getAllSpecificAnswerFeedbackContents($this->questionOBJ->getId());
        }

        return $all_feedback_content !== '';
    }
}
