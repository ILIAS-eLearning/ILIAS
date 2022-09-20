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
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\MaxNestingFileSystemStorageHandler;
use ILIAS\DI\Container;
use ILIAS\ResourceStorage\Revision\Repository\RevisionDBRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceDBRepository;
use ILIAS\ResourceStorage\Information\Repository\InformationDBRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderDBRepository;
use ILIAS\ResourceStorage\Resource\Repository\CollectionDBRepository;

/**
 * Class ilStorageHandlerV1Migration
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilStorageHandlerV1Migration implements Migration
{
    protected \DirectoryIterator $iterator;
    /**
     * @var \ilDBInterface
     */
    protected $database;
    protected ?\ILIAS\ResourceStorage\StorageHandler\Migrator $migrator = null;
    protected ?\ILIAS\ResourceStorage\Resource\ResourceBuilder $resource_builder = null;
    protected ?string $data_dir = null;

    protected string $from = 'fsv1';
    protected string $to = 'fsv2';

    public function getLabel(): string
    {
        return 'ilStorageHandlerV1Migration';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10000;
    }

    /**
     * @return \ilDatabaseUpdatedObjective[]|\ilIniFilesLoadedObjective[]|\ilStorageContainersExistingObjective[]
     */
    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilIniFilesLoadedObjective(),
            new \ilDatabaseUpdatedObjective(),
            new ilStorageContainersExistingObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $ilias_ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        $client_id = $environment->getResource(Environment::RESOURCE_CLIENT_ID);

        $data_dir = $ilias_ini->readVariable('clients', 'datadir');
        $this->data_dir = $data_dir . "/" . $client_id;

        $configuration = new LocalConfig("{$data_dir}/{$client_id}");
        $f = new FlySystemFilesystemFactory();
        $filesystem = $f->getLocal($configuration);

        $this->database = $environment->getResource(Environment::RESOURCE_DATABASE);
        /** @noRector  $DIC */
        // ATTENTION: This is a total abomination. It only exists to allow the db-
        // update to run. This is a memento to the fact, that dependency injection
        // is something we want. Currently, every component could just service
        // locate the whole world via the global $DIC.
        /** @noRector  $DIC */
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
            new RevisionDBRepository($this->database),
            new ResourceDBRepository($this->database),
            new CollectionDBRepository($this->database),
            new InformationDBRepository($this->database),
            new StakeholderDBRepository($this->database),
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

    public function step(Environment $environment): void
    {
        /** @var $io IOWrapper */
        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);

        $r = $this->database->queryF(
            "SELECT rid FROM il_resource WHERE storage_id = %s LIMIT 1",
            ['text'],
            [$this->from]
        );
        $d = $this->database->fetchObject($r);
        if ($d->rid) {
            $resource = $this->resource_builder->get(new ResourceIdentification($d->identification));
            if (!$this->migrator->migrate($resource, $this->to)) {
                $i = $resource->getIdentification()->serialize();
                $io->text('Resource ' . $i . ' not migrated, file not found. All Stakeholder have been informed about the deletion.');
            }
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->database->queryF(
            "SELECT COUNT(rid) as old_storage FROM il_resource WHERE storage_id != %s",
            ['text'],
            [$this->to]
        );
        $d = $this->database->fetchObject($r);

        return (int) ($d->old_storage ?? 0);
    }
}
