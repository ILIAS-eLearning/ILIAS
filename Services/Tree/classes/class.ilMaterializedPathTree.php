<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base class for materialize path based trees
 * Based on implementation of Werner Randelshofer
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesTree
 */
class ilMaterializedPathTree implements ilTreeImplementation
{
    private const MAXIMUM_POSSIBLE_DEPTH = 100;

    protected ilTree $tree;
    protected ilDBInterface $db;
    protected ilLogger $logger;

    /**
     * Constructor
     * @param ilTree $a_tree
     */
    public function __construct(ilTree $a_tree)
    {
        global $DIC;

        $this->tree = $a_tree;
        $this->db = $DIC->database();
        if (ilContext::getType() != "") {
            $this->logger = $DIC->logger()->tree();
        }
    }

    /**
     * Get maximum possible depth
     */
    protected function getMaximumPossibleDepth() : int
    {
        return self::MAXIMUM_POSSIBLE_DEPTH;
    }

    /**
     * Get tree object
     */
    public function getTree() : \ilTree
    {
        return $this->tree;
    }

    /**
     * Get subtree ids
     * @param int $a_node_id
     * @return int[]
     */
    public function getSubTreeIds(int $a_node_id) : array
    {
        $node = $this->getTree()->getNodeTreeData($a_node_id);
        $query = 'SELECT child FROM ' . $this->getTree()->getTreeTable() . ' ' .
            'WHERE path BETWEEN ' .
            $this->db->quote($node['path'], 'text') . ' AND ' .
            $this->db->quote($node['path'] . '.Z', 'text') . ' ' .
            'AND child != %s ' .
            'AND ' . $this->getTree()->getTreePk() . ' = %s';

        $res = $this->db->queryF(
            $query,
            array('integer', 'integer'),
            array($a_node_id, $this->getTree()->getTreeId())
        );
        $childs = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $childs[] = (int) $row['child'];
        }
        return $childs;
    }

    /**
     * @inheritdoc
     * @todo add test
     */
    public function getRelation(array $a_node_a, array $a_node_b) : int
    {
        if ($a_node_a['child'] == $a_node_b['child']) {
            return ilTree::RELATION_EQUALS;
        }
        if (stripos($a_node_a['path'], $a_node_b['path'] . '.') === 0) {
            return ilTree::RELATION_CHILD;
        }
        if (stripos($a_node_b['path'], $a_node_a['path'] . '.') === 0) {
            return ilTree::RELATION_PARENT;
        }
        $path_a = substr($a_node_a['path'], 0, strrpos($a_node_a['path'], '.'));
        $path_b = substr($a_node_b['path'], 0, strrpos($a_node_b['path'], '.'));
        if ($a_node_a['path'] && $path_a === $path_b) {
            return ilTree::RELATION_SIBLING;
        }
        return ilTree::RELATION_NONE;
    }

    /**
     * @inheritdoc
     */
    public function getTrashSubTreeQuery(
        array $a_node,
        array $a_types,
        bool $a_force_join_reference = true,
        array $a_fields = []
    ) : string {
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

        // @todo order by
        $query = 'SELECT ' .
            $fields . ' ' .
            'FROM ' . $this->getTree()->getTreeTable() . ' ' .
            $join . ' ' .
            'WHERE ' . $this->getTree()->getTreeTable() . '.path ' .
            'BETWEEN ' .
            $this->db->quote($a_node['path'], 'text') . ' AND ' .
            $this->db->quote($a_node['path'] . '.Z', 'text') . ' ' .
            'AND ' . $this->getTree()->getTreeTable() . '.' . $this->getTree()->getTreePk() . ' < 0 ' .
            $type_str . ' ' .
            'ORDER BY ' . $this->getTree()->getTreeTable() . '.path';

        return $query;
    }

    /**
     * Get subtree query
     * @param array $a_node
     * @param array $a_types
     * @param bool  $a_force_join_reference
     * @param array $a_fields
     * @return string query
     */
    public function getSubTreeQuery(
        array $a_node,
        array $a_types = [],
        bool $a_force_join_reference = true,
        array $a_fields = []
    ) : string {
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

        // @todo order by
        $query = 'SELECT ' .
            $fields . ' ' .
            'FROM ' . $this->getTree()->getTreeTable() . ' ' .
            $join . ' ' .
            'WHERE ' . $this->getTree()->getTreeTable() . '.path ' .
            'BETWEEN ' .
            $this->db->quote($a_node['path'], 'text') . ' AND ' .
            $this->db->quote($a_node['path'] . '.Z', 'text') . ' ' .
            'AND ' . $this->getTree()->getTreeTable() . '.' . $this->getTree()->getTreePk() . ' = ' . $this->db->quote(
                $this->getTree()->getTreeId(),
                'integer'
            ) . ' ' .
            $type_str . ' ' .
            'ORDER BY ' . $this->getTree()->getTreeTable() . '.path';

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function getPathIds(int $a_endnode, int $a_startnode = 0) : array
    {
        $this->db->setLimit(1, 0);
        $query = 'SELECT path FROM ' . $this->getTree()->getTreeTable() . ' ' .
            'WHERE child = ' . $this->db->quote($a_endnode, 'integer') . ' ';
        $res = $this->db->query($query);

        $path = "";
        while ($row = $this->db->fetchAssoc($res)) {
            $path = (string) $row['path'];
        }

        $pathIds = array_map('intval', explode('.', $path));

        if ($a_startnode != 0) {
            while (count($pathIds) > 0 && $pathIds[0] != $a_startnode) {
                array_shift($pathIds);
            }
        }
        return $pathIds;
    }

    /**
     * @inheritdoc
     */
    public function insertNode(int $a_node_id, int $a_parent_id, int $a_pos) : void
    {
        $insert_node_callable = function (ilDBInterface $ilDB) use ($a_node_id, $a_parent_id, $a_pos) : void {
            // get path and depth of parent
            $this->db->setLimit(1, 0);

            $res = $this->db->queryF(
                'SELECT parent, depth, path FROM ' . $this->getTree()->getTreeTable() . ' ' .
                'WHERE child = %s ' . ' ' .
                'AND ' . $this->getTree()->getTreePk() . ' = %s',
                array('integer', 'integer'),
                array($a_parent_id, $this->getTree()->getTreeId())
            );

            $r = $this->db->fetchObject($res);

            if ($r->parent === null) {
                $this->logger->logStack(ilLogLevel::ERROR);
                throw new ilInvalidTreeStructureException('Parent node not found in tree');
            }

            if ($r->depth >= $this->getMaximumPossibleDepth()) {
                $this->logger->logStack(ilLogLevel::ERROR);
                throw new ilInvalidTreeStructureException('Maximum tree depth exceeded');
            }

            $parentPath = $r->path;
            $depth = (int) $r->depth + 1;
            $lft = 0;
            $rgt = 0;

            $this->db->insert(
                $this->getTree()->getTreeTable(),
                array($this->getTree()->getTreePk() => array('integer', $this->getTree()->getTreeId()),
                      'child' => array('integer', $a_node_id),
                      'parent' => array('integer', $a_parent_id),
                      'lft' => array('integer', $lft),
                      'rgt' => array('integer', $rgt),
                      'depth' => array('integer', $depth),
                      'path' => array('text', $parentPath . "." . $a_node_id)
                )
            );
        };

        // use ilAtomQuery to lock tables if tree is main tree
        // otherwise just call this closure without locking
        if ($this->getTree()->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock("tree");
            $ilAtomQuery->addQueryCallable($insert_node_callable);
            $ilAtomQuery->run();
        } else {
            $insert_node_callable($this->db);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteTree(int $a_node_id) : void
    {
        $delete_tree_callable = function (ilDBInterface $ilDB) use ($a_node_id) : void {
            $query = 'SELECT * FROM ' . $this->getTree()->getTreeTable() . ' ' .
                'WHERE ' . $this->getTree()->getTreeTable() . '.child = %s ' .
                'AND ' . $this->getTree()->getTreeTable() . '.' . $this->getTree()->getTreePk() . ' = %s ';
            $res = $this->db->queryF($query, array('integer', 'integer'), array(
                $a_node_id,
                $this->getTree()->getTreeId()
            ));
            $row = $this->db->fetchAssoc($res);

            $query = 'DELETE FROM ' . $this->getTree()->getTreeTable() . ' ' .
                'WHERE path BETWEEN ' . $this->db->quote($row['path'], 'text') . ' ' .
                'AND ' . $this->db->quote($row['path'] . '.Z', 'text') . ' ' .
                'AND ' . $this->getTree()->getTreePk() . ' = ' . $this->db->quote(
                    $this->getTree()->getTreeId(),
                    'integer'
                );
            $this->db->manipulate($query);
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
    public function moveToTrash(int $a_node_id) : void
    {
        $move_to_trash_callable = function (ilDBInterface $ilDB) use ($a_node_id) : void {
            $node = $this->getTree()->getNodeTreeData($a_node_id);

            // Set the nodes deleted (negative tree id)
            $this->db->manipulateF(
                '
				UPDATE ' . $this->getTree()->getTreeTable() . ' ' .
                'SET tree = %s' . ' ' .
                'WHERE ' . $this->getTree()->getTreePk() . ' = %s ' .
                'AND path BETWEEN %s AND %s',
                array('integer', 'integer', 'text', 'text'),
                array(-$a_node_id, $this->getTree()->getTreeId(), $node['path'], $node['path'] . '.Z')
            );
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
     * @inheritdoc
     * @todo check "$this->db->substr(..." call with parameters
     */
    public function moveTree(int $a_source_id, int $a_target_id, int $a_position) : void
    {
        $move_tree_callable = function (ilDBInterface $ilDB) use ($a_source_id, $a_target_id, $a_position) : void {
            // Receive node infos for source and target
            $this->db->setLimit(2, 0);

            $res = $this->db->query(
                'SELECT depth, child, parent, path FROM ' . $this->getTree()->getTreeTable() . ' ' .
                'WHERE ' . $this->db->in('child', array($a_source_id, $a_target_id), false, 'integer') . ' ' .
                'AND tree = ' . $this->db->quote($this->getTree()->getTreeId(), 'integer')
            );

            // Check in tree
            if ($this->db->numRows($res) != 2) {
                $this->logger->logStack(ilLogLevel::ERROR, 'Objects not found in tree');
                throw new InvalidArgumentException('Error moving subtree');
            }

            $source_depth = $target_depth = 0;
            $source_path = $target_path = '';
            $source_parent = 0;
            while ($row = $this->db->fetchObject($res)) {
                if ($row->child == $a_source_id) {
                    $source_path = $row->path;
                    $source_depth = $row->depth;
                    $source_parent = $row->parent;
                } else {
                    $target_path = $row->path;
                    $target_depth = $row->depth;
                }
            }

            if ($target_depth >= $source_depth) {
                // We move nodes deeper into the tree. Therefore we need to
                // check whether we might exceed the maximal path length.
                // We use FOR UPDATE here, because we don't want anyone to
                // insert new nodes while we move the subtree.

                $res = $this->db->queryF(
                    'SELECT  MAX(depth) max_depth ' .
                    'FROM    ' . $this->getTree()->getTreeTable() . ' ' .
                    'WHERE   path BETWEEN %s AND %s' . ' ' .
                    'AND     tree = %s ',
                    array('text', 'text', 'integer'),
                    array($source_path, $source_path . '.Z', $this->getTree()->getTreeId())
                );

                $row = $this->db->fetchObject($res);

                if ($row->max_depth - $source_depth + $target_depth + 1 > $this->getMaximumPossibleDepth()) {
                    $this->logger->logStack(ilLogLevel::ERROR, 'Objects not found in tree');
                    throw new ilInvalidTreeStructureException('Maximum tree depth exceeded');
                }
            }
            // Check target not child of source
            if ((substr($target_path . '.', 0, strlen($source_path)) . '.') == $source_path . '.') {
                $this->logger->logStack(ilLogLevel::ERROR, 'Target is child of source');
                throw new InvalidArgumentException('Error moving subtree: target is child of source');
            }
            $depth_diff = $target_depth - $source_depth + 1;

            // move subtree:
            $query =
                'UPDATE ' . $this->getTree()->getTreeTable() . ' ' .
                'SET parent = CASE WHEN parent = ' . $this->db->quote($source_parent, 'integer') . ' ' .
                'THEN ' . $this->db->quote($a_target_id, 'integer') . ' ' .
                'ELSE parent END, path = ' .
                $this->db->concat(array(
                    array($this->db->quote($target_path, 'text'), 'text'),
                    array($this->db->substr('path', strrpos('.' . $source_path, '.')), 'text')
                )) . ' ' .
                ',depth = depth + ' . $this->db->quote($depth_diff, 'integer') . ' ' .
                'WHERE path  BETWEEN ' . $this->db->quote($source_path, 'text') . ' ' .
                'AND ' . $this->db->quote($source_path . '.Z', 'text') . ' ';

            if (!$this->getTree()->__isMainTree()) {
                $query .= ('AND ' . $this->db->quote($this->getTree()->getTreeId(), \ilDBConstants::T_INTEGER));
            }
            $this->db->manipulate($query);
        };

        if ($this->getTree()->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock("tree");
            $ilAtomQuery->addQueryCallable($move_tree_callable);
            $ilAtomQuery->run();
        } else {
            $move_tree_callable($this->db);
        }
    }

    public static function createFromParentReleation() : void
    {
        global $DIC;

        $db = $DIC->database();
        $r = $db->queryF('SELECT DISTINCT * FROM tree WHERE parent = %s', array('integer'), array(0));

        while ($row = $db->fetchAssoc($r)) {
            self::createMaterializedPath(0, '');
        }
    }

    /**
     * @param int    $parent
     * @param string $parentPath
     * @return bool
     */
    private static function createMaterializedPath(int $parent, string $parentPath) : bool
    {
        global $DIC;

        $db = $DIC->database();

        $q = ' UPDATE tree
			SET path = CONCAT(COALESCE(' . $db->quote($parentPath, 'text') . ', \'\'), COALESCE( ' . $db->cast(
            "child",
            "text"
        ) . ' , \'\'))
			WHERE parent = %s';
        $r = $db->manipulateF($q, array('integer'), array($parent));

        $r = $db->queryF('SELECT child FROM tree WHERE parent = %s', array('integer'), array($parent));

        while ($row = $db->fetchAssoc($r)) {
            self::createMaterializedPath(
                (int) $row['child'],
                $parentPath . $row['child'] . '.'
            );
        }
        return true;
    }

    /**
     * @param int $a_endnode_id
     * @return array
     */
    public function getSubtreeInfo(int $a_endnode_id) : array
    {
        if ($this->getTree()->__isMainTree() && $this->getTree()->getTreeId() == 1) {
            $treeClause1 = '';
            $treeClause2 = '';
        } else {
            $treeClause1 = ' AND t1.' . $this->getTree()->getTreePk() . ' = ' . $this->db->quote(
                $this->getTree()->getTreeId(),
                'integer'
            );
            $treeClause2 = ' AND t2.' . $this->getTree()->getTreePk() . ' = ' . $this->db->quote(
                $this->getTree()->getTreeId(),
                'integer'
            );
        }

        // first query for the path of the given node
        $query = "
            SELECT t1." . $this->getTree()->getTreePk() . ", t1.path
            FROM " . $this->getTree()->getTreeTable() . " t1 
            WHERE t1.child = " . $this->db->quote($a_endnode_id, 'integer') .
            $treeClause1;

        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);
        if ($row[$this->getTree()->getTreePk()] == $this->getTree()->getTreeId()) {
            $path = (string) $row['path'];
        } else {
            return [];
        }

        // then query for the nodes in that path
        $query = "SELECT t2." . $this->getTree()->getTreePk() . ", t2.child child, type, t2.path path " .
            "FROM " . $this->getTree()->getTreeTable() . " t2 " .
            "JOIN " . $this->getTree()->getTableReference() . " obr ON t2.child = obr.ref_id " .
            "JOIN " . $this->getTree()->getObjectDataTable() . " obd ON obr.obj_id = obd.obj_id " .
            "WHERE t2.path BETWEEN " . $this->db->quote($path, 'text') . " AND " . $this->db->quote(
                $path . '.Z',
                'text'
            ) .
            $treeClause2 . ' ' .
            "ORDER BY t2.path";

        $res = $this->db->query($query);
        $nodes = [];
        while ($row = $this->db->fetchAssoc($res)) {
            // filter out deleted items if tree is repository
            if ($row[$this->getTree()->getTreePk()] != $this->getTree()->getTreeId()) {
                continue;
            }

            $nodes[$row['child']]['child'] = (int) $row['child'];
            $nodes[$row['child']]['type'] = (string) $row['type'];
            $nodes[$row['child']]['path'] = (string) $row['path'];
        }

        $depth_first_compare = static function (array $a, array $b) : int {
            $a_exploded = explode('.', $a['path']);
            $b_exploded = explode('.', $b['path']);

            $a_padded = '';
            foreach ($a_exploded as $num) {
                $a_padded .= (str_pad((string) $num, 14, '0', STR_PAD_LEFT));
            }
            $b_padded = '';
            foreach ($b_exploded as $num) {
                $b_padded .= (str_pad((string) $num, 14, '0', STR_PAD_LEFT));
            }

            return strcasecmp($a_padded, $b_padded);
        };

        uasort($nodes, $depth_first_compare);

        return $nodes;
    }

    /**
     * @inheritdoc
     */
    public function validateParentRelations() : array
    {
        $query = 'select child from ' . $this->getTree()->getTreeTable() . ' child where not exists ' .
            '( ' .
            'select child from ' . $this->getTree()->getTreeTable() . ' parent where child.parent = parent.child and ' .
            '(child.path BETWEEN parent.path AND CONCAT(parent.path,' . $this->db->quote('Z', 'text') . ') )' . ')' .
            'and ' . $this->getTree()->getTreePk() . ' = ' . $this->getTree()->getTreeId() . ' and child <> 1';
        $res = $this->db->query($query);
        $failures = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $failures[] = $row[$this->getTree()->getTreePk()];
        }
        return $failures;
    }
}
