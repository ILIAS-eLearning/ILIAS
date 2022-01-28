<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLTIConsumer
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilObjLTIConsumerAccess extends ilObjectAccess implements ilConditionHandling
{
    public static function _getCommands()
    {
        $commands = array(
            array(
                "permission" => "read",
                "cmd" => "infoScreen",
                "lang_var" => "",
                "default" => true
            ),
            array(
                'permission' => 'write',
                'cmd' => 'ilLTIConsumerSettingsGUI::showSettings',
                'lang_var' => 'settings'
            )
        );
        
        return $commands;
    }

    public static function getConditionOperators() : array
    {
        return [
            ilConditionHandler::OPERATOR_PASSED
        ];
    }
    
    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id) : bool
    {
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_PASSED:
                return ilLPStatus::_hasUserCompleted($a_trigger_obj_id, $a_usr_id);
        }
        
        return false;
    }
}
