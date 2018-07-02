<?php
namespace ILIAS\TMS\TableRelations\Graphs;

/**
 * Store graph information.
 */
interface AbstractGraph {
	/**
	 * add a node to graph.
	 *
	 * @param	AbstractNode	$node
	 */
	public function addNode(AbstractNode $node);

	/**
	 * connect two nodes symmetrically within graph.
	 *
	 * @param	AbstractEdge $edge
	 */
	public function connectNodesSymmetric(AbstractEdge $edge);

	/**
	 * connect two nodes directed within graph.
	 * $from is connected with to, not the other way around.
	 *
	 * @param	AbstractEdge $edge
	 */
	public function connectNodesDirected(AbstractEdge $edge);

	/**
	 * Get all nodes lying on all possible paths between $from_id and $to_id.
	 * We only consider connections, which visit any node at most once.
	 *
	 * @param	string	$from_id
	 * @param	string	$to_id
	 * @return	AbstractNode[]
	 */
	public function getNodesBetween($from_id, $to_id, $subgraph_id = null);

	/**
	 * Get node by id.
	 *
	 * @param	string	$node_id
	 * @return	AbstractNode
	 */
	public function getNodeById($node_id);

	/**
	 * Is graph connected?
	 *
	 * @return bool
	 */
	public function connected($from_id, $to_id);

	/**
	 * Get all nodes within graph.
	 *
	 * @return AbstractNode[]
	 */
	public function nodes();

	/**
	 * Get all edges within graph.
	 *
	 * @return AbstractEdge[]
	 */
	public function edges();

	/**
	 * Get subgraph id of a node within this graph.
	 *
	 * @param	AbstractNode	$node
	 */
	public function nodeSubgraphId(AbstractNode $node);

	/**
	 * Get the edge connecting nodes with ids from_id and to_id directed.
	 *
	 * @param	int	$from
	 * @param	int	$to
	 * @return AbstractEdge
	 */
	public function edgeBetween($from_id, $to_id);

	/**
	 * Get all paths connecting nodes with ids from_id and to_id, that run
	 * entirely within subgraph sg.
	 *
	 * @param	int	$from
	 * @param	int	$to
	 * @param	int	$sg
	 * @return	Path[]
	 */
	public function getPathsBetween($from_id, $to_id, $sg = null);
}
