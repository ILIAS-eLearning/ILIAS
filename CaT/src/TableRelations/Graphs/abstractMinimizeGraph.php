<?php
namespace CaT\TableRelations\Graphs;

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
interface AbstractMinimizeGraph extends AbstractGraph {
	/**
	 * Get the smallest complete subgraph containing given Nodes.
	 * This means to return all nodes, that are en route between any
	 * choice of two nodes out of a given set.
	 *
	 * @param	AbstractNode[]	$nodes
	 * @return	AbstractGraph
	 */
	public function reduceGraphToNodes(array $nodes);
}
