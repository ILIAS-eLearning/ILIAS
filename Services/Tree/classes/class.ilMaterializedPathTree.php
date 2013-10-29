<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tree/interfaces/interface.ilTreeImplementation.php';

/**
 * Base class for materialize path based trees
 * Based on implementation of Werner Randelshofer
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$

 * @ingroup ServicesTree
 * 
 */
class ilMaterializedPathTree implements ilTreeImplementation
{
	private $maximum_possible_depth = 100;
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
	 * Get maximum possible depth
	 * @return type
	 */
	protected function getMaximumPossibleDepth()
	{
		return $this->maximum_possible_depth;
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
		
		$node = $this->getTree()->getNodeTreeData($a_node_id);
		$query = 'SELECT child FROM '.$this->getTree()->getTreeTable().' '.
				'WHERE path BETWEEN '.
				$ilDB->quote($node['path'], 'text').' AND '.
				$ilDB->quote($node['path'].'.Z', 'text').' '.
				'AND child != %s '.
				'AND '.$this->getTree()->getTreePk().' = %s';
		
		$res = $ilDB->queryF(
				$query, 
				array('integer', 'integer'),
				array($a_node_id, $this->getTree()->getTreeId())
		);
		while($row = $ilDB->fetchAssoc($res))
		{
			$childs[] = $row['child'];
		}
		return $childs ? $childs : array();
	}

	/**
	 * Get relation of two nodes
	 * @param type $a_node_a
	 * @param type $a_node_b
	 * @todo
	 */
	public function getRelation($a_node_a, $a_node_b)
	{
		return ilTree::RELATION_NONE;
	}

	/**
	 * Get subtree query
	 * @param type $a_node
	 * @param type $a_types
	 * 
	 */
	public function getSubTreeQuery($a_node, $a_types = '', $a_force_join_reference = true)
	{
		global $ilDB;
		
		$type_str = '';
		if(is_array($a_types))
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

		// @todo order by
		$query = 'SELECT * FROM '.$this->getTree()->getTreeTable().' '.
				$join.' '.
				'WHERE '.$this->getTree()->getTreeTable().'.path '.
				'BETWEEN '.
				$ilDB->quote($a_node['path'],'text').' AND '.
				$ilDB->quote($a_node['path'].'.Z','text').' '.
				'AND '.$this->getTree()->getTreeTable().'.'.$this->getTree()->getTreePk().' = '.$ilDB->quote($this->getTree()->getTreeId().'integer').
				$type_str.' '.
				'ORDER BY '.$this->getTree()->getTreeTable().'.path';
		
		return $query;
	}
	
	/**
	 * Get path ids
	 * @param int $a_endnode
	 * @param int $a_startnode
	 */
	public function getPathIds($a_endnode, $a_startnode = 0)
	{
		global $ilDB;
		
		$ilDB->setLimit(1);
		$query = 'SELECT path FROM ' . $this->getTree()->getTreeTable() .
			'WHERE child = '. $ilDB->quote($a_endnode,'integer').' ';
		$res = $ilDB->query($query);

		$path = null;
		while ($row = $ilDB->fetchAssoc($res))
		{
			$path = $row['path'];
		}

		$pathIds = explode('.', $path);

		if ($a_startnode != 0)
		{
			while (count($pathIds) > 0 && $pathIds[0] != $a_startnode)
			{
				array_shift($pathIds);
			}
		}
		return $pathIds;
	}

