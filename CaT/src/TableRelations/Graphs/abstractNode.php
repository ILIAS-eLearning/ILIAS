<?php
namespace \CaT\TableRelations\Graphs;

/**
 * Represents a Node within abstractGraph.
 */
interface abstractNode {
	/**
	 * Get the id of the node.
	 *
	 * @return	string	$id
	 */
	public function id();
}