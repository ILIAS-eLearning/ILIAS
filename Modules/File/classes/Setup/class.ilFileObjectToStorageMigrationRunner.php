<?php

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\Information\Repository\InformationARRepository;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Resource\Repository\ResourceARRepository;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Revision\Repository\RevisionARRepository;
use ILIAS\ResourceStorage\StorageHandler\FileSystemStorageHandler;
use ILIAS\ResourceStorage\Lock\LockHandlerilDB;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderARRepository;
use ILIAS\ResourceStorage\Consumer\ConsumerFactory;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\Filesystem\Filesystem;

class ilFileObjectToStorageMigrationRunner
{

    /**
     * @var ConsumerFactory
     */
    protected $consumer_factory;
    /**
     * @var Manager
     */
    protected $storage_manager;
    /**
     * @var ResourceBuilder
     */
    protected $resource_builder;
    /**
     * @var false|resource
     */
    protected $migration_log_handle;
    /**
     * @var Filesystem
     */
    protected $file_system;
    /**
     * @var ilDBInterface
     */
    protected $database;
    /**
     * @var bool
     */
    protected $keep_originals = false;
    /**
     * @var null|int
     */
    protected $migrate_to_new_object_id = null;

    /**
     * ilFileObjectToStorageMigration constructor.
     * @param Filesystem    $file_system
     * @param ilDBInterface $database
     */
    public function __construct(Filesystem $file_system, ilDBInterface $database, string $log_file_path)
    {
        $this->file_system = $file_system;
        $this->database = $database;
        $this->migration_log_handle = fopen($log_file_path, 'ab');

        $storage_handler = new FileSystemStorageHandler($this->file_system, Location::STORAGE);
        $builder = new ResourceBuilder(
            $storage_handler,
            new RevisionARRepository(),
            new ResourceARRepository(),
            new InformationARRepository(),
            new StakeholderARRepository(),
            new LockHandlerilDB($database)
        );
        $this->resource_builder = $builder;
        $this->storage_manager = new Manager($builder);
        $this->consumer_factory = new ConsumerFactory(new StorageHandlerFactory([$storage_handler]));
    }

    /**
     * @inheritDoc
     */
    public function migrate(ilFileObjectToStorageDirectory $item) : void
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
                $aditional_info
            );
        }

        $this->resource_builder->store($resource);
        $this->database->manipulateF(
            'UPDATE file_data SET rid = %s WHERE file_id = %s',
            ['text', 'integer'],
            [$resource->getIdentification()->serialize(), $object_id]
        );
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
        string $aditional_info = null
    ) : void {
        fputcsv($this->migration_log_handle, [
            $object_id,
            $old_path,
            $rid,
            $version,
            $status,
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
    ) : StorableResource {
        $r = $this->database->queryF("SELECT rid FROM file_data WHERE file_id = %s", ['integer'],
            [$item->getObjectId()]);
        $d = $this->database->fetchObject($r);

        if (isset($d->rid) && $d->rid !== '' && ($resource_identification = $this->storage_manager->find($d->rid)) && $resource_identification !== null) {
            $resource = $this->resource_builder->get($resource_identification);
        } else {
            $resource = $this->resource_builder->newBlank();
        }
        return $resource;
    }

    /**
     * @return int|null
     */
    public function getMigrateToNewObjectId() : ?int
    {
        return $this->migrate_to_new_object_id;
    }

    /**
     * @param int|null $migrate_to_new_object_id
     * @return ilFileObjectToStorageMigrationRunner
     */
    public function setMigrateToNewObjectId(?int $migrate_to_new_object_id) : ilFileObjectToStorageMigrationRunner
    {
        $this->migrate_to_new_object_id = $migrate_to_new_object_id;
        $this->keep_originals = true;
        return $this;
    }

}
