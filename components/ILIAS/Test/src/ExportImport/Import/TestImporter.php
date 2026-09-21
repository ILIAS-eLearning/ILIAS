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

namespace ILIAS\Test\ExportImport\Import;

use assFileUploadStakeholder;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\ReferenceId;
use ILIAS\Data\UUID\Factory as UUIDFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Test\ExportImport\Envelopes\QuestionSetConfig;
use ILIAS\Test\Participants\Participant;
use ILIAS\Test\Scoring\Marks\MarkSchema;
use ILIAS\Test\Scoring\Marks\MarksRepository;
use ILIAS\Test\Settings\GlobalSettings\UserIdentifiers;
use ILIAS\Test\Settings\MainSettings\MainSettings;
use ILIAS\Test\Settings\ScoreReporting\ScoreSettings;
use ILIAS\Test\ExportImport\TransformationsBuilder;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Deserializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\CollectResources;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\IdMappingProcessor;
use ILIAS\TestQuestionPool\ExportImport\Import\QuestionsImporter;
use ILIAS\TestQuestionPool\ExportImport\Import\SkillAssignmentsImporter;
use ILIAS\TestQuestionPool\ExportImport\Pipes\CollectQuestionImages;
use ilImportMapping;
use ilObjTest;
use ilTestPage;
use Psr\Log\LoggerInterface;

/**
 * Orchestrates denormalization and persistence of a test import and its related resources.
 */
class TestImporter
{
    public function __construct(
        private readonly TransformationsBuilder $builder,
        private readonly ilDBInterface $database,
        private readonly LoggerInterface $log,
        private readonly IRSS $irss,
        private readonly DataFactory $data_factory,
        private readonly QuestionsImporter $questions_importer,
        private readonly RandomTestConfigImporter $random_test_config_importer,
        private readonly TestResultsImporter $test_results_importer,
        private readonly SkillAssignmentsImporter $skill_importer,
        private readonly SkillLevelThresholdsImporter $skill_thresholds_importer,
        private readonly MarksRepository $marks_repository,
    ) {
    }

