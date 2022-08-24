<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base class for nested set path based trees
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesTree
 */
class ilNestedSetTree implements ilTreeImplementation
{
    protected ilTree $tree;
    protected ilDBInterface $db;

    /**
     * Constructor
     */
    public function __construct(ilTree $a_tree)
    {
        global $DIC;

        $this->tree = $a_tree;
        $this->db = $DIC->database();
    }

    public function getTree(): \ilTree
    {
        return $this->tree;
    }

    /**
     * Get subtree ids
     * @retutn int[]
     */
    public function getSubTreeIds(int $a_node_id): array
    {
        $query = 'SELECT s.child FROM ' .
            $this->getTree()->getTreeTable() . ' s, ' .
            $this->getTree()->getTreeTable() . ' t ' .
            'WHERE t.child = %s ' .
            'AND s.lft > t.lft ' .
            'AND s.rgt < t.rgt ' .
            'AND s.' . $this->getTree()->getTreePk() . ' = %s';

        $res = $this->db->queryF(
            $query,
            array('integer', 'integer'),
            array($a_node_id, $this->getTree()->getTreeId())
        );
        $childs = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $childs[] = (int) $row->child;
        }
        return $childs;
    }

    /**
     * @inheritdoc
     */
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
                    $this->getTree()->getObjectDataTable() . ".type",
                    $a_types,
                    false,
                    "text"
                );
            }
        }

        $join = '';
        if ($type_str || $a_force_join_reference) {
            $join = $this->getTree()->buildJoin();
        }

        $fields = '* ';
        if (count($a_fields)) {
            $fields = implode(',', $a_fields);
        }

        $query = 'SELECT ' .
            $fields . ' ' .
            "FROM " . $this->getTree()->getTreeTable() . " " .
            $join . ' ' .
            "WHERE " . $this->getTree()->getTreeTable() . '.lft ' .
            'BETWEEN ' . $this->db->quote($a_node['lft'], 'integer') . ' ' .
            'AND ' . $this->db->quote($a_node['rgt'], 'integer') . ' ' .
            "AND " . $this->getTree()->getTreeTable() . "." . $this->getTree()->getTreePk() . ' < 0 ' .
            $type_str . ' ' .
            "ORDER BY " . $this->getTree()->getTreeTable() . ".lft";

        return $query;
    }

    /**
     * Get subtree
     */
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
                    $this->getTree()->getObjectDataTable() . ".type",
                    $a_types,
                    false,
                    "text"
                );
            }
        }


        $join = '';
        if ($type_str || $a_force_join_reference) {
            $join = $this->getTree()->buildJoin();
        }

        $fields = '* ';
        if (count($a_fields)) {
            $fields = implode(',', $a_fields);
        }

        $query = 'SELECT ' .
            $fields . ' ' .
            "FROM " . $this->getTree()->getTreeTable() . " " .
            $join . ' ' .
            "WHERE " . $this->getTree()->getTreeTable() . '.lft ' .
            'BETWEEN ' . $this->db->quote($a_node['lft'], 'integer') . ' ' .
            'AND ' . $this->db->quote($a_node['rgt'], 'integer') . ' ' .
            "AND " . $this->getTree()->getTreeTable() . "." . $this->getTree()->getTreePk() . " = " . $this->db->quote(
                $this->getTree()->getTreeId(),
                'integer'
            ) . ' ' .
            $type_str . ' ' .
            "ORDER BY " . $this->getTree()->getTreeTable() . ".lft";
        return $query;
    }

    /**
     * @inheritdoc
     */
    public function getRelation(array $a_node_a, array $a_node_b): int
    {
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
     * @inheritdoc
     */
    public function insertNode(int $a_node_id, int $a_parent_id, int $a_pos): void
    {
        $insert_node_callable = function (ilDBInterface $db) use ($a_node_id, $a_parent_id, $a_pos): void {
            switch ($a_pos) {
                case ilTree::POS_FIRST_NODE:

                    // get left value of parent
                    $query = sprintf(
                        'SELECT * FROM ' . $this->getTree()->getTreeTable() . ' ' .
                        'WHERE child = %s ' .
                        'AND ' . $this->getTree()->getTreePk() . ' = %s ',
                        $this->db->quote($a_parent_id, 'integer'),
                        $this->db->quote($this->getTree()->getTreeId(), 'integer')
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

                    if ($this->getTree()->__isMainTree()) {
                        $query = sprintf(
                            'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                            'lft = CASE WHEN lft > %s THEN lft + 2 ELSE lft END, ' .
                            'rgt = CASE WHEN rgt > %s THEN rgt + 2 ELSE rgt END ',
                            $this->db->quote($left, 'integer'),
                            $this->db->quote($left, 'integer')
                        );
                        $res = $this->db->manipulate($query);
                    } else {
                        $query = sprintf(
                            'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                            'lft = CASE WHEN lft > %s THEN lft + 2 ELSE lft END, ' .
                            'rgt = CASE WHEN rgt > %s THEN rgt + 2 ELSE rgt END ' .
                            'WHERE ' . $this->getTree()->getTreePk() . ' = %s ',
                            $this->db->quote($left, 'integer'),
                            $this->db->quote($left, 'integer'),
                            $this->db->quote($this->getTree()->getTreeId(), 'integer')
                        );
                        $res = $this->db->manipulate($query);
                    }

                    break;

                case ilTree::POS_LAST_NODE:
                    // Special treatment for trees with gaps
                    if ($this->getTree()->getGap() > 0) {
                        // get lft and rgt value of parent
                        $query = sprintf(
                            'SELECT rgt,lft,parent FROM ' . $this->getTree()->getTreeTable() . ' ' .
                            'WHERE child = %s ' .
                            'AND ' . $this->getTree()->getTreePk() . ' =  %s',
                            $this->db->quote($a_parent_id, 'integer'),
                            $this->db->quote($this->getTree()->getTreeId(), 'integer')
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
                            if ($this->getTree()->__isMainTree()) {
                                $query = sprintf(
                                    'SELECT MAX(rgt) max_rgt FROM ' . $this->getTree()->getTreeTable() . ' ' .
                                    'WHERE parent = %s ',
                                    $this->db->quote($a_parent_id, 'integer')
                                );
                                $res = $this->db->query($query);
                                $r = $this->db->fetchAssoc($res);
                            } else {
                                $query = sprintf(
                                    'SELECT MAX(rgt) max_rgt FROM ' . $this->getTree()->getTreeTable() . ' ' .
                                    'WHERE parent = %s ' .
                                    'AND ' . $this->getTree()->getTreePk() . ' = %s',
                                    $this->db->quote($a_parent_id, 'integer'),
                                    $this->db->quote($this->getTree()->getTreeId(), 'integer')
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
                            if ($this->getTree()->__isMainTree()) {
                                $query = sprintf(
                                    'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                                    'lft = CASE WHEN lft  > %s THEN lft + %s ELSE lft END, ' .
                                    'rgt = CASE WHEN rgt >= %s THEN rgt + %s ELSE rgt END ',
                                    $this->db->quote($parentRgt, 'integer'),
                                    $this->db->quote((2 + $this->getTree()->getGap() * 2), 'integer'),
                                    $this->db->quote($parentRgt, 'integer'),
                                    $this->db->quote((2 + $this->getTree()->getGap() * 2), 'integer')
                                );
                                $res = $this->db->manipulate($query);
                            } else {
                                $query = sprintf(
                                    'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                                    'lft = CASE WHEN lft  > %s THEN lft + %s ELSE lft END, ' .
                                    'rgt = CASE WHEN rgt >= %s THEN rgt + %s ELSE rgt END ' .
                                    'WHERE ' . $this->getTree()->getTreePk() . ' = %s ',
                                    $this->db->quote($parentRgt, 'integer'),
                                    $this->db->quote((2 + $this->getTree()->getGap() * 2), 'integer'),
                                    $this->db->quote($parentRgt, 'integer'),
                                    $this->db->quote((2 + $this->getTree()->getGap() * 2), 'integer'),
                                    $this->db->quote($this->getTree()->getTreeId(), 'integer')
                                );
                                $res = $this->db->manipulate($query);
                            }
                        }
                    } // Treatment for trees without gaps
                    else {

                        // get right value of parent
                        if ($this->getTree()->__isMainTree()) {
                            $query = sprintf(
                                'SELECT * FROM ' . $this->getTree()->getTreeTable() . ' ' .
                                'WHERE child = %s ',
                                $this->db->quote($a_parent_id, 'integer')
                            );
                            $res = $this->db->query($query);
                        } else {
                            $query = sprintf(
                                'SELECT * FROM ' . $this->getTree()->getTreeTable() . ' ' .
                                'WHERE child = %s ' .
                                'AND ' . $this->getTree()->getTreePk() . ' = %s ',
                                $this->db->quote($a_parent_id, 'integer'),
                                $this->db->quote($this->getTree()->getTreeId(), 'integer')
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
                        if ($this->getTree()->__isMainTree()) {
                            $query = sprintf(
                                'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                                'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, ' .
                                'rgt = CASE WHEN rgt >= %s THEN rgt + 2 ELSE rgt END ',
                                $this->db->quote($right, 'integer'),
                                $this->db->quote($right, 'integer')
                            );
                            $res = $this->db->manipulate($query);
                        } else {
                            $query = sprintf(
                                'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                                'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, ' .
                                'rgt = CASE WHEN rgt >= %s THEN rgt + 2 ELSE rgt END ' .
                                'WHERE ' . $this->getTree()->getTreePk() . ' = %s',
                                $this->db->quote($right, 'integer'),
                                $this->db->quote($right, 'integer'),
                                $this->db->quote($this->getTree()->getTreeId(), 'integer')
                            );
                            $res = $this->db->manipulate($query);
                        }
                    }

                    break;

                default:

                    // get right value of preceeding child
                    $query = sprintf(
                        'SELECT * FROM ' . $this->getTree()->getTreeTable() . ' ' .
                        'WHERE child = %s ' .
                        'AND ' . $this->getTree()->getTreePk() . ' = %s ',
                        $this->db->quote($a_pos, 'integer'),
                        $this->db->quote($this->getTree()->getTreeId(), 'integer')
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

                    if ($this->getTree()->__isMainTree()) {
                        $query = sprintf(
                            'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                            'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, ' .
                            'rgt = CASE WHEN rgt >  %s THEN rgt + 2 ELSE rgt END ',
                            $this->db->quote($right, 'integer'),
                            $this->db->quote($right, 'integer')
                        );
                        $res = $this->db->manipulate($query);
                    } else {
                        $query = sprintf(
                            'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                            'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, ' .
                            'rgt = CASE WHEN rgt >  %s THEN rgt + 2 ELSE rgt END ' .
                            'WHERE ' . $this->getTree()->getTreePk() . ' = %s',
                            $this->db->quote($right, 'integer'),
                            $this->db->quote($right, 'integer'),
                            $this->db->quote($this->getTree()->getTreeId(), 'integer')
                        );
                        $res = $this->db->manipulate($query);
                    }

                    break;

            }

            // get depth
            $depth = $this->getTree()->getDepth($a_parent_id) + 1;

            // insert node
            $query = sprintf(
                'INSERT INTO ' . $this->getTree()->getTreeTable() . ' (' . $this->getTree()->getTreePk() . ',child,parent,lft,rgt,depth) ' .
                'VALUES (%s,%s,%s,%s,%s,%s)',
                $this->db->quote($this->getTree()->getTreeId(), 'integer'),
                $this->db->quote($a_node_id, 'integer'),
                $this->db->quote($a_parent_id, 'integer'),
                $this->db->quote($lft, 'integer'),
                $this->db->quote($rgt, 'integer'),
                $this->db->quote($depth, 'integer')
            );
            $res = $this->db->manipulate($query);
        };

        if ($this->getTree()->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock('tree');
            $ilAtomQuery->addQueryCallable($insert_node_callable);
            $ilAtomQuery->run();
        } else {
            $insert_node_callable($this->db);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteTree(int $a_node_id): void
    {
        $delete_tree_callable = function (ilDBInterface $db) use ($a_node_id): void {

            // Fetch lft, rgt directly (without fetchNodeData) to avoid unnecessary table locks
            // (object_reference, object_data)
            $query = 'SELECT *  FROM ' . $this->getTree()->getTreeTable() . ' ' .
                'WHERE child = ' . $this->db->quote($a_node_id, 'integer') . ' ' .
                'AND ' . $this->getTree()->getTreePk() . ' = ' . $this->db->quote(
                    $this->getTree()->getTreeId(),
                    'integer'
                );
            $res = $this->db->query($query);
            $a_node = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

            // delete subtree
            $query = sprintf(
                'DELETE FROM ' . $this->getTree()->getTreeTable() . ' ' .
                'WHERE lft BETWEEN %s AND %s ' .
                'AND rgt BETWEEN %s AND %s ' .
                'AND ' . $this->getTree()->getTreePk() . ' = %s',
                $this->db->quote($a_node['lft'], 'integer'),
                $this->db->quote($a_node['rgt'], 'integer'),
                $this->db->quote($a_node['lft'], 'integer'),
                $this->db->quote($a_node['rgt'], 'integer'),
                $this->db->quote($a_node[$this->getTree()->getTreePk()], 'integer')
            );
            $res = $this->db->manipulate($query);

            // Performance improvement: We only close the gap, if the node
            // is not in a trash tree, and if the resulting gap will be
            // larger than twice the gap value

            $diff = $a_node["rgt"] - $a_node["lft"] + 1;
            if (
                $a_node[$this->getTree()->getTreePk()] >= 0 &&
                $a_node['rgt'] - $a_node['lft'] >= $this->getTree()->getGap() * 2
            ) {
                if ($this->getTree()->__isMainTree()) {
                    $query = sprintf(
                        'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
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
                        'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                        'lft = CASE WHEN lft > %s THEN lft - %s ELSE lft END, ' .
                        'rgt = CASE WHEN rgt > %s THEN rgt - %s ELSE rgt END ' .
                        'WHERE ' . $this->getTree()->getTreePk() . ' = %s ',
                        $this->db->quote($a_node['lft'], 'integer'),
                        $this->db->quote($diff, 'integer'),
                        $this->db->quote($a_node['lft'], 'integer'),
                        $this->db->quote($diff, 'integer'),
                        $this->db->quote($a_node[$this->getTree()->getTreePk()], 'integer')
                    );
                    $res = $this->db->manipulate($query);
                }
            }
        };

        // get lft and rgt values. Don't trust parameter lft/rgt values of $a_node
        if ($this->getTree()->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock('tree');
            $ilAtomQuery->addQueryCallable($delete_tree_callable);
            $ilAtomQuery->run();
        } else {
            $delete_tree_callable($this->db);
        }
    }

    /**
     * @inheritdoc
     */
    public function moveToTrash(int $a_node_id): void
    {
        $move_to_trash_callable = function (ilDBInterface $db) use ($a_node_id): void {
            $node = $this->getTree()->getNodeTreeData($a_node_id);

            $query = 'UPDATE ' . $this->getTree()->getTreeTable() . ' ' .
                'SET tree = ' . $this->db->quote(-1 * $node['child'], 'integer') . ' ' .
                'WHERE ' . $this->getTree()->getTreePk() . ' =  ' . $this->db->quote(
                    $this->getTree()->getTreeId(),
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
        if ($this->getTree()->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock("tree");
            $ilAtomQuery->addQueryCallable($move_to_trash_callable);
            $ilAtomQuery->run();
        } else {
            $move_to_trash_callable($this->db);
        }
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

        $depth_cache = $this->getTree()->getDepthCache();
        $parent_cache = $this->getTree()->getParentCache();

        if (
            $this->getTree()->__isMainTree() &&
            isset($depth_cache[$a_endnode_id]) &&
            isset($parent_cache[$a_endnode_id])) {
            $nodeDepth = $depth_cache[$a_endnode_id];
            $parentId = $parent_cache[$a_endnode_id];
        } else {
            $nodeDepth = $this->getTree()->getDepth($a_endnode_id);
            $parentId = $this->getTree()->getParentId($a_endnode_id);
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
            $takeId = $takeId || $this->getTree()->getRootId() == $a_startnode_id;
            if ($takeId) {
                $pathIds[] = $this->getTree()->getRootId();
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
                if ($this->getTree()->__isMainTree()) {
                    $qJoin .= ' JOIN ' . $this->getTree()->getTreeTable() . ' t' . $i . ' ON ' .
                        't' . $i . '.child=t' . ($i - 1) . '.parent ';
                } else {
                    $qJoin .= ' JOIN ' . $this->getTree()->getTreeTable() . ' t' . $i . ' ON ' .
                        't' . $i . '.child=t' . ($i - 1) . '.parent AND ' .
                        't' . $i . '.' . $this->getTree()->getTreePk() . ' = ' . $this->getTree()->getTreeId();
                }
            }

            if ($this->getTree()->__isMainTree()) {
                $types = array('integer');
                $data = array($parentId);
                $query = 'SELECT ' . $qSelect . ' ' .
                    'FROM ' . $this->getTree()->getTreeTable() . ' t0 ' . $qJoin . ' ' .
                    'WHERE t0.child = %s ';
            } else {
                $types = array('integer', 'integer');
                $data = array($this->getTree()->getTreeId(), $parentId);
                $query = 'SELECT ' . $qSelect . ' ' .
                    'FROM ' . $this->getTree()->getTreeTable() . ' t0 ' . $qJoin . ' ' .
                    'WHERE t0.' . $this->getTree()->getTreePk() . ' = %s ' .
                    'AND t0.child = %s ';
            }

            $this->db->setLimit(1, 0);
            $res = $this->db->queryF($query, $types, $data);

            if ($res->numRows() == 0) {
                return array();
            }

            $row = $this->db->fetchAssoc($res);

            $takeId = $takeId || $this->getTree()->getRootId() == $a_startnode_id;
            if ($takeId) {
                $pathIds[] = $this->getTree()->getRootId();
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
     * if startnode is not given the rootnode is startnode
     * @return int[]
     */
    public function getPathIdsUsingNestedSets(int $a_endnode_id, int $a_startnode_id = 0): array
    {
        // The nested sets algorithm is very easy to implement.
        // Unfortunately it always does a full table space scan to retrieve the path
        // regardless whether indices on lft and rgt are set or not.
        // (At least, this is what happens on MySQL 4.1).
        // This algorithms performs well for small trees which are deeply nested.

        if ($this->getTree()->__isMainTree()) {
            $fields = array('integer');
            $data = array($a_endnode_id);
            $query = "SELECT T2.child " .
                "FROM " . $this->getTree()->getTreeTable() . " T1, " . $this->getTree()->getTreeTable() . " T2 " .
                "WHERE T1.child = %s " .
                "AND T1.lft BETWEEN T2.lft AND T2.rgt " .
                "ORDER BY T2.depth";
        } else {
            $fields = array('integer', 'integer', 'integer');
            $data = array($a_endnode_id, $this->getTree()->getTreeId(), $this->getTree()->getTreeId());
            $query = "SELECT T2.child " .
                "FROM " . $this->getTree()->getTreeTable() . " T1, " . $this->getTree()->getTreeTable() . " T2 " .
                "WHERE T1.child = %s " .
                "AND T1.lft BETWEEN T2.lft AND T2.rgt " .
                "AND T1." . $this->getTree()->getTreePk() . " = %s " .
                "AND T2." . $this->getTree()->getTreePk() . " = %s " .
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
     * @inheritdoc
     */
    public function moveTree(int $a_source_id, int $a_target_id, int $a_position): void
    {
        $move_tree_callable = function (ilDBInterface $ilDB) use ($a_source_id, $a_target_id, $a_position): void {
            // Receive node infos for source and target
            $query = 'SELECT * FROM ' . $this->getTree()->getTreeTable() . ' ' .
                'WHERE ( child = %s OR child = %s ) ' .
                'AND ' . $this->getTree()->getTreePk() . ' = %s ';
            $res = $this->db->queryF($query, array('integer', 'integer', 'integer'), array(
                $a_source_id,
                $a_target_id,
                $this->getTree()->getTreeId()
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

            $query = 'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                'lft = CASE WHEN lft >  %s THEN lft + %s ELSE lft END, ' .
                'rgt = CASE WHEN rgt >= %s THEN rgt + %s ELSE rgt END ';

            if ($this->getTree()->__isMainTree()) {
                $res = $this->db->manipulateF($query, array('integer', 'integer', 'integer', 'integer'), [
                    $target_rgt,
                    $spread_diff,
                    $target_rgt,
                    $spread_diff
                ]);
            } else {
                $query .= ('WHERE ' . $this->getTree()->getTreePk() . ' = %s ');
                $res = $this->db->manipulateF(
                    $query,
                    array('integer', 'integer', 'integer', 'integer', 'integer'),
                    array(
                        $target_rgt,
                        $spread_diff,
                        $target_rgt,
                        $spread_diff,
                        $this->getTree()->getTreeId()
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

            $query = 'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                'parent = CASE WHEN parent = %s THEN %s ELSE parent END, ' .
                'rgt = rgt + %s, ' .
                'lft = lft + %s, ' .
                'depth = depth + %s ' .
                'WHERE lft >= %s ' .
                'AND rgt <= %s ';

            if ($this->getTree()->__isMainTree()) {
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
                $query .= 'AND ' . $this->getTree()->getTreePk() . ' = %s ';
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
                        $this->getTree()->getTreeId()
                    )
                );
            }

            // done: close old gap
            $query = 'UPDATE ' . $this->getTree()->getTreeTable() . ' SET ' .
                'lft = CASE WHEN lft >= %s THEN lft - %s ELSE lft END, ' .
                'rgt = CASE WHEN rgt >= %s THEN rgt - %s ELSE rgt END ';

            if ($this->getTree()->__isMainTree()) {
                $res = $this->db->manipulateF($query, array('integer', 'integer', 'integer', 'integer'), [
                    $source_lft + $where_offset,
                    $spread_diff,
                    $source_rgt + $where_offset,
                    $spread_diff
                ]);
            } else {
                $query .= ('WHERE ' . $this->getTree()->getTreePk() . ' = %s ');

                $res = $this->db->manipulateF(
                    $query,
                    array('integer', 'integer', 'integer', 'integer', 'integer'),
                    array(
                        $source_lft + $where_offset,
                        $spread_diff,
                        $source_rgt + $where_offset,
                        $spread_diff,
                        $this->getTree()->getTreeId()
                    )
                );
            }
        };

        if ($this->getTree()->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock('tree');
            $ilAtomQuery->addQueryCallable($move_tree_callable);
            $ilAtomQuery->run();
        } else {
            $move_tree_callable($this->db);
        }
    }

    /**
     * @param int $a_endnode_id
     * @return array<int, array{lft: int, rgt: int, child: int, type: string}>
     */
    public function getSubtreeInfo(int $a_endnode_id): array
    {
        $query = "SELECT t2.lft lft, t2.rgt rgt, t2.child child, type " .
            "FROM " . $this->getTree()->getTreeTable() . " t1 " .
            "JOIN " . $this->getTree()->getTreeTable() . " t2 ON (t2.lft BETWEEN t1.lft AND t1.rgt) " .
            "JOIN " . $this->getTree()->getTableReference() . " obr ON t2.child = obr.ref_id " .
            "JOIN " . $this->getTree()->getObjectDataTable() . " obd ON obr.obj_id = obd.obj_id " .
            "WHERE t1.child = " . $this->db->quote($a_endnode_id, 'integer') . " " .
            "AND t1." . $this->getTree()->getTreePk() . " = " . $this->db->quote(
                $this->getTree()->getTreeId(),
                'integer'
            ) . " " .
            "AND t2." . $this->getTree()->getTreePk() . " = " . $this->db->quote(
                $this->getTree()->getTreeId(),
                'integer'
            ) . " " .
            "ORDER BY t2.lft";

        $res = $this->db->query($query);
        $nodes = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $nodes[(int) $row->child]['lft'] = (int) $row->lft;
            $nodes[(int) $row->child]['rgt'] = (int) $row->rgt;
            $nodes[(int) $row->child]['child'] = (int) $row->child;
            $nodes[(int) $row->child]['type'] = (string) $row->type;
        }
        return $nodes;
    }

    /**
     * @inheritdoc
     * @todo add unit test; check failure result
     * @fixme fix $row access
     */
    public function validateParentRelations(): array
    {
        $query = 'select child from ' . $this->getTree()->getTreeTable() . ' child where not exists ' .
            '( ' .
            'select child from ' . $this->getTree()->getTreeTable() . ' parent where child.parent = parent.child and (parent.lft < child.lft) and (parent.rgt > child.rgt) ' .
            ')' .
            'and ' . $this->getTree()->getTreePk() . ' = ' . $this->getTree()->getTreeId() . ' and child <> 1';
        $res = $this->db->query($query);

        $failures = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $failures[] = $row[$this->getTree()->getTreePk()];
        }
        return $failures;
    }
}