	/**
	 * Delete a subtree
	 * @param int $a_node_id
	 */
	public function deleteTree($a_node_id)
	{
		global $ilDB;
		
		$a_node = $this->getTree()->getNodeData($a_node_id);
		
		$query = 'DELETE FROM '.$this->getTree()->getTreeTable().' '.
				'WHERE path BETWEEN '.$ilDB->quote($a_node['path'],'text').' '.
				'AND '.$ilDB->quote($a_node['path'].'.Z','text').' '.
				'AND '.$this->getTree()->getTreePk().' = '.$ilDB->quote($a_node[$this->getTree()->getTreePk()]);
		$ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * Move subtree to trash
	 * @param type $a_node_id
	 * @todo
	 */
	public function moveToTrash($a_node_id)
	{
		global $ilDB;
 
		// LOCKED ###########################################################
		if ($this->getTree()->__isMainTree())
		{
			$ilDB->lockTables(
					array(
						0 => array('name' => 'tree', 'type' => ilDB::LOCK_WRITE)));
		}
		try 
		{
			$node = $this->getTree()->getNodeTreeData($a_node_id);
		}
		catch(Exception $e) 
		{
			if ($this->getTree()->__isMainTree())
			{
				$ilDB->unlockTables();
			}
			throw $e;
		}


		// Set the nodes deleted (negative tree id)
		$ilDB->manipulateF('
			UPDATE ' . $this->getTree()->getTreeTable().' '.
			'SET tree = %s' .' '.
			'WHERE ' . $this->getTree()->getTreePk() . ' = %s ' .
			'AND path BETWEEN %s AND %s', 
		array('integer', 'integer', 'text', 'text'),
		array(-$a_node_id, $this->getTree()->getTreeId(), $node['path'], $node['path'] . '.Z'));

		
		// LOCKED ###########################################################
		if ($this->getTree()->__isMainTree())
		{
			$ilDB->unlockTables();
		}
		return true;
	}
	
	/**
	 * move source subtree to target node
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
		$this->ilDB->setLimit(2);
		$res = $this->ilDB->query(
			'SELECT depth, child, parent, path FROM ' . $this->getTree()->getTreeTable() . 
			'WHERE ' . $this->ilDB->in('child', array($a_source_id, $a_target_id), false, 'integer') . 
			'AND tree = ' . $this->ilDB->quote($this->getTree()->getTreeId(), 'integer')
		);

		// Check in tree
		if ($this->ilDB->numRows($res) != 2)
		{
			if ($this->getTree()->__isMainTree())
			{
				$ilDB->unlockTables();
			}
			$GLOBALS['ilLog']->logStack();
			$GLOBALS['ilLog']->write(__METHOD__.': Objects not found in tree');
			throw new InvalidArgumentException('Error moving subtree');
		}

		while ($row = $this->ilDB->fetchObject($res))
		{
			if ($row->child == $a_source_id)
			{
				$source_path = $row->path;
				$source_depth = $row->depth;
				$source_parent = $row->parent;
			}
			else
			{
				$target_path = $row->path;
				$target_depth = $row->depth;
			}
		}

		if ($target_depth >= $source_depth)
		{
			// We move nodes deeper into the tree. Therefore we need to
			// check whether we might exceed the maximal path length.
			// We use FOR UPDATE here, because we don't want anyone to
			// insert new nodes while we move the subtree.

			$res = $this->ilDB->queryF('
                    SELECT  MAX(depth) max_depth
                    FROM    ' . $this->getTree()->getTreeTable() . '
                    WHERE   path BETWEEN %s AND %s
                    AND     tree = %s ', 
				array('text', 'text', 'integer'), array($source_path, $source_path . '.Z', $this->getTree()->getTreeId()));

			$row = $this->ilDB->fetchObject($res);

			if ($row->max_depth - $source_depth + $target_depth + 1 > $this->getMaximumPossibleDepth())
			{
				if ($this->getTree()->__isMainTree())
				{
					$ilDB->unlockTables();
				}
				$GLOBALS['ilLog']->logStack();
				$GLOBALS['ilLog']->write(__METHOD__.': Objects not found in tree');
				throw new ilInvalidTreeStructureException('Maximum tree depth exceeded');
			}
		}
		// Check target not child of source
		if (substr($target_path . '.', 0, strlen($source_path) . '.') == $source_path . '.')
		{
			if ($this->getTree()->__isMainTree())
			{
				$ilDB->unlockTables();
			}
			$GLOBALS['ilLog']->logStack();
			$GLOBALS['ilLog']->write(__METHOD__.': Target is child of source');
			throw new ilInvalidArgumentException('Error moving subtree: target is child of source');
		}
		$depth_diff = $target_depth - $source_depth + 1;

		// move subtree:
		$query = '
                UPDATE ' . $this->table_tree . '
                SET parent = CASE WHEN parent = ' . $this->ilDB->quote($source_parent, 'integer') . '
                             THEN ' . $this->ilDB->quote($a_target_id, 'integer') . '
                             ELSE parent END,

                    path = ' . $this->ilDB->concat(array(
					array($this->ilDB->quote($target_path, 'text'), 'text'),
					array($this->ilDB->substr('path', strrpos('.' . $source_path, '.')), 'text'))) . ' ,

                    depth = depth + ' . $this->ilDB->quote($depth_diff, 'integer') . '

                WHERE path  BETWEEN ' . $this->ilDB->quote($source_path, 'text') . '
                            AND ' . $this->ilDB->quote($source_path . '.Z', 'text') . '

                AND tree = ' . $this->ilDB->quote($this->tree_id, 'integer');


		$this->ilDB->manipulate($query);
		if ($this->getTree()->__isMainTree())
		{
			$ilDB->unlockTables();
		}
		return true;
	}
	
}
?>
