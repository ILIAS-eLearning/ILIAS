<?php

/**
 * Interface ilDBInterface
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBInterface
{

    /**
     * @return bool
     */
    public function doesCollationSupportMB4Strings();

    /**
     * @param $query string to sanitize, all MB4-Characters like emojis will re replaced with ???
     *
     * @return string sanitized query
     */
    public function sanitizeMB4StringIfNotSupported($query);

    /**
     * Get reserved words. This must be overwritten in DBMS specific class.
     * This is mainly used to check whether a new identifier can be problematic
     * because it is a reserved word. So createTable / alterTable usually check
     * these.
     */
    public static function getReservedWords();


    /**
     * @param null $tmpClientIniFile
     */
    public function initFromIniFile($tmpClientIniFile = null);


    /**
     * @param bool $return_false_on_error
     * @return mixed
     */
    public function connect($return_false_on_error = false);


    /**
     * @param $table_name string
     *
     * @return int
     */
    public function nextId($table_name);


    /**
     * @param $table_name
     * @param $fields
     * @param bool $drop_table
     * @param bool $ignore_erros
     * @return mixed
     */
    public function createTable($table_name, $fields, $drop_table = false, $ignore_erros = false);


    /**
     * @param $table_name   string
     * @param $primary_keys array
     */
    public function addPrimaryKey($table_name, $primary_keys);


    /**
     * @param $table_name
     * @param int $start
     */
    public function createSequence($table_name, $start = 1);


    /**
     * @param $table_name
     * @return string
     */
    public function getSequenceName($table_name);


    /**
     * @param $table_name string
     *
     * @return bool
     */
    public function tableExists($table_name);


    /**
     * @param $table_name  string
     * @param $column_name string
     *
     * @return bool
     */
    public function tableColumnExists($table_name, $column_name);


    /**
     * @param $table_name  string
     * @param $column_name string
     * @param $attributes  array
     */
    public function addTableColumn($table_name, $column_name, $attributes);


    /**
     * @param $table_name
     * @param bool $error_if_not_existing
     * @return bool
     */
    public function dropTable($table_name, $error_if_not_existing = true);


    /**
     * @param $old_name
     * @param $new_name
     *
     * @return mixed
     */
    public function renameTable($old_name, $new_name);


    /**
     * Run a (read-only) Query on the database
     *
     * The implementation MUST start and stop a $ilBench Database-Benchmark, e.g.:
     *
     * $ilBench->startDbBench($sql);
     * .... [run the query]
     * $ilBench->stopDbBench();
     *
     * @param $query string
     *
     * @return \ilPDOStatement
     */
    public function query($query);


    /**
     * @param $query_result
     * @param int $fetch_mode
     * @return array
     */
    public function fetchAll($query_result, $fetch_mode = ilDBConstants::FETCHMODE_ASSOC);


    /**
     * @param $table_name string
     */
    public function dropSequence($table_name);


    /**
     * @param $table_name  string
     * @param $column_name string
     */
    public function dropTableColumn($table_name, $column_name);


    /**
     * @param $table_name      string
     * @param $column_old_name string
     * @param $column_new_name string
     */
    public function renameTableColumn($table_name, $column_old_name, $column_new_name);


    /**
     * @param $table_name string
     * @param $values
     * @return int|void
     */
    public function insert($table_name, $values);


    /**
     * @param $query_result PDOStatement
     *
     * @return mixed|null
     */
    public function fetchObject($query_result);


    /**
     * @param $table_name string
     * @param $values     array
     * @param $where      array
     * @return int|void
     */
    public function update($table_name, $values, $where);


    /**
     * Run a (write) Query on the database
     *
     * The implementation MUST start and stop a $ilBench Database-Benchmark, e.g.:
     *
     * $ilBench->startDbBench($sql);
     * .... [run the query]
     * $ilBench->stopDbBench();
     *
     * @param $query string
     * @return int|void
     */
    public function manipulate($query);


    /**
     * @param $query_result ilDBStatement
     *
     * @return mixed
     */
    public function fetchAssoc($query_result);


    /**
     * @param $query_result PDOStatement
     *
     * @return int
     */
    public function numRows($query_result);


    /**
     * @param $value
     * @param $type
     *
     * @return string
     */
    public function quote($value, $type);


    /**
     * @param $table_name
     * @param $fields
     * @param string $index_name
     * @param bool $fulltext
     * @return bool
     */
    public function addIndex($table_name, $fields, $index_name = '', $fulltext = false);


    /**
     * @param $table_name
     * @param $fields
     *
     * @return mixed
     */
    public function indexExistsByFields($table_name, $fields);

    /**
     * @param int $fetchMode
     * @return mixed
     */
    //	public function fetchRow($fetchMode = ilDBConstants::FETCHMODE_ASSOC);

    /**
     * Get DSN. This must be overwritten in DBMS specific class.
     */
    public function getDSN();


    /**
     * Get DSN. This must be overwritten in DBMS specific class.
     */
    public function getDBType();


    /**
     * Abstraction of lock table
     *
     * @deprecated Use ilAtomQuery instead
     * @param array table definitions
     * @return
     */
    public function lockTables($tables);


    /**
     * Unlock tables locked by previous lock table calls
     *
     * @deprecated Use ilAtomQuery instead
     * @return
     */
    public function unlockTables();


    /**
     * @param $field  string
     * @param $values array
     * @param bool $negate
     * @param string $type
     * @return string
     */
    public function in($field, $values, $negate = false, $type = "");


    /**
     * @param $query  string
     * @param $types  string[]
     * @param $values mixed[]
     * @return \ilDBStatement
     */
    public function queryF($query, $types, $values);


    /**
     * @param $query  string
     * @param $types  string[]
     * @param $values mixed[]
     * @return string
     */
    public function manipulateF($query, $types, $values);


    /**
     * Return false iff slave is not supported.
     *
     * @param $bool
     * @return bool
     */
    public function useSlave($bool);


    /**
     * @param $limit
     * @param $offset
     */
    public function setLimit($limit, $offset);


    /**
     * Generate a like subquery.
     *
     * @param string $column
     * @param string $type
     * @param mixed $value
     * @param bool $case_insensitive
     * @return string
     */
    public function like($column, $type, $value = "?", $case_insensitive = true);


    /**
     * @return string the now statement
     */
    public function now();


    /**
     * Replace into method.
     *
     * @param    string        table name
     * @param    array         primary key values: array("field1" => array("text", $name), "field2" => ...)
     * @param    array         other values: array("field1" => array("text", $name), "field2" => ...)
     */
    public function replace($table, $primaryKeys, $otherColumns);


    /**
     * @param $columns
     * @param $value
     * @param $type
     * @param bool $emptyOrNull
     * @return string
     */
    public function equals($columns, $value, $type, $emptyOrNull = false);


    /**
     * @param $user
     */
    public function setDBUser($user);


    /**
     * @param $port
     */
    public function setDBPort($port);


    /**
     * @param $password
     */
    public function setDBPassword($password);


    /**
     * @param $host
     */
    public function setDBHost($host);


    /**
     * @param $a_exp
     * @return string
     */
    public function upper($a_exp);


    /**
     * @param $a_exp
     * @return string
     */
    public function lower($a_exp);


    /**
     * @param $a_exp
     * @return string
     */
    public function substr($a_exp);

    /**
     * Prepare a query (SELECT) statement to be used with execute.
     *
     * @param	string $a_query
     *
     * @return	\ilDBStatement
     */
    public function prepare($a_query, $a_types = null, $a_result_types = null);


    /**
     * @param $a_query
     * @param null $a_types
     * @return ilDBStatement
     */
    public function prepareManip($a_query, $a_types = null);


    /**
     * @param $a_status
     */
    public function enableResultBuffering($a_status);


    /**
     * @param $stmt
     * @param array $data
     * @throws ilDatabaseException
     * @return ilDBStatement
     */
    public function execute($stmt, $data = array());

    /**
     * @param $sequence
     * @return mixed
     */
    public function sequenceExists($sequence);


    /**
     * @return array
     */
    public function listSequences();



    //
    // type-specific methods
    //

    /**
     * @param $feature
     * @return bool
     */
    public function supports($feature);


    /**
     * @return bool
     */
    public function supportsFulltext();


    /**
     * @return bool
     */
    public function supportsSlave();


    /**
     * @return bool
     */
    public function supportsTransactions();

    //
    //
    //
    /**
     * @return array
     */
    public function listTables();


    /**
     * @param string $module Manager|Reverse
     *
     * @return ilDBReverse|ilDBManager
     *
     * @internal Please do not use this in consumer code outside the Setup-Process or DB-Update-Steps.
     */
    public function loadModule($module);


    /**
     * @return array
     */
    public function getAllowedAttributes();


    /**
     * @param array $values
     * @param bool $allow_null
     * @return mixed
     */
    public function concat(array $values, $allow_null = true);


    /**
     * @param $a_needle
     * @param $a_string
     * @param int $a_start_pos
     * @return mixed
     */
    public function locate($a_needle, $a_string, $a_start_pos = 1);


    /**
     * @param $identifier
     * @param bool $check_option
     * @return string
     */
    public function quoteIdentifier($identifier, $check_option = false);


    /**
     * @param $table
     * @param $column
     * @param $attributes
     * @return bool
     */
    public function modifyTableColumn($table, $column, $attributes);


    /**
     * @param $a_st
     * @return mixed
     */
    public function free($a_st);


    /**
     * @param $a_name
     * @return bool
     */
    public function checkTableName($a_name);


    /**
     * @param $a_word
     * @return bool
     */
    public static function isReservedWord($a_word);


    /**
     * @return bool
     * @throws \ilDatabaseException
     */
    public function beginTransaction();


    /**
     * @return bool
     * @throws \ilDatabaseException
     */
    public function commit();


    /**
     * @return bool
     * @throws \ilDatabaseException
     */
    public function rollback();


    /**
     * @param $a_table
     * @param $a_constraint
     * @return string
     */
    public function constraintName($a_table, $a_constraint);


    /**
     * @param $a_table
     * @param string $a_name
     * @return bool
     */
    public function dropIndex($a_table, $a_name = "i1");


    /**
     * @param $a_name
     * @param string $a_charset
     * @param string $a_collation
     * @return mixed
     */
    public function createDatabase($a_name, $a_charset = "utf8", $a_collation = "");


    /**
     * @param $table_name
     * @param $afields
     * @return bool
     */
    public function dropIndexByFields($table_name, $afields);


    /**
     * @return string
     */
    public function getPrimaryKeyIdentifier();


    /**
     * @param $table_name
     * @param $afields
     * @param string $a_name
     * @return bool
     */
    public function addFulltextIndex($table_name, $afields, $a_name = 'in');


    /**
     * @param $a_table
     * @param $a_name
     * @return bool
     */
    public function dropFulltextIndex($a_table, $a_name);


    /**
     * @param $a_table
     * @param $a_name
     * @return bool
     */
    public function isFulltextIndex($a_table, $a_name);


    /**
     * @param $storage_engine
     */
    public function setStorageEngine($storage_engine);


    /**
     * @return string
     */
    public function getStorageEngine();


    /**
     * @return \ilAtomQuery
     */
    public function buildAtomQuery();


    /**
     * @param string $a_field_name
     * @param string $a_seperator
     * @param string $a_order
     * @return string
     */
    public function groupConcat($a_field_name, $a_seperator = ",", $a_order = null);


    /**
     * @param string $a_field_name
     * @param string $a_dest_type
     * @return string;
     */
    public function cast($a_field_name, $a_dest_type);
}

