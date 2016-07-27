<?php
namespace CaT\TableRelations\Tables;
use CaT\TableRelations\Graphs as Graphs;
use CaT\Filter\Predicates as Predicates;

Abstract class TableDependency implements AbstractTableDependency, Graphs\AbstractEdge {
	public function dependingTables(AbstractTable $from, AbstractTable $to, Predicates\Predicate $predicate) {
		$this->from = $from;
		$this->to = $to;
		$this->predicate = $predicate;
	}

	public function fromId() {
		return $this->from->id();
	}

	public function toId() {
		return $this->to->id();
	}

	public function from() {
		return $this->from;
	}

	public function to() {
		return $this->to;
	}

	public function dependencyCondition() {
		return $this->predicate;
	}

	public function fields() {
		return $this->predicate->fields();
	}
}
