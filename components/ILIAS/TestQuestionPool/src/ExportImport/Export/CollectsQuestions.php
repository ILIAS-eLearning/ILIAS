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

declare(strict_types=1);

namespace ILIAS\TestQuestionPool\ExportImport\Export;

use assFormulaQuestionUnit;
use assFormulaQuestionUnitCategory;
use assQuestion;
use Generator;
use ilAssClozeTestFeedback;
use ilAssMultiOptionQuestionFeedback;
use ilAssQuestionSkillAssignmentList;
use ilAssSpecificFeedbackIdentifierList;
use ilDBInterface;
use ILIAS\Data\ObjectId;
use ILIAS\TestQuestionPool\ExportImport\Envelopes\Feedback;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties;
use ilUnitConfigurationRepository;

/**
 * Trait to collect questions and related data from a question pool or test object.
 */
trait CollectsQuestions
{
    /**
     * Get the question properties for all questions related to the target object.
     *
     * @return array<int, GeneralQuestionProperties>
     */
    abstract public function getQuestionProperties(): array;

    /**
     * Get the object ID of the object that contains the questions.
     */
    abstract public function getObjectId(): ObjectId;

    /**
     * Get the database interface.
     */
    abstract private function database(): ilDBInterface;


    private ?ilAssQuestionSkillAssignmentList $skill_assignments = null;

    /**
    * Collect the question objects by instantiating the question objects.
    *
    * @return Generator<assQuestion>
    */
    public function getQuestionObjects(): Generator
    {
        foreach ($this->getQuestionProperties() as $question) {
            yield assQuestion::instantiateQuestion($question->getQuestionId());
        }
    }

    /*
        Units
    */

    /**
     * Get all unit categories and units for a formula question.
     *
     * @return array{categories: list<assFormulaQuestionUnitCategory>, base_units: list<assFormulaQuestionUnit>, units: list<assFormulaQuestionUnit>}
     */
    public function getUnitsAndCategories(int $question_id): array
    {
        $repository = new ilUnitConfigurationRepository($question_id);
        $data = [
            'categories' => [],
            'base_units' => [],
            'units' => [],
        ];

        foreach ($repository->getCategorizedUnits() as $item) {
            if ($item instanceof assFormulaQuestionUnitCategory) {
                $data['categories'][] = $item;
            }

            if (!$item instanceof assFormulaQuestionUnit) {
                continue;
            }

            if ($item->getBaseUnit() === 0 || $item->getBaseUnit() === $item->getId()) {
                $data['base_units'][] = $item;
            } else {
                $data['units'][] = $item;
            }
        }

        return $data;
    }

    /*
        Feedback
    */

    /**
     * Collect the feedback content for a question and return it as a Feedback transfer object.
     */
    public function getFeedback(assQuestion $question): Feedback
    {
        return new Feedback(
            new Id($question->getId(), 'question'),
            $question->feedbackOBJ->getGenericFeedbackExportPresentation($question->getId(), false),
            $question->feedbackOBJ->getGenericFeedbackExportPresentation($question->getId(), true),
            $this->loadSpecificFeedback($question),
        );
    }

    private function loadSpecificFeedback(assQuestion $question): array
    {
        // Skip if specific feedback is not available or supported by the question type.
        if (
            !$question->feedbackOBJ instanceof ilAssMultiOptionQuestionFeedback ||
            !$question->feedbackOBJ->isSpecificAnswerFeedbackAvailable($question->getId())
        ) {
            return [];
        }

        $feedback = [];

        // Cloze question type specific feedback uses the identifier list to load the answer-specific feedback.
        if ($question->feedbackOBJ instanceof ilAssClozeTestFeedback) {
            $feedback_list = new ilAssSpecificFeedbackIdentifierList();
            $feedback_list->load($question->getId());

            foreach ($feedback_list as $identifier) {
                $feedback[$identifier->getAnswerIndex()] = [
                    'answer_index' => $identifier->getAnswerIndex(),
                    'question_index' => $identifier->getQuestionIndex(),
                    'feedback' => $question->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation(
                        $question->getId(),
                        $identifier->getQuestionIndex(),
                        $identifier->getAnswerIndex()
                    ),
                ];
            }

            return $feedback;
        }

        // Other question types with multiple answer options share the same approach
        foreach (array_keys($question->feedbackOBJ->getAnswerOptionsByAnswerIndex()) as $answer_index) {
            $feedback[$answer_index] = [
                'answer_index' => $answer_index,
                'question_index' => 0,
                'feedback' => $question->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation(
                    $question->getId(),
                    0,
                    $answer_index
                ),
            ];
        }
        return $feedback;
    }

    /*
        Skill Assignments
    */

    /**
     * @return array<\ilAssQuestionSkillAssignment>
     */
    public function getSkillAssignments(): array
    {
        if ($this->skill_assignments === null) {
            $this->skill_assignments = new ilAssQuestionSkillAssignmentList($this->database());
            $this->skill_assignments->setParentObjId($this->getObjectId()->toInt());
            $this->skill_assignments->loadFromDb();
            $this->skill_assignments->loadAdditionalSkillData();
        }

        $assignments = [];
        foreach ($this->getQuestionProperties() as $question) {
            $assignments = array_merge(
                $assignments,
                $this->skill_assignments->getAssignmentsByQuestionId($question->getQuestionId())
            );
        }

        return $assignments;
    }

    /*
        CO Page & Media Objects
    */

    public function getQuestionPageIds(): array
    {
        $question_page_ids = [];
        foreach ($this->getQuestionObjects() as $question) {
            $question_page_ids[] = "qpl:{$question->getId()}";
        }
        return $question_page_ids;
    }
}
