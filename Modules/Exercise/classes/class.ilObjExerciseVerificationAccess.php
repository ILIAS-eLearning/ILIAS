<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjExerciseVerificationAccess extends ilObjectAccess
{
    public static function _getCommands() : array
    {
        $commands = [];
        $commands[] = ['permission' => 'read', 'cmd' => 'view', 'lang_var' => 'show', 'default' => true];
        return $commands;
    }

    public static function _checkGoto($a_target) : bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode('_', $a_target);

        // #11021
        // personal workspace context: do not force normal login
        if (isset($t_arr[2]) && $t_arr[2] === 'wsp') {
            return ilSharedResourceGUI::hasAccess((int) $t_arr[1]);
        }
        return (bool) $ilAccess->checkAccess('read', '', (int) $t_arr[1]);
    }
}
