<?php

namespace ILIAS\TMS\TableRelations\Tables;

use ILIAS\TMS\Filter\Predicates as Predicates;
use ILIAS\TMS\TableRelations\Graphs as Graphs;
/**
 * @inheritdoc
 */
class Query implements AbstractQuery {
	protected $path;
	protected $having;
	protected $requested;
	protected $group_by;
	protected $filter;
	protected $root_table;

	/**
	 * @var AbstractField[]
	 */
	protected $order_by = [];

	/**
	 * @var string
	 */
	protected $order_mode;

	public function __construct() {
		$this->path = null;
		$this->having = null;
		$this->requested = null;
		$this->group_by = array();
		$this->filter = null;
		$this->root_table = null;
		$this->order_by = [];
		$this->order_mode = TableSpace::ORDER_ASC;
	}

	/**
	 * Iterator-functions
	 */
	public function valid() {
		return $this->path ? $this->path->valid() : false;
	}

	/**
	 * @inheritdoc
	 */
	public function key() {
		return $this->path->key();
	}
	
	/**
	 * @inheritdoc
	 */
	public function current() {
		return $this->path->current();
	}
	
	/**
	 * @inheritdoc
	 */
	public function next() {
		$this->current++;
		return $this->path->next();
	}

	/**
	 * We ignore root table in $path.
	 */
	public function rewind() {
		if($this->path) {
			$this->path->rewind();
			$this->path->next();
		}
	}

	/**
	 * Get the join condition, on which the current object is joined.
	 *
	 * @return Predicates\Predicate
	 */
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

	/**
	 * @inheritdoc
	 */
	public function orderByFields() {
		return $this->order_by;
	}

	/**
	 * @inheritdoc
	 */
	public function orderByMode() {
		return $this->order_mode;
	}


	/**
	 * @inheritdoc
	 */
	public function filter() {
		return $this->filter;
	}

	/**
	 * Query parameter setters;
	 */

	/**
	 * @inheritdoc
	 */
	public function withRequested(array $requested_fields) {
		$return = clone $this;
		$return->requested = $requested_fields;
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function withRootTable(AbstractTable $root_table) {
		$return = clone $this;
		$return->root_table = $root_table;
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function withJoins(Graphs\Path $path) {
		$return = clone $this;
		$return->path = $path;
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function withJoinConditions(array $join_conditions) {
		$return = clone $this;
		$return->join_conditions = $join_conditions;
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function withFilter(Predicates\Predicate $filter) {
		$return = clone $this;
		$return->filter = $filter;
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function withHaving(Predicates\Predicate $having) {
		$return = clone $this;
		$return->having = $having;
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function withGroupByField($group_by) {
		$return = clone $this;
		$return->group_by[] = $group_by;
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function withOrderBy(array $order_by,$order_mode = TableSpace::ORDER_ASC) {
		$return = clone $this;
		$return->order_by = $order_by;
		$return->order_mode = $order_mode;
		return $return;
	}
}
