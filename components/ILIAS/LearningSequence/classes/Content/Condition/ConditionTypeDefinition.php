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

namespace ILIAS\LearningSequence\Content\Condition;

/**
 * A condition type which is defined by the name of its condition class.
 */
final readonly class ConditionTypeDefinition implements ConditionType
{
    /**
     * @param class-string<AbstractCondition> $condition_class
     */
    public function __construct(
        private string $condition_class
    ) {
    }

    public function getConditionClass(): string
    {
        return $this->condition_class;
    }
}
