<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* class ilRbacLog
*  Log changes in Rbac-related settings
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilRbacReview.php 24262 2010-06-15 06:48:14Z nkrzywon $
*
* @ingroup ServicesAccessControl
*/
class ilRbacLog
{
    const EDIT_PERMISSIONS = 1;
    const MOVE_OBJECT = 2;
    const LINK_OBJECT = 3;
    const COPY_OBJECT = 4;
    const CREATE_OBJECT = 5;
    const EDIT_TEMPLATE = 6;
    const EDIT_TEMPLATE_EXISTING = 7;
    const CHANGE_OWNER = 8;

    public static function isActive()
    {
        include_once "Services/PrivacySecurity/classes/class.ilPrivacySettings.php";
        $settings = ilPrivacySettings::_getInstance();
        if ($settings->enabledRbacLog()) {
            return true;
        }
        return false;
    }

    public static function gatherFaPa($a_ref_id, array $a_role_ids, $a_add_action = false)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

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

    public static function diffFaPa(array $a_old, array $a_new)
    {
        $result = array();

        // roles
        foreach ((array) $a_old["ops"] as $role_id => $ops) {
            $diff = array_diff($ops, $a_new["ops"][$role_id]);
            if (sizeof($diff)) {
                $result["ops"][$role_id]["rmv"] = array_values($diff);
            }
            $diff = array_diff($a_new["ops"][$role_id], $ops);
            if (sizeof($diff)) {
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
                if (sizeof($diff)) {
                    $result["inht"]["rmv"] = array_values($diff);
                }
                $diff = array_diff($a_new["inht"], $a_old["inht"]);
                if (sizeof($diff)) {
                    $result["inht"]["add"] = array_values($diff);
                }
            }
        }

        return $result;
    }

    public static function gatherTemplate($a_role_ref_id, $a_role_id)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        return $rbacreview->getAllOperationsOfRole($a_role_id, $a_role_ref_id);
    }

    public static function diffTemplate(array $a_old, array $a_new)
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
                if (sizeof($diff)) {
                    $result[$type]["rmv"] = array_values($diff);
                }
                $diff = array_diff($a_new[$type], $a_old[$type]);
                if (sizeof($diff)) {
                    $result[$type]["add"] = array_values($diff);
                }
            }
        }
        return $result;
    }

    public static function add($a_action, $a_ref_id, array $a_diff, $a_source_ref_id = false)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];

        if (self::isValidAction($a_action) && sizeof($a_diff)) {
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

    protected static function isValidAction($a_action)
    {
        if (in_array($a_action, array(self::EDIT_PERMISSIONS, self::MOVE_OBJECT, self::LINK_OBJECT,
            self::COPY_OBJECT, self::CREATE_OBJECT, self::EDIT_TEMPLATE, self::EDIT_TEMPLATE_EXISTING,
            self::CHANGE_OWNER))) {
            return true;
        }
        return false;
    }

    public static function getLogItems($a_ref_id, $a_limit, $a_offset, array $a_filter = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];

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

        $set = $ilDB->query("SELECT COUNT(*) FROM rbac_log WHERE ref_id = " . $ilDB->quote($a_ref_id, "integer") . implode('', $where));
        $count = array_pop($ilDB->fetchAssoc($set));

        $ilDB->setLimit($a_limit, $a_offset);
        $set = $ilDB->query("SELECT * FROM rbac_log WHERE ref_id = " . $ilDB->quote($a_ref_id, "integer") .
            implode('', $where) . " ORDER BY created DESC");
        $result = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $row["data"] = unserialize($row["data"]);
            $result[] = $row;
        }
        return array("cnt" => $count, "set" => $result);
    }

    public static function delete($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->query("DELETE FROM rbac_log WHERE ref_id = " . $ilDB->quote($a_ref_id, "integer"));

        self::garbageCollection();
    }

    public static function garbageCollection()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once "Services/PrivacySecurity/classes/class.ilPrivacySettings.php";
        $settings = ilPrivacySettings::_getInstance();
        $max = $settings->getRbacLogAge();

        $ilDB->query("DELETE FROM rbac_log WHERE created < " . $ilDB->quote(strtotime("-" . $max . "months"), "integer"));
    }
}
