<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjCategoryReferenceAccess extends ilContainerReferenceAccess
{
    /**
     * get commands
     *
     * Depends on permissions
     *
     * @param int $a_ref_id Reference id of course link
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
    public static function _getCommands($a_ref_id = null)
    {
        global $DIC;

        $ilAccess = $DIC->access();

        if ($ilAccess->checkAccess('write', '', $a_ref_id)) {
            // Only local (reference specific commands)
            $commands = array(
                array("permission" => "visible", "cmd" => "", "lang_var" => "show","default" => true),
                array("permission" => "write", "cmd" => "editReference", "lang_var" => "edit")
            );
        } else {
            include_once('./Modules/Category/classes/class.ilObjCategoryAccess.php');
            $commands = ilObjCategoryAccess::_getCommands();
        }
        return $commands;
    }
}
