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

class ilObjContentPageAccess extends ilObjectAccess implements ilContentPageObjectConstants, ilConditionHandling
{
    public static function _getCommands(): array
    {
        $commands = [
            [
                'permission' => 'read',
                'cmd' => self::UI_CMD_VIEW,
                'lang_var' => 'show',
                'default' => true
            ],
            [
                'permission' => 'write',
                'cmd' => 'edit',
                'lang_var' => 'settings'
            ],
        ];

        return $commands;
    }

    public static function _checkGoto(string $target): bool
    {
        $targetAttributes = explode('_', $target);

        if (2 !== count($targetAttributes) || $targetAttributes[0] !== self::OBJ_TYPE || ((int) $targetAttributes[1]) <= 0) {
            return false;
        }

        return parent::_checkGoto($target);
    }

    public static function getConditionOperators(): array
    {
        return [];
    }

    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id): bool
    {
        return false;
    }
}
