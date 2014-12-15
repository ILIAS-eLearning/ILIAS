<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tree/interfaces/interface.ilTreeImplementation.php';

/**
 * Base class for nested set path based trees
 * 
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$

 * @ingroup ServicesTree
 * 
 */
class ilNestedSetTree implements ilTreeImplementation
{
	private $tree = NULL;
	
	/**
	 * Constructor
	 * @param ilTree $tree
	 */
	public function __construct(ilTree $a_tree)
	{
		$this->tree = $a_tree;
	}

	/**
	 * Get tree object
	 * @return ilTree $tree
	 */
	public function getTree()
	{
		return $this->tree;
	}
	
	/**
	 * Get subtree ids
	 * @param type $a_node_id
	 */
	public function getSubTreeIds($a_node_id)
	{
		global $ilDB;
		
		$query = 'SELECT s.child FROM '.
			$this->getTree()->getTreeTable().' s, '.
			$this->getTree()->getTreeTable().' t '. 
			'WHERE t.child = %s '.
			'AND s.lft > t.lft '.
			'AND s.rgt < t.rgt '.
			'AND s.'.$this->getTree()->getTreePk().' = %s';
		
		$res = $ilDB->queryF(
			$query, 
			array('integer','integer'),
			array($a_node_id,$this->getTree()->getTreeId())
		);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$childs[] = $row->child;
		}
		return $childs ? $childs : array();
	}
	
	/**
	 * Get subtree
	 * @param type $a_node
	 * @param type $a_with_data
	 * @param type $a_types
	 */
	public function getSubTreeQuery($a_node, $a_types = '', $a_force_join_reference = true, $a_fields = array())
	{
		global $ilDB;
		
		$type_str = '';
		if (is_array($a_types))
		{
			if($a_types)
			{
				$type_str = "AND ".$ilDB->in($this->getTree()->getObjectDataTable().".type", $a_types, false, "text");
			}
		}
		else if(strlen($a_types))
		{
			$type_str = "AND ".$this->getTree()->getObjectDataTable().".type = ".$ilDB->quote($a_types, "text");
		}
		
		$join = '';
		if($type_str or $a_force_join_reference)
		{
			$join = $this->getTree()->buildJoin();
		}
		
		$fields = '* ';
		if(count($a_fields))
		{
			$fields = implode(',',$a_fields);
		}

		$query = 'SELECT '.
			$fields.' '.
			"FROM ".$this->getTree()->getTreeTable()." ".
			$join.' '.
			"WHERE ".$this->getTree()->getTreeTable().'.lft '.
			'BETWEEN '.$ilDB->quote($a_node['lft'],'integer').' '.
			'AND '.$ilDB->quote($a_node['rgt'],'integer').' '.
			"AND ".$this->getTree()->getTreeTable().".".$this->getTree()->getTreePk()." = ".$ilDB->quote($this->getTree()->getTreeId(),'integer').' '.
			$type_str.' '.
			"ORDER BY ".$this->getTree()->getTreeTable().".lft";
		
		#$GLOBALS['ilLog']->write(__METHOD__.'-----------------: '. $query);
		
		return $query;
	}
	

	/**
	 * Get relation
	 * @param type $a_node_a
	 * @param type $a_node_b
	 */
	public function getRelation($a_node_a, $a_node_b)
	{
		if($a_node_a['child'] == $a_node_b['child'])
		{
			$GLOBALS['ilLog']->write(__METHOD__.': EQUALS');
			return ilTree::RELATION_EQUALS;
		}
		if($a_node_a['lft'] < $a_node_b['lft'] and $a_node_a['rgt'] > $a_node_b['rgt'])
		{
			$GLOBALS['ilLog']->write(__METHOD__.': PARENT');
			return ilTree::RELATION_PARENT;
		}
		if($a_node_b['lft'] < $a_node_a['lft'] and $a_node_b['rgt'] > $a_node_a['rgt'])
		{
			$GLOBALS['ilLog']->write(__METHOD__.': CHILD');
			return ilTree::RELATION_CHILD;
		}
		
		// if node is also parent of node b => sibling
		if($a_node_a['parent'] == $a_node_b['parent'])
		{
			$GLOBALS['ilLog']->write(__METHOD__.': SIBLING');
			return ilTree::RELATION_SIBLING;
		}
		$GLOBALS['ilLog']->write(__METHOD__.': NONE');
		return ilTree::RELATION_NONE;
	}
	
	/**
	 * Get path ids
	 * @param int $a_endnode
	 * @param int $a_startnode
	 */
	public function getPathIds($a_endnode, $a_startnode = 0)
	{
		return $this->getPathIdsUsingAdjacencyMap($a_endnode, $a_startnode);
	}
	
	/**
	 * Insert tree node
	 * @param type $a_node_id
	 * @param type $a_parent_id
	 * @param type $a_pos
	 */
	public function insertNode($a_node_id, $a_parent_id, $a_pos)
	{
		global $ilDB;

		// LOCKED ###############################
		if($this->getTree()->__isMainTree())
		{
			$ilDB->lockTables(
				array(
					0 => array('name' => 'tree', 'type' => ilDB::LOCK_WRITE)));
		}
		switch ($a_pos)
		{
			case ilTree::POS_FIRST_NODE:

				// get left value of parent
				$query = sprintf('SELECT * FROM '.$this->getTree()->getTreeTable().' '.
					'WHERE child = %s '.
					'AND '.$this->getTree()->getTreePk().' = %s ',
					$ilDB->quote($a_parent_id,'integer'),
					$ilDB->quote($this->getTree()->getTreeId(),'integer'));
				
				$res = $ilDB->query($query);
				$r = $ilDB->fetchObject($res);

				if ($r->parent == NULL)
				{
					if($this->getTree()->__isMainTree())
					{
						$ilDB->unlockTables();
					}
					$GLOBALS['ilLog']->logStack();
					throw new ilInvalidTreeStructureException('Parent with id '. $a_parent_id.' not found in tree');
				}

				$left = $r->lft;
				$lft = $left + 1;
				$rgt = $left + 2;

				// spread tree
				$query = sprintf('UPDATE '.$this->getTree()->getTreeTable().' SET '.
					'lft = CASE WHEN lft > %s THEN lft + 2 ELSE lft END, '.
					'rgt = CASE WHEN rgt > %s THEN rgt + 2 ELSE rgt END '.
					'WHERE '.$this->getTree()->getTreePk().' = %s ',
					$ilDB->quote($left,'integer'),
					$ilDB->quote($left,'integer'),
					$ilDB->quote($this->getTree()->getTreeId(),'integer'));
				$res = $ilDB->manipulate($query);
				break;

			case IL_LAST_NODE:
				// Special treatment for trees with gaps
				if ($this->getTree()->getGap() > 0)
				{
					// get lft and rgt value of parent
					$query = sprintf('SELECT rgt,lft,parent FROM '.$this->getTree()->getTreeTable().' '.
						'WHERE child = %s '.
						'AND '.$this->getTree()->getTreePk().' =  %s',
						$ilDB->quote($a_parent_id,'integer'),
						$ilDB->quote($this->getTree()->getTreeId(),'integer'));
					$res = $ilDB->query($query);
					$r = $ilDB->fetchAssoc($res);

					if ($r['parent'] == null)
					{
						if($this->getTree()->__isMainTree())
						{
							$ilDB->unlockTables();
						}
						$GLOBALS['ilLog']->logStack();
						throw new ilInvalidTreeStructureException('Parent with id '. $a_parent_id.' not found in tree');
					}
					$parentRgt = $r['rgt'];
					$parentLft = $r['lft'];
					
					// Get the available space, without taking children into account yet
					$availableSpace = $parentRgt - $parentLft;
					if ($availableSpace < 2)
					{
						// If there is not enough space between parent lft and rgt, we don't need
						// to look any further, because we must spread the tree.
						$lft = $parentRgt;
					}
					else
					{
						// If there is space between parent lft and rgt, we need to check
						// whether there is space left between the rightmost child of the
						// parent and parent rgt.
						$query = sprintf('SELECT MAX(rgt) max_rgt FROM '.$this->getTree()->getTreeTable().' '.
							'WHERE parent = %s '.
							'AND '.$this->getTree()->getTreePk().' = %s',
							$ilDB->quote($a_parent_id,'integer'),
							$ilDB->quote($this->getTree()->getTreeId(),'integer'));
						$res = $ilDB->query($query);
						$r = $ilDB->fetchAssoc($res);

						if (isset($r['max_rgt']))
						{
							// If the parent has children, we compute the available space
							// between rgt of the rightmost child and parent rgt.
							$availableSpace = $parentRgt - $r['max_rgt'];
							$lft = $r['max_rgt'] + 1;
						}
						else
						{
							// If the parent has no children, we know now, that we can
							// add the new node at parent lft + 1 without having to spread
							// the tree.
							$lft = $parentLft + 1;
						}
					}
					$rgt = $lft + 1;
					

					// spread tree if there is not enough space to insert the new node
					if ($availableSpace < 2)
					{
						//$this->log->write('ilTree.insertNode('.$a_node_id.','.$a_parent_id.') creating gap at '.$a_parent_id.' '.$parentLft.'..'.$parentRgt.'+'.(2 + $this->gap * 2));
						$query = sprintf('UPDATE '.$this->getTree()->getTreeTable().' SET '.
							'lft = CASE WHEN lft  > %s THEN lft + %s ELSE lft END, '.
							'rgt = CASE WHEN rgt >= %s THEN rgt + %s ELSE rgt END '.
							'WHERE '.$this->getTree()->getTreePk().' = %s ',
							$ilDB->quote($parentRgt,'integer'),
							$ilDB->quote((2 + $this->getTree()->getGap() * 2),'integer'),
							$ilDB->quote($parentRgt,'integer'),
							$ilDB->quote((2 + $this->getTree()->getGap() * 2),'integer'),
							$ilDB->quote($this->getTree()->getTreeId(),'integer'));
						$res = $ilDB->manipulate($query);
					}
				}
				// Treatment for trees without gaps
				else 
				{

					// get right value of parent
					$query = sprintf('SELECT * FROM '.$this->getTree()->getTreeTable().' '.
						'WHERE child = %s '.
						'AND '.$this->getTree()->getTreePk().' = %s ',
						$ilDB->quote($a_parent_id,'integer'),
						$ilDB->quote($this->getTree()->getTreeId(),'integer'));
					$res = $ilDB->query($query);
					$r = $ilDB->fetchObject($res);

					if ($r->parent == null)
					{
						if($this->getTree()->__isMainTree())
						{
							$ilDB->unlockTables();
						}
						$GLOBALS['ilLog']->logStack();
						throw new ilInvalidTreeStructureException('Parent with id '. $a_parent_id.' not found in tree');
					}

					$right = $r->rgt;
					$lft = $right;
					$rgt = $right + 1;

					// spread tree
					$query = sprintf('UPDATE '.$this->getTree()->getTreeTable().' SET '.
						'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, '.
						'rgt = CASE WHEN rgt >= %s THEN rgt + 2 ELSE rgt END '.
						'WHERE '.$this->getTree()->getTreePk().' = %s',
						$ilDB->quote($right,'integer'),
						$ilDB->quote($right,'integer'),
						$ilDB->quote($this->getTree()->getTreeId(),'integer'));
					$res = $ilDB->manipulate($query);
				}

				break;

			default:

				// get right value of preceeding child
				$query = sprintf('SELECT * FROM '.$this->getTree()->getTreeTable().' '.
					'WHERE child = %s '.
					'AND '.$this->getTree()->getTreePk().' = %s ',
					$ilDB->quote($a_pos,'integer'),
					$ilDB->quote($this->getTree()->getTreeId(),'integer'));
				$res = $ilDB->query($query);
				$r = $ilDB->fetchObject($res);

				// crosscheck parents of sibling and new node (must be identical)
				if ($r->parent != $a_parent_id)
				{
					if($this->getTree()->__isMainTree())
					{
						$ilDB->unlockTables();
					}
					$GLOBALS['ilLog']->logStack();
					throw new ilInvalidTreeStructureException('Parent with id '. $a_parent_id.' not found in tree');
				}

				$right = $r->rgt;
				$lft = $right + 1;
				$rgt = $right + 2;

				// update lft/rgt values
				$query = sprintf('UPDATE '.$this->getTree()->getTreeTable().' SET '.
					'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, '.
					'rgt = CASE WHEN rgt >  %s THEN rgt + 2 ELSE rgt END '.
					'WHERE '.$this->getTree()->getTreePk().' = %s',
					$ilDB->quote($right,'integer'),
					$ilDB->quote($right,'integer'),
					$ilDB->quote($this->getTree()->getTreeId(),'integer'));
				$res = $ilDB->manipulate($query);
				break;

		}

		// get depth
		$depth = $this->getTree()->getDepth($a_parent_id) + 1;

		// insert node
		$query = sprintf('INSERT INTO '.$this->getTree()->getTreeTable().' ('.$this->getTree()->getTreePk().',child,parent,lft,rgt,depth) '.
			'VALUES (%s,%s,%s,%s,%s,%s)',
			$ilDB->quote($this->getTree()->getTreeId(),'integer'),
			$ilDB->quote($a_node_id,'integer'),
			$ilDB->quote($a_parent_id,'integer'),
			$ilDB->quote($lft,'integer'),
			$ilDB->quote($rgt,'integer'),
			$ilDB->quote($depth,'integer'));
		$res = $ilDB->manipulate($query);

		// Finally unlock tables and update cache
		if($this->getTree()->__isMainTree())
		{
			$ilDB->unlockTables();
		}
	}


	/**
	 * Delete a subtree
	 * @param type $a_node_id
	 */
	public function deleteTree($a_node_id)
	{
		global $ilDB;
		
		// LOCKED ###########################################################
		// get lft and rgt values. Don't trust parameter lft/rgt values of $a_node
		if($this->getTree()->__isMainTree())
		{
			$ilDB->lockTables(
				array(
					0 => array('name' => 'tree', 'type' => ilDB::LOCK_WRITE)));
		}

		// Fetch lft, rgt directly (without fetchNodeData) to avoid unnecessary table locks
		// (object_reference, object_data)
		$query = 'SELECT *  FROM '.$this->getTree()->getTreeTable().' '.
				'WHERE child = '.$ilDB->quote($a_node_id,'integer').' '.
				'AND '.$this->getTree()->getTreePk().' = '.$ilDB->quote($this->getTree()->getTreeId(),'integer');
		$res = $ilDB->query($query);
		$a_node = $res->fetchRow(DB_FETCHMODE_ASSOC);
		
		// delete subtree
		$query = sprintf('DELETE FROM '.$this->getTree()->getTreeTable().' '.
			'WHERE lft BETWEEN %s AND %s '.
			'AND rgt BETWEEN %s AND %s '.
			'AND '.$this->getTree()->getTreePk().' = %s',
			$ilDB->quote($a_node['lft'],'integer'),
			$ilDB->quote($a_node['rgt'],'integer'),
			$ilDB->quote($a_node['lft'],'integer'),
			$ilDB->quote($a_node['rgt'],'integer'),
			$ilDB->quote($a_node[$this->getTree()->getTreePk()],'integer'));
		$res = $ilDB->manipulate($query);
			
        // Performance improvement: We only close the gap, if the node 
        // is not in a trash tree, and if the resulting gap will be 
        // larger than twice the gap value 

		$diff = $a_node["rgt"] - $a_node["lft"] + 1;
		if(
			$a_node[$this->getTree()->getTreePk()] >= 0 && 
			$a_node['rgt'] - $a_node['lft'] >= $this->getTree()->getGap() * 2
		)
		{
			// close gaps
			$query = sprintf('UPDATE '.$this->getTree()->getTreeTable().' SET '.
				'lft = CASE WHEN lft > %s THEN lft - %s ELSE lft END, '.
				'rgt = CASE WHEN rgt > %s THEN rgt - %s ELSE rgt END '.
				'WHERE '.$this->getTree()->getTreePk().' = %s ',
				$ilDB->quote($a_node['lft'],'integer'),
				$ilDB->quote($diff,'integer'),
				$ilDB->quote($a_node['lft'],'integer'),
				$ilDB->quote($diff,'integer'),
				$ilDB->quote($a_node[$this->getTree()->getTreePk()],'integer'));
				
			$res = $ilDB->manipulate($query);
		}

		if($this->getTree()->__isMainTree())
		{
			$ilDB->unlockTables();
		}
		// LOCKED ###########################################################
		return true;
	}
	
	/**
	 * Move to trash
	 * @param type $a_node_id
	 * 
	 * @todo lock table
	 */
	public function moveToTrash($a_node_id)
	{
		global $ilDB;

		$node = $this->getTree()->getNodeTreeData($a_node_id);

		$query = 'UPDATE '.$this->getTree()->getTreeTable().' '.
			'SET tree = '.$ilDB->quote(-1 * $node['child'],'integer').' '.
			'WHERE '.$this->getTree()->getTreePk().' =  '.$ilDB->quote($this->getTree()->getTreeId(),'integer').' '.
			'AND lft BETWEEN '.$ilDB->quote($node['lft'],'integer').' AND '.$ilDB->quote($node['rgt'],'integer').' ';

		$ilDB->manipulate($query);
		return true;
	}


	/**
	* get path from a given startnode to a given endnode
	* if startnode is not given the rootnode is startnode
	* @access	public
	* @param	integer		node_id of endnode
	* @param	integer		node_id of startnode (optional)
	* @return	array		all path ids from startnode to endnode
	*/
	protected function getPathIdsUsingAdjacencyMap($a_endnode_id, $a_startnode_id = 0)
	{
		global $ilDB;

		// The adjacency map algorithm is harder to implement than the nested sets algorithm.
		// This algorithms performs an index search for each of the path element.
		// This algorithms performs well for large trees which are not deeply nested.

		// The $takeId variable is used, to determine if a given id shall be included in the path
		$takeId = $a_startnode_id == 0;
		
		$depth_cache = $this->getTree()->getDepthCache();
		$parent_cache = $this->getTree()->getParentCache();
		
		if(
			$this->getTree()->__isMainTree() && 
			isset($depth_cache[$a_endnode_id]) &&
			isset($parent_cache[$a_endnode_id]))
		{
			$nodeDepth = $depth_cache[$a_endnode_id];
			$parentId = $parent_cache[$a_endnode_id];
		}
		else
		{
			$nodeDepth = $this->getTree()->getDepth($a_endnode_id);
			$parentId = $this->getTree()->getParentId($a_endnode_id);
		}

		// Fetch the node ids. For shallow depths we can fill in the id's directly.	
		$pathIds = array();
		
		// backward compatible check for nodes not in tree
		if(!$nodeDepth )
		{
			return array();
		}
		else if ($nodeDepth == 1)
		{
				$takeId = $takeId || $a_endnode_id == $a_startnode_id;
				if ($takeId) $pathIds[] = $a_endnode_id;
		}
		else if ($nodeDepth == 2)
		{
				$takeId = $takeId || $parentId == $a_startnode_id;
				if ($takeId) $pathIds[] = $parentId;
				$takeId = $takeId || $a_endnode_id == $a_startnode_id;
				if ($takeId) $pathIds[] = $a_endnode_id;
		}
		else if ($nodeDepth == 3)
		{
				$takeId = $takeId || $this->getTree()->getRootId() == $a_startnode_id;
				if ($takeId) $pathIds[] = $this->getTree()->getRootId();
				$takeId = $takeId || $parentId == $a_startnode_id;
				if ($takeId) $pathIds[] = $parentId;
				$takeId = $takeId || $a_endnode_id == $a_startnode_id;
				if ($takeId) $pathIds[] = $a_endnode_id;
		}
		else if ($nodeDepth < 32)
		{
			// Adjacency Map Tree performs better than
			// Nested Sets Tree even for very deep trees.
			// The following code construct nested self-joins
			// Since we already know the root-id of the tree and
			// we also know the id and parent id of the current node,
			// we only need to perform $nodeDepth - 3 self-joins. 
			// We can further reduce the number of self-joins by 1
			// by taking into account, that each row in table tree
			// contains the id of itself and of its parent.
			$qSelect = 't1.child c0';
			$qJoin = '';
			for ($i = 1; $i < $nodeDepth - 2; $i++)
			{
				$qSelect .= ', t'.$i.'.parent c'.$i;
				$qJoin .= ' JOIN '.$this->getTree()->getTreeTable().' t'.$i.' ON '.
							't'.$i.'.child=t'.($i - 1).'.parent AND '.
							't'.$i.'.'.$this->getTree()->getTreePk().' = '.(int) $this->getTree()->getTreeId();
			}
			
			$types = array('integer','integer');
			$data = array($this->getTree()->getTreeId(),$parentId);
			$query = 'SELECT '.$qSelect.' '.
				'FROM '.$this->getTree()->getTreeTable().' t0 '.$qJoin.' '.
				'WHERE t0.'.$this->getTree()->getTreePk().' = %s '.
				'AND t0.child = %s ';
				
			$ilDB->setLimit(1);
			$res = $ilDB->queryF($query,$types,$data);

			if ($res->numRows() == 0)
			{
				return array();
			}
			
			$row = $ilDB->fetchAssoc($res);
			
			$takeId = $takeId || $this->getTree()->getRootId() == $a_startnode_id;
			if ($takeId) $pathIds[] = $this->getTree()->getRootId();
			for ($i = $nodeDepth - 4; $i >=0; $i--)
			{
				$takeId = $takeId || $row['c'.$i] == $a_startnode_id;
				if ($takeId) $pathIds[] = $row['c'.$i];
			}
			$takeId = $takeId || $parentId == $a_startnode_id;
			if ($takeId) $pathIds[] = $parentId;
			$takeId = $takeId || $a_endnode_id == $a_startnode_id;
			if ($takeId) $pathIds[] = $a_endnode_id;
		}
		else
		{
			// Fall back to nested sets tree for extremely deep tree structures
			return $this->getPathIdsUsingNestedSets($a_endnode_id, $a_startnode_id);
		}
		return $pathIds;
	}
	
	/**
	* get path from a given startnode to a given endnode
	* if startnode is not given the rootnode is startnode
	* @access	public
	* @param	integer		node_id of endnode
	* @param	integer		node_id of startnode (optional)
	* @return	array		all path ids from startnode to endnode
	*/
	public function getPathIdsUsingNestedSets($a_endnode_id, $a_startnode_id = 0)
	{
		global $ilDB;
		
		// The nested sets algorithm is very easy to implement.
		// Unfortunately it always does a full table space scan to retrieve the path
		// regardless whether indices on lft and rgt are set or not.
		// (At least, this is what happens on MySQL 4.1).
		// This algorithms performs well for small trees which are deeply nested.
		
		$fields = array('integer','integer','integer');
		$data = array($a_endnode_id,$this->getTree()->getTreeId(),$this->getTree()->getTreeId());
		
		$query = "SELECT T2.child ".
			"FROM ".$this->getTree()->getTreeTable()." T1, ".$this->getTree()->getTreeTable()." T2 ".
			"WHERE T1.child = %s ".
			"AND T1.lft BETWEEN T2.lft AND T2.rgt ".
			"AND T1.".$this->getTree()->getTreePk()." = %s ".
			"AND T2.".$this->getTree()->getTreePk()." = %s ".
			"ORDER BY T2.depth";

		$res = $ilDB->queryF($query,$fields,$data);
		
		$takeId = $a_startnode_id == 0;
		while($row = $ilDB->fetchAssoc($res))
		{
			if ($takeId || $row['child'] == $a_startnode_id)
			{
				$takeId = true;
				$pathIds[] = $row['child'];
			}
		}
		return $pathIds ? $pathIds : array();
	}
	
	
	/**
	 * Move source subtree to target 
	 * @param type $a_source_id
	 * @param type $a_target_id
	 * @param type $a_position
	 */
	public function moveTree($a_source_id, $a_target_id, $a_position)
	{
		global $ilDB;

		if ($this->getTree()->__isMainTree())
		{
			$ilDB->lockTables(
					array(
						0 => array('name' => 'tree', 'type' => ilDB::LOCK_WRITE)));
		}
		// Receive node infos for source and target
		$query = 'SELECT * FROM ' . $this->getTree()->getTreeTable() . ' ' .
				'WHERE ( child = %s OR child = %s ) ' .
				'AND ' . $this->getTree()->getTreePk() . ' = %s ';
		$res = $ilDB->queryF($query, array('integer', 'integer', 'integer'), array(
			$a_source_id,
			$a_target_id,
			$this->getTree()->getTreeId()));

		// Check in tree
		if ($res->numRows() != 2)
		{
			if ($this->getTree()->__isMainTree())
			{
				$ilDB->unlockTables();
			}
			$GLOBALS['ilLog']->logStack();
			$GLOBALS['ilLog']->write(__METHOD__.': Objects not found in tree');
			throw new InvalidArgumentException('Error moving subtree');
		}
		while ($row = $ilDB->fetchObject($res))
		{
			if ($row->child == $a_source_id)
			{
				$source_lft = $row->lft;
				$source_rgt = $row->rgt;
				$source_depth = $row->depth;
				$source_parent = $row->parent;
			}
			else
			{
				$target_lft = $row->lft;
				$target_rgt = $row->rgt;
				$target_depth = $row->depth;
			}
		}

		// Check target not child of source
		if ($target_lft >= $source_lft and $target_rgt <= $source_rgt)
		{
			if ($this->getTree()->__isMainTree())
			{
				$ilDB->unlockTables();
			}
			$GLOBALS['ilLog']->logStack();
			$GLOBALS['ilLog']->write(__METHOD__.': Target is child of source');
			throw new ilInvalidArgumentException('Error moving subtree: target is child of source');
		}

		// Now spread the tree at the target location. After this update the table should be still in a consistent state.
		// implementation for IL_LAST_NODE
		$spread_diff = $source_rgt - $source_lft + 1;
		#var_dump("<pre>","SPREAD_DIFF: ",$spread_diff,"<pre>");

		$query = 'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
				'lft = CASE WHEN lft >  %s THEN lft + %s ELSE lft END, ' .
				'rgt = CASE WHEN rgt >= %s THEN rgt + %s ELSE rgt END ' .
				'WHERE ' . $this->getTree()->getTreePk() . ' = %s ';
		$res = $ilDB->manipulateF($query, array('integer', 'integer', 'integer', 'integer', 'integer'), array(
			$target_rgt,
			$spread_diff,
			$target_rgt,
			$spread_diff,
			$this->getTree()->getTreeId()));

		// Maybe the source node has been updated, too.
		// Check this:
		if ($source_lft > $target_rgt)
		{
			$where_offset = $spread_diff;
			$move_diff = $target_rgt - $source_lft - $spread_diff;
		}
		else
		{
			$where_offset = 0;
			$move_diff = $target_rgt - $source_lft;
		}
		$depth_diff = $target_depth - $source_depth + 1;


		$query = 'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
				'parent = CASE WHEN parent = %s THEN %s ELSE parent END, ' .
				'rgt = rgt + %s, ' .
				'lft = lft + %s, ' .
				'depth = depth + %s ' .
				'WHERE lft >= %s ' .
				'AND rgt <= %s ' .
				'AND ' . $this->getTree()->getTreePk() . ' = %s ';
		$res = $ilDB->manipulateF($query, array('integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer'), array(
			$source_parent,
			$a_target_id,
			$move_diff,
			$move_diff,
			$depth_diff,
			$source_lft + $where_offset,
			$source_rgt + $where_offset,
			$this->getTree()->getTreeId()));

		// done: close old gap
		$query = 'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
				'lft = CASE WHEN lft >= %s THEN lft - %s ELSE lft END, ' .
				'rgt = CASE WHEN rgt >= %s THEN rgt - %s ELSE rgt END ' .
				'WHERE ' . $this->getTree()->getTreePk() . ' = %s ';

		$res = $ilDB->manipulateF($query, array('integer', 'integer', 'integer', 'integer', 'integer'), array(
			$source_lft + $where_offset,
			$spread_diff,
			$source_rgt + $where_offset,
			$spread_diff,
			$this->getTree()->getTreeId()));

		if ($this->getTree()->__isMainTree())
		{
			$ilDB->unlockTables();
		}
	}
	
	/**
	 * Get rbac subtree info
	 * @global type $ilDB
	 * @param type $a_endnode_id
	 * @return type
	 */
	public function getSubtreeInfo($a_endnode_id)
	{
		global $ilDB;
		
		$query = "SELECT t2.lft lft, t2.rgt rgt, t2.child child, type ".
			"FROM ".$this->getTree()->getTreeTable()." t1 ".
			"JOIN ".$this->getTree()->getTreeTable()." t2 ON (t2.lft BETWEEN t1.lft AND t1.rgt) ".
			"JOIN ".$this->getTree()->getTableReference()." obr ON t2.child = obr.ref_id ".
			"JOIN ".$this->getTree()->getObjectDataTable()." obd ON obr.obj_id = obd.obj_id ".
			"WHERE t1.child = ".$ilDB->quote($a_endnode_id,'integer')." ".
			"AND t1.".$this->getTree()->getTreePk()." = ".$ilDB->quote($this->getTree()->getTreeId(),'integer')." ".
			"AND t2.".$this->getTree()->getTreePk()." = ".$ilDB->quote($this->getTree()->getTreeId(),'integer')." ".
			"ORDER BY t2.lft";
		
		 $GLOBALS['ilLog']->write(__METHOD__.': '.$query);
		
			
		$res = $ilDB->query($query);
		$nodes = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$nodes[$row->child]['lft']	= $row->lft;
			$nodes[$row->child]['rgt']	= $row->rgt;
			$nodes[$row->child]['child']= $row->child;
			$nodes[$row->child]['type']	= $row->type;
			
		}
		return (array) $nodes;
	}

}
?>
