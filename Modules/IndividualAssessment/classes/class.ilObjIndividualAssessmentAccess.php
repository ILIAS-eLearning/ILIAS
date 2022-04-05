<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

class ilObjIndividualAssessmentAccess extends ilObjectAccess implements ilConditionHandling
{
    /**
     * @inheritdoc
     */
    public static function _getCommands() : array
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
    public static function getConditionOperators() : array
    {
        return [
            ilConditionHandler::OPERATOR_PASSED,
            ilConditionHandler::OPERATOR_FAILED
        ];
    }

    /**
     * @inheritdoc
     */
    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id) : bool
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
