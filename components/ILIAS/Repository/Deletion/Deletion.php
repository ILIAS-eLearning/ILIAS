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

class Deletion
{
    public function __construct(
        protected TreeInterface $tree,
        protected PermissionInterface $permission,
        protected EventInterface $event,
        protected ObjectInterface $object,
        protected bool $trash_enabled
    ) {

    }

    /**
     * Delete: If trash is enabled, objects are moved to the trash. If trash is disabled,
     * objects are removed from system directly.
     * @return int[]
     */
    public function deleteObjectsByRefIds(array $ids): void
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
            $this->removeObjectsFromSystemByRefIds($ids, true);
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

            $this->event->beforeSubTreeRemoval(
                $node_data['obj_id']
            );

            // remember already checked deleted node_ids
            if (!$direct_from_tree) {
                $checked[] = -$id;
            } else {
                $checked[] = $id;
            }

            // dive in recursive manner in each already deleted subtrees and remove these objects too

            foreach ($subtree_nodes as $node) {
                if (!$node_obj = $this->object->getInstanceByRefId($node["ref_id"])) {
                    continue;
                }

                // NOTE: This has been outside of the for loop before
                $this->removeDeletedNodes($node["ref_id"], $checked, true, $affected_ids);

                $this->event->beforeObjectRemoval(
                    $node_obj->getId(),
                    $node_obj->getRefId(),
                    $node_obj->getType(),
                    $node_obj->getTitle()
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
                    try {
                        $node_obj->delete();
                    } catch (\Exception $e) {
                        $this->event->failedRemoval(
                            $node_obj->getId(),
                            $node_obj->getRefId(),
                            $node_obj->getType(),
                            $node_obj->getTitle(),
                            $e->getMessage()
                        );
                    }

                }
            }

            // Use the saved tree object here (negative tree_id)
            if (!$direct_from_tree) {
                if ($saved_tree) {
                    $saved_tree->deleteTree($node_data);
                }
            } else {
                $this->tree->deleteTree($node_data);
            }

            $this->event->afterTreeDeletion(
                (int) $node_data['tree'],
                (int) $node_data['child']
            );
        }

        // send global events
        foreach ($affected_ids as $aid) {
            $this->event->afterObjectRemoval(
                $aid["obj_id"],
                $aid["ref_id"],
                $aid["type"],
                $aid["old_parent_ref_id"]
            );
        }
    }

    /**
     * Remove already deleted objects within the objects in trash
     */
    protected function removeDeletedNodes(
        int $a_node_id,
        array $a_checked,
        bool $a_delete_objects,     // seems to be always true
        array &$a_affected_ids
    ): void {

        foreach ($this->tree->getTrashedSubtrees($a_node_id) as $tree_id) {
            // $tree_id is negative here
            // only continue recursion if fetched node wasn't touched already!
            if (!in_array($tree_id, $a_checked)) {
                $deleted_tree = $this->tree->getTree($tree_id);
                $a_checked[] = $tree_id;

                $tree_id *= (-1);
                // $tree_id is positive here
                $del_node_data = $deleted_tree->getNodeData($tree_id);
                $del_subtree_nodes = $deleted_tree->getSubTree($del_node_data);
                // delete trash in the trash of trash...
                $this->removeDeletedNodes($tree_id, $a_checked, $a_delete_objects, $a_affected_ids);

                if ($a_delete_objects) {
                    foreach ($del_subtree_nodes as $node) {
                        $object = $this->object->getInstanceByRefId($node["ref_id"]);
                        if (!is_null($object)) {
                            $a_affected_ids[$node["ref_id"]] = [
                                "ref_id" => $node["ref_id"],
                                "obj_id" => $object->getId(),
                                "type" => $object->getType(),
                                "old_parent_ref_id" => $node["parent"]
                            ];
                            $this->event->beforeObjectRemoval(
                                $object->getId(),
                                $object->getRefId(),
                                $object->getType(),
                                $object->getTitle()
                            );
                            try {
                                $object->delete();
                            } catch (\Exception $e) {
                                $this->event->failedRemoval(
                                    $object->getId(),
                                    $object->getRefId(),
                                    $object->getType(),
                                    $object->getTitle(),
                                    $e->getMessage()
                                );
                            }
                        }
                    }
                }
                // tree instance with -child tree id
                $trash_tree = $this->tree->getTree((int) $del_node_data['tree']);
                $trash_tree->deleteTree($del_node_data);
                $this->event->afterTreeDeletion(
                    (int) $del_node_data['tree'],
                    (int) $del_node_data['child']
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
