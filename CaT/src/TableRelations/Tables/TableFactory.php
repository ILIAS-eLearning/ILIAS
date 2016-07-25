<?php
namespace CaT\TableRelations\Tables;

use CaT\Filter as Filters;

class TableFactory {
	public function __construct() {
		$this->predicate_factory = new Filters\PredicateFactory;
	}

	public function TableField($name, $table_id = null) {
		return new TableField($this->predicate_factory, $name, $table_id);
	}

	public function Table($name, $table_id,array $fields = array()) {
		$table = new Table($name, $table_id);
		foreach ($fields as $field) {
			$table->addField($field);
		}
		return $table;
	}

	public function TableJoin(abstractTable $from, abstractTable $to, Filters\Predicates\Predicate $predicate) {
		$table = new TableJoin;
		$table->dependingTables($from, $to, $predicate);
		return $table;
	}

	public function TableLeftJoin(abstractTable $from, abstractTable $to, Filters\Predicates\Predicate $predicate) {
		$table = new TableLeftJoin;
		$table->dependingTables($from, $to, $predicate);
		return $table;
	}
}