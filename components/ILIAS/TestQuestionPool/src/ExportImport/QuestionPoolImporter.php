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

namespace ILIAS\TestQuestionPool\ExportImport;

use assQuestion;
use ilCtrl;
use ilDBInterface;
use ILIAS\Data\ReferenceId;
use ILIAS\Language\Language;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Builder;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Deserializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\IdMappingPipe;
use ILIAS\TestQuestionPool\ExportImport\Envelopes\Feedback;
use ILIAS\TestQuestionPool\ExportImport\Import\QuestionSelectionStage;
use ILIAS\TestQuestionPool\ExportImport\Import\UploadValidationStage;
use ilImportMapping;
use ilObjQuestionPool;

/**
 * Orchestrates the import of a question pool. It uses the Builder to create a pipeline of transformations that are used
 * to normalize the data provided by the deserializer. It imports the question pool object and its content (questions,
 * skill assignments, etc.) into the database using repository classes and legacy active record models.
 */
class QuestionPoolImporter
{
    private const string COMPONENT = 'components/ILIAS/TestQuestionPool';

    public function __construct(
        private readonly Builder $builder,
        private readonly ilCtrl $ctrl,
        private readonly ilDBInterface $database,
        private readonly Language $language,
        private readonly SkillAssignmentsImporter $skill_importer,
    ) {
    }

    /**
     * Import a question pool from a deserializer instance. It will import the question pool object, questions and skill
     * assignments into the database.
     */
    public function import(
        Deserializer $deserializer,
        ilImportMapping $mapping,
        ReferenceId $parent_id,
        ImportContext $context
    ): ImportContext {
        $id_mapping_pipe = new IdMappingPipe($mapping, self::COMPONENT);
        $tt = $this->builder->withAdditionalPipes(append: [$id_mapping_pipe])->create();

        $selected_questions = QuestionSelectionStage::getSelectedQuestions($context);

        $deserializer->addHandler(
            'general',
            function (array $objects) use ($tt, $mapping, $parent_id, &$context): void {
                $new_pool_id = $this->importQuestionPool(
                    array_pop($objects),
                    $tt,
                    $mapping,
                    $parent_id
                );
                $context = $context->with('pool_obj_id', $new_pool_id);
            }
        );

        $deserializer->addHandler(
            'questions',
            function (array $questions) use ($tt, $mapping, $selected_questions): void {
                foreach ($questions as $question) {
                    $this->importQuestion(
                        $question,
                        $tt,
                        $mapping,
                        $selected_questions
                    );
                }
            }
        );

        $deserializer->addHandler(
            'skill_assignments',
            function (array $assignments) use ($tt, &$context): void {
                $result = $this->skill_importer->import(
                    $assignments,
                    UploadValidationStage::getInstallId($context),
                    $tt,
                );
                $context = $context->with('skill_assignments', $result);
            }
        );

        $deserializer->process();

        return $context;
    }

    protected function importQuestionPool(
        array $normalized,
        Transformations $transformations,
        ilImportMapping $mapping,
        ReferenceId $parent_id
    ): int {
        $pool_object = $transformations->denormalize($normalized, ilObjQuestionPool::class);
        $old_pool_id = $pool_object->getId();

        $pool_object->setTitle('Imported'); //TODO: Remove after testing
        $new_pool_id = $pool_object->create(true);
        $pool_object->getObjectProperties()->storePropertyIsOnline(
            $pool_object->getObjectProperties()->getPropertyIsOnline()->withOffline()
        );
        $pool_object->saveToDb();

        $pool_object->createReference();
        $pool_object->putInTree($parent_id->toInt());
        $pool_object->setPermissions($parent_id->toInt());

        $mapping->addMapping(self::COMPONENT, 'qpl', (string) $old_pool_id, (string) $new_pool_id);
        $mapping->addMapping(self::COMPONENT, 'object', (string) $old_pool_id, (string) $new_pool_id);
        $mapping->addMapping('components/ILIAS/MetaData', 'md', "{$old_pool_id}:0:qpl", "{$new_pool_id}:0:qpl");

        return $new_pool_id;
    }

    protected function importQuestion(
        array $normalized,
        Transformations $transformations,
        ilImportMapping $mapping,
        array $selected_questions
    ): void {
        $question_class = $normalized['type'];
        if (!class_exists($question_class)) {
            throw new \InvalidArgumentException("Question class {$question_class} does not exist");
        }

        /** @var assQuestion $question */
        $question = $transformations->denormalize($normalized, new $question_class());
        $old_question_id = $question->getId();
        if (!in_array($old_question_id, $selected_questions)) {
            return;
        }

        $feedback_class = $question::getFeedbackClassNameByQuestionType($question->getQuestionType());
        $question->feedbackOBJ = new $feedback_class($question, $this->ctrl, $this->database, $this->language);

        $new_question_id = $question->createNewQuestion(false);
        $question->saveToDb();

        $mapping->addMapping(self::COMPONENT, 'question', (string) $old_question_id, (string) $new_question_id);
        $mapping->addMapping(self::COMPONENT, 'question_assignment', (string) $new_question_id, (string) $question->getObjId());
        $mapping->addMapping('components/ILIAS/Taxonomy', 'tax_item', "qpl:quest:{$old_question_id}", (string) $new_question_id);
        $mapping->addMapping('components/ILIAS/Taxonomy', 'tax_item_obj_id', "qpl:quest:{$old_question_id}", (string) $question->getObjId());
        $mapping->addMapping('components/ILIAS/COPage', 'pg', "qpl:{$old_question_id}", "qpl:{$new_question_id}");

        $feedback = $transformations->denormalize($normalized['feedback'], Feedback::class);
        $this->importFeedback($feedback, $question);
    }

    protected function importFeedback(Feedback $feedback, assQuestion $question): void
    {
        $question_id = $question->getId();
        $question->feedbackOBJ->importGenericFeedback($question_id, false, $feedback->getGenericUncompleted());
        $question->feedbackOBJ->importGenericFeedback($question_id, true, $feedback->getGenericCompleted());

        foreach ($feedback->getSpecificFeedback() as $specific_feedback) {
            $question->feedbackOBJ->importSpecificAnswerFeedback(
                $question_id,
                (int) $specific_feedback['question_index'],
                (int) $specific_feedback['answer_index'],
                $specific_feedback['feedback']
            );
        }
    }
}
