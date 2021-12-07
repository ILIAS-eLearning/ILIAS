<?php

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\Lock\LockHandlerilDB;
use ILIAS\Filesystem\Provider\FlySystem\FlySystemFilesystemFactory;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\MaxNestingFileSystemStorageHandler;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Resource\InfoResolver\StreamInfoResolver;
use ILIAS\Setup\Environment;
use ILIAS\ResourceStorage\Revision\Repository\RevisionDBRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceDBRepository;
use ILIAS\ResourceStorage\Information\Repository\InformationDBRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderDBRepository;

/**
 * Class ilResourceStorageMigrationHelper
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilResourceStorageMigrationHelper
{
    protected ResourceStakeholder $stakeholder;
    protected string $client_data_dir;
    protected ilDBInterface $database;
    protected ResourceBuilder $resource_builder;

    /**
     * ilResourceStorageMigrationHelper constructor.
     * @param ResourceStakeholder $stakeholder
     * @param string              $client_data_dir
     * @param ilDBInterface       $database
     */
    public function __construct(
        ResourceStakeholder $stakeholder,
        Environment $environment
    ) {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $ilias_ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        $client_id = $environment->getResource(Environment::RESOURCE_CLIENT_ID);
        $data_dir = $ilias_ini->readVariable('clients', 'datadir');
        $client_data_dir = "{$data_dir}/{$client_id}";

        $this->stakeholder = $stakeholder;
        $this->client_data_dir = $client_data_dir;
        $this->database = $db;
        $file_system_factory = new FlySystemFilesystemFactory();
        $this->resource_builder = new ResourceBuilder(
            new StorageHandlerFactory([
                new MaxNestingFileSystemStorageHandler($file_system_factory->getLocal(
                    new LocalConfig($this->client_data_dir)
                ),
                    Location::STORAGE)
            ]),
            new RevisionDBRepository($db),
            new ResourceDBRepository($db),
            new InformationDBRepository($db),
            new StakeholderDBRepository($db),
            new LockHandlerilDB($this->database)
        );
    }

    public static function getPreconditions() : array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective(),
        ];
    }

    public function getClientDataDir() : string
    {
        return $this->client_data_dir;
    }

    public function getDatabase() : ilDBInterface
    {
        return $this->database;
    }

    public function getStakeholder() : ResourceStakeholder
    {
        return $this->stakeholder;
    }

    public function getResourceBuilder() : ResourceBuilder
    {
        return $this->resource_builder;
    }

    public function movePathToStorage(string $absolute_path, int $owner_user_id) : ResourceIdentification
    {
        $stream = Streams::ofResource(fopen($absolute_path, 'rb'));
        // create new resource from legacy files stream
        $resource = $this->resource_builder->newFromStream(
            $stream,
            new StreamInfoResolver(
                $stream,
                1,
                $owner_user_id,
                basename($absolute_path)
            ),
            false
        );

        // add bibliographic stakeholder and store resource
        $resource->addStakeholder($this->stakeholder);
        $this->resource_builder->store($resource);

        return $resource->getIdentification();
    }

}
