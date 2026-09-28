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

class ilObjSurveyQuestionPoolAccess extends ilObjectAccess
{
    public static function _getCommands(): array
    {
        $commands = array(
            array("permission" => "read",
                  "cmd" => "questions",
                  "lang_var" => "edit_questions",
                  "default" => true
            ),
            array("permission" => "write", "cmd" => "questions", "lang_var" => "edit_questions"),
            array("permission" => "write", "cmd" => "properties", "lang_var" => "settings")
        );

        return $commands;
    }

    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target, 3);
        if ($t_arr[0] !== "spl" ||
            !isset($t_arr[1]) ||
            !ctype_digit($t_arr[1]) ||
            ($ref_id = (int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("visible", "", $ref_id) ||
            $ilAccess->checkAccess("read", "", $ref_id)) {
            return true;
        }
        return false;
    }

    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        global $DIC;
        $ilAccess = $DIC->access();
        $user_id ??= $DIC->user()->getId();

        if (in_array($permission, ["read", "visible"]) && self::_isOffline(ilObject::_lookupObjId($ref_id))) {
            if (!$ilAccess->checkAccessOfUser($user_id, "write", "", $ref_id)) {
                return false;
            }
        }
        return true;
    }
}
