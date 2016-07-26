<?php
namespace CaT\TableRelations\Graphs;

/**
 * Store graph information.
 */
interface abstractGraph {

	/**
	 * add a whole subgraph to this graph.
	 *
	 * @param	abstractGraph $graph
	 */
	public function addGraph(abstractGraph $graph);

	/**
	 * add a node to graph.
	 *
	 * @param	abstractNode	$node
	 */
	public function addNode(abstractNode $node);

	/**
	 * connect two nodes symmetrically within graph.
	 *
	 * @param	abstractEdge $edge
	 */
	public function connectNodesSymmetric(abstractEdge $edge);

	/**
	 * connect two nodes directed within graph.
	 * $from is connected with to, not the other way around.
	 *
	 * @param	abstractEdge $edge
	 */
	public function connectNodesDirected(abstractEdge $edge);

	/**
	 * Get all nodes lying on all possible paths between $from_id and $to_id.
	 * We only consider connections, which visit any node at most once.
	 *
	 * @param	string	$from_id
	 * @param	string	$to_id
	 * @return	abstractNode[]
	 */
	public function getNodesBetween($from_id, $to_id, $subgraph_id = null);

	/**
	 * Get node by id.
	 *
	 * @param	string	$node_id
	 * @return	abstractNode
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
	 * @return abstractNode[]
	 */
	public function nodes();

	/**
	 * Get all edges within graph.
	 *
	 * @return abstractEdge[]
	 */
	public function edges();

	/**
	 * Get subgraph id of a node within this graph.
	 *
	 * @param	abstractNode	$node
	 */
	public function nodeSubgraphId(abstractNode $node);
}