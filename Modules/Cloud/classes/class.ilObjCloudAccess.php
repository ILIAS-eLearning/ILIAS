<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("class.ilCloudConnector.php");
require_once("class.ilObjCloud.php");

/**
 * Class ilObjCloudAccess
 * @author    Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author    Martin Studer martin@fluxlabs.ch
 */
class ilObjCloudAccess extends ilObjectAccess
{
    protected static array $access_cache = [];

    public static function _getCommands() : array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "render", "lang_var" => "show", "default" => true),
            array("permission" => "write", "cmd" => "editSettings", "lang_var" => "settings"),
        );

        return $commands;
    }

    public function _checkAccess(
        $a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = ""
    ) : bool {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $rbacsystem = $DIC['rbacsystem'];
        $rbacreview = $DIC['rbacreview'];

        $object = new ilObjCloud($a_ref_id);

        /**
         * Check if plugin of object is active
         */
        try {
            ilCloudConnector::checkServiceActive($object->getServiceName());
        } catch (Exception $e) {
            return false;
        }

        if ($a_user_id === "") {
            $a_user_id = $ilUser->getId();
        }

        /**
         * Check if authentication is complete. If not, only the owner of the object has access. This prevents the
         * authentication of an account which does not belong to the owner.
         */
        if (self::checkAuthStatus($a_obj_id) && $a_user_id !== $object->getOwnerId() && !$rbacreview->isAssigned($a_user_id,
                2) === false) {
            return false;
        }

        switch ($a_permission) {
            case "visible":
            case "read":
                if (self::checkOnline($a_obj_id) && !$rbacsystem->checkAccessOfUser($a_user_id, "write",
                        $a_ref_id) === false) {
                    return false;
                }
                break;
        }

        return true;
    }

    public static function _checkGoto(string $a_target) : bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $t_arr = explode("_", $a_target);

        if ($ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }

        return false;
    }

    public static function checkOnline(int $id) : array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!isset(self::$access_cache[$id]["online"])) {
            $set = $ilDB->query("SELECT is_online FROM il_cld_data " . " WHERE id = " . $ilDB->quote($id, "integer"));
            $rec = $ilDB->fetchAssoc($set);
            self::$access_cache[$id]["online"] = (boolean) ($rec["is_online"]);
        }

        return self::$access_cache[$id]["online"];
    }

    public static function checkAuthStatus(int $id) : array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!isset(self::$access_cache[$id]["auth_status"])) {
            $set = $ilDB->query("SELECT auth_complete FROM il_cld_data " . " WHERE id = " . $ilDB->quote($id,
                    "integer"));
            $rec = $ilDB->fetchAssoc($set);
            self::$access_cache[$id]["auth_status"] = (boolean) $rec["auth_complete"];
        }

        return self::$access_cache[$id]["auth_status"];
    }
}
