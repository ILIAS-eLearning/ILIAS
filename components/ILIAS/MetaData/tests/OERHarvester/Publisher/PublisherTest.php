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

namespace ILIAS\MetaData\OERHarvester\Publisher;

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
use ILIAS\MetaData\OERHarvester\CronJob\Results\WrapperInterface;
use ILIAS\MetaData\OERHarvester\CronJob\Results\NullWrapper;
use ILIAS\MetaData\Repository\RepositoryInterface as LOMRepository;
use ILIAS\MetaData\Repository\NullRepository as NullLOMRepository;
use ILIAS\Cron\Job\JobResult;

class PublisherTest extends TestCase
{
    protected function getSettings(
        int $editorial_ref_id = 0,
        int $publishing_ref_id = 0,
        bool $automatic_publishing_enabled = false,
    ): SettingsInterface {
        return new class (
            $editorial_ref_id,
            $publishing_ref_id,
            $automatic_publishing_enabled
        ) extends NullSettings {
            public function __construct(
                protected int $editorial_ref_id,
                protected int $publishing_ref_id,
                protected bool $automatic_publishing_enabled
            ) {
            }

            public function isAutomaticPublishingEnabled(): bool
            {
                return $this->automatic_publishing_enabled;
            }

            public function getContainerRefIDForEditorialStep(): int
            {
                return $this->editorial_ref_id;
            }

            public function getContainerRefIDForPublishing(): int
            {
                return $this->publishing_ref_id;
            }
        };
    }

    /**
     * Returned ref_ids are always given by concatenation of target ref_id and obj_id.
     */
    protected function getObjectHandler(
        array $exclusive_ref_ids = [],
    ): ObjectHandler {
        return new class ($exclusive_ref_ids) extends NullObjectHandler {
            public array $exposed_ref_creations = [];
            public array $exposed_ref_deletions = [];

            public function __construct(
                protected array $exclusive_ref_ids
            ) {
            }

            public function referenceObjectInTargetContainer(int $obj_id, int $container_ref_id): int
            {
                $new_ref_id = (int) ($container_ref_id . $obj_id);
                $this->exposed_ref_creations[] = [
                    'obj_id' => $obj_id,
                    'container_ref_id' => $container_ref_id,
                    'new_ref_id' => $new_ref_id
                ];
                return $new_ref_id;
            }

            public function isOnlyReference(int $ref_id): bool
            {
                return in_array($ref_id, $this->exclusive_ref_ids);
            }

            public function deleteReference(int $ref_id): void
            {
                $this->exposed_ref_deletions[] = $ref_id;
            }
        };
    }

    protected function getExportHandler(
        int ...$already_have_export_obj_ids
    ): ExportHandler {
        return new class ($already_have_export_obj_ids) extends NullExportHandler {
            public array $exposed_created_exports_obj_ids = [];

            public function __construct(
                protected array $already_have_export_obj_ids
            ) {
            }

            public function hasPublicAccessExport(int $obj_id): bool
            {
                return in_array($obj_id, $this->already_have_export_obj_ids);
            }

            public function createPublicAccessExport(int $obj_id): void
            {
                $this->exposed_created_exports_obj_ids[] = $obj_id;
            }
        };
    }

