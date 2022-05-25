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
 * Class ilObjMediaPoolAccess
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjMediaPoolAccess extends ilObjectAccess
{
    public static function _getCommands() : array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "", "lang_var" => "show",
                "default" => true),
            array("permission" => "write", "cmd" => "", "lang_var" => "edit_content",
                "default" => false),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings",
                "default" => false)
        );
        
        return $commands;
    }

    /**
     * check whether goto script will succeed
     */
    public static function _checkGoto(string $target) : bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);

        if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            return true;
        }
        return false;
    }
}
