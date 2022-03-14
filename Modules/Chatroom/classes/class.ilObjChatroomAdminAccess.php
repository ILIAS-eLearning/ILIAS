<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjChatroomAdminAccess
 * Access class for chatroom objects.
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilObjChatroomAdminAccess extends ilObjectAccess
{
    public static function _getCommands() : array
    {
        $commands = [];
        $commands[] = ['permission' => 'read', 'cmd' => 'view', 'lang_var' => 'enter', 'default' => true];
        $commands[] = ['permission' => 'write', 'cmd' => 'edit', 'lang_var' => 'edit'];
        $commands[] = ['permission' => 'write', 'cmd' => 'versions', 'lang_var' => 'versions'];

        return $commands;
    }

    public static function _checkGoto(string $a_target): bool
    {
        global $DIC;

        $t_arr = explode('_', $a_target);

        if ($t_arr[0] !== 'chtr' || !isset($t_arr[1]) || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($DIC->rbac()->system()->checkAccess('visible', (int) $t_arr[1])) {
            return true;
        }

        return false;
    }
}
