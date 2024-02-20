<?php

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

declare(strict_types=1);

/**
 * Base class for nested set path based trees
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesTree
 */
class ilNestedSetTree implements ilTreeImplementation
{
    public const POS_LAST_NODE = -2;
    public const POS_FIRST_NODE = -1;

    public const RELATION_CHILD = 1;
    public const RELATION_PARENT = 2;
    protected const DEFAULT_GAP = 50;
    protected const DEFAULT_LANGUAGE = 'en';

    protected ilDBInterface $db;
    protected ilLogger $logger;
    private string $lang_code;
    protected int $tree_id;
    protected int $root_id;
    protected int $parent_id;
    protected string $table_tree;
    protected string $tree_pk;
    protected string $ref_pk;
    protected string $obj_pk;
    protected int $gap;
    protected array $depth_cache;
    protected array $parent_cache;
    protected bool $use_cache;
    protected string $table_obj_data;
    protected string $table_obj_reference;
    protected array $translation_cache = [];
    protected array $in_tree_cache = [];

    public function __construct(
        int $tree_id,
        int $root_id = 0,
        ilDBInterface $db = null
    ) {
        global $DIC;
        $this->db = $db ?? $DIC->database();
        $this->logger = ilLoggerFactory::getLogger('tree');
        $this->lang_code = self::DEFAULT_LANGUAGE;

        if ($root_id > 0) {
            $this->root_id = $root_id;
        } else {
            $this->root_id = ROOT_FOLDER_ID;
        }

        $this->tree_id = $tree_id;
        $this->table_tree = 'tree';
        $this->ref_pk = 'ref_id';
        $this->obj_pk = 'obj_id';
        $this->tree_pk = 'tree';
        $this->table_obj_reference = 'object_reference';
        $this->table_obj_data = 'object_data';

        $this->use_cache = true;
        $this->gap = self::DEFAULT_GAP;
    }

    public function setObjectTablePK(string $a_column_name): void
    {
        $this->obj_pk = $a_column_name;
    }

    public function setTableNames(
        string $a_table_tree,
        string $a_table_obj_data,
        string $a_table_obj_reference = ""
    ): void {
        $this->table_tree = $a_table_tree;
        $this->table_obj_data = $a_table_obj_data;
        $this->table_obj_reference = $a_table_obj_reference;
    }

    public function setTreeTablePK(string $column_name): void
    {
        $this->tree_pk = $column_name;
    }

    public function setReferenceTablePK(string $a_column_name): void
    {
        $this->ref_pk = $a_column_name;
    }

    public function setRootId(int $a_root_id): void
    {
        $this->root_id = $a_root_id;
    }

    public function getTreeId(): int
    {
        return $this->tree_id;
    }

