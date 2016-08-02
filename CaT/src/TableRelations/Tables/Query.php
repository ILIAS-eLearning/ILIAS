<?php

namespace CaT\TableRelations\Tables;

use CaT\Filter\Predicates as Predicates;
use CaT\TableRelations\Graphs as Graphs;
/**
 * @inheritdoc
 */
class Query implements AbstractQuery{

	protected $path;
	protected $having = null;
	protected $requested;
	protected $group_by = array();
	protected $filter = null;
	protected $root_table;


	public function valid() {
		return $this->path->valid();
	}

	public function key() {
		return $this->path->key();
	}

	public function current() {
		return $this->path->current();
	}

	public function next() {
		$this->current++;
		return $this->path->next();
	}

	/**
	 * We ignore root table in $path.
	 */
	public function rewind() {
		$this->path->rewind();
		$this->path->next();
	}

	public function currentJoinCondition() {
		return $this->join_conditions[$this->key()];
	}

	/**
	 * @inheritdoc
	 */
	public function requested() {
		return $this->requested;
	}

	/**
	 * @inheritdoc
	 */
	public function rootTable() {
		return $this->root_table;
	}

	/**
	 * @inheritdoc
	 */
	public function having() {
		return $this->having;
	}


	/**
	 * @inheritdoc
	 */
	public function groupBy() {
		return $this->group_by;
	}

	public function filter() {
		return $this->filter;
	}

	/**
	 * Query parameter setters;
	 */

	/**
	 * Set the fields to be returned in query.
	 *
	 * @param Predicates\Field[] $requested_fields
	 */
	public function setRequested(array $requested_fields) {
		$this->requested = $requested_fields;
		return $this;
	}

	/**
	 * Set the root table.
	 *
	 * @param Predicates\Field[] $requested_fields
	 */
	public function setRootTable(AbstractTable $root_table) {
		$this->root_table = $root_table;
		return $this;
	}

	/**
	 * Set a number of joins. Non obligatory.
	 *
	 * @param Predicates\Field[] $requested_fields
	 */
	public function setJoins(Graphs\Path $path) {
		$this->path = $path;
		return $this;
	}

	/**
	 * Set the joins associated whith join-tables.
	 *
	 * @param Predicates\Predicate[] $requested_fields
	 */
	public function setJoinConditions(array $join_conditions) {
		$this->join_conditions = $join_conditions;
		return $this;
	}

	/**
	 * Set filter (i.e. where).
	 *
	 * @param Predicates\Predicate $filter
	 */
	public function setFilter(Predicates\Predicate $filter) {
		$this->filter = $filter;
		return $this;
	}

	/**
	 * Set having (i.e. where).
	 *
	 * @param Predicates\Predicate $having
	 */
	public function setHaving(Predicates\Predicate $having) {
		$this->having = $having;
		return $this;
	}

	/**
	 * Set having (i.e. where).
	 *
	 * @param Predicate\Field[] $group_by
	 */
	public function setGroupBy($group_by) {
		$this->group_by[] = $group_by;
		return $this;
	}
}