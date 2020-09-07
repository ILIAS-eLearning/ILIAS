<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/ContainerReference/classes/class.ilContainerReferenceAccess.php");

/**
 * @author Fabian Wolf <wolf@leifos.com>
 * @extends ilContainerReferenceAccess
 *
 * @ingroup ModulesGroupReference
*/

class ilObjGroupReferenceAccess extends ilContainerReferenceAccess
{
    /**
    * Checks wether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * Please do not check any preconditions handled by
    * ilConditionHandler here. Also don't do any RBAC checks.
    *
    * @global ilAccessHandler $ilAccess
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
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        
        switch ($a_permission) {
            case 'visible':
            case 'read':
                include_once './Modules/GroupReference/classes/class.ilObjGroupReference.php';
                $target_ref_id = ilObjGroupReference::_lookupTargetRefId($a_obj_id);
                
                if (!$ilAccess->checkAccessOfUser($a_user_id, $a_permission, $a_cmd, $target_ref_id)) {
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * get commands
     *
     * Depends on permissions
     *
     * @global ilAccessHandler $ilAccess
     *
     * @param int $a_ref_id Reference id of group link
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *	(
     *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *	);
     *
     * @return array
     */
    public static function _getCommands($a_ref_id = 0)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        
        if ($ilAccess->checkAccess('write', '', $a_ref_id)) {
            // Only local (reference specific commands)
            $commands = array(
                array("permission" => "visible", "cmd" => "", "lang_var" => "show","default" => true),
                array("permission" => "write", "cmd" => "editReference", "lang_var" => "edit")
            );
        } else {
            include_once('./Modules/Group/classes/class.ilObjGroupAccess.php');
            $commands = ilObjGroupAccess::_getCommands();
        }
        return $commands;
    }
}
