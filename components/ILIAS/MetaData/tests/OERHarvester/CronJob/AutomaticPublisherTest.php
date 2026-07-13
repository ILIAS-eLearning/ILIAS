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

namespace ILIAS\MetaData\OERHarvester\CronJob;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface;
use ILIAS\MetaData\OERHarvester\Settings\NullSettings;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\HandlerInterface as ObjectHandler;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\NullHandler as NullObjectHandler;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RepositoryInterface as StatusRepository;
use ILIAS\MetaData\OERHarvester\ResourceStatus\NullRepository as NullStatusRepository;
use ILIAS\MetaData\OERHarvester\ExposedRecords\RepositoryInterface as ExposedRecordRepository;
use ILIAS\MetaData\OERHarvester\ExposedRecords\NullRepository as NullExposedRecordRepository;
use ILIAS\MetaData\Copyright\Search\FactoryInterface as SearchFactory;
use ILIAS\MetaData\Copyright\Search\NullFactory;
use ILIAS\MetaData\OERHarvester\XML\WriterInterface as SimpleDCXMLWriter;
use ILIAS\MetaData\OERHarvester\XML\NullWriter;
use ILIAS\MetaData\Copyright\Search\SearcherInterface;
use ILIAS\MetaData\Copyright\Search\NullSearcher;
use ILIAS\MetaData\Elements\RessourceID\NullRessourceID;
use ILIAS\MetaData\OERHarvester\ExposedRecords\NullRecord;
use ILIAS\MetaData\OERHarvester\ExposedRecords\RecordInfosInterface;
use ILIAS\MetaData\OERHarvester\ExposedRecords\NullRecordInfos;
use ILIAS\MetaData\OERHarvester\CronJob\Results\WrapperInterface;
use ILIAS\MetaData\OERHarvester\CronJob\Results\NullWrapper;
use ILIAS\MetaData\Repository\RepositoryInterface as LOMRepository;
use ILIAS\MetaData\Repository\NullRepository as NullLOMRepository;
use ILIAS\Cron\Job\JobResult;
use ILIAS\MetaData\OERHarvester\Publisher\NullPublisher;
use ILIAS\MetaData\OERHarvester\Publisher\PublisherInterface;

class AutomaticPublisherTest extends TestCase
{
    protected function getPublisher(
        ?int $throw_error_on_withdraw_obj_id = null,
        ?int $throw_error_on_publish_obj_id = null
    ): PublisherInterface {
        return new class (
            $throw_error_on_withdraw_obj_id,
            $throw_error_on_publish_obj_id
        ) extends NullPublisher {
            public array $exposed_withdrawn_objects = [];
            public array $exposed_submitted_objects = [];
            public array $exposed_published_objects = [];

            public function __construct(
                protected ?int $throw_error_on_withdraw_obj_id,
                protected ?int $throw_error_on_publish_obj_id
            ) {
            }

            public function withdraw(int $obj_id): void
            {
                if ($this->throw_error_on_withdraw_obj_id === $obj_id) {
                    throw new \ilMDOERHarvesterException('error');
                }
                $this->exposed_withdrawn_objects[] = $obj_id;
            }

            public function submit(int $obj_id): void
            {
                $this->exposed_submitted_objects[] = $obj_id;
            }

            public function publish(int $obj_id, string $type): void
            {
                if ($this->throw_error_on_publish_obj_id === $obj_id) {
                    throw new \ilMDOERHarvesterException('error');
                }
                $this->exposed_published_objects[] = [$obj_id, $type];
            }
        };
    }

    protected function getSettings(
        bool $automatic_publishing_enabled = false,
        bool $editorial_step_enabled = false,
        array $types = [],
        array $copyright_ids = [],
        int $publishing_container_ref_id = 0
    ): SettingsInterface {
        return new class (
            $automatic_publishing_enabled,
            $editorial_step_enabled,
            $types,
            $copyright_ids,
            $publishing_container_ref_id
        ) extends NullSettings {
            public function __construct(
                protected bool $automatic_publishing_enabled,
                protected bool $editorial_step_enabled,
                protected array $types,
                protected array $copyright_ids,
                protected int $publishing_container_ref_id
            ) {
            }

            public function isAutomaticPublishingEnabled(): bool
            {
                return $this->automatic_publishing_enabled;
            }

            public function isEditorialStepEnabled(): bool
            {
                return $this->editorial_step_enabled;
            }

            public function getObjectTypesSelectedForPublishing(): array
            {
                return $this->types;
            }

            public function getCopyrightEntryIDsSelectedForPublishing(): array
            {
                return $this->copyright_ids;
            }

            public function getContainerRefIDForPublishing(): int
            {
                return $this->publishing_container_ref_id;
            }
        };
    }

