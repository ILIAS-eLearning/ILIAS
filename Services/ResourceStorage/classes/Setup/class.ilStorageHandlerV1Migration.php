<?php

use ILIAS\Setup\CLI\IOWrapper;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ILIAS\ResourceStorage\StorageHandler\Migrator;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FlySystem\FlySystemFilesystemFactory;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\FileSystemStorageHandler;
use ILIAS\ResourceStorage\Policy\FileNamePolicyStack;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Lock\LockHandlerilDB;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderARRepository;
use ILIAS\ResourceStorage\Information\Repository\InformationARRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceARRepository;
use ILIAS\ResourceStorage\Revision\Repository\RevisionARRepository;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\MaxNestingFileSystemStorageHandler;
use ILIAS\DI\Container;

/**
 * Class ilStorageHandlerV1Migration
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilStorageHandlerV1Migration implements Migration
{
    /**
     * @var \DirectoryIterator
     */
    protected $iterator;
    /**
     * @var \ilDBInterface
     */
    protected $database;
    /**
     * @var Migrator
     */
    protected $migrator;
    /**
     * @var ResourceBuilder
     */
    protected $resource_builder;
    /**
     * @var string
     */
    protected $data_dir;

    protected $from = 'fsv1';
    protected $to = 'fsv2';

    public function getLabel() : string
    {
        return 'ilStorageHandlerV1Migration';
    }

    public function getDefaultAmountOfStepsPerRun() : int
    {
        return 10000;
    }

    public function getPreconditions(Environment $environment) : array
    {
        return [
            new \ilIniFilesLoadedObjective(),
            new \ilDatabaseUpdatedObjective(),
            new ilStorageContainersExistingObjective()
        ];
    }

    public function prepare(Environment $environment) : void
    {
        $ilias_ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        $client_id = $environment->getResource(Environment::RESOURCE_CLIENT_ID);

        $data_dir = $ilias_ini->readVariable('clients', 'datadir');
        $this->data_dir = $data_dir . "/" . $client_id;

        $configuration = new LocalConfig("{$data_dir}/{$client_id}");
        $f = new FlySystemFilesystemFactory();
        $filesystem = $f->getLocal($configuration);

        $this->database = $environment->getResource(Environment::RESOURCE_DATABASE);

        // ATTENTION: This is a total abomination. It only exists to allow the db-
        // update to run. This is a memento to the fact, that dependency injection
        // is something we want. Currently, every component could just service
        // locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"] ?? [];
        $GLOBALS["DIC"] = new Container();
        $GLOBALS["DIC"]["ilDB"] = $this->database;
        $GLOBALS["ilDB"] = $this->database;

        $storage_handler_factory = new StorageHandlerFactory([
            new MaxNestingFileSystemStorageHandler($filesystem, Location::STORAGE),
            new FileSystemStorageHandler($filesystem, Location::STORAGE)
        ]);

        $this->resource_builder = new ResourceBuilder(
            $storage_handler_factory,
            new RevisionARRepository(),
            new ResourceARRepository(),
            new InformationARRepository(),
            new StakeholderARRepository(),
            new LockHandlerilDB($this->database),
            new FileNamePolicyStack()
        );

        $this->migrator = new Migrator(
            $storage_handler_factory,
            $this->resource_builder,
            $this->database,
            $this->data_dir
        );

    }

    public function step(Environment $environment) : void
    {
        /** @var $io IOWrapper */
        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);

        $r = $this->database->queryF(
            "SELECT identification FROM il_resource WHERE storage_id = %s LIMIT 1",
            ['text'],
            [$this->from]
        );
        $d = $this->database->fetchObject($r);
        if ($d->identification) {
            $resource = $this->resource_builder->get(new ResourceIdentification($d->identification));
            if (!$this->migrator->migrate($resource, $this->to)) {
                $io->text('Resource ' . $resource->getIdentification()->serialize() . ' not migrated, file not found');
            }
        }
    }

    public function getRemainingAmountOfSteps() : int
    {
        $r = $this->database->queryF(
            "SELECT COUNT(identification) as old_storage FROM il_resource WHERE storage_id != %s",
            ['text'],
            [$this->to]
        );
        $d = $this->database->fetchObject($r);

        return (int) ($d->old_storage ?? 0);
    }

}
