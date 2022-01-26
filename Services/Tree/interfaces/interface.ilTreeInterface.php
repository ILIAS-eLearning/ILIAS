<?php

/**
 * Tree class
 * data representation in hierachical trees using the Nested Set Model with Gaps
 * by Joe Celco.
 *
 * @author  Sascha Hofmann <saschahofmann@gmx.de>
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTree
 */
interface ilTreeInterface
{
    public const TREE_TYPE_MATERIALIZED_PATH = 'mp';
    public const TREE_TYPE_NESTED_SET = 'ns';
    
    public const POS_LAST_NODE = -2;
    public const POS_FIRST_NODE = -1;
    
    public const RELATION_CHILD = 1;
    public const RELATION_PARENT = 2;
    public const RELATION_SIBLING = 3;
    public const RELATION_EQUALS = 4;
    public const RELATION_NONE = 5;
    
    /**
     * Init tree implementation
     */
    public function initTreeImplementation(): void;
    
    /**
     * Get tree implementation
     *
     * @return ilTreeImplementation $impl
     */
    public function getTreeImplementation(): ilTreeImplementation;
    
    /**
     * Use Cache (usually activated)
     */
    public function useCache(bool $a_use = true): void;
    
    /**
     * Check if cache is active
     */
    public function isCacheUsed(): bool;
    
    /**
     * Get depth cache
     */
    public function getDepthCache(): array;
    
    /**
     * Get parent cache
     */
    public function getParentCache(): array;
    
    /**
     * Do not use it
     * Store user language. This function is used by the "main"
     * tree only (during initialisation).
     *
     * @todo remove this dependency to user language from tree class
     */
    public function initLangCode(): void;
    
    /**
     * Get tree table name
     */
    public function getTreeTable(): string;
    
    /**
     * Get object data table
     */
    public function getObjectDataTable(): string;
    
    /**
     * Get tree primary key
     */
    public function getTreePk(): string;
    
    /**
     * Get reference table if available
     */
    public function getTableReference(): string;
    
    /**
     * Get default gap
     */
    public function getGap(): int;
    
    /**
     * reset in tree cache
     */
    public function resetInTreeCache(): void;
    
    /**
     * set table names
     * The primary key of the table containing your object_data must be 'obj_id'
     * You may use a reference table.
     * If no reference table is specified the given tree table is directly joined
     * with the given object_data table.
     * The primary key in object_data table and its foreign key in reference table must have the same name!
     */
    public function setTableNames(
        string $a_table_tree,
        string $a_table_obj_data,
        string $a_table_obj_reference = ""
    ): void;
    
    /**
     * set column containing primary key in reference table
     */
    public function setReferenceTablePK(string $a_column_name): void;
    
    /**
     * set column containing primary key in object table
     */
    public function setObjectTablePK($a_column_name): void;
    
    /**
     * set column containing primary key in tree table
     */
    public function setTreeTablePK($a_column_name): void;
    
    /**
     * build join depending on table settings
     *
     * @access    private
     * @return    string
     */
    public function buildJoin(): string;
    
    /**
     * Get relation of two nodes
     *
     * @todo add unit test
     */
    public function getRelation(int $a_node_a, int $a_node_b): int;
    
    /**
     * get relation of two nodes by node data
     */
    public function getRelationOfNodes(array $a_node_a_arr, array $a_node_b_arr): int;
    
    /**
     * @param int $a_node
     * @return int[]
     */
    public function getChildIds(int $a_node): array;
    
    /**
     * get child nodes of given node
     *
     * @todo remove dependency to ilObjUser and use $this->lang_code
     */
    public function getChilds(int $a_node_id, string $a_order = "", string $a_direction = "ASC"): array;
    
    /**
     * get child nodes of given node (exclude filtered obj_types)
     *
     * @param string[] objects to filter (e.g array('rolf'))
     * @param int node_id
     * @param string sort order of returned childs, optional (possible values: 'title','desc','last_update' or 'type')
     * @param string sort direction, optional (possible values: 'DESC' or 'ASC'; defalut is 'ASC')
     * @return array with node data of all childs or empty array
     */
    public function getFilteredChilds(
        array $a_filter,
        int $a_node,
        string $a_order = "",
        string $a_direction = "ASC"
    ): array;
    
    /**
     * get child nodes of given node by object type
     *
     * @todo check the perfomance optimization and remove
     */
    public function getChildsByType(int $a_node_id, string $a_type): array;
    
