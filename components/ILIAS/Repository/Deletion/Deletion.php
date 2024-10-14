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

namespace ILIAS\Repository\Deletion;

use ilRepositoryException;

class Deletion
{
    public function __construct(
        protected TreeInterface $tree,
        protected PermissionInterface $permission,
        protected EventInterface $event,
        protected bool $trash_enabled
    ) {

    }

    /**
     * Delete: If trash is enabled, objects are moved to the trash. If trash is disabled,
     * objects are removed from system directly.
     * @return int[]
     */
    public function deleteObjectsByRefIds(array $ids): array
    {
        // check if objects are already deleted
        if (count($del_ids = $this->tree->getDeletedTreeNodeIds($ids)) > 0) {
            throw new AlreadyDeletedException('Deletion: Some tree nodes are already deleted: ' . implode(', ', $del_ids));
        }

        // check delelete permissions
        if (count($miss_ids = $this->permission->getRefIdsWithoutDeletePermission($ids)) > 0) {
            throw new MissingPermissionException('Deletion: missing permission: ' . implode(', ', $miss_ids));
        }

        if ($this->trash_enabled) {
            $this->moveToTrash($ids);
        } else {
            foreach ($ids as $id) {
                $handled_ids = [];
                //                $this->finalDeleteNode($id, $handled_ids);
            }
        }
    }

    /**
     * Remove objects from system directly ($direct_from_tree === true)
     * or from trash ($direct_from_tree === false)
     */
    public function removeObjectsFromSystemByRefIds(
        array $ref_ids,
        bool $direct_from_tree = false
    ): void {

        $affected_ids = [];
        $ref_ids = array_map('intval', $ref_ids);
        foreach ($ref_ids as $id) {
            $saved_tree = null;

            // get subtree nodes
            if (!$direct_from_tree) {
                // from trash
                $saved_tree = $this->tree->getTrashTree($id);
                $node_data = $saved_tree->getNodeData($id);
                // subtree nodes from trashed tree will include the $id node itself, too
                $subtree_nodes = $saved_tree->getSubTree($node_data);
            } else {
                // from main tree
                $node_data = $this->tree->getNodeData($id);
                // subtree nodes will include the $id node itself, too
                $subtree_nodes = $this->tree->getSubTree($node_data);
            }

            ilChangeEvent::_recordWriteEvent(
                $node_data['obj_id'],
                $ilUser->getId(),
                'purge',
                null
            );
            // END ChangeEvent: Record remove from system.

            // remember already checked deleted node_ids
            if (!$direct_from_tree) {
                $checked[] = -$id;
            } else {
                $checked[] = $id;
            }

            // dive in recursive manner in each already deleted subtrees and remove these objects too
            // @todo Why only for the main id not the subnodes!?
            self::removeDeletedNodes($id, $checked, true, $affected_ids);

            foreach ($subtree_nodes as $node) {
                if (!$node_obj = ilObjectFactory::getInstanceByRefId($node["ref_id"], false)) {
                    continue;
                }

                $logger->info(
                    'delete obj_id: ' . $node_obj->getId() .
                    ', ref_id: ' . $node_obj->getRefId() .
                    ', type: ' . $node_obj->getType() .
                    ', title: ' . $node_obj->getTitle()
                );
                $affected_ids[$node["ref_id"]] = [
                    "ref_id" => $node["ref_id"],
                    "obj_id" => $node_obj->getId(),
                    "type" => $node_obj->getType(),
                    "old_parent_ref_id" => $node["parent"]
                ];

                // this is due to bug #1860 (even if this will not completely fix it)
                // and the fact, that media pool folders may find their way into
                // the recovery folder (what results in broken pools, if the are deleted)
                // Alex, 2006-07-21
                if (!$direct_from_tree || $node_obj->getType() !== "fold") {
                    $node_obj->delete();
                }
            }

            // Use the saved tree object here (negative tree_id)
            if (!$direct_from_tree) {
                if ($saved_tree) {
                    $saved_tree->deleteTree($node_data);
                }
            } else {
                $tree->deleteTree($node_data);
            }

            $logger->info(
                'deleted tree, tree_id: ' . $node_data['tree'] .
                ', child: ' . $node_data['child']
            );
        }

        // send global events
        foreach ($affected_ids as $aid) {
            $ilAppEventHandler->raise(
                "components/ILIAS/Object",
                "delete",
                [
                    "obj_id" => $aid["obj_id"],
                    "ref_id" => $aid["ref_id"],
                    "type" => $aid["type"],
                    "old_parent_ref_id" => $aid["old_parent_ref_id"]
                ]
            );
        }
    }

