<?php
namespace ILIAS\TMS\TableRelations\TestFixtures;

use ILIAS\TMS\TableRelations\Graphs as Graphs;
use ILIAS\TMS\TableRelations as TR;

class GraphTestFactory extends TR\GraphFactory {
	public function Node($id, $subgraph = 0) {
		return new TestNode($id, $subgraph);
	}

	public function Edge($from_id,$to_id) {
		return new TestEdge($from_id,$to_id);
	}
}

