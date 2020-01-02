<?php

/**
 * Class ilDBLegacyInterface
 *
 * These are all public methods from ilDBInnoDB
 *
 * Currently unused, will be used to find missing methods in PDO and to discuss if they are still needed
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBLegacyInterface
{
    public function getDBType();


    public function initConnection();


    public function supportsFulltext();


    public function getStorageEngine();


    public function supportsSlave();


    /**
     * @param a_val
     */
    public function setDBSlaveActive($a_val);


    public function getDBSlaveActive();


    /**
     * @param a_user
     */
    public function setDBSlaveUser($a_user);


    public function getDBSlaveUser();


    /**
     * @param a_port
     */
    public function setDBSlavePort($a_port);


    public function getDBSlavePort();


    /**
     * @param a_host
     */
    public function setDBSlaveHost($a_host);


    public function getDBSlaveHost();


    /**
     * @param a_password
     */
    public function setDBSlavePassword($a_password);


    public function getDBSlavePassword();


    /**
     * @param a_name
     */
    public function setDBSlaveName($a_name);


    public function getDBSlaveName();


    public function getDSN();


    public function getSlaveDSN();


    public function getHostDSN();


    /**
     * @param a_storage_engine
     */
    public function setStorageEngine($a_storage_engine);


    public function getReservedWords();


    /**
     * @param tmpClientIniFile
     */
    public function initFromIniFile($tmpClientIniFile = null);


    public function doConnect();


    public function now();


    public function getDBVersion();


    public function isMysql4_0OrHigher();


    public function isMysql4_1();


    public function isMysql4_1OrHigher();


    public function isMysql5_6OrHigher();


    /**
     * @param a_query
     */
    public function checkQuerySize($a_query);


    /**
     * @param a_table
     * @param a_fields
     * @param a_name
     */
    public function addFulltextIndex($a_table, $a_fields, $a_name = 'in');


    /**
     * @param a_table
     * @param a_name
     */
    public function dropFulltextIndex($a_table, $a_name);


    /**
     * @param a_table
     * @param a_name
     */
    public function isFulltextIndex($a_table, $a_name);


    /**
     * @param a_tables
     */
    public function lockTables($a_tables);


    public function unlockTables();


    public function getErrorNo();


    public function getLastError();


    /**
     * @param sql
     * @param a_handle_error
     */
    public function query($sql, $a_handle_error = true);


    /**
     * @param module
     */
    public function loadModule($module);


    /**
     * @param a_user
     */
    public function setDBUser($a_user);


    public function getDBUser();


    /**
     * @param a_port
     */
    public function setDBPort($a_port);


    public function getDBPort();


    /**
     * @param a_host
     */
    public function setDBHost($a_host);


    public function getDBHost();


    /**
     * @param a_password
     */
    public function setDBPassword($a_password);


    public function getDBPassword();


    /**
     * @param a_name
     */
    public function setDBName($a_name);


    public function getDBName();


    /**
     * @param a_status
     */
    public function enableResultBuffering($a_status);


    /**
     * @param a_return_false_for_error
     */
    public function connect($a_return_false_for_error = false);


    public function disconnect();


    public function connectHost();


    /**
     * @param feature
     */
    public function supports($feature);


    public function supportsTransactions();


    /**
     * @param a_val
     */
    public function useSlave($a_val = true);


    /**
     * @param a_res
     * @param a_info
     * @param a_level
     */
    public function handleError($a_res, $a_info = '', $a_level = '');


    /**
     * @param a_message
     * @param a_level
     */
    public function raisePearError($a_message, $a_level = '');


    /**
     * @param a_res
     */
    public function isDbError($a_res);


    /**
     * @param a_name
     * @param a_charset
     * @param a_collation
     */
    public function createDatabase($a_name, $a_charset = 'utf8', $a_collation = '');


    /**
     * @param a_name
     * @param a_definition_array
     * @param a_drop_table
     * @param a_ignore_erros
     */
    public function createTable($a_name, $a_definition_array, $a_drop_table = false, $a_ignore_erros = false);


    /**
     * @param a_name
     * @param a_error_if_not_existing
     */
    public function dropTable($a_name, $a_error_if_not_existing = true);


    /**
     * @param a_name
     * @param a_changes
     */
    public function alterTable($a_name, $a_changes);


    /**
     * @param a_table
     * @param a_column
     * @param a_attributes
     */
    public function addTableColumn($a_table, $a_column, $a_attributes);


    /**
     * @param a_table
     * @param a_column
     */
    public function dropTableColumn($a_table, $a_column);


    /**
     * @param a_table
     * @param a_column
     * @param a_attributes
     */
    public function modifyTableColumn($a_table, $a_column, $a_attributes);


    /**
     * @param a_table
     * @param a_column
     * @param a_new_column
     */
    public function renameTableColumn($a_table, $a_column, $a_new_column);


    /**
     * @param a_name
     * @param a_new_name
     */
    public function renameTable($a_name, $a_new_name);


    /**
     * @param a_table
     * @param a_fields
     */
    public function addPrimaryKey($a_table, $a_fields);


    public function getPrimaryKeyIdentifier();


    /**
     * @param a_table
     */
    public function dropPrimaryKey($a_table);


    /**
     * @param a_table
     * @param a_fields
     * @param a_name
     * @param a_fulltext
     */
    public function addIndex($a_table, $a_fields, $a_name = 'in', $a_fulltext = false);


    /**
     * @param a_table
     * @param a_fields
     */
    public function indexExistsByFields($a_table, $a_fields);


    /**
     * @param a_table
     * @param a_fields
     */
    public function dropIndexByFields($a_table, $a_fields);


    /**
     * @param a_table
     * @param a_name
     */
    public function dropIndex($a_table, $a_name = 'in');


    /**
     * @param a_table
     * @param a_fields
     * @param a_name
     */
    public function addUniqueConstraint($a_table, $a_fields, $a_name = 'con');


    /**
     * @param a_table
     * @param a_name
     */
    public function dropUniqueConstraint($a_table, $a_name = 'con');


    /**
     * @param a_table
     * @param a_fields
     */
    public function dropUniqueConstraintByFields($a_table, $a_fields);


    /**
     * @param a_table_name
     * @param a_start
     */
    public function createSequence($a_table_name, $a_start = 1);


    /**
     * @param a_table_name
     */
    public function dropSequence($a_table_name);


    /**
     * @param a_name
     */
    public function checkTableName($a_name);


    /**
     * @param a_cols
     */
    public function checkTableColumns($a_cols);


    /**
     * @param a_col
     * @param a_def
     */
    public function checkColumn($a_col, $a_def);


    /**
     * @param a_def
     * @param a_modify_mode
     */
    public function checkColumnDefinition($a_def, $a_modify_mode = false);


    /**
     * @param a_name
     */
    public function checkColumnName($a_name);


    /**
     * @param a_name
     */
    public function checkIndexName($a_name);


    public function getAllowedAttributes();


    /**
     * @param a_table
     * @param a_constraint
     */
    public function constraintName($a_table, $a_constraint);


    /**
     * @param a_word
     */
    public function isReservedWord($a_word);


    /**
     * @param a_query
     * @param a_types
     * @param a_values
     */
    public function queryF($a_query, $a_types, $a_values);


    /**
     * @param a_query
     * @param a_types
     * @param a_values
     */
    public function manipulateF($a_query, $a_types, $a_values);


    /**
     * @param sql
     */
    public function logStatement($sql);


    /**
     * @param a_limit
     * @param a_offset
     */
    public function setLimit($a_limit, $a_offset = 0);


    /**
     * @param a_table_name
     */
    public function nextId($a_table_name);


    /**
     * @param sql
     */
    public function manipulate($sql);


    /**
     * @param a_query
     * @param a_types
     * @param a_result_types
     */
    public function prepare($a_query, $a_types = null, $a_result_types = null);


    /**
     * @param a_query
     * @param a_types
     */
    public function prepareManip($a_query, $a_types = null);


    /**
     * @param a_stmt
     * @param a_data
     */
    public function execute($a_stmt, $a_data = null);


    /**
     * @param a_stmt
     * @param a_data
     */
    public function executeMultiple($a_stmt, $a_data);


    /**
     * @param a_table
     * @param a_columns
     */
    public function insert($a_table, $a_columns);


    /**
     * @param a_table
     * @param a_columns
     * @param a_where
     */
    public function update($a_table, $a_columns, $a_where);


    /**
     * @param a_table
     * @param a_pk_columns
     * @param a_other_columns
     */
    public function replace($a_table, $a_pk_columns, $a_other_columns);


    /**
     * @param a_set
     */
    public function fetchAssoc($a_set);


    /**
     * @param a_st
     */
    public function free($a_st);


    /**
     * @param a_set
     */
    public function fetchObject($a_set);


    /**
     * @param a_set
     */
    public function numRows($a_set);


    /**
     * @param a_field
     * @param a_values
     * @param negate
     * @param a_type
     */
    public function in($a_field, $a_values, $negate = false, $a_type = '');


    /**
     * @param a_arr
     * @param a_type
     * @param a_cnt
     */
    public function addTypesToArray($a_arr, $a_type, $a_cnt);


    /**
     * @param a_values
     * @param a_allow_null
     */
    public function concat($a_values, $a_allow_null = true);


    /**
     * @param a_exp
     * @param a_pos
     * @param a_len
     */
    public function substr($a_exp, $a_pos = 1, $a_len = -1);


    /**
     * @param a_exp
     */
    public function upper($a_exp);


    /**
     * @param a_exp
     */
    public function lower($a_exp);


    /**
     * @param a_needle
     * @param a_string
     * @param a_start_pos
     */
    public function locate($a_needle, $a_string, $a_start_pos = 1);


    /**
     * @param a_col
     * @param a_type
     * @param a_value
     * @param case_insensitive
     */
    public function like($a_col, $a_type, $a_value = '?', $case_insensitive = true);


    /**
     * @param a_col
     * @param a_value
     * @param a_type
     * @param a_empty_or_null
     */
    public function equals($a_col, $a_value, $a_type, $a_empty_or_null = false);


    /**
     * @param a_col
     * @param a_value
     * @param a_type
     * @param a_empty_or_null
     */
    public function equalsNot($a_col, $a_value, $a_type, $a_empty_or_null = false);


    /**
     * @param a_expr
     * @param a_to_text
     */
    public function fromUnixtime($a_expr, $a_to_text = true);


    public function unixTimestamp();


    /**
     * @param a_table
     */
    public function tableExists($a_table);


    /**
     * @param a_table
     * @param a_column_name
     */
    public function tableColumnExists($a_table, $a_column_name);


    /**
     * @param a_table
     * @param a_fields
     */
    public function uniqueConstraintExists($a_table, $a_fields);


    public function listTables();


    /**
     * @param a_sequence
     */
    public function sequenceExists($a_sequence);


    public function listSequences();


    /**
     * @param a_query
     * @param a_type
     */
    public function quote($a_query, $a_type = null);


    /**
     * @param a_identifier
     * @param check_option
     */
    public function quoteIdentifier($a_identifier, $check_option = false);


    public function beginTransaction();


    public function commit();


    public function rollback();


    /**
     * @param a_tablename
     * @param a_fields
     * @param a_mode
     * @param a_where
     */
    public function autoExecute($a_tablename, $a_fields, $a_mode = 'MDB2_AUTOQUERY_INSERT', $a_where = false);


    public function getLastInsertId();


    /**
     * @param sql
     */
    public function getOne($sql);


    /**
     * @param sql
     * @param mode
     */
    public function getRow($sql, $mode = 3);


    /**
     * @param query_result
     * @param fetch_mode
     */
    public function fetchAll($query_result, $fetch_mode = 2);


    /**
     * @param a_value
     */
    public function setSubType($a_value);


    public function getSubType();


    /**
     * @param engine
     */
    public function migrateAllTablesToEngine($engine = 'InnoDB');


    public function supportsEngineMigration();


    /**
     * @param table_name
     */
    public function getSequenceName($table_name);


    public function buildAtomQuery();


    /**
     * @param error_class
     */
    public function PEAR($error_class = null);


    public function _PEAR();


    /**
     * @param class
     * @param var
     */
    public function getStaticProperty($class, $var);


    /**
     * @param func
     * @param args
     */
    public function registerShutdownFunc($func, $args = array());


    /**
     * @param data
     * @param code
     */
    public function isError($data, $code = null);


    /**
     * @param mode
     * @param options
     */
    public function setErrorHandling($mode = null, $options = null);


    /**
     * @param code
     */
    public function expectError($code = '*');


    public function popExpect();


    /**
     * @param error_code
     */
    public function _checkDelExpect($error_code);


    /**
     * @param error_code
     */
    public function delExpect($error_code);


    /**
     * @param message
     * @param code
     * @param mode
     * @param options
     * @param userinfo
     * @param error_class
     * @param skipmsg
     */
    public function raiseError($message = null, $code = null, $mode = null, $options = null, $userinfo = null, $error_class = null, $skipmsg = false);


    /**
     * @param message
     * @param code
     * @param userinfo
     */
    public function throwError($message = null, $code = null, $userinfo = null);


    /**
     * @param mode
     * @param options
     */
    public function staticPushErrorHandling($mode, $options = null);


    public function staticPopErrorHandling();


    /**
     * @param mode
     * @param options
     */
    public function pushErrorHandling($mode, $options = null);


    public function popErrorHandling();


    /**
     * @param ext
     */
    public function loadExtension($ext);
}
