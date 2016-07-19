<?php
namespace \CaT\TableRelations\Graphs;

/**
 * Represents na edge within abstractGraph.
 */
interface abstractEdge {
	/**
	 * Get the id of the node the edge instance is starting at.
	 *
	 * @return	string $node_id
	 */
	public function from();

	/**
	 * Get the id of the node the edge instance is ending at.
	 *
	 * @return	string $node_id
	 */
	public function to();
}