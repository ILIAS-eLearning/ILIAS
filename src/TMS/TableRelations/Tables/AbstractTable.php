<?php
namespace ILIAS\TMS\TableRelations\Tables;

use ILIAS\TMS\Filter\Predicates as Predicates;

/**
 * Store metadata about tables.
 */
interface AbstractTable {

	/**
	 * Get the list of all fields whithin this AbstractTable.
	 *
	 * @return	TableField[]	Field
	 */
	public function fields();

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
	 * Add constrains to this instance of AbstractTable.
	 * It must be a predicate solely operating on the 
	 * fields within this table.
	 *
	 * @return	Predicates\Predicate|null	$predicate
	 */
	public function constraint();

	/**
	 * Check if a field is contained in this table.
	 */
	public function fieldInTable(AbstractTableField $field);

	/**
	 * Get the field with the corresponding id, if it is in table.
	 *
	 * @param	string	$id
	 * @return	AbstractTableField
	 */
	public function field($id);
}
