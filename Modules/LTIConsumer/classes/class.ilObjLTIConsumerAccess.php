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
 * Class ilObjLTIConsumer
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilObjLTIConsumerAccess extends ilObjectAccess implements ilConditionHandling
{
    /**
     * @return array<int, array>
     */
    public static function _getCommands() : array
    {
        return array(
            array(
                "permission" => "read",
                "cmd" => "launch",
                "lang_var" => "",
                "default" => true
            ),
            array(
                'permission' => 'write',
                'cmd' => 'ilLTIConsumerSettingsGUI::showSettings',
                'lang_var' => 'settings'
            )
        );
    }

    /**
     * @return string[]
     */
    public static function getConditionOperators() : array
    {
        return [
            ilConditionHandler::OPERATOR_PASSED
        ];
    }
    
    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id) : bool
    {
        if ($a_operator == ilConditionHandler::OPERATOR_PASSED) {
            return ilLPStatus::_hasUserCompleted($a_trigger_obj_id, $a_usr_id);
        }
        
        return false;
    }
}
