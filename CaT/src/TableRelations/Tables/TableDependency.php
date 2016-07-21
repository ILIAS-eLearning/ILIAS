<?php
namespace \CaT\TableRelations\Tables;
use \CaT\TableRelations\Graphs as Graphs;
use \CaT\Filter\Predicates as Predicates;

abstract class TableDependency implements abstractTableDependency, Graphs\abstractEdge {
	public function dependingTables(abstractTable $from, abstractTable $to, Predicates\Predicate $predicate) {
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

	public function dependencyCondition() {
		return $this->predicate;
	}
}