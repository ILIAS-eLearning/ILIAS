<?php declare(strict_types=1);

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

    public static function _checkGoto(string $target) : bool
    {
        global $DIC;

        $t_arr = explode('_', $target);

        if ($t_arr[0] !== 'chtr' || !isset($t_arr[1]) || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($DIC->rbac()->system()->checkAccess('visible', (int) $t_arr[1])) {
            return true;
        }

        return false;
    }
}
