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
    * Checks whether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * Please do not check any preconditions handled by
    * ilConditionHandler here. Also don't do any RBAC checks.
    */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];

        switch ($permission) {
            case 'visible':
            case 'read':
                include_once './Modules/GroupReference/classes/class.ilObjGroupReference.php';
                $target_ref_id = (int) ilObjGroupReference::_lookupTargetRefId($obj_id);

                if (!$ilAccess->checkAccessOfUser($user_id, $permission, $cmd, $target_ref_id)) {
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
     */
    public static function _getCommands($a_ref_id = 0): array
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
