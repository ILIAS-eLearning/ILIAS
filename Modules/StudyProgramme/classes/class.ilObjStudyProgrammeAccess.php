<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once('./Services/Object/classes/class.ilObjectAccess.php');
require_once('./Services/User/classes/class.ilUserAccountSettings.php');
require_once('./Services/Conditions/interfaces/interface.ilConditionHandling.php');


/**
 * Class ilObjStudyProgrammeAccess
 *
 * TODO: deletion is only allowed if there are now more users assigned to the
 * programme.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */
class ilObjStudyProgrammeAccess extends ilObjectAccess implements ilConditionHandling
{

    /**
    * Checks wether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * Please do not check any preconditions handled by
    * ilConditionHandler here. Also don't do any RBAC checks.
    *
    * @param	string		$a_cmd			command (not permission!)
    * @param	string		$a_permission	permission
    * @param	int			$a_ref_id		reference id
    * @param	int			$a_obj_id		object id
    * @param	int			$a_user_id		user id (if not provided, current user is taken)
    *
    * @return	boolean		true, if everything is ok
    */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }

        if ($a_permission == "delete") {
            require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
            $prg = ilObjStudyProgramme::getInstanceByRefId($a_ref_id);
            if ($prg->hasRelevantProgresses()) {
                return false;
            }
        }

        return parent::_checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "");
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
    public static function _getCommands()
    {
        $commands = array();
        $commands[] = array('permission' => 'read', 'cmd' => 'view', 'lang_var' => 'show', 'default' => true);
        $commands[] = array('permission' => 'write', 'cmd' => 'view', 'lang_var' => 'edit_content');
        $commands[] = array( 'permission' => 'write', 'cmd' => 'edit', 'lang_var' => 'settings');

        return $commands;
    }

    /**
     * check whether goto script will succeed
     */
    public static function _checkGoto($a_target)
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
    public static function getConditionOperators()
    {
        return array(
            ilConditionHandler::OPERATOR_ACCREDITED_OR_PASSED
        );
    }

    /**
     *
     * @param type $a_obj_id
     * @param type $a_operator
     * @param type $a_value
     * @param type $a_usr_id
     * @return boolean
     */
    public static function checkCondition($a_obj_id, $a_operator, $a_value, $a_usr_id)
    {
        if ($a_operator === ilConditionHandler::OPERATOR_ACCREDITED_OR_PASSED) {
            $valid_progress = array(
                ilStudyProgrammeProgress::STATUS_COMPLETED,
                ilStudyProgrammeProgress::STATUS_ACCREDITED
            );

            $prg_user_progress = ilObjStudyProgramme::_getStudyProgrammeUserProgressDB()->getInstancesForUser($a_obj_id, $a_usr_id);
            foreach ($prg_user_progress as $progress) {
                if (in_array($progress->getStatus(), $valid_progress)) {
                    return true;
                }
            }
        }
        return false;
    }
}
