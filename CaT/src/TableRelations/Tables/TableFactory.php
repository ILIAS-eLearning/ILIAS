<?php
use CaT\Filter\Predicates as Predicates;
class TableFactory {
	public function __construct() {
		$this->predicate_factory = new \Predicates\PredicateFactory;
	}

	public function TableField($name, $table_id = null) {
		return new TableField($this->predicate_factory, $name, $table_id);
	}

	public function Table($name, $table_id) {
		return new Table($name, $table_id);
	}
}