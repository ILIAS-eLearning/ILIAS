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
 * Class ilObjContentObjectAccess
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesScormAicc
 */
class ilObjSAHSLearningModuleAccess extends ilObjectAccess implements ilConditionHandling
{
    /**
     * Get possible conditions operaditors
     * @return string[]
     */
    public static function getConditionOperators(): array
    {
        return array(
            ilConditionHandler::OPERATOR_FINISHED,
            ilConditionHandler::OPERATOR_FAILED
        );
    }

    public static function checkCondition(
        int $a_trigger_obj_id,
        string $a_operator,
        string $a_value,
        int $a_usr_id
    ): bool {
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_FAILED:
                return ilLPStatus::_lookupStatus($a_trigger_obj_id, $a_usr_id) == ilLPStatus::LP_STATUS_FAILED_NUM;
                break;

            case ilConditionHandler::OPERATOR_FINISHED:
            default:
                return ilLPStatus::_hasUserCompleted($a_trigger_obj_id, $a_usr_id);
        }
    }

    /**
     * get commands
     * this method returns an array of all possible commands/permission combinations
     * example:
     * $commands = array
     *    (
     *        array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *        array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *    );
     * @return array<int, array>
     */
    public static function _getCommands(int $a_obj_id = null): array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "view", "lang_var" => "show", "default" => true),
            //            array("permission" => "write", "cmd" => "editContent", "lang_var" => "edit_content"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings")
        );
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

        if ($t_arr[0] !== "sahs" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("visible", "", (int) $t_arr[1]) || $ilAccess->checkAccess("read", "", (int) $t_arr[1])) {
            return true;
        }
        return false;
    }


    /**
     * Returns the number of bytes used on the harddisk by the learning module
     * with the specified object id.
     * @param int $a_id object id of a file object.
     */
    public static function _lookupDiskUsage(int $a_id): int
    {
        $lm_data_dir = ilFileUtils::getWebspaceDir('filesystem') . "/lm_data";
        $lm_dir = $lm_data_dir . DIRECTORY_SEPARATOR . "lm_" . $a_id;

        return file_exists($lm_dir) ? ilFileUtils::dirsize($lm_dir) : 0;
    }
}
