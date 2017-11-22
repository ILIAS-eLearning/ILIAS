<?php

namespace ILIAS\TMS\TableRelations\Graphs;
/**
 * We are using depth first search , to get infos about graph.
 * These will be represented by a collection of paths, which are 
 * sequences of nodes, that may be not self crossing (can not
 * contain the same node more than once).
 */
class Graph implements AbstractGraph {

	protected $nodes = array();
	protected $edges = array();
	public $connections = array();
	protected $node_subgraph = array();
	protected $paths = array();

	/**
	 * @inheritdoc
	 */
	public function addNode(AbstractNode $node) {
		$node_id = $node->id();
		$subgraph_id = $node->subgraph();
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
	public function connectNodesSymmetric(AbstractEdge $edge) {
		$from_id = $edge->fromId();
		$to_id = $edge->toId();
		if(!$this->connectionSymmetricFits($edge)) {
			throw new GraphException("unfit symmetric connection with the flow rule, $from_id to $to_id");
		}
		$this->connectNodeIdsDirected($from_id, $to_id, $edge);
		$this->connectNodeIdsDirected($to_id, $from_id, $edge);
		$this->edges[] = $edge;
	}

	/**
	 * @inheritdoc
	 */
	public function connectNodesDirected(AbstractEdge $edge) {
		$from_id = $edge->fromId();
		$to_id = $edge->toId();
		if(!$this->connectionDirectedFits($edge)) {
			throw new GraphException("unfit directed connection with the flow rule, $from_id to $to_id");
		}
		$this->connectNodeIdsDirected($from_id, $to_id, $edge);
		$this->edges[] = $edge;
	}

	protected function connectionDirectedFits(AbstractEdge $edge) {
		$from_id = $edge->fromId();
		$to_id = $edge->toId();
		return $this->checkNoCurrentSymmetricConnections($to_id);
	}

	protected function connectionSymmetricFits(AbstractEdge $edge) {
		$from_id = $edge->fromId();
		$to_id = $edge->toId();
		return $this->checkNoIncommingOnlyConnections($from_id) && $this->checkNoIncommingOnlyConnections($to_id);
	}


	protected function checkNoCurrentSymmetricConnections($node_id) {
		foreach ($this->neighbours($node_id) as $neighbour_id) {
			if($this->edgeBetween($neighbour_id, $node_id)) {
				return false;
			}
		}
		return true;
	}

	protected function checkNoIncommingOnlyConnections($node_id) {
		$neighbours_from = $this->neighbours($node_id);
		$neighbours_to = array();
		foreach ($this->connections as $a_node_id => $s_g_connections) {
			foreach ($s_g_connections as $s_g => $to_ids) {
				if(isset($to_ids[$node_id])) {
					$neighbours_to[] = $a_node_id;
				}
			}
		}
		if(count(array_diff($neighbours_to,$neighbours_from))>0) {
			return false;
		}
		return true;
	}


	protected function connectNodeIdsDirected($from_id, $to_id, AbstractEdge $edge) {
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
	}

	/**
	 * @inheritdoc
	 */
	public function nodeSubgraphId(AbstractNode $node) {
		return $this->getSubgraphOfNodeId($node->id());
	}

	protected function getSubgraphOfNodeId($node_id) {
		return $this->nodes[$node_id]->subgraph();
	}

	/**
	 * @inheritdoc
	 */
	public function getNodesBetween($from_id, $to_id, $subgraph = null) {
		$return = array();
		foreach ($this->DepthFirstSearch($from_id, $to_id, $subgraph) as $path) {
			foreach ($path->sequence() as $node) {
				$return[$node->id()] = $node;
			}
		}
		return array_values($return);
	}

	/**
	 * @inheritdoc
	 */
	public function getPathsBetween($from_id, $to_id, $sg = null) {
		return $this->DepthFirstSearch($from_id, $to_id, $sg);
	}

	/**
	 * @inheritdoc
	 */
	public function getNodesWithinSubgraphBetween($from_id, $to_id, $subgraph) {
		return $this->getNodesBetween($from_id, $to_id, $subgraph);
	}

	/**
	 * @inheritdoc
	 */
	public function connected($from_id, $to_id) {
		return count($this->DepthFirstSearch($from_id, $to_id)) > 0;
	}

	/**
	 * @inheritdoc
	 */
	public function nodes() {
		return $this->nodes;
	}

	/**
	 * @inheritdoc
	 */
	public function edges() {
		return $this->edges;
	}

	/**
	 * @inheritdoc
	 */
	public function edgeBetween($from_id, $to_id) {
		if(isset($this->connections[$from_id])) {
			foreach ($this->connections[$from_id] as $subgraph => $edges) {
				if(isset($edges[$to_id])) {
					return $edges[$to_id];
				}
			}
		}
		return null;
	}

	/**
	 * Can $to_id be directly arrived from $from_id?
	 *
	 * @return string[]
	 */
	protected function neighbours($from_id, $subgraph = null) {
		if(!isset($this->connections[$from_id])) {
			return array();
		}
		$connections = $this->connections[$from_id];
		if($subgraph !== null) {
			return $connections[$subgraph] ? array_keys($connections[$subgraph]) : array();
		} else {
			$return = array();
			foreach ($connections as $s_g => $s_g_connections) {
				$return = array_merge($return, array_keys($s_g_connections));
			}
			return $return;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getNodeById($id) {
		return isset($this->nodes[$id]) ? $this->nodes[$id] : null;
	}

	protected function DepthFirstSearch($from_id, $to_id, $subgraph = null) {
		$node_paths = array(Path::getInstanceByNode($this->nodes[$from_id]));
		$return_paths = array();
		while( $path = array_shift($node_paths)) {
			$path_head = $path->endNode();
			if($path_head->id() === $to_id) {
				array_push($return_paths, $path);
				continue;
			}
			$neighbours = $this->neighbours($path_head->id(), $subgraph);
			foreach ($neighbours as $neighbour_id) {
				if(!$path->contains($neighbour_id)) {
					$new_path = $path->cloneAndAddNode($this->nodes[$neighbour_id]);
					array_push($node_paths, $new_path);
				}
			}
		}
		return $return_paths;
	}
}
