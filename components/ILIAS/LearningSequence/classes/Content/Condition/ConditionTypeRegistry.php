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
use LogicException;
use ReflectionClass;

/**
 * Knows all contributed condition types.
 *
 * The contributions are validated when they are used for the first time: a
 * contributed class which cannot be used as condition is a programming error
 * and must not be skipped silently, since the conditions of a missing type
 * could not be evaluated anymore.
 */
final class ConditionTypeRegistry implements ConditionTypes
{
    /**
     * @var ConditionType[]
     */
    private array $condition_types;

    /**
     * @var class-string<AbstractCondition>[]|null
     */
    private ?array $condition_classes = null;

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

    /**
     * @throws LogicException if a contributed class cannot be used as condition
     */
    public function getAllConditions(): array
    {
        return $this->condition_classes ??= $this->validate(array_values(array_unique(array_map(
            static fn(ConditionType $type): string => $type->getConditionClass(),
            $this->condition_types
        ))));
    }

    public function getConditionByName(string $name_to_find): ?string
    {
        return array_find(
            $this->getAllConditions(),
            static fn(string $class): bool => AbstractCondition::getIdentifierForClass($class) === $name_to_find
        );
    }

    /**
     * Makes sure every class is an instantiable condition which is either an
     * input or an output condition, and that no two classes share the name
     * under which the type is stored in lso_condition_types.
     *
     * @param string[] $classes
     * @return class-string<AbstractCondition>[]
     * @throws LogicException
     */
    private function validate(array $classes): array
    {
        $classes_by_name = [];
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                throw new LogicException("Contributed condition class '{$class}' does not exist.");
            }
            if (!is_subclass_of($class, AbstractCondition::class)) {
                throw new LogicException(
                    "Contributed condition class '{$class}' does not extend " . AbstractCondition::class . '.'
                );
            }
            if (!(new ReflectionClass($class))->isInstantiable()) {
                throw new LogicException("Contributed condition class '{$class}' cannot be instantiated.");
            }
            if (!is_subclass_of($class, InputConditionInterface::class)
                && !is_subclass_of($class, OutputConditionInterface::class)
            ) {
                throw new LogicException(
                    "Contributed condition class '{$class}' is neither an input nor an output condition."
                );
            }

            $name = AbstractCondition::getIdentifierForClass($class);
            if (isset($classes_by_name[$name])) {
                throw new LogicException(
                    "Contributed condition classes '{$classes_by_name[$name]}' and '{$class}' "
                    . "share the name '{$name}'."
                );
            }
            $classes_by_name[$name] = $class;
        }

        return $classes;
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