    /**
     * get child nodes of given node by object type
     */
    public function getChildsByTypeFilter(
        int $a_node_id,
        array $a_types,
        string $a_order = "",
        string $a_direction = "ASC"
    ): array;
    
    /**
     * Insert node from trash deletes trash entry.
     * If we have database query exceptions we could wrap insertNode in try/catch
     * and rollback if the insert failed.
     *
     * @todo use atom query for deletion and creating or use an update statement.
     */
    public function insertNodeFromTrash(
        int $a_source_id,
        int $a_target_id,
        int $a_tree_id,
        int $a_pos = self::POS_LAST_NODE,
        bool $a_reset_deleted_date = false
    ): void;
    
    /**
     * insert new node with node_id under parent node with parent_id
     *
     * @throws InvalidArgumentException
     */
    public function insertNode(
        int $a_node_id,
        int $a_parent_id,
        int $a_pos = self::POS_LAST_NODE,
        bool $a_reset_deletion_date = false
    ): void;
    
    /**
     * get filtered subtree
     * get all subtree nodes beginning at a specific node
     * excluding specific object types and their child nodes.
     * E.g getFilteredSubTreeNodes()
     */
    public function getFilteredSubTree(int $a_node_id, array $a_filter = []): array;
    
    /**
     * Get all ids of subnodes
     *
     * @param int $a_ref_id
     * @return int[]
     */
    public function getSubTreeIds(int $a_ref_id): array;
    
    /**
     * get all nodes in the subtree under specified node
     *
     * @todo remove the in cache exception for lm tree
     * @todo refactor $a_type to string[]
     */
    public function getSubTree(array $a_node, bool $a_with_data = true, array $a_type = []): array;
    
    /**
     * delete node and the whole subtree under this node
     */
    public function deleteTree(array $a_node): void;
    
    /**
     * Validate parent relations of tree
     *
     * @return int[] array of failure nodes
     */
    public function validateParentRelations(): array;
    
    /**
     * get path from a given startnode to a given endnode
     * if startnode is not given the rootnode is startnode.
     * This function chooses the algorithm to be used.
     */
    public function getPathFull(int $a_endnode_id, int $a_startnode_id = 0): array;
    
    /**
     * Preload depth/parent
     *
     * @param int[]
     */
    public function preloadDepthParent(array $a_node_ids): void;
    
    /**
     * get path from a given startnode to a given endnode
     * if startnode is not given the rootnode is startnode
     *
     * @return int[] all path ids from startnode to endnode
     */
    public function getPathId(int $a_endnode_id, int $a_startnode_id = 0): array;
    
    /**
     * Returns the node path for the specified object reference.
     * Note: this function returns the same result as getNodePathForTitlePath,
     * but takes ref-id's as parameters.
     * This function differs from getPathFull, in the following aspects:
     * - The title of an object is not translated into the language of the user
     * - This function is significantly faster than getPathFull.
     *
     * @return    array    ordered path info (depth,parent,child,obj_id,type,title)
     *               or null, if the node_id can not be converted into a node path.
     */
    public function getNodePath(int $a_endnode_id, int $a_startnode_id = 0): array;
    
    /**
     * check consistence of tree
     * all left & right values are checked if they are exists only once
     *
     * @todo      remove exception for "check-method"
     */
    public function checkTree(): bool;
    
    /**
     * check, if all childs of tree nodes exist in object table
     *
     * @throws ilInvalidTreeStructureException
     */
    public function checkTreeChilds(bool $a_no_zero_child = true): bool;
    
    /**
     * Return the current maximum depth in the tree
     */
    public function getMaximumDepth(): int;
    
    /**
     * return depth of a node in tree
     */
    public function getDepth(int $a_node_id): int;
    
    /**
     * return all columns of tabel tree
     *
     * @throws InvalidArgumentException
     */
    public function getNodeTreeData(int $a_node_id): array;
    
    /**
     * get all information of a node.
     * get data of a specific node from tree and object_data
     *
     * @throws InvalidArgumentException
     */
    public function getNodeData(int $a_node_id, ?int $a_tree_pk = null): array;
    
    /**
     * get data of parent node from tree and object_data
     */
    public function fetchNodeData(array $a_row): array;
    
    /**
     * get all information of a node.
     * get data of a specific node from tree and object_data
     */
    public function isInTree(?int $a_node_id): bool;
    
    /**
     * get data of parent node from tree and object_data
     */
    public function getParentNodeData(int $a_node_id): array;
    
    /**
     * checks if a node is in the path of an other node
     */
    public function isGrandChild(int $a_startnode_id, int $a_querynode_id): bool;
    
