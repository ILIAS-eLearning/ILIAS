<?php
namespace ILIAS\TMS\TableRelations\Graphs;

/**
 * Represents a Node within AbstractGraph.
 */
interface AbstractNode {
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
