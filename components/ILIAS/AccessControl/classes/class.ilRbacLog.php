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

use ILIAS\Data\Range;
use ILIAS\Data\Order;

/**
 * class ilRbacLog
 *  Log changes in Rbac-related settings
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAccessControl
 */
class ilRbacLog
{
    private const LOG_TABLE_NAME = 'rbac_log';
    public const EDIT_PERMISSIONS = 1;
    public const MOVE_OBJECT = 2;
    public const LINK_OBJECT = 3;
    public const COPY_OBJECT = 4;
    public const CREATE_OBJECT = 5;
    public const EDIT_TEMPLATE = 6;
    public const EDIT_TEMPLATE_EXISTING = 7;
    public const CHANGE_OWNER = 8;

    public function __construct(
        private readonly ilDBInterface $db
    ) {
    }

    public function getLogItems(
        int $ref_id,
        Range $range,
        Order $order,
        ?array $filter
    ): array {
        $this->db->setLimit($range->getLength(), $range->getStart());
        $set = $this->db->query(
            'SELECT * FROM ' . self::LOG_TABLE_NAME
            . ' WHERE ref_id = ' . $this->db->quote($ref_id, ilDBConstants::T_INTEGER)
            . $this->getWhereForFilter($filter)
            . $order->join(
                ' ORDER BY ',
                static fn(string $ret, string $key, string $value): string =>
                    $ret === ' ORDER BY ' ? "{$ret} {$key} {$value}" : "{$ret}, {$key} {$value} "
            )
        );
        $result = [];
        while ($row = $this->db->fetchAssoc($set)) {
            $row['data'] = unserialize($row['data'], [false]);
            $result[] = $row;
        }
        return $result;
    }

    public function getLogItemsCount(
        int $ref_id,
        array $filter
    ): int {
        $result = $this->db->fetchObject(
            $this->db->query(
                'SELECT COUNT(*) as cnt FROM ' . self::LOG_TABLE_NAME . ' WHERE ref_id = '
                . $this->db->quote(
                    $ref_id,
                    ilDBConstants::T_INTEGER
                ) . $this->getWhereForFilter($filter)
            )
        );
        return $result->cnt;
    }

    private function getWhereForFilter(?array $filter): string
    {
        if ($filter === null) {
            return '';
        }

        $where = [];
        if (isset($filter['action'])) {
            $where[] = ' ' . $this->db->in(
                'action',
                $filter['action'],
                false,
                ilDBConstants::T_INTEGER
            );
        }
        if (isset($filter['from'])) {
            $where[] = ' created >= ' . $this->db->quote(
                $filter['from'],
                ilDBConstants::T_INTEGER
            );
        }
        if (isset($filter['to'])) {
            $where[] = ' created <= ' . $this->db->quote(
                $filter['to'],
                ilDBConstants::T_INTEGER
            );
        }

        if (count($where) > 0) {
            $where = array_merge([' AND '], [implode(' AND ', $where)]);
        }
        return implode('', $where);
    }

    public static function isActive(): bool
    {
        return ilPrivacySettings::getInstance()->enabledRbacLog();
    }

    public static function gatherFaPa(int $ref_id, array $role_ids, bool $add_action = false): array
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $result = [];

        // #10946 - if result is written to log directly we need to add an "action" dimension
        // if result is used as input to diffFaPa() we need "raw" data

        // roles
        foreach ($role_ids as $role_id) {
            if ($role_id != SYSTEM_ROLE_ID) {
                if ($add_action) {
                    $result["ops"][$role_id]["add"] = $rbacreview->getRoleOperationsOnObject($role_id, $ref_id);
                } else {
                    $result["ops"][$role_id] = $rbacreview->getRoleOperationsOnObject($role_id, $ref_id);
                }
            }
        }

        // inheritance
        if ($ref_id && $ref_id != ROLE_FOLDER_ID) {
            if ($add_action) {
                $result["inht"]["add"] = $rbacreview->getRolesOfRoleFolder($ref_id);
            } else {
                $result["inht"] = $rbacreview->getRolesOfRoleFolder($ref_id);
            }
        }

