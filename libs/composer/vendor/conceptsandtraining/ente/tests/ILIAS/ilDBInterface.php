<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

/**
 * Essentials of ILIAS database for this framework.
 */
interface ilDBInterface {
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
	 * @param $table_name
	 * @param $fields
	 * @param string $index_name
	 * @param bool $fulltext
	 * @return bool
	 */
	public function addIndex($table_name, $fields, $index_name = '', $fulltext = false);

	/**
	 * @param $table_name  string
	 * @param $column_name string
	 * @param $attributes  array
	 */
	public function addTableColumn($table_name, $column_name, $attributes);

	/**
	 * @param $query string
	 *
	 * @return \ilPDOStatement
	 */
	public function query($query);

	/**
	 * @param $table_name string
	 * @param $values
	 * @return int|void
	 */
	public function insert($table_name, $values);

	/**
	 * @param $query_result ilDBStatement
	 *
	 * @return mixed
	 */
	public function fetchAssoc($query_result);

	/**
	 * @param $value
	 * @param $type
	 *
	 * @return string
	 */
	public function quote($value, $type);

	/**
	 * @param $query string
	 * @return int|void
	 */
	public function manipulate($query);

	/**
	 * @param $field  string
	 * @param $values array
	 * @param bool $negate
	 * @param string $type
	 * @return string
	 */
	public function in($field, $values, $negate = false, $type = "");
}
