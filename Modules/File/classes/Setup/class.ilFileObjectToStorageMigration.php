<?php

use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FlySystem\FlySystemFilesystemFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\Information\Repository\InformationARRepository;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Resource\Repository\ResourceARRepository;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Revision\Repository\RevisionARRepository;
use ILIAS\ResourceStorage\StorageHandler\FileSystemStorageHandler;
use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\ResourceStorage\Lock\LockHandlerilDB;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderARRepository;
use ILIAS\ResourceStorage\Consumer\ConsumerFactory;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Resource\StorableResource;

class ilFileObjectToStorageMigration implements Setup\Migration
{
    private const FILE_PATH_REGEX = '/.*\/file_([\d]*)$/';
    public const MIGRATION_LOG_CSV = "migration_log.csv";

    /**
     * @var ConsumerFactory
     */
    protected $consumer_factory;
    /**
     * @var ilFileObjectToStorageMigrationHelper
     */
    protected $helper;
    /**
     * @var Manager
     */
    protected $storage_manager;
    /**
     * @var ResourceBuilder
     */
    protected $resource_builder;
    /**
     * @var bool
     */
    protected $keep_originals = false;
    /**
     * @var false|resource
     */
    protected $migration_log_handle;

    /**
     * @inheritDoc
     */
    public function getLabel() : string
    {
        return "Migration of File-Objects to Storage service";
    }

    /**
     * @inheritDoc
     */
    public function getDefaultAmountOfStepsPerRun() : int
    {
        return 10000000;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function prepare(Environment $environment) : void
    {
        /**
         * @var $db         ilDBInterface
         * @var $ilias_ini  ilIniFile
         * @var $client_ini ilIniFile
         * @var $client_id  string
         */
        $ilias_ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);

        $client_id = $client_ini->readVariable('client', 'name');
        $data_dir = $ilias_ini->readVariable('clients', 'datadir');

        global $DIC;
        $DIC['ilDB'] = $db;
        $DIC['ilBench'] = null;

        if (!$this->helper instanceof ilFileObjectToStorageMigrationHelper) {

            $legacy_files_dir = "{$data_dir}/{$client_id}/ilFile";
            if (!defined("CLIENT_DATA_DIR")) {
                define('CLIENT_DATA_DIR', "{$data_dir}/{$client_id}");
            }
            if (!defined("CLIENT_ID")) {
                define('CLIENT_ID', $client_id);
            }
            if (!defined("ILIAS_ABSOLUTE_PATH")) {
                define("ILIAS_ABSOLUTE_PATH", dirname(__FILE__, 5));
            }

            define('ILIAS_WEB_DIR', dirname(__DIR__, 4) . "/data/");
            define("CLIENT_WEB_DIR", dirname(__DIR__, 4) . "/data/" . $client_id);

            if (!is_readable($legacy_files_dir)) {
                throw new Exception("{$legacy_files_dir} is not readable, abort...");
            }

            $this->migration_log_handle = fopen($legacy_files_dir . "/" . self::MIGRATION_LOG_CSV, "a");

            $this->helper = new ilFileObjectToStorageMigrationHelper($legacy_files_dir, self::FILE_PATH_REGEX);
        }
        $this->helper->rewind();
        if (!$this->storage_manager instanceof Manager) {
            $storageConfiguration = new LocalConfig("{$data_dir}/{$client_id}");

            $f = new FlySystemFilesystemFactory();
            $storage_handler = new FileSystemStorageHandler($f->getLocal($storageConfiguration), Location::STORAGE);
            $builder = new ResourceBuilder(
                $storage_handler,
                new RevisionARRepository(),
                new ResourceARRepository(),
                new InformationARRepository(),
                new StakeholderARRepository(),
                new LockHandlerilDB($db)
            );
            $this->resource_builder = $builder;
            $this->storage_manager = new Manager($builder);
            $this->consumer_factory = new ConsumerFactory(new StorageHandlerFactory([$storage_handler]));
        }
    }

    /**
     * @inheritDoc
     */
    public function step(Environment $environment) : void
    {
        /**
         * @var $db ilDBInterface
         */
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $item = $this->helper->getNext();
        $resource = $this->getResource($db, $item);

        foreach ($item->getVersions() as $version) {
            try {
                $status = 'success';
                $aditional_info = '';
                $this->resource_builder->appendFromStream($resource,
                    Streams::ofResource(fopen($version->getPath(), 'rb')),
                    $this->keep_originals,
                    $version->getVersion()
                );
            } catch (Throwable $t) {
                $status = 'failed';
                $aditional_info = $t->getMessage();
            }

            $this->logMigratedFile(
                $item->getObjectId(),
                $resource->getIdentification()->serialize(),
                $version->getVersion(),
                $version->getPath(),
                $status,
                $aditional_info
            );

        }

        $this->resource_builder->store($resource);
        $db->manipulateF(
            'UPDATE file_data SET rid = %s WHERE file_id = %s',
            ['text', 'integer'],
            [$resource->getIdentification()->serialize(), $item->getObjectId()]
        );

        $item->tearDown();

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
     * @inheritDoc
     */
    public function getRemainingAmountOfSteps() : int
    {
        return $this->helper->getAmountOfItems();
    }

    /**
     * @param ilDBInterface $db
     * @param ilFileObjectToStorageDirectory $item
     * @return StorableResource
     */
    private function getResource(
        ilDBInterface $db,
        ilFileObjectToStorageDirectory $item
    ) : StorableResource {
        $r = $db->queryF("SELECT rid FROM file_data WHERE file_id = %s", ['integer'], [$item->getObjectId()]);
        $d = $db->fetchObject($r);

        if (isset($d->rid) && $d->rid !== '' && ($resource_identification = $this->storage_manager->find($d->rid)) && $resource_identification !== null) {
            $resource = $this->resource_builder->get($resource_identification);
        } else {
            $resource = $this->resource_builder->newBlank();
        }
        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function getKey() : string
    {
        return 'fileobject_to_storage';
    }

}
