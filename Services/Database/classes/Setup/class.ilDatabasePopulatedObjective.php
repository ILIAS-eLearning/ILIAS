<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilDatabasePopulatedObjective extends \ilDatabaseObjective
{
    const MIN_NUMBER_OF_ILIAS_TABLES = 200; // educated guess

    public function getHash() : string
    {
        return hash("sha256", implode("-", [
            self::class,
            $this->config->getHost(),
            $this->config->getPort(),
            $this->config->getDatabase()
        ]));
    }

    public function getLabel() : string
    {
        return "The database is populated with ILIAS-tables.";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        if ($environment->getResource(Setup\Environment::RESOURCE_DATABASE)) {
            return [];
        }
        return [
            new \ilDatabaseExistsObjective($this->config)
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        /**
         * @var $db ilDBInterface
         * @var $io Setup\CLI\IOWrapper
         */
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $io = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);

        // $this->setDefaultEngine($db); // maybe we could set the default?
        $default = $this->getDefaultEngine($db);

        $io->text("Default DB engine is {$default}");

        switch ($default) {
            case 'innodb':
            case 'myisam':
                $io->text("reading dump file, this may take a while...");
                $this->readDumpFile($db);
                break;

            default:
                throw new Setup\UnachievableException(
                    "Cannot determine database default engine, must be InnoDB or MyISAM"
                );
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);

        return !$this->isDatabasePopulated($db);
    }

    protected function isDatabasePopulated(ilDBInterface $db) : bool
    {
        $probe_tables = ['usr_data', 'object_data', 'object_reference'];
        $number_of_probe_tables = count($probe_tables);
        $tables = $db->listTables();
        $number_of_tables = count($tables);

        return
            $number_of_tables > self::MIN_NUMBER_OF_ILIAS_TABLES
            && count(array_intersect($tables, $probe_tables)) == $number_of_probe_tables;
    }

    /**
     * @param ilDBInterface $db
     * @throws ilDatabaseException
     */
    private function readDumpFile(ilDBInterface $db) : void
    {
        $path_to_db_dump = $this->config->getPathToDBDump();
        if (!is_file(realpath($path_to_db_dump)) ||
            !is_readable(realpath($path_to_db_dump))) {
            throw new Setup\UnachievableException(
                "Cannot read database dump file: $path_to_db_dump"
            );
        }
        foreach ($this->queryReader(realpath($path_to_db_dump)) as $query) {
            try {
                $statement = $db->prepareManip($query);
                $db->execute($statement);
            } catch (Throwable $e) {
                throw new Setup\UnachievableException(
                    "Cannot populate database with dump file: $path_to_db_dump. Query failed: $query wih message " . $e->getMessage(
                    )
                );
            }
        }
    }

    private function queryReader(string $path_to_db_dump): Generator
    {
        $stack = '';
        $handle = fopen($path_to_db_dump, "r");
        while (($line = fgets($handle)) !== false) {
            if (preg_match('/^--/', $line)) { // Skip comments
                continue;
            }
            if (preg_match('/^\/\*/', $line)) { // Run Variables Assignments as single query
                yield $line;
                $stack = '';
                continue;
            }
            if (!preg_match('/;$/', trim($line))) { // Break after ; character which indicates end of query
                $stack .= $line;
            } else {
                $stack .= $line;
                yield $stack;
                $stack = '';
            }
        }

        fclose($handle);
    }

    /**
     * @param ilDBInterface|null $db
     */
    private function setDefaultEngine(ilDBInterface $db) : void
    {
        switch ($db->getDBType()) {
            case ilDBConstants::TYPE_PDO_MYSQL_INNODB:
            case ilDBConstants::TYPE_INNODB:
            case ilDBConstants::TYPE_GALERA:
                $db->manipulate('SET default_storage_engine=InnoDB;');
                break;
            case ilDBConstants::TYPE_PDO_MYSQL_MYISAM:
            case ilDBConstants::TYPE_MYSQL:
                $db->manipulate('SET default_storage_engine=MyISAM;');
                break;

        }
    }

    /**
     * @param ilDBInterface $db
     * @return string
     */
    private function getDefaultEngine(ilDBInterface $db) : string
    {
        try {
            $r = $db->query('SHOW ENGINES ');

            $default = '';
            while ($d = $db->fetchObject($r)) {
                if ($d->Support === 'DEFAULT') {
                    $default = $d->Engine;
                }
            }
            return strtolower($default);
        } catch (Throwable $e) {
            return 'unknown';
        }
    }
}
