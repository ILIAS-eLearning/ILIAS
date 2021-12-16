<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjCmiXapiAccess
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilObjCmiXapiAccess extends ilObjectAccess implements ilConditionHandling
{
    public static function _getCommands()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $commands = array(
            array(
                "permission" => "read",
                "cmd" => "infoScreen",
                "lang_var" => "infoScreen",
                "default" => true
            ),
            array(
                'permission' => 'write',
                'cmd' => 'ilCmiXapiSettingsGUI::show',
                'lang_var' => ilObjCmiXapiGUI::TAB_ID_SETTINGS
            )
        );
        
        return $commands;
    }

    public static function getConditionOperators() : array
    {
        return [
            ilConditionHandler::OPERATOR_FINISHED,
            ilConditionHandler::OPERATOR_FAILED
        ];
    }
    
    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id) : bool
    {
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_FAILED:
                return ilLPStatus::_lookupStatus($a_trigger_obj_id, $a_usr_id) == ilLPStatus::LP_STATUS_FAILED_NUM;
            
            case ilConditionHandler::OPERATOR_FINISHED:
                return ilLPStatus::_hasUserCompleted($a_trigger_obj_id, $a_usr_id);
        }
        
        return false;
    }
}
