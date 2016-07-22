<?php

namespace CaT\TableRelations\Graphs;
/**
 * Instead on relying on expensive graph search algorithms, 
 * we will cache graph information in path-objcets as we build it.
 * This means we will create for any node a set of paths starting
 * from it, and update them successive as we add edges.
 */
class Graph implements abstractGraph {

	protected $nodes = array();
	protected $edges = array();
	protected $connections = array();
	protected $subgraphs = array();
	protected $node_subgraph = array();
	protected $paths = array();

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
		if(isset($this->nodes[$node_id])) {
			throw new GraphException("$node_id allready in graph");
		}
		$this->nodes[$node_id] = $node;
		if(!isset($this->subgraphs[$subgraph_id])) {
			$this->subgraphs[$subgraph_id] = array();
		}
		$this->subgraphs[$subgraph_id][] = $node_id;
		$this->node_subgraph[$node_id] = $subgraph_id;
	}

	/**
	 * @inheritdoc
	 */
	public function connectNodesSymmetric(abstractEdge $edge) {
		$from_id = $edge->fromId();
		$to_id = $edge->toId();
		$this->connectNodeIdsDirected($from_id, $to_id, $edge);
		$this->connectNodeIdsDirected($to_id, $from_id, $edge);
		$this->edges[] = $edge;
	}

	/**
	 * @inheritdoc
	 */
	public function connectNodesDirected(abstractEdge $edge) {
		$from_id = $edge->fromId();
		$to_id = $edge->toId();
		$this->connectNodeIdsDirected($from_id, $to_id, $edge);
		$this->edges[] = $edge;
	}

	protected function connectNodeIdsDirected($from_id, $to_id, abstractEdge $edge) {
		if($from_id === $to_id) {
			throw new GraphException("cant't connect $from_id to itself");
		}
		if(!isset($this->nodes[$from_id]) || !isset($this->nodes[$to_id])) {
			throw new GraphException("$from_id or $to_id does not exist in graph");
		}

		$to_subgraph = $this->getSubgraphOfNodeId($to_id);
		if(isset($this->connections[$from_id][$to_subgraph][$to_id])) {
			throw new GraphException("$from_id and $to_id allready connected");
		}
		if(isset($this->connections[$from_id])) {
			if(isset($this->connections[$from_id][$to_subgraph])) {
				$this->connections[$from_id][$to_subgraph][$to_id] = $edge;
			} else {
				$this->connections[$from_id][$to_subgraph] = array($to_id => $edge);
			}
		}
		else {
			$this->connections[$from_id] = array($to_subgraph => array($to_id => $edge));
		}
		$this->updatePaths($from_id, $to_id, $to_subgraph);
	}

	public function nodeSubgraphId(abstractNode $node) {
		return $this->getSubgraphOfNodeId($node->id());
	}

	protected function updatePaths($from_id, $to_id, $to_subgraph) {
		if(!isset($this->paths[$from_id])) {
			$this->paths[$from_id] = array();
		}
		$updated_paths = array();
		$to_data = array("sg" => $to_subgraph);
		$from_data = array("sg" => $this->nodeSubgraphId($from_id));
		foreach ($this->paths as $path_start_id => $paths) {
			if($path_start_id === $from_id) {
				$new_path = Path::getInstanceByStartId($from_id,$from_data);
				$this->paths[$path_start_id][] 
					= $new_path;
				$updated_paths[$path_start_id][] = $new_path;
			} elseif($path_start_id === $to_id) {
				continue;
			} else {
				foreach ($paths as $path) {
					if($path->contains($from_id) && !$path->contains($to_id)) {
						if(!$path->endsAt($from_id)) {
							$this->paths[$path_start_id][] =
								$path->getSubgraphUpToIncluding($from_id);
						}
						$updated_paths[$path_start_id][] = $path;
					}
				}
			}

		}
		foreach ($updated_paths as $path_start_id => $paths) {
			foreach ($paths as $path) {
				$flag = 0;
				foreach($this->paths[$to_id] as $path_from_to) {
					if($flag = 0) {
						$path->appendUpTo($path_from_to);
						$this->paths[$path_start_id][] = $path;
					}
					$flag = 1;
				}
			}
		}
	}

	protected function getSubgraphOfNodeId($node_id) {
		return $this->node_subgraph[$node_id];
	}

	/**
	 * @inheritdoc
	 */
	public function getNodesBetween($from_id, $to_id) {
		$node_ids = array_unique($this->getNodeIdsBetween($from_id, $to_id));
		foreach ($node_ids as $node_id) {
			$return[] = $this->nodes[$node_id];
		}
		return $return;
	}

	protected function getNodeIdsBetween() {

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
		return array_values($this->nodes);
	}

	/**
	 * @inheritdoc
	 */
	public function edges() {
		return $this->edges;
	}

	/**
	 * Can $to_id be directly arrived from $from_id?
	 *
	 * @return bool
	 */
	protected function directedNeighbours($from_id, $to_id) {
		
	}
}