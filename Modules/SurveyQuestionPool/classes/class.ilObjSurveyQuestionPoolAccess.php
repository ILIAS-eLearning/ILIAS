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
 * @author Helmut Schottmueller <helmut.schottmueller@mac.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjSurveyQuestionPoolAccess extends ilObjectAccess
{
    public static function _getCommands(): array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "questions", "lang_var" => "edit_questions",
                "default" => true),
            array("permission" => "write", "cmd" => "questions", "lang_var" => "edit_questions"),
            array("permission" => "write", "cmd" => "properties", "lang_var" => "settings")
        );

        return $commands;
    }

    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);

        if ($ilAccess->checkAccess("visible", "", $t_arr[1]) ||
            $ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }
        return false;
    }
}