    /**
     * Import a test from a deserializer instance. It will import the test object, related resources and content
     * (settings, questions, participants, results, etc.) into the database. It will return the import context with the
     * new test object id and reference id.
     */
    public function import(
        Deserializer $deserializer,
        ilImportMapping $mapping,
        ReferenceId $parent_id,
        ImportContext $context
    ): ImportContext {
        $resource_collector = new CollectResources($this->irss, $this->log);
        $question_images_collector = new CollectQuestionImages(new UUIDFactory(), $this->data_factory->objId(0));

        $transformations = $this->builder->forImport(
            null,
            new IdMappingProcessor($mapping, 'components/ILIAS/Test', $this->log),
            $question_images_collector,
            $resource_collector
        );

        /** @var ilObjTest|null $test_object */
        $test_object = null;

        $deserializer->addHandler(
            'general',
            function (array $objects) use ($transformations, $mapping, $parent_id, &$test_object): void {
                $test_object = $this->importTest(
                    array_pop($objects),
                    $transformations,
                    $mapping,
                    $parent_id
                );
            }
        );

        $deserializer->addHandler(
            'settings',
            function (array $settings) use ($transformations, $mapping, &$test_object): void {
                $this->importSettings(
                    $settings,
                    $transformations,
                    $mapping,
                    $test_object
                );
            }
        );

        $deserializer->addHandler(
            'questions',
            function (array $normalized) use ($transformations, $mapping, $context, &$test_object): void {
                $this->importQuestions(
                    $normalized,
                    $transformations,
                    $mapping,
                    $context,
                    $test_object
                );
            }
        );

        $deserializer->addHandler(
            'question_set_config',
            function (array $normalized) use ($transformations, $mapping, $context, &$test_object): void {
                $this->importQuestionSetConfig(
                    reset($normalized),
                    $transformations,
                    $mapping,
                    $test_object
                );
            }
        );

        $deserializer->addHandler(
            'skill_assignments',
            function (array $assignments) use ($transformations, $mapping, &$context): void {
                $result = $this->skill_importer->import(
                    $assignments,
                    $context->installId(),
                    $transformations,
                    $mapping,
                );
                $context = $context->withSkillAssignments($result);
            }
        );

        $deserializer->addHandler(
            'skill_thresholds',
            function (array $thresholds) use ($transformations, $mapping, &$context): void {
                $result = $this->skill_thresholds_importer->import(
                    $thresholds,
                    $context->installId(),
                    $transformations,
                    $mapping,
                );
                $context = $context->withSkillThresholds($result);
            }
        );

        $deserializer->addHandler(
            'participants',
            function (array $participants) use ($transformations, $mapping): void {
                $this->importParticipants(
                    $participants,
                    $transformations,
                    $mapping,
                );
            }
        );

        $deserializer->addHandler(
            'results',
            function (array $results) use ($transformations): void {
                $this->test_results_importer->import(
                    $results,
                    $transformations,
                );
            }
        );

        $deserializer->addHandler(
            'additional_working_times',
            function (array $times) use ($transformations): void {
                $this->test_results_importer->importAdditionalWorkingTimes(
                    $times,
                    $transformations,
                );
            }
        );

        $this->log->info('Importing users and resources mappings...');
        $this->importMappings($mapping, $resource_collector, $context);
        $this->log->info('...Finished importing users and resources mappings');

        $this->log->info('Importing test export file...');
        $deserializer->process();
        $this->log->info('...Finished importing test export file');

        $this->log->info('Importing question images...');
        $this->questions_importer->importQuestionImages(
            $test_object->getId(),
            $mapping,
            $context,
            $question_images_collector
        );
        $this->log->info('...Finished importing question images');

        $this->log->info("Finished importing test {$test_object->getTestId()} (Test ID), {$test_object->getId()} (Object ID)");
        return $context->withTestObjId($test_object->getId())->withTestRefId($test_object->getRefId());
    }

    /**
     * Finalize the import after all dependencies have been imported.
     * It will replace the old question ids with the new question ids in the test pages and remap taxonomy IDs in random
     * question set source pool definitions.
     */
    public function finalize(ilImportMapping $mapping): void
    {
        $this->log->info('Finalizing test import...');
        $this->questions_importer->finalizeQuestionPages($mapping);
        $this->random_test_config_importer->finalizeTaxonomyFilters($mapping);
        $this->log->info('...Finished finalizing test');
    }


    private function importMappings(
        ilImportMapping $mapping,
        CollectResources $resource_collector,
        ImportContext $context
    ): void {
        $user_mapping = $context->userMappings();

        $this->log->info('Importing user mappings...');
        $user_resolver = new UserImportResolver($this->database, $this->log);
        $imported_users = $user_resolver->resolve(
            UserIdentifiers::from($user_mapping['identifier']),
            $user_mapping['mapping']
        );
        $user_resolver->store($imported_users, $mapping);
        $this->log->info('...Finished importing user mappings');

        $this->log->info('Importing resources and storing mappings...');
        $import_dir = dirname($context->componentImportFile()) . '/expDir_1';
        foreach ($context->resourceMappings() as $resource) {
            $clean_id = str_replace(['-', '_'], '', $resource['id']);
            $resource_path = "$import_dir/resources/{$clean_id}.{$resource['suffix']}";
            if (!file_exists($resource_path)) {
                $this->log->error("Imported resource path does not exist: {$resource_path}, skipping");
                continue;
            }

            $new_id = $this->irss->manage()->stream(
                Streams::ofResource(fopen($resource_path, 'rb')),
                new assFileUploadStakeholder(),
                $resource['title']
            );
            $resource_collector->storeMapping($resource['id'], $new_id);
            $this->log->debug("Imported resource: {$resource_path} -> {$new_id->serialize()}");
        }
        $this->log->info('...Finished importing resources and storing mappings');
    }

