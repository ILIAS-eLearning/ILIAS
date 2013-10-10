<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for tree implementations
 * Currrently nested set or materialize path
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesTree
 * 
 */
interface ilTreeImplementation
{
	
	/**
	 * Get subtree ids for a specific node
	 * 
	 * @return array node_ids
	 * @todo should be merged with getSubTree()
	 */
	public function getSubTreeIds($a_node_id);
	
	/**
	 * Get subtree
	 * @param array $a_node
	 * @param mixed $a_types
	 */
	public function getSubTreeQuery($a_node,$a_types = '', $a_force_join_reference = true);

	/**
	 * Get relation of two nodes
	 * 
	 * @see ilTree RELATION_NONE, RELATION_CHILD, RELATION_PARENT, RELATION_SIBLING
	 * @param int $a_node_a
	 * @param int $a_node_b
	 * @return int relation
	 */
	public function getRelation($a_node_a, $a_node_b);
	
	/**
	 * Get path ids from a startnode to a given endnode
	 * @param int $a_endnode
	 * @param int $a_startnode
	 */
	public function getPathIds($a_endnode, $a_startnode = 0);
	
	
	
	/**
	 * Delete tree
	 * @param int $node_id
	 */
	public function deleteTree($a_node_id);
	
	
	/**
	 * Move subtree to trash
	 * @param type $a_node_id
	 */
	public function moveToTrash($a_node_id);
			
	
	
}
?>
