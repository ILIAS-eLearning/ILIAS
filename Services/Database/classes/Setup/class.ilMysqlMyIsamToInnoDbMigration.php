<?php


namespace ILIAS\Setup;


use ilDatabaseInitializedObjective;
use ilDatabaseUpdatedObjective;
use ilException;
use ILIAS\Setup;
use ilIniFilesLoadedObjective;

class ilMysqlMyIsamToInnoDbMigration implements Migration
{

    protected ?string $db_name = null;
    protected ?\ilDBInterface $database = null;

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return "Migration to convert tables from MyISAM to Innodb service";
    }

    /**
     * @inheritDoc
     */
    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 20;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective()
        ];
    }

    /**
     * @inheritDoc
     */
    public function prepare(Environment $environment): void
    {
        /**
         * @var $client_id  string
         */
        $this->database = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $this->db_name = $client_ini->readVariable('db', 'name');

    }

    /**
     * @inheritDoc
     * @throws ilException
     */
    public function step(Environment $environment): void
    {
        $errors = $this->database->migrateAllTablesToEngine();
        if (sizeof($errors) > 0) {
            $error_string = '';
            foreach ($errors as $table_name => $error) {
                $error_string .= sprintf("Table: %s => ErrorMessage: %s\n", $table_name, $error);
            }
            throw new ilException("The migration of the following tables did throw errors, please resolve the problem before you continue: \n" . $error_string);
        }
    }

    /**
     * @inheritDoc
     */
    public function getRemainingAmountOfSteps(): int
    {
        if($this->db_name !== null) {
            $set = $this->database->queryF("SELECT count(*) as tables
                FROM INFORMATION_SCHEMA.TABLES
                WHERE ENGINE='MyISAM' AND table_schema = %s;", ['text'], [
                $this->db_name,
            ]);
            $row = $this->database->fetchAssoc($set);
            return (int) $row['tables'];
        }
        return 0;
    }
}