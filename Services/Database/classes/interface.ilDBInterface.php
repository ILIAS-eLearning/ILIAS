<?php

/**
 * Interface ilDBInterface
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBInterface {

	/**
	 * Get reserved words. This must be overwritten in DBMS specific class.
	 * This is mainly used to check whether a new identifier can be problematic
	 * because it is a reserved word. So createTable / alterTable usually check
	 * these.
	 */
	static function getReservedWords();


	/**
	 * @param null $tmpClientIniFile
	 */
	public function initFromIniFile($tmpClientIniFile = null);


	/**
	 * @return void
	 */
	public function connect();


	/**
	 * @param $table_name string
	 *
	 * @return int
	 */
	public function nextId($table_name);


	/**
	 * experimental....
	 *
	 * @param $table_name string
	 * @param $fields     array
	 */
	public function createTable($table_name, $fields);


	/**
	 * @param $table_name   string
	 * @param $primary_keys array
	 */
	public function addPrimaryKey($table_name, $primary_keys);


	/**
	 * @param $table_name string
	 */
	public function createSequence($table_name);


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
	 * @param $table_name string
	 */
	public function dropTable($table_name);


	/**
	 * @param $query string
	 *
	 * @return \ilDBStatement
	 */
	public function query($query);

	/**
	 * @param $query_result PDOStatement
	 *
	 * @return array
	 */
	//public function fetchAll($query_result);

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
	 * @param $query string
	 * @return int|void
	 */
	public function manipulate($query);


	/**
	 * @param $query_result PDOStatement
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
	 * @param $index_name
	 *
	 * @return null
	 */
	public function addIndex($table_name, $index_name);

	/**
	 * @param $fetchMode int
	 * @return mixed
	 * @throws ilDatabaseException
	 */
	//function fetchRow($fetchMode = DB_FETCHMODE_ASSOC);

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
	 * @param array table definitions
	 * @return
	 */
	public function lockTables($a_tables);


	/**
	 * Unlock tables locked by previous lock table calls
	 * @return
	 */
	public function unlockTables();


	/**
	 * @param $field string
	 * @param $values array
	 * @param bool $negate
	 * @param string $type
	 * @return string
	 */
	public function in($field, $values, $negate = false, $type = "");


	/**
	 * @param $query string
	 * @param $types string[]
	 * @param $values mixed[]
	 * @return \ilDBStatement
	 */
	public function queryF($query, $types, $values);


	/**
	 * @param $query string
	 * @param $types string[]
	 * @param $values mixed[]
	 * @return string
	 */
	public function manipulateF($query, $types, $values);


	/**
	 * Return false iff slave is not supported.
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
	 * @param bool $caseInsensitive
	 * @return string
	 */
	public function like($column, $type, $value = "?", $caseInsensitive = true);


	/**
	 * @return string the now statement
	 */
	public function now();


	/**
	 * Replace into method.
	 *
	 * @param    string        table name
	 * @param    array        primary key values: array("field1" => array("text", $name), "field2" => ...)
	 * @param    array        other values: array("field1" => array("text", $name), "field2" => ...)
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
}