<?php

namespace CaT\TableRelations\Tables;

use CaT\Filter\Predicates as Predicates;

/**
 * An object representing quereis on a graph.
 * Tables inside query may be iterated inside a 
 * foreach loop.
 */

interface AbstractQuery extends \Iterator {

	/**
	 * List of requested fields
	 *
	 * @return Predicates\Field[]
	 */
	public function requested();

	/**
	 * Get the root table.
	 *
	 * @return Table.
	 */
	public function rootTable();

	/**
	 * Return having condition
	 *
	 * @return Predicates\Predicate
	 */
	public function having();

	/**
	 * Get a field to group by, if any.
	 *
	 * @return Predicates\Field|null
	 */
	public function groupedBy();
}