<?php

namespace CaT\TableRelations\Graphs;

/**
 * A path is a sequence of node ids. It must at least have a start-node.
 * A path is never self-crossing.
 * One may also add some metadata associated with a node.
 */
class Path {
	protected $sequence = array();
	public function __construct($start_node_id, array $data) {

	}
}