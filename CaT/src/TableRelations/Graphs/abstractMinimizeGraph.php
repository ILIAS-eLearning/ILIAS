<?php
namespace \CaT\TableRelations\Graphs;

/**
 * Perform graph operations.
 * Our graph will be divided into several subgraphs.
 * We will need the ability to find all nodes connecting two nodes.
 * A node N "connecting" two node means these may be connected by
 * some path visiting any other node at most once and N exactly once.
 * Connections are allways non self crossing.
 * We will need the ability to find paths connecting a subgraph to
 * some node N, i.e. there a paths starting inside a subgraph and finishing
 * at N.
 */
interface abstractMinimizeGraph extends abstractGraph {
	/**
	 * Get all nodes lying on all possible paths between $from_id and $to_id.
	 * We only consider connections, which visit any node at most once.
	 *
	 * @param	string	$from_id
	 * @param	string	$to_id
	 * @return	abstractNode[]
	 */
	public function getNodesBetween($from_id, $to_id);

	/**
	 * Get all nodes lying on all possible paths between $from and $to
	 * within a subgraph $grapher only. $from and $to may be outiside
	 * $grapher though.
	 * We only consider connections, which visit any node at most once.
	 *
	 * @param	string	$from_id
	 * @param	string	$to_id
	 * @return	abstractNode[]
	 */
	public function getNodesWithinSubgraphBetween($from_id, $to_id, $subgraph_id = 0);

	/**
	 * Get the smallest complete subgraph containing given Nodes.
	 * This means to return all nodes, that are en route between any
	 * choice of two nodes out of a given set.
	 *
	 * @param	abstractNode[]	$nodes
	 * @return	abstractNode[]
	 */
	public function reduceGraphTo(array $nodes);
}