<?php
use CaT\TableRelations\Graphs as Graphs;

class GraphTestFactory extends Graphs\GraphFactory {
	public function Node($id) {
		return new TestNode($id);
	}

	public function Edge($from_id,$to_id) {
		return new TestEdge($from_id,$to_id);
	}
}

class TestNode implements Graphs\abstractNode {
	public function __construct($id) {
		$this->id = $id;
	}

	public function id() {
		return $this->id;
	}
}

class TestEdge implements Graphs\abstractEdge {
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