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

use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FlySystem\FlySystemFilesystemFactory;
use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilFileObjectToStorageMigration implements Setup\Migration
{
    private const FILE_PATH_REGEX = '/.*\/file_([\d]*)$/';
    public const MIGRATION_LOG_CSV = "migration_log.csv";
    private ilFileObjectToStorageMigrationHelper $helper;

    protected bool $prepared = false;
    private bool $confirmed = false;
    protected ilFileObjectToStorageMigrationRunner $runner;
    protected ilDBInterface $database;

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
        return 10;
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
            new ilStorageContainersExistingObjective()
        ];
    }

    /**
     * @inheritDoc
     */
    public function prepare(Environment $environment) : void
    {
        /**
         * @var $ilias_ini  ilIniFile
         * @var $client_ini ilIniFile
         * @var $client_id  string
         * @var $io  Setup\CLI\IOWrapper
         */
        $ilias_ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $this->database = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);

        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);
        $data_dir = $ilias_ini->readVariable('clients', 'datadir');

        if (!$this->prepared) {
            global $DIC;
            $DIC['ilDB'] = $this->database;
            $DIC['ilBench'] = null;

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
            if (!defined("ILIAS_WEB_DIR")) {
                define('ILIAS_WEB_DIR', dirname(__DIR__, 4) . "/data/");
            }
            if (!defined("CLIENT_WEB_DIR")) {
                define("CLIENT_WEB_DIR", dirname(__DIR__, 4) . "/data/" . $client_id);
            }

            // if dir doesn't exists there are no steps to do,
            // so don't initialize ilFileObjectToStorageMigrationHelper
            if (!is_dir($legacy_files_dir)) {
                return;
            }

            if (!is_readable($legacy_files_dir)) {
                throw new Exception("{$legacy_files_dir} is not readable, abort...");
            }

            if (!is_writable("{$data_dir}/{$client_id}/storage")) {
                throw new Exception("storage directory is not writable, abort...");
            }

            $this->helper = new ilFileObjectToStorageMigrationHelper($legacy_files_dir, $this->database);

            $storageConfiguration = new LocalConfig("{$data_dir}/{$client_id}");
            $f = new FlySystemFilesystemFactory();

            $this->runner = new ilFileObjectToStorageMigrationRunner(
                $f->getLocal($storageConfiguration),
                $this->database,
                $legacy_files_dir . "/" . self::MIGRATION_LOG_CSV
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function step(Environment $environment) : void
    {
        $this->showConfirmation($environment);
        $item = $this->helper->getNext();
        $this->runner->migrate($item);
    }

    /**
     * @inheritDoc
     */
    public function getRemainingAmountOfSteps() : int
    {
        $r = $this->database->query("SELECT COUNT(file_id) AS amount FROM file_data WHERE rid IS NULL OR rid = '';");
        $d = $this->database->fetchObject($r);

        return (int) $d->amount;
    }

    /**
     * @param Environment $environment
     * @return void
     */
    protected function showConfirmation(Environment $environment) : void
    {
        if (!$this->confirmed) {
            $io = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);
            $this->confirmed = $io->confirmExplicit(
                'The migration of File-Objects should be done in ILIAS 7 not 8, see Modules/File/classes/Setup/MISSING_MIGRATION.md, type "Understood" to proceed',
                'Understood'
            );
        }
    }
}