    /**
     * Remove already deleted objects within the objects in trash
     */
    protected function removeDeletedNodes(
        int $a_node_id,
        array $a_checked,
        bool $a_delete_objects,
        array &$a_affected_ids
    ): void {
        global $DIC;

        $ilLog = $DIC["ilLog"];
        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();
        $logger = $DIC->logger()->rep();

        $log = $ilLog;

        // this queries for trash items in the trash of deleted nodes
        $q = 'SELECT tree FROM tree WHERE parent = ' . $ilDB->quote($a_node_id, ilDBConstants::T_INTEGER) .
            ' AND tree < 0 ' .
            ' AND tree = -1 * child' ;

        $r = $ilDB->query($q);

        while ($row = $ilDB->fetchObject($r)) {
            // only continue recursion if fetched node wasn't touched already!
            if (!in_array($row->tree, $a_checked)) {
                $deleted_tree = new ilTree($row->tree);
                $a_checked[] = $row->tree;

                $row->tree *= (-1);
                $del_node_data = $deleted_tree->getNodeData($row->tree);
                $del_subtree_nodes = $deleted_tree->getSubTree($del_node_data);
                // delete trash in the trash of trash...
                self::removeDeletedNodes($row->tree, $a_checked, $a_delete_objects, $a_affected_ids);

                if ($a_delete_objects) {
                    foreach ($del_subtree_nodes as $node) {
                        $node_obj = ilObjectFactory::getInstanceByRefId($node["ref_id"]);
                        $logger->info(
                            'removeDeletedNodes: delete obj_id: ' . $node_obj->getId() .
                            ', ref_id: ' . $node_obj->getRefId() .
                            ', type: ' . $node_obj->getType() .
                            ', title: ' . $node_obj->getTitle()
                        );
                        $a_affected_ids[$node["ref_id"]] = [
                            "ref_id" => $node["ref_id"],
                            "obj_id" => $node_obj->getId(),
                            "type" => $node_obj->getType(),
                            "old_parent_ref_id" => $node["parent"]
                        ];
                        $node_obj->delete();
                    }
                }
                // tree instance with -child tree id
                $trash_tree = new ilTree($del_node_data['tree']);
                $trash_tree->deleteTree($del_node_data);
                $logger->info(
                    'removeDeltedNodes: deleted tree, tree_id: ' . $del_node_data['tree'] .
                    ', child: ' . $del_node_data['child']
                );
            }
        }
    }


    protected function moveToTrash(array $ids): void
    {
        // save subtree / delete subtree from tree
        $affected_ids = [];
        $affected_parents = [];
        foreach ($ids as $id) {
            if ($this->tree->isDeleted($id)) {
                throw new AlreadyDeletedException("Move To Trash: Object with ref_id: " . $id . " already deleted.");
            }

            $subnodes = $this->tree->getSubtree($this->tree->getNodeData($id));

            foreach ($subnodes as $subnode) {
                $this->permission->revokePermission((int) $subnode["child"]);

                $affected_ids[$subnode["child"]] = $subnode["child"];
                $affected_parents[$subnode["child"]] = $subnode["parent"];
            }

            $this->event->beforeMoveToTrash($id, $subnodes);

            if (!$this->tree->moveToTrash($id)) {
                throw new AlreadyDeletedException("Move To Trash: Object with ref_id: " . $id . " already deleted.");
            }
            $affected_ids[$id] = $id;
        }

        // send global events
        foreach ($affected_ids as $aid) {
            $this->event->afterMoveToTrash($aid, $affected_parents[$aid]);
        }
    }


}