    protected function getObjectHandler(
        array $deleted_obj_ids = [],
        int $valid_publishing_container = 0,
        array $ref_ids_in_container = [],
        array $deleted_ref_ids = []
    ): ObjectHandler {
        return new class (
            $deleted_obj_ids,
            $valid_publishing_container,
            $ref_ids_in_container,
            $deleted_ref_ids
        ) extends NullObjectHandler {
            public function __construct(
                protected array $deleted_obj_ids,
                protected int $valid_publishing_container,
                protected array $ref_ids_in_container,
                protected array $deleted_ref_ids
            ) {
            }

            public function doesReferenceExist(int $ref_id): bool
            {
                return !in_array($ref_id, $this->deleted_ref_ids);
            }

            public function isObjectDeleted(int $obj_id): bool
            {
                return in_array($obj_id, $this->deleted_obj_ids);
            }

            public function isReferenceInContainer(int $ref_id, int $container_ref_id): bool
            {
                if ($container_ref_id !== $this->valid_publishing_container) {
                    return false;
                }
                return in_array($ref_id, $this->ref_ids_in_container);
            }

            public function getTypeOfObject(int $obj_id): string
            {
                return 'type_' . $obj_id;
            }
        };
    }

    /**
     * Currently harvested objects are passed as obj_id => href_id
     */
    protected function getStatusRepository(
        array $currently_harvested = [],
        array $blocked_obj_ids = [],
        bool $throw_error = false
    ): StatusRepository {
        return new class ($currently_harvested, $blocked_obj_ids, $throw_error) extends NullStatusRepository {
            public function __construct(
                protected array $currently_harvested,
                protected array $blocked_obj_ids,
                protected bool $throw_error
            ) {
            }

            public function getAllHarvestedObjIDs(): \Generator
            {
                if ($this->throw_error === true) {
                    throw new \ilMDOERHarvesterException('error');
                }
                yield from array_keys($this->currently_harvested);
            }

            public function filterOutBlockedObjects(int ...$obj_ids): \Generator
            {
                foreach ($obj_ids as $obj_id) {
                    if (!in_array($obj_id, $this->blocked_obj_ids)) {
                        yield $obj_id;
                    }
                }
            }

            public function getHarvestRefID(int $obj_id): int
            {
                return $this->currently_harvested[$obj_id] ?? 0;
            }
        };
    }

    /**
     * Records are passed as array via obj_id => metadata-xml as string.
     * Records supposed to be marked as deleted are as their obj_id to the
     * second argument.
     */
    protected function getExposedRecordRepository(
        array $returned_records = [],
        array $deleted_records = []
    ): ExposedRecordRepository {
        return new class ($returned_records, $deleted_records) extends NullExposedRecordRepository {
            public array $exposed_deletions = [];
            public array $exposed_updates = [];

            public function __construct(
                protected array $returned_records,
                protected array $deleted_records
            ) {
            }

            public function getRecords(
                ?\DateTimeImmutable $from = null,
                ?\DateTimeImmutable $until = null,
                ?int $limit = null,
                ?int $offset = null
            ): \Generator {
                foreach ($this->returned_records as $obj_id => $metadata) {
                    $is_deleted = in_array($obj_id, $this->deleted_records);
                    yield new class ($obj_id, $is_deleted, $metadata) extends NullRecord {
                        public function __construct(
                            protected int $obj_id,
                            protected bool $is_deleted,
                            protected ?string $metadata
                        ) {
                        }

                        public function infos(): RecordInfosInterface
                        {
                            return new class ($this->obj_id, $this->is_deleted) extends NullRecordInfos {
                                public function __construct(
                                    protected int $obj_id,
                                    protected bool $is_deleted
                                ) {
                                }

                                public function objID(): int
                                {
                                    return $this->obj_id;
                                }

                                public function isDeleted(): bool
                                {
                                    return $this->is_deleted;
                                }
                            };
                        }

                        public function metadata(): ?\DOMDocument
                        {
                            if ($this->metadata === null) {
                                return null;
                            }
                            $xml = new \DOMDocument();
                            $xml->loadXML($this->metadata);
                            return $xml;
                        }
                    };
                }
            }

            public function deleteRecordsMarkedAsDeletedOlderThan(\DateInterval $interval): void
            {
                $this->exposed_deletions[] = ['interval' => $interval];
            }

            public function updateRecord(int $obj_id, bool $is_deleted, ?\DOMDocument $metadata): void
            {
                $this->exposed_updates[] = [
                    'obj_id' => $obj_id,
                    'deleted' => $is_deleted,
                    'metadata' => $metadata?->saveXML()
                ];
            }
        };
    }

