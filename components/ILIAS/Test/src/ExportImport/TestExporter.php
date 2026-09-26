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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\ObjectId;
use ILIAS\Data\UUID\Factory as UUIDFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Language\Language;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Taxonomy\DomainService as Taxonomy;
use ILIAS\Test\ExportImport\Normalize\Processors\CollectUserIds;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\Test\Questions\Properties\Repository as QuestionsRepository;
use ILIAS\Test\Results\Data\Repository as ResultsRepository;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Export\ExportStep;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Export\ExportState;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Export\Exporter;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serialize\Serializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Processors\CollectResources;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serialize\XmlSerializer;
use TestQuestionPool\ExportImport\Normalize\Processors\CollectQuestionImages;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

class TestExporter implements Exporter
{
    public function __construct(
        private readonly TransformationsBuilder $builder,
        private readonly DataFactory $data_factory,
        private readonly \ilDBInterface $db,
        private readonly \ilTree $tree,
        private readonly Language $lng,
        private readonly TestLogger $logger,
        private readonly \ilComponentRepository $component_repository,
        private readonly IRSS $irss,
        private readonly ParticipantRepository $participant_repository,
        private readonly ResultsRepository $results_repository,
        private readonly QuestionsRepository $questions_repository,
        private readonly GeneralQuestionPropertiesRepository $general_questions_repository,
        private readonly Taxonomy $taxonomy
    ) {
    }

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

        $transformations = $this->builder->forExport(
            new CollectUserIds(),
            new CollectQuestionImages(
                new UUIDFactory(),
                $object_id
            ),
            new CollectResources(
                $this->irss,
                $this->logger
            )
        );

        $state->setTransformations($transformations);
        $state->logger()->info('...Finished preparing test export (1/3)');
    }

    private function extractObjectId(ExportState $state): ?ObjectId
    {
        $target_ids = $state->target()->getObjectIds();

        if ($target_ids === []) {
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

    private function collector(ExportState $state): TestCollector
    {
        $collector = $state->collector();
        if (!($collector instanceof TestCollector)) {
            throw new \LogicException('Unexpected test collector');
        }

        return $collector;
    }

    public function process(ExportState $state): void
    {
        $state->logger()->info('Processing test export (2/3)...');
        $state->assertStep(ExportStep::PREPARE);
        $state->setStep(ExportStep::PROCESS);

        $state->serializer()->group(
            'general',
            fn() => $this->exportObject(
                $this->collector($state),
                $state->transformations(),
                $state->serializer(),
                $state
            )
        );
        $state->serializer()->group(
            'settings',
            fn() => $this->exportSettings(
                $this->collector($state),
                $state->transformations(),
                $state->serializer(),
                $state
            )
        );
        $state->serializer()->group(
            'questions',
            fn() => $this->exportQuestions(
                $this->collector($state),
                $state->transformations(),
                $state->serializer(),
                $state
            )
        );
        $state->serializer()->group(
            'question_set_config',
            fn() => $this->exportQuestionSetConfig(
                $this->collector($state),
                $state->transformations(),
                $state->serializer(),
            )
        );
        $state->serializer()->group(
            'additional_working_times',
            fn() => $this->exportAdditionalWorkingTimes(
                $this->collector($state),
                $state->transformations(),
                $state->serializer(),
            )
        );
        $state->serializer()->group(
            'skill_assignments',
            fn() => $this->exportSkillAssignments(
                $this->collector($state),
                $state->transformations(),
                $state->serializer(),
            )
        );
        $state->serializer()->group(
            'skill_thresholds',
            fn() => $this->exportSkillLevelThresholds(
                $this->collector($state),
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
                $this->collector($state),
                $state->transformations(),
                $state->serializer()
            )
        );
        $state->serializer()->group(
            'results',
            fn() => $this->exportResults(
                $this->collector($state),
                $state->transformations(),
                $state->serializer()
            )
        );
    }

    public function write(ExportState $state): void
    {
        $state->logger()->info('Writing test export (3/3)...');
        $state->assertStep(ExportStep::PROCESS);
        $state->setStep(ExportStep::WRITE);

        $export_dir = $state->path()->getPathToComponentExpDirInContainer();
        $question_image_collector = $state->transformations()
            ->normalizationProcessor(CollectQuestionImages::class);
        if (!($question_image_collector instanceof CollectQuestionImages)) {
            throw new \LogicException('Unexpected question image processor');
        }

        $resource_collector = $state->transformations()
            ->normalizationProcessor(CollectResources::class);
        if (!($resource_collector instanceof CollectResources)) {
            throw new \LogicException('Unexpected resource processor');
        }

        foreach ($question_image_collector->getFiles() as $file) {
            if (!file_exists($file['from'])) {
                $state->logger()->warning('Question image file not found: ' . $file['from']);
                continue;
            }

            $state->writer()->writeFileByFilePath(
                $file['from'],
                "{$export_dir}/" . $file['to']
            );
            $state->logger()->debug("Copied question image {$file['from']} to {$export_dir}/{$file['to']}");
        }

        foreach ($resource_collector->getResources() as $id => $resource) {
            $clean_id = str_replace(['-', '_'], '', $id);
            $file = "{$clean_id}.{$resource->getCurrentRevision()->getInformation()->getSuffix()}";

            $state->writer()->writeFilesByResourceId(
                $id,
                "{$export_dir}/resources/{$file}"
            );
            $state->logger()->debug("Copied resource {$id} to {$export_dir}/resources/{$file}");
        }

        $this->writeMappings(
            $this->collector($state),
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
        $state->addDependency('components/ILIAS/Tracking', 'lpsettings', [$obj_id]);
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

        $intro_page_id = $main_settings->getIntroductionSettings()->getIntroductionPageId();
        if ($intro_page_id !== null && $intro_page_id !== 0) {
            $state->addDependency('components/ILIAS/COPage', 'pg', ["tst:{$intro_page_id}"]);
        }
        $concluding_page_id = $main_settings->getFinishingSettings()->getConcludingRemarksPageId();
        if ($concluding_page_id !== null && $concluding_page_id !== 0) {
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

            if ($question instanceof \assFormulaQuestion) {
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
        $serializer = XmlSerializer::inMemory();
        $serializer->createDocument('Test Export Mappings');
        $serializer->startGroup('mappings');

        $user_id_processor = $transformations->normalizationProcessor(CollectUserIds::class);
        if (!($user_id_processor instanceof CollectUserIds)) {
            throw new \LogicException('Unexpected user ID processor');
        }
        $user_ids = $user_id_processor->getIds();
        $serializer->append('users', $collector->getUserMapping($user_ids));

        $resource_processor = $transformations->normalizationProcessor(CollectResources::class);
        if (!($resource_processor instanceof CollectResources)) {
            throw new \LogicException('Unexpected resource processor');
        }
        $resources = $resource_processor->getResources();
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
