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

namespace ILIAS\Test\ExportImport;

use assFormulaQuestion;
use ilDBInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\ObjectId;
use ILIAS\Data\UUID\Factory as UUIDFactory;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Taxonomy\DomainService as Taxonomy;
use ILIAS\Test\ExportImport\Pipes\CollectUserIds;
use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\Test\Questions\Properties\Repository as QuestionsRepository;
use ILIAS\Test\Results\Data\Repository as ResultsRepository;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Bridge\ExportState;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Bridge\ExportStep;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Builder;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Exporter;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Serializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\CollectResources;
use ILIAS\TestQuestionPool\ExportImport\Pipes\CollectQuestionImages;

class TestExporter implements Exporter
{
    public function __construct(
        private readonly Builder $builder,
        private readonly DataFactory $data_factory,
        private readonly ilDBInterface $db,
        private readonly IRSS $irss,
        private readonly ParticipantRepository $participant_repository,
        private readonly ResultsRepository $results_repository,
        private readonly QuestionsRepository $questions_repository,
        private readonly Taxonomy $taxonomy
    ) {
    }

    /**
     * @inheritDoc
     */
    public function prepare(ExportState $state): void
    {
        $state->assertStep(ExportStep::INIT);
        $state->setStep(ExportStep::PREPARE);

        $object_id = $this->extractObjectId($state);
        if ($object_id === null) {
            return;
        }

        $collector = new TestCollector(
            $this->participant_repository,
            $this->results_repository,
            $this->questions_repository,
            $this->db,
            $object_id
        );
        $state->setCollector($collector);

        $transformations = $this->builder
            ->withAdditionalPipes([
                new CollectUserIds(),
                new CollectQuestionImages(
                    new UUIDFactory(),
                    $object_id
                ),
                new CollectResources($this->irss),
            ])
            ->create();

        $state->setTransformations($transformations);
    }

    private function extractObjectId(ExportState $state): ?ObjectId
    {
        $target_ids = $state->target()->getObjectIds();

        if (count($target_ids) === 0) {
            $state->logger()->warning('No target object IDs found for test export');
            return null;
        }

        if (count($target_ids) > 1) {
            $state->logger()->warning(
                'Multiple target object IDs found for test export. Only the first one will be used.'
            );
        }

        return $this->data_factory->objId(array_shift($target_ids));
    }

    /**
     * @inheritDoc
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
            'settings',
            fn() => $this->exportSettings(
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
        $state->serializer()->group(
            'skill_thresholds',
            fn() => $this->exportSkillLevelThresholds(
                $state->collector(),
                $state->transformations(),
                $state->serializer(),
            )
        );

        if ($state->getOption() === Types::XML_WITH_RESULTS->value) {
            $this->processResults($state);
        }
    }

    private function processResults(ExportState $state): void
    {
        $state->serializer()->group(
            'participants',
            fn() => $this->exportParticipants(
                $state->collector(),
                $state->transformations(),
                $state->serializer()
            )
        );
        $state->serializer()->group(
            'results',
            fn() => $this->exportResults(
                $state->collector(),
                $state->transformations(),
                $state->serializer()
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function write(ExportState $state): void
    {
        $state->assertStep(ExportStep::PROCESS);
        $state->setStep(ExportStep::WRITE);

        $export_dir = $state->path()->getPathToComponentExpDirInContainer();
        $question_image_pipe = $state->transformations()->context(CollectQuestionImages::class);
        $resource_pipe = $state->transformations()->context(CollectResources::class);

        foreach ($question_image_pipe->getFiles() as $file) {
            if (file_exists($file['from'])) {
                $state->writer()->writeFileByFilePath(
                    $file['from'],
                    "{$export_dir}/" . $file['to']
                );
            } else {
                $state->logger()->warning('Question image file not found: ' . $file['from']);
            }
        }

        foreach ($resource_pipe->getResources() as $id => $resource) {
            $file = "{$id}.{$resource->getCurrentRevision()->getInformation()->getSuffix()}";
            $state->writer()->writeFilesByResourceId(
                $id,
                "{$export_dir}/resources/{$file}"
            );

        }

        $this->exportMappings(
            $state->collector(),
            $state->transformations(),
            $state->serializer()
        );
    }


    private function exportObject(
        TestCollector $collector,
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

    private function exportSettings(
        TestCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
        ExportState $state
    ): void {
        $test = $collector->getObject();
        $main_settings = $test->getMainSettings();

        $serializer->append('main', $transformations->normalize($main_settings));
        $serializer->append('scoring', $transformations->normalize($test->getScoreSettings()));
        $serializer->append('marks', $transformations->normalize($test->getMarkSchema()));

        if ($intro_page_id = $main_settings->getIntroductionSettings()->getIntroductionPageId()) {
            $state->addDependency('components/ILIAS/COPage', 'pg', ["tst:{$intro_page_id}"]);
        }
        if ($concluding_page_id = $main_settings->getFinishingSettings()->getConcludingRemarksPageId()) {
            $state->addDependency('components/ILIAS/COPage', 'pg', ["tst:{$concluding_page_id}"]);
        }
    }

    private function exportQuestions(
        TestCollector $collector,
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
        TestCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
    ): void {
        foreach ($collector->getSkillAssignments() as $assignment) {
            $serializer->append('skill_assignment', $transformations->normalize($assignment));
        }
    }

    private function exportSkillLevelThresholds(
        TestCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
    ): void {
        foreach ($collector->getSkillLevelThresholds() as $threshold) {
            $serializer->append('skill_level_threshold', $transformations->normalize($threshold));
        }
    }

    private function exportParticipants(
        TestCollector $collector,
        Transformations $transformations,
        Serializer $serializer
    ): void {
        foreach ($collector->getParticipants() as $participant) {
            $serializer->append('participant', $transformations->normalize($participant));
        }
    }

    private function exportResults(
        TestCollector $collector,
        Transformations $transformations,
        Serializer $serializer
    ): void {
        foreach ($collector->getParticipantsIds() as $participant_id) {
            $serializer->append(
                'results',
                $transformations->normalize(
                    $collector->getResults($participant_id)
                )
            );
        }
    }

    private function exportMappings(
        TestCollector $collector,
        Transformations $transformations,
        Serializer $serializer
    ): void {
        $serializer->startGroup('mappings');

        $user_ids = $transformations->context(CollectUserIds::class)->getIds();
        $serializer->append('users', $collector->getUserMapping($user_ids));

        $resources = $transformations->context(CollectResources::class)->getResources();
        $serializer->append(
            'resources',
            array_map($transformations->normalize(...), $resources)
        );

        $serializer->endGroup('mappings');
    }
}
