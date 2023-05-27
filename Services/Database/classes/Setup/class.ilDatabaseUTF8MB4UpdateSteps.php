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

/**
 * Class ilDatabaseUTF8MB4UpdateSteps
 * Contains update steps to convert the database, all tables and columns to UTF8MB4
 *
 * @author Sven Dyhr <sven.dyhr@tik.uni-stuttgart.de>
 */
class ilDatabaseUTF8MB4UpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;
    private string $charset;
    private string $collation;
    private string $dbName;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
        $this->dbName = $this->db->getDbName();
        $this->charset = "utf8mb4";
        $this->collation = $this->selectCollation();
    }

    /**
     * Checks if the database supports utf8mb4_unicode_520_ci and if so returns it.
     * Should be the case for MariaDB 10.x & MySQL 5.7 and 8.0
     *
     * utf8mb4_unicode_ci is returned as fallback
     *
     * @return string   Contains the collation used for conversion.
     */
    private function selectCollation(): string
    {
        $q = "SHOW COLLATION WHERE COLLATION LIKE 'utf8mb4_unicode_520_ci'";
        if ($this->db->query($q)->fetch()) {
            return "utf8mb4_unicode_520_ci";
        }
        return "utf8mb4_unicode_ci";
    }

    /**
     * Fetches all tables of the database.
     *
     * ilDBInterface provides the method "listTables", but it ignores sequence tables.
     * Since all tables should be converted, a separate function is necessary.
     *
     * @return array    An array containing names of all tables of the database.
     */
    private function getTables(): array
    {
        $q = "SHOW TABLES FROM $this->dbName";
        $statement = $this->db->query($q);
        $tables = [];
        while ($data = $statement->fetch()) {
            $tables[] = array_values($data)[0];
        }
        return $tables;
    }

    /**
     * Fetches the character set and collation of the database.
     *
     * @return array|false  False if the query does not return any data,
     *                      an array with keys charset and collation.
     */
    private function getCharsetDB(): array|false
    {
        $q = "SELECT DEFAULT_CHARACTER_SET_NAME as charset, " .
             "DEFAULT_COLLATION_NAME as collation " .
             "FROM information_schema.SCHEMATA " .
             "WHERE SCHEMA_NAME = '$this->dbName'";
        return $this->db->query($q)->fetch();
    }

    /**
     * Fetches the character set and collation of a given table.
     *
     * @param string $table The table whose character set and collation are to be fetched
     * @return array|false  False if the given table does not exist, otherwise an array
     *                      with keys charset and collation
     */
    private function getCharsetTable(string $table): array|false
    {
        if (!$this->db->tableExists($table)) {
            return false;
        }
        $q = "SELECT CCSA.CHARACTER_SET_NAME AS charset, " .
             "TABLE_COLLATION AS collation " .
             "FROM information_schema.TABLES AS T " .
             "JOIN information_schema.COLLATION_CHARACTER_SET_APPLICABILITY AS CCSA " .
             "WHERE T.TABLE_COLLATION = CCSA.COLLATION_NAME " .
             "AND TABLE_SCHEMA='$this->dbName' AND TABLE_NAME='$table'";
        return $this->db->query($q)->fetch();
    }

    /**
     * Fetches all information about a given column of a given table.
     *
     * @param string $table     The table containing the column
     * @param string $column    The column whose information are to be retrieved
     * @return array|false      False if the given table does not exist or does not contain the
     *                          given column, otherwise an array containing the metadata
     */
    private function getColumnMetadata(string $table, string $column): array|false
    {
        if (!$this->db->tableExists($table) || !$this->db->tableColumnExists($table, $column)) {
            return false;
        }
        $q = "SELECT * FROM information_schema.COLUMNS WHERE table_schema = '$this->dbName' " .
             "AND table_name = '$table' AND column_name = '$column'";
        return $this->db->query($q)->fetch();
    }

    /**
     * Converts a given column of a table to a new column type
     *
     * @param string      $table    The table containing the column
     * @param string      $column   The column whose type is to be changed
     * @param string      $type     The new type of the column
     */
    private function convertColumn(string $table, string $column, string $type): void
    {
        //do nothing if table or column does not exist
        if (!$this->db->tableExists($table) || !$this->db->tableColumnExists($table, $column)) {
            return;
        }

        $data = $this->getColumnMetadata($table, $column);
        // do nothing if column doesn't have a collation (non text fields)
        if (is_null($data["CHARACTER_SET_NAME"])) {
            return;
        }
        // do nothing if column already has the new character set AND type
        if ($data["CHARACTER_SET_NAME"] === $this->charset && $data["COLUMN_TYPE"] === $type) {
            return;
        }

        $constraint = ($data["IS_NULLABLE"] === "YES") ? "NULL" : "NOT NULL";
        if (!is_null($data["COLUMN_DEFAULT"])) {
            $constraint .= " DEFAULT {$data['COLUMN_DEFAULT']}";
        }

        $q = "ALTER TABLE $table CHANGE $column $column $type " .
             "CHARACTER SET $this->charset COLLATE $this->collation $constraint";
        $this->db->manipulate($q);
    }

    /**
     * Converts the character set of the database itself
     */
    private function convertCharsetDatabase(): void
    {
        $db_info = $this->getCharsetDB();

        if ($db_info === false || $db_info["charset"] === $this->charset) {
            return;
        }
        $q = "ALTER DATABASE $this->dbName " .
             "CHARACTER SET = $this->charset " .
             "COLLATE = $this->collation";
        $this->db->manipulate($q);
    }

    /**
     * Converts the character set of all tables of the database
     */
    private function convertCharsetTables(): void
    {
        $tables = $this->getTables();
        foreach ($tables as $table) {
            $table_info = $this->getCharsetTable($table);
            if ($table_info === false || $table_info["charset"] === $this->charset) {
                continue;
            }
            $q = "ALTER TABLE $table CONVERT TO " .
                 "CHARACTER SET $this->charset COLLATE $this->collation";
            $this->db->manipulate($q);
        }
    }

    /**
     * Takes care of some special cases that would throw errors due to byte limit
     */
    private function convertCharsetColumnsSpecialCases(): void
    {
        $cases = array(
            "event" => array("description", "location", "tutor_name", "details"),
            "crs_settings" => array("syllabus", "contact_consultation", "important", "target_group")
        );
        foreach ($cases as $table => $columns) {
            foreach ($columns as $column) {
                $this->convertColumn($table, $column, "text");
            }
        }
    }

    public function step_1(): void
    {
        $this->convertCharsetDatabase();
    }

    public function step_2(): void
    {
        $this->convertCharsetColumnsSpecialCases();
    }

    public function step_3(): void
    {
        $this->convertCharsetTables();
    }
}
