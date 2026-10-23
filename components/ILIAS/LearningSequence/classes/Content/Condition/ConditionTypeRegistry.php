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

use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\OutputConditionInterface;

final class ConditionTypeRegistry implements ConditionTypes
{
    /**
     * @var ConditionType[]
     */
    private array $condition_types;

    /**
     * @param ConditionType[] $condition_types
     */
    public function __construct(array $condition_types)
    {
        $this->condition_types = $condition_types;
    }

    public function getAllInputConditions(): array
    {
        return $this->filterBy(InputConditionInterface::class);
    }

    public function getAllOutputConditions(): array
    {
        return $this->filterBy(OutputConditionInterface::class);
    }

    public function getAllConditions(): array
    {
        return array_values(array_unique(array_map(
            static fn(ConditionType $type): string => $type->getConditionClass(),
            $this->condition_types
        )));
    }

    public function getConditionByName(string $name_to_find): ?string
    {
        return array_find(
            $this->getAllConditions(),
            static fn(string $class): bool => AbstractCondition::getIdentifierForClass($class) === $name_to_find
        );
    }

    /**
     * @return class-string<AbstractCondition>[]
     */
    private function filterBy(string $interface): array
    {
        return array_values(array_filter(
            $this->getAllConditions(),
            static fn(string $class): bool => is_subclass_of($class, $interface)
        ));
    }
}
