<?php

/**
 * Interface ilDBPdoInterface
 */
interface ilDBPdoInterface extends ilDBInterface {

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
