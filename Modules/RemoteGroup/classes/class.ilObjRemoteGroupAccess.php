<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ModulesRemoteGroup
*/

class ilObjRemoteGroupAccess extends ilObjectAccess
{
    /**
    * checks whether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null) : bool
    {
        global $ilUser, $lng, $rbacsystem, $ilAccess;

        if (is_null($user_id)) {
            $user_id = $ilUser->getId();
        }

        switch ($permission) {
            case "visible":
                $active = ilObjRemoteGroup::_lookupOnline($obj_id);
                $tutor = $rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id);

                if (!$active) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                }
                if (!$tutor and !$active) {
                    return false;
                }
                break;

            case 'read':
                $tutor = $rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id);
                if ($tutor) {
                    return true;
                }
                $active = ilObjRemoteGroup::_lookupOnline($obj_id);

                if (!$active) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                break;
        }
        return true;
    }


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
    public static function _getCommands() : array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "show", "lang_var" => "info",
                "default" => true),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "edit")
        );
        
        return $commands;
    }
}
