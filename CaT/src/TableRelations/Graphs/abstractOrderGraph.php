<?php
namespace \CaT\TableRelations\Graphs;

/**
 * We will need to sort nodes according to
 * 1. their order (wether a node is connected by means of only
 * directed of only undirected edges.)
 * 2. their minimum distance from some initial node.
 */
interface abstractMinimizeGraph extends abstractGraph {
	public function getPathsStartingAt($node_id);
	public function orderNodesStartingAtNodeId($node_id);
}