<?php
namespace \CaT\TableRelations\Tables;

use CaT\Filter\Predicates as Predicates;

/**
 * Store dependencies between abstract tables.
 */
interface abstractTableDependency {
	/**
	 * Describe two tables as being dependent via a predicate.
	 * The predicate should represent a boolean-return depending on table-fields.
	 *
	 * @param	abstractTable	$table_1
	 * @param	abstractTable	$table_2
	 * @param	Predicates/Predicate	$predicate
	 */
	public function dependingTables(abstractTable $table_1, abstractTable $table_2, Predicates\Predicate $predicate);


	public function dependanceCondition();
}