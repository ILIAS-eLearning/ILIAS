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
* Class ilObjSCORMVerificationAccess
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*/
class ilObjSCORMVerificationAccess extends ilObjectAccess
{
    /**
     * @return array<int, array<string, string|bool>>
     */
    public static function _getCommands(): array
    {
        $commands = [];
        $commands[] = ['permission' => 'read', 'cmd' => 'view', 'lang_var' => 'show', 'default' => true];
        return $commands;
    }

    public static function _checkGoto(string $target): bool
    {
        global $DIC;
        $ilAccess = $DIC->access();

        $t_arr = explode('_', $target);

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
