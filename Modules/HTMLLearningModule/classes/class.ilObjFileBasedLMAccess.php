<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjFileBasedLMAccess extends ilObjectAccess
{
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilRbacSystem $rbacsystem;
    protected ilAccessHandler $access;
    public static $startfile;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
    }

    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilAccess = $this->access;

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }

        switch ($a_permission) {
            case "read":

                if (ilObjFileBasedLMAccess::_determineStartUrl($a_obj_id) == "") {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                break;
        }
        return true;
    }
    
    public static function _getCommands()
    {
        $commands = array(
            array("permission" => "read", "cmd" => "view", "lang_var" => "show",
                "default" => true),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "edit_content"),
            array("permission" => "write", "cmd" => "properties", "lang_var" => "settings")
        );
        
        return $commands;
    }

    //
    // access relevant methods
    //

    public static function _determineStartUrl(int $a_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (isset(self::$startfile[$a_id])) {
            $start_file = self::$startfile[$a_id];
        } else {
            $q = "SELECT startfile FROM file_based_lm WHERE id = " . $ilDB->quote($a_id, "integer");
            $set = $ilDB->query($q);
            $rec = $ilDB->fetchAssoc($set);
            $start_file = $rec["startfile"];
            self::$startfile[$a_id] = $start_file . "";
        }
        
        $dir = ilUtil::getWebspaceDir() . "/lm_data/lm_" . $a_id;
        
        if (($start_file != "") &&
            (is_file($dir . "/" . $start_file))) {
            return "./" . $dir . "/" . $start_file;
        } elseif (is_file($dir . "/index.html")) {
            return "./" . $dir . "/index.html";
        } elseif (is_file($dir . "/index.htm")) {
            return "./" . $dir . "/index.htm";
        }

        return "";
    }

    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);

        if ($t_arr[0] != "htlm" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("visible", "", $t_arr[1]) ||
            $ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }
        return false;
    }

    /**
     * Returns the number of bytes used on the harddisk by the learning module
     * with the specified object id.
     */
    public static function _lookupDiskUsage(int $a_id) : int
    {
        $lm_data_dir = ilUtil::getWebspaceDir('filesystem') . "/lm_data";
        $lm_dir = $lm_data_dir . DIRECTORY_SEPARATOR . "lm_" . $a_id;
        
        return file_exists($lm_dir) ? ilUtil::dirsize($lm_dir) : 0;
    }

    public static function _preloadData($a_obj_ids, $a_ref_ids)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT id, startfile FROM file_based_lm WHERE " .
            $ilDB->in("id", $a_obj_ids, false, "integer");

        $lm_set = $ilDB->query($q);
        while ($rec = $ilDB->fetchAssoc($lm_set)) {
            self::$startfile[$rec["id"]] = $rec["startfile"] . "";
        }
    }
}
