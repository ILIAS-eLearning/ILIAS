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
 * Class ilObjStudyProgrammeAccess
 *
 * TODO: deletion is only allowed if there are no more users assigned to the programme.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilObjStudyProgrammeAccess extends ilObjectAccess implements ilConditionHandling
{
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        if ($user_id === 0 || $user_id === null) {
            global $DIC;
            $user_id = $DIC->user()->getId();
        }

        if ($permission === "delete") {
            $prg = ilObjStudyProgramme::getInstanceByRefId($ref_id);
            if ($prg->hasRelevantProgresses()) {
                return false;
            }
        }

        return parent::_checkAccess($cmd, $permission, $ref_id, $obj_id, $user_id);
    }

    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *    (
     *        array('permission' => 'read', 'cmd' => 'view', 'lang_var' => 'show'),
     *        array('permission' => 'write', 'cmd' => 'edit', 'lang_var' => 'edit'),
     *    );
     */
    public static function _getCommands(): array
    {
        $commands = [];
        $commands[] = ['permission' => 'read', 'cmd' => 'view', 'lang_var' => 'show', 'default' => true];
        $commands[] = ['permission' => 'write', 'cmd' => 'view', 'lang_var' => 'edit_content'];
        $commands[] = [ 'permission' => 'write', 'cmd' => 'edit', 'lang_var' => 'settings'];

        return $commands;
    }

    /**
     * check whether goto script will succeed
     */
    public static function _checkGoto(string $target): bool
    {
        global $DIC;
        $ilAccess = $DIC->access();
        $t_arr = explode('_', $target);
        if ($t_arr[0] !== 'prg' || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        return $ilAccess->checkAccess('read', '', (int) $t_arr[1]);
    }

    /**
     * Get operators
     */
    public static function getConditionOperators(): array
    {
        return array(
            ilConditionHandler::OPERATOR_ACCREDITED_OR_PASSED
        );
    }

    /**
     * @param int    $a_trigger_obj_id
     * @param string $a_operator
     * @param string $a_value
     * @param int    $a_usr_id
     * @return boolean
     */
    public static function checkCondition(
        int $a_trigger_obj_id,
        string $a_operator,
        string $a_value,
        int $a_usr_id
    ): bool {
        if ($a_operator === ilConditionHandler::OPERATOR_ACCREDITED_OR_PASSED) {
            $valid_progress = array(
                ilStudyProgrammeProgress::STATUS_COMPLETED,
                ilStudyProgrammeProgress::STATUS_ACCREDITED
            );

            $prg_user_progress = ilStudyProgrammeDIC::dic()['model.Progress.ilStudyProgrammeProgressRepository']
                ->getByPrgIdAndUserId($a_trigger_obj_id, $a_usr_id);

            foreach ($prg_user_progress as $progress) {
                if (in_array($progress->getStatus(), $valid_progress)) {
                    return true;
                }
            }
        }
        return false;
    }
}
