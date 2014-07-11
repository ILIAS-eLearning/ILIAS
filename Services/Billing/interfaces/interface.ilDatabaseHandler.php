<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilDatabaseHandler
{
	const LOCK_READ  = 2;
	const LOCK_WRITE = 1;
	
	/**
	 * @param mixed $set
	 * @return mixed|null
	 */
	public function fetchAssoc($set);

	/**
	 * @param mixed $set
	 * @return mixed|null
	 */
	public function fetchObject($set);

	/**
	 * @param string $query
	 * @return mixed A result set/handle
	 */
	public function query($query);

	/**
	 * @param string $query
	 * @return int The number of affected rows
	 */
	public function manipulate($query);

	/**
	 * @param string $query
	 * @param array  $types
	 * @param array  $values
	 * @return mixed
	 */
	public function queryF($query, $types, $values);

	/**
	 * @param string $query
	 * @param array  $types
	 * @param array  $values
	 * @return int The number of affected rows
	 */
	public function manipulateF($query, $types, $values);

	/**
	 * @param  string$query
	 * @param string $type
	 * @return string
	 */
	public function quote($query, $type = '');

	/**
	 * @param string $field
	 * @param array  $values
	 * @param bool   $negate
	 * @param string $type
	 * @return string
	 */
	public function in($field, $values, $negate = false, $type = '');

	/**
	 * @param string  $field
	 * @param string  $type
	 * @param string  $value
	 * @param bool    $case_insensitive
	 * @return string
	 */
	public function like($field, $type, $value = "?", $case_insensitive = true);

	/**
	 * @param int $limit
	 * @param int $offset
	 */
	public function setLimit($limit, $offset = 0);

	/**
	 * @param string $table
	 * @param array  $what
	 * @return int
	 */
	public function insert($table, $what);

	/**
	 * @param string $table
	 * @param array  $what
	 * @param array  $where
	 * @return int
	 */
	public function update($table, $what, $where);

	/**
	 * @param string $table
	 * @return int
	 */
	public function nextId($table);

	/**
	 * @param array $definition
	 */
	public function lockTables(array $definition);

	/**
	 *
	 */
	public function unlockTables();

	/**
	 *
	 */
	public function beginTransaction();

	/**
	 *
	 */
	public function commit();

	/**
	 *
	 */
	public function rollback();
}