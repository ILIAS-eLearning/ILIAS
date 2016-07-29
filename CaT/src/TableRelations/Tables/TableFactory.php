<?php
namespace CaT\TableRelations\Tables;

use CaT\Filter as Filters;

class TableFactory {
	public function __construct() {
		$this->predicate_factory = new Filters\PredicateFactory;
	}

	public function Field($name, $table_id = null) {
		return new TableField($this->predicate_factory, $name, $table_id);
	}

	public function Table($name, $table_id,array $fields = array()) {
		$table = new Table($name, $table_id);
		foreach ($fields as $field) {
			$table->addField($field);
		}
		return $table;
	}

	public function TableJoin(AbstractTable $from, AbstractTable $to, Filters\Predicates\Predicate $predicate) {
		$table = new TableJoin;
		$table->dependingTables($from, $to, $predicate);
		return $table;
	}

	public function TableLeftJoin(AbstractTable $from, AbstractTable $to, Filters\Predicates\Predicate $predicate) {
		$table = new TableLeftJoin;
		$table->dependingTables($from, $to, $predicate);
		return $table;
	}

	public function DerivedField($name, \Closure $postprocess,$fields) {
		return new DerivedField($this->predicate_factory,$name,$postprocess,$fields);
	}
}
