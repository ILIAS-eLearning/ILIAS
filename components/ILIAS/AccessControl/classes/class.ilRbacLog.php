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
 * class ilRbacLog
 *  Log changes in Rbac-related settings
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAccessControl
 */
class ilRbacLog
{
    public const EDIT_PERMISSIONS = 1;
    public const MOVE_OBJECT = 2;
    public const LINK_OBJECT = 3;
    public const COPY_OBJECT = 4;
    public const CREATE_OBJECT = 5;
    public const EDIT_TEMPLATE = 6;
    public const EDIT_TEMPLATE_EXISTING = 7;
    public const CHANGE_OWNER = 8;

    public static function isActive(): bool
    {
        return ilPrivacySettings::getInstance()->enabledRbacLog();
    }

    public static function gatherFaPa(int $a_ref_id, array $a_role_ids, bool $a_add_action = false): array
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $result = array();

        // #10946 - if result is written to log directly we need to add an "action" dimension
        // if result is used as input to diffFaPa() we need "raw" data

        // roles
        foreach ($a_role_ids as $role_id) {
            if ($role_id != SYSTEM_ROLE_ID) {
                if ($a_add_action) {
                    $result["ops"][$role_id]["add"] = $rbacreview->getRoleOperationsOnObject($role_id, $a_ref_id);
                } else {
                    $result["ops"][$role_id] = $rbacreview->getRoleOperationsOnObject($role_id, $a_ref_id);
                }
            }
        }

        // inheritance
        if ($a_ref_id && $a_ref_id != ROLE_FOLDER_ID) {
            if ($a_add_action) {
                $result["inht"]["add"] = $rbacreview->getRolesOfRoleFolder($a_ref_id);
            } else {
                $result["inht"] = $rbacreview->getRolesOfRoleFolder($a_ref_id);
            }
        }

        return $result;
    }

    public static function diffFaPa(array $a_old, array $a_new): array
    {
        $result = array();

        // roles
        foreach ((array) $a_old["ops"] as $role_id => $ops) {
            $diff = array_diff($ops, $a_new["ops"][$role_id]);
            if ($diff !== []) {
                $result["ops"][$role_id]["rmv"] = array_values($diff);
            }
            $diff = array_diff($a_new["ops"][$role_id], $ops);
            if ($diff !== []) {
                $result["ops"][$role_id]["add"] = array_values($diff);
            }
        }

        if (isset($a_old["inht"]) || isset($a_new["inht"])) {
            if (isset($a_old["inht"]) && !isset($a_new["inht"])) {
                $result["inht"]["rmv"] = $a_old["inht"];
            } elseif (!isset($a_old["inht"]) && isset($a_new["inht"])) {
                $result["inht"]["add"] = $a_new["inht"];
            } else {
                $diff = array_diff($a_old["inht"], $a_new["inht"]);
                if ($diff !== []) {
                    $result["inht"]["rmv"] = array_values($diff);
                }
                $diff = array_diff($a_new["inht"], $a_old["inht"]);
                if ($diff !== []) {
                    $result["inht"]["add"] = array_values($diff);
                }
            }
        }
        return $result;
    }

    public static function gatherTemplate(int $a_role_ref_id, int $a_role_id): array
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        return $rbacreview->getAllOperationsOfRole($a_role_id, $a_role_ref_id);
    }

    public static function diffTemplate(array $a_old, array $a_new): array
    {
        $result = array();
        $types = array_unique(array_merge(array_keys($a_old), array_keys($a_new)));
        foreach ($types as $type) {
            if (!isset($a_old[$type])) {
                $result[$type]["add"] = $a_new[$type];
            } elseif (!isset($a_new[$type])) {
                $result[$type]["rmv"] = $a_old[$type];
            } else {
                $diff = array_diff($a_old[$type], $a_new[$type]);
                if ($diff !== []) {
                    $result[$type]["rmv"] = array_values($diff);
                }
                $diff = array_diff($a_new[$type], $a_old[$type]);
                if ($diff !== []) {
                    $result[$type]["add"] = array_values($diff);
                }
            }
        }
        return $result;
    }

    public static function add(int $a_action, int $a_ref_id, array $a_diff, bool $a_source_ref_id = false): bool
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilDB = $DIC->database();

        if (self::isValidAction($a_action) && count($a_diff)) {
            if ($a_source_ref_id) {
                $a_diff["src"] = $a_source_ref_id;
            }
            $id = $ilDB->nextId('rbac_log');

            $ilDB->query("INSERT INTO rbac_log (log_id, user_id, created, ref_id, action, data)" .
                " VALUES (" . $ilDB->quote($id, "integer") . "," . $ilDB->quote($ilUser->getId(), "integer") .
                "," . $ilDB->quote(time(), "integer") .
                "," . $ilDB->quote($a_ref_id, "integer") . "," . $ilDB->quote($a_action, "integer") .
                "," . $ilDB->quote(serialize($a_diff), "text") . ")");
            return true;
        }
        return false;
    }

    protected static function isValidAction(int $a_action): bool
    {
        if (in_array(
            $a_action,
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

    public static function getLogItems(int $a_ref_id, int $a_limit, int $a_offset, array $a_filter = null): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $rbacreview = $DIC->rbac()->review();

        $where = [];
        if ($a_filter) {
            if ($a_filter["action"]) {
                $where[] = "action = " . $ilDB->quote($a_filter["action"], "integer");
            }
            if ($a_filter["date"]["from"]) {
                $from = $a_filter["date"]["from"]->get(IL_CAL_UNIX);
                $from = strtotime("00:00:00", $from);
                $where[] = "created >= " . $ilDB->quote($from, "integer");
            }
            if ($a_filter["date"]["to"]) {
                $to = $a_filter["date"]["to"]->get(IL_CAL_UNIX);
                $to = strtotime("23:59:59", $to);
                $where[] = "created <= " . $ilDB->quote($to, "integer");
            }

            if (count($where) > 0) {
                $where = array_merge([' AND '], [implode(' AND ', $where)]);
            }
        }

        $set = $ilDB->query("SELECT COUNT(*) FROM rbac_log WHERE ref_id = " . $ilDB->quote(
            $a_ref_id,
            "integer"
        ) . implode('', $where));
        $res = $ilDB->fetchAssoc($set);
        $count = array_pop($res);

        $ilDB->setLimit($a_limit, $a_offset);
        $set = $ilDB->query("SELECT * FROM rbac_log WHERE ref_id = " . $ilDB->quote($a_ref_id, "integer") .
            implode('', $where) . " ORDER BY created DESC");
        $result = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $row["data"] = unserialize($row["data"]);
            $result[] = $row;
        }
        return ["cnt" => $count, "set" => $result];
    }

    public static function delete(int $a_ref_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilDB->query("DELETE FROM rbac_log WHERE ref_id = " . $ilDB->quote($a_ref_id, "integer"));
        self::garbageCollection();
    }

    public static function garbageCollection(): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $settings = ilPrivacySettings::getInstance();
        $max = $settings->getRbacLogAge();

        $ilDB->query("DELETE FROM rbac_log WHERE created < " . $ilDB->quote(
            strtotime("-" . $max . "months"),
            "integer"
        ));
    }
}
