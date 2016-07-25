<?php
namespace CaT\TableRelations\Graphs;

/**
 * Represents a Node within abstractGraph.
 */
interface abstractNode {
	/**
	 * Get the id of the node.
	 *
	 * @return	string
	 */
	public function id();

	/**
	 * Get Subgraph_id of the node.
	 *
	 * @return	string
	 */
	public function subgraph();
}