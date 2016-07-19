<?php
namespace \CaT\TableRelations\Tables;

use CaT\Filter\Predicates as Predicates;

/**
 * Store metadata about tables.
 */
interface abstractTable {
	/**
	 * Add a field to this instance of abstractTable.
	 *
	 * @param	Predicats\Field	Field
	 */
	public function addField(Predicates\Field $field);

	/**
	 * Get the list of all fields whithin this abstractTable.
	 *
	 * @return	Predicats\Field[]	Field
	 */
	public function getFields();

	/**
	 * Add a constrain to this instance of abstractTable.
	 * It must be a predicate solely operating on the 
	 * fields within this table.
	 *
	 * @param	Predicats\Field	Field
	 */
	public function addConstrain(Predicates\Predicate $predicate);

	public function title();
}