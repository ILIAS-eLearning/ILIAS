<?php declare(strict_types=0);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjCourseVerificationAccess
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjCourseVerificationAccess extends ilObjectAccess
{
    public static function _getCommands() : array
    {
        $commands = [];
        $commands[] = ['permission' => 'read', 'cmd' => 'view', 'lang_var' => 'show', 'default' => true];
        return $commands;
    }

    public static function _checkGoto(string $a_target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode('_', $a_target);

        // #11021
        // personal workspace context: do not force normal login
        if (isset($t_arr[2]) && $t_arr[2] === 'wsp') {
            return ilSharedResourceGUI::hasAccess((int) $t_arr[1]);
        }

        if ($ilAccess->checkAccess('read', '', (int) $t_arr[1])) {
            return true;
        }

        return false;
    }
}
