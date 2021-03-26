<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjWorkspaceRootFolderAccess
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjWorkspaceRootFolderAccess extends ilObjectAccess
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
        $commands = array(
            array("permission" => "read", "cmd" => "render", "lang_var" => "show",
                "default" => true),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
        );
        
        return $commands;
    }
}
