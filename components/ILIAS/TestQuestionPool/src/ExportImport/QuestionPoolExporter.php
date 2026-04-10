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

use assFormulaQuestion;
use ilDBInterface;
use ILIAS\Data\ObjectId;
use ILIAS\Data\UUID\Factory as UUIDFactory;
use ILIAS\Export\ExportHandler\I\Consumer\ExportWriter\HandlerInterface as ExportWriter;
use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\CollectionInterface as ExportConfig;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Builder;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Serializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\ExportContext;
use ILIAS\Taxonomy\DomainService as Taxonomy;
use ILIAS\TestQuestionPool\ExportImport\Pipes\CollectQuestionImages;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

/**
 * Orchestrates the export of a question pool. It uses the Builder to create a pipeline of transformations that are used
 * to normalize the data and then writes the normalized data to the serializer. It also copies the needed files to the
 * export directory.
 */
class QuestionPoolExporter
{
    public function __construct(
        private readonly Builder $builder,
        private readonly GeneralQuestionPropertiesRepository $question_repository,
        private readonly ilDBInterface $db,
        private readonly Taxonomy $taxonomy
    ) {
    }

    /**
     * Performs the export for a given question pool. It returns the export context which contains the serialized data
     * and the dependencies of the export.
     */
    public function export(
        ObjectId $pool_id,
        ExportConfig $config,
        Serializer $serializer,
        ExportWriter $writer,
        string $export_dir
    ): ExportContext {
        $context = $this->prepare($pool_id, $config);
        $context = $this->process($context, $serializer);
        return $this->write($context, $writer, $export_dir);
    }

    /**
     * Prepares the export context by creating the transformations and the question image pipe. It returns the export
     * context which is used to share the context between the prepare, process and write steps.
     */
    public function prepare(ObjectId $pool_id, ExportConfig $config): ExportContext
    {
        $question_image_pipe = new CollectQuestionImages(
            new UUIDFactory(),
            $pool_id
        );

        $transformations = $this->builder->withAdditionalPipes([$question_image_pipe])
            ->create();

        return new ExportContext($pool_id, $config, $transformations);
    }

    /**
     * Normalizes the question pool object and its questions and writes them to the serializer. It also collects the
     * dependencies of the export.
     */
    public function process(ExportContext $context, Serializer $serializer): ExportContext
    {
        $context->setSerializer($serializer);
        $tt = $context->getTransformations();

        $collector = new QuestionPoolCollector(
            $this->question_repository,
            $this->db,
            $context->getPoolId()
        );

        $serializer->group(
            'general',
            fn() => $this->exportObject($collector, $tt, $serializer, $context)
        );
        $serializer->group(
            'questions',
            fn() => $this->exportQuestions($collector, $tt, $serializer, $context)
        );
        $serializer->group(
            'skill_assignments',
            fn() => $this->exportSkillAssignments($collector, $tt, $serializer)
        );

        return $context;
    }

    /**
     * Finalizes the export by copying the question images to the export directory and returning the export context.
     */
    public function write(ExportContext $export, ExportWriter $writer, string $export_dir): ExportContext
    {
        // Copy the question images to the export directory
        $question_image_pipe = $export->getTransformations()->context(CollectQuestionImages::class);
        foreach ($question_image_pipe->getFiles() as $file) {
            $writer->writeFileByFilePath($file['from'], "{$export_dir}/" . $file['to']);
        }

        return $export;
    }


    protected function exportObject(
        QuestionPoolCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
        ExportContext $export
    ): void {
        $serializer->append('object', $transformations->normalize($collector->getObject()));

        $obj_id = $collector->getPoolId()->toInt();

        $export->addDependency('components/ILIAS/ILIASObject', 'common', [$obj_id]);
        $export->addDependency('components/ILIAS/MetaData', 'qpl', ["{$obj_id}:0:qpl"]);
        $export->addDependency(
            'components/ILIAS/Taxonomy',
            'tax',
            $this->taxonomy->getUsageOfObject($obj_id)
        );
    }

    protected function exportQuestions(
        QuestionPoolCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
        ExportContext $export
    ): void {
        foreach ($collector->getQuestionObjects() as $question) {
            $normalized = [
                ... $transformations->normalize($question),
                'feedback' => $transformations->normalize(
                    $collector->getFeedback($question)
                )
            ];

            if ($question instanceof assFormulaQuestion) {
                $normalized['formula_data'] = $transformations->normalize($collector->getUnitsAndCategories($question->getId()));
            }

            $serializer->append('question', $normalized);
            $export->addDependency('components/ILIAS/COPage', 'pg', ["qpl:{$question->getId()}"]);
        }
    }

    protected function exportSkillAssignments(
        QuestionPoolCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
    ): void {
        foreach ($collector->getSkillAssignments() as $assignment) {
            $serializer->append('skill_assignment', $transformations->normalize($assignment));
        }
    }
}
