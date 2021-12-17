<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Currently wraps standard operator constants. Please note that plugins may use their own
 * operators not being represented here.
 * We may generalize the "not" attribute and move from strings to objects in the future.
 * @author  killing@leifos.de
 * @ingroup ServiceConditions
 */
class ilConditionOperatorFactory
{

    /**
     * Passed operator
     */
    public function passed() : string
    {
        return ilConditionHandler::OPERATOR_PASSED;
    }

    /**
     * Finished operator
     */
    public function finished() : string
    {
        return ilConditionHandler::OPERATOR_FINISHED;
    }

    /**
     * Not finished operator
     */
    public function notFinished() : string
    {
        return ilConditionHandler::OPERATOR_NOT_FINISHED;
    }

    /**
     * Not member operator
     */
    public function notMember() : string
    {
        return ilConditionHandler::OPERATOR_NOT_MEMBER;
    }

    /**
     * Failed operator
     */
    public function failed() : string
    {
        return ilConditionHandler::OPERATOR_FAILED;
    }

    /**
     * Learning progress (passed) operator. Maybe renamed in the future
     */
    public function learningProgress() : string
    {
        return ilConditionHandler::OPERATOR_LP;
    }

    /**
     * Accredited or passed operator
     */
    public function accreditedOrPassed() : string
    {
        return ilConditionHandler::OPERATOR_ACCREDITED_OR_PASSED;
    }
}
