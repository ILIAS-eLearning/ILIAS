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
 * Class ilObjCategoryAccess
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjCategoryAccess extends ilObjectAccess
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
        $commands = [];
        $commands[] = ["permission" => "read", "cmd" => "render", "lang_var" => "show", "default" => true];


        // BEGIN WebDAV
        if (ilDAVActivationChecker::_isActive()) {
            $webdav_obj = new ilObjWebDAV();
            $commands[] = $webdav_obj->retrieveWebDAVCommandArrayForActionMenu();
        }
        // END WebDAV
        $commands[] = ["permission" => "write", "cmd" => "enableAdministrationPanel", "lang_var" => "edit_content"];
        $commands[] = ["permission" => "write", "cmd" => "edit", "lang_var" => "settings"];

        return $commands;
    }

    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);

        if ($t_arr[0] !== "cat" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", (int) $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", (int) $t_arr[1])) {
            return true;
        }
        return false;
    }
}
