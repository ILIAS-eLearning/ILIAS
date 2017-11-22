<?php

namespace ILIAS\TMS\TableRelations\TestFixtures;

use ILIAS\TMS\TableRelations\Graphs as Graphs;

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