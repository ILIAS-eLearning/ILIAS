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

namespace ILIAS\LearningSequence\Content\Condition\OutputCondition\AlwaysOutputCondition;

use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\OutputConditionInterface;

/**
 * Class AlwaysOutputCondition
 *
 * Bei Always Condition beachten, dass es keine Option gibt eine andere OutputCondition zu wählen. ALWAYS!!!
 */
final class AlwaysOutputCondition extends AbstractCondition implements OutputConditionInterface
{
    protected const string NAME = "always";

    /**
     * @inheritDoc
     */
    public static function migrate(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function check(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function allowMultipleConditionsOfSameType(): bool
    {
        return false;
    }
}
