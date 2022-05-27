<?php declare(strict_types=1);
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
 
/**
 * Class ilDBPdoMySQL
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilDBPdoMySQL extends ilDBPdo
{
    /**
     * @var string[]
     */
    protected array $modes = [
        'STRICT_TRANS_TABLES',
        'STRICT_ALL_TABLES',
        'IGNORE_SPACE',
        'NO_ZERO_IN_DATE',
        'NO_ZERO_DATE',
        'ERROR_FOR_DIVISION_BY_ZERO',
        'NO_ENGINE_SUBSTITUTION',
    ];

    public function supportsTransactions() : bool
    {
        return false;
    }

    public function initHelpers() : void
    {
        $this->manager = new ilDBPdoManager($this->pdo, $this);
        $this->reverse = new ilDBPdoReverse($this->pdo, $this);
        $this->field_definition = new ilDBPdoMySQLFieldDefinition($this);
    }

    protected function initSQLMode() : void
    {
        $this->pdo->exec("SET SESSION sql_mode = '" . implode(",", $this->modes) . "';");
    }

    public function supportsEngineMigration() : bool
    {
        return true;
    }

    /**
     * @return array<int, int|bool>
     */
    protected function getAdditionalAttributes() : array
    {
        return array(
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_TIMEOUT => 300 * 60,
        );
    }
    
    public function migrateTableToEngine(string $table_name, string $engine = ilDBConstants::MYSQL_ENGINE_INNODB) : bool
    {
        try {
            $this->pdo->exec("ALTER TABLE {$table_name} ENGINE={$engine}");
            if ($this->sequenceExists($table_name)) {
                $this->pdo->exec("ALTER TABLE {$table_name}_seq ENGINE={$engine}");
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    
    /**
     * @return array<int|string, string>
     */
    public function migrateAllTablesToEngine(string $engine = ilDBConstants::MYSQL_ENGINE_INNODB) : array
    {
        $engines = $this->queryCol('SHOW ENGINES');
        if (!in_array($engine, $engines, true)) {
            return [];
        }
        $errors = [];
        $tables = $this->listTables();
        array_walk($tables, function (string $table_name) use (&$errors, $engine) : void {
            try {
                $this->pdo->exec("ALTER TABLE $table_name ENGINE=$engine");
                if ($this->sequenceExists($table_name)) {
                    $this->pdo->exec("ALTER TABLE {$table_name}_seq ENGINE=$engine");
                }
            } catch (Exception $e) {
                $errors[$table_name] = $e->getMessage();
            }
        });
    
        return $errors;
    }
    
    public function migrateTableCollation(
        string $table_name,
        string $collation = ilDBConstants::MYSQL_COLLATION_UTF8MB4
    ) : bool {
        $collation_split = explode("_", $collation);
        $character = $collation_split[0] ?? 'utf8mb4';
        $collate = $collation;
        $q = "ALTER TABLE {$this->quoteIdentifier($table_name)} CONVERT TO CHARACTER SET {$character} COLLATE {$collate};";
        try {
            $this->pdo->exec($q);
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }
    
    /**
     * @inheritDoc
     */
    public function migrateAllTablesToCollation(string $collation = ilDBConstants::MYSQL_COLLATION_UTF8MB4) : array
    {
        $manager = $this->loadModule(ilDBConstants::MODULE_MANAGER);
        $errors = [];
        foreach ($manager->listTables() as $table_name) {
            if (!$this->migrateTableCollation($table_name, $collation)) {
                $errors[] = $table_name;
            }
        }

        return $errors;
    }

    /**
     * @inheritDoc
     */
    public function supportsCollationMigration() : bool
    {
        return true;
    }

    public function nextId(string $table_name) : int
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

        return (int) $value;
    }

    /**
     * @inheritDoc
     */
    public function doesCollationSupportMB4Strings() : bool
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
