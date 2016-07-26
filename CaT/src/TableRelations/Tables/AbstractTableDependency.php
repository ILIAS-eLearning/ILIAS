<?php
namespace CaT\TableRelations\Tables;

/**
 * Store binary dependencies between Abstract tables.
 */
interface AbstractTableDependency {

	/**
	 * Get the first table of dependency
	 */
	public function from();

	/**
	 * Get the second table of dependency
	 */
	public function to();
	
	/**
	 * Describe two tables as being dependent via a predicate.
	 * The predicate should represent a boolean-return depending on table-fields.
	 *
	 * @return	Predicates\Predicate	$predicate
	 */
	public function dependencyCondition();
}
