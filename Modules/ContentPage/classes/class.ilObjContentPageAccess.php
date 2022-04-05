<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjContentPageAccess extends ilObjectAccess implements ilContentPageObjectConstants, ilConditionHandling
{
    public static function _getCommands() : array
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

    public static function _checkGoto(string $target) : bool
    {
        $targetAttributes = explode('_', $target);

        if (2 !== count($targetAttributes) || $targetAttributes[0] !== self::OBJ_TYPE || ((int) $targetAttributes[1]) <= 0) {
            return false;
        }

        return parent::_checkGoto($target);
    }

    public static function getConditionOperators() : array
    {
        return [];
    }

    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id) : bool
    {
        return false;
    }
}
