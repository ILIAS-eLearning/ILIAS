<?php

namespace ILIAS\TMS\TableRelations\Graphs;

/**
 * A path is a sequence of node ids. It must at least have a start-node.
 * A path is never self-crossing.
 * One may also add some metadata associated with a node.
 */
class Path implements \Iterator {
	protected $sequence = array();
	protected $order = array();
	protected $start_node = null;
	protected $end_node = null;

	protected $current = 0;

	/**
	 * Iterator-functions
	 */

	public function valid() {
		return $this->current > 0 && $this->current <= count($this->sequence);
	}

	public function key() {
		return key($this->sequence);
	}

	public function current() {
		return current($this->sequence);
	}

	public function next() {
		$this->current++;
		return next($this->sequence);
	}

	public function rewind() {
		$this->current = count($this->sequence) > 0 ? 1 : 0;
		reset($this->sequence);
	}

	public function __construct() {
		$this->current = 0;
	}

	/**
	 * Get a path containing only one node, which is then the
	 * starting node ofc..
	 *
	 * @param	AbstractNode	$start_node
	 * @return	Path
	 */
	public static function getInstanceByNode(AbstractNode $start_node) {
		$path = new Path;
		return $path->addNode($start_node);
	}

	/**
	 * Get a path from a sequence of nodes,
	 *
	 * @param	AbstractNode[]	$sequence
	 * @return	Path
	 */
	public static function getInstanceBySequence(array $sequence) {
		$path = new Path;
		foreach ($sequence as $node_id => $node) {
			$path->addNode($node);
		}
		return $path;
	}

	/**
	 * Get the node at the start of the path.
	 *
	 * @return	AbstractNode
	 */
	public function startNode() {
		return $this->sequence[$this->start_node];
	}

	/**
	 * Get the node at the end of the path.
	 *
	 * @return	AbstractNode
	 */
	public function endNode() {
		return $this->sequence[$this->end_node];
	}

	/**
	 * Append a node to the end ot the path
	 *
	 * @param	AbstractNode	$node
	 * @return Path
	 */
	public function addNode(AbstractNode $node) {
		if($this->current === 0) {
			$this->current = 1;
		}
		$node_id = $node->id();
		if(isset($this->sequence[$node_id])) {
			throw new GraphException("$node_id allready in path");
		}
		$this->sequence[$node_id] = $node;
		$this->order[$node_id] = count($this->sequence);
		if($this->start_node === null) {
			$this->start_node = $node_id;
		}
		$this->end_node = $node_id;
		return $this;
	}

	/**
	 * Append a node to the end ot the path and clone
	 *
	 * @param	AbstractNode	$node
	 * @return	Path
	 */
	public function cloneAndAddNode(AbstractNode $node) {
		return self::getInstanceBySequence($this->sequence)->addNode($node);
	}

	/**
	 * Does th path end at a node having a certain id?
	 *
	 * @param	int|string	$node
	 * @return	bool
	 */
	public function pathEndsAt($node_id) {
		return $this->end_node === $node_id;
	}

	/**
	 * Append a sequence to the end of the path
	 *
	 * @param	AbstractNode[]	$sequence
	 * @return	Path
	 */
	protected function addSequence(array $sequence) {
		foreach ($sequence as $node) {
			$this->addNode($node);
		}
	}

	/**
	 * At which node does this path intersect another?
	 *
	 * @param	Path	$path
	 * @return	int|string|null
	 */
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

	/**
	 * Append a path to the end of the path, if there are no intersections.
	 *
	 * @param	Path	$path
	 * @return	Path
	 */
	public function append(Path $path) {
		if($this->intersectsPathAt($path) === null) {
			$this->addSequence($path->sequence);
		}
		return $this;
	}

	/**
	 * Append a path to the end of the path up to some node or intersection.
	 *
	 * @param	Path	$path
	 * @param	int|string|null	$node_id
	 * @return	Path
	 */
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

	/**
	 * Get the subpath of this path up to some node id.
	 *
	 * @param	int|string|null	$node_id
	 * @return	Path
	 */
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

	/** 
	 * Does this path contain a node with some node_id
	 *
	 * @param	int|string	$node_id
	 * @return	bool
	 */
	public function contains($node_id) {
		if(isset($this->sequence[$node_id])) {
			return true;
		}
		return false;
	}

	/** 
	 * Get the sequence of this path.
	 *
	 * @return	AbstractNode[]
	 */
	public function sequence() {
		return array_values($this->sequence);
	}

	/** 
	 * Get the position of some node id.
	 *
	 * @param	int|string	$node_id
	 * @return	int
	 */
	public function positionOf($node_id) {
		return isset($this->order[$node_id]) ? $this->order[$node_id] : 0;
	}

	/** 
	 * Insert a node after a node having some node id
	 *
	 * @param	int|string	$node_id
	 * @param	AbstractNode	$node
	 * @return	Path
	 */
	public function insertAfter($node_id, AbstractNode $node) {
		if(!$this->contains($node_id)) {
			throw new GraphException("cant insert after nonexistent node $node_id");
		}
		$sequence = $this->sequence;
		$this->sequence = array();
		$this->order = array();
		$this->start_node = null;
		$this->end_node = null;
		foreach ($sequence as $i_node_id => $i_node) {
			$this->addNode($i_node);
			if($i_node_id === $node_id) {
				$this->addNode($node);
			}
		}
		return $this;
	}
}
