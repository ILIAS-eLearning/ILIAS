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
 * Base class for materialize path based trees
 * Based on implementation of Werner Randelshofer
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesTree
 */
class ilMaterializedPathTree implements ilTreeImplementation
{
    private const MAXIMUM_POSSIBLE_DEPTH = 100;
    public const RELATION_CHILD = 1;
    public const RELATION_PARENT = 2;
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
        if (ilContext::getType() != "") {
            $this->logger = ilLoggerFactory::getLogger('tree');
        }
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

    /**
     * Get maximum possible depth
     */
    protected function getMaximumPossibleDepth(): int
    {
        return self::MAXIMUM_POSSIBLE_DEPTH;
    }

    public function getRootId(): int
    {
        return $this->root_id;
    }

    public function getTreeId(): int
    {
        return $this->tree_id;
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

    public function getObjectDataTable(): string
    {
        return $this->table_obj_data;
    }

    /**
     * Get subtree ids
     * @param int $a_node_id
     * @return int[]
     */
    public function getSubTreeIds(int $a_node_id): array
    {
        $node = $this->getNodeTreeData($a_node_id);
        $query = 'SELECT child FROM ' . $this->getTreeTable() . ' ' .
            'WHERE path BETWEEN ' .
            $this->db->quote($node['path'], 'text') . ' AND ' .
            $this->db->quote($node['path'] . '.Z', 'text') . ' ' .
            'AND child != %s ' .
            'AND ' . $this->getTreePk() . ' = %s';

        $res = $this->db->queryF(
            $query,
            array('integer', 'integer'),
            array($a_node_id, $this->getTreeId())
        );
        $childs = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $childs[] = (int) $row['child'];
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

    /**
     * @inheritdoc
     * @todo add test
     */
    public function getRelation(array $a_node_a, array $a_node_b): int
    {
        if ($a_node_a === [] || $a_node_b === []) {
            return ilTree::RELATION_NONE;
        }
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
    ): string {
        $type_str = '';
        if (is_array($a_types)) {
            if ($a_types) {
                $type_str = "AND " . $this->db->in(
                    $this->getObjectDataTable() . ".type",
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

        // @todo order by
        $query = 'SELECT ' .
            $fields . ' ' .
            'FROM ' . $this->getTreeTable() . ' ' .
            $join . ' ' .
            'WHERE ' . $this->getTreeTable() . '.path ' .
            'BETWEEN ' .
            $this->db->quote($a_node['path'], 'text') . ' AND ' .
            $this->db->quote($a_node['path'] . '.Z', 'text') . ' ' .
            'AND ' . $this->getTreeTable() . '.' . $this->getTreePk() . ' < 0 ' .
            $type_str . ' ' .
            'ORDER BY ' . $this->getTreeTable() . '.path';

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
    ): string {
        $type_str = '';
        if (count($a_types)) {
            if ($a_types) {
                $type_str = "AND " . $this->db->in(
                    $this->getObjectDataTable() . ".type",
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

        // @todo order by
        $query = 'SELECT ' .
            $fields . ' ' .
            'FROM ' . $this->getTreeTable() . ' ' .
            $join . ' ' .
            'WHERE ' . $this->getTreeTable() . '.path ' .
            'BETWEEN ' .
            $this->db->quote($a_node['path'], 'text') . ' AND ' .
            $this->db->quote($a_node['path'] . '.Z', 'text') . ' ' .
            'AND ' . $this->getTreeTable() . '.' . $this->getTreePk() . ' = ' . $this->db->quote(
                $this->getTreeId(),
                'integer'
            ) . ' ' .
            $type_str . ' ' .
            'ORDER BY ' . $this->getTreeTable() . '.path';

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function getPathIds(int $a_endnode, int $a_startnode = 0): array
    {
        $this->db->setLimit(1, 0);
        $query = 'SELECT path FROM ' . $this->getTreeTable() . ' ' .
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
    public function insertNode(int $a_node_id, int $a_parent_id, int $a_pos): void
    {
        $insert_node_callable = function (ilDBInterface $ilDB) use ($a_node_id, $a_parent_id, $a_pos): void {
            // get path and depth of parent
            $this->db->setLimit(1, 0);

            $res = $this->db->queryF(
                'SELECT parent, depth, path FROM ' . $this->getTreeTable() . ' ' .
                'WHERE child = %s ' . ' ' .
                'AND ' . $this->getTreePk() . ' = %s',
                array('integer', 'integer'),
                array($a_parent_id, $this->getTreeId())
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
                $this->getTreeTable(),
                array($this->getTreePk() => array('integer', $this->getTreeId()),
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
        if ($this->__isMainTree()) {
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
    public function deleteTree(int $a_node_id): void
    {
        $delete_tree_callable = function (ilDBInterface $ilDB) use ($a_node_id): void {
            $query = 'SELECT * FROM ' . $this->getTreeTable() . ' ' .
                'WHERE ' . $this->getTreeTable() . '.child = %s ' .
                'AND ' . $this->getTreeTable() . '.' . $this->getTreePk() . ' = %s ';
            $res = $this->db->queryF($query, array('integer', 'integer'), array(
                $a_node_id,
                $this->getTreeId()
            ));
            $row = $this->db->fetchAssoc($res);

            $query = 'DELETE FROM ' . $this->getTreeTable() . ' ' .
                'WHERE path BETWEEN ' . $this->db->quote($row['path'], 'text') . ' ' .
                'AND ' . $this->db->quote($row['path'] . '.Z', 'text') . ' ' .
                'AND ' . $this->getTreePk() . ' = ' . $this->db->quote(
                    $this->getTreeId(),
                    'integer'
                );
            $this->db->manipulate($query);
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

    /**
     * @inheritdoc
     */
    public function moveToTrash(int $a_node_id): void
    {
        $move_to_trash_callable = function (ilDBInterface $ilDB) use ($a_node_id): void {
            $node = $this->getNodeTreeData($a_node_id);

            // Set the nodes deleted (negative tree id)
            $this->db->manipulateF(
                '
				UPDATE ' . $this->getTreeTable() . ' ' .
                'SET tree = %s' . ' ' .
                'WHERE ' . $this->getTreePk() . ' = %s ' .
                'AND path BETWEEN %s AND %s',
                array('integer', 'integer', 'text', 'text'),
                array(-$a_node_id, $this->getTreeId(), $node['path'], $node['path'] . '.Z')
            );
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

    /**
     * @inheritdoc
     * @todo check "$this->db->substr(..." call with parameters
     */
    public function moveTree(int $a_source_id, int $a_target_id, int $a_position): void
    {
        $move_tree_callable = function (ilDBInterface $ilDB) use ($a_source_id, $a_target_id, $a_position): void {
            // Receive node infos for source and target
            $this->db->setLimit(2, 0);

            $res = $this->db->query(
                'SELECT depth, child, parent, path FROM ' . $this->getTreeTable() . ' ' .
                'WHERE ' . $this->db->in('child', array($a_source_id, $a_target_id), false, 'integer') . ' ' .
                'AND tree = ' . $this->db->quote($this->getTreeId(), 'integer')
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
                    'FROM    ' . $this->getTreeTable() . ' ' .
                    'WHERE   path BETWEEN %s AND %s' . ' ' .
                    'AND     tree = %s ',
                    array('text', 'text', 'integer'),
                    array($source_path, $source_path . '.Z', $this->getTreeId())
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
                'UPDATE ' . $this->getTreeTable() . ' ' .
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

            if (!$this->__isMainTree()) {
                $query .= ('AND ' . $this->db->quote($this->getTreeId(), \ilDBConstants::T_INTEGER));
            }
            $this->db->manipulate($query);
        };

        if ($this->__isMainTree()) {
            $ilAtomQuery = $this->db->buildAtomQuery();
            $ilAtomQuery->addTableLock("tree");
            $ilAtomQuery->addQueryCallable($move_tree_callable);
            $ilAtomQuery->run();
        } else {
            $move_tree_callable($this->db);
        }
    }

    public static function createFromParentRelation(ilDBInterface $db): void
    {
        $result = $db->queryF('SELECT DISTINCT * FROM tree WHERE parent = %s', ['integer'], [0]);

        while ($row = $db->fetchAssoc($result)) {
            self::createMaterializedPath($db, 0, '');
        }
    }

    private static function createMaterializedPath(ilDBInterface $db, int $parent, string $parentPath): void
    {
        $q = ' UPDATE tree
			SET path = CONCAT(COALESCE(' . $db->quote($parentPath, 'text') . ', \'\'), COALESCE( ' . $db->cast(
            "child",
            "text"
        ) . ' , \'\')) WHERE parent = %s';
        $db->manipulateF($q, ['integer'], [$parent]);
        $result = $db->queryF('SELECT child FROM tree WHERE parent = %s', ['integer'], [$parent]);

        while ($row = $db->fetchAssoc($result)) {
            self::createMaterializedPath(
                $db,
                (int) $row['child'],
                $parentPath . $row['child'] . '.'
            );
        }
    }

    /**
     * @param int $a_endnode_id
     * @return array
     */
    public function getSubtreeInfo(int $a_endnode_id): array
    {
        if ($this->__isMainTree() && $this->getTreeId() == 1) {
            $treeClause1 = '';
            $treeClause2 = '';
        } else {
            $treeClause1 = ' AND t1.' . $this->getTreePk() . ' = ' . $this->db->quote(
                $this->getTreeId(),
                'integer'
            );
            $treeClause2 = ' AND t2.' . $this->getTreePk() . ' = ' . $this->db->quote(
                $this->getTreeId(),
                'integer'
            );
        }

        // first query for the path of the given node
        $query = "
            SELECT t1." . $this->getTreePk() . ", t1.path
            FROM " . $this->getTreeTable() . " t1 
            WHERE t1.child = " . $this->db->quote($a_endnode_id, 'integer') .
            $treeClause1;

        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);
        if ($row[$this->getTreePk()] ?? null == $this->getTreeId()) {
            $path = (string) $row['path'];
        } else {
            return [];
        }

        // then query for the nodes in that path
        $query = "SELECT t2." . $this->getTreePk() . ", t2.child child, t2.parent parent, type, t2.path path " .
            "FROM " . $this->getTreeTable() . " t2 " .
            "JOIN " . $this->getTableReference() . " obr ON t2.child = obr.ref_id " .
            "JOIN " . $this->getObjectDataTable() . " obd ON obr.obj_id = obd.obj_id " .
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
            if ($row[$this->getTreePk()] != $this->getTreeId()) {
                continue;
            }

            $nodes[$row['child']]['child'] = (int) $row['child'];
            $nodes[$row['child']]['parent'] = (int) $row['parent'];
            $nodes[$row['child']]['type'] = (string) $row['type'];
            $nodes[$row['child']]['path'] = (string) $row['path'];
        }

        $depth_first_compare = static function (array $a, array $b): int {
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
    public function validateParentRelations(): array
    {
        $query = 'select child from ' . $this->getTreeTable() . ' child where not exists ' .
            '( ' .
            'select child from ' . $this->getTreeTable() . ' parent where child.parent = parent.child and ' .
            '(child.path BETWEEN parent.path AND CONCAT(parent.path,' . $this->db->quote('Z', 'text') . ') )' . ')' .
            'and ' . $this->getTreePk() . ' = ' . $this->getTreeId() . ' and child <> 1';
        $res = $this->db->query($query);
        $failures = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $failures[] = $row[$this->getTreePk()];
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

    public function useCache(bool $use = true): void
    {
        $this->use_cache = $use;
    }

    public function isCacheUsed(): bool
    {
        return $this->__isMainTree() && $this->use_cache;
    }

    /**
     * checks if a node is in the path of an other node
     */
    public function isGrandChild(int $a_startnode_id, int $a_querynode_id): bool
    {
        return $this->getRelation($a_startnode_id, $a_querynode_id) == self::RELATION_PARENT;
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

    protected function __isMainTree(): bool
    {
        return $this->table_tree === 'tree';
    }
}
