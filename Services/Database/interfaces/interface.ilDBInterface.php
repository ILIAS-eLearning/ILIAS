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
 * Interface ilDBInterface
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBInterface
{
    public function doesCollationSupportMB4Strings() : bool;

    /**
     * @param $query string to sanitize, all MB4-Characters like emojis will re replaced with ???
     * @return string sanitized query
     */
    public function sanitizeMB4StringIfNotSupported(string $query) : string;

    /**
     * Get reserved words. This must be overwritten in DBMS specific class.
     * This is mainly used to check whether a new identifier can be problematic
     * because it is a reserved word. So createTable / alterTable usually check
     * these.
     * @return string[]
     */
    public static function getReservedWords() : array;

    public function initFromIniFile(?ilIniFile $ini = null) : void;

    public function connect(bool $return_false_on_error = false) : ?bool;

    public function nextId(string $table_name) : int;

    public function createTable(
        string $table_name,
        array $fields,
        bool $drop_table = false,
        bool $ignore_erros = false
    ) : bool;

    public function addPrimaryKey(string $table_name, array $primary_keys) : bool;

    /**
     * @param $table_name
     * @param int $start
     * @deprecated use 'autoincrement' in $fields instead
     */
    public function createSequence(string $table_name, int $start = 1) : bool;

    public function getSequenceName(string $table_name) : string;

    public function tableExists(string $table_name) : bool;

    public function tableColumnExists(string $table_name, string $column_name) : bool;

    public function addTableColumn(string $table_name, string $column_name, array $attributes) : bool;

    public function dropTable(string $table_name, bool $error_if_not_existing = true) : bool;

    public function renameTable(string $old_name, string $new_name) : bool;

    /**
     * Run a (read-only) Query on the database
     */
    public function query(string $query) : ilDBStatement;

    public function fetchAll(ilDBStatement $statement, int $fetch_mode = ilDBConstants::FETCHMODE_ASSOC) : array;

    public function dropSequence(string $table_name) : bool;

    public function dropTableColumn(string $table_name, string $column_name) : bool;

    public function renameTableColumn(string $table_name, string $column_old_name, string $column_new_name) : bool;

    /**
     * @return int The number of rows affected by the manipulation
     */
    public function insert(string $table_name, array $values) : int;

    public function fetchObject(ilDBStatement $query_result) : ?stdClass;

    /**
     * @return int The number of rows affected by the manipulation
     */
    public function update(string $table_name, array $values, array $where) : int;

    /**
     * Run a (write) Query on the database
     * @return int The number of rows affected by the manipulation
     */
    public function manipulate(string $query) : int;

    public function fetchAssoc(ilDBStatement $statement) : ?array;

    public function numRows(ilDBStatement $statement) : int;

    /**
     * @param mixed $value
     */
    public function quote($value, string $type) : string;

    public function addIndex(string $table_name, array $fields, string $index_name = '', bool $fulltext = false) : bool;

    public function indexExistsByFields(string $table_name, array $fields) : bool;

    /**
     * Get DSN. This must be overwritten in DBMS specific class.
     */
    public function getDSN() : string;

    /**
     * Get DSN. This must be overwritten in DBMS specific class.
     */
    public function getDBType() : string;

    /**
     * Abstraction of lock table
     * @param array table definitions
     * @deprecated Use ilAtomQuery instead
     */
    public function lockTables(array $tables) : void;

    /**
     * Unlock tables locked by previous lock table calls
     * @deprecated Use ilAtomQuery instead
     */
    public function unlockTables() : void;

    public function in(string $field, array $values, bool $negate = false, string $type = "") : string;

    /**
     * @param $types  string[]
     */
    public function queryF(string $query, array $types, array $values) : ilDBStatement;

    /**
     * @param $types  string[]
     * @return int The number of rows affected by the manipulation
     */
    public function manipulateF(string $query, array $types, array $values) : int;

    /**
     * @deprecated
     */
    public function useSlave(bool $bool) : bool;

    public function setLimit(int $limit, int $offset = 0) : void;

    /**
     * Generate a like subquery.
     */
    public function like(string $column, string $type, string $value = "?", bool $case_insensitive = true) : string;

    /**
     * @return string the now statement
     */
    public function now() : string;

    /**
     * Replace into method.
     * @param string        table name
     * @param array         primary key values: array("field1" => array("text", $name), "field2" => ...)
     * @param array         other values: array("field1" => array("text", $name), "field2" => ...)
     * @return int The number of rows affected by the manipulation
     */
    public function replace(string $table, array $primary_keys, array $other_columns) : int;

    public function equals(string $columns, $value, string $type, bool $emptyOrNull = false) : string;

    public function setDBUser(string $user) : void;

    public function setDBPort(int $port) : void;

    public function setDBPassword(string $password) : void;

    public function setDBHost(string $host) : void;

    public function upper(string $expression) : string;

    public function lower(string $expression) : string;

    public function substr(string $expression) : string;

    /**
     * Prepare a query (SELECT) statement to be used with execute.
     */
    public function prepare(string $a_query, array $a_types = null, array $a_result_types = null) : ilDBStatement;

    public function prepareManip(string $a_query, ?array $a_types = null) : ilDBStatement;

    public function enableResultBuffering(bool $a_status) : void;

    /**
     * @throws ilDatabaseException
     */
    public function execute(ilDBStatement $stmt, array $data = []) : ilDBStatement;

    public function sequenceExists(string $sequence) : bool;

    /**
     * @return string[]
     */
    public function listSequences() : array;

    public function supports(string $feature) : bool;

    public function supportsFulltext() : bool;

    public function supportsSlave() : bool;

    public function supportsTransactions() : bool;

    /**
     * @return string[]
     */
    public function listTables() : array;

    /**
     * @param string $module Manager|Reverse
     * @return ilDBReverse|ilDBManager
     * @internal Please do not use this in consumer code outside the Setup-Process or DB-Update-Steps.
     */
    public function loadModule(string $module);

    /**
     * @return string[]
     */
    public function getAllowedAttributes() : array;

    public function concat(array $values, bool $allow_null = true) : string;

    public function locate(string $needle, string $string, int $start_pos = 1) : string;

    public function quoteIdentifier(string $identifier, bool $check_option = false) : string;

    public function modifyTableColumn(string $table, string $column, array $attributes) : bool;

    public function free(ilDBStatement $a_st) : void;

    public function checkTableName(string $a_name) : bool;

    public static function isReservedWord(string $a_word) : bool;

    /**
     * @throws \ilDatabaseException
     */
    public function beginTransaction() : bool;

    /**
     * @throws \ilDatabaseException
     */
    public function commit() : bool;

    /**
     * @throws \ilDatabaseException
     */
    public function rollback() : bool;

    public function constraintName(string $a_table, string $a_constraint) : string;

    public function dropIndex(string $a_table, string $a_name = "i1") : bool;

    public function createDatabase(string $a_name, string $a_charset = "utf8", string $a_collation = "") : bool;

    public function dropIndexByFields(string $table_name, array $afields) : bool;

    public function getPrimaryKeyIdentifier() : string;

    public function addFulltextIndex(string $table_name, array $afields, string $a_name = 'in') : bool;

    public function dropFulltextIndex(string $a_table, string $a_name) : bool;

    public function isFulltextIndex(string $a_table, string $a_name) : bool;

    public function setStorageEngine(string $storage_engine) : void;

    public function getStorageEngine() : string;

    public function buildAtomQuery() : ilAtomQuery;

    public function groupConcat(string $a_field_name, string $a_seperator = ",", ?string $a_order = null) : string;

    public function cast(string $a_field_name, string $a_dest_type) : string;
}
