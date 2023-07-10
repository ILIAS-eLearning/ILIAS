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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTree
 */
class ilTreeTrashQueries
{
    protected const QUERY_LIMIT = 10;

    private int $ref_id = 0;

    private ilTree $tree;

    private ilLogger $logger;

    private ilDBInterface $db;

    /**
     * ilTreeTrash constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->logger = $DIC->logger()->tree();
        $this->tree = $DIC->repositoryTree();
    }

    /**
     * @param int[] $ref_ids
     * @return bool
     */
    public function isTrashedTrash(array $ref_ids): bool
    {
        $query = 'select tree,child,parent from tree ' .
            'where ' . $this->db->in('child', $ref_ids, false, \ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        $trashed_trash = false;
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            if ((int) $row->child != ((int) $row->tree * -1)) {
                $trashed_trash = true;
            }
            if ($this->tree->isDeleted((int) $row->parent)) {
                $trashed_trash = true;
            }
        }
        return $trashed_trash;
    }

    /**
     * @param int $deleted_node
     * @return int $rep_ref_id
     * @throws \ilRepositoryException
     */
    public function findRepositoryLocationForDeletedNode(int $deleted_node): int
    {
        $query = 'select parent from tree ' .
            'where child = ' . $this->db->quote($deleted_node, \ilDBConstants::T_INTEGER) . ' ' .
            'and tree = ' . $this->db->quote($deleted_node * -1, \ilDBConstants::T_INTEGER);
        $this->logger->debug($query);
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->parent;
        }
        $this->logger->warning('Invalid node given for restoring to original location: deleted node id: ' . $deleted_node);
        throw new \ilRepositoryException('Invalid node given for restoring to original location');
    }

    /**
     * @param int $ref_id
     * @return string[]
     */
    public function getTrashedNodeTypesForContainer(int $ref_id): array
    {
        $subtreequery = $this->tree->getTrashSubTreeQuery($ref_id, ['child']);
        $query = 'select distinct(type) obj_type from object_data obd ' .
            'join object_reference obr on obd.obj_id = obr.obj_id ' .
            'where ref_id in (' .
            $subtreequery . ' ' .
            ') ';

        $obj_types = [];
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_types[] = (string) $row->obj_type;
        }
        return $obj_types;
    }

    /**
     * @param int $ref_id
     * @return int
     */
    public function getNumberOfTrashedNodesForTrashedContainer(int $ref_id): int
    {
        $res = $this->db->query($this->tree->getTrashSubTreeQuery($ref_id, ['child']));
        return $res->numRows();
    }

    /**
     * Get trashed nodes
     * @return ilTreeTrashItem[]
     * @throws \ilDatabaseException
     */
    public function getTrashNodeForContainer(
        int $ref_id,
        array $filter,
        int &$max_entries,
        string $order_field,
        string $order_direction,
        int $limit = self::QUERY_LIMIT,
        int $offset = 0
    ): array {
        $subtreequery = $this->tree->getTrashSubTreeQuery($ref_id, ['child']);

        $select = 'SELECT ref_id, obd.obj_id, type, title, description, deleted, deleted_by ';
        $select_count = 'SELECT COUNT(ref_id) max_entries ';

        $from = 'FROM object_data obd ' .
            'JOIN object_reference obr ON obd.obj_id = obr.obj_id ' .
            'WHERE ref_id IN (' .
            $subtreequery . ' ' .
            ') ';

        $order = ' ';
        if ($order_field) {
            $order = 'ORDER BY ' . $order_field . ' ' . $order_direction;
        }

        $query = $select . $from . $this->appendTrashNodeForContainerQueryFilter($filter) . $order;
        $query_count = $select_count . $from . $this->appendTrashNodeForContainerQueryFilter($filter);

        /**
         * Count query
         */
        $res_max_entries = $this->db->query($query_count);
        while ($max_entries_row = $res_max_entries->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            $max_entries = (int) $max_entries_row->max_entries;
        }

        $this->db->setLimit($limit, $offset);
        $res = $this->db->query($query);

        $items = [];
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            $item = new \ilTreeTrashItem();
            $item->setObjId((int) $row->obj_id);
            $item->setRefId((int) $row->ref_id);
            $item->setTitle((string) $row->title);
            $item->setDescription((string) $row->description);
            $item->setType((string) $row->type);
            $item->setDeleted((string) $row->deleted);
            $item->setDeletedBy((int) $row->deleted_by);
            $items[] = $item;
        }
        return $items;
    }

    /**
     * Unfortunately not supported by mysql 5
     * @param int $ref_id
     * @return void
     * @throws ilDatabaseException
     * @deprecated
     */
    public function getTrashedNodesForContainerUsingRecursion(int $ref_id): void
    {
        $query = 'WITH RECURSIVE trash (child,tree) AS ' .
            '( SELECT child, tree FROM tree WHERE child = ' . $this->db->quote(
                $ref_id,
                \ilDBConstants::T_INTEGER
            ) . ' ' .
            'UNION SELECT tc.child,tc.tree FROM tree tc JOIN tree tp ON tp.child = tc.parent ) ' .
            'SELECT * FROM trash WHERE tree < 1 ';

        $trash_ids = [];
        try {
            $res = $this->db->query($query);
            while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
                $trash_ids[] = (int) $row->child;
            }
        } catch (\ilDatabaseException $e) {
            $this->logger->warning($query . ' is not supported');
            throw $e;
        }
    }

    /**
     * @param array{type?: string, title?: string, deleted?: array{from?: \ilDate, to?: \ilDate}} $filter
     * @return string
     */
    protected function appendTrashNodeForContainerQueryFilter(array $filter): string
    {
        $query = '';
        if (isset($filter['type'])) {
            $query .= 'and ' . $this->db->like(
                'type',
                \ilDBConstants::T_TEXT,
                $filter['type'] . '%'
            ) . ' ';
        }
        if (isset($filter['title'])) {
            $query .= 'and ' . $this->db->like(
                'title',
                \ilDBConstants::T_TEXT,
                '%' . $filter['title'] . '%'
            ) . ' ';
        }
        if (
            $filter['deleted']['from'] instanceof \ilDate &&
            $filter['deleted']['to'] instanceof \ilDate) {
            $query .= ('AND deleted BETWEEN ' .
                $this->db->quote($filter['deleted']['from']->get(\IL_CAL_DATE), \ilDBConstants::T_TEXT) . ' AND ' .
                $this->db->quote($filter['deleted']['to']->get(IL_CAL_DATE), \ilDBConstants::T_TEXT) . ' ');
        } elseif ($filter['deleted']['from'] instanceof \ilDate) {
            $query .= 'AND deleted >= ' . $this->db->quote(
                $filter['deleted']['from']->get(IL_CAL_DATE),
                \ilDBConstants::T_TEXT
            ) . ' ';
        } elseif ($filter['deleted']['to'] instanceof \ilDate) {
            $query .= 'AND deleted <= ' . $this->db->quote(
                $filter['deleted']['to']->get(IL_CAL_DATE),
                \ilDBConstants::T_TEXT
            ) . ' ';
        }

        if (isset($filter['deleted_by'])) {
            $usr_id = \ilObjUser::_lookupId($filter['deleted_by']);
            if ($usr_id > 0) {
                $query .= 'AND deleted_by = ' . $this->db->quote($usr_id, \ilDBConstants::T_INTEGER) . ' ';
            }
        }

        return $query;
    }
}
