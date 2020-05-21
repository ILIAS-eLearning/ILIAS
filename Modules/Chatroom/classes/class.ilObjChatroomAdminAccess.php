<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectAccess.php';

/**
 * Class ilObjChatroomAdminAccess
 * Access class for chatroom objects.
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilObjChatroomAdminAccess extends ilObjectAccess
{
    /**
     * {@inheritdoc}
     */
    public static function _getCommands()
    {
        $commands = array();
        $commands[] = array("permission" => "read", "cmd" => "view", "lang_var" => "enter", "default" => true);
        $commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "edit");
        $commands[] = array("permission" => "write", "cmd" => "versions", "lang_var" => "versions");

        return $commands;
    }

    /**
     * {@inheritdoc}
     */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $t_arr = explode('_', $a_target);

        if ($t_arr[0] != 'chtr' || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($DIC->rbac()->system()->checkAccess('visible', $t_arr[1])) {
            return true;
        }

        return false;
    }
}
