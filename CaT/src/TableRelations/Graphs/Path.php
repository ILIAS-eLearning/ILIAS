<?php

namespace CaT\TableRelations\Graphs;

/**
 * A path is a sequence of node ids. It must at least have a start-node.
 * A path is never self-crossing.
 * One may also add some metadata associated with a node.
 */
class Path {
	protected $sequence = array();
	protected $start_node = null;
	protected $end_node = null;

	public static function getInstanceByNode(abstractNode $start_node) {
		$path = new Path;
		return $path->addNode($start_node);
	}

	public static function getInstanceBySequence(array $sequence) {
		$path = new Path;
		foreach ($sequence as $node_id => $node) {
			$path->addNode($node);
		}
		return $path;
	}

	public function startNode() {
		return $this->sequence[$this->start_node];
	}

	public function endNode() {
		return $this->sequence[$this->end_node];
	}

	public function addNode(abstractNode $node) {
		$node_id = $node->id();
		if(isset($this->sequence[$node_id])) {
			throw new GraphException("$node_id allready in path");
		}
		$this->sequence[$node_id] = $node;
		if($this->start_node === null) {
			$this->start_node = $node_id;
		}
		$this->end_node = $node_id;
		return $this;
	}

	public function cloneAndAddNode(abstractNode $node) {
		return self::getInstanceBySequence($this->sequence)->addNode($node);
	}

	public function pathEndsAt($node_id) {
		return $this->end_node === $node_id;
	}

	protected function addSequence(array $sequence) {
		$end_node = $this->end_node;
		foreach ($sequence as $node_id => $node) {
			if(!$node instanceof abstractNode) {
				throw new GraphException("element $node_id of sequence not a node");
			}
			if(isset($this->sequence[$node_id])) {
				throw new GraphException("$node_id allready in path");				
			}
			$this->sequence[$node_id] = $node;
			$end_node = $node_id;
		}
		$this->end_node = $end_node;
	}

	public function intersectsPathAt(Path $path) {
		$this_nodes = array_keys($this->sequence);
		$other_nodes = array_keys($path->sequence);
		if(count(array_intersect($this_nodes, $other_nodes)) > 0) {
			foreach ($this_nodes as $node_id) {
				if(isset($path->sequence[$node_id])) {
					return $node_id;
				}
			}
		}
		return null;
	}

	public function append(Path $path) {
		if($this->intersectsPathAt($path) === null) {
			$this->addSequence($path->sequence);
		}
		return $this;
	}

	public function appendUpTo(Path $path, $node_id = null) {
		if($node_id === null) {
			$node_id = $this->intersectsPathAt($path);
		}
		$seq_to_add = array();
		foreach ($path->sequence as $other_node_id => $other_node) {
			if($other_node_id === $node_id) {
				break;
			}
			$seq_to_add[] = $other_node;
		}
		$this->addSequence($seq_to_add);
		return $this;
	}

	public function getSubpathUpToIncluding($node_id) {
		if(!$this->contains($node_id)) {
			throw new GraphException("no $node_id in path");
		}
		$seq = array();
		foreach ($this->sequence as $this_node_id => $node) {
			$seq[] = $node;
			if($this_node_id === $node_id) {
				break;
			}
		}
		return self::getInstanceBySequence($seq);
	}

	public function contains($node_id) {
		if(isset($this->sequence[$node_id])) {
			return true;
		}
		return false;
	}

	public function sequence() {
		return array_values($this->sequence);
	}
}