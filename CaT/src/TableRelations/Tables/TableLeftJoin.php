<?php
namespace \CaT\TableRelations\Tables;
use \CaT\TableRelations\Graphs as Graphs;


class TableLeftJoin implements Graphs\abstractTableDependency,Graphs\abstractEdge {

	public function dependingTables(abstractTable $from, abstractTable $to, Predicates\Predicate $predicate) {
		$this->from = $from;
		$this->to = $to;
		$this->predicate = $predicate;
	}

	public function from() {
		return $this->from->id();
	}

	public function to() {
		return $this->to->id();
	}

	public function dependanceCondition() {
		return $this->predicate;
	}
}