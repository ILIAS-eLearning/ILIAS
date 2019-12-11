<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
* Class ilObjWorkspaceFolderAccess
*
*
* @author 	Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilObjFolderAccess.php 26739 2010-11-28 20:33:51Z smeyer $
*
*/
class ilObjWorkspaceFolderAccess extends ilObjectAccess
{
    private static $folderSettings;
   
    private static function getFolderSettings()
    {
        if (is_null(ilObjWorkspaceFolderAccess::$folderSettings)) {
            ilObjWorkspaceFolderAccess::$folderSettings = new ilSetting('fold');
        }
        return ilObjWorkspaceFolderAccess::$folderSettings;
    }
     
    
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
        $commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "edit");
        return $commands;
    }
}
