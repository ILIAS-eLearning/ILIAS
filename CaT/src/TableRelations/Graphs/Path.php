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

	public static function getInstanceByStartId($start_node_id, array $data) {
		$path = new Path;
		return $path->addNode($start_node_id, $data);
	}

	public static function getInstanceBySequence(array $sequence) {
		$path = new Path;
		foreach ($sequence as $node_id => $data) {
			$path->addNode($node_id, $data);
		}
		return $path;
	}


	public function addNode($node_id, array $data) {
		if(isset($this->sequence[$node_id])) {
			throw new GraphException("$node_id allready in path");
		}
		if($this->start_node === null) {
			$this->start_node = $node_id;
		}
		$this->sequence[$node_id] = $data;
		$this->end_node = $node_id;
		return $this;
	}

	public function pathEndsAt($node_id) {
		return $this->end_node === $node_id;
	}

	protected function addSequence(array $sequence) {
		$end_node = $this->end_node;
		foreach ($sequence as $node_id => $data) {
			assert('is_array($data)');
			$this->sequence[$node_id] = $data;
			$end_node = $node_id;
		}
		$this->end_node = $end_node;
	}

	public function getNodeData($node_id) {
		return $this->sequence[$node_id];
	}

	public function intersectsPathAt(Path $path) {
		$this_nodes = array_keys($this->sequence);
		$other_nodes = array_keys($path->sequence);
		if(count(array_intersect($this_nodes, $other_nodes)) > 0) {
			foreach ($this->sequence as $node_id => $stuff) {
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
		foreach ($path->sequence as $other_node_id => $data) {
			if($other_node_id === $node_id) {
				break;
			}
			$seq_to_add[$other_node_id] = $data;
		}
		$this->addSequence($seq_to_add);
		return $this;
	}

	public function getSubpathUpToIncluding($node_id) {
		if(!$this->contains($node_id)) {
			throw new GraphException("no $node_id in path");
		}
		$seq = array();
		foreach ($this->sequence as $this_node_id => $data) {
			$seq[$this_node_id] = $data;
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
		return $this->sequence;
	}
}