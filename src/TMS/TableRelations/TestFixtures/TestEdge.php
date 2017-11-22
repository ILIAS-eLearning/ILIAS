<?php

namespace ILIAS\TMS\TableRelations\TestFixtures;

use ILIAS\TMS\TableRelations\Graphs as Graphs;

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