    /**
     * create a new tree
     * to do: ???
     */
    public function addTree(int $a_tree_id, int $a_node_id = -1): bool;
    
    /**
     * remove an existing tree
     */
    public function removeTree(int $a_tree_id): bool;
    
    /**
     * Move node to trash bin
     *
     * @throws InvalidArgumentException
     * @todo remove ilUser dependency
     */
    public function moveToTrash(int $a_node_id, bool $a_set_deleted = false, int $a_deleted_by = 0): bool;
    
    /**
     * This is a wrapper for isSaved() with a more useful name
     */
    public function isDeleted(int $a_node_id): bool;
    
    /**
     * Use method isDeleted
     *
     * @deprecated since 4.4.0
     */
    public function isSaved(int $a_node_id): bool;
    
    /**
     * Preload deleted information
     */
    public function preloadDeleted(array $a_node_ids): void;
    
    /**
     * get data saved/deleted nodes
     *
     * @throws InvalidArgumentException
     */
    public function getSavedNodeData(int $a_parent_id): array;
    
    /**
     * get object id of saved/deleted nodes
     */
    public function getSavedNodeObjIds(array $a_obj_ids): array;
    
    /**
     * get parent id of given node
     *
     * @throws InvalidArgumentException
     */
    public function getParentId(int $a_node_id): ?int;
    
    /**
     * get left value of given node
     *
     * @throws InvalidArgumentException
     * @todo move to tree implementation and throw NotImplementedException for materialized path implementation
     */
    public function getLeftValue(int $a_node_id): int;
    
    /**
     * get sequence number of node in sibling sequence
     *
     * @throws InvalidArgumentException
     * @todo move to tree implementation and throw NotImplementedException for materialized path implementation
     */
    public function getChildSequenceNumber(array $a_node, string $type = ""): int;
    
    public function readRootId(): int;
    
    public function getRootId(): int;
    
    public function setRootId(int $a_root_id): void;
    
    public function getTreeId(): int;
    
    public function setTreeId(int $a_tree_id): void;
    
    /**
     * get node data of successor node
     *
     * @throws InvalidArgumentException
     * @todo  move to tree implementation and throw NotImplementedException for materialized path implementation
     * @fixme fix return false
     */
    public function fetchSuccessorNode(int $a_node_id, string $a_type = ""): ?array;
    
    /**
     * get node data of predecessor node
     *
     * @throws InvalidArgumentException
     * @todo  move to tree implementation and throw NotImplementedException for materialized path implementation
     * @fixme fix return false
     */
    public function fetchPredecessorNode(int $a_node_id, string $a_type = ""): ?array;
    
    /**
     * Wrapper for renumber. This method locks the table tree
     * (recursive)
     */
    public function renumber(int $node_id = 1, int $i = 1): int;
    
    /**
     * Check for parent type
     * e.g check if a folder (ref_id 3) is in a parent course obj => checkForParentType(3,'crs');
     */
    public function checkForParentType(int $a_ref_id, string $a_type, bool $a_exclude_source_check = false): int;
    
    /**
     * Move Tree Implementation
     *
     * @access    public
     * @param int source ref_id
     * @param int target ref_id
     * @param int location ilTree::POS_LAST_NODE or ilTree::POS_FIRST_NODE
     * @return int
     */
    public function moveTree(int $a_source_id, int $a_target_id, int $a_location = self::POS_LAST_NODE): void;
    
    /**
     * This method is used for change existing objects
     * and returns all necessary information for this action.
     * The former use of ilTree::getSubtree needs to much memory.
     */
    public function getRbacSubtreeInfo(int $a_endnode_id): array;
    
    /**
     * Get tree subtree query
     */
    public function getSubTreeQuery(
        int $a_node_id,
        array $a_fields = [],
        array $a_types = [],
        bool $a_force_join_reference = false
    ): string;
    
    public function getTrashSubTreeQuery(
        int $a_node_id,
        array $a_fields = [],
        array $a_types = [],
        bool $a_force_join_reference = false
    ): string;
    
    /**
     * get all node ids in the subtree under specified node id, filter by object ids
     */
    public function getSubTreeFilteredByObjIds(int $a_node_id, array $a_obj_ids, array $a_fields = []): array;
    
    public function deleteNode(int $a_tree_id, int $a_node_id): void;
    
    /**
     * Lookup object types in trash
     *
     * @return string[]
     */
    public function lookupTrashedObjectTypes(): array;
    
    /**
     * check if current tree instance operates on repository tree table
     */
    public function isRepositoryTree(): bool;
}
