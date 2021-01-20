<?php

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Consumer\ConsumerFactory;

/**
 * Class ilFileObjectToStrageMigrationContainer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileObjectToStrageMigrationContainer
{
    /**
     * @var ilFileObjectToStorageDirectory
     */
    protected $dir;
    /**
     * @var
     */
    private $keep_originals = false;
    /**
     * @var ilDBInterface
     */
    private $db;
    /**
     * @var
     */
    private $resource_builder;
    /**
     * @var Manager
     */
    private $manager;
    /**
     * @var ConsumerFactory
     */
    private $consumer;
    protected $migration_log_handle;

    /**
     * ilFileObjectToStrageMigrationContainer constructor.
     * @param ilFileObjectToStorageDirectory $dir
     * @param ilDBInterface                  $db
     * @param                                $resource_builder
     * @param Manager                        $manager
     * @param ConsumerFactory                $consumer
     */
    public function __construct(
        ilFileObjectToStorageDirectory $dir,
        ilDBInterface $db,
        $resource_builder,
        Manager $manager,
        ConsumerFactory $consumer
    ) {
        $this->dir = $dir;
        $this->db = $db;
        $this->resource_builder = $resource_builder;
        $this->manager = $manager;
        $this->consumer = $consumer;
    }

    public function migrate() : void
    {
        $db = $this->db;
        $item = $this->dir;
        $resource = $this->getResource($db, $item);

        foreach ($item->getVersions() as $version) {
            try {
                $status = 'success';
                $aditional_info = '';
                $this->resource_builder->appendFromStream(
                    $resource,
                    Streams::ofResource(fopen($version->getPath(), 'rb')),
                    $this->keep_originals,
                    $version->getTitle(),
                    $version->getOwner()
                );
            } catch (Throwable $t) {
                $status = 'failed';
                $aditional_info = $t->getMessage();
                return;
            }
        }

        $this->resource_builder->store($resource);
        $db->manipulateF(
            'UPDATE file_data SET rid = %s WHERE file_id = %s',
            ['text', 'integer'],
            [$resource->getIdentification()->serialize(), $item->getObjectId()]
        );

        $item->tearDown();
    }

    /**
     * @param ilDBInterface                  $db
     * @param ilFileObjectToStorageDirectory $item
     * @return StorableResource
     */
    private function getResource(
        ilDBInterface $db,
        ilFileObjectToStorageDirectory $item
    ) : StorableResource {
        $r = $db->queryF("SELECT rid FROM file_data WHERE file_id = %s", ['integer'], [$item->getObjectId()]);
        $d = $db->fetchObject($r);

        if (isset($d->rid) && $d->rid !== '' && ($resource_identification = $this->manager->find($d->rid)) && $resource_identification !== null) {
            $resource = $this->resource_builder->get($resource_identification);
        } else {
            $resource = $this->resource_builder->newBlank();
        }
        return $resource;
    }
}