    public function getSubTreeIds(int $a_node_id): array
    {
        $query = 'SELECT s.child FROM ' .
            $this->table_tree . ' s, ' .
            $this->table_tree . ' t ' .
            'WHERE t.child = %s ' .
            'AND s.lft > t.lft ' .
            'AND s.rgt < t.rgt ' .
            'AND s.' . $this->tree_pk . ' = %s';

        $res = $this->db->queryF(
            $query,
            array('integer', 'integer'),
            array($a_node_id, $this->tree_id)
        );
        $childs = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $childs[] = (int) $row->child;
        }
        return $childs;
    }

    public function getTrashSubTreeQuery(
        array $a_node,
        array $a_types,
        bool $a_force_join_reference = true,
        array $a_fields = []
    ): string {
        $type_str = '';
        if (is_array($a_types)) {
            if ($a_types) {
                $type_str = "AND " . $this->db->in(
                    $this->table_obj_data . ".type",
                    $a_types,
                    false,
                    "text"
                );
            }
        }

        $join = '';
        if ($type_str || $a_force_join_reference) {
            $join = $this->buildJoin();
        }

        $fields = '* ';
        if (count($a_fields)) {
            $fields = implode(',', $a_fields);
        }

        $query = 'SELECT ' .
            $fields . ' ' .
            "FROM " . $this->table_tree . " " .
            $join . ' ' .
            "WHERE " . $this->table_tree . '.lft ' .
            'BETWEEN ' . $this->db->quote($a_node['lft'], 'integer') . ' ' .
            'AND ' . $this->db->quote($a_node['rgt'], 'integer') . ' ' .
            "AND " . $this->table_tree . "." . $this->tree_pk . ' < 0 ' .
            $type_str . ' ' .
            "ORDER BY " . $this->table_tree . ".lft";

        return $query;
    }

    public function getSubTreeQuery(
        array $a_node,
        array $a_types = [],
        bool $a_force_join_reference = true,
        array $a_fields = []
    ): string {
        $type_str = '';
        if (count($a_types)) {
            if ($a_types) {
                $type_str = "AND " . $this->db->in(
                    $this->table_obj_data . ".type",
                    $a_types,
                    false,
                    "text"
                );
            }
        }


        $join = '';
        if ($type_str || $a_force_join_reference) {
            $join = $this->buildJoin();
        }

        $fields = '* ';
        if (count($a_fields)) {
            $fields = implode(',', $a_fields);
        }

        $query = 'SELECT ' .
            $fields . ' ' .
            "FROM " . $this->table_tree . " " .
            $join . ' ' .
            "WHERE " . $this->table_tree . '.lft ' .
            'BETWEEN ' . $this->db->quote($a_node['lft'], 'integer') . ' ' .
            'AND ' . $this->db->quote($a_node['rgt'], 'integer') . ' ' .
            "AND " . $this->table_tree . "." . $this->tree_pk . " = " . $this->db->quote(
                $this->tree_id,
                'integer'
            ) . ' ' .
            $type_str . ' ' .
            "ORDER BY " . $this->table_tree . ".lft";
        return $query;
    }

    public function getRelation(array $a_node_a, array $a_node_b): int
    {
        if ($a_node_a === [] || $a_node_b === []) {
            return ilTree::RELATION_NONE;
        }
        if ($a_node_a['child'] == $a_node_b['child']) {
            return ilTree::RELATION_EQUALS;
        }
        if ($a_node_a['lft'] < $a_node_b['lft'] && $a_node_a['rgt'] > $a_node_b['rgt']) {
            return ilTree::RELATION_PARENT;
        }
        if ($a_node_b['lft'] < $a_node_a['lft'] && $a_node_b['rgt'] > $a_node_a['rgt']) {
            return ilTree::RELATION_CHILD;
        }

        // if node is also parent of node b => sibling
        if ($a_node_a['parent'] == $a_node_b['parent']) {
            return ilTree::RELATION_SIBLING;
        }
        return ilTree::RELATION_NONE;
    }

    public function getPathIds(int $a_endnode, int $a_startnode = 0): array
    {
        return $this->getPathIdsUsingAdjacencyMap($a_endnode, $a_startnode);
    }

    /**
     * get path from a given startnode to a given endnode
     * if startnode is not given the rootnode is startnode
     *
     * @return int[]
     */
    protected function getPathIdsUsingAdjacencyMap(int $a_endnode_id, int $a_startnode_id = 0): array
    {
        // The adjacency map algorithm is harder to implement than the nested sets algorithm.
        // This algorithms performs an index search for each of the path element.
        // This algorithms performs well for large trees which are not deeply nested.

        // The $takeId variable is used, to determine if a given id shall be included in the path
        $takeId = $a_startnode_id == 0;

        $depth_cache = $this->depth_cache;
        $parent_cache = $this->parent_cache;

        if (
            $this->__isMainTree() &&
            isset($depth_cache[$a_endnode_id]) &&
            isset($parent_cache[$a_endnode_id])) {
            $nodeDepth = $depth_cache[$a_endnode_id];
            $parentId = $parent_cache[$a_endnode_id];
        } else {
            $nodeDepth = $this->getDepth($a_endnode_id);
            $parentId = $this->getParentId($a_endnode_id);
        }

        // Fetch the node ids. For shallow depths we can fill in the id's directly.
        $pathIds = array();

        // backward compatible check for nodes not in tree
        if (!$nodeDepth) {
            return array();
        } elseif ($nodeDepth == 1) {
            $takeId = $takeId || $a_endnode_id == $a_startnode_id;
            if ($takeId) {
                $pathIds[] = $a_endnode_id;
            }
        } elseif ($nodeDepth == 2) {
            $takeId = $takeId || $parentId == $a_startnode_id;
            if ($takeId) {
                $pathIds[] = $parentId;
            }
            $takeId = $takeId || $a_endnode_id == $a_startnode_id;
            if ($takeId) {
                $pathIds[] = $a_endnode_id;
            }
        } elseif ($nodeDepth == 3) {
            $takeId = $takeId || $this->root_id == $a_startnode_id;
            if ($takeId) {
                $pathIds[] = $this->root_id;
            }
            $takeId = $takeId || $parentId == $a_startnode_id;
            if ($takeId) {
                $pathIds[] = $parentId;
            }
            $takeId = $takeId || $a_endnode_id == $a_startnode_id;
            if ($takeId) {
                $pathIds[] = $a_endnode_id;
            }
        } elseif ($nodeDepth < 32) {
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
            for ($i = 1; $i < $nodeDepth - 2; $i++) {
                $qSelect .= ', t' . $i . '.parent c' . $i;
                if ($this->__isMainTree()) {
                    $qJoin .= ' JOIN ' . $this->table_tree . ' t' . $i . ' ON ' .
                        't' . $i . '.child=t' . ($i - 1) . '.parent ';
                } else {
                    $qJoin .= ' JOIN ' . $this->table_tree . ' t' . $i . ' ON ' .
                        't' . $i . '.child=t' . ($i - 1) . '.parent AND ' .
                        't' . $i . '.' . $this->tree_pk . ' = ' . $this->tree_id;
                }
            }

            if ($this->__isMainTree()) {
                $types = array('integer');
                $data = array($parentId);
                $query = 'SELECT ' . $qSelect . ' ' .
                    'FROM ' . $this->table_tree . ' t0 ' . $qJoin . ' ' .
                    'WHERE t0.child = %s ';
            } else {
                $types = array('integer', 'integer');
                $data = array($this->tree_id, $parentId);
                $query = 'SELECT ' . $qSelect . ' ' .
                    'FROM ' . $this->table_tree . ' t0 ' . $qJoin . ' ' .
                    'WHERE t0.' . $this->tree_pk . ' = %s ' .
                    'AND t0.child = %s ';
            }

            $this->db->setLimit(1, 0);
            $res = $this->db->queryF($query, $types, $data);

            if ($res->numRows() == 0) {
                return array();
            }

            $row = $this->db->fetchAssoc($res);

            $takeId = $takeId || $this->root_id == $a_startnode_id;
            if ($takeId) {
                $pathIds[] = $this->root_id;
            }
            for ($i = $nodeDepth - 4; $i >= 0; $i--) {
                $takeId = $takeId || $row['c' . $i] == $a_startnode_id;
                if ($takeId) {
                    $pathIds[] = (int) $row['c' . $i];
                }
            }
            $takeId = $takeId || $parentId == $a_startnode_id;
            if ($takeId) {
                $pathIds[] = $parentId;
            }
            $takeId = $takeId || $a_endnode_id == $a_startnode_id;
            if ($takeId) {
                $pathIds[] = $a_endnode_id;
            }
        } else {
            // Fall back to nested sets tree for extremely deep tree structures
            return $this->getPathIdsUsingNestedSets($a_endnode_id, $a_startnode_id);
        }
        return $pathIds;
    }

    /**
     * get path from a given startnode to a given endnode
     * if startnode is not given the rootnode is startnode using nested sets
     * @return int[]
     */
    protected function getPathIdsUsingNestedSets(int $a_endnode_id, int $a_startnode_id = 0): array
    {
        // The nested sets algorithm is very easy to implement.
        // Unfortunately it always does a full table space scan to retrieve the path
        // regardless whether indices on lft and rgt are set or not.
        // (At least, this is what happens on MySQL 4.1).
        // This algorithms performs well for small trees which are deeply nested.

        if ($this->__isMainTree()) {
            $fields = array('integer');
            $data = array($a_endnode_id);
            $query = "SELECT T2.child " .
                "FROM " . $this->table_tree . " T1, " . $this->table_tree . " T2 " .
                "WHERE T1.child = %s " .
                "AND T1.lft BETWEEN T2.lft AND T2.rgt " .
                "ORDER BY T2.depth";
        } else {
            $fields = array('integer', 'integer', 'integer');
            $data = array($a_endnode_id, $this->tree_id, $this->tree_id);
            $query = "SELECT T2.child " .
                "FROM " . $this->table_tree . " T1, " . $this->table_tree . " T2 " .
                "WHERE T1.child = %s " .
                "AND T1.lft BETWEEN T2.lft AND T2.rgt " .
                "AND T1." . $this->tree_pk . " = %s " .
                "AND T2." . $this->tree_pk . " = %s " .
                "ORDER BY T2.depth";
        }

        $res = $this->db->queryF($query, $fields, $data);

        $takeId = $a_startnode_id == 0;
        $pathIds = [];
        while ($row = $this->db->fetchAssoc($res)) {
            if ($takeId || $row['child'] == $a_startnode_id) {
                $takeId = true;
                $pathIds[] = (int) $row['child'];
            }
        }
        return $pathIds;
    }

    /**
     * @param int $a_endnode_id
     * @return array<int, array{lft: int, rgt: int, child: int, type: string}>
     */
    public function getSubtreeInfo(int $a_endnode_id): array
    {
        $query = "SELECT t2.lft lft, t2.rgt rgt, t2.child child, t2.parent parent, type " .
            "FROM " . $this->table_tree . " t1 " .
            "JOIN " . $this->table_tree . " t2 ON (t2.lft BETWEEN t1.lft AND t1.rgt) " .
            "JOIN " . $this->table_obj_reference . " obr ON t2.child = obr.ref_id " .
            "JOIN " . $this->table_obj_data . " obd ON obr.obj_id = obd.obj_id " .
            "WHERE t1.child = " . $this->db->quote($a_endnode_id, 'integer') . " " .
            "AND t1." . $this->tree_pk . " = " . $this->db->quote(
                $this->tree_id,
                'integer'
            ) . " " .
            "AND t2." . $this->tree_pk . " = " . $this->db->quote(
                $this->tree_id,
                'integer'
            ) . " " .
            "ORDER BY t2.lft";

        $res = $this->db->query($query);
        $nodes = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $nodes[(int) $row->child]['lft'] = (int) $row->lft;
            $nodes[(int) $row->child]['rgt'] = (int) $row->rgt;
            $nodes[(int) $row->child]['child'] = (int) $row->child;
            $nodes[(int) $row->child]['parent'] = (int) $row->parent;
            $nodes[(int) $row->child]['type'] = (string) $row->type;
        }
        return $nodes;
    }

    /**
     * get sequence number of node in sibling sequence
     * @throws InvalidArgumentException
     */
    public function getChildSequenceNumber(array $a_node, string $type = ""): int
    {
        if (!isset($a_node)) {
            $message = "No node_id given!";
            ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::ERROR);
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

    protected function getDepth(int $node_id): int
    {
        global $DIC;

        if ($node_id) {
            if ($this->__isMainTree()) {
                $query = 'SELECT depth FROM ' . $this->table_tree . ' ' .
                    'WHERE child = %s ';
                $res = $this->db->queryF($query, array('integer'), array($node_id));
                $row = $this->db->fetchObject($res);
            } else {
                $query = 'SELECT depth FROM ' . $this->table_tree . ' ' .
                    'WHERE child = %s ' .
                    'AND ' . $this->tree_pk . ' = %s ';
                $res = $this->db->queryF($query, array('integer', 'integer'), array($node_id, $this->tree_id));
                $row = $this->db->fetchObject($res);
            }
            return (int) ($row->depth ?? 0);
        }

        return 1;
    }

    protected function getNodeTreeData(int $node_id): array
    {
        global $DIC;

        if (!$node_id) {
            $this->logger->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException('Missing or empty parameter $node_id: ' . $node_id);
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE child = ' . $this->db->quote($node_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            return $row;
        }

        return [];
    }

    public function getParentId(int $node_id): ?int
    {
        global $DIC;
        if ($this->__isMainTree()) {
            $query = 'SELECT parent FROM ' . $this->table_tree . ' ' .
                'WHERE child = %s ';
            $res = $this->db->queryF(
                $query,
                ['integer'],
                [$node_id]
            );
        } else {
            $query = 'SELECT parent FROM ' . $this->table_tree . ' ' .
                'WHERE child = %s ' .
                'AND ' . $this->tree_pk . ' = %s ';
            $res = $this->db->queryF($query, array('integer', 'integer'), array(
                $node_id,
                $this->tree_id
            ));
        }

        if ($row = $this->db->fetchObject($res)) {
            return (int) $row->parent;
        }

        return null;
    }

    public function getNodeData(int $node_id, ?int $tree_pk = null): array
    {
        if ($this->__isMainTree()) {
            if ($node_id < 1) {
                $message = 'No valid parameter given! $node_id: %s' . $node_id;
                $this->logger->error($message);
                throw new InvalidArgumentException($message);
            }
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() .
            'WHERE ' . $this->table_tree . '.child = %s ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ';
        $res = $this->db->queryF($query, array('integer', 'integer'), array(
            $node_id,
            $tree_pk === null ? $this->tree_id : $tree_pk
        ));
        $row = $this->db->fetchAssoc($res);
        $row[$this->tree_pk] = $this->tree_id;

        return $this->fetchNodeData($row);
    }

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
     * get all nodes in the subtree under specified node
     */
    public function getSubTree(array $node, bool $with_data = true, array $type = []): array
    {
        $query = $this->getSubTreeQuery($node, $type);
        $res = $this->db->query($query);
        $subtree = [];
        while ($row = $this->db->fetchAssoc($res)) {
            if ($with_data) {
                $subtree[] = $this->fetchNodeData($row);
            } else {
                $subtree[] = (int) $row['child'];
            }
            if ($this->__isMainTree() || $this->table_tree == 'lm_tree') {
                $this->in_tree_cache[$row['child']] = true;
            }
        }

        return $subtree;
    }

    public function getRootId(): int
    {
        return $this->root_id;
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

        $pathIds = $this->getPathIds($a_endnode_id, $a_startnode_id);

        if ($this->__isMainTree()) {
            $this->path_id_cache[$a_endnode_id][$a_startnode_id] = $pathIds;
        }
        return $pathIds;
    }

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
     * get child nodes of given node (exclude filtered obj_types)
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

    public function getObjectDataTable(): string
    {
        return $this->table_obj_data;
    }

    public function getTreeTable(): string
    {
        return $this->table_tree;
    }

    public function getTableReference(): string
    {
        return $this->table_obj_reference;
    }

    public function getTreePk(): string
    {
        return $this->tree_pk;
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
        $this->root_id = 0;
        if ($row = $this->db->fetchObject($res)) {
            $this->root_id = (int) $row->child;
        }

        return $this->root_id;
    }

    /**
     * get node data of successor node
     * @throws InvalidArgumentException
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
     * @fixme fix return false
     */
    public function fetchPredecessorNode(int $a_node_id, string $a_type = ""): ?array
    {
        if (!isset($a_node_id)) {
            $message = "No node_id given!";
            ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::ERROR);
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
     * get data of parent node from tree and object_data
     */
    protected function fetchNodeData(array $a_row): array
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $lng = $DIC['lng'];

        $data = $a_row;
        $data["desc"] = (string) ($a_row["description"] ?? ''); // for compability

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
                    $data["title"] = (string) $row->title;
                    $data["description"] = ilStr::shortenTextExtended((string) $row->description, ilObject::DESC_LENGTH, true);
                    $data["desc"] = (string) $row->description;
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

    public function insertNode(int $a_node_id, int $a_parent_id, int $a_pos): void
    {
        $insert_node_callable = function (ilDBInterface $db) use ($a_node_id, $a_parent_id, $a_pos): void {
            switch ($a_pos) {
                case ilTree::POS_FIRST_NODE:

                    // get left value of parent
                    $query = sprintf(
                        'SELECT * FROM ' . $this->table_tree . ' ' .
                        'WHERE child = %s ' .
                        'AND ' . $this->tree_pk . ' = %s ',
                        $this->db->quote($a_parent_id, 'integer'),
                        $this->db->quote($this->tree_id, 'integer')
                    );

                    $res = $this->db->query($query);
                    $r = $this->db->fetchObject($res);

                    if ($r->parent === null) {
                        ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::ERROR);
                        throw new ilInvalidTreeStructureException('Parent with id ' . $a_parent_id . ' not found in tree');
                    }

                    $left = $r->lft;
                    $lft = $left + 1;
                    $rgt = $left + 2;

                    if ($this->__isMainTree()) {
                        $query = sprintf(
                            'UPDATE ' . $this->table_tree . ' SET ' .
                            'lft = CASE WHEN lft > %s THEN lft + 2 ELSE lft END, ' .
                            'rgt = CASE WHEN rgt > %s THEN rgt + 2 ELSE rgt END ',
                            $this->db->quote($left, 'integer'),
                            $this->db->quote($left, 'integer')
                        );
                        $res = $this->db->manipulate($query);
                    } else {
                        $query = sprintf(
                            'UPDATE ' . $this->table_tree . ' SET ' .
                            'lft = CASE WHEN lft > %s THEN lft + 2 ELSE lft END, ' .
                            'rgt = CASE WHEN rgt > %s THEN rgt + 2 ELSE rgt END ' .
                            'WHERE ' . $this->tree_pk . ' = %s ',
                            $this->db->quote($left, 'integer'),
                            $this->db->quote($left, 'integer'),
                            $this->db->quote($this->tree_id, 'integer')
                        );
                        $res = $this->db->manipulate($query);
                    }

                    break;

                case ilTree::POS_LAST_NODE:
                    // Special treatment for trees with gaps
                    if ($this->gap > 0) {
                        // get lft and rgt value of parent
                        $query = sprintf(
                            'SELECT rgt,lft,parent FROM ' . $this->table_tree . ' ' .
                            'WHERE child = %s ' .
                            'AND ' . $this->tree_pk . ' =  %s',
                            $this->db->quote($a_parent_id, 'integer'),
                            $this->db->quote($this->tree_id, 'integer')
                        );
                        $res = $this->db->query($query);
                        $r = $this->db->fetchAssoc($res);

                        if ($r['parent'] === null) {
                            ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::ERROR);
                            throw new ilInvalidTreeStructureException('Parent with id ' . $a_parent_id . ' not found in tree');
                        }
                        $parentRgt = (int) $r['rgt'];
                        $parentLft = (int) $r['lft'];

                        // Get the available space, without taking children into account yet
                        $availableSpace = $parentRgt - $parentLft;
                        if ($availableSpace < 2) {
                            // If there is not enough space between parent lft and rgt, we don't need
                            // to look any further, because we must spread the tree.
                            $lft = $parentRgt;
                        } else {
                            // If there is space between parent lft and rgt, we need to check
                            // whether there is space left between the rightmost child of the
                            // parent and parent rgt.
                            if ($this->__isMainTree()) {
                                $query = sprintf(
                                    'SELECT MAX(rgt) max_rgt FROM ' . $this->table_tree . ' ' .
                                    'WHERE parent = %s ',
                                    $this->db->quote($a_parent_id, 'integer')
                                );
                                $res = $this->db->query($query);
                                $r = $this->db->fetchAssoc($res);
                            } else {
                                $query = sprintf(
                                    'SELECT MAX(rgt) max_rgt FROM ' . $this->table_tree . ' ' .
                                    'WHERE parent = %s ' .
                                    'AND ' . $this->tree_pk . ' = %s',
                                    $this->db->quote($a_parent_id, 'integer'),
                                    $this->db->quote($this->tree_id, 'integer')
                                );
                                $res = $this->db->query($query);
                                $r = $this->db->fetchAssoc($res);
                            }

                            if (isset($r['max_rgt'])) {
                                // If the parent has children, we compute the available space
                                // between rgt of the rightmost child and parent rgt.
                                $availableSpace = $parentRgt - $r['max_rgt'];
                                $lft = $r['max_rgt'] + 1;
                            } else {
                                // If the parent has no children, we know now, that we can
                                // add the new node at parent lft + 1 without having to spread
                                // the tree.
                                $lft = $parentLft + 1;
                            }
                        }
                        $rgt = $lft + 1;

                        // spread tree if there is not enough space to insert the new node
                        if ($availableSpace < 2) {
                            if ($this->__isMainTree()) {
                                $query = sprintf(
                                    'UPDATE ' . $this->table_tree . ' SET ' .
                                    'lft = CASE WHEN lft  > %s THEN lft + %s ELSE lft END, ' .
                                    'rgt = CASE WHEN rgt >= %s THEN rgt + %s ELSE rgt END ',
                                    $this->db->quote($parentRgt, 'integer'),
                                    $this->db->quote((2 + $this->gap * 2), 'integer'),
                                    $this->db->quote($parentRgt, 'integer'),
                                    $this->db->quote((2 + $this->gap * 2), 'integer')
                                );
                                $res = $this->db->manipulate($query);
                            } else {
                                $query = sprintf(
                                    'UPDATE ' . $this->table_tree . ' SET ' .
                                    'lft = CASE WHEN lft  > %s THEN lft + %s ELSE lft END, ' .
                                    'rgt = CASE WHEN rgt >= %s THEN rgt + %s ELSE rgt END ' .
                                    'WHERE ' . $this->tree_pk . ' = %s ',
                                    $this->db->quote($parentRgt, 'integer'),
                                    $this->db->quote((2 + $this->gap * 2), 'integer'),
                                    $this->db->quote($parentRgt, 'integer'),
                                    $this->db->quote((2 + $this->gap * 2), 'integer'),
                                    $this->db->quote($this->tree_id, 'integer')
                                );
                                $res = $this->db->manipulate($query);
                            }
                        }
                    } // Treatment for trees without gaps
                    else {
                        // get right value of parent
                        if ($this->__isMainTree()) {
                            $query = sprintf(
                                'SELECT * FROM ' . $this->table_tree . ' ' .
                                'WHERE child = %s ',
                                $this->db->quote($a_parent_id, 'integer')
                            );
                            $res = $this->db->query($query);
                        } else {
                            $query = sprintf(
                                'SELECT * FROM ' . $this->table_tree . ' ' .
                                'WHERE child = %s ' .
                                'AND ' . $this->tree_pk . ' = %s ',
                                $this->db->quote($a_parent_id, 'integer'),
                                $this->db->quote($this->tree_id, 'integer')
                            );
                            $res = $this->db->query($query);
                        }
                        $r = $this->db->fetchObject($res);

                        if ($r->parent === null) {
                            ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::ERROR);
                            throw new ilInvalidTreeStructureException('Parent with id ' . $a_parent_id . ' not found in tree');
                        }

                        $right = $r->rgt;
                        $lft = $right;
                        $rgt = $right + 1;

                        // spread tree
                        if ($this->__isMainTree()) {
                            $query = sprintf(
                                'UPDATE ' . $this->table_tree . ' SET ' .
                                'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, ' .
                                'rgt = CASE WHEN rgt >= %s THEN rgt + 2 ELSE rgt END ',
                                $this->db->quote($right, 'integer'),
                                $this->db->quote($right, 'integer')
                            );
                            $res = $this->db->manipulate($query);
                        } else {
                            $query = sprintf(
                                'UPDATE ' . $this->table_tree . ' SET ' .
                                'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, ' .
                                'rgt = CASE WHEN rgt >= %s THEN rgt + 2 ELSE rgt END ' .
                                'WHERE ' . $this->tree_pk . ' = %s',
                                $this->db->quote($right, 'integer'),
                                $this->db->quote($right, 'integer'),
                                $this->db->quote($this->tree_id, 'integer')
                            );
                            $res = $this->db->manipulate($query);
                        }
                    }

                    break;

                default:

                    // get right value of preceeding child
                    $query = sprintf(
                        'SELECT * FROM ' . $this->table_tree . ' ' .
                        'WHERE child = %s ' .
                        'AND ' . $this->tree_pk . ' = %s ',
                        $this->db->quote($a_pos, 'integer'),
                        $this->db->quote($this->tree_id, 'integer')
                    );
                    $res = $this->db->query($query);
                    $r = $this->db->fetchObject($res);

                    // crosscheck parents of sibling and new node (must be identical)
                    if ($r->parent != $a_parent_id) {
                        ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::ERROR);
                        throw new ilInvalidTreeStructureException('Parent with id ' . $a_parent_id . ' not found in tree');
                    }

                    $right = $r->rgt;
                    $lft = $right + 1;
                    $rgt = $right + 2;

                    if ($this->__isMainTree()) {
                        $query = sprintf(
                            'UPDATE ' . $this->table_tree . ' SET ' .
                            'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, ' .
                            'rgt = CASE WHEN rgt >  %s THEN rgt + 2 ELSE rgt END ',
                            $this->db->quote($right, 'integer'),
                            $this->db->quote($right, 'integer')
                        );
                        $res = $this->db->manipulate($query);
                    } else {
                        $query = sprintf(
                            'UPDATE ' . $this->table_tree . ' SET ' .
                            'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, ' .
                            'rgt = CASE WHEN rgt >  %s THEN rgt + 2 ELSE rgt END ' .
                            'WHERE ' . $this->tree_pk . ' = %s',
                            $this->db->quote($right, 'integer'),
                            $this->db->quote($right, 'integer'),
                            $this->db->quote($this->tree_id, 'integer')
                        );
                        $res = $this->db->manipulate($query);
                    }

                    break;
            }

            // get depth
            $depth = $this->getDepth($a_parent_id) + 1;

            // insert node
            $query = sprintf(
                'INSERT INTO ' . $this->table_tree . ' (' . $this->tree_pk . ',child,parent,lft,rgt,depth) ' .
                'VALUES (%s,%s,%s,%s,%s,%s)',
                $this->db->quote($this->tree_id, 'integer'),
                $this->db->quote($a_node_id, 'integer'),
                $this->db->quote($a_parent_id, 'integer'),
                $this->db->quote($lft, 'integer'),
                $this->db->quote($rgt, 'integer'),
                $this->db->quote($depth, 'integer')
            );
            $res = $this->db->manipulate($query);
        };

        if ($this->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock('tree');
            $ilAtomQuery->addQueryCallable($insert_node_callable);
            $ilAtomQuery->run();
        } else {
            $insert_node_callable($this->db);
        }
    }

    public function deleteTree(int $a_node_id): void
    {
        $delete_tree_callable = function (ilDBInterface $db) use ($a_node_id): void {
            // Fetch lft, rgt directly (without fetchNodeData) to avoid unnecessary table locks
            // (object_reference, object_data)
            $query = 'SELECT *  FROM ' . $this->table_tree . ' ' .
                'WHERE child = ' . $this->db->quote($a_node_id, 'integer') . ' ' .
                'AND ' . $this->tree_pk . ' = ' . $this->db->quote(
                    $this->tree_id,
                    'integer'
                );
            $res = $this->db->query($query);
            $a_node = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

            // delete subtree
            $query = sprintf(
                'DELETE FROM ' . $this->table_tree . ' ' .
                'WHERE lft BETWEEN %s AND %s ' .
                'AND rgt BETWEEN %s AND %s ' .
                'AND ' . $this->tree_pk . ' = %s',
                $this->db->quote($a_node['lft'], 'integer'),
                $this->db->quote($a_node['rgt'], 'integer'),
                $this->db->quote($a_node['lft'], 'integer'),
                $this->db->quote($a_node['rgt'], 'integer'),
                $this->db->quote($a_node[$this->tree_pk], 'integer')
            );
            $res = $this->db->manipulate($query);

            // Performance improvement: We only close the gap, if the node
            // is not in a trash tree, and if the resulting gap will be
            // larger than twice the gap value

            $diff = $a_node["rgt"] - $a_node["lft"] + 1;
            if (
                $a_node[$this->tree_pk] >= 0 &&
                $a_node['rgt'] - $a_node['lft'] >= $this->gap * 2
            ) {
                if ($this->__isMainTree()) {
                    $query = sprintf(
                        'UPDATE ' . $this->table_tree . ' SET ' .
                        'lft = CASE WHEN lft > %s THEN lft - %s ELSE lft END, ' .
                        'rgt = CASE WHEN rgt > %s THEN rgt - %s ELSE rgt END ',
                        $this->db->quote($a_node['lft'], 'integer'),
                        $this->db->quote($diff, 'integer'),
                        $this->db->quote($a_node['lft'], 'integer'),
                        $this->db->quote($diff, 'integer')
                    );
                    $res = $this->db->manipulate($query);
                } else {
                    $query = sprintf(
                        'UPDATE ' . $this->table_tree . ' SET ' .
                        'lft = CASE WHEN lft > %s THEN lft - %s ELSE lft END, ' .
                        'rgt = CASE WHEN rgt > %s THEN rgt - %s ELSE rgt END ' .
                        'WHERE ' . $this->tree_pk . ' = %s ',
                        $this->db->quote($a_node['lft'], 'integer'),
                        $this->db->quote($diff, 'integer'),
                        $this->db->quote($a_node['lft'], 'integer'),
                        $this->db->quote($diff, 'integer'),
                        $this->db->quote($a_node[$this->tree_pk], 'integer')
                    );
                    $res = $this->db->manipulate($query);
                }
            }
        };

        // get lft and rgt values. Don't trust parameter lft/rgt values of $a_node
        if ($this->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock('tree');
            $ilAtomQuery->addQueryCallable($delete_tree_callable);
            $ilAtomQuery->run();
        } else {
            $delete_tree_callable($this->db);
        }
    }

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

    public function moveToTrash(int $a_node_id): void
    {
        $move_to_trash_callable = function (ilDBInterface $db) use ($a_node_id): void {
            $node = $this->getNodeTreeData($a_node_id);

            $query = 'UPDATE ' . $this->table_tree . ' ' .
                'SET tree = ' . $this->db->quote(-1 * $node['child'], 'integer') . ' ' .
                'WHERE ' . $this->tree_pk . ' =  ' . $this->db->quote(
                    $this->tree_id,
                    'integer'
                ) . ' ' .
                'AND lft BETWEEN ' . $this->db->quote(
                    $node['lft'],
                    'integer'
                ) . ' AND ' . $this->db->quote($node['rgt'], 'integer') . ' ';

            $this->db->manipulate($query);
        };

        // use ilAtomQuery to lock tables if tree is main tree
        // otherwise just call this closure without locking
        if ($this->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock("tree");
            $ilAtomQuery->addQueryCallable($move_to_trash_callable);
            $ilAtomQuery->run();
        } else {
            $move_to_trash_callable($this->db);
        }
    }

    public function moveTree(int $a_source_id, int $a_target_id, int $a_position): void
    {
        $move_tree_callable = function (ilDBInterface $ilDB) use ($a_source_id, $a_target_id, $a_position): void {
            // Receive node infos for source and target
            $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                'WHERE ( child = %s OR child = %s ) ' .
                'AND ' . $this->tree_pk . ' = %s ';
            $res = $this->db->queryF($query, array('integer', 'integer', 'integer'), array(
                $a_source_id,
                $a_target_id,
                $this->tree_id
            ));

            // Check in tree
            if ($res->numRows() != 2) {
                ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::ERROR, 'Objects not found in tree');
                throw new InvalidArgumentException('Error moving subtree');
            }
            $source_lft = $target_lft = $source_rgt = $target_rgt = $source_depth = $target_depth = $source_parent = 0;
            while ($row = $this->db->fetchObject($res)) {
                if ($row->child == $a_source_id) {
                    $source_lft = $row->lft;
                    $source_rgt = $row->rgt;
                    $source_depth = $row->depth;
                    $source_parent = $row->parent;
                } else {
                    $target_lft = $row->lft;
                    $target_rgt = $row->rgt;
                    $target_depth = $row->depth;
                }
            }

            // Check target not child of source
            if ($target_lft >= $source_lft && $target_rgt <= $source_rgt) {
                ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::ERROR, 'Target is child of source');
                throw new InvalidArgumentException('Error moving subtree: target is child of source');
            }

            // Now spread the tree at the target location. After this update the table should be still in a consistent state.
            // implementation for ilTree::POS_LAST_NODE
            $spread_diff = $source_rgt - $source_lft + 1;
            #var_dump("<pre>","SPREAD_DIFF: ",$spread_diff,"<pre>");

            $query = 'UPDATE ' . $this->table_tree . ' SET ' .
                'lft = CASE WHEN lft >  %s THEN lft + %s ELSE lft END, ' .
                'rgt = CASE WHEN rgt >= %s THEN rgt + %s ELSE rgt END ';

            if ($this->__isMainTree()) {
                $res = $this->db->manipulateF($query, array('integer', 'integer', 'integer', 'integer'), [
                    $target_rgt,
                    $spread_diff,
                    $target_rgt,
                    $spread_diff
                ]);
            } else {
                $query .= ('WHERE ' . $this->tree_pk . ' = %s ');
                $res = $this->db->manipulateF(
                    $query,
                    array('integer', 'integer', 'integer', 'integer', 'integer'),
                    array(
                        $target_rgt,
                        $spread_diff,
                        $target_rgt,
                        $spread_diff,
                        $this->tree_id
                    )
                );
            }

            // Maybe the source node has been updated, too.
            // Check this:
            if ($source_lft > $target_rgt) {
                $where_offset = $spread_diff;
                $move_diff = $target_rgt - $source_lft - $spread_diff;
            } else {
                $where_offset = 0;
                $move_diff = $target_rgt - $source_lft;
            }
            $depth_diff = $target_depth - $source_depth + 1;

            $query = 'UPDATE ' . $this->table_tree . ' SET ' .
                'parent = CASE WHEN parent = %s THEN %s ELSE parent END, ' .
                'rgt = rgt + %s, ' .
                'lft = lft + %s, ' .
                'depth = depth + %s ' .
                'WHERE lft >= %s ' .
                'AND rgt <= %s ';

            if ($this->__isMainTree()) {
                $res = $this->db->manipulateF(
                    $query,
                    array('integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
                    [
                        $source_parent,
                        $a_target_id,
                        $move_diff,
                        $move_diff,
                        $depth_diff,
                        $source_lft + $where_offset,
                        $source_rgt + $where_offset
                    ]
                );
            } else {
                $query .= 'AND ' . $this->tree_pk . ' = %s ';
                $res = $this->db->manipulateF(
                    $query,
                    array('integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
                    array(
                        $source_parent,
                        $a_target_id,
                        $move_diff,
                        $move_diff,
                        $depth_diff,
                        $source_lft + $where_offset,
                        $source_rgt + $where_offset,
                        $this->tree_id
                    )
                );
            }

            // done: close old gap
            $query = 'UPDATE ' . $this->table_tree . ' SET ' .
                'lft = CASE WHEN lft >= %s THEN lft - %s ELSE lft END, ' .
                'rgt = CASE WHEN rgt >= %s THEN rgt - %s ELSE rgt END ';

            if ($this->__isMainTree()) {
                $res = $this->db->manipulateF($query, array('integer', 'integer', 'integer', 'integer'), [
                    $source_lft + $where_offset,
                    $spread_diff,
                    $source_rgt + $where_offset,
                    $spread_diff
                ]);
            } else {
                $query .= ('WHERE ' . $this->tree_pk . ' = %s ');

                $res = $this->db->manipulateF(
                    $query,
                    array('integer', 'integer', 'integer', 'integer', 'integer'),
                    array(
                        $source_lft + $where_offset,
                        $spread_diff,
                        $source_rgt + $where_offset,
                        $spread_diff,
                        $this->tree_id
                    )
                );
            }
        };

        if ($this->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock('tree');
            $ilAtomQuery->addQueryCallable($move_tree_callable);
            $ilAtomQuery->run();
        } else {
            $move_tree_callable($this->db);
        }
    }

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
     * @inheritdoc
     * @todo add unit test; check failure result
     * @fixme fix $row access
     */
    public function validateParentRelations(): array
    {
        $query = 'select child from ' . $this->table_tree . ' child where not exists ' .
            '( ' .
            'select child from ' . $this->table_tree . ' parent where child.parent = parent.child and (parent.lft < child.lft) and (parent.rgt > child.rgt) ' .
            ')' .
            'and ' . $this->tree_pk . ' = ' . $this->tree_id . ' and child <> 1';
        $res = $this->db->query($query);

        $failures = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $failures[] = $row[$this->tree_pk];
        }
        return $failures;
    }

    public function buildJoin(): string
    {
        if ($this->table_obj_reference) {
            return "JOIN " . $this->table_obj_reference . " ON " . $this->table_tree . ".child=" . $this->table_obj_reference . "." . $this->ref_pk . " " .
                "JOIN " . $this->table_obj_data . " ON " . $this->table_obj_reference . "." . $this->obj_pk . "=" . $this->table_obj_data . "." . $this->obj_pk . " ";
        } else {
            return "JOIN " . $this->table_obj_data . " ON " . $this->table_tree . ".child=" . $this->table_obj_data . "." . $this->obj_pk . " ";
        }
    }

    public function useCache(bool $use = true): void
    {
        $this->use_cache = $use;
    }

    public function isCacheUsed(): bool
    {
        return $this->__isMainTree() && $this->use_cache;
    }

    public function isInTree(?int $node_id): bool
    {
        if (is_null($node_id) || !$node_id) {
            return false;
        }
        // is in tree cache
        if ($this->isCacheUsed() && isset($this->in_tree_cache[$node_id])) {
            return $this->in_tree_cache[$node_id];
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $this->table_tree . '.child = %s ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s';

        $res = $this->db->queryF($query, array('integer', 'integer'), array(
            $node_id,
            $this->tree_id
        ));

        if ($res->numRows() > 0) {
            if ($this->__isMainTree()) {
                $this->in_tree_cache[$node_id] = true;
            }
            return true;
        } else {
            if ($this->__isMainTree()) {
                $this->in_tree_cache[$node_id] = false;
            }
            return false;
        }
    }

    protected function isRepositoryTree(): bool
    {
        return $this->table_tree == 'tree';
    }

    /**
     * checks if a node is in the path of an other node
     */
    public function isGrandChild(int $a_startnode_id, int $a_querynode_id): bool
    {
        return $this->getRelation($a_startnode_id, $a_querynode_id) == self::RELATION_PARENT;
    }

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
     * Wrapper for renumber. This method locks the table tree
     * (recursive)
     */
    public function renumber(int $node_id = 1, int $i = 1): int
    {
        $renumber_callable = function (ilDBInterface $db) use ($node_id, $i, &$return) {
            $return = $this->__renumber($node_id, $i);
        };

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
     * This method is private. Always call ilNestedSetTree->renumber() since it locks the tree table
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

    protected function __isMainTree(): bool
    {
        return $this->table_tree === 'tree';
    }
}
