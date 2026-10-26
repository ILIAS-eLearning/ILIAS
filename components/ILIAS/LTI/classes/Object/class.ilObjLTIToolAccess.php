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

declare(strict_types=1);

/**
 * Access checks of the repository object type lti. An object completed by a user can also be the
 * precondition of another one.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilObjLTIToolAccess extends ilObjectAccess implements ilConditionHandling
{
    /**
     * @return array
     */
    public static function getConditionOperators(): array
    {
        return [ilConditionHandler::OPERATOR_PASSED];
    }

    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id): bool
    {
        return $a_operator === ilConditionHandler::OPERATOR_PASSED
            && ilLPStatus::_hasUserCompleted($a_trigger_obj_id, $a_usr_id);
    }

    /**
     * The learning progress only exists for a tool that reports results.
     */
    public static function hasLearningProgressAccess(ilObjLTITool $object): bool
    {
        return $object->getTool()->hasOutcome() && ilLearningProgressAccess::checkAccess($object->getRefId());
    }

    /**
     * The statements are shown to everybody when the object says so, otherwise to who may read the outcomes.
     */
    public static function hasStatementsAccess(ilObjLTITool $object): bool
    {
        return $object->getUseXapi() && ($object->isStatementsReportEnabled() || self::hasOutcomesAccess($object));
    }

    /**
     * The ranking is shown to everybody when the object enables it, otherwise to who may read the outcomes.
     */
    public static function hasRankingAccess(ilObjLTITool $object): bool
    {
        return $object->getUseXapi() && ($object->getHighscoreEnabled() || self::hasOutcomesAccess($object));
    }

    public static function hasOutcomesAccess(ilObjLTITool $object): bool
    {
        global $DIC;

        return $DIC->access()->checkAccess('read_outcomes', '', $object->getRefId());
    }

    /**
     * @return array
     */
    public static function _getCommands(): array
    {
        return [
            [
                'permission' => 'read',
                'cmd' => ilObjLTIToolGUI::CMD_LAUNCH,
                'lang_var' => '',
                'default' => true,
            ],
            [
                'permission' => 'write',
                'cmd' => ilLTIObjectSettingsGUI::class . '::' . ilLTIObjectSettingsGUI::CMD_SHOW,
                'lang_var' => 'settings',
            ],
        ];
    }
}
