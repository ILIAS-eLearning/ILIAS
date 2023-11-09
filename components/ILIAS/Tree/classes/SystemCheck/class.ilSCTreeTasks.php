<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Defines a system check task
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTreeTasks
{
    protected ilTree $tree;
    protected ilDBInterface $db;
    private ilSCTask $task;

    public function __construct(ilSCTask $task)
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->task = $task;
    }

    public static function findDeepestDuplicate(): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT child FROM tree first  ' .
            'WHERE EXISTS ( ' .
            'SELECT child FROM tree second WHERE first.child = second.child ' .
            'GROUP BY child HAVING COUNT(child)  >  1 ) ' .
            'ORDER BY depth DESC';

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->child;
        }
        return 0;
    }

    public static function repairPK(): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->addPrimaryKey('tree', array('child'));
    }

    public static function getNodeInfo(int $a_tree_id, int $a_child): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT * FROM tree WHERE child = ' . $ilDB->quote(
            $a_child,
            ilDBConstants::T_INTEGER
        ) . ' AND tree = ' . $ilDB->quote($a_tree_id, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);

        $node = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $node['child'] = (int) $row->child;
            $node['tree'] = (int) $row->tree;
            $node['depth'] = (int) $row->depth;

            // read obj_id
            $query = 'SELECT obj_id FROM object_reference WHERE ref_id = ' . $ilDB->quote(
                $a_child,
                ilDBConstants::T_INTEGER
            );
            $ref_res = $ilDB->query($query);
            while ($ref_row = $ref_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $node['obj_id'] = (int) $ref_row->obj_id;

                // read object info
                $query = 'SELECT title, description, type FROM object_data ' .
                    'WHERE obj_id = ' . $ilDB->quote($ref_row->obj_id, ilDBConstants::T_INTEGER);
                $obj_res = $ilDB->query($query);
                while ($obj_row = $obj_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                    $node['title'] = (string) $obj_row->title;
                    $node['description'] = (string) $obj_row->description;
                    $node['type'] = (string) $obj_row->type;
                }
            }
        }
        return $node;
    }

    public static function getChilds(int $a_tree_id, int $a_childs): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT * FROM tree WHERE tree = ' . $ilDB->quote(
            $a_tree_id,
            ilDBConstants::T_INTEGER
        ) . ' ' . 'AND child = ' . $ilDB->quote($a_childs, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);

        $childs = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $childs[] = (int) $row->child;
        }
        return $childs;
    }

    public static function findDuplicates(int $a_duplicate_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT * FROM tree first  ' .
            'WHERE EXISTS ( ' .
            'SELECT child FROM tree second WHERE first.child = second.child ' .
            'GROUP BY child HAVING COUNT(child)  >  1 ) ' .
            'AND child = ' . $ilDB->quote($a_duplicate_id, ilDBConstants::T_INTEGER) . ' ' .
            'ORDER BY depth DESC';
        $res = $ilDB->query($query);

        $nodes = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $node = array();
            $node['tree'] = (int) $row->tree;
            $node['child'] = (int) $row->child;
            $node['depth'] = (int) $row->depth;

            $nodes[] = $node;
        }

        return $nodes;
    }

    public static function hasDuplicate(int $a_child): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        return count(self::findDuplicates($a_child));
    }

    public static function deleteDuplicateFromTree(int $a_duplicate_id, bool $a_delete_trash): bool
    {
        $dups = self::findDuplicates($a_duplicate_id);
        foreach ($dups as $dup) {
            if ($a_delete_trash && $dup['tree'] < 1) {
                self::deleteDuplicate($dup['tree'], $dup['child']);
            }
            if (!$a_delete_trash && $dup['tree'] == 1) {
                self::deleteDuplicate($dup['tree'], $dup['child']);
            }
        }
        return true;
    }

    protected static function deleteDuplicate(int $tree_id, int $dup_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT child FROM tree ' .
            'WHERE parent = ' . $ilDB->quote($dup_id, ilDBConstants::T_INTEGER) . ' ' .
            'AND tree = ' . $ilDB->quote($tree_id, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // start recursion
            self::deleteDuplicate($tree_id, (int) $row->child);
        }
        // now delete node
        if (self::hasDuplicate($dup_id)) {
            $query = 'DELETE FROM tree ' .
                'WHERE child = ' . $ilDB->quote($dup_id, ilDBConstants::T_INTEGER) . ' ' .
                'AND tree = ' . $ilDB->quote($tree_id, ilDBConstants::T_INTEGER);
            $ilDB->manipulate($query);
        }
    }

    protected function getDB(): ilDBInterface
    {
        return $this->db;
    }

    public function getTask(): ilSCTask
    {
        return $this->task;
    }

    public function validateStructure(): int
    {
        $failures = $this->checkStructure();

        if (count($failures)) {
            $this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
        } else {
            $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        }
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        return count($failures);
    }

    public function checkStructure(): array
    {
        return $this->tree->validateParentRelations();
    }

    public function validateDuplicates(): int
    {
        $failures = $this->checkDuplicates();

        if (count($failures)) {
            $this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
        } else {
            $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        }
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        return count($failures);
    }

    public function checkDuplicates(): array
    {
        $query = 'SELECT child, count(child) num FROM tree ' .
            'GROUP BY child ' .
            'HAVING count(child) > 1';
        $res = $this->db->query($query);

        $failures = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $failures[] = (int) $row->child;
        }
        return $failures;
    }

    public function findMissingTreeEntries(): int
    {
        $failures = $this->readMissingTreeEntries();

        if (count($failures)) {
            $this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
        } else {
            $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        }

        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        return count($failures);
    }

    public function findMissing(): int
    {
        $failures = $this->readMissing();

        if (count($failures)) {
            $this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
        } else {
            $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        }

        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        return count($failures);
    }

    public function repairMissing(): void
    {
        $failures = $this->readMissing();
        $recf_ref_id = $this->createRecoveryContainer();
        foreach ($failures as $ref_id) {
            $this->repairMissingObject($recf_ref_id, $ref_id);
        }
    }

    protected function repairMissingObject(int $a_parent_ref, int $a_ref_id): void
    {
        // check if object entry exist
        $query = 'SELECT obj_id FROM object_reference ' .
            'WHERE ref_id = ' . $this->db->quote($a_ref_id, ilDBConstants::T_INTEGER);

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $query = 'SELECT type, title FROM object_data ' .
                'WHERE obj_id = ' . $this->db->quote($row->obj_id, ilDBConstants::T_INTEGER);
            $ores = $this->db->query($query);

            $done = false;
            while ($orow = $ores->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $done = true;

                $factory = new ilObjectFactory();
                $ref_obj = $factory->getInstanceByRefId($a_ref_id, false);

                if ($ref_obj instanceof ilObjRoleFolder) {
                    $ref_obj->delete();
                } elseif ($ref_obj instanceof ilObject) {
                    $ref_obj->putInTree($a_parent_ref);
                    $ref_obj->setPermissions($a_parent_ref);

                    break;
                }
            }
            if (!$done) {
                // delete reference value
                $query = 'DELETE FROM object_reference WHERE ref_id = ' . $this->db->quote(
                    $a_ref_id,
                    ilDBConstants::T_INTEGER
                );
                $this->db->manipulate($query);
            }
        }
    }

    /**
     * @return int[]
     */
    protected function readMissing(): array
    {
        $query = 'SELECT ref_id FROM object_reference ' .
            'LEFT JOIN tree ON ref_id = child ' .
            'WHERE child IS NULL';
        $res = $this->db->query($query);

        $failures = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $failures[] = (int) $row->ref_id;
        }
        return $failures;
    }

    public function repairMissingTreeEntries(): void
    {
        $missing = $this->readMissingTreeEntries();
        foreach ($missing as $ref_id) {
            // check for duplicates
            $query = 'SELECT tree, child FROM tree ' .
                'WHERE child = ' . $this->db->quote($ref_id, ilDBConstants::T_INTEGER);
            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->deleteMissingTreeEntry((int) $row->tree, $ref_id);
            }
        }
    }

    protected function deleteMissingTreeEntry(int $a_tree_id, int $a_ref_id): void
    {
        $query = 'SELECT child FROM tree ' .
            'WHERE parent = ' . $this->db->quote($a_ref_id, ilDBConstants::T_INTEGER) . ' ' .
            'AND tree = ' . $this->db->quote($a_tree_id, ilDBConstants::T_INTEGER);

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // check for duplicates
            $query = 'SELECT tree, child FROM tree ' .
                'WHERE child = ' . $this->db->quote($row->child, ilDBConstants::T_INTEGER);
            $resd = $this->db->query($query);
            while ($rowd = $resd->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->deleteMissingTreeEntry((int) $rowd->tree, (int) $rowd->child);
            }
        }

        // finally delete

        $factory = new ilObjectFactory();
        $ref_obj = $factory->getInstanceByRefId($a_ref_id, false);

        if (($ref_obj instanceof ilObject) && $ref_obj->getType()) {
            $ref_obj->delete();
        }

        $query = 'DELETE from tree ' .
            'WHERE tree = ' . $this->db->quote($a_tree_id, ilDBConstants::T_INTEGER) . ' ' .
            'AND child = ' . $this->db->quote($a_ref_id, ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }

    /**
     * Read missing tree entries for referenced objects
     * Entry in tree but no entry in object reference
     */
    protected function readMissingTreeEntries(): array
    {
        $query = 'SELECT child FROM tree ' .
            'LEFT JOIN object_reference ON child = ref_id ' .
            'WHERE ref_id IS NULL';

        $res = $this->db->query($query);

        $failures = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $failures[] = (int) $row->child;
        }
        return $failures;
    }

    protected function createRecoveryContainer(): int
    {
        $now = new ilDateTime(time(), IL_CAL_UNIX);

        $folder = new ilObjFolder();
        $folder->setTitle('__System check recovery: ' . $now->get(IL_CAL_DATETIME));
        $folder->create();
        $folder->createReference();
        $folder->putInTree(RECOVERY_FOLDER_ID);

        return $folder->getRefId();
    }
}
