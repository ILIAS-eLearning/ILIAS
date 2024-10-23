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

/**
 * Repository Utilities (application layer, put GUI related stuff into ilRepositoryTrashGUI)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRepUtil
{
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilSetting $settings;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();
    }

    /**
     * Delete objects. Move them to trash (if trash feature is enabled).
     * @param int   $a_cur_ref_id
     * @param int[] $a_ids ref ids
     * @throws ilRepositoryException
     * @deprecated will be removed with ilias 11
     */
    public static function deleteObjects(
        int $a_cur_ref_id,
        array $a_ids
    ): void {
        global $DIC;

        $DIC->repository()->internal()->domain()->deletion()->deleteObjectsByRefIds($a_ids);
    }

    /**
     * remove objects from trash bin and all entries therefore every object needs a specific deleteObject() method
     * @param int[] $a_ref_ids
     * @param bool  $a_from_recovery_folder
     * @throws ilDatabaseException
     * @throws ilInvalidTreeStructureException
     * @throws ilObjectNotFoundException
     * @throws ilRepositoryException
     */
    public static function removeObjectsFromSystem(
        array $a_ref_ids,
        bool $a_from_recovery_folder = false
    ): void {
        global $DIC;

        $ilLog = $DIC["ilLog"];
        $ilAppEventHandler = $DIC["ilAppEventHandler"];
        $tree = $DIC->repositoryTree();
        $logger = $DIC->logger()->rep();

        $log = $ilLog;

        $affected_ids = [];

        // DELETE THEM
        $a_ref_ids = array_map('intval', $a_ref_ids);
        foreach ($a_ref_ids as $id) {
            $saved_tree = null;
            // GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
            if (!$a_from_recovery_folder) {
                $trees = ilTree::lookupTreesForNode($id);
                $tree_id = end($trees);

                if ($tree_id) {
                    $saved_tree = new ilTree($tree_id);
                    $node_data = $saved_tree->getNodeData($id);
                    $subtree_nodes = $saved_tree->getSubTree($node_data);
                } else {
                    throw new ilRepositoryException('No valid tree id found for node id: ' . $id);
                }
            } else {
                $node_data = $tree->getNodeData($id);
                $subtree_nodes = $tree->getSubTree($node_data);
            }

            global $DIC;

            $ilUser = $DIC->user();
            $tree = $DIC->repositoryTree();

            ilChangeEvent::_recordWriteEvent(
                $node_data['obj_id'],
                $ilUser->getId(),
                'purge',
                null
            );
            // END ChangeEvent: Record remove from system.

            // remember already checked deleted node_ids
            if (!$a_from_recovery_folder) {
                $checked[] = -$id;
            } else {
                $checked[] = $id;
            }

            // dive in recursive manner in each already deleted subtrees and remove these objects too
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
                if (!$a_from_recovery_folder || $node_obj->getType() !== "fold") {
                    $node_obj->delete();
                }
            }

            // Use the saved tree object here (negative tree_id)
            if (!$a_from_recovery_folder) {
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
    private static function removeDeletedNodes(
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

    /**
     * Move objects from trash back to repository
     * @param int   $a_cur_ref_id
     * @param int[] $a_ref_ids
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     * @throws ilRepositoryException
     */
    public static function restoreObjects(
        int $a_cur_ref_id,
        array $a_ref_ids
    ): void {
        global $DIC;

        $rbacsystem = $DIC->rbac()->system();
        $ilAppEventHandler = $DIC["ilAppEventHandler"];
        $lng = $DIC->language();
        $tree = $DIC->repositoryTree();

        $cur_obj_id = ilObject::_lookupObjId($a_cur_ref_id);

        $no_create = [];

        foreach ($a_ref_ids as $id) {
            $obj_data = ilObjectFactory::getInstanceByRefId($id);

            if (!$rbacsystem->checkAccess('create', $a_cur_ref_id, $obj_data->getType())) {
                $no_create[] = ilObject::_lookupTitle(ilObject::_lookupObjId($id));
            }
        }

        if (count($no_create)) {
            throw new ilRepositoryException($lng->txt("msg_no_perm_paste") . " " . implode(',', $no_create));
        }

        $affected_ids = [];

        foreach ($a_ref_ids as $id) {
            $affected_ids[$id] = $id;

            // INSERT AND SET PERMISSIONS
            try {
                $tree_ids = ilTree::lookupTreesForNode($id);
                $tree_id = $tree_ids[0];
                self::insertSavedNodes($id, $a_cur_ref_id, $tree_id, $affected_ids);
            } catch (Exception $e) {
                throw new ilRepositoryException('Restore from trash failed with message: ' . $e->getMessage());
            }


            // BEGIN ChangeEvent: Record undelete.
            global $DIC;

            $ilUser = $DIC->user();


            ilChangeEvent::_recordWriteEvent(
                ilObject::_lookupObjId($id),
                $ilUser->getId(),
                'undelete',
                ilObject::_lookupObjId($tree->getParentId($id))
            );
            ilChangeEvent::_catchupWriteEvents(
                $cur_obj_id,
                $ilUser->getId()
            );
            // END PATCH ChangeEvent: Record undelete.
        }

        // send events
        foreach ($affected_ids as $id) {
            // send global event
            $ilAppEventHandler->raise(
                "components/ILIAS/Object",
                "undelete",
                ["obj_id" => ilObject::_lookupObjId($id), "ref_id" => $id]
            );
        }
    }

    /**
     * Recursive method to insert all saved nodes of the clipboard
     */
    private static function insertSavedNodes(
        int $a_source_id,
        int $a_dest_id,
        int $a_tree_id,
        array &$a_affected_ids
    ): void {
        global $DIC;

        $tree = $DIC->repositoryTree();

        ilLoggerFactory::getLogger('rep')->debug('Restoring from trash: source_id: ' . $a_source_id . ', dest_id: ' . $a_dest_id . ', tree_id:' . $a_tree_id);
        ilLoggerFactory::getLogger('rep')->info('Restoring ref_id  ' . $a_source_id . ' from trash.');

        // read child of node
        $saved_tree = new ilTree($a_tree_id);
        $childs = $saved_tree->getChilds($a_source_id);

        // then delete node and put in tree
        try {
            $tree->insertNodeFromTrash($a_source_id, $a_dest_id, $a_tree_id, ilTree::POS_LAST_NODE, true);
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('rep')->error('Restore from trash failed with message: ' . $e->getMessage());
            throw $e;
        }

        $ref_obj = ilObjectFactory::getInstanceByRefId($a_source_id, false);
        if ($ref_obj instanceof ilObject) {
            $lroles = $GLOBALS['rbacreview']->getRolesOfRoleFolder($a_source_id, true);
            foreach ($lroles as $role_id) {
                $role = new ilObjRole($role_id);
                $role->setParent($a_source_id);
                $role->delete();
            }
            if ($a_dest_id) {
                $ref_obj->setPermissions($a_dest_id);
            }
        }
        foreach ($childs as $child) {
            self::insertSavedNodes($child["child"], $a_source_id, $a_tree_id, $a_affected_ids);
        }
    }



    //
    // OBJECT TYPE HANDLING / REMOVAL
    //

    protected function findTypeInTrash(
        string $a_type
    ): array {
        $ilDB = $this->db;

        $res = [];

        $set = $ilDB->query("SELECT child" .
            " FROM tree" .
            " JOIN object_reference ref ON (tree.child = ref.ref_id)" .
            " JOIN object_data od ON (od.obj_id = ref.obj_id)" .
            " WHERE tree.tree < " . $ilDB->quote(0, "integer") .
            " AND od.type = " . $ilDB->quote($a_type, "text"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row["child"];
        }

        return $res;
    }

    protected function getObjectTypeId(
        string $a_type
    ): int {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT obj_id" .
            " FROM object_data " .
            " WHERE type = " . $ilDB->quote("typ", "text") .
            " AND title = " . $ilDB->quote($a_type, "text"));
        $row = $ilDB->fetchAssoc($set);
        return (int) $row["obj_id"];
    }

    public function deleteObjectType(
        string $a_type
    ): void {
        $ilDB = $this->db;
        $tree = $this->tree;
        $ilSetting = $this->settings;

        // delete object instances (repository/trash)

        $ref_ids_in_tree = $tree->getSubTree($tree->getNodeData(ROOT_FOLDER_ID), false, [$a_type]);
        if ($ref_ids_in_tree) {
            self::deleteObjects(0, $ref_ids_in_tree);
        }

        if ($ilSetting->get('enable_trash')) {
            $ref_ids_in_trash = $this->findTypeInTrash($a_type);
            if ($ref_ids_in_trash) {
                self::removeObjectsFromSystem($ref_ids_in_trash);
            }
        }

        // delete "component"
        $type_id = $this->getObjectTypeId($a_type);
        if ($type_id) {
            // see ilRepositoryObjectPlugin::beforeActivation()

            $ilDB->manipulate("DELETE FROM object_data" .
                " WHERE obj_id = " . $ilDB->quote($type_id, "integer"));

            // RBAC

            // basic operations
            $ilDB->manipulate("DELETE FROM rbac_ta" .
                " WHERE typ_id = " . $ilDB->quote($type_id, "integer") /*.
                " AND ".$ilDB->in("ops_id", array(1, 2, 3, 4, 6), "", "integer") */);

            // creation operation
            $set = $ilDB->query("SELECT ops_id" .
                " FROM rbac_operations " .
                " WHERE class = " . $ilDB->quote("create", "text") .
                " AND operation = " . $ilDB->quote("create_" . $a_type, "text"));
            $row = $ilDB->fetchAssoc($set);
            $create_ops_id = $row["ops_id"];
            if ($create_ops_id) {
                $ilDB->manipulate("DELETE FROM rbac_operations" .
                    " WHERE ops_id = " . $ilDB->quote($create_ops_id, "integer"));

                $ilDB->manipulate("DELETE FROM rbac_templates" .
                    " WHERE ops_id = " . $ilDB->quote($create_ops_id, "integer"));

                // container create
                foreach (["root", "cat", "crs", "grp", "fold"] as $parent_type) {
                    $parent_type_id = $this->getObjectTypeId($parent_type);
                    if ($parent_type_id) {
                        $ilDB->manipulate("DELETE FROM rbac_ta" .
                            " WHERE typ_id = " . $ilDB->quote($parent_type_id, "integer") .
                            " AND ops_id = " . $ilDB->quote($create_ops_id, "integer"));
                    }
                }
            }
        }

        // delete new item settings
        ilObjRepositorySettings::deleteObjectType($a_type);
    }
}
