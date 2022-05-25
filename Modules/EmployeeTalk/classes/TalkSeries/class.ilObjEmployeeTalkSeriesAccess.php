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

use ILIAS\EmployeeTalk\UI\ControlFlowCommand;

final class ilObjEmployeeTalkSeriesAccess extends ilObjectAccess
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new ilObjEmployeeTalkSeriesAccess();
        }

        return self::$instance;
    }

    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *    (
     *        array('permission' => 'read', 'cmd' => 'view', 'lang_var' => 'show'),
     *        array('permission' => 'write', 'cmd' => 'edit', 'lang_var' => 'edit'),
     *    );
     */
    public static function _getCommands() : array
    {
        $commands = [
            [
                'permission' => 'read',
                'cmd' => ControlFlowCommand::DEFAULT,
                'lang_var' => 'show',
                'default' => true,
            ]
        ];

        return $commands;
    }

    public static function _isOffline($a_obj_id): bool
    {
        return false;
    }

    /**
     * @param string $a_target check whether goto script will succeed
     *
     * @return bool
     */
    public static function _checkGoto(string $a_target) : bool
    {
        global $DIC;

        $t_arr = explode('_', $a_target);
        if ($t_arr[0] !== ilObjEmployeeTalkSeries::TYPE || ((int) $t_arr[1]) <= 0) {
            return false;
        }
        if ($DIC->access()->checkAccess('read', '', $t_arr[1])) {
            return true;
        }

        return false;
    }
}
