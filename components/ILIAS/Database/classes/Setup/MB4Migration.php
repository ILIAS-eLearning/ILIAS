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

declare(strict_types=1);

namespace ILIAS\Database\Setup;

use ilDBInterface;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ilIniFilesLoadedObjective;
use ilDatabaseInitializedObjective;
use ilDatabaseUpdatedObjective;
use ilDBPdoInterface;
use ilDBConstants;
use ILIAS\Database\PDO\Internal;

class MB4Migration implements Migration
{
    private const CHARSET = 'utf8mb4';

    private Internal $db;
    private string $dbName;
    private string $collation;

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->dbName = $this->db->getDbName();
        $this->collation = $this->selectCollation();
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective()
        ];
    }

    public function step(Environment $environment): void
    {
        $this->convertCharsetDatabase();
        $this->convertCharsetTables();
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return Migration::INFINITE;
    }

    public function getRemainingAmountOfSteps(): int
    {
        return 1;
    }

    public function getLabel(): string
    {
        return 'Migrate all tables from utf8mb3 to utf8mb4';
    }

    /**
     * Returns utf8mb4_unicode_520_ci if the database supports it and falls back to utf8mb4_unicode_ci.
     *
     * @return string
     */
    private function selectCollation(): string
    {
        $result = $this->db->queryF(
            'SHOW COLLATION WHERE COLLATION = %s',
            [ilDBConstants::T_TEXT],
            [ilDBConstants::MYSQL_COLLATION_UTF8MB4_520]
        );
        return $this->db->fetchAssoc($result) !== null ?
            ilDBConstants::MYSQL_COLLATION_UTF8MB4_520 :
            ilDBConstants::MYSQL_COLLATION_UTF8MB4_UNICODE;
    }

    /**
     * ilDBInterface provides the method listTables, but it ignores sequence tables.
     *
     * @return string[]
     */
    private function findTables(): array
    {
        $s = $this->db->query('SHOW TABLES FROM ' . $this->db->quoteIdentifier($this->dbName));
        return array_map('current', $this->db->fetchAll($s));
    }

    private function findDBCharset(): ?string
    {
        $q = 'SELECT DEFAULT_CHARACTER_SET_NAME as charset ' .
             'FROM information_schema.SCHEMATA ' .
             'WHERE SCHEMA_NAME = %s';
        return $this->db->fetchAssoc($this->db->queryF($q, [ilDBConstants::T_TEXT], [$this->dbName]))['charset'] ?? null;
    }

    private function findTableCharset(string $table): ?string
    {
        $q = 'SELECT CCSA.CHARACTER_SET_NAME AS charset ' .
             'FROM information_schema.TABLES AS T ' .
             'JOIN information_schema.COLLATION_CHARACTER_SET_APPLICABILITY AS CCSA ' .
             'WHERE T.TABLE_COLLATION = CCSA.COLLATION_NAME ' .
             'AND TABLE_SCHEMA=%s AND TABLE_NAME=%s';
        return $this->db->fetchAssoc($this->db->queryF(
            $q,
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$this->dbName, $table]
        ))['charset'] ?? null;
    }

    private function convertCharsetDatabase(): void
    {
        $charset = $this->findDBCharset();
        if ($charset === null || $charset === self::CHARSET) {
            return;
        }
        try {
            $this->db->manipulate(sprintf(
                'ALTER DATABASE %s CHARACTER SET = %s COLLATE = %s',
                $this->db->quoteIdentifier($this->dbName),
                $this->db->quoteIdentifier(self::CHARSET),
                $this->db->quoteIdentifier($this->collation),
            ));
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    private function convertCharsetTables(): void
    {
        foreach ($this->findTables() as $table) {
            $table_charset = $this->findTableCharset($table);
            if ($table_charset === null || $table_charset === self::CHARSET) {
                continue;
            }
            $this->db->manipulate(sprintf(
                'ALTER TABLE %s CONVERT TO CHARACTER SET %s COLLATE %s',
                $this->db->quoteIdentifier($table),
                $this->db->quoteIdentifier(self::CHARSET),
                $this->db->quoteIdentifier($this->collation),
            ));
        }
    }
}
