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

namespace ILIAS\MetaData\OERHarvester;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface;
use ILIAS\MetaData\OERHarvester\Settings\NullSettings;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\HandlerInterface as ObjectHandler;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\NullHandler as NullObjectHandler;
use ILIAS\MetaData\OERHarvester\Export\HandlerInterface as ExportHandler;
use ILIAS\MetaData\OERHarvester\Export\NullHandler as NullExportHandler;
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
use ILIAS\MetaData\OERHarvester\Results\WrapperInterface;
use ILIAS\MetaData\OERHarvester\Results\NullWrapper;
use ILIAS\MetaData\Repository\RepositoryInterface as LOMRepository;
use ILIAS\MetaData\Repository\NullRepository as NullLOMRepository;

class HarvesterTest extends TestCase
{
    protected function getSettings(
        array $types = [],
        array $copyright_ids = [],
        int $harvesting_target_ref_id = 0,
        int $exposed_source_ref_id = 0
    ): SettingsInterface {
        return new class (
            $types,
            $copyright_ids,
            $harvesting_target_ref_id,
            $exposed_source_ref_id
        ) extends NullSettings {
            public function __construct(
                protected array $types,
                protected array $copyright_ids,
                protected int $harvesting_target_ref_id,
                protected int $exposed_source_ref_id
            ) {
            }

            public function getObjectTypesSelectedForHarvesting(): array
            {
                return $this->types;
            }

            public function getCopyrightEntryIDsSelectedForHarvesting(): array
            {
                return $this->copyright_ids;
            }

            public function getContainerRefIDForHarvesting(): int
            {
                return $this->harvesting_target_ref_id;
            }

            public function getContainerRefIDForExposing(): int
            {
                return $this->exposed_source_ref_id;
            }
        };
    }

    /**
     * Returned ref_ids are always given by concatenation of target ref_id and obj_id.
     * Returned types are always 'type_{ref_id}'
     */
    protected function getObjectHandler(
        array $deleted_obj_ids = [],
        int $valid_source_container = 0,
        array $obj_ids_referenced_in_container = [],
        ?int $throw_error_on_deletion_ref_id = null,
        ?int $throw_error_on_ref_creation_obj_id = null
    ): ObjectHandler {
        return new class (
            $deleted_obj_ids,
            $valid_source_container,
            $obj_ids_referenced_in_container,
            $throw_error_on_deletion_ref_id,
            $throw_error_on_ref_creation_obj_id
        ) extends NullObjectHandler {
            public array $exposed_ref_creations = [];
            public array $exposed_ref_deletions = [];

            public function __construct(
                protected array $deleted_obj_ids,
                protected int $valid_target_container,
                protected array $obj_ids_referenced_in_container,
                protected ?int $throw_error_on_deletion_ref_id = null,
                protected ?int $throw_error_on_ref_creation_obj_id = null
            ) {
            }

            public function referenceObjectInTargetContainer(int $obj_id, int $container_ref_id): int
            {
                if ($obj_id === $this->throw_error_on_ref_creation_obj_id) {
                    throw new \ilMDOERHarvesterException('error');
                }
                $new_ref_id = (int) ($container_ref_id . $obj_id);
                $this->exposed_ref_creations[] = [
                    'obj_id' => $obj_id,
                    'container_ref_id' => $container_ref_id,
                    'new_ref_id' => $new_ref_id
                ];
                return $new_ref_id;
            }

            public function getObjectReferenceIDInContainer(int $obj_id, int $container_ref_id): ?int
            {
                if (in_array($obj_id, $this->obj_ids_referenced_in_container)) {
                    return (int) ($container_ref_id . $obj_id);
                }
                return null;
            }

            public function isObjectDeleted(int $obj_id): bool
            {
                return in_array($obj_id, $this->deleted_obj_ids);
            }

            public function deleteReference(int $ref_id): void
            {
                if ($ref_id === $this->throw_error_on_deletion_ref_id) {
                    throw new \ilMDOERHarvesterException('error');
                }
                $this->exposed_ref_deletions[] = $ref_id;
            }

            public function getTypeOfReferencedObject(int $ref_id): string
            {
                return 'type_' . $ref_id;
            }
        };
    }

