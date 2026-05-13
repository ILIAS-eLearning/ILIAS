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
use ilComponentRepository;
use ilDBInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\ObjectId;
use ILIAS\Data\UUID\Factory as UUIDFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Language\Language;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Taxonomy\DomainService as Taxonomy;
use ILIAS\Test\ExportImport\Pipes\CollectUserIds;
use ILIAS\Test\Logging\TestLogger;
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
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\SimpleXMLSerializer;
use ILIAS\TestQuestionPool\ExportImport\Pipes\CollectQuestionImages;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ilTree;

class TestExporter implements Exporter
{
    public function __construct(
        private readonly Builder $builder,
        private readonly DataFactory $data_factory,
        private readonly ilDBInterface $db,
        private readonly ilTree $tree,
        private readonly Language $lng,
        private readonly TestLogger $logger,
        private readonly ilComponentRepository $component_repository,
        private readonly IRSS $irss,
        private readonly ParticipantRepository $participant_repository,
        private readonly ResultsRepository $results_repository,
        private readonly QuestionsRepository $questions_repository,
        private readonly GeneralQuestionPropertiesRepository $general_questions_repository,
        private readonly Taxonomy $taxonomy
    ) {
    }

    /**
     * @inheritDoc
     */
    public function prepare(ExportState $state): void
    {
        $state->logger()->info('Preparing test export (1/3)...');
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
            $this->general_questions_repository,
            $this->db,
            $this->tree,
            $this->lng,
            $this->logger,
            $this->component_repository,
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
                new CollectResources(
                    $this->irss,
                    $this->logger
                ),
            ])
            ->create();

        $state->setTransformations($transformations);
        $state->logger()->info('...Finished preparing test export (1/3)');
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
        $state->logger()->info('Processing test export (2/3)...');
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
            'question_set_config',
            fn() => $this->exportQuestionSetConfig(
                $state->collector(),
                $state->transformations(),
                $state->serializer(),
            )
        );
        $state->serializer()->group(
            'additional_working_times',
            fn() => $this->exportAdditionalWorkingTimes(
                $state->collector(),
                $state->transformations(),
                $state->serializer(),
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
            $state->logger()->info('Processing test results export ...');
            $this->processResults($state);
            $state->logger()->info('...Finished processing test results export');
        }

        $state->logger()->info('...Finished processing test export (2/3)');
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
        $state->logger()->info('Writing test export (3/3)...');
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
                $state->logger()->debug("Copied question image {$file['from']} to {$export_dir}/{$file['to']}");
            } else {
                $state->logger()->warning('Question image file not found: ' . $file['from']);
            }
        }

        foreach ($resource_pipe->getResources() as $id => $resource) {
            $clean_id = str_replace(['-', '_'], '', $id);
            $file = "{$clean_id}.{$resource->getCurrentRevision()->getInformation()->getSuffix()}";

            $state->writer()->writeFilesByResourceId(
                $id,
                "{$export_dir}/resources/{$file}"
            );
            $state->logger()->debug("Copied resource {$id} to {$export_dir}/resources/{$file}");
        }

        $this->writeMappings(
            $state->collector(),
            $state->transformations(),
            $state
        );
        $state->logger()->debug('Stored test export mappings');

        $state->logger()->info('...Finished writing test export (3/3)');
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
        $state->addDependency('components/ILIAS/MetaData', 'tst', ["{$obj_id}:0:tst"]);
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
        $question_properties = $collector->getTestQuestionProperties();

        foreach ($collector->getQuestionObjects() as $question) {
            $normalized = [
                ... $transformations->normalize($question),
                'feedback' => $transformations->normalize(
                    $collector->getFeedback($question)
                ),
                'sequence' => $question_properties[$question->getId()]->getSequenceInformation()?->getPlaceInSequence(),
            ];

            if ($question instanceof assFormulaQuestion) {
                $data = $collector->getUnitsAndCategories($question->getId());
                $normalized['formula_data'] = $transformations->normalize($data);
            }

            $serializer->append('question', $normalized);
            $state->addDependency('components/ILIAS/COPage', 'pg', ["qpl:{$question->getId()}"]);
        }
    }

    private function exportQuestionSetConfig(
        TestCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
    ): void {
        $serializer->append(
            'question_set_config',
            $transformations->normalize($collector->getQuestionSetConfig())
        );
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
        $additional_data = $collector->getAdditionalParticipantData($collector->getParticipantsIds());

        foreach ($collector->getParticipants() as $participant) {
            $normalized = $transformations->normalize($participant);
            if ($participant->getActiveId() !== null) {
                $normalized = array_merge($normalized, $additional_data[$participant->getActiveId()]);
            }

            $serializer->append('participant', $normalized);
        }
    }

    private function exportResults(
        TestCollector $collector,
        Transformations $transformations,
        Serializer $serializer
    ): void {
        foreach ($collector->getParticipantsIds() as $participant_id) {
            $serializer->append(
                'set',
                $transformations->normalize(
                    $collector->getResults($participant_id)
                )
            );
        }
    }

    private function exportAdditionalWorkingTimes(
        TestCollector $collector,
        Transformations $transformations,
        Serializer $serializer,
    ): void {
        foreach ($collector->getAdditionalWorkingTimes() as $additional_working_time) {
            $serializer->append('time', $transformations->normalize($additional_working_time));
        }
    }

    private function writeMappings(
        TestCollector $collector,
        Transformations $transformations,
        ExportState $state
    ): void {
        $serializer = new SimpleXMLSerializer()->open('memory');
        $serializer->createDocument('Test Export Mappings');
        $serializer->startGroup('mappings');

        $user_ids = $transformations->context(CollectUserIds::class)->getIds();
        $serializer->append('users', $collector->getUserMapping($user_ids));

        $resources = $transformations->context(CollectResources::class)->getResources();
        $serializer->append(
            'resources',
            array_map($transformations->normalize(...), $resources)
        );

        $serializer->endGroup('mappings');

        $state->writer()->writeFileByStream(
            Streams::ofString($serializer->write()),
            "{$state->path()->getPathToComponentDirInContainer()}/mappings.xml"
        );
    }
}