    private function importTest(
        array $normalized,
        Transformations $transformations,
        ilImportMapping $mapping,
        ReferenceId $parent_id
    ): ilObjTest {
        $test_object = $transformations->denormalize($normalized, ilObjTest::class);
        $old_obj_id = $test_object->getId();
        $old_test_id = $test_object->getTestId();

        $test_object->setTestId(-1);
        $test_object->setTitle($test_object->getTitle());
        $new_obj_id = $test_object->create();
        $test_object->saveToDb(true);
        $this->log->debug("Created new test object: {$old_test_id} -> {$test_object->getTestId()} (Test ID), {$old_obj_id} -> {$new_obj_id} (Object ID)");

        $test_object->createReference();
        $test_object->putInTree($parent_id->toInt());
        $test_object->setPermissions($parent_id->toInt());
        $this->log->debug("Stored test object in tree: {$parent_id->toInt()} (Parent Ref) -> {$test_object->getRefId()} (Test Ref)");

        $mapping->addMapping('components/ILIAS/Test', 'tst', (string) $old_test_id, (string) $test_object->getTestId());
        $mapping->addMapping('components/ILIAS/Test', 'object', (string) $old_obj_id, (string) $new_obj_id);
        $mapping->addMapping('components/ILIAS/MetaData', 'md', "{$old_obj_id}:0:tst", "{$new_obj_id}:0:tst");

        return $test_object;
    }

    private function importSettings(
        array $list,
        Transformations $transformations,
        ilImportMapping $mapping,
        ilObjTest $test_object
    ): void {
        $settings_id = $test_object->getMainSettings()->getId();

        $main_settings = $transformations->denormalize($list[0], MainSettings::class)->withId($settings_id);
        $scoring_settings = $transformations->denormalize($list[1], ScoreSettings::class)->withId($settings_id);
        $mark_schema = $transformations->denormalize($list[2], MarkSchema::class)->withTestId($test_object->getTestId());

        if ($intro_page_id = $main_settings->getIntroductionSettings()->getIntroductionPageId()) {
            $new_page_id = $this->createPage($intro_page_id, $test_object->getId(), $mapping);
            $main_settings = $main_settings->withIntroductionSettings(
                $main_settings->getIntroductionSettings()->withIntroductionPageId($new_page_id)
            );
            $this->log->debug("Imported introduction page: {$intro_page_id} -> {$new_page_id}");
        }

        if ($concluding_page_id = $main_settings->getFinishingSettings()->getConcludingRemarksPageId()) {
            $new_page_id = $this->createPage($concluding_page_id, $test_object->getId(), $mapping);
            $main_settings = $main_settings->withFinishingSettings(
                $main_settings->getFinishingSettings()->withConcludingRemarksPageId($new_page_id)
            );
            $this->log->debug("Imported concluding remarks page: {$concluding_page_id} -> {$new_page_id}");
        }

        $test_object->getMainSettingsRepository()->store($main_settings);
        $test_object->getScoreSettingsRepository()->store($scoring_settings);
        $this->marks_repository->storeMarkSchema($mark_schema);
        $this->log->debug("Imported test settings and mark schema: {$settings_id} (Settings ID)");
    }

    private function createPage(int $imported_page_id, int $parent_id, ilImportMapping $mapping): int
    {
        $page = new ilTestPage();
        $page->setParentId($parent_id);
        $page->createPageWithNextId();

        $mapping->addMapping(
            'components/ILIAS/COPage',
            'pg',
            "tst:{$imported_page_id}",
            "tst:{$page->getId()}"
        );

        return $page->getId();
    }

    private function importQuestions(
        array $list,
        Transformations $transformations,
        ilImportMapping $mapping,
        ImportContext $context,
        ilObjTest $test_object
    ): void {
        foreach ($list as $normalized) {
            $question = $this->questions_importer->importQuestion(
                $normalized, 
                $transformations, 
                $mapping, 
                $context->selectedQuestionIds()
            );
            if (!$question instanceof \assQuestion || $normalized['sequence'] === null) {
                continue;
            }

            $sequence = $transformations->int($normalized['sequence']);
            $test_object->questions[$sequence] = $question->getId();
            $this->log->debug("Stored question {$question->getId()} at sequence {$sequence} in test");
        }

        $test_object->saveQuestionsToDb();
        $this->log->debug('Saved test questions to database');
    }