    protected function getExportHandler(
        ?int $throw_exception_for_id = null,
        int ...$already_have_export_obj_ids
    ): ExportHandler {
        return new class ($throw_exception_for_id, $already_have_export_obj_ids) extends NullExportHandler {
            public array $exposed_created_exports_obj_ids = [];

            public function __construct(
                protected ?int $throw_exception_for_id,
                protected array $already_have_export_obj_ids
            ) {
            }

            public function hasPublicAccessExport(int $obj_id): bool
            {
                if ($this->throw_exception_for_id === $obj_id) {
                    throw new \Exception('error');
                }
                return in_array($obj_id, $this->already_have_export_obj_ids);
            }

            public function createPublicAccessExport(int $obj_id): void
            {
                if ($this->throw_exception_for_id === $obj_id) {
                    throw new \Exception('error');
                }
                $this->exposed_created_exports_obj_ids[] = $obj_id;
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
            public array $exposed_deletions = [];
            public array $exposed_creations = [];

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
                return $this->currently_harvested[$obj_id];
            }

            public function deleteHarvestRefID(int $obj_id): void
            {
                $this->exposed_deletions[] = $obj_id;
            }

            public function setHarvestRefID(int $obj_id, int $harvested_ref_id): void
            {
                $this->exposed_creations[] = [
                    'obj_id' => $obj_id,
                    'href_id' => $harvested_ref_id
                ];
            }
        };
    }

