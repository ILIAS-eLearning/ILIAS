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
 
namespace ILIAS\Setup;

use ilDatabaseInitializedObjective;
use ilDatabaseUpdatedObjective;
use ilDBConstants;
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
    public function getLabel() : string
    {
        return "Migration to convert tables from MyISAM to Innodb service";
    }

    /**
     * @inheritDoc
     */
    public function getDefaultAmountOfStepsPerRun() : int
    {
        return 20;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Environment $environment) : array
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
    public function prepare(Environment $environment) : void
    {
        /**
         * @var $client_id  string
         */
        $this->database = $environment->getResource(Environment::RESOURCE_DATABASE);
        $client_ini = $environment->getResource(Environment::RESOURCE_CLIENT_INI);
        $this->db_name = $client_ini->readVariable('db', 'name');
    }

    /**
     * @inheritDoc
     * @throws ilException
     */
    public function step(Environment $environment) : void
    {
        $rows = $this->getNonInnoDBTables();
        $table_name = array_pop($rows);
        if (is_string($table_name) && strlen($table_name) > 0) {
            $migration = $this->database->migrateTableToEngine($table_name);
        }
        if (isset($migration) && $migration === false) {
            throw new ilException("The migration of the following tables did throw errors, please resolve the problem before you continue: \n" . $table_name);
        }
    }

    /**
     * @inheritDoc
     */
    public function getRemainingAmountOfSteps() : int
    {
        if ($this->db_name !== null) {
            $rows = $this->getNonInnoDBTables();
            return count($rows);
        }
        return 0;
    }

    protected function getNonInnoDBTables() : array
    {
        $tables = [];
        $set = $this->database->queryF("SELECT table_name
                FROM INFORMATION_SCHEMA.TABLES
                WHERE ENGINE != %s AND table_schema = %s;", ['text', 'text'], [
            ilDBConstants::MYSQL_ENGINE_INNODB,
            $this->db_name
        ]);
        while ($row = $this->database->fetchAssoc($set)) {
            $tables[] = $row['table_name'];
        }
        return $tables;
    }
}
