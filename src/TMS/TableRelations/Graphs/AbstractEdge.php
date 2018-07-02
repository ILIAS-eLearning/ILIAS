<?php
namespace ILIAS\TMS\TableRelations\Graphs;

/**
 * Represents na edge within AbstractGraph.
 */
interface AbstractEdge {
	/**
	 * Get the id of the node the edge instance is starting at.
	 *
	 * @return	string $node_id
	 */
	public function fromId();

	/**
	 * Get the id of the node the edge instance is ending at.
	 *
	 * @return	string $node_id
	 */
	public function toId();
}