    /**
     * Records are passed as array via obj_id => metadata-xml as string
     */
    protected function getExposedRecordRepository(array $returned_records = []): ExposedRecordRepository
    {
        return new class ($returned_records) extends NullExposedRecordRepository {
            public array $exposed_deletions = [];
            public array $exposed_updates = [];
            public array $exposed_creations = [];

            public function __construct(protected array $returned_records)
            {
            }

            public function getRecords(
                ?\DateTimeImmutable $from = null,
                ?\DateTimeImmutable $until = null,
                ?int $limit = null,
                ?int $offset = null
            ): \Generator {
                foreach ($this->returned_records as $obj_id => $metadata) {
                    yield new class ($obj_id, $metadata) extends NullRecord {
                        public function __construct(
                            protected int $obj_id,
                            protected string $metadata
                        ) {
                        }

                        public function infos(): RecordInfosInterface
                        {
                            return new class ($this->obj_id) extends NullRecordInfos {
                                public function __construct(protected int $obj_id)
                                {
                                }

                                public function objID(): int
                                {
                                    return $this->obj_id;
                                }
                            };
                        }

                        public function metadata(): \DOMDocument
                        {
                            $xml = new \DOMDocument();
                            $xml->loadXML($this->metadata);
                            return $xml;
                        }
                    };
                }
            }

            public function deleteRecord(int $obj_id): void
            {
                $this->exposed_deletions[] = ['obj_id' => $obj_id];
            }

            public function updateRecord(int $obj_id, \DOMDocument $metadata): void
            {
                $this->exposed_updates[] = [
                    'obj_id' => $obj_id,
                    'metadata' => $metadata->saveXML()
                ];
            }

            public function createRecord(int $obj_id, string $identifier, \DOMDocument $metadata): void
            {
                $this->exposed_creations[] = [
                    'obj_id' => $obj_id,
                    'identifier' => $identifier,
                    'metadata' => $metadata->saveXML()
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

    public function testRunDeleteDeprecatedReferenceIncorrectTypeOrCopyright(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $object_handler = $this->getObjectHandler(),
            $this->getExportHandler(),
            $status_repo = $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $this->getExposedRecordRepository(),
            $search_factory = $this->getSearchFactory(45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 1 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertSame(
            [['restricted' => true, 'types' => ['type', 'second type'], 'entries' => [12, 5]]],
            $search_factory->exposed_search_params
        );
        $this->assertSame([32], $status_repo->exposed_deletions);
        $this->assertSame([12332], $object_handler->exposed_ref_deletions);
    }

    public function testRunDeleteDeprecatedReferenceBlocked(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $object_handler = $this->getObjectHandler(),
            $this->getExportHandler(),
            $status_repo = $this->getStatusRepository([32 => 12332, 45 => 12345], [32]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(45, 32),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 1 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertSame([32], $status_repo->exposed_deletions);
        $this->assertSame([12332], $object_handler->exposed_ref_deletions);
    }

    public function testRunDeleteDeprecatedReferenceObjectDeleted(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $object_handler = $this->getObjectHandler([32]),
            $this->getExportHandler(),
            $status_repo = $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(45, 32),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 1 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertSame([32], $status_repo->exposed_deletions);
        $this->assertSame([12332], $object_handler->exposed_ref_deletions);
    }

    public function testRunDeleteDeprecatedReferenceContinueDespiteError(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $object_handler = $this->getObjectHandler([], 0, [], 12345),
            $this->getExportHandler(),
            $status_repo = $this->getStatusRepository([32 => 12332, 45 => 12345, 67 => 12367]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 2 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertSame([32, 67], $status_repo->exposed_deletions);
        $this->assertSame([12332, 12367], $object_handler->exposed_ref_deletions);
    }

    public function testRunHarvestObject(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $object_handler = $this->getObjectHandler(),
            $export_handler = $this->getExportHandler(),
            $status_repo = $this->getStatusRepository([32 => 12332]),
            $this->getExposedRecordRepository(),
            $search_factory = $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 1 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
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
        $this->assertSame(
            [['obj_id' => 45, 'href_id' => 12345]],
            $status_repo->exposed_creations
        );
        $this->assertSame(
            [['obj_id' => 45, 'container_ref_id' => 123, 'new_ref_id' => 12345]],
            $object_handler->exposed_ref_creations
        );
        $this->assertSame([45], $export_handler->exposed_created_exports_obj_ids);
    }

    public function testRunDoNotHarvestBlockedObject(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $object_handler = $this->getObjectHandler(),
            $export_handler = $this->getExportHandler(),
            $status_repo = $this->getStatusRepository([32 => 12332], [45]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($status_repo->exposed_creations);
        $this->assertEmpty($object_handler->exposed_ref_creations);
        $this->assertEmpty($export_handler->exposed_created_exports_obj_ids);
    }

    public function testRunDoNotHarvestDeletedObject(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $object_handler = $this->getObjectHandler([45]),
            $export_handler = $this->getExportHandler(),
            $status_repo = $this->getStatusRepository([32 => 12332]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($status_repo->exposed_creations);
        $this->assertEmpty($object_handler->exposed_ref_creations);
        $this->assertEmpty($export_handler->exposed_created_exports_obj_ids);
    }

    public function testRunDoNotHarvestAlreadyHarvestedObject(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $object_handler = $this->getObjectHandler(),
            $export_handler = $this->getExportHandler(),
            $status_repo = $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($status_repo->exposed_creations);
        $this->assertEmpty($object_handler->exposed_ref_creations);
        $this->assertEmpty($export_handler->exposed_created_exports_obj_ids);
    }

    public function testRunDoNotHarvestIfNoTargetContainerIsSet(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 0, 456),
            $object_handler = $this->getObjectHandler(),
            $export_handler = $this->getExportHandler(),
            $status_repo = $this->getStatusRepository([32 => 12332]),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($status_repo->exposed_creations);
        $this->assertEmpty($object_handler->exposed_ref_creations);
        $this->assertEmpty($export_handler->exposed_created_exports_obj_ids);
    }

    public function testRunHarvestObjectContinueDespiteError(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $object_handler = $this->getObjectHandler([], 0, [], null, 45),
            $export_handler = $this->getExportHandler(),
            $status_repo = $this->getStatusRepository(),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(32, 45, 67),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 2 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertSame(
            [
                ['obj_id' => 32, 'href_id' => 12332],
                ['obj_id' => 67, 'href_id' => 12367]
            ],
            $status_repo->exposed_creations
        );
        $this->assertSame(
            [
                ['obj_id' => 32, 'container_ref_id' => 123, 'new_ref_id' => 12332],
                ['obj_id' => 67, 'container_ref_id' => 123, 'new_ref_id' => 12367]
            ],
            $object_handler->exposed_ref_creations
        );
        $this->assertSame([32, 67], $export_handler->exposed_created_exports_obj_ids);
    }

    public function testRunHarvestObjectDoNotExportWhenExportExists(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $object_handler = $this->getObjectHandler(),
            $export_handler = $this->getExportHandler(null, 45),
            $status_repo = $this->getStatusRepository([32 => 12332]),
            $this->getExposedRecordRepository(),
            $search_factory = $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 1 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
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
        $this->assertSame(
            [['obj_id' => 45, 'href_id' => 12345]],
            $status_repo->exposed_creations
        );
        $this->assertSame(
            [['obj_id' => 45, 'container_ref_id' => 123, 'new_ref_id' => 12345]],
            $object_handler->exposed_ref_creations
        );
        $this->assertEmpty($export_handler->exposed_created_exports_obj_ids);
    }

    public function testRunHarvestObjectContinueDespiteExportError(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $object_handler = $this->getObjectHandler(),
            $export_handler = $this->getExportHandler(45),
            $status_repo = $this->getStatusRepository(),
            $this->getExposedRecordRepository(),
            $this->getSearchFactory(32, 45, 67),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 3 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertSame(
            [
                ['obj_id' => 32, 'href_id' => 12332],
                ['obj_id' => 45, 'href_id' => 12345],
                ['obj_id' => 67, 'href_id' => 12367]
            ],
            $status_repo->exposed_creations
        );
        $this->assertSame(
            [
                ['obj_id' => 32, 'container_ref_id' => 123, 'new_ref_id' => 12332],
                ['obj_id' => 45, 'container_ref_id' => 123, 'new_ref_id' => 12345],
                ['obj_id' => 67, 'container_ref_id' => 123, 'new_ref_id' => 12367]
            ],
            $object_handler->exposed_ref_creations
        );
        $this->assertSame([32, 67], $export_handler->exposed_created_exports_obj_ids);
    }

    public function testRunDeleteExposedRecordIncorrectTypeOrCopyright(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $this->getObjectHandler([], 456, [32, 45]),
            $this->getExportHandler(),
            $this->getStatusRepository([32 => 12332]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>', 45 => '<el>45</el>']),
            $search_factory = $this->getSearchFactory(32),
            new NullLOMRepository(),
            $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 1 exposed records.',
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
        $this->assertEmpty($record_repo->exposed_creations);
        $this->assertEmpty($record_repo->exposed_updates);
        $this->assertSame(
            [['obj_id' => 45]],
            $record_repo->exposed_deletions
        );
    }

    public function testRunDeleteExposedRecordBlocked(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $this->getObjectHandler([], 456, [32, 45]),
            $this->getExportHandler(),
            $this->getStatusRepository([32 => 12332], [45]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>', 45 => '<el>45</el>']),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 1 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($record_repo->exposed_creations);
        $this->assertEmpty($record_repo->exposed_updates);
        $this->assertSame(
            [['obj_id' => 45]],
            $record_repo->exposed_deletions
        );
    }

    public function testRunDeleteExposedRecordObjectDeleted(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $this->getObjectHandler([45], 456, [32, 45]),
            $this->getExportHandler(),
            $this->getStatusRepository([32 => 12332]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>', 45 => '<el>45</el>']),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 1 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($record_repo->exposed_creations);
        $this->assertEmpty($record_repo->exposed_updates);
        $this->assertSame(
            [['obj_id' => 45]],
            $record_repo->exposed_deletions
        );
    }

    public function testRunDeleteExposedRecordNotInSourceContainer(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $this->getObjectHandler([], 456, [32]),
            $this->getExportHandler(),
            $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>', 45 => '<el>45</el>']),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 1 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($record_repo->exposed_creations);
        $this->assertEmpty($record_repo->exposed_updates);
        $this->assertSame(
            [['obj_id' => 45]],
            $record_repo->exposed_deletions
        );
    }

    public function testRunUpdateExposedRecord(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $this->getObjectHandler([], 456, [32, 45]),
            $this->getExportHandler(),
            $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>', 45 => '<el>45</el>']),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $writer = $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45 changed</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 1 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($record_repo->exposed_creations);
        $this->assertCount(1, $record_repo->exposed_updates);
        $this->assertSame(45, $record_repo->exposed_updates[0]['obj_id']);
        $this->assertXmlStringEqualsXmlString(
            '<el>45 changed</el>',
            $record_repo->exposed_updates[0]['metadata']
        );
        $this->assertEmpty($record_repo->exposed_deletions);
        $this->assertEquals(
            [
                ['obj_id' => 32, 'ref_id' => 45632, 'type' => 'type_45632'],
                ['obj_id' => 45, 'ref_id' => 45645, 'type' => 'type_45645']
            ],
            $writer->exposed_params
        );
    }

    public function testRunCreateNewExposedRecord(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $this->getObjectHandler([], 456, [32, 45]),
            $this->getExportHandler(),
            $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>']),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $writer = $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45 new</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_OK, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 1 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($record_repo->exposed_updates);
        $this->assertCount(1, $record_repo->exposed_creations);
        $this->assertSame(45, $record_repo->exposed_creations[0]['obj_id']);
        $this->assertSame('il__type_45645_45', $record_repo->exposed_creations[0]['identifier']);
        $this->assertXmlStringEqualsXmlString(
            '<el>45 new</el>',
            $record_repo->exposed_creations[0]['metadata']
        );
        $this->assertEmpty($record_repo->exposed_deletions);
        $this->assertEquals(
            [
                ['obj_id' => 32, 'ref_id' => 45632, 'type' => 'type_45632'],
                ['obj_id' => 45, 'ref_id' => 45645, 'type' => 'type_45645']
            ],
            $writer->exposed_params
        );
    }

    public function testRunDoNotCreateNewExposedRecordWhenBlocked(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $this->getObjectHandler([], 456, [32, 45]),
            $this->getExportHandler(),
            $this->getStatusRepository([32 => 12332], [45]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>']),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $writer = $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45 new</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($record_repo->exposed_updates);
        $this->assertEmpty($record_repo->exposed_creations);
        $this->assertEmpty($record_repo->exposed_deletions);
    }

    public function testRunDoNotCreateNewExposedRecordWhenObjectDeleted(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $this->getObjectHandler([45], 456, [32, 45]),
            $this->getExportHandler(),
            $this->getStatusRepository([32 => 12332]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>']),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $writer = $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45 new</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($record_repo->exposed_updates);
        $this->assertEmpty($record_repo->exposed_creations);
        $this->assertEmpty($record_repo->exposed_deletions);
    }

    public function testRunDoNotCreateNewExposedRecordWhenNotInSourceContainer(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 456),
            $this->getObjectHandler([], 456, [32]),
            $this->getExportHandler(),
            $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>']),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $writer = $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45 new</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($record_repo->exposed_updates);
        $this->assertEmpty($record_repo->exposed_creations);
        $this->assertEmpty($record_repo->exposed_deletions);
    }

    public function testRunDoNotCreateNewExposedRecordWhenNoSourceContainerIsSet(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5], 123, 0),
            $this->getObjectHandler([], 456, [32, 45]),
            $this->getExportHandler(),
            $this->getStatusRepository([32 => 12332, 45 => 12345]),
            $record_repo = $this->getExposedRecordRepository([32 => '<el>32</el>']),
            $this->getSearchFactory(32, 45),
            new NullLOMRepository(),
            $writer = $this->getXMLWriter([32 => '<el>32</el>', 45 => '<el>45 new</el>']),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_NO_ACTION, $result->exposed_status);
        $this->assertSame(
            'Deleted 0 deprecated references.<br>' .
            'Created 0 new references.<br>' .
            'Created, updated, or deleted 0 exposed records.',
            $result->exposed_message
        );
        $this->assertEmpty($record_repo->exposed_updates);
        $this->assertEmpty($record_repo->exposed_creations);
        $this->assertEmpty($record_repo->exposed_deletions);
    }

    public function testRunWithUnforeseenError(): void
    {
        $harvester = new Harvester(
            $this->getSettings(['type', 'second type'], [12, 5]),
            $object_handler = $this->getObjectHandler(),
            $this->getExportHandler(),
            $status_repo = $this->getStatusRepository([], [], true),
            $this->getExposedRecordRepository(),
            $search_factory = $this->getSearchFactory(),
            new NullLOMRepository(),
            $this->getXMLWriter(),
            $this->getNullLogger()
        );

        $result = $harvester->run($this->getCronResultWrapper());

        $this->assertSame(\ILIAS\Cron\Job\JobResult::STATUS_FAIL, $result->exposed_status);
        $this->assertSame(
            'error',
            $result->exposed_message
        );
    }
}
