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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\ObjectId;
use ILIAS\Data\UUID\Factory as UUIDFactory;
use ILIAS\Taxonomy\DomainService as Taxonomy;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Export\ExportPhase;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Export\ExportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Export\Exporter;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serialize\Serializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;
use ILIAS\TestQuestionPool\ExportImport\TransformationsBuilder;
use TestQuestionPool\ExportImport\Normalize\Processors\CollectQuestionImages;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

/**
 * Orchestrates normalization and serialization of a question pool export and copies its files.
 */
class QuestionPoolExporter implements Exporter
{
    public function __construct(
        private readonly TransformationsBuilder $builder,
        private readonly DataFactory $data_factory,
        private readonly GeneralQuestionPropertiesRepository $question_repository,
        private readonly \ilDBInterface $db,
        private readonly Taxonomy $taxonomy
    ) {
    }

    /**
     * Prepares the export transformations and question image collector.
     */
    public function prepare(ExportContext $context): void
    {
        $context->logger()->info('Preparing question pool export (1/3)...');
        $context->assertPhase(ExportPhase::Init);
        $context->setPhase(ExportPhase::Prepare);

        $pool_id = $this->extractObjectId($context);
        if ($pool_id === null) {
            $context->logger()->warning('No question pool object ID found for export');
            return;
        }

        $collector = new QuestionPoolCollector(
            $this->question_repository,
            $this->db,
            $pool_id
        );
        $context->setCollector($collector);

        $question_image_collector = new CollectQuestionImages(new UUIDFactory(), $pool_id);

        $transformations = $this->builder->forExport($question_image_collector);
        $context->setTransformations($transformations);

        $context->logger()->info('...Finished preparing question pool export (1/3)');
    }

    /**
     * Normalizes the question pool object and its questions and writes them to the serializer. It also collects the
     * dependencies of the export.
     */
    public function process(ExportContext $context): void
    {
        $context->logger()->info('Processing question pool export (2/3)...');
        $context->assertPhase(ExportPhase::Prepare);
        $context->setPhase(ExportPhase::Process);

        $context->serializer()->group(
            'general',
            fn() => $this->exportObject(
                $this->collector($context),
                $context->transformations(),
                $context->serializer(),
                $context
            )
        );
        $context->serializer()->group(
            'questions',
            fn() => $this->exportQuestions(
                $this->collector($context),
                $context->transformations(),
                $context->serializer(),
                $context
            )
        );
        $context->serializer()->group(
            'skill_assignments',
            fn() => $this->exportSkillAssignments(
                $this->collector($context),
                $context->transformations(),
                $context->serializer(),
            )
        );

        $context->logger()->info('...Finished processing question pool export (2/3)');
    }

    /**
     * Finalizes the export by copying the question images to the export directory and returning the export context.
     */
    public function write(ExportContext $context): void
    {
        $context->logger()->info('Writing question pool export (3/3)...');
        $context->assertPhase(ExportPhase::Process);
        $context->setPhase(ExportPhase::Write);

        $export_dir = $context->path()->getPathToComponentExpDirInContainer();
        $question_image_collector = $context->transformations()
            ->normalizationProcessor(CollectQuestionImages::class);
        if (!($question_image_collector instanceof CollectQuestionImages)) {
            throw new \LogicException('Unexpected question image processor');
        }

        foreach ($question_image_collector->getFiles() as $file) {
            $context->writer()->writeFileByFilePath(
                $file['from'],
                "{$export_dir}/" . $file['to']
            );
        }

        $context->logger()->info('...Finished writing question pool export (3/3)');
    }


    private function extractObjectId(ExportContext $context): ?ObjectId
    {
        $target_ids = $context->target()->getObjectIds();

        if ($target_ids === []) {
            $context->logger()->warning('No target object IDs found for question pool export');
            return null;
        }

        if (count($target_ids) > 1) {
            $context->logger()->warning(
                'Multiple target object IDs found for question pool export. Only the first one will be used.'
            );
        }

        return $this->data_factory->objId(array_shift($target_ids));
    }

    private function collector(ExportContext $context): QuestionPoolCollector
    {
        $collector = $context->collector();
        if (!($collector instanceof QuestionPoolCollector)) {
            throw new \LogicException('Unexpected question pool collector');
        }

        return $collector;
    }

    private function exportObject(
        QuestionPoolCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
        ExportContext $context
    ): void {
        $serializer->append('object', $transformations->normalize($collector->getObject()));

        $obj_id = $collector->getObjectId()->toInt();

        $context->addDependency('components/ILIAS/ILIASObject', 'common', [$obj_id]);
        $context->addDependency('components/ILIAS/MetaData', 'qpl', ["{$obj_id}:0:qpl"]);
        $context->addDependency(
            'components/ILIAS/Taxonomy',
            'tax',
            $this->taxonomy->getUsageOfObject($obj_id)
        );
    }

    private function exportQuestions(
        QuestionPoolCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
        ExportContext $context
    ): void {
        foreach ($collector->getQuestionObjects() as $question) {
            $normalized = [
                ... $transformations->normalize($question),
                'feedback' => $transformations->normalize($collector->getFeedback($question))
            ];

            if ($question instanceof \assFormulaQuestion) {
                $normalized['formula_data'] = $transformations->normalize(
                    $collector->getUnitsAndCategories($question->getId())
                );
            }

            $serializer->append('question', $normalized);
            $context->addDependency('components/ILIAS/COPage', 'pg', ["qpl:{$question->getId()}"]);
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
