<?php
namespace \CaT\TableRelations\Tables;

use \CaT\Filter\Predicates as Predicates;

/**
 * Store metadata about tables.
 */
interface abstractTable {
	/**
	 * Add a field to this instance of abstractTable.
	 *
	 * @param	abstractTableField	Field
	 */
	public function addField(abstractTableField $field);

	/**
	 * Get the list of all fields whithin this abstractTable.
	 *
	 * @return	TableField[]	Field
	 */
	public function getFields();

	/**
	 * Add a constrain to this instance of abstractTable.
	 * It must be a predicate solely operating on the 
	 * fields within this table.
	 *
	 * @param	Predicates\Predicate	$predicate
	 */
	public function addConstrain(Predicates\Predicate $predicate);

	/**
	 * Get the title of table. Note: a table represented by a title
	 * may be used several times with different id's/constrains.
	 *
	 * @return	string	$title
	 */
	public function title();

	/**
	 * Get the id of the table.
	 *
	 * @return	string	$id
	 */
	public function id();

	/**
	 * Add a constrain to this instance of abstractTable.
	 * It must be a predicate solely operating on the 
	 * fields within this table.
	 *
	 * @return	Predicates\Predicate	$predicate
	 */
	public function constrain();
}