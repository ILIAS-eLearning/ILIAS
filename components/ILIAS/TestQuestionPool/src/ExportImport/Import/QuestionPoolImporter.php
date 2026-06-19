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

namespace ILIAS\TestQuestionPool\ExportImport\Import;

use ilCtrl;
use ilDBInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\ReferenceId;
use ILIAS\Data\UUID\Factory;
use ILIAS\Language\Language;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Builder;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Deserializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\IdMappingPipe;
use ILIAS\TestQuestionPool\ExportImport\Import\QuestionSelectionStage;
use ILIAS\TestQuestionPool\ExportImport\Import\UploadValidationStage;
use ILIAS\TestQuestionPool\ExportImport\Pipes\CollectQuestionImages;
use ilImportMapping;
use ilObjQuestionPool;
use Psr\Log\LoggerInterface;

/**
 * Orchestrates the import of a question pool. It uses the Builder to create a pipeline of transformations that are used
 * to normalize the data provided by the deserializer. It imports the question pool object and its content (questions,
 * skill assignments, etc.) into the database using repository classes and legacy active record models.
 */
class QuestionPoolImporter
{
    public function __construct(
        private readonly Builder $builder,
        private readonly LoggerInterface $log,
        private readonly DataFactory $data_factory,
        private readonly QuestionsImporter $questions_importer,
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
        $id_mapping_pipe = new IdMappingPipe($mapping, 'components/ILIAS/TestQuestionPool', $this->log);
        $images_pipe = new CollectQuestionImages(new Factory(), $this->data_factory->objId(0));
        $tt = $this->builder->withAdditionalPipes(append: [$id_mapping_pipe, $images_pipe])->create();

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
                    $this->questions_importer->importQuestion(
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
            function (array $assignments) use ($tt, $mapping, &$context): void {
                $result = $this->skill_importer->import(
                    $assignments,
                    UploadValidationStage::getInstallId($context),
                    $tt,
                    $mapping,
                );
                $context = $context->with('skill_assignments', $result);
            }
        );

        $this->log->info('Importing question pool export file...');
        $deserializer->process();
        $this->log->info('...Finished importing question pool export file');

        $this->log->info('Importing question images...');
        $this->questions_importer->importQuestionImages(
            $context->get('pool_obj_id'),
            $mapping,
            $context,
            $images_pipe
        );
        $this->log->info('...Finished importing question images');

        $this->log->info("Finished importing question pool {$context->get('pool_obj_id')} (Object ID)");
        return $context;
    }

    /**
     * Finalize the import after all dependencies have been imported.
     * It will replace the old question ids with the new question ids in the question pages.
     */
    public function finalize(ilImportMapping $mapping): void
    {
        $this->log->info('Finalizing question pool import...');
        $this->questions_importer->finalizeQuestionPages($mapping);
        $this->log->info('...Finished finalizing question pool');
    }

    protected function importQuestionPool(
        array $normalized,
        Transformations $transformations,
        ilImportMapping $mapping,
        ReferenceId $parent_id
    ): int {
        $pool_object = $transformations->denormalize($normalized, ilObjQuestionPool::class);
        $old_pool_id = $pool_object->getId();

        $pool_object->setTitle($pool_object->getTitle());
        $new_pool_id = $pool_object->create(true);
        $pool_object->getObjectProperties()->storePropertyIsOnline(
            $pool_object->getObjectProperties()->getPropertyIsOnline()->withOffline()
        );
        $pool_object->saveToDb();
        $this->log->debug("Created new pool object: {$old_pool_id} -> {$new_pool_id}");

        $pool_object->createReference();
        $pool_object->putInTree($parent_id->toInt());
        $pool_object->setPermissions($parent_id->toInt());
        $this->log->debug("Stored pool object in tree: {$parent_id->toInt()} (Parent Ref) -> {$pool_object->getRefId()} (Pool Ref)");

        $mapping->addMapping('components/ILIAS/TestQuestionPool', 'qpl', (string) $old_pool_id, (string) $new_pool_id);
        $mapping->addMapping('components/ILIAS/TestQuestionPool', 'object', (string) $old_pool_id, (string) $new_pool_id);
        $mapping->addMapping('components/ILIAS/MetaData', 'md', "{$old_pool_id}:0:qpl", "{$new_pool_id}:0:qpl");

        return $new_pool_id;
    }
}
