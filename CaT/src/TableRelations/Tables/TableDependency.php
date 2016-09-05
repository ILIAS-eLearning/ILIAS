<?php
namespace CaT\TableRelations\Tables;
use CaT\TableRelations\Graphs as Graphs;
use CaT\Filter\Predicates as Predicates;
/**
 * @inheritdoc
 */
Abstract class TableDependency implements AbstractTableDependency, Graphs\AbstractEdge {

	/**
	 * Which tables are depending by this?
	 *
	 * @param AbstractTable	$from
	 * @param AbstractTable	$to
	 * @param Predicates\Predicate $predicate
	 */
	public function dependingTables(AbstractTable $from, AbstractTable $to, Predicates\Predicate $predicate) {
		$this->from = $from;
		$this->to = $to;
		$this->predicate = $predicate;
	}

	/**
	 * @inheritdoc
	 */
	public function fromId() {
		return $this->from->id();
	}

	/**
	 * @inheritdoc
	 */
	public function toId() {
		return $this->to->id();
	}

	/**
	 * @inheritdoc
	 */
	public function from() {
		return $this->from;
	}

	/**
	 * @inheritdoc
	 */
	public function to() {
		return $this->to;
	}

	/**
	 * @inheritdoc
	 */
	public function dependencyCondition() {
		return $this->predicate;
	}

	/**
	 * @return	AbstractFields
	 */
	public function fields() {
		return $this->predicate->fields();
	}
}
