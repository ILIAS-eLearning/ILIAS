<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
* Class ilObjSCORMVerificationAccess
*
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjFolderAccess.php 26739 2010-11-28 20:33:51Z smeyer $
*/
class ilObjSCORMVerificationAccess extends ilObjectAccess
{
    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *	(
     *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *	);
     */
    public static function _getCommands()
    {
        $commands = array();
        $commands[] = array("permission" => "read", "cmd" => "view", "lang_var" => "show", "default" => true);
        return $commands;
    }
    
    public static function _checkGoto($a_target)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        
        $t_arr = explode("_", $a_target);
        
        // #11021
        // personal workspace context: do not force normal login
        if (isset($t_arr[2]) && $t_arr[2] == "wsp") {
            include_once "Services/PersonalWorkspace/classes/class.ilSharedResourceGUI.php";
            return ilSharedResourceGUI::hasAccess($t_arr[1]);
        }

        if ($ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }
        return false;
    }
}
