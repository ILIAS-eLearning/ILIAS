<?php
use CaT\TableRelations\Graphs as Graphs;
use CaT\TableRelations as TR;

class GraphTestFactory extends TR\GraphFactory {
	public function Node($id, $subgraph = 0) {
		return new TestNode($id, $subgraph);
	}

	public function Edge($from_id,$to_id) {
		return new TestEdge($from_id,$to_id);
	}
}

class TestNode implements Graphs\AbstractNode {
	public function __construct($id, $subgraph = 0) {
		$this->id = $id;
		$this->subgraph = $subgraph;
	}

	public function id() {
		return $this->id;
	}

	public function subgraph() {
		return $this->subgraph;
	}
}

class TestEdge implements Graphs\AbstractEdge {
	public function __construct($from_id, $to_id) {
		$this->from_id = $from_id;
		$this->to_id = $to_id;
	}

	public function fromId() {
		return $this->from_id;
	}

	public function toId() {
		return $this->to_id;
	}
}
