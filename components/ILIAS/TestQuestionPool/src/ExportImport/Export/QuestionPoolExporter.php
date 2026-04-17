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

use assFormulaQuestion;
use ilDBInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\ObjectId;
use ILIAS\Data\UUID\Factory as UUIDFactory;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Bridge\ExportState;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Bridge\ExportStep;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Builder;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Exporter;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Serializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\Taxonomy\DomainService as Taxonomy;
use ILIAS\TestQuestionPool\ExportImport\Pipes\CollectQuestionImages;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

/**
 * Orchestrates the export of a question pool. It uses the Builder to create a pipeline of transformations that are used
 * to normalize the data and then writes the normalized data to the serializer. It also copies the needed files to the
 * export directory.
 */
class QuestionPoolExporter implements Exporter
{
    public function __construct(
        private readonly Builder $builder,
        private readonly DataFactory $data_factory,
        private readonly GeneralQuestionPropertiesRepository $question_repository,
        private readonly ilDBInterface $db,
        private readonly Taxonomy $taxonomy
    ) {
    }

    /**
     * Prepares the export by creating the transformations and the question image pipe.
     */
    public function prepare(ExportState $state): void
    {
        $state->assertStep(ExportStep::INIT);
        $state->setStep(ExportStep::PREPARE);

        $pool_id = $this->extractObjectId($state);
        if ($pool_id === null) {
            return;
        }

        $collector = new QuestionPoolCollector(
            $this->question_repository,
            $this->db,
            $pool_id
        );
        $state->setCollector($collector);

        $question_image_pipe = new CollectQuestionImages(
            new UUIDFactory(),
            $pool_id
        );

        $transformations = $this->builder
            ->withAdditionalPipes([$question_image_pipe])
            ->create();
        $state->setTransformations($transformations);
    }

    /**
     * Normalizes the question pool object and its questions and writes them to the serializer. It also collects the
     * dependencies of the export.
     */
    public function process(ExportState $state): void
    {
        $state->assertStep(ExportStep::PREPARE);
        $state->setStep(ExportStep::PROCESS);

        $state->serializer()->group(
            'general',
            fn() => $this->exportObject(
                $state->collector(),
                $state->transformations(),
                $state->serializer(),
                $state
            )
        );
        $state->serializer()->group(
            'questions',
            fn() => $this->exportQuestions(
                $state->collector(),
                $state->transformations(),
                $state->serializer(),
                $state
            )
        );
        $state->serializer()->group(
            'skill_assignments',
            fn() => $this->exportSkillAssignments(
                $state->collector(),
                $state->transformations(),
                $state->serializer(),
            )
        );
    }

    /**
     * Finalizes the export by copying the question images to the export directory and returning the export context.
     */
    public function write(ExportState $state): void
    {
        $state->assertStep(ExportStep::PROCESS);
        $state->setStep(ExportStep::WRITE);

        $export_dir = $state->path()->getPathToComponentExpDirInContainer();
        $question_image_pipe = $state->transformations()->context(CollectQuestionImages::class);

        foreach ($question_image_pipe->getFiles() as $file) {
            $state->writer()->writeFileByFilePath(
                $file['from'],
                "{$export_dir}/" . $file['to']
            );
        }
    }


    private function extractObjectId(ExportState $state): ?ObjectId
    {
        $target_ids = $state->target()->getObjectIds();

        if (count($target_ids) === 0) {
            $state->logger()->warning('No target object IDs found for question pool export');
            return null;
        }

        if (count($target_ids) > 1) {
            $state->logger()->warning(
                'Multiple target object IDs found for question pool export. Only the first one will be used.'
            );
        }

        return $this->data_factory->objId(array_shift($target_ids));
    }

    private function exportObject(
        QuestionPoolCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
        ExportState $state
    ): void {
        $serializer->append('object', $transformations->normalize($collector->getObject()));

        $obj_id = $collector->getObjectId()->toInt();

        $state->addDependency('components/ILIAS/ILIASObject', 'common', [$obj_id]);
        $state->addDependency('components/ILIAS/MetaData', 'qpl', ["{$obj_id}:0:qpl"]);
        $state->addDependency(
            'components/ILIAS/Taxonomy',
            'tax',
            $this->taxonomy->getUsageOfObject($obj_id)
        );
    }

    private function exportQuestions(
        QuestionPoolCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
        ExportState $state
    ): void {
        foreach ($collector->getQuestionObjects() as $question) {
            $normalized = [
                ... $transformations->normalize($question),
                'feedback' => $transformations->normalize(
                    $collector->getFeedback($question)
                )
            ];

            if ($question instanceof assFormulaQuestion) {
                $data = $collector->getUnitsAndCategories($question->getId());
                $normalized['formula_data'] = $transformations->normalize($data);
            }

            $serializer->append('question', $normalized);
            $state->addDependency('components/ILIAS/COPage', 'pg', ["qpl:{$question->getId()}"]);
        }
    }

    private function exportSkillAssignments(
        QuestionPoolCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
    ): void {
        foreach ($collector->getSkillAssignments() as $assignment) {
            $serializer->append('skill_assignment', $transformations->normalize($assignment));
        }
    }
}
