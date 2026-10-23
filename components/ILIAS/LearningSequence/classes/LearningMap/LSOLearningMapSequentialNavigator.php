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

namespace ILIAS\LearningSequence\LearningMap;

use ILIAS\LearningSequence\Player\LinearNavigator;

/**
 * Provides navigation rules for sequential learning maps.
 */
class LSOLearningMapSequentialNavigator extends LinearNavigator
{
    /**
     * Determines whether the current item may be left.
     */
    public function canLeave(\LSLearnerItem $current): bool
    {
        $condition = $current->getPostCondition();
        $status = $current->getLearningProgressStatus();

        return match ($condition->getConditionOperator()) {
            \ilLSPostCondition::OPERATOR_ALWAYS => true,
            \ilLSPostCondition::OPERATOR_FAILED => $status === \ilLPStatus::LP_STATUS_FAILED_NUM,
            \ilLSPostCondition::OPERATOR_NOT_FINISHED => $status !== \ilLPStatus::LP_STATUS_COMPLETED_NUM,
            default => $status === \ilLPStatus::LP_STATUS_COMPLETED_NUM,
        };
    }

    /**
     * Determines whether the target item may be entered from the current item.
     */
    public function canEnterFrom(\LSLearnerItem $current, \LSLearnerItem $target): bool
    {
        return $this->canLeave($current);
    }
}
