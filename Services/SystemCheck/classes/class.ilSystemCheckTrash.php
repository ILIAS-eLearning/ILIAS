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
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSystemCheckTrash
{
    public const MODE_TRASH_RESTORE = 1;
    public const MODE_TRASH_REMOVE = 2;

    private int $limit_number = 0;
    private ilDateTime $limit_age;
    private array $limit_types = array();
    private int $mode;

    protected ilLogger $logger;
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilRbacAdmin $admin;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->sysc();
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->admin = $DIC->rbac()->admin();

        $this->limit_age = new ilDate(0, IL_CAL_UNIX);
    }

    public function setNumberLimit(int $a_limit): void
    {
        $this->limit_number = $a_limit;
    }

    public function getNumberLimit(): int
    {
        return $this->limit_number;
    }

    public function setAgeLimit(ilDateTime $dt): void
    {
        $this->limit_age = $dt;
    }

    public function getAgeLimit(): ilDateTime
    {
        return $this->limit_age;
    }

    public function setTypesLimit(array $a_types): void
    {
        $this->limit_types = $a_types;
    }

    public function getTypesLimit(): array
    {
        return $this->limit_types;
    }

    public function setMode(int $a_mode): void
    {
        $this->mode = $a_mode;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function start(): bool
    {
        $this->logger->info('Handling delete');
        switch ($this->getMode()) {
            case self::MODE_TRASH_RESTORE:
                $this->logger->info('Restore trash to recovery folder');
                $this->restore();
                return true;
                break;

            case self::MODE_TRASH_REMOVE:
                $this->logger->info('Remove selected from system.');
                $this->logger->info('Type limit: ' . print_r($this->getTypesLimit(), true));
                $this->logger->info('Age limit: ' . $this->getAgeLimit());
                $this->logger->info('Number limit: ' . $this->getNumberLimit());
                $this->removeSelectedFromSystem();
                return true;
                break;
        }
        return false;
    }

    protected function restore(): void
    {
        $deleted = $this->readDeleted();

        $this->logger->info('Found deleted : ' . print_r($deleted, true));


        foreach ($deleted as $tmp_num => $deleted_info) {
            $child_id = (int) ($deleted_info['child'] ?? 0);
            $ref_obj = ilObjectFactory::getInstanceByRefId($child_id, false);
            if (!$ref_obj instanceof ilObject) {
                continue;
            }

            $this->tree->deleteNode((int) ($deleted_info['tree'] ?? 0), $child_id);
            $this->logger->info('Object tree entry deleted');

            if ($ref_obj->getType() !== 'rolf') {
                $this->admin->revokePermission($child_id);
                $ref_obj->putInTree(RECOVERY_FOLDER_ID);
                $ref_obj->setPermissions(RECOVERY_FOLDER_ID);
                $this->logger->info('Object moved to recovery folder');
            }
        }
    }

    protected function removeSelectedFromSystem(): void
    {
        $deleted = $this->readSelectedDeleted();
        foreach ($deleted as $del_num => $deleted_info) {
            $sub_nodes = $this->readDeleted((int) ($deleted_info['tree'] ?? 0));

            foreach ($sub_nodes as $sub_num => $subnode_info) {
                $tree_id = (int) ($subnode_info['tree'] ?? 0);
                $node_id = (int) ($subnode_info['child'] ?? 0);
                try {
                    $ref_obj = ilObjectFactory::getInstanceByRefId($node_id, false);
                } catch (Throwable $t) {
                    // cannot instantiate object, must skip this
                    $this->removePartial($tree_id, $node_id);
                    continue;
                }
                if (!$ref_obj instanceof ilObject) {
                    continue;
                }

                try {
                    $ref_obj->delete();
                } catch (Throwable $t) {
                    // cannot instantiate object, must skip this
                    $this->removePartial($tree_id, $node_id);
                    continue;
                }

                ilTree::_removeEntry($tree_id, $node_id);
            }
        }
    }

    private function removePartial(int $tree_id, int $node_id): void
    {
        $object_id = ilObject::_lookupObjectId($node_id);
        $sql = "DELETE FROM " . ilObject::TABLE_OBJECT_DATA . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($object_id, "integer") . PHP_EOL;
        $this->db->manipulate($sql);

        $sql = "DELETE FROM object_description" . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($object_id, "integer") . PHP_EOL;
        $this->db->manipulate($sql);

        ilTree::_removeEntry($tree_id, $node_id);
    }

    protected function readSelectedDeleted(): array
    {
        $and_types = '';
        $this->logger->dump($this->getTypesLimit());

        $types = array();
        foreach ($this->getTypesLimit() as $id => $type) {
            if ($type) {
                $types[] = $type;
            }
        }
        if (count($types)) {
            $and_types = 'AND ' . $this->db->in('o.type', $this->getTypesLimit(), false, ilDBConstants::T_TEXT) . ' ';
        }

        $and_age = '';
        $age_limit = $this->getAgeLimit()->get(IL_CAL_UNIX);
        if ($age_limit > 0) {
            $and_age = 'AND r.deleted < ' . $this->db->quote(
                $this->getAgeLimit()->get(IL_CAL_DATETIME),
                ilDBConstants::T_TEXT
            ) . ' ';
        }
        $limit = '';
        if ($this->getNumberLimit()) {
            $limit = 'LIMIT ' . $this->getNumberLimit();
        }

        $query = 'SELECT child,tree FROM tree t JOIN object_reference r ON child = r.ref_id ' .
            'JOIN object_data o on r.obj_id = o.obj_id ' .
            'WHERE tree < ' . $this->db->quote(0, ilDBConstants::T_INTEGER) . ' ' .
            'AND child = -tree ';

        $query .= $and_age;
        $query .= $and_types;
        $query .= 'ORDER BY depth desc ';
        $query .= $limit;

        $this->logger->info($query);

        $deleted = array();
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $deleted[] = array(
                'tree' => $row->tree,
                'child' => $row->child
            );
        }
        return $deleted;
    }

    protected function readDeleted(?int $tree_id = null): array
    {
        $query = 'SELECT child,tree FROM tree t JOIN object_reference r ON child = r.ref_id ' .
            'JOIN object_data o on r.obj_id = o.obj_id ';

        if ($tree_id === null) {
            $query .= 'WHERE tree < ' . $this->db->quote(0, 'integer') . ' ';
        } else {
            $query .= 'WHERE tree = ' . $this->db->quote($tree_id, 'integer') . ' ';
        }
        $query .= 'ORDER BY depth desc';

        $res = $this->db->query($query);

        $deleted = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $deleted[] = array(
                'tree' => $row->tree,
                'child' => $row->child
            );
        }
        return $deleted;
    }
}
