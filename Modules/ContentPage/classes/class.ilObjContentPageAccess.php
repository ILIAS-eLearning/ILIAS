<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjContentPageAccess
 */
class ilObjContentPageAccess extends \ilObjectAccess implements \ilContentPageObjectConstants, \ilConditionHandling
{
    /**
     * @inheritdoc
     */
    public static function _getCommands()
    {
        $commands = [
            [
                'permission'=> 'read',
                'cmd'       => self::UI_CMD_VIEW,
                'lang_var'  => 'show',
                'default'   => true
            ],
            [
                'permission'=> 'write',
                'cmd'       => 'edit',
                'lang_var'  => 'settings'
            ],
        ];

        return $commands;
    }

    /**
     * @inheritdoc
     */
    public static function _checkGoto($a_target)
    {
        $targetAttributes = explode('_', $a_target);

        if (2 != count($targetAttributes) || $targetAttributes[0] != self::OBJ_TYPE || ((int) $targetAttributes[1]) <= 0) {
            return false;
        }

        return parent::_checkGoto($a_target);
    }

    /**
     * @inheritdoc
     */
    public static function getConditionOperators()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function checkCondition($a_trigger_obj_id, $a_operator, $a_value, $a_usr_id)
    {
        return false;
    }
}
