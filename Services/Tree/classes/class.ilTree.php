<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/


/**
 * Tree class
 * data representation in hierachical trees using the Nested Set Model with Gaps
 * by Joe Celco.
 * @author  Sascha Hofmann <saschahofmann@gmx.de>
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTree
 */
class ilTree
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

    protected const DEFAULT_LANGUAGE = 'en';
    protected const DEFAULT_GAP = 50;

    protected ilLogger $logger;
    protected ilDBInterface $db;
    protected ?ilAppEventHandler $eventHandler;

    /**
     * Used in fetchNodeData
     * @var string
     */
    private string $lang_code;

    /**
     * points to root node (may be a subtree)
     */
    protected int $root_id;

    /**
     * to use different trees in one db-table
     */
    protected int $tree_id;

    /**
     * table name of tree table
     */
    protected string $table_tree;

    /**
     * table name of object_data table
     */
    protected string $table_obj_data;

    /**
     * table name of object_reference table
     */
    protected string $table_obj_reference;

    /**
     * column name containing primary key in reference table
     */
    protected string $ref_pk;

    /**
     * column name containing primary key in object table
     */
    protected string $obj_pk;

    /**
     * column name containing tree id in tree table
     */
    protected string $tree_pk;

    /**
     * Size of the gaps to be created in the nested sets sequence numbering of the
     * tree nodes.
     * Having gaps in the tree greatly improves performance on all operations
     * that add or remove tree nodes.
     * Setting this to zero will leave no gaps in the tree.
     * Setting this to a value larger than zero will create gaps in the tree.
     * Each gap leaves room in the sequence numbering for the specified number of
     * nodes.
     * (The gap is expressed as the number of nodes. Since each node consumes
     * two sequence numbers, specifying a gap of 1 will leave space for 2
     * sequence numbers.)
     * A gap is created, when a new child is added to a node, and when not
     * enough room between node.rgt and the child with the highest node.rgt value
     * of the node is available.
     * A gap is closed, when a node is removed and when (node.rgt - node.lft)
     * is bigger than gap * 2.
     */
    private int $gap;

    protected bool $use_cache;
    /** @var array<int, bool> */
    protected array $oc_preloaded = [];
    protected array $depth_cache = [];
    protected array $parent_cache = [];
    protected array $in_tree_cache = [];
    protected array $translation_cache = [];
    protected array $parent_type_cache = [];
    protected array $is_saved_cache = [];

    private ?ilTreeImplementation $tree_impl = null;

    private array $path_id_cache = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        int $a_tree_id,
        int $a_root_id = 0,
        ilDBInterface $db = null
    ) {
        global $DIC;

        $this->db = $db ?? $DIC->database();
        $this->logger = ilLoggerFactory::getLogger('tree');
        //$this->logger = $DIC->logger()->tree();
        if (isset($DIC['ilAppEventHandler'])) {
            $this->eventHandler = $DIC['ilAppEventHandler'];
        }

        $this->lang_code = self::DEFAULT_LANGUAGE;

        if (func_num_args() > 3) {
            $this->logger->error("Wrong parameter count!");
            $this->logger->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException("Wrong parameter count!");
        }

        if ($a_root_id > 0) {
            $this->root_id = $a_root_id;
        } else {
            $this->root_id = ROOT_FOLDER_ID;
        }

        $this->tree_id = $a_tree_id;
        $this->table_tree = 'tree';
        $this->table_obj_data = 'object_data';
        $this->table_obj_reference = 'object_reference';
        $this->ref_pk = 'ref_id';
        $this->obj_pk = 'obj_id';
        $this->tree_pk = 'tree';

        $this->use_cache = true;

        // By default, we create gaps in the tree sequence numbering for 50 nodes
        $this->gap = self::DEFAULT_GAP;

        // init tree implementation
        $this->initTreeImplementation();
    }

    /**
     * @param int $node_id
     * @return int[]
     */
    public static function lookupTreesForNode(int $node_id): array
    {
        global $DIC;

        $db = $DIC->database();

        $query = 'select tree from tree ' .
            'where child = ' . $db->quote($node_id, \ilDBConstants::T_INTEGER);
        $res = $db->query($query);

        $trees = [];
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            $trees[] = (int) $row->tree;
        }
        return $trees;
    }

    /**
     * Init tree implementation
     */
    public function initTreeImplementation(): void
    {
        global $DIC;

        if (!$DIC->isDependencyAvailable('settings') || $DIC->settings()->getModule() != 'common') {
            $setting = new ilSetting('common');
        } else {
            $setting = $DIC->settings();
        }

        if ($this->__isMainTree()) {
            if ($setting->get('main_tree_impl', 'ns') == 'ns') {
                $this->tree_impl = new ilNestedSetTree($this);
            } else {
                $this->tree_impl = new ilMaterializedPathTree($this);
            }
        } else {
            $this->tree_impl = new ilNestedSetTree($this);
        }
    }

    /**
     * Get tree implementation
     * @return ilTreeImplementation $impl
     */
    public function getTreeImplementation(): ilTreeImplementation
    {
        return $this->tree_impl;
    }

    /**
     * Use Cache (usually activated)
     */
    public function useCache(bool $a_use = true): void
    {
        $this->use_cache = $a_use;
    }

    /**
     * Check if cache is active
     */
    public function isCacheUsed(): bool
    {
        return $this->__isMainTree() && $this->use_cache;
    }

    /**
     * Get depth cache
     */
    public function getDepthCache(): array
    {
        return $this->depth_cache;
    }

    /**
     * Get parent cache
     */
    public function getParentCache(): array
    {
        return $this->parent_cache;
    }

    protected function getLangCode(): string
    {
        return $this->lang_code;
    }

    /**
     * Do not use it
     * Store user language. This function is used by the "main"
     * tree only (during initialisation).
     * @todo remove this dependency to user language from tree class
     */
    public function initLangCode(): void
    {
        global $DIC;

        if ($DIC->offsetExists('ilUser')) {
            $this->lang_code = $DIC->user()->getCurrentLanguage() ?
                $DIC->user()->getCurrentLanguage() : self::DEFAULT_LANGUAGE;
        } else {
            $this->lang_code = self::DEFAULT_LANGUAGE;
        }
    }

    /**
     * Get tree table name
     */
    public function getTreeTable(): string
    {
        return $this->table_tree;
    }

    /**
     * Get object data table
     */
    public function getObjectDataTable(): string
    {
        return $this->table_obj_data;
    }

    /**
     * Get tree primary key
     */
    public function getTreePk(): string
    {
        return $this->tree_pk;
    }

    /**
     * Get reference table if available
     */
    public function getTableReference(): string
    {
        return $this->table_obj_reference;
    }

    /**
     * Get default gap
     */
    public function getGap(): int
    {
        return $this->gap;
    }

    /**
     * reset in tree cache
     */
    public function resetInTreeCache(): void
    {
        $this->in_tree_cache = array();
    }

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
    ): void {
        $this->table_tree = $a_table_tree;
        $this->table_obj_data = $a_table_obj_data;
        $this->table_obj_reference = $a_table_obj_reference;

        // reconfigure tree implementation
        $this->initTreeImplementation();
    }

    /**
     * set column containing primary key in reference table
     */
    public function setReferenceTablePK(string $a_column_name): void
    {
        $this->ref_pk = $a_column_name;
    }

    /**
     * set column containing primary key in object table
     */
    public function setObjectTablePK(string $a_column_name): void
    {
        $this->obj_pk = $a_column_name;
    }

    /**
     * set column containing primary key in tree table
     */
    public function setTreeTablePK(string $a_column_name): void
    {
        $this->tree_pk = $a_column_name;
    }

    /**
     * build join depending on table settings
     * @access    private
     * @return    string
     */
    public function buildJoin(): string
    {
        if ($this->table_obj_reference) {
            // Use inner join instead of left join to improve performance
            return "JOIN " . $this->table_obj_reference . " ON " . $this->table_tree . ".child=" . $this->table_obj_reference . "." . $this->ref_pk . " " .
                "JOIN " . $this->table_obj_data . " ON " . $this->table_obj_reference . "." . $this->obj_pk . "=" . $this->table_obj_data . "." . $this->obj_pk . " ";
        } else {
            // Use inner join instead of left join to improve performance
            return "JOIN " . $this->table_obj_data . " ON " . $this->table_tree . ".child=" . $this->table_obj_data . "." . $this->obj_pk . " ";
        }
    }

    /**
     * Get relation of two nodes
     * @todo add unit test
     */
    public function getRelation(int $a_node_a, int $a_node_b): int
    {
        return $this->getRelationOfNodes(
            $this->getNodeTreeData($a_node_a),
            $this->getNodeTreeData($a_node_b)
        );
    }

    /**
     * get relation of two nodes by node data
     */
    public function getRelationOfNodes(array $a_node_a_arr, array $a_node_b_arr): int
    {
        return $this->getTreeImplementation()->getRelation($a_node_a_arr, $a_node_b_arr);
    }

    /**
     * @param int $a_node
     * @return int[]
     */
    public function getChildIds(int $a_node): array
    {
        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE parent = ' . $this->db->quote($a_node, 'integer') . ' ' .
            'AND tree = ' . $this->db->quote($this->tree_id, 'integer' . ' ' .
                'ORDER BY lft');
        $res = $this->db->query($query);

        $childs = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $childs[] = (int) $row->child;
        }
        return $childs;
    }

    /**
     * get child nodes of given node
     * @todo remove dependency to ilObjUser and use $this->lang_code
     */
    public function getChilds(int $a_node_id, string $a_order = "", string $a_direction = "ASC"): array
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilUser = $DIC['ilUser'];

        // init childs
        $childs = [];

        // number of childs
        $count = 0;

        // init order_clause
        $order_clause = "";

        // set order_clause if sort order parameter is given
        if (!empty($a_order)) {
            $order_clause = "ORDER BY " . $a_order . " " . $a_direction;
        } else {
            $order_clause = "ORDER BY " . $this->table_tree . ".lft";
        }

        $query = sprintf(
            'SELECT * FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() .
            "WHERE parent = %s " .
            "AND " . $this->table_tree . "." . $this->tree_pk . " = %s " .
            $order_clause,
            $this->db->quote($a_node_id, 'integer'),
            $this->db->quote($this->tree_id, 'integer')
        );

        $res = $this->db->query($query);

        if (!$count = $res->numRows()) {
            return [];
        }

        // get rows and object ids
        $rows = [];
        $obj_ids = [];
        while ($r = $this->db->fetchAssoc($res)) {
            $rows[] = $r;
            $obj_ids[] = (int) $r["obj_id"];
        }

        // preload object translation information
        if ($this->__isMainTree() && $this->isCacheUsed() && is_object($ilObjDataCache) &&
            is_object($ilUser) && $this->lang_code == $ilUser->getLanguage() && !isset($this->oc_preloaded[$a_node_id])) {
            //			$ilObjDataCache->preloadTranslations($obj_ids, $this->lang_code);
            $ilObjDataCache->preloadObjectCache($obj_ids, $this->lang_code);
            $this->fetchTranslationFromObjectDataCache($obj_ids);
            $this->oc_preloaded[$a_node_id] = true;
        }

        foreach ($rows as $row) {
            $childs[] = $this->fetchNodeData($row);

            // Update cache of main tree
            if ($this->__isMainTree()) {
                #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Storing in tree cache '.$row['child'].' = true');
                $this->in_tree_cache[$row['child']] = $row['tree'] == 1;
            }
        }
        $childs[$count - 1]["last"] = true;
        return $childs;
    }

    /**
     * get child nodes of given node (exclude filtered obj_types)
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
    ): array {
        $childs = $this->getChilds($a_node, $a_order, $a_direction);

        $filtered = [];
        foreach ($childs as $child) {
            if (!in_array($child["type"], $a_filter)) {
                $filtered[] = $child;
            }
        }
        return $filtered;
    }

    /**
     * get child nodes of given node by object type
     * @todo check the perfomance optimization and remove
     */
    public function getChildsByType(int $a_node_id, string $a_type): array
    {
        if ($a_type == 'rolf' && $this->table_obj_reference) {
            // Performance optimization: A node can only have exactly one
            // role folder as its child. Therefore we don't need to sort the
            // results, and we can let the database know about the expected limit.
            $this->db->setLimit(1, 0);
            $query = sprintf(
                "SELECT * FROM " . $this->table_tree . " " .
                $this->buildJoin() .
                "WHERE parent = %s " .
                "AND " . $this->table_tree . "." . $this->tree_pk . " = %s " .
                "AND " . $this->table_obj_data . ".type = %s ",
                $this->db->quote($a_node_id, 'integer'),
                $this->db->quote($this->tree_id, 'integer'),
                $this->db->quote($a_type, 'text')
            );
        } else {
            $query = sprintf(
                "SELECT * FROM " . $this->table_tree . " " .
                $this->buildJoin() .
                "WHERE parent = %s " .
                "AND " . $this->table_tree . "." . $this->tree_pk . " = %s " .
                "AND " . $this->table_obj_data . ".type = %s " .
                "ORDER BY " . $this->table_tree . ".lft",
                $this->db->quote($a_node_id, 'integer'),
                $this->db->quote($this->tree_id, 'integer'),
                $this->db->quote($a_type, 'text')
            );
        }
        $res = $this->db->query($query);

        // init childs
        $childs = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $childs[] = $this->fetchNodeData($row);
        }
        return $childs;
    }

    /**
     * get child nodes of given node by object type
     */
    public function getChildsByTypeFilter(
        int $a_node_id,
        array $a_types,
        string $a_order = "",
        string $a_direction = "ASC"
    ): array {
        $filter = ' ';
        if ($a_types) {
            $filter = 'AND ' . $this->table_obj_data . '.type IN(' . implode(',', ilArrayUtil::quoteArray($a_types)) . ') ';
        }

        // set order_clause if sort order parameter is given
        if (!empty($a_order)) {
            $order_clause = "ORDER BY " . $a_order . " " . $a_direction;
        } else {
            $order_clause = "ORDER BY " . $this->table_tree . ".lft";
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() .
            'WHERE parent = ' . $this->db->quote($a_node_id, 'integer') . ' ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = ' . $this->db->quote(
                $this->tree_id,
                'integer'
            ) . ' ' .
            $filter .
            $order_clause;

        $res = $this->db->query($query);

        $childs = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $childs[] = $this->fetchNodeData($row);
        }

        return $childs;
    }

    /**
     * Insert node from trash deletes trash entry.
     * If we have database query exceptions we could wrap insertNode in try/catch
     * and rollback if the insert failed.
     * @todo use atom query for deletion and creating or use an update statement.
     */
    public function insertNodeFromTrash(
        int $a_source_id,
        int $a_target_id,
        int $a_tree_id,
        int $a_pos = self::POS_LAST_NODE,
        bool $a_reset_deleted_date = false
    ): void {
        if ($this->__isMainTree()) {
            if ($a_source_id <= 1 || $a_target_id <= 0) {
                $this->logger->logStack(ilLogLevel::WARNING);
                throw new InvalidArgumentException('Invalid parameter given for ilTree::insertNodeFromTrash');
            }
        }
        if ($this->isInTree($a_source_id)) {
            ilLoggerFactory::getLogger('tree')->error('Node already in tree');
            ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::WARNING);
            throw new InvalidArgumentException('Node already in tree.');
        }

        $query = 'DELETE from tree ' .
            'WHERE tree = ' . $this->db->quote($a_tree_id, 'integer') . ' ' .
            'AND child = ' . $this->db->quote($a_source_id, 'integer');
        $this->db->manipulate($query);

        $this->insertNode($a_source_id, $a_target_id, self::POS_LAST_NODE, $a_reset_deleted_date);
    }

    /**
     * insert new node with node_id under parent node with parent_id
     * @throws InvalidArgumentException
     */
    public function insertNode(
        int $a_node_id,
        int $a_parent_id,
        int $a_pos = self::POS_LAST_NODE,
        bool $a_reset_deletion_date = false
    ): void {
        // CHECK node_id and parent_id > 0 if in main tree
        if ($this->__isMainTree()) {
            if ($a_node_id <= 1 || $a_parent_id <= 0) {
                $message = sprintf(
                    'Invalid parameters! $a_node_id: %s $a_parent_id: %s',
                    $a_node_id,
                    $a_parent_id
                );
                $this->logger->logStack(ilLogLevel::ERROR, $message);
                throw new InvalidArgumentException($message);
            }
        }
        if ($this->isInTree($a_node_id)) {
            throw new InvalidArgumentException("Node " . $a_node_id . " already in tree " .
                $this->table_tree . "!");
        }

        $this->getTreeImplementation()->insertNode($a_node_id, $a_parent_id, $a_pos);

        $this->in_tree_cache[$a_node_id] = true;

        // reset deletion date
        if ($a_reset_deletion_date) {
            ilObject::_resetDeletedDate($a_node_id);
        }
        if (isset($this->eventHandler) && ($this->eventHandler instanceof ilAppEventHandler) && $this->__isMainTree()) {
            $this->eventHandler->raise(
                'Services/Tree',
                'insertNode',
                [
                    'tree' => $this->table_tree,
                    'node_id' => $a_node_id,
                    'parent_id' => $a_parent_id
                ]
            );
        }
    }

    /**
     * get filtered subtree
     * get all subtree nodes beginning at a specific node
     * excluding specific object types and their child nodes.
     * E.g getFilteredSubTreeNodes()
     */
    public function getFilteredSubTree(int $a_node_id, array $a_filter = []): array
    {
        $node = $this->getNodeData($a_node_id);

        $first = true;
        $depth = 0;
        $filtered = [];
        foreach ($this->getSubTree($node) as $subnode) {
            if ($depth && $subnode['depth'] > $depth) {
                continue;
            }
            if (!$first && in_array($subnode['type'], $a_filter)) {
                $depth = $subnode['depth'];
                $first = false;
                continue;
            }
            $depth = 0;
            $first = false;
            $filtered[] = $subnode;
        }
        return $filtered;
    }

    /**
     * Get all ids of subnodes
     * @param int $a_ref_id
     * @return int[]
     */
    public function getSubTreeIds(int $a_ref_id): array
    {
        return $this->getTreeImplementation()->getSubTreeIds($a_ref_id);
    }

    /**
     * get all nodes in the subtree under specified node
     * @todo remove the in cache exception for lm tree
     * @todo refactor $a_type to string[]
     */
    public function getSubTree(array $a_node, bool $a_with_data = true, array $a_type = []): array
    {
        $query = $this->getTreeImplementation()->getSubTreeQuery($a_node, $a_type);

        $res = $this->db->query($query);
        $subtree = [];
        while ($row = $this->db->fetchAssoc($res)) {
            if ($a_with_data) {
                $subtree[] = $this->fetchNodeData($row);
            } else {
                $subtree[] = (int) $row['child'];
            }
            // the lm_data "hack" should be removed in the trunk during an alpha
            if ($this->__isMainTree() || $this->table_tree == "lm_tree") {
                $this->in_tree_cache[$row['child']] = true;
            }
        }
        return $subtree;
    }

    /**
     * delete node and the whole subtree under this node
     */
    public function deleteTree(array $a_node): void
    {
        if ($this->__isMainTree()) {
            // moved to trash and then deleted.
            if (!$this->__checkDelete($a_node)) {
                $this->logger->logStack(ilLogLevel::ERROR);
                throw new ilInvalidTreeStructureException('Deletion canceled due to invalid tree structure.' . print_r(
                    $a_node,
                    true
                ));
            }
        }
        $this->getTreeImplementation()->deleteTree((int) $a_node['child']);
        $this->resetInTreeCache();
    }

    /**
     * Validate parent relations of tree
     * @return int[] array of failure nodes
     */
    public function validateParentRelations(): array
    {
        return $this->getTreeImplementation()->validateParentRelations();
    }

    /**
     * get path from a given startnode to a given endnode
     * if startnode is not given the rootnode is startnode.
     * This function chooses the algorithm to be used.
     */
    public function getPathFull(int $a_endnode_id, int $a_startnode_id = 0): array
    {
        $pathIds = $this->getPathId($a_endnode_id, $a_startnode_id);

        // We retrieve the full path in a single query to improve performance
        // Abort if no path ids were found
        if (count($pathIds) == 0) {
            return [];
        }

        $inClause = 'child IN (';
        for ($i = 0; $i < count($pathIds); $i++) {
            if ($i > 0) {
                $inClause .= ',';
            }
            $inClause .= $this->db->quote($pathIds[$i], 'integer');
        }
        $inClause .= ')';

        $q = 'SELECT * ' .
            'FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() . ' ' .
            'WHERE ' . $inClause . ' ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = ' . $this->db->quote(
                $this->tree_id,
                'integer'
            ) . ' ' .
            'ORDER BY depth';
        $r = $this->db->query($q);

        $pathFull = [];
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $pathFull[] = $this->fetchNodeData($row);

            // Update cache
            if ($this->__isMainTree()) {
                $this->in_tree_cache[$row['child']] = $row['tree'] == 1;
            }
        }
        return $pathFull;
    }

    /**
     * Preload depth/parent
     * @param int[]
     */
    public function preloadDepthParent(array $a_node_ids): void
    {
        global $DIC;

        if (!$this->__isMainTree() || !$this->isCacheUsed()) {
            return;
        }

        $res = $this->db->query('SELECT t.depth, t.parent, t.child ' .
            'FROM ' . $this->table_tree . ' t ' .
            'WHERE ' . $this->db->in("child", $a_node_ids, false, "integer") .
            'AND ' . $this->tree_pk . ' = ' . $this->db->quote($this->tree_id, "integer"));
        while ($row = $this->db->fetchAssoc($res)) {
            $this->depth_cache[$row["child"]] = (int) $row["depth"];
            $this->parent_cache[$row["child"]] = (int) $row["parent"];
        }
    }

    /**
     * get path from a given startnode to a given endnode
     * if startnode is not given the rootnode is startnode
     * @return int[] all path ids from startnode to endnode
     */
    public function getPathId(int $a_endnode_id, int $a_startnode_id = 0): array
    {
        if (!$a_endnode_id) {
            $this->logger->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException(__METHOD__ . ': No endnode given!');
        }

        // path id cache
        if ($this->isCacheUsed() && isset($this->path_id_cache[$a_endnode_id][$a_startnode_id])) {
            return $this->path_id_cache[$a_endnode_id][$a_startnode_id];
        }

        $pathIds = $this->getTreeImplementation()->getPathIds($a_endnode_id, $a_startnode_id);

        if ($this->__isMainTree()) {
            $this->path_id_cache[$a_endnode_id][$a_startnode_id] = $pathIds;
        }
        return $pathIds;
    }

    /**
     * Returns the node path for the specified object reference.
     * Note: this function returns the same result as getNodePathForTitlePath,
     * but takes ref-id's as parameters.
     * This function differs from getPathFull, in the following aspects:
     * - The title of an object is not translated into the language of the user
     * - This function is significantly faster than getPathFull.
     * @return    array    ordered path info (depth,parent,child,obj_id,type,title)
     *               or null, if the node_id can not be converted into a node path.
     */
    public function getNodePath(int $a_endnode_id, int $a_startnode_id = 0): array
    {
        $pathIds = $this->getPathId($a_endnode_id, $a_startnode_id);

        // Abort if no path ids were found
        if (count($pathIds) == 0) {
            return [];
        }

        $types = [];
        $data = [];
        for ($i = 0; $i < count($pathIds); $i++) {
            $types[] = 'integer';
            $data[] = $pathIds[$i];
        }

        $query = 'SELECT t.depth,t.parent,t.child,d.obj_id,d.type,d.title ' .
            'FROM ' . $this->table_tree . ' t ' .
            'JOIN ' . $this->table_obj_reference . ' r ON r.ref_id = t.child ' .
            'JOIN ' . $this->table_obj_data . ' d ON d.obj_id = r.obj_id ' .
            'WHERE ' . $this->db->in('t.child', $data, false, 'integer') . ' ' .
            'ORDER BY t.depth ';

        $res = $this->db->queryF($query, $types, $data);

        $titlePath = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $titlePath[] = $row;
        }
        return $titlePath;
    }

    /**
     * check consistence of tree
     * all left & right values are checked if they are exists only once
     * @todo      remove exception for "check-method"
     */
    public function checkTree(): bool
    {
        $types = array('integer');
        $query = 'SELECT lft,rgt FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $this->tree_pk . ' = %s ';

        $res = $this->db->queryF($query, $types, array($this->tree_id));
        $lft = $rgt = [];
        while ($row = $this->db->fetchObject($res)) {
            $lft[] = $row->lft;
            $rgt[] = $row->rgt;
        }

        $all = array_merge($lft, $rgt);
        $uni = array_unique($all);

        if (count($all) != count($uni)) {
            $message = 'Tree is corrupted!';
            $this->logger->error($message);
            throw new ilInvalidTreeStructureException($message);
        }
        return true;
    }

    /**
     * check, if all childs of tree nodes exist in object table
     * @throws ilInvalidTreeStructureException
     */
    public function checkTreeChilds(bool $a_no_zero_child = true): bool
    {
        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $this->tree_pk . ' = %s ' .
            'ORDER BY lft';
        $r1 = $this->db->queryF($query, array('integer'), array($this->tree_id));

        while ($row = $this->db->fetchAssoc($r1)) {
            //echo "tree:".$row[$this->tree_pk].":lft:".$row["lft"].":rgt:".$row["rgt"].":child:".$row["child"].":<br>";
            if (($row["child"] == 0) && $a_no_zero_child) {
                $message = "Tree contains child with ID 0!";
                $this->logger->error($message);
                throw new ilInvalidTreeStructureException($message);
            }

            if ($this->table_obj_reference) {
                // get object reference data
                $query = 'SELECT * FROM ' . $this->table_obj_reference . ' WHERE ' . $this->ref_pk . ' = %s ';
                $r2 = $this->db->queryF($query, array('integer'), array($row['child']));

                //echo "num_childs:".$r2->numRows().":<br>";
                if ($r2->numRows() == 0) {
                    $message = "No Object-to-Reference entry found for ID " . $row["child"] . "!";
                    $this->logger->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }
                if ($r2->numRows() > 1) {
                    $message = "More Object-to-Reference entries found for ID " . $row["child"] . "!";
                    $this->logger->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }

                // get object data
                $obj_ref = $this->db->fetchAssoc($r2);

                $query = 'SELECT * FROM ' . $this->table_obj_data . ' WHERE ' . $this->obj_pk . ' = %s';
                $r3 = $this->db->queryF($query, array('integer'), array($obj_ref[$this->obj_pk]));
                if ($r3->numRows() == 0) {
                    $message = " No child found for ID " . $obj_ref[$this->obj_pk] . "!";
                    $this->logger->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }
                if ($r3->numRows() > 1) {
                    $message = "More childs found for ID " . $obj_ref[$this->obj_pk] . "!";
                    $this->logger->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }
            } else {
                // get only object data
                $query = 'SELECT * FROM ' . $this->table_obj_data . ' WHERE ' . $this->obj_pk . ' = %s';
                $r2 = $this->db->queryF($query, array('integer'), array($row['child']));
                //echo "num_childs:".$r2->numRows().":<br>";
                if ($r2->numRows() == 0) {
                    $message = "No child found for ID " . $row["child"] . "!";
                    $this->logger->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }
                if ($r2->numRows() > 1) {
                    $message = "More childs found for ID " . $row["child"] . "!";
                    $this->logger->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }
            }
        }
        return true;
    }

    /**
     * Return the current maximum depth in the tree
     */
    public function getMaximumDepth(): int
    {
        global $DIC;

        $query = 'SELECT MAX(depth) depth FROM ' . $this->table_tree;
        $res = $this->db->query($query);

        $row = $this->db->fetchAssoc($res);
        return (int) $row['depth'];
    }

    /**
     * return depth of a node in tree
     */
    public function getDepth(int $a_node_id): int
    {
        global $DIC;

        if ($a_node_id) {
            if ($this->__isMainTree()) {
                $query = 'SELECT depth FROM ' . $this->table_tree . ' ' .
                    'WHERE child = %s ';
                $res = $this->db->queryF($query, array('integer'), array($a_node_id));
                $row = $this->db->fetchObject($res);
            } else {
                $query = 'SELECT depth FROM ' . $this->table_tree . ' ' .
                    'WHERE child = %s ' .
                    'AND ' . $this->tree_pk . ' = %s ';
                $res = $this->db->queryF($query, array('integer', 'integer'), array($a_node_id, $this->tree_id));
                $row = $this->db->fetchObject($res);
            }
            return (int) ($row->depth ?? 0);
        }
        return 1;
    }

    /**
     * return all columns of tabel tree
     * @throws InvalidArgumentException
     */
    public function getNodeTreeData(int $a_node_id): array
    {
        global $DIC;

        if (!$a_node_id) {
            $this->logger->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException('Missing or empty parameter $a_node_id: ' . $a_node_id);
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE child = ' . $this->db->quote($a_node_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            return $row;
        }
        return [];
    }

    /**
     * get all information of a node.
     * get data of a specific node from tree and object_data
     * @throws InvalidArgumentException
     */
    public function getNodeData(int $a_node_id, ?int $a_tree_pk = null): array
    {
        if ($this->__isMainTree()) {
            if ($a_node_id < 1) {
                $message = 'No valid parameter given! $a_node_id: %s' . $a_node_id;
                $this->logger->error($message);
                throw new InvalidArgumentException($message);
            }
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() .
            'WHERE ' . $this->table_tree . '.child = %s ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ';
        $res = $this->db->queryF($query, array('integer', 'integer'), array(
            $a_node_id,
            $a_tree_pk === null ? $this->tree_id : $a_tree_pk
        ));
        $row = $this->db->fetchAssoc($res);
        $row[$this->tree_pk] = $this->tree_id;

        return $this->fetchNodeData($row);
    }

    /**
     * get data of parent node from tree and object_data
     */
    public function fetchNodeData(array $a_row): array
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $lng = $DIC['lng'];

        $data = $a_row;
        $data["desc"] = (string) ($a_row["description"] ?? '');  // for compability

        // multilingual support systemobjects (sys) & categories (db)
        $translation_type = '';
        if (is_object($objDefinition)) {
            $translation_type = $objDefinition->getTranslationType($data["type"] ?? '');
        }

        if ($translation_type == "sys") {
            if ($data["type"] == "rolf" && $data["obj_id"] != ROLE_FOLDER_ID) {
                $data["description"] = (string) $lng->txt("obj_" . $data["type"] . "_local_desc") . $data["title"] . $data["desc"];
                $data["desc"] = $lng->txt("obj_" . $data["type"] . "_local_desc") . $data["title"] . $data["desc"];
                $data["title"] = $lng->txt("obj_" . $data["type"] . "_local");
            } else {
                $data["title"] = $lng->txt("obj_" . $data["type"]);
                $data["description"] = $lng->txt("obj_" . $data["type"] . "_desc");
                $data["desc"] = $lng->txt("obj_" . $data["type"] . "_desc");
            }
        } elseif ($translation_type == "db") {

            // Try to retrieve object translation from cache
            $lang_code = ''; // This did never work, because it was undefined before
            if ($this->isCacheUsed() &&
                array_key_exists($data["obj_id"] . '.' . $lang_code, $this->translation_cache)) {
                $key = $data["obj_id"] . '.' . $lang_code;
                $data["title"] = $this->translation_cache[$key]['title'];
                $data["description"] = $this->translation_cache[$key]['description'];
                $data["desc"] = $this->translation_cache[$key]['desc'];
            } else {
                // Object translation is not in cache, read it from database
                $query = 'SELECT title,description FROM object_translation ' .
                    'WHERE obj_id = %s ' .
                    'AND lang_code = %s ';

                $res = $this->db->queryF($query, array('integer', 'text'), array(
                    $data['obj_id'],
                    $this->lang_code
                ));
                $row = $this->db->fetchObject($res);

                if ($row) {
                    $data["title"] = $row->title;
                    $data["description"] = ilStr::shortenTextExtended((string) $row->description, ilObject::DESC_LENGTH, true);
                    $data["desc"] = $row->description;
                }

                // Store up to 1000 object translations in cache
                if ($this->isCacheUsed() && count($this->translation_cache) < 1000) {
                    $key = $data["obj_id"] . '.' . $lang_code;
                    $this->translation_cache[$key] = [];
                    $this->translation_cache[$key]['title'] = $data["title"];
                    $this->translation_cache[$key]['description'] = $data["description"];
                    $this->translation_cache[$key]['desc'] = $data["desc"];
                }
            }
        }

        // TODO: Handle this switch by module.xml definitions
        if (isset($data['type']) && ($data['type'] == 'crsr' || $data['type'] == 'catr' || $data['type'] == 'grpr' || $data['type'] === 'prgr')) {
            $data['title'] = ilContainerReference::_lookupTitle((int) $data['obj_id']);
        }
        return $data;
    }

    /**
     * Get translation data from object cache (trigger in object cache on preload)
     * @param array $a_obj_ids object ids
     * @todo handle dependency
     */
    protected function fetchTranslationFromObjectDataCache(array $a_obj_ids): void
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        if ($this->isCacheUsed() && is_array($a_obj_ids) && is_object($ilObjDataCache)) {
            foreach ($a_obj_ids as $id) {
                $this->translation_cache[$id . '.']['title'] = $ilObjDataCache->lookupTitle((int) $id);
                $this->translation_cache[$id . '.']['description'] = $ilObjDataCache->lookupDescription((int) $id);
                $this->translation_cache[$id . '.']['desc'] =
                    $this->translation_cache[$id . '.']['description'];
            }
        }
    }

    /**
     * get all information of a node.
     * get data of a specific node from tree and object_data
     */
    public function isInTree(?int $a_node_id): bool
    {
        if (is_null($a_node_id) || !$a_node_id) {
            return false;
        }
        // is in tree cache
        if ($this->isCacheUsed() && isset($this->in_tree_cache[$a_node_id])) {
            return $this->in_tree_cache[$a_node_id];
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $this->table_tree . '.child = %s ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s';

        $res = $this->db->queryF($query, array('integer', 'integer'), array(
            $a_node_id,
            $this->tree_id
        ));

        if ($res->numRows() > 0) {
            if ($this->__isMainTree()) {
                $this->in_tree_cache[$a_node_id] = true;
            }
            return true;
        } else {
            if ($this->__isMainTree()) {
                $this->in_tree_cache[$a_node_id] = false;
            }
            return false;
        }
    }

    /**
     * get data of parent node from tree and object_data
     */
    public function getParentNodeData(int $a_node_id): array
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];

        if ($this->table_obj_reference) {
            // Use inner join instead of left join to improve performance
            $innerjoin = "JOIN " . $this->table_obj_reference . " ON v.child=" . $this->table_obj_reference . "." . $this->ref_pk . " " .
                "JOIN " . $this->table_obj_data . " ON " . $this->table_obj_reference . "." . $this->obj_pk . "=" . $this->table_obj_data . "." . $this->obj_pk . " ";
        } else {
            // Use inner join instead of left join to improve performance
            $innerjoin = "JOIN " . $this->table_obj_data . " ON v.child=" . $this->table_obj_data . "." . $this->obj_pk . " ";
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' s, ' . $this->table_tree . ' v ' .
            $innerjoin .
            'WHERE s.child = %s ' .
            'AND s.parent = v.child ' .
            'AND s.' . $this->tree_pk . ' = %s ' .
            'AND v.' . $this->tree_pk . ' = %s';
        $res = $this->db->queryF($query, array('integer', 'integer', 'integer'), array(
            $a_node_id,
            $this->tree_id,
            $this->tree_id
        ));
        $row = $this->db->fetchAssoc($res);
        if (is_array($row)) {
            return $this->fetchNodeData($row);
        }
        return [];
    }

    /**
     * checks if a node is in the path of an other node
     */
    public function isGrandChild(int $a_startnode_id, int $a_querynode_id): bool
    {
        return $this->getRelation($a_startnode_id, $a_querynode_id) == self::RELATION_PARENT;
    }

    /**
     * create a new tree
     * to do: ???
     */
    public function addTree(int $a_tree_id, int $a_node_id = -1): bool
    {
        global $DIC;

        // FOR SECURITY addTree() IS NOT ALLOWED ON MAIN TREE
        if ($this->__isMainTree()) {
            $message = sprintf(
                'Operation not allowed on main tree! $a_tree_if: %s $a_node_id: %s',
                $a_tree_id,
                $a_node_id
            );
            $this->logger->error($message);
            throw new InvalidArgumentException($message);
        }

        if ($a_node_id <= 0) {
            $a_node_id = $a_tree_id;
        }

        $query = 'INSERT INTO ' . $this->table_tree . ' (' .
            $this->tree_pk . ', child,parent,lft,rgt,depth) ' .
            'VALUES ' .
            '(%s,%s,%s,%s,%s,%s)';
        $res = $this->db->manipulateF(
            $query,
            array('integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
            array(
                $a_tree_id,
                $a_node_id,
                0,
                1,
                2,
                1
            )
        );

        return true;
    }

    /**
     * remove an existing tree
     */
    public function removeTree(int $a_tree_id): bool
    {
        if ($this->__isMainTree()) {
            $this->logger->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException('Operation not allowed on main tree');
        }
        if (!$a_tree_id) {
            $this->logger->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException('Missing parameter tree id');
        }

        $query = 'DELETE FROM ' . $this->table_tree .
            ' WHERE ' . $this->tree_pk . ' = %s ';
        $this->db->manipulateF($query, array('integer'), array($a_tree_id));
        return true;
    }

    /**
     * Move node to trash bin
     * @throws InvalidArgumentException
     * @todo remove ilUser dependency
     */
    public function moveToTrash(int $a_node_id, bool $a_set_deleted = false, int $a_deleted_by = 0): bool
    {
        global $DIC;

        $user = $DIC->user();
        if (!$a_deleted_by) {
            $a_deleted_by = $user->getId();
        }

        if (!$a_node_id) {
            $this->logger->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException('No valid parameter given! $a_node_id: ' . $a_node_id);
        }

        $query = $this->getTreeImplementation()->getSubTreeQuery($this->getNodeTreeData($a_node_id), [], false);
        $res = $this->db->query($query);

        $subnodes = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $subnodes[] = (int) $row['child'];
        }

        if (!count($subnodes)) {
            // Possibly already deleted
            return false;
        }

        if ($a_set_deleted) {
            ilObject::setDeletedDates($subnodes, $a_deleted_by);
        }
        // netsted set <=> mp
        $this->getTreeImplementation()->moveToTrash($a_node_id);
        return true;
    }

    /**
     * This is a wrapper for isSaved() with a more useful name
     */
    public function isDeleted(int $a_node_id): bool
    {
        return $this->isSaved($a_node_id);
    }

    /**
     * Use method isDeleted
     * @deprecated since 4.4.0
     */
    public function isSaved(int $a_node_id): bool
    {
        if ($this->isCacheUsed() && isset($this->is_saved_cache[$a_node_id])) {
            return $this->is_saved_cache[$a_node_id];
        }

        $query = 'SELECT ' . $this->tree_pk . ' FROM ' . $this->table_tree . ' ' .
            'WHERE child = %s ';
        $res = $this->db->queryF($query, array('integer'), array($a_node_id));
        $row = $this->db->fetchAssoc($res);

        $tree_id = $row[$this->tree_pk] ?? 0;
        if ($tree_id < 0) {
            if ($this->__isMainTree()) {
                $this->is_saved_cache[$a_node_id] = true;
            }
            return true;
        } else {
            if ($this->__isMainTree()) {
                $this->is_saved_cache[$a_node_id] = false;
            }
            return false;
        }
    }

    /**
     * Preload deleted information
     */
    public function preloadDeleted(array $a_node_ids): void
    {
        if (!is_array($a_node_ids) || !$this->isCacheUsed()) {
            return;
        }

        $query = 'SELECT ' . $this->tree_pk . ', child FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $this->db->in("child", $a_node_ids, false, "integer");

        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            if ($row[$this->tree_pk] < 0) {
                if ($this->__isMainTree()) {
                    $this->is_saved_cache[$row["child"]] = true;
                }
            } else {
                if ($this->__isMainTree()) {
                    $this->is_saved_cache[$row["child"]] = false;
                }
            }
        }
    }

    /**
     * get data saved/deleted nodes
     * @throws InvalidArgumentException
     */
    public function getSavedNodeData(int $a_parent_id): array
    {
        global $DIC;

        if (!isset($a_parent_id)) {
            $message = "No node_id given!";
            $this->logger->error($message);
            throw new InvalidArgumentException($message);
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() .
            'WHERE ' . $this->table_tree . '.' . $this->tree_pk . ' < %s ' .
            'AND ' . $this->table_tree . '.parent = %s';
        $res = $this->db->queryF($query, array('integer', 'integer'), array(
            0,
            $a_parent_id
        ));

        $saved = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $saved[] = $this->fetchNodeData($row);
        }

        return $saved;
    }

    /**
     * get object id of saved/deleted nodes
     */
    public function getSavedNodeObjIds(array $a_obj_ids): array
    {
        global $DIC;

        $query = 'SELECT ' . $this->table_obj_data . '.obj_id FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() .
            'WHERE ' . $this->table_tree . '.' . $this->tree_pk . ' < ' . $this->db->quote(0, 'integer') . ' ' .
            'AND ' . $this->db->in($this->table_obj_data . '.obj_id', $a_obj_ids, false, 'integer');
        $res = $this->db->query($query);
        $saved = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $saved[] = (int) $row['obj_id'];
        }

        return $saved;
    }

    /**
     * get parent id of given node
     * @throws InvalidArgumentException
     */
    public function getParentId(int $a_node_id): ?int
    {
        global $DIC;
        if ($this->__isMainTree()) {
            $query = 'SELECT parent FROM ' . $this->table_tree . ' ' .
                'WHERE child = %s ';
            $res = $this->db->queryF(
                $query,
                ['integer'],
                [$a_node_id]
            );
        } else {
            $query = 'SELECT parent FROM ' . $this->table_tree . ' ' .
                'WHERE child = %s ' .
                'AND ' . $this->tree_pk . ' = %s ';
            $res = $this->db->queryF($query, array('integer', 'integer'), array(
                $a_node_id,
                $this->tree_id
            ));
        }

        if ($row = $this->db->fetchObject($res)) {
            return (int) $row->parent;
        }
        return null;
    }

    /**
     * get left value of given node
     * @throws InvalidArgumentException
     * @todo move to tree implementation and throw NotImplementedException for materialized path implementation
     */
    public function getLeftValue(int $a_node_id): int
    {
        global $DIC;

        if (!isset($a_node_id)) {
            $message = "No node_id given!";
            $this->logger->error($message);
            throw new InvalidArgumentException($message);
        }

        $query = 'SELECT lft FROM ' . $this->table_tree . ' ' .
            'WHERE child = %s ' .
            'AND ' . $this->tree_pk . ' = %s ';
        $res = $this->db->queryF($query, array('integer', 'integer'), array(
            $a_node_id,
            $this->tree_id
        ));
        $row = $this->db->fetchObject($res);
        return (int) $row->lft;
    }

    /**
     * get sequence number of node in sibling sequence
     * @throws InvalidArgumentException
     * @todo move to tree implementation and throw NotImplementedException for materialized path implementation
     */
    public function getChildSequenceNumber(array $a_node, string $type = ""): int
    {
        if (!isset($a_node)) {
            $message = "No node_id given!";
            $this->logger->error($message);
            throw new InvalidArgumentException($message);
        }

        if ($type) {
            $query = 'SELECT count(*) cnt FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft <= %s ' .
                'AND type = %s ' .
                'AND parent = %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ';

            $res = $this->db->queryF($query, array('integer', 'text', 'integer', 'integer'), array(
                $a_node['lft'],
                $type,
                $a_node['parent'],
                $this->tree_id
            ));
        } else {
            $query = 'SELECT count(*) cnt FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft <= %s ' .
                'AND parent = %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ';

            $res = $this->db->queryF($query, array('integer', 'integer', 'integer'), array(
                $a_node['lft'],
                $a_node['parent'],
                $this->tree_id
            ));
        }
        $row = $this->db->fetchAssoc($res);
        return (int) $row["cnt"];
    }

    public function readRootId(): int
    {
        $query = 'SELECT child FROM ' . $this->table_tree . ' ' .
            'WHERE parent = %s ' .
            'AND ' . $this->tree_pk . ' = %s ';
        $res = $this->db->queryF($query, array('integer', 'integer'), array(
            0,
            $this->tree_id
        ));
        $row = $this->db->fetchObject($res);
        $this->root_id = (int) $row->child;
        return $this->root_id;
    }

    public function getRootId(): int
    {
        return $this->root_id;
    }

    public function setRootId(int $a_root_id): void
    {
        $this->root_id = $a_root_id;
    }

    public function getTreeId(): int
    {
        return $this->tree_id;
    }

    public function setTreeId(int $a_tree_id): void
    {
        $this->tree_id = $a_tree_id;
    }

    /**
     * get node data of successor node
     * @throws InvalidArgumentException
     * @todo  move to tree implementation and throw NotImplementedException for materialized path implementation
     * @fixme fix return false
     */
    public function fetchSuccessorNode(int $a_node_id, string $a_type = ""): ?array
    {
        // get lft value for current node
        $query = 'SELECT lft FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $this->table_tree . '.child = %s ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ';
        $res = $this->db->queryF($query, array('integer', 'integer'), array(
            $a_node_id,
            $this->tree_id
        ));
        $curr_node = $this->db->fetchAssoc($res);

        if ($a_type) {
            $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft > %s ' .
                'AND ' . $this->table_obj_data . '.type = %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ' .
                'ORDER BY lft ';
            $this->db->setLimit(1, 0);
            $res = $this->db->queryF($query, array('integer', 'text', 'integer'), array(
                $curr_node['lft'],
                $a_type,
                $this->tree_id
            ));
        } else {
            $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft > %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ' .
                'ORDER BY lft ';
            $this->db->setLimit(1, 0);
            $res = $this->db->queryF($query, array('integer', 'integer'), array(
                $curr_node['lft'],
                $this->tree_id
            ));
        }

        if ($res->numRows() < 1) {
            return null;
        } else {
            $row = $this->db->fetchAssoc($res);
            return $this->fetchNodeData($row);
        }
    }

    /**
     * get node data of predecessor node
     * @throws InvalidArgumentException
     * @todo  move to tree implementation and throw NotImplementedException for materialized path implementation
     * @fixme fix return false
     */
    public function fetchPredecessorNode(int $a_node_id, string $a_type = ""): ?array
    {
        if (!isset($a_node_id)) {
            $message = "No node_id given!";
            $this->logger->error($message);
            throw new InvalidArgumentException($message);
        }

        // get lft value for current node
        $query = 'SELECT lft FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $this->table_tree . '.child = %s ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ';
        $res = $this->db->queryF($query, array('integer', 'integer'), array(
            $a_node_id,
            $this->tree_id
        ));

        $curr_node = $this->db->fetchAssoc($res);

        if ($a_type) {
            $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft < %s ' .
                'AND ' . $this->table_obj_data . '.type = %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ' .
                'ORDER BY lft DESC';
            $this->db->setLimit(1, 0);
            $res = $this->db->queryF($query, array('integer', 'text', 'integer'), array(
                $curr_node['lft'],
                $a_type,
                $this->tree_id
            ));
        } else {
            $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft < %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ' .
                'ORDER BY lft DESC';
            $this->db->setLimit(1, 0);
            $res = $this->db->queryF($query, array('integer', 'integer'), array(
                $curr_node['lft'],
                $this->tree_id
            ));
        }

        if ($res->numRows() < 1) {
            return null;
        } else {
            $row = $this->db->fetchAssoc($res);
            return $this->fetchNodeData($row);
        }
    }

    /**
     * Wrapper for renumber. This method locks the table tree
     * (recursive)
     */
    public function renumber(int $node_id = 1, int $i = 1): int
    {
        $renumber_callable = function (ilDBInterface $db) use ($node_id, $i, &$return) {
            $return = $this->__renumber($node_id, $i);
        };

        // LOCKED ###################################
        if ($this->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock($this->table_tree);

            $ilAtomQuery->addQueryCallable($renumber_callable);
            $ilAtomQuery->run();
        } else {
            $renumber_callable($this->db);
        }
        return $return;
    }

    /**
     * This method is private. Always call ilTree->renumber() since it locks the tree table
     * renumber left/right values and close the gaps in numbers
     * (recursive)
     */
    private function __renumber(int $node_id = 1, int $i = 1): int
    {
        if ($this->isRepositoryTree()) {
            $query = 'UPDATE ' . $this->table_tree . ' SET lft = %s WHERE child = %s';
            $this->db->manipulateF(
                $query,
                array('integer', 'integer'),
                array(
                    $i,
                    $node_id
                )
            );
        } else {
            $query = 'UPDATE ' . $this->table_tree . ' SET lft = %s WHERE child = %s AND tree = %s';
            $this->db->manipulateF(
                $query,
                array('integer', 'integer', 'integer'),
                array(
                    $i,
                    $node_id,
                    $this->tree_id
                )
            );
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE parent = ' . $this->db->quote($node_id, 'integer') . ' ' .
            'ORDER BY lft';
        $res = $this->db->query($query);

        $childs = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $childs[] = (int) $row->child;
        }

        foreach ($childs as $child) {
            $i = $this->__renumber($child, $i + 1);
        }
        $i++;

        // Insert a gap at the end of node, if the node has children
        if (count($childs) > 0) {
            $i += $this->gap * 2;
        }

        if ($this->isRepositoryTree()) {
            $query = 'UPDATE ' . $this->table_tree . ' SET rgt = %s WHERE child = %s';
            $res = $this->db->manipulateF(
                $query,
                array('integer', 'integer'),
                array(
                    $i,
                    $node_id
                )
            );
        } else {
            $query = 'UPDATE ' . $this->table_tree . ' SET rgt = %s WHERE child = %s AND tree = %s';
            $res = $this->db->manipulateF($query, array('integer', 'integer', 'integer'), array(
                $i,
                $node_id,
                $this->tree_id
            ));
        }
        return $i;
    }

    /**
     * Check for parent type
     * e.g check if a folder (ref_id 3) is in a parent course obj => checkForParentType(3,'crs');
     */
    public function checkForParentType(int $a_ref_id, string $a_type, bool $a_exclude_source_check = false): int
    {
        // #12577
        $cache_key = $a_ref_id . '.' . $a_type . '.' . ((int) $a_exclude_source_check);

        // Try to return a cached result
        if ($this->isCacheUsed() &&
            array_key_exists($cache_key, $this->parent_type_cache)) {
            return (int) $this->parent_type_cache[$cache_key];
        }

        // Store up to 1000 results in cache
        $do_cache = ($this->__isMainTree() && count($this->parent_type_cache) < 1000);

        // ref_id is not in tree
        if (!$this->isInTree($a_ref_id)) {
            if ($do_cache) {
                $this->parent_type_cache[$cache_key] = false;
            }
            return 0;
        }

        $path = array_reverse($this->getPathFull($a_ref_id));

        // remove first path entry as it is requested node
        if ($a_exclude_source_check) {
            array_shift($path);
        }

        foreach ($path as $node) {
            // found matching parent
            if ($node["type"] == $a_type) {
                if ($do_cache) {
                    $this->parent_type_cache[$cache_key] = (int) $node["child"];
                }
                return (int) $node["child"];
            }
        }

        if ($do_cache) {
            $this->parent_type_cache[$cache_key] = false;
        }
        return 0;
    }

    /**
     * STATIC METHOD
     * Removes a single entry from a tree. The tree structure is NOT updated!
     * @throws InvalidArgumentException
     */
    public static function _removeEntry(int $a_tree, int $a_child, string $a_db_table = "tree"): void
    {
        global $DIC;

        $db = $DIC->database();

        if ($a_db_table === 'tree') {
            if ($a_tree == 1 && $a_child == ROOT_FOLDER_ID) {
                $message = sprintf(
                    'Tried to delete root node! $a_tree: %s $a_child: %s',
                    $a_tree,
                    $a_child
                );
                ilLoggerFactory::getLogger('tree')->error($message);
                throw new InvalidArgumentException($message);
            }
        }

        $query = 'DELETE FROM ' . $a_db_table . ' ' .
            'WHERE tree = %s ' .
            'AND child = %s ';
        $res = $db->manipulateF($query, array('integer', 'integer'), array(
            $a_tree,
            $a_child
        ));
    }

    /**
     * Check if operations are done on main tree
     */
    public function __isMainTree(): bool
    {
        return $this->table_tree === 'tree';
    }

    /**
     * Check for deleteTree()
     * compares a subtree of a given node by checking lft, rgt against parent relation
     * @throws ilInvalidTreeStructureException
     * @deprecated since 4.4.0
     */
    public function __checkDelete(array $a_node): bool
    {
        $query = $this->getTreeImplementation()->getSubTreeQuery($a_node, [], false);
        $this->logger->debug($query);
        $res = $this->db->query($query);

        $counter = (int) $lft_childs = [];
        while ($row = $this->db->fetchObject($res)) {
            $lft_childs[$row->child] = (int) $row->parent;
            ++$counter;
        }

        // CHECK FOR DUPLICATE CHILD IDS
        if ($counter != count($lft_childs)) {
            $message = 'Duplicate entries for "child" in maintree! $a_node_id: ' . $a_node['child'];

            $this->logger->error($message);
            throw new ilInvalidTreeStructureException($message);
        }

        // GET SUBTREE BY PARENT RELATION
        $parent_childs = [];
        $this->__getSubTreeByParentRelation($a_node['child'], $parent_childs);
        $this->__validateSubtrees($lft_childs, $parent_childs);

        return true;
    }

    /**
     * @throws ilInvalidTreeStructureException
     * @deprecated since 4.4.0
     */
    public function __getSubTreeByParentRelation(int $a_node_id, array &$parent_childs): bool
    {
        // GET PARENT ID
        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE child = %s ' .
            'AND tree = %s ';
        $res = $this->db->queryF($query, array('integer', 'integer'), array(
            $a_node_id,
            $this->tree_id
        ));

        $counter = 0;
        while ($row = $this->db->fetchObject($res)) {
            $parent_childs[$a_node_id] = (int) $row->parent;
            ++$counter;
        }
        // MULTIPLE ENTRIES
        if ($counter > 1) {
            $message = 'Multiple entries in maintree! $a_node_id: ' . $a_node_id;

            $this->logger->error($message);
            throw new ilInvalidTreeStructureException($message);
        }

        // GET ALL CHILDS
        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE parent = %s ';
        $res = $this->db->queryF($query, array('integer'), array($a_node_id));

        while ($row = $this->db->fetchObject($res)) {
            // RECURSION
            $this->__getSubTreeByParentRelation((int) $row->child, $parent_childs);
        }
        return true;
    }

    /**
     * @param array $lft_childs
     * @param array $parent_childs
     * @return bool
     * @throws ilInvalidTreeStructureException
     * @deprecated since 4.4.0
     */
    public function __validateSubtrees(array &$lft_childs, array $parent_childs): bool
    {
        // SORT BY KEY
        ksort($lft_childs);
        ksort($parent_childs);

        $this->logger->debug('left childs ' . print_r($lft_childs, true));
        $this->logger->debug('parent childs ' . print_r($parent_childs, true));

        if (count($lft_childs) != count($parent_childs)) {
            $message = '(COUNT) Tree is corrupted! Left/Right subtree does not comply with parent relation';
            $this->logger->error($message);
            throw new ilInvalidTreeStructureException($message);
        }

        foreach ($lft_childs as $key => $value) {
            if ($parent_childs[$key] != $value) {
                $message = '(COMPARE) Tree is corrupted! Left/Right subtree does not comply with parent relation';
                $this->logger->error($message);
                throw new ilInvalidTreeStructureException($message);
            }
            if ($key == ROOT_FOLDER_ID) {
                $message = '(ROOT_FOLDER) Tree is corrupted! Tried to delete root folder';
                $this->logger->error($message);
                throw new ilInvalidTreeStructureException($message);
            }
        }
        return true;
    }

    /**
     * Move Tree Implementation
     * @access    public
     * @param int source ref_id
     * @param int target ref_id
     * @param int location ilTree::POS_LAST_NODE or ilTree::POS_FIRST_NODE
     * @return int
     */
    public function moveTree(int $a_source_id, int $a_target_id, int $a_location = self::POS_LAST_NODE): void
    {
        $old_parent_id = $this->getParentId($a_source_id);
        $this->getTreeImplementation()->moveTree($a_source_id, $a_target_id, $a_location);
        if (isset($GLOBALS['DIC']["ilAppEventHandler"]) && $this->__isMainTree()) {
            $GLOBALS['DIC']['ilAppEventHandler']->raise(
                "Services/Tree",
                "moveTree",
                array(
                    'tree' => $this->table_tree,
                    'source_id' => $a_source_id,
                    'target_id' => $a_target_id,
                    'old_parent_id' => $old_parent_id
                )
            );
        }
    }

    /**
     * This method is used for change existing objects
     * and returns all necessary information for this action.
     * The former use of ilTree::getSubtree needs to much memory.
     */
    public function getRbacSubtreeInfo(int $a_endnode_id): array
    {
        return $this->getTreeImplementation()->getSubtreeInfo($a_endnode_id);
    }

    /**
     * Get tree subtree query
     */
    public function getSubTreeQuery(
        int $a_node_id,
        array $a_fields = [],
        array $a_types = [],
        bool $a_force_join_reference = false
    ): string {
        return $this->getTreeImplementation()->getSubTreeQuery(
            $this->getNodeTreeData($a_node_id),
            $a_types,
            $a_force_join_reference,
            $a_fields
        );
    }

    public function getTrashSubTreeQuery(
        int $a_node_id,
        array $a_fields = [],
        array $a_types = [],
        bool $a_force_join_reference = false
    ): string {
        return $this->getTreeImplementation()->getTrashSubTreeQuery(
            $this->getNodeTreeData($a_node_id),
            $a_types,
            $a_force_join_reference,
            $a_fields
        );
    }

    /**
     * get all node ids in the subtree under specified node id, filter by object ids
     *
     * @param int[] $a_obj_ids
     * @param string[] $a_fields
     */
    public function getSubTreeFilteredByObjIds(int $a_node_id, array $a_obj_ids, array $a_fields = []): array
    {
        $node = $this->getNodeData($a_node_id);
        if (!count($node)) {
            return [];
        }

        $res = [];

        $query = $this->getTreeImplementation()->getSubTreeQuery($node, [], true, array($this->ref_pk));

        $fields = '*';
        if (count($a_fields)) {
            $fields = implode(',', $a_fields);
        }

        $query = "SELECT " . $fields .
            " FROM " . $this->getTreeTable() .
            " " . $this->buildJoin() .
            " WHERE " . $this->getTableReference() . "." . $this->ref_pk . " IN (" . $query . ")" .
            " AND " . $this->db->in($this->getObjectDataTable() . "." . $this->obj_pk, $a_obj_ids, false, "integer");
        $set = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($set)) {
            $res[] = $row;
        }

        return $res;
    }

    public function deleteNode(int $a_tree_id, int $a_node_id): void
    {
        $query = 'DELETE FROM tree where ' .
            'child = ' . $this->db->quote($a_node_id, 'integer') . ' ' .
            'AND tree = ' . $this->db->quote($a_tree_id, 'integer');
        $this->db->manipulate($query);

        $this->eventHandler->raise(
            "Services/Tree",
            "deleteNode",
            [
                'tree' => $this->table_tree,
                'node_id' => $a_node_id,
                'tree_id' => $a_tree_id
            ]
        );
    }

    /**
     * Lookup object types in trash
     * @return string[]
     */
    public function lookupTrashedObjectTypes(): array
    {
        $query = 'SELECT DISTINCT(o.type) ' . $this->db->quoteIdentifier('type') .
            ' FROM tree t JOIN object_reference r ON child = r.ref_id ' .
            'JOIN object_data o on r.obj_id = o.obj_id ' .
            'WHERE tree < ' . $this->db->quote(0, 'integer') . ' ' .
            'AND child = -tree ' .
            'GROUP BY o.type';
        $res = $this->db->query($query);

        $types_deleted = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $types_deleted[] = (string) $row->type;
        }
        return $types_deleted;
    }

    /**
     * check if current tree instance operates on repository tree table
     */
    public function isRepositoryTree(): bool
    {
        return $this->table_tree == 'tree';
    }
} // END class.tree
