<?php

namespace CaT\TableRelations\Tables;

use CaT\Filter\Predicates as Predicates;
use CaT\TableRelations\Graphs as Graphs;

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
	 * Get the root table. This corresponds to the 'FROM' part
	 * of a sql query.
	 *
	 * @return Table.
	 */
	public function rootTable();

	/**
	 * Return having condition
	 *
	 * @return Predicates\Predicate|null
	 */
	public function having();

	/**
	 * Get a field to group by, if any.
	 *
	 * @return Predicates\Field|null
	 */
	public function groupBy();

	/**
	 * Get global predicate on the whole table selection.
	 *
	 * @return Predicates\Predicvate|null
	 */
	public function filter();

	/**
	 * Set the fields to be returned in query.
	 *
	 * @param	Predicates\Field[] $requested_fields
	 * @return	AbstractQuery
	 */
	public function withRequested(array $requested_fields);

	/**
	 * Set the root table.
	 *
	 * @param	AbstractTable	$root_table
	 * @return	AbstractQuery
	 */
	public function withRootTable(AbstractTable $root_table);

	/**
	 * Set a sequence of joins. Non obligatory.
	 *
	 * @param Graphs\Path $path
	 * @return	AbstractQuery
	 */
	public function withJoins(Graphs\Path $path);

	/**
	 * Set the joins associated whith join-tables.
	 *
	 * @param Predicates\Predicate[][] $requested_fields
	 * @return	AbstractQuery
	 */
	public function withJoinConditions(array $join_conditions);

	/**
	 * Set filter (i.e. where).
	 *
	 * @param Predicates\Predicate $filter
	 * @return	AbstractQuery
	 */
	public function withFilter(Predicates\Predicate $filter);

	/**
	 * Set having (i.e. where).
	 *
	 * @param Predicates\Predicate $having
	 * @return	AbstractQuery
	 */
	public function withHaving(Predicates\Predicate $having);

	/**
	 * Set having (i.e. where).
	 *
	 * @param Predicate\Field $group_by
	 * @return	AbstractQuery
	 */
	public function withGroupByField($group_by);
}