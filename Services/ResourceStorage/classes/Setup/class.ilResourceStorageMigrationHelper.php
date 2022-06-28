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
        $file_system_factory = new FlySystemFilesystemFactory();
        $this->resource_builder = new ResourceBuilder(
            new StorageHandlerFactory([
                new MaxNestingFileSystemStorageHandler(
                    $file_system_factory->getLocal(
                        new LocalConfig($this->client_data_dir)
                    ),
                    Location::STORAGE
                )
            ]),
            new RevisionDBRepository($db),
            new ResourceDBRepository($db),
            new InformationDBRepository($db),
            new StakeholderDBRepository($db),
            new LockHandlerilDB($this->database)
        );
    }

    /**
     * @return \ilDatabaseInitializedObjective[]|\ilDatabaseUpdatedObjective[]|\ilIniFilesLoadedObjective[]
     */
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

    public function movePathToStorage(string $absolute_path, int $owner_user_id) : ?ResourceIdentification
    {
        $open_path = fopen($absolute_path, 'rb');
        if ($open_path === false) {
            return null;
        }
        $stream = Streams::ofResource($open_path);
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
