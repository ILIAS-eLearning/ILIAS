<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for tree implementations
 * Currrently nested set or materialized path
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesTree
 */
interface ilTreeImplementation
{

    /**
     * Get subtree ids for a specific node
     * @return array node_ids
     */
    public function getSubTreeIds(int $a_node_id) : array;

    /**
     * Get subtree query
     */
    public function getSubTreeQuery(
        array $a_node,
        array $a_types = [],
        bool $a_force_join_reference = true,
        array $a_fields = []
    ) : string;

    /**
     * Get subtree query for trashed tree items
     */
    public function getTrashSubTreeQuery(
        array $a_node,
        array $a_types,
        bool $a_force_join_reference = true,
        array $a_fields = []
    ) : string;

    /**
     * Get relation of two nodes
     */
    public function getRelation(array $a_node_a, array $a_node_b) : int;

    /**
     * Get path ids from a startnode to a given endnode
     * @param int $a_endnode
     * @param int $a_startnode
     * @return int[]
     */
    public function getPathIds(int $a_endnode, int $a_startnode = 0) : array;

    /**
     * @throws ilInvalidTreeStructureException
     */
    public function insertNode(int $a_node_id, int $a_parent_id, int $a_pos) : void;

    /**
     * Delete tree
     */
    public function deleteTree(int $a_node_id) : void;

    /**
     * Move subtree to trash
     */
    public function moveToTrash(int $a_node_id) : void;

    /**
     * Move a source subtree to target
     * @throws InvalidArgumentException
     */
    public function moveTree(int $a_source_id, int $a_target_id, int $a_position) : void;

    /**
     * Get subtree info lft, rgt, path, child, type
     * @return array
     */
    public function getSubtreeInfo(int $a_endnode_id) : array;

    /**
     * Validate the parent relations of the tree implementation
     * For nested set, validate the lft, rgt against child <-> parent
     * For materialized path validate path against child <-> parent
     * @return int[] array of failure nodes
     */
    public function validateParentRelations() : array;
}
