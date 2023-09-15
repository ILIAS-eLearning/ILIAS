<?php

declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

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
    public function passed(): string
    {
        return ilConditionHandler::OPERATOR_PASSED;
    }

    /**
     * Finished operator
     */
    public function finished(): string
    {
        return ilConditionHandler::OPERATOR_FINISHED;
    }

    /**
     * Not finished operator
     */
    public function notFinished(): string
    {
        return ilConditionHandler::OPERATOR_NOT_FINISHED;
    }

    /**
     * Not member operator
     */
    public function notMember(): string
    {
        return ilConditionHandler::OPERATOR_NOT_MEMBER;
    }

    /**
     * Failed operator
     */
    public function failed(): string
    {
        return ilConditionHandler::OPERATOR_FAILED;
    }

    /**
     * Learning progress (passed) operator. Maybe renamed in the future
     */
    public function learningProgress(): string
    {
        return ilConditionHandler::OPERATOR_LP;
    }

    /**
     * Accredited or passed operator
     */
    public function accreditedOrPassed(): string
    {
        return ilConditionHandler::OPERATOR_ACCREDITED_OR_PASSED;
    }
}
