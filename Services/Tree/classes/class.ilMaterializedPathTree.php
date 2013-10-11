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
	
}
?>