    protected function getSearchFactory(int ...$search_result_obj_ids): SearchFactory
    {
        return new class ($search_result_obj_ids) extends NullFactory {
            public array $exposed_search_params;

            public function __construct(public array $search_result_obj_ids)
            {
            }

            public function get(): SearcherInterface
            {
                return new class ($this) extends NullSearcher {
                    protected array $types = [];
                    protected bool $restricted_to_repository = false;

                    public function __construct(protected SearchFactory $factory)
                    {
                    }

                    public function withRestrictionToRepositoryObjects(bool $restricted): SearcherInterface
                    {
                        $clone = clone $this;
                        $clone->restricted_to_repository = $restricted;
                        return $clone;
                    }

                    public function withAdditionalTypeFilter(string $type): SearcherInterface
                    {
                        $clone = clone $this;
                        $clone->types[] = $type;
                        return $clone;
                    }

                    public function search(
                        LOMRepository $lom_repository,
                        int $first_entry_id,
                        int ...$further_entry_ids
                    ): \Generator {
                        $this->factory->exposed_search_params[] = [
                            'restricted' => $this->restricted_to_repository,
                            'types' => $this->types,
                            'entries' => [$first_entry_id, ...$further_entry_ids]
                        ];
                        foreach ($this->factory->search_result_obj_ids as $obj_id) {
                            yield new class ($obj_id) extends NullRessourceID {
                                public function __construct(protected int $obj_id)
                                {
                                }

                                public function objID(): int
                                {
                                    return $this->obj_id;
                                }
                            };
                        }
                    }
                };
            }
        };
    }

    /**
     * Metadata is passed as array via obj_id => metadata-xml as string
     */
    protected function getXMLWriter(array $returned_md = []): SimpleDCXMLWriter
    {
        return new class ($returned_md) extends NullWriter {
            public array $exposed_params = [];

            public function __construct(protected array $returned_md)
            {
            }

            public function writeSimpleDCMetaData(int $obj_id, int $ref_id, string $type): \DOMDocument
            {
                $this->exposed_params[] = [
                    'obj_id' => $obj_id,
                    'ref_id' => $ref_id,
                    'type' => $type
                ];

                $xml = new \DOMDocument();
                $xml->loadXML($this->returned_md[$obj_id]);
                return $xml;
            }
        };
    }

    protected function getNullLogger(): \ilLogger
    {
        return $this->createMock(\ilLogger::class);
    }

    protected function getCronResultWrapper(): WrapperInterface
    {
        return new class () extends NullWrapper {
            public int $exposed_status;
            public string $exposed_message;

            public function withMessage(string $message): WrapperInterface
            {
                $clone = clone $this;
                $clone->exposed_message = $message;
                return $clone;
            }

            public function withStatus(int $status): WrapperInterface
            {
                $clone = clone $this;
                $clone->exposed_status = $status;
                return $clone;
            }
        };
    }

    public function testRunWithdrawDeprecatedReferenceIncorrectTypeOrCopyright(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(),
            $this->getSettings(false, false, ['type', 'second type'], [12, 5]),
            $this->getObjectHandler(),
            $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $this->getExposedRecordRepository(),
            $search_factory = $this->getSearchFactory(45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Withdrew 1 deprecated objects.<br>' .
            'Updated 0 published objects.',
            $result->exposed_message
        );
        $this->assertSame(
            [['restricted' => true, 'types' => ['type', 'second type'], 'entries' => [12, 5]]],
            $search_factory->exposed_search_params
        );
        $this->assertSame([32], $publisher->exposed_withdrawn_objects);
    }

    public function testRunWithdrawDeprecatedReferenceBlocked(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(),
            $this->getSettings(false, false, ['type', 'second type'], [12, 5]),
            $this->getObjectHandler(),
            $this->getStatusRepository([32 => 12332, 45 => 12345], [32]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(45, 32),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Withdrew 1 deprecated objects.<br>' .
            'Updated 0 published objects.',
            $result->exposed_message
        );
        $this->assertSame([32], $publisher->exposed_withdrawn_objects);
    }

