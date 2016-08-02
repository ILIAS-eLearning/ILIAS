<?php
namespace CaT\TableRelations;

use CaT\Filter as Filters;

class TableFactory {
	public function __construct(Filters\PredicateFactory $predicate_factory, GraphFactory $gf) {
		$this->predicate_factory = $predicate_factory;
		$this->graph_factory = $gf;
	}

	public function Field($name, $table_id = null) {
		return new Tables\TableField($this->predicate_factory, $name, $table_id);
	}

	public function Table($name, $table_id,array $fields = array()) {
		$table = new Tables\Table($name, $table_id);
		foreach ($fields as $field) {
			$table->addField($field);
		}
		return $table;
	}

	public function TableJoin(Tables\AbstractTable $from, Tables\AbstractTable $to, Filters\Predicates\Predicate $predicate) {
		$table = new Tables\TableJoin;
		$table->dependingTables($from, $to, $predicate);
		return $table;
	}

	public function TableLeftJoin(Tables\AbstractTable $from, Tables\AbstractTable $to, Filters\Predicates\Predicate $predicate) {
		$table = new Tables\TableLeftJoin;
		$table->dependingTables($from, $to, $predicate);
		return $table;
	}

	public function DerivedField($name, \Closure $postprocess,$fields) {
		return new Tables\DerivedField($this->predicate_factory,$name,$postprocess,$fields);
	}

	public function TableSpace() {
		return new Tables\TableSpace($this, $this->graph_factory,$this->predicate_factory);
	}

	public function query() {
		return new Tables\Query;
	}
}
