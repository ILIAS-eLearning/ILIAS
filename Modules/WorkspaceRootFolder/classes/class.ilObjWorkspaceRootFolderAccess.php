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
    public static function _getCommands(): array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "render", "lang_var" => "show",
                "default" => true),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
        );

        return $commands;
    }
}
