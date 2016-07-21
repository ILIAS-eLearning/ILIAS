<?php

namespace \CaT\TableRelations\Graphs;

class Graph implements abstractGraph {

	protected $nodes = array();
	protected $node_ids = array();
	protected $edges = array();
	protected $connections = array();
	protected $subgraphs = array();

	/**
	 * @inheritdoc
	 */
	public function addGraph(abstractGraph $graph) {

	}

	/**
	 * @inheritdoc
	 */
	public function addNode(abstractNode $node, $subgraph_id = 0) {
		$node_id = $node->id();
		if(in_array($node_id, $this->node_ids)) {
			throw new GraphException("$node_id allready in graph");
		}
		$this->nodes[] = $node;
		$this->node_ids[] = $node_id;
		if(!isset($this->subgraphs[$subgraph_id])) {
			$this->subgraphs[$subgraph_id] = array();
		}
		$subgraphs[$subgraph_id] = $node_id;
	}

	/**
	 * @inheritdoc
	 */
	public function connectNodesSymmetric(abstractEdge $edge) {

	}

	/**
	 * @inheritdoc
	 */
	public function connectNodesDirected(abstractEdge $edge) {

	}

	/**
	 * @inheritdoc
	 */
	public function getNodesBetween($from_id, $to_id) {

	}

	/**
	 * @inheritdoc
	 */
	public function getNodesWithinSubgraphBetween($from_id, $to_id, $subgraph_id = 0) {

	}

	/**
	 * @inheritdoc
	 */
	public function isConnected() {

	}

	/**
	 * @inheritdoc
	 */
	public function nodes() {

	}

	/**
	 * @inheritdoc
	 */
	public function edges() {

	}
}