    public function testRunWithdrawDeprecatedReferenceObjectDeleted(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(),
            $this->getSettings(false, false, ['type', 'second type'], [12, 5]),
            $this->getObjectHandler([32]),
            $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(45, 32),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Withdrew 1 deprecated objects.<br>' .
            'Updated 0 published objects.',
            $result->exposed_message
        );
        $this->assertSame([32], $publisher->exposed_withdrawn_objects);
    }

    public function testRunWithdrawDeprecatedReferenceReferenceDeleted(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(),
            $this->getSettings(false, false, ['type', 'second type'], [12, 5]),
            $this->getObjectHandler([], 0, [], [12332]),
            $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(45, 32),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Withdrew 1 deprecated objects.<br>' .
            'Updated 0 published objects.',
            $result->exposed_message
        );
        $this->assertSame([32], $publisher->exposed_withdrawn_objects);
    }

    public function testRunWithdrawDeprecatedReferenceContinueDespiteError(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(45),
            $this->getSettings(false, false, ['type', 'second type'], [12, 5]),
            $this->getObjectHandler(),
            $this->getStatusRepository([32 => 12332, 45 => 12345, 67 => 12367]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Withdrew 2 deprecated objects.<br>' .
            'Updated 0 published objects.',
            $result->exposed_message
        );
        $this->assertSame([32, 67], $publisher->exposed_withdrawn_objects);
    }

    public function testRunPublishObject(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(),
            $this->getSettings(true, false, ['type', 'second type'], [12, 5], 123),
            $this->getObjectHandler(),
            $this->getStatusRepository([32 => 12332]),
            $this->getExposedRecordRepository(),
            $search_factory = $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Withdrew 0 deprecated objects.<br>' .
            'Updated 0 published objects.<br>' .
            'Published or submitted for review 1 new objects.',
            $result->exposed_message
        );
        $this->assertSame(
            [[
                'restricted' => true,
                'types' => ['type', 'second type'],
                'entries' => [12, 5]
            ]],
            $search_factory->exposed_search_params
        );
        $this->assertSame([[45, 'type_45']], $publisher->exposed_published_objects);
    }

    public function testRunSubmitObject(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(),
            $this->getSettings(true, true, ['type', 'second type'], [12, 5], 123),
            $this->getObjectHandler(),
            $this->getStatusRepository([32 => 12332]),
            $this->getExposedRecordRepository(),
            $search_factory = $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Withdrew 0 deprecated objects.<br>' .
            'Updated 0 published objects.<br>' .
            'Published or submitted for review 1 new objects.',
            $result->exposed_message
        );
        $this->assertSame(
            [[
                 'restricted' => true,
                 'types' => ['type', 'second type'],
                 'entries' => [12, 5]
             ]],
            $search_factory->exposed_search_params
        );
        $this->assertSame([45], $publisher->exposed_submitted_objects);
    }

    public function testRunDoNotPublishIfNoAutomaticPublishing(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(),
            $this->getSettings(false, false, ['type', 'second type'], [12, 5], 123),
            $this->getObjectHandler(),
            $this->getStatusRepository([32 => 12332]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Withdrew 0 deprecated objects.<br>' .
            'Updated 0 published objects.',
            $result->exposed_message
        );
        $this->assertEmpty($publisher->exposed_published_objects);
    }

    public function testRunDoNotPublishBlockedObject(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(),
            $this->getSettings(true, false, ['type', 'second type'], [12, 5], 123),
            $this->getObjectHandler(),
            $this->getStatusRepository([32 => 12332], [45]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Withdrew 0 deprecated objects.<br>' .
            'Updated 0 published objects.<br>' .
            'Published or submitted for review 0 new objects.',
            $result->exposed_message
        );
        $this->assertEmpty($publisher->exposed_published_objects);
    }

    public function testRunDoNotHarvestDeletedObject(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(),
            $this->getSettings(true, false, ['type', 'second type'], [12, 5], 123),
            $this->getObjectHandler([45]),
            $this->getStatusRepository([32 => 12332]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Withdrew 0 deprecated objects.<br>' .
            'Updated 0 published objects.<br>' .
            'Published or submitted for review 0 new objects.',
            $result->exposed_message
        );
        $this->assertEmpty($publisher->exposed_published_objects);
    }

    public function testRunDoNotPublishAlreadyPublishedObject(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(),
            $this->getSettings(true, false, ['type', 'second type'], [12, 5], 123),
            $this->getObjectHandler(),
            $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Withdrew 0 deprecated objects.<br>' .
            'Updated 0 published objects.<br>' .
            'Published or submitted for review 0 new objects.',
            $result->exposed_message
        );
        $this->assertEmpty($publisher->exposed_published_objects);
    }

    public function testRunPublishObjectContinueDespiteError(): void
    {
        $harvester = new AutomaticPublisher(
            $publisher = $this->getPublisher(null, 45),
            $this->getSettings(true, false, ['type', 'second type'], [12, 5], 123),
            $this->getObjectHandler(),
            $this->getStatusRepository(),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(32, 45, 67),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Withdrew 0 deprecated objects.<br>' .
            'Updated 0 published objects.<br>' .
            'Published or submitted for review 2 new objects.',
            $result->exposed_message
        );
        $this->assertSame([
            [32, 'type_32'],
            [67, 'type_67']
        ], $publisher->exposed_published_objects);
    }

    public function testRunUpdateExposedRecord(): void
    {
        $harvester = new AutomaticPublisher(
            $this->getPublisher(),
            $this->getSettings(false, false, ['type', 'second type'], [12, 5], 123),
            $this->getObjectHandler([], 123, [12332, 12345]),
            $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>', 45 => '<el>45</el>']),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $writer = $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45 changed</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Withdrew 0 deprecated objects.<br>' .
            'Updated 1 published objects.',
            $result->exposed_message
        );
        $this->assertCount(1, $record_repo->exposed_updates);
        $this->assertSame(45, $record_repo->exposed_updates[0]['obj_id']);
        $this->assertSame(false, $record_repo->exposed_updates[0]['deleted']);
        $this->assertXmlStringEqualsXmlString(
            '<el>45 changed</el>',
            $record_repo->exposed_updates[0]['metadata']
        );
        $this->assertEquals(
            [
                ['obj_id' => 32, 'ref_id' => 12332, 'type' => 'type_32'],
                ['obj_id' => 45, 'ref_id' => 12345, 'type' => 'type_45']
            ],
            $writer->exposed_params
        );
        $this->assertCount(1, $record_repo->exposed_deletions);
    }

    public function testRunUpdateExposedRecordMarkedAsDeleted(): void
    {
        $harvester = new AutomaticPublisher(
            $this->getPublisher(),
            $this->getSettings(false, false, ['type', 'second type'], [12, 5], 123),
            $this->getObjectHandler([], 123, [12332, 12345]),
            $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>', 45 => null], [45]),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $writer = $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45 changed</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Withdrew 0 deprecated objects.<br>' .
            'Updated 1 published objects.',
            $result->exposed_message
        );
        $this->assertCount(1, $record_repo->exposed_updates);
        $this->assertSame(45, $record_repo->exposed_updates[0]['obj_id']);
        $this->assertSame(false, $record_repo->exposed_updates[0]['deleted']);
        $this->assertXmlStringEqualsXmlString(
            '<el>45 changed</el>',
            $record_repo->exposed_updates[0]['metadata']
        );
        $this->assertEquals(
            [
                ['obj_id' => 32, 'ref_id' => 12332, 'type' => 'type_32'],
                ['obj_id' => 45, 'ref_id' => 12345, 'type' => 'type_45']
            ],
            $writer->exposed_params
        );
        $this->assertCount(1, $record_repo->exposed_deletions);
    }

    public function testRunDoNotUpdateNotHarvestedExposedRecordMarkedAsDeleted(): void
    {
        $harvester = new AutomaticPublisher(
            $this->getPublisher(),
            $this->getSettings(false, false, ['type', 'second type'], [12, 5], 123),
            $this->getObjectHandler([], 123, [12332]),
            $this->getStatusRepository([32 => 12332]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>', 45 => null], [45]),
            $this->getSearchFactory(32),
            new NullLOMRepository(),
            $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45 changed</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Withdrew 0 deprecated objects.<br>' .
            'Updated 0 published objects.',
            $result->exposed_message
        );
        $this->assertEmpty($record_repo->exposed_updates);
        $this->assertCount(1, $record_repo->exposed_deletions);
    }

    public function testRunWithUnforeseenError(): void
    {
        $harvester = new AutomaticPublisher(
            $this->getPublisher(),
            $this->getSettings(false, false, ['type', 'second type'], [12, 5]),
            $this->getObjectHandler(),
            $this->getStatusRepository([], [], true),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(JobResult::STATUS_FAIL, $result->exposed_status);
        $this->assertSame(
            'error',
            $result->exposed_message
        );
    }
}
