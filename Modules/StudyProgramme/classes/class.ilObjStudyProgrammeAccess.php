<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilObjStudyProgrammeAccess
 *
 * TODO: deletion is only allowed if there are no more users assigned to the programme.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilObjStudyProgrammeAccess extends ilObjectAccess implements ilConditionHandling
{
    /**
    * Checks whether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * Please do not check any preconditions handled by
    * ilConditionHandler here. Also don't do any RBAC checks.
    *
    * @param	string		$cmd			command (not permission!)
    * @param	string		$permission	permission
    * @param	int			$ref_id		reference id
    * @param	int			$obj_id		object id
    * @param	int			$user_id		user id (if not provided, current user is taken)
    *
    * @return	boolean		true, if everything is ok
    */
    public function _checkAccess($cmd, $permission, $ref_id, $obj_id, $user_id = "") : bool
    {
        if ($user_id == "") {
            global $DIC;
            $user_id = $DIC->user()->getId();
        }

        if ($permission == "delete") {
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
    public static function _getCommands() : array
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
    public static function _checkGoto($a_target) : bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $t_arr = explode('_', $a_target);
        if ($t_arr[0] != 'prg' || ((int) $t_arr[1]) <= 0) {
            return false;
        }
        if ($ilAccess->checkAccess('read', '', $t_arr[1])) {
            return true;
        }

        return false;
    }

    /**
     * Get operators
     */
    public static function getConditionOperators() : array
    {
        return array(
            ilConditionHandler::OPERATOR_ACCREDITED_OR_PASSED
        );
    }

    /**
     *
     * @param int $obj_id
     * @param string $operator
     * @param mixed $value
     * @param int $usr_id
     * @return boolean
     */
    public static function checkCondition($obj_id, $operator, $value, $usr_id) : bool
    {
        if ($operator === ilConditionHandler::OPERATOR_ACCREDITED_OR_PASSED) {
            $valid_progress = array(
                ilStudyProgrammeProgress::STATUS_COMPLETED,
                ilStudyProgrammeProgress::STATUS_ACCREDITED
            );

            $prg_user_progress = ilStudyProgrammeDIC::dic()['model.Progress.ilStudyProgrammeProgressRepository']
                ->getByPrgIdAndUserId($obj_id, $usr_id);
                
            foreach ($prg_user_progress as $progress) {
                if (in_array($progress->getStatus(), $valid_progress)) {
                    return true;
                }
            }
        }
        return false;
    }
}
