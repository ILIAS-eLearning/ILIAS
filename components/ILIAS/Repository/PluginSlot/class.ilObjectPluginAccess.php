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

/**
 * Access class for repsoitory plugins
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjectPluginAccess extends ilObjectAccess
{
    protected ilObjUser $user;
    protected ilAccessHandler $access;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->access = $DIC->access();
    }

    /**
    * check condition
    *
    * this method is called by ilConditionHandler
    */
    public function _checkCondition(int $a_obj_id, string $a_operator, $a_value, int $a_usr_id = 0): bool
    {
        return true;
    }

    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);

        if ($ilAccess->checkAccess("read", "", (int) $t_arr[1])) {
            return true;
        }
        return false;
    }

    // this is called by permission -> check permissions of user screen
    public static function _getCommands(): array
    {
        return [];
    }
}