/**
 * Interface ilDBPdoInterface
 */
interface ilDBPdoInterface extends ilDBInterface
{

    /**
     * @param bool $native
     * @return int
     */
    public function getServerVersion($native = false);


    /**
     * @param $query
     * @param int $type
     * @param int $colnum
     * @return array
     */
    public function queryCol($query, $type = ilDBConstants::FETCHMODE_DEFAULT, $colnum = 0);


    /**
     * @param $query
     * @param null $types
     * @param int $fetchmode
     * @return array
     */
    public function queryRow($query, $types = null, $fetchmode = ilDBConstants::FETCHMODE_DEFAULT);


    /**
     * @param $value
     * @param bool $escape_wildcards
     * @return string
     */
    public function escape($value, $escape_wildcards = false);


    /**
     * @param $text
     * @return string
     */
    public function escapePattern($text);


    /**
     * @param string $engine
     *
     * @return array of failed tables
     */
    public function migrateAllTablesToEngine($engine = ilDBConstants::MYSQL_ENGINE_INNODB);


    /**
     * @return bool
     */
    public function supportsEngineMigration();


    /**
     * @param string $collation
     *
     * @return array of failed tables
     */
    public function migrateAllTablesToCollation($collation = ilDBConstants::MYSQL_COLLATION_UTF8MB4);


