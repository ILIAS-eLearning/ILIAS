<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDBPdoMySQL
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilDBPdoMySQL extends ilDBPdo implements ilDBInterface
{

    /**
     * @return bool
     */
    public function supportsTransactions()
    {
        return false;
    }


    public function initHelpers()
    {
        $this->manager = new ilDBPdoManager($this->pdo, $this);
        $this->reverse = new ilDBPdoReverse($this->pdo, $this);
        $this->field_definition = new ilDBPdoMySQLFieldDefinition($this);
    }


    protected function initSQLMode()
    {
        $this->pdo->query("SET SESSION sql_mode = 'IGNORE_SPACE,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';");
    }


    /**
     * @return bool
     */
    public function supportsEngineMigration()
    {
        return true;
    }


    /**
     * @return array
     */
    protected function getAdditionalAttributes()
    {
        return array(
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_TIMEOUT => 300 * 60,
        );
    }


    /**
     * @param string $engine
     * @return array
     */
    public function migrateAllTablesToEngine($engine = ilDBConstants::MYSQL_ENGINE_INNODB)
    {
        $engines = $this->queryCol('SHOW ENGINES');
        if (!in_array($engine, $engines)) {
            return array();
        }

        $errors = array();
        foreach ($this->listTables() as $table) {
            try {
                $this->pdo->exec("ALTER TABLE {$table} ENGINE={$engine}");
            } catch (Exception $e) {
                $errors[$table] = $e->getMessage();
            }
        }

        return $errors;
    }


    /**
     * @inheritDoc
     */
    public function migrateAllTablesToCollation($collation = ilDBConstants::MYSQL_COLLATION_UTF8MB4)
    {
        $ilDBPdoManager = $this->loadModule(ilDBConstants::MODULE_MANAGER);
        $errors = array();
        foreach ($ilDBPdoManager->listTables() as $table_name) {
            $q = "ALTER TABLE {$this->quoteIdentifier($table_name)} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
            try {
                $this->pdo->exec($q);
            } catch (PDOException $e) {
                $errors[] = $table_name;
            }
        }

        return $errors;
    }


    /**
     * @inheritDoc
     */
    public function supportsCollationMigration()
    {
        return true;
    }


    /**
     * @param string $table_name
     * @return int
     */
    public function nextId($table_name)
    {
        $sequence_name = $this->quoteIdentifier($this->getSequenceName($table_name), true);
        $seqcol_name = $this->quoteIdentifier('sequence');
        $query = "INSERT INTO $sequence_name ($seqcol_name) VALUES (NULL)";
        try {
            $this->pdo->exec($query);
        } catch (PDOException $e) {
            // no such table check
        }

        $result = $this->query('SELECT LAST_INSERT_ID() AS next');
        $value = $result->fetchObject()->next;

        if (is_numeric($value)) {
            $query = "DELETE FROM $sequence_name WHERE $seqcol_name < $value";
            $this->pdo->exec($query);
        }

        return $value;
    }


    /**
     * @inheritDoc
     */
    public function doesCollationSupportMB4Strings()
    {
        // Currently ILIAS does not support utf8mb4, after that ilDB could check like this:
        //		static $supported;
        //		if (!isset($supported)) {
        //			$q = "SELECT default_character_set_name FROM information_schema.SCHEMATA WHERE schema_name = %s;";
        //			$res = $this->queryF($q, ['text'], [$this->getDbname()]);
        //			$data = $this->fetchObject($res);
        //			$supported = ($data->default_character_set_name === 'utf8mb4');
        //		}

        return false;
    }
}
