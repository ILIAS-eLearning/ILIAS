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

use ILIAS\DI\Container;
use ILIAS\ResourceStorage\Collection\CollectionBuilder;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Identification\UniqueIDIdentificationGenerator;
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
use ILIAS\ResourceStorage\Resource\Repository\CollectionDBRepository;

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
    protected CollectionBuilder $collection_builder;

    /**
     * ilResourceStorageMigrationHelper constructor.
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
        if (!defined("CLIENT_WEB_DIR")) {
            define("CLIENT_WEB_DIR", dirname(__DIR__, 4) . "/data/" . $client_id);
        }
        if (!defined("ILIAS_WEB_DIR")) {
            define("ILIAS_WEB_DIR", dirname(__DIR__, 4));
        }
        if (!defined("CLIENT_ID")) {
            define("CLIENT_ID", $client_id);
        }

        $this->stakeholder = $stakeholder;
        $this->client_data_dir = $client_data_dir;
        $this->database = $db;

        // Build Container
        $init = new InitResourceStorage();
        $container = new Container();
        $container['ilDB'] = $db;
        $storageConfiguration = new LocalConfig($client_data_dir);
        $f = new FlySystemFilesystemFactory();
        $container['filesystem.storage'] = $f->getLocal($storageConfiguration);

        $this->resource_builder = $init->getResourceBuilder($container);
        $this->collection_builder = new CollectionBuilder(
            new CollectionDBRepository($db)
        );
    }

    /**
     * @return \ilDatabaseInitializedObjective[]|\ilDatabaseUpdatedObjective[]|\ilIniFilesLoadedObjective[]
     */
    public static function getPreconditions(): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective(),
        ];
    }

    public function getClientDataDir(): string
    {
        return $this->client_data_dir;
    }

    public function getDatabase(): ilDBInterface
    {
        return $this->database;
    }

    public function getStakeholder(): ResourceStakeholder
    {
        return $this->stakeholder;
    }

    public function getResourceBuilder(): ResourceBuilder
    {
        return $this->resource_builder;
    }

    public function getCollectionBuilder(): CollectionBuilder
    {
        return $this->collection_builder;
    }

    public function moveFilesOfPathToCollection(
        string $absolute_path,
        int $resource_owner_id,
        int $collection_owner_user_id = ResourceCollection::NO_SPECIFIC_OWNER,
        ?Closure $file_name_callback = null,
        ?Closure $revision_name_callback = null
    ): ?ResourceCollectionIdentification {
        $collection = $this->getCollectionBuilder()->new($collection_owner_user_id);
        /** @var SplFileInfo $file_info */
        foreach (new DirectoryIterator($absolute_path) as $file_info) {
            if (!$file_info->isFile()) {
                continue;
            }
            $resource_id = $this->movePathToStorage(
                $file_info->getRealPath(),
                $resource_owner_id,
                $file_name_callback,
                $revision_name_callback
            );
            if ($resource_id !== null) {
                $collection->add($resource_id);
            }
        }
        if ($collection->count() === 0) {
            return null;
        }

        if ($this->getCollectionBuilder()->store($collection)) {
            return $collection->getIdentification();
        }
        return null;
    }

    public function moveFilesOfPatternToCollection(
        string $absolute_base_path,
        string $pattern,
        int $resource_owner_id,
        int $collection_owner_user_id = ResourceCollection::NO_SPECIFIC_OWNER,
        ?Closure $file_name_callback = null,
        ?Closure $revision_name_callback = null
    ): ?ResourceCollectionIdentification {
        $collection = $this->getCollectionBuilder()->new($collection_owner_user_id);

        $regex_iterator = new RecursiveRegexIterator(
            new RecursiveDirectoryIterator($absolute_base_path),
            $pattern,
            RecursiveRegexIterator::MATCH
        );

        foreach ($regex_iterator as $file_info) {
            if (!$file_info->isFile()) {
                continue;
            }
            $resource_id = $this->movePathToStorage(
                $file_info->getRealPath(),
                $resource_owner_id,
                $file_name_callback,
                $revision_name_callback
            );
            if ($resource_id !== null) {
                $collection->add($resource_id);
            }
        }
        if ($collection->count() === 0) {
            return null;
        }

        if ($this->getCollectionBuilder()->store($collection)) {
            return $collection->getIdentification();
        }
        return null;
    }

    public function movePathToStorage(
        string $absolute_path,
        int $owner_user_id,
        ?Closure $file_name_callback = null,
        ?Closure $revision_name_callback = null
    ): ?ResourceIdentification {
        $open_path = fopen($absolute_path, 'rb');
        if ($open_path === false) {
            return null;
        }
        $stream = Streams::ofResource($open_path);

        // create new resource from legacy files stream
        $revision_title = $revision_name_callback !== null
            ? $revision_name_callback(basename($absolute_path))
            : basename($absolute_path);

        $file_name = $file_name_callback !== null
            ? $file_name_callback(basename($absolute_path))
            : null;

        $resource = $this->resource_builder->newFromStream(
            $stream,
            new StreamInfoResolver(
                $stream,
                1,
                $owner_user_id,
                $revision_title,
                $file_name
            ),
            false
        );

        // add bibliographic stakeholder and store resource
        $resource->addStakeholder($this->stakeholder);
        $this->resource_builder->store($resource);

        return $resource->getIdentification();
    }
}
