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
	 */
	public function getRelation($a_node_a, $a_node_b)
	{
		if($a_node_a['child'] == $a_node_b['child'])
		{
			$GLOBALS['ilLog']->write(__METHOD__.': EQUALS');
			return ilTree::RELATION_EQUALS;
		}
		if(stristr($a_node_a['path'], $a_node_b['path']))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': CHILD');
			return ilTree::RELATION_CHILD;
		}
		if(stristr($a_node_b['path'], $a_node_a['path']))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': PARENT');
			return ilTree::RELATION_PARENT;
		}
		$path_a = substr($a_node_a['path'],0,strrpos($a_node_a['path'],'.'));
		$path_b = substr($a_node_b['path'],0,strrpos($a_node_b['path'],'.'));
		$GLOBALS['ilLog']->write(__METHOD__.': Comparing '.$path_a .' '. 'with '.$path_b);

		if($a_node_a['path'] and (strcmp($path_a,$path_b) === 0))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': SIBLING');
			return ilTree::RELATION_SIBLING;
		}

		$GLOBALS['ilLog']->write(__METHOD__.': NONE');
		return ilTree::RELATION_NONE;
	}

	/**
	 * Get subtree query
	 * @param type $a_node
	 * @param type $a_types
	 * 
	 */
	public function getSubTreeQuery($a_node, $a_types = '', $a_force_join_reference = true, $a_fields = array())
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
		
		$fields = '* ';
		if(count($a_fields))
		{
			$fields = implode(',',$a_fields);
		}

		// @todo order by
		$query = 'SELECT '.
				$fields.' '.
				'FROM '.$this->getTree()->getTreeTable().' '.
				$join.' '.
				'WHERE '.$this->getTree()->getTreeTable().'.path '.
				'BETWEEN '.
				$ilDB->quote($a_node['path'],'text').' AND '.
				$ilDB->quote($a_node['path'].'.Z','text').' '.
				'AND '.$this->getTree()->getTreeTable().'.'.$this->getTree()->getTreePk().' = '.$ilDB->quote($this->getTree()->getTreeId(),'integer').' '.
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
		$query = 'SELECT path FROM ' . $this->getTree()->getTreeTable() .' '.
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
	 * Insert new node under parent node
	 * @param type $a_node_id
	 * @param type $a_parent_id
	 * @param type $a_pos
	 */
	public function insertNode($a_node_id, $a_parent_id, $a_pos)
	{
		global $ilDB;
		 
		// LOCKED ###########################################################
		if ($this->getTree()->__isMainTree())
		{
			$ilDB->lockTables(
					array(
						0 => array('name' => 'tree', 'type' => ilDB::LOCK_WRITE)));
		}

		// get path and depth of parent
		$ilDB->setLimit(1);
		
		$res = $ilDB->queryF(
			'SELECT parent, depth, path FROM ' . $this->getTree()->getTreeTable() . ' ' .
			'WHERE child = %s '. ' '.
			'AND ' . $this->getTree()->getTreePk() . ' = %s', array('integer', 'integer'), 
			array($a_parent_id, $this->getTree()->getTreeId()));


		$r = $ilDB->fetchObject($res);

		if ($r->parent == NULL)
		{
			if ($this->getTree()->__isMainTree())
			{
				$ilDB->unlockTables();
			}
			$GLOBALS['ilLog']->logStack();
			throw new ilInvalidTreeStructureException('Parent node not found in tree');
		}

		if ($r->depth >= $this->getMaximumPossibleDepth())
		{
			// LOCKED ###########################################################
			if ($this->getTree()->__isMainTree())
			{
				$ilDB->unlockTables();
			}
			$GLOBALS['ilLog']->logStack();
			throw new ilInvalidTreeStructureException('Maximum tree depth exceeded');
		}

		$parentPath = $r->path;
		$depth = $r->depth + 1;
		$lft = 0;
		$rgt = 0;


		$ilDB->insert($this->getTree()->getTreeTable(), array($this->getTree()->getTreePk() => array('integer', $this->getTree()->getTreeId()),
			'child' => array('integer', $a_node_id),
			'parent' => array('integer', $a_parent_id),
			'lft' => array('integer', $lft),
			'rgt' => array('integer', $rgt),
			'depth' => array('integer', $depth),
			'path' => array('text', $parentPath . "." . $a_node_id)));
		
		
		if ($this->getTree()->__isMainTree())
		{
			$ilDB->unlockTables();
		}
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
		$ilDB->setLimit(2);
		$res = $ilDB->query(
			'SELECT depth, child, parent, path FROM ' . $this->getTree()->getTreeTable() . ' '.
			'WHERE ' . $ilDB->in('child', array($a_source_id, $a_target_id), false, 'integer') . ' '.
			'AND tree = ' . $ilDB->quote($this->getTree()->getTreeId(), 'integer')
		);

		// Check in tree
		if ($ilDB->numRows($res) != 2)
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

			$res = $ilDB->queryF(
                    'SELECT  MAX(depth) max_depth '.
                    'FROM    ' . $this->getTree()->getTreeTable() . ' '.
                    'WHERE   path BETWEEN %s AND %s'.' '.
                    'AND     tree = %s ', 
				array('text', 'text', 'integer'), array($source_path, $source_path . '.Z', $this->getTree()->getTreeId()));

			$row = $ilDB->fetchObject($res);

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
                UPDATE ' . $this->getTree()->getTreeTable() . '
                SET parent = CASE WHEN parent = ' . $ilDB->quote($source_parent, 'integer') . '
                             THEN ' . $ilDB->quote($a_target_id, 'integer') . '
                             ELSE parent END,

                    path = ' . $ilDB->concat(array(
					array($ilDB->quote($target_path, 'text'), 'text'),
					array($ilDB->substr('path', strrpos('.' . $source_path, '.')), 'text'))) . ' ,

                    depth = depth + ' . $ilDB->quote($depth_diff, 'integer') . '

                WHERE path  BETWEEN ' . $ilDB->quote($source_path, 'text') . '
                            AND ' . $ilDB->quote($source_path . '.Z', 'text') . '

                AND tree = ' . $ilDB->quote($this->getTree()->getTreeId(), 'integer');
		
		$GLOBALS['ilLog']->write(__METHOD__.': query is ' . $query);


		$ilDB->manipulate($query);
		if ($this->getTree()->__isMainTree())
		{
			$ilDB->unlockTables();
		}
		return true;
	}
	
	
	public static function createFromParentReleation()
	{
		 global $ilDB;

		$r = $ilDB->queryF('SELECT DISTINCT * FROM tree WHERE parent = %s', array('integer'), array(0));

		while ($row = $ilDB->fetchAssoc($r))
		{
			$success = self::createMaterializedPath(0, '');

			if ($success !== true)
			{
			}
		}
	}
	
	
	private static function createMaterializedPath($parent, $parentPath)
	{
		global $ilDB;
		$q = ' UPDATE tree
			SET path = CONCAT(COALESCE(' . $ilDB->quote($parentPath, 'text') . ', \'\'), COALESCE(child, \'\'))
			WHERE parent = %s';
		$r = $ilDB->manipulateF($q, array('integer'), array($parent));

		$r = $ilDB->queryF('SELECT child FROM tree WHERE parent = %s', array('integer'), array($parent));

		while ($row = $ilDB->fetchAssoc($r))
		{
			self::createMaterializedPath($row['child'], $parentPath . $row['child'] . '.');
		}

		return true;
	}

	public function getSubtreeInfo($a_endnode_id)
	{
		global $ilDB;
		
		// This is an optimization without the temporary tables become too big for our system.
		// The idea is to use a subquery to join and filter the trees, and only the result
		// is joined to obj_reference and obj_data.
		
		 $query = "SELECT t2.child child, type, t2.path path " .
				"FROM " . $this->getTree()->getTreeTable() . " t1 " .
				"JOIN " . $this->getTree()->getTreeTable() . " t2 ON (t2.path BETWEEN t1.path AND CONCAT(t1.path, '.Z')) " .
				"JOIN " . $this->getTree()->getTableReference() . " obr ON t2.child = obr.ref_id " .
				"JOIN " . $this->getTree()->getObjectDataTable() . " obd ON obr.obj_id = obd.obj_id " .
				"WHERE t1.child = " . $ilDB->quote($a_endnode_id, 'integer') . " " .
				"AND t1." . $this->getTree()->getTreePk() . " = " . $ilDB->quote($this->getTree()->getTreeId(), 'integer') . " " .
				"AND t2." . $this->getTree()->getTreePk() . " = " . $ilDB->quote($this->getTree()->getTreeId(), 'integer') . " " .
				"ORDER BY t2.path";

		 
		$res = $ilDB->query($query);
		$nodes = array();
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			#$nodes[$row->child]['lft'] = $row->lft;
			#$nodes[$row->child]['rgt'] = $row->rgt;
			$nodes[$row->child]['child'] = $row->child;
			$nodes[$row->child]['type'] = $row->type;
			$nodes[$row->child]['path'] = $row->path;
		}
		return (array) $nodes;
	}
}

?>
