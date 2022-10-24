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
* Class ilObjectAccess
*
* This class contains methods that check object specific conditions
* for access to objects. Every object type should provide an
* inherited class called ilObj<TypeName>Access
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilObjectAccess implements ilWACCheckingClass
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

        // add no access info item and return false if access is not granted
        // $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $a_text, $a_data = "");
        //
        // for all RBAC checks use checkAccessOfUser instead the normal checkAccess-method:
        // $rbacsystem->checkAccessOfUser($a_user_id, $a_permission, $a_ref_id)

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
     * @return array{permission?:string, cmd?:string, lang_var?:string, default?:bool}[]
     */
    public static function _getCommands(): array
    {
        return [];
    }

    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto(string $target): bool
    {
        global $DIC;
        $ilAccess = $DIC->access();

        $arr = explode("_", $target);

        if (
            $ilAccess->checkAccess("read", "", (int) $arr[1]) ||
            $ilAccess->checkAccess("visible", "", (int) $arr[1])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Type-specific implementation of general status, has to be overwritten if object type does
     * not support centralized offline handling
     *
     * Used in ListGUI and Learning Progress
     */
    public static function _isOffline(int $obj_id): bool
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        if ($objDefinition->supportsOfflineHandling(ilObject::_lookupType($obj_id))) {
            return ilObject::lookupOfflineStatus($obj_id);
        }
        return false;
    }

    /**
     * Preload data
     */
    public static function _preloadData(array $obj_ids, array $ref_ids): void
    {
    }

    /**
     * @inheritdoc
     */
    public function canBeDelivered(ilWACPath $ilWACPath): bool
    {
        global $ilAccess;

        preg_match("/\\/obj_([\\d]*)\\//uism", $ilWACPath->getPath(), $results);
        foreach (ilObject2::_getAllReferences((int) $results[1]) as $ref_id) {
            if ($ilAccess->checkAccess('visible', '', $ref_id) || $ilAccess->checkAccess('read', '', $ref_id)) {
                return true;
            }
        }

        return false;
    }
}
