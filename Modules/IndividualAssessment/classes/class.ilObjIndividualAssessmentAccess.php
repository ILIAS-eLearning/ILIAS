<?php

require_once 'Services/Object/classes/class.ilObjectAccess.php';
require_once 'Services/Conditions/classes/class.ilConditionHandler.php';
require_once 'Services/Conditions/interfaces/interface.ilConditionHandling.php';
class ilObjIndividualAssessmentAccess extends ilObjectAccess implements ilConditionHandling
{
    /**
     * @inheritdoc
     */
    public static function _getCommands()
    {
        $commands = array(
            array("permission" => "read", "cmd" => "", "lang_var" => "show", "default" => true)
            ,array("permission" => "write", "cmd" => "edit", "lang_var" => "edit", "default" => false)
        );
        return $commands;
    }

    /**
     * ilConditionHandling implementation
     *
     * @inheritdoc
     */
    public static function getConditionOperators()
    {
        include_once './Services/Conditions/classes/class.ilConditionHandler.php';
        return array(
            ilConditionHandler::OPERATOR_PASSED,
            ilConditionHandler::OPERATOR_FAILED
        );
    }

    /**
     * @inheritdoc
     */
    public static function checkCondition($iass_id, $a_operator, $a_value, $a_usr_id)
    {
        require_once 'Modules/IndividualAssessment/classes/LearningProgress/class.ilIndividualAssessmentLPInterface.php';
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_PASSED:
                return ilIndividualAssessmentLPInterface::determineStatusOfMember($iass_id, $a_usr_id)
                    == ilIndividualAssessmentMembers::LP_COMPLETED;
                break;
            case ilConditionHandler::OPERATOR_FAILED:
                return ilIndividualAssessmentLPInterface::determineStatusOfMember($iass_id, $a_usr_id)
                    == ilIndividualAssessmentMembers::LP_FAILED;
            default:
                return false;
        }
        return false;
    }
}