    /**
     * Currently harvested objects are passed as obj_id => href_id
     */
    protected function getStatusRepository(
        array $currently_harvested = []
    ): StatusRepository {
        return new class ($currently_harvested) extends NullStatusRepository {
            public array $exposed_blocks = [];
            public array $exposed_deletions = [];
            public array $exposed_creations = [];

            public function __construct(
                protected array $currently_harvested
            ) {
            }

            public function setHarvestingBlocked(int $obj_id, bool $blocked): void
            {
                $this->exposed_blocks[] = [
                    'obj_id' => $obj_id,
                    'blocked' => $blocked
                ];
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

    protected function getExposedRecordRepository(
        int ...$has_record_obj_ids,
    ): ExposedRecordRepository {
        return new class ($has_record_obj_ids) extends NullExposedRecordRepository {
            public array $exposed_deletions = [];
            public array $exposed_updates = [];
            public array $exposed_creations = [];

            public function __construct(
                protected array $has_record_obj_ids
            ) {
            }

            public function doesRecordExistForObjID(int $obj_id): bool
            {
                return in_array($obj_id, $this->has_record_obj_ids);
            }

            public function updateRecord(int $obj_id, bool $is_deleted, ?\DOMDocument $metadata): void
            {
                $this->exposed_updates[] = [
                    'obj_id' => $obj_id,
                    'deleted' => $is_deleted,
                    'metadata' => $metadata?->saveXML()
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

    protected function getNullAccess(): \ilAccess
    {
        return $this->createMock(\ilAccess::class);
    }

    public function testBlock(): void
    {
        $publisher = new Publisher(
            $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository(),
            $this->getObjectHandler(),
            $this->getExportHandler(),
            $this->getSettings(),
            $this->getXMLWriter(),
            $this->getNullAccess()
        );

        $publisher->block(123);

        $this->assertSame(
            [[
                'obj_id' => 123,
                'blocked' => true
             ]],
            $status_repo->exposed_blocks
        );
    }

    public function testUnblock(): void
    {
        $publisher = new Publisher(
            $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository(),
            $this->getObjectHandler(),
            $this->getExportHandler(),
            $this->getSettings(),
            $this->getXMLWriter(),
            $this->getNullAccess()
        );

        $publisher->unblock(123);

        $this->assertSame([['obj_id' => 123, 'blocked' => false]], $status_repo->exposed_blocks);
    }

    public function testPublish(): void
    {
        $md = <<<XML
<?xml version="1.0"?>
<md>metadata</md>

XML;
        $publisher = new Publisher(
            $exposed_repo = $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository(),
            $object_handler = $this->getObjectHandler(),
            $export_handler = $this->getExportHandler(),
            $this->getSettings(0, 456),
            $this->getXMLWriter([123 => $md]),
            $this->getNullAccess()
        );

        $publisher->publish(123, 'type');

        $this->assertSame(
            [[
                'obj_id' => 123,
                'container_ref_id' => 456,
                'new_ref_id' => 456123
            ]],
            $object_handler->exposed_ref_creations
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'href_id' => 456123
             ]],
            $status_repo->exposed_creations
        );
        $this->assertSame(
            [123],
            $export_handler->exposed_created_exports_obj_ids
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'identifier' => 'il__type_123',
                 'metadata' => $md
             ]],
            $exposed_repo->exposed_creations
        );
    }

    public function testPublishWithAlreadyExportedObject(): void
    {
        $md = <<<XML
<?xml version="1.0"?>
<md>metadata</md>

XML;
        $publisher = new Publisher(
            $exposed_repo = $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository(),
            $object_handler = $this->getObjectHandler(),
            $export_handler = $this->getExportHandler(123),
            $this->getSettings(0, 456),
            $this->getXMLWriter([123 => $md]),
            $this->getNullAccess()
        );

        $publisher->publish(123, 'type');

        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'container_ref_id' => 456,
                 'new_ref_id' => 456123
             ]],
            $object_handler->exposed_ref_creations
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'href_id' => 456123
             ]],
            $status_repo->exposed_creations
        );
        $this->assertEmpty($export_handler->exposed_created_exports_obj_ids);
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'identifier' => 'il__type_123',
                 'metadata' => $md
             ]],
            $exposed_repo->exposed_creations
        );
    }

    public function testPublishWithAlreadyExistingRecord(): void
    {
        $md = <<<XML
<?xml version="1.0"?>
<md>metadata</md>

XML;
        $publisher = new Publisher(
            $exposed_repo = $this->getExposedRecordRepository(123),
            $status_repo = $this->getStatusRepository(),
            $object_handler = $this->getObjectHandler(),
            $export_handler = $this->getExportHandler(),
            $this->getSettings(0, 456),
            $this->getXMLWriter([123 => $md]),
            $this->getNullAccess()
        );

        $publisher->publish(123, 'type');

        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'container_ref_id' => 456,
                 'new_ref_id' => 456123
             ]],
            $object_handler->exposed_ref_creations
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'href_id' => 456123
             ]],
            $status_repo->exposed_creations
        );
        $this->assertSame(
            [123],
            $export_handler->exposed_created_exports_obj_ids
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'deleted' => false,
                 'metadata' => $md
             ]],
            $exposed_repo->exposed_updates
        );
    }

    public function testWithdraw(): void
    {
        $publisher = new Publisher(
            $exposed_repo = $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository([123 => 456123]),
            $object_handler = $this->getObjectHandler(),
            $this->getExportHandler(),
            $this->getSettings(),
            $this->getXMLWriter(),
            $this->getNullAccess()
        );

        $publisher->withdraw(123);

        $this->assertSame(
            [456123],
            $object_handler->exposed_ref_deletions
        );
        $this->assertSame(
            [123],
            $status_repo->exposed_deletions
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'deleted' => true,
                 'metadata' => null
             ]],
            $exposed_repo->exposed_updates
        );
        $this->assertEmpty($status_repo->exposed_blocks);
    }

    public function testWithdrawWithOnlyOneReference(): void
    {
        $publisher = new Publisher(
            $exposed_repo = $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository([123 => 456123]),
            $object_handler = $this->getObjectHandler([456123]),
            $this->getExportHandler(),
            $this->getSettings(),
            $this->getXMLWriter(),
            $this->getNullAccess()
        );

        $publisher->withdraw(123);

        $this->assertEmpty($object_handler->exposed_ref_deletions);
        $this->assertSame(
            [123],
            $status_repo->exposed_deletions
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'deleted' => true,
                 'metadata' => null
             ]],
            $exposed_repo->exposed_updates
        );
        $this->assertEmpty($status_repo->exposed_blocks);
    }

    public function testWithdrawWithAutomaticPublishing(): void
    {
        $publisher = new Publisher(
            $exposed_repo = $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository([123 => 456123]),
            $object_handler = $this->getObjectHandler(),
            $this->getExportHandler(),
            $this->getSettings(0, 0, true),
            $this->getXMLWriter(),
            $this->getNullAccess()
        );

        $publisher->withdraw(123);

        $this->assertSame(
            [456123],
            $object_handler->exposed_ref_deletions
        );
        $this->assertSame(
            [123],
            $status_repo->exposed_deletions
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'deleted' => true,
                 'metadata' => null
             ]],
            $exposed_repo->exposed_updates
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'blocked' => true
             ]],
            $status_repo->exposed_blocks
        );
    }

    public function testSubmit(): void
    {
        $publisher = new Publisher(
            $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository(),
            $object_handler = $this->getObjectHandler(),
            $export_handler = $this->getExportHandler(),
            $this->getSettings(456),
            $this->getXMLWriter(),
            $this->getNullAccess()
        );

        $publisher->submit(123);

        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'container_ref_id' => 456,
                 'new_ref_id' => 456123
             ]],
            $object_handler->exposed_ref_creations
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'href_id' => 456123
             ]],
            $status_repo->exposed_creations
        );
        $this->assertSame(
            [123],
            $export_handler->exposed_created_exports_obj_ids
        );
    }

    public function testSubmitWithAlreadyExportedObject(): void
    {
        $publisher = new Publisher(
            $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository(),
            $object_handler = $this->getObjectHandler(),
            $export_handler = $this->getExportHandler(123),
            $this->getSettings(456),
            $this->getXMLWriter(),
            $this->getNullAccess()
        );

        $publisher->submit(123);

        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'container_ref_id' => 456,
                 'new_ref_id' => 456123
             ]],
            $object_handler->exposed_ref_creations
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'href_id' => 456123
             ]],
            $status_repo->exposed_creations
        );
        $this->assertEmpty($export_handler->exposed_created_exports_obj_ids);
    }

    public function testAccept(): void
    {
        $md = <<<XML
<?xml version="1.0"?>
<md>metadata</md>

XML;
        $publisher = new Publisher(
            $exposed_repo = $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository([123 => 456123]),
            $object_handler = $this->getObjectHandler(),
            $this->getExportHandler(),
            $this->getSettings(456, 789),
            $this->getXMLWriter([123 => $md]),
            $this->getNullAccess()
        );

        $publisher->accept(123, 'type');

        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'container_ref_id' => 789,
                 'new_ref_id' => 789123
             ]],
            $object_handler->exposed_ref_creations
        );
        $this->assertSame(
            [456123],
            $object_handler->exposed_ref_deletions
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'href_id' => 789123
             ]],
            $status_repo->exposed_creations
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'identifier' => 'il__type_123',
                 'metadata' => $md
             ]],
            $exposed_repo->exposed_creations
        );
    }

    public function testAcceptWithAlreadyExistingRecord(): void
    {
        $md = <<<XML
<?xml version="1.0"?>
<md>metadata</md>

XML;
        $publisher = new Publisher(
            $exposed_repo = $this->getExposedRecordRepository(123),
            $status_repo = $this->getStatusRepository([123 => 456123]),
            $object_handler = $this->getObjectHandler(),
            $this->getExportHandler(),
            $this->getSettings(456, 789),
            $this->getXMLWriter([123 => $md]),
            $this->getNullAccess()
        );

        $publisher->accept(123, 'type');

        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'container_ref_id' => 789,
                 'new_ref_id' => 789123
             ]],
            $object_handler->exposed_ref_creations
        );
        $this->assertSame(
            [456123],
            $object_handler->exposed_ref_deletions
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'href_id' => 789123
             ]],
            $status_repo->exposed_creations
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'deleted' => false,
                 'metadata' => $md
             ]],
            $exposed_repo->exposed_updates
        );
    }

    public function testReject(): void
    {
        $publisher = new Publisher(
            $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository([123 => 456123]),
            $object_handler = $this->getObjectHandler(),
            $this->getExportHandler(),
            $this->getSettings(),
            $this->getXMLWriter(),
            $this->getNullAccess()
        );

        $publisher->reject(123);

        $this->assertSame(
            [456123],
            $object_handler->exposed_ref_deletions
        );
        $this->assertSame(
            [123],
            $status_repo->exposed_deletions
        );
        $this->assertEmpty($status_repo->exposed_blocks);
    }

    public function testRejectWithOnlyOneReference(): void
    {
        $publisher = new Publisher(
            $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository([123 => 456123]),
            $object_handler = $this->getObjectHandler([456123]),
            $this->getExportHandler(),
            $this->getSettings(),
            $this->getXMLWriter(),
            $this->getNullAccess()
        );

        $publisher->reject(123);

        $this->assertEmpty($object_handler->exposed_ref_deletions);
        $this->assertSame(
            [123],
            $status_repo->exposed_deletions
        );
        $this->assertEmpty($status_repo->exposed_blocks);
    }

    public function testRejectWithAutomaticPublishing(): void
    {
        $publisher = new Publisher(
            $this->getExposedRecordRepository(),
            $status_repo = $this->getStatusRepository([123 => 456123]),
            $object_handler = $this->getObjectHandler(),
            $this->getExportHandler(),
            $this->getSettings(0, 0, true),
            $this->getXMLWriter(),
            $this->getNullAccess()
        );

        $publisher->reject(123);

        $this->assertSame(
            [456123],
            $object_handler->exposed_ref_deletions
        );
        $this->assertSame(
            [123],
            $status_repo->exposed_deletions
        );
        $this->assertSame(
            [[
                 'obj_id' => 123,
                 'blocked' => true
             ]],
            $status_repo->exposed_blocks
        );
    }
}
