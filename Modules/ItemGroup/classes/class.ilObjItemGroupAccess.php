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
 * Item group access class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjItemGroupAccess extends ilObjectAccess
{
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilRbacSystem $rbacsystem;
    protected ilAccessHandler $access;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
    }

    public static function _getCommands()
    {
        $commands = array(
            array("permission" => "read", "cmd" => "gotoParent", "lang_var" => "", "default" => true),
            array("permission" => "write", "cmd" => "listMaterials", "lang_var" => "edit_content", "default" => false),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings", "default" => false)
        );
        
        return $commands;
    }

    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        return true;
    }
    
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);

        if ($t_arr[0] != "itgr" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }
        return false;
    }
}
