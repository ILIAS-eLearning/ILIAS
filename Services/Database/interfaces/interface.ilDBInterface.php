<?php declare(strict_types=1);

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

    /**
     * @param ilIniFile|null $tmpClientIniFile
     */
    public function initFromIniFile(ilIniFile $ini = null) : void;

    public function connect(bool $return_false_on_error = false) : ?bool;

    /**
     * @param $table_name string
     */
    public function nextId(string $table_name) : int;

    public function createTable(
        string $table_name,
        array $fields,
        bool $drop_table = false,
        bool $ignore_erros = false
    ) : bool;

    /**
     * @param $table_name   string
     * @param $primary_keys array
     */
    public function addPrimaryKey(string $table_name, array $primary_keys) : bool;

    public function createSequence(string $table_name, int $start = 1) : bool;

    public function getSequenceName(string $table_name) : string;

    /**
     * @param $table_name string
     */
    public function tableExists(string $table_name) : bool;

    /**
     * @param $table_name  string
     * @param $column_name string
     */
    public function tableColumnExists(string $table_name, string $column_name) : bool;

    /**
     * @param $table_name  string
     * @param $column_name string
     * @param $attributes  array
     */
    public function addTableColumn(string $table_name, string $column_name, array $attributes) : bool;

    public function dropTable(string $table_name, bool $error_if_not_existing = true) : bool;

    public function renameTable(string $old_name, string $new_name) : bool;

    /**
     * Run a (read-only) Query on the database
     * @param $query string
     */
    public function query(string $query) : ilDBStatement;

    /**
     * @param ilDBStatement $query_result
     * @return mixed[]
     */
    public function fetchAll(ilDBStatement $statement, int $fetch_mode = ilDBConstants::FETCHMODE_ASSOC) : array;

    /**
     * @param $table_name string
     */
    public function dropSequence(string $table_name) : bool;

    /**
     * @param $table_name  string
     * @param $column_name string
     */
    public function dropTableColumn(string $table_name, string $column_name) : bool;

    /**
     * @param $table_name      string
     * @param $column_old_name string
     * @param $column_new_name string
     */
    public function renameTableColumn(string $table_name, string $column_old_name, string $column_new_name) : bool;

    /**
     * @param       $table_name string
     */
    public function insert(string $table_name, array $values) : void;

    /**
     * @param $query_result ilDBStatement
     */
    public function fetchObject(ilDBStatement $query_result) : ?stdClass;

    /**
     * @param $table_name string
     * @param $values     array
     * @param $where      array
     */
    public function update(string $table_name, array $values, array $where) : void;

    /**
     * Run a (write) Query on the database
     * @param $query string
     */
    public function manipulate(string $query) : bool;

    /**
     * @param $query_result ilDBStatement
     */
    public function fetchAssoc(ilDBStatement $statement) : ?array;

    /**
     * @param $query_result ilDBStatement
     */
    public function numRows(ilDBStatement $statement) : int;

    /**
     * @param mixed  $value
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

    /**
     * @param $field  string
     * @param $values array
     */
    public function in(string $field, array $values, bool $negate = false, string $type = "") : string;

    /**
     * @param $query  string
     * @param $types  string[]
     * @param $values mixed[]
     */
    public function queryF(string $query, array $types, array $values) : ilDBStatement;

    /**
     * @param $query  string
     * @param $types  string[]
     * @param $values mixed[]
     */
    public function manipulateF(string $query, array $types, array $values) : bool;

    /**
     * @deprecated
     */
    public function useSlave(bool $bool) : bool;

    public function setLimit(int $limit, int $offset) : void;

    /**
     * Generate a like subquery.
     * @param mixed $value
     */
    public function like(string $column, string $type, $value = "?", bool $case_insensitive = true) : string;

    /**
     * @return string the now statement
     */
    public function now() : string;

    /**
     * Replace into method.
     * @param string        table name
     * @param array         primary key values: array("field1" => array("text", $name), "field2" => ...)
     * @param array         other values: array("field1" => array("text", $name), "field2" => ...)
     */
    public function replace(string $table, array $primary_keys, array $other_columns) : void;

    /**
     * @param $columns
     * @param $value
     * @param $type
     * @return string
     */
    public function equals($columns, $value, $type, bool $emptyOrNull = false);

    public function setDBUser(string $user) : void;

    public function setDBPort(int $port) : void;

    public function setDBPassword(string $password) : void;

    public function setDBHost(string $host) : void;

    /**
     * @param string $a_exp
     */
    public function upper(string $expression) : string;

    /**
     * @param string $a_exp
     */
    public function lower(string $expression) : string;

    /**
     * @param string $a_exp
     */
    public function substr(string $expression) : string;

    /**
     * Prepare a query (SELECT) statement to be used with execute.
     */
    public function prepare(string $a_query, array $a_types = null, array $a_result_types = null) : ilDBStatement;

    /**
     * @param array|null $a_types
     */
    public function prepareManip(string $a_query, array $a_types = null) : ilDBStatement;

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

    public function locate(string $a_needle, string $a_string, int $a_start_pos = 1) : string;

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

    /**
     * @return \ilAtomQuery
     */
    public function buildAtomQuery();

    public function groupConcat(string $a_field_name, string $a_seperator = ",", string $a_order = null) : string;

    /**
     * @return string;
     */
    public function cast(string $a_field_name, string $a_dest_type) : string;
}
