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
    protected ?ilFileObjectToStorageMigrationRunner $runner;
    protected ?ilDBInterface $database;

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return "Migration of File-Objects to Storage service";
    }

    /**
     * @inheritDoc
     */
    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Environment $environment): array
    {
        return ilResourceStorageMigrationHelper::getPreconditions();
    }

    /**
     * @inheritDoc
     */
    public function prepare(Environment $environment): void
    {
        $irss_helper = new ilResourceStorageMigrationHelper(
            new ilObjFileStakeholder(),
            $environment
        );

        $legacy_files_dir = $irss_helper->getClientDataDir() . "/ilFile";
        $this->helper = new ilFileObjectToStorageMigrationHelper(
            $irss_helper
        );

        $storage_configuration = new LocalConfig($irss_helper->getClientDataDir());
        $f = new FlySystemFilesystemFactory();

        $this->runner = new ilFileObjectToStorageMigrationRunner(
            $f->getLocal($storage_configuration),
            $irss_helper,
            self::MIGRATION_LOG_CSV
        );

        $this->database = $irss_helper->getDatabase();
    }

    /**
     * @inheritDoc
     */
    public function step(Environment $environment): void
    {
        $this->showConfirmation($environment);
        $item = $this->helper->getNext();
        $this->runner->migrate($item);
    }

    /**
     * @inheritDoc
     */
    public function getRemainingAmountOfSteps(): int
    {
        $r = $this->database->query("SELECT COUNT(file_id) AS amount FROM file_data WHERE rid IS NULL OR rid = '';");
        $d = $this->database->fetchObject($r);

        return (int) $d->amount;
    }

    /**
     * @param Environment $environment
     * @return void
     */
    protected function showConfirmation(Environment $environment): void
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