    private function importQuestionSetConfig(
        array $normalized,
        Transformations $transformations,
        ilImportMapping $mapping,
        ilObjTest $test_object
    ): void {
        $config = $transformations->denormalize($normalized, QuestionSetConfig::class);

        if ($config->isRandom()) {
            $this->random_test_config_importer->import($config, $mapping, $test_object);
        }
    }

    private function importParticipants(array $list, Transformations $transformations, ilImportMapping $mapping): void
    {
        foreach ($list as $normalized) {
            if ($normalized['active_id'] === null) {
                $this->importInvitedParticipant($normalized, $transformations);
                continue;
            }

            $old_active_id = $transformations->denormalize($normalized['active_id'], Id::class)->getId();
            $new_active_id = $this->database->nextId('tst_active');
            $mapping->addMapping('components/ILIAS/Test', 'participant', (string) $old_active_id, (string) $new_active_id);
            $this->log->debug("Stored participant/test session mapping: {$old_active_id} -> {$new_active_id}");

            // The mapping processor replaces TestID, UserID and ActiveID
            $participant = $transformations->denormalize($normalized, Participant::class);

            $this->database->insert(
                'tst_active',
                [
                    'active_id' => [ilDBConstants::T_INTEGER, $new_active_id],
                    'user_fi' => [ilDBConstants::T_INTEGER, $participant->getUserId()],
                    'test_fi' => [ilDBConstants::T_INTEGER, $participant->getTestId()],
                    'anonymous_id' => [ilDBConstants::T_TEXT, $participant->getAnonymousId()],
                    'tries' => [ilDBConstants::T_INTEGER, $participant->getAttempts()],
                    'submitted' => [ilDBConstants::T_INTEGER, $participant->getSubmitted() ? 1 : 0],
                    'last_finished_pass' => [ilDBConstants::T_INTEGER, $participant->getLastFinishedAttempt()],
                    'last_started_pass' => [ilDBConstants::T_INTEGER, $participant->getLastStartedAttempt()],
                    'importname' => [ilDBConstants::T_TEXT, "{$participant->getFirstname()} {$participant->getLastname()}"],
                    'tstamp' => [ilDBConstants::T_INTEGER, time()],
                    'submittimestamp' => [ilDBConstants::T_TIMESTAMP, $transformations->nullableString($normalized['submittimestamp'])],
                    'lastindex' => [ilDBConstants::T_INTEGER, $transformations->nullableInt($normalized['lastindex'])],
                    'objective_container' => [ilDBConstants::T_INTEGER, $transformations->nullableInt($normalized['objective_container'])],
                    'start_lock' => [ilDBConstants::T_TEXT, $transformations->nullableString($normalized['start_lock'])],
                ]
            );
            $this->log->debug("Stored test session in database: {$new_active_id} (Active ID)");
        }
    }

    private function importInvitedParticipant(array $normalized, Transformations $transformations): void
    {
        // The mapping processor replaces TestID and UserID
        $participant = $transformations->denormalize($normalized, Participant::class);

        $this->database->insert(
            'tst_invited_user',
            [
                'test_fi' => [ilDBConstants::T_INTEGER, $participant->getTestId()],
                'user_fi' => [ilDBConstants::T_INTEGER, $participant->getUserId()],
                'ip_range_from' => [ilDBConstants::T_TEXT, $participant->getClientIpFrom()],
                'ip_range_to' => [ilDBConstants::T_TEXT, $participant->getClientIpTo()],
                'tstamp' => [ilDBConstants::T_INTEGER, $participant->getInvitationDate()],
            ]
        );
        $this->log->debug("Stored invited participant in database: {$participant->getUserId()} (User ID), {$participant->getTestId()} (Test ID)");
    }
}
