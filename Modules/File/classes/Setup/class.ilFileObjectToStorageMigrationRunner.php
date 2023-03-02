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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Lock\LockHandlerilDB;
use ILIAS\ResourceStorage\Consumer\ConsumerFactory;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\Filesystem\Filesystem;
use ILIAS\ResourceStorage\Policy\FileNamePolicyException;
use ILIAS\ResourceStorage\StorageHandler\FileSystemStorageHandlerV2;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\MaxNestingFileSystemStorageHandler;
use ILIAS\ResourceStorage\Information\Repository\InformationDBRepository;
use ILIAS\ResourceStorage\Revision\Repository\RevisionDBRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceDBRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderDBRepository;
use ILIAS\ResourceStorage\Preloader\StandardRepositoryPreloader;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

class ilFileObjectToStorageMigrationRunner
{
    protected string $movement_implementation;

    protected ConsumerFactory $consumer_factory;
    protected Manager $storage_manager;
    protected ResourceBuilder $resource_builder;
    /**
     * @var false|resource
     */
    protected $migration_log_handle;
    protected Filesystem $file_system;
    protected ilDBInterface $database;
    protected bool $keep_originals = false;
    protected ?int $migrate_to_new_object_id = null;
    protected ResourceStakeholder $stakeholder;

    public function __construct(
        Filesystem $file_system,
        ilResourceStorageMigrationHelper $irss_helper,
        string $log_file_name
    ) {
        $this->file_system = $file_system;
        $this->database = $irss_helper->getDatabase();

        $legacy_directory = $irss_helper->getClientDataDir() . '/ilFile/';
        if (is_dir($legacy_directory)) {
            $this->migration_log_handle = fopen($legacy_directory . $log_file_name, 'ab');
        } else {
            $this->migration_log_handle = false;
        }

        $this->resource_builder = $irss_helper->getResourceBuilder();
        $this->storage_manager = $irss_helper->getManager();
        $this->stakeholder = $irss_helper->getStakeholder();
    }

    /**
     * @inheritDoc
     */
    public function migrate(ilFileObjectToStorageDirectory $item): void
    {
        $resource = $this->getResource($item);

        $object_id = $this->getMigrateToNewObjectId() ?? $item->getObjectId();
        foreach ($item->getVersions() as $version) {
            try {
                $status = 'success';
                $aditional_info = '';

                $stream = Streams::ofResource(fopen($version->getPath(), 'rb'));

                $info_resolver = new ilFileObjectToStorageInfoResolver(
                    $stream,
                    $version->getVersion(),
                    $version->getOwner(),
                    $version->getTitle(),
                    (new DateTimeImmutable())->setTimestamp($version->getCreationDateTimestamp())
                );

                $this->resource_builder->appendFromStream(
                    $resource,
                    $stream,
                    $info_resolver,
                    $this->keep_originals
                );
            } catch (Throwable $t) {
                $status = 'failed';
                $aditional_info = $t->getMessage();
            }

            $this->logMigratedFile(
                $object_id,
                $resource->getIdentification()->serialize(),
                $version->getVersion(),
                $version->getPath(),
                $status,
                $this->movement_implementation,
                $aditional_info
            );
        }
        $resource->addStakeholder($this->stakeholder);
        try {
            $this->resource_builder->store($resource);
            $this->database->manipulateF(
                'UPDATE file_data SET rid = %s WHERE file_id = %s',
                ['text', 'integer'],
                [$resource->getIdentification()->serialize(), $object_id]
            );
        } catch (FileNamePolicyException $e) {
            // continue
        }

        if (null === $this->getMigrateToNewObjectId()) {
            $item->tearDown();
        }
    }

    private function logMigratedFile(
        int $object_id,
        string $rid,
        int $version,
        string $old_path,
        string $status,
        string $movement_implementation,
        string $aditional_info = null
    ): void {
        if (!$this->migration_log_handle) {
            return;
        }
        fputcsv($this->migration_log_handle, [
            $object_id,
            $old_path,
            $rid,
            $version,
            $status,
            $movement_implementation,
            $aditional_info
        ], ";");
    }

    /**
     * @param ilFileObjectToStorageDirectory $item
     * @return StorableResource
     * @throws \ILIAS\ResourceStorage\Resource\ResourceNotFoundException
     */
    private function getResource(
        ilFileObjectToStorageDirectory $item
    ): StorableResource {
        $r = $this->database->queryF(
            "SELECT rid FROM file_data WHERE file_id = %s",
            ['integer'],
            [$item->getObjectId()]
        );
        $d = $this->database->fetchObject($r);

        if (isset($d->rid) && $d->rid !== '' && ($resource_identification = $this->storage_manager->find(
            $d->rid
        )) && $resource_identification !== null) {
            $resource = $this->resource_builder->get($resource_identification);
        } else {
            $resource = $this->resource_builder->newBlank();
        }
        return $resource;
    }

    /**
     * @return int|null
     */
    public function getMigrateToNewObjectId(): ?int
    {
        return $this->migrate_to_new_object_id;
    }

    /**
     * @param int|null $migrate_to_new_object_id
     * @return ilFileObjectToStorageMigrationRunner
     */
    public function setMigrateToNewObjectId(?int $migrate_to_new_object_id): ilFileObjectToStorageMigrationRunner
    {
        $this->migrate_to_new_object_id = $migrate_to_new_object_id;
        $this->keep_originals = true;
        return $this;
    }
}
