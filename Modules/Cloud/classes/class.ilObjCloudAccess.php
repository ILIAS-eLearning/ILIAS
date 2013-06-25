<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");
include_once("class.ilCloudConnector.php");
include_once("class.ilObjCloud.php");

/**
 * Class ilObjCloudAccess
 *
 * @author    Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id:
 *
 * @extends ilObjectAccess
 */
class ilObjCloudAccess extends ilObjectAccess
{
    protected static $access_cache = array();

    public function _getCommands()
    {
        $commands = array
        (
            array("permission" => "read", "cmd" => "render", "lang_var" => "show", "default" => true),
            array("permission" => "write", "cmd" => "editSettings", "lang_var" => "settings")
        );

        return $commands;
    }


    /**
     * @param string $a_cmd
     * @param string $a_permission
     * @param int $a_ref_id
     * @param int $a_obj_id
     * @param string $a_user_id
     * @return bool
     */
    function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        global $ilUser, $rbacsystem;

        $object = new ilObjCloud($a_ref_id);

        /**
         * Check if plugin of object is active
         */
        try
        {
            ilCloudConnector::checkServiceActive($object->getServiceName());
        } catch (Exception $e)
        {
            return false;
        }


        if ($a_user_id == "")
        {
            $a_user_id = $ilUser->getId();
        }

        /**
         * Check if authentication is complete. If not, only the owner of the object has access. This prevents the
         * authentication of an account which does not belong to the owner.
         */
        if (!ilObjCloudAccess::checkAuthStatus($a_obj_id) && $a_user_id != $object->getOwnerId())
        {
            return false;
        }

        switch ($a_permission)
        {
            case "visible":
            case "read":
                if (!ilObjCloudAccess::checkOnline($a_obj_id) && !$rbacsystem->checkAccessOfUser($a_user_id, "write", $a_ref_id))
                {
                    return false;
                }
                break;
        }
        return true;

    }

    /**
     * @param $a_target
     * @return bool
     */
    function _checkGoto($a_target)
    {

        global $ilAccess;

        $t_arr = explode("_", $a_target);

        if ($ilAccess->checkAccess("read", "", $t_arr[1]))
        {
            return true;
        }
        return false;
    }

    /**
     * @param $a_id
     * @return mixed
     */
    static function checkOnline($a_id)
    {
        global $ilDB;

        if(!isset(self::$access_cache[$a_id]["online"]))
        {
            $set = $ilDB->query("SELECT is_online FROM il_cld_data " .
                " WHERE id = " . $ilDB->quote($a_id, "integer")
            );
            $rec = $ilDB->fetchAssoc($set);
            self::$access_cache[$a_id]["online"] = (boolean)($rec["is_online"]);
        }
        return self::$access_cache[$a_id]["online"];


    }

    /**
     * @param $a_id
     * @return mixed
     */
    static function checkAuthStatus($a_id)
    {
        global $ilDB;

        if (!isset(self::$access_cache[$a_id]["auth_status"]))
        {
            $set = $ilDB->query("SELECT auth_complete FROM il_cld_data " .
                    " WHERE id = " . $ilDB->quote($a_id, "integer")
            );
            $rec = $ilDB->fetchAssoc($set);
            self::$access_cache[$a_id]["auth_status"] = (boolean)$rec["auth_complete"];
        }
        return self::$access_cache[$a_id]["auth_status"];

    }
}


?>
