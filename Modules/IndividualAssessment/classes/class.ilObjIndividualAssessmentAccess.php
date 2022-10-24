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

class ilObjIndividualAssessmentAccess extends ilObjectAccess implements ilConditionHandling
{
    /**
     * @inheritdoc
     */
    public static function _getCommands(): array
    {
        return [
            ["permission" => "read", "cmd" => "", "lang_var" => "show", "default" => true],
            ["permission" => "write", "cmd" => "edit", "lang_var" => "settings", "default" => false]
        ];
    }

    /**
     * ilConditionHandling implementation
     *
     * @inheritdoc
     */
    public static function getConditionOperators(): array
    {
        return [
            ilConditionHandler::OPERATOR_PASSED,
            ilConditionHandler::OPERATOR_FAILED
        ];
    }

    /**
     * @inheritdoc
     */
    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id): bool
    {
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_PASSED:
                return ilIndividualAssessmentLPInterface::determineStatusOfMember($a_trigger_obj_id, $a_usr_id)
                    == ilIndividualAssessmentMembers::LP_COMPLETED;
            case ilConditionHandler::OPERATOR_FAILED:
                return ilIndividualAssessmentLPInterface::determineStatusOfMember($a_trigger_obj_id, $a_usr_id)
                    == ilIndividualAssessmentMembers::LP_FAILED;
            default:
                return false;
        }
    }
}