    /**
     * @return bool
     */
    public function supportsCollationMigration();


    /**
     * @param $table
     * @param $fields
     * @param string $name
     * @return bool
     */
    public function addUniqueConstraint($table, $fields, $name = "con");


    /**
     * @param $table
     * @param string $name
     * @return bool
     */
    public function dropUniqueConstraint($table, $name = "con");


    /**
     * @param $table
     * @param $fields
     * @return bool
     */
    public function dropUniqueConstraintByFields($table, $fields);


    /**
     * @param $name
     * @return bool
     */
    public function checkIndexName($name);


    /**
     * @return int
     */
    public function getLastInsertId();


    /**
     * @param $query
     * @param null $types
     * @param null $result_types
     * @return ilDBStatement
     */
    public function prepare($query, $types = null, $result_types = null);


    /**
     * @param $table
     * @param array $fields
     * @return bool
     */
    public function uniqueConstraintExists($table, array $fields);


    /**
     * @param $table_name
     */
    public function dropPrimaryKey($table_name);


    /**
     * @param $stmt
     * @param $data
     */
    public function executeMultiple($stmt, $data);


    /**
     * @param $expr
     * @param bool $to_text
     * @return string
     */
    public function fromUnixtime($expr, $to_text = true);


    /**
     * @return string
     */
    public function unixTimestamp();


    /**
     * Generate an insert, update or delete query and call prepare() and execute() on it
     *
     * @param string $tablename of the table
     * @param array $fields ($key=>$value) where $key is a field name and $value its value
     * @param int $mode of query to build
     *                          ilDBConstants::AUTOQUERY_INSERT
     *                          ilDBConstants::AUTOQUERY_UPDATE
     *                          ilDBConstants::AUTOQUERY_DELETE
     *                          ilDBConstants::AUTOQUERY_SELECT
     * @param string $where (in case of update and delete queries, this string will be put after the sql WHERE statement)
     *
     * @deprecated Will be removed in ILIAS 5.3
     * @return bool
     */
    public function autoExecute($tablename, $fields, $mode = ilDBConstants::AUTOQUERY_INSERT, $where = false);


    /**
     * returns the Version of the Database (e.g. MySQL 5.6)
     *
     * @return string
     */
    public function getDBVersion();
}