        return $result;
    }

    public static function diffFaPa(array $old, array $new): array
    {
        $result = [];

        // roles
        foreach ((array) $old["ops"] as $role_id => $ops) {
            $diff = array_diff($ops, $new["ops"][$role_id]);
            if ($diff !== []) {
                $result["ops"][$role_id]["rmv"] = array_values($diff);
            }
            $diff = array_diff($new["ops"][$role_id], $ops);
            if ($diff !== []) {
                $result["ops"][$role_id]["add"] = array_values($diff);
            }
        }

        if (isset($old["inht"]) || isset($new["inht"])) {
            if (isset($old["inht"]) && !isset($new["inht"])) {
                $result["inht"]["rmv"] = $old["inht"];
            } elseif (!isset($old["inht"]) && isset($new["inht"])) {
                $result["inht"]["add"] = $new["inht"];
            } else {
                $diff = array_diff($old["inht"], $new["inht"]);
                if ($diff !== []) {
                    $result["inht"]["rmv"] = array_values($diff);
                }
                $diff = array_diff($new["inht"], $old["inht"]);
                if ($diff !== []) {
                    $result["inht"]["add"] = array_values($diff);
                }
            }
        }
        return $result;
    }

    public static function gatherTemplate(int $role_ref_id, int $role_id): array
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        return $rbacreview->getAllOperationsOfRole($role_id, $role_ref_id);
    }

    public static function diffTemplate(array $old, array $new): array
    {
        $result = [];
        $types = array_unique(array_merge(array_keys($old), array_keys($new)));
        foreach ($types as $type) {
            if (!isset($old[$type])) {
                $result[$type]["add"] = $new[$type];
            } elseif (!isset($new[$type])) {
                $result[$type]["rmv"] = $old[$type];
            } else {
                $diff = array_diff($old[$type], $new[$type]);
                if ($diff !== []) {
                    $result[$type]["rmv"] = array_values($diff);
                }
                $diff = array_diff($new[$type], $old[$type]);
                if ($diff !== []) {
                    $result[$type]["add"] = array_values($diff);
                }
            }
        }
        return $result;
    }

    public static function add(int $action, int $ref_id, array $diff, bool $source_ref_id = false): bool
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilDB = $DIC->database();

        if (self::isValidAction($action) && count($diff)) {
            if ($source_ref_id) {
                $diff["src"] = $source_ref_id;
            }
            $id = $ilDB->nextId(self::LOG_TABLE_NAME);

            $ilDB->query('INSERT INTO ' . self::LOG_TABLE_NAME . ' (log_id, user_id, created, ref_id, action, data)' .
                " VALUES (" . $ilDB->quote($id, "integer") . "," . $ilDB->quote($ilUser->getId(), "integer") .
                "," . $ilDB->quote(time(), "integer") .
                "," . $ilDB->quote($ref_id, "integer") . "," . $ilDB->quote($action, "integer") .
                "," . $ilDB->quote(serialize($diff), "text") . ")");
            return true;
        }
        return false;
    }

    protected static function isValidAction(int $action): bool
    {
        if (in_array(
            $action,
            [
                self::EDIT_PERMISSIONS,
                self::MOVE_OBJECT,
                self::LINK_OBJECT,
                self::COPY_OBJECT,
                self::CREATE_OBJECT,
                self::EDIT_TEMPLATE,
                self::EDIT_TEMPLATE_EXISTING,
                self::CHANGE_OWNER
            ]
        )) {
            return true;
        }
        return false;
    }

    public static function delete(int $ref_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilDB->query('DELETE FROM ' . self::LOG_TABLE_NAME . ' WHERE ref_id = ' . $ilDB->quote($ref_id, 'integer'));
        self::garbageCollection();
    }

    public static function garbageCollection(): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $settings = ilPrivacySettings::getInstance();
        $max = $settings->getRbacLogAge();

        $ilDB->query('DELETE FROM ' . self::LOG_TABLE_NAME . ' WHERE created < ' . $ilDB->quote(
            strtotime("-" . $max . "months"),
            "integer"
        ));
    }
}
