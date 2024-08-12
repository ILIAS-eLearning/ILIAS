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
    /**
     * @return array<int, mixed[]>
     */
    public static function _getCommands(): array
    {
        return [
            [
                "permission" => "read",
                "cmd" => "launch",
                "lang_var" => "show",
                "default" => true
            ],
            [
                'permission' => 'write',
                'cmd' => 'ilCmiXapiSettingsGUI::show',
                'lang_var' => ilObjCmiXapiGUI::TAB_ID_SETTINGS
            ]
        ];
    }

    /**
     * @return string[]
     */
    public static function getConditionOperators(): array
    {
        return [
            ilConditionHandler::OPERATOR_FINISHED,
            ilConditionHandler::OPERATOR_FAILED
        ];
    }

    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id): bool
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
