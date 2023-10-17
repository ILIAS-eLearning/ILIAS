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

namespace ILIAS\MetaData\Manipulator\Path;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;

class PathConditionsCollection implements PathConditionsCollectionInterface
{
    protected PathFactory $path_factory;
    /**
     * @var StepInterface[] $history
     */
    protected array $history;
    /**
     * @var PathInterface[] $path_conditions
     */
    protected array $path_conditions;
    protected bool $is_relative;
    protected bool $leads_to_exactly_one_element;

    public function __construct(PathFactory $path_factory, PathInterface $path_interface)
    {
        $this->path_factory = $path_factory;
        $this->history = [];
        $this->path_conditions = [];
        $this->is_relative = $path_interface->isRelative();
        $this->leads_to_exactly_one_element = $path_interface->leadsToExactlyOneElement();
        $this->buildPathConditionsDict($path_interface);
    }

    public function getConditionPathByStepName(string $name): PathInterface
    {
        if ($this->hasPathConditionWithName($name)) {
            return $this->path_conditions[$name];
        }
        return $this->buildPathFromSteps([], true, false);
    }

    public function getPathWithoutConditions(): PathInterface
    {
        return $this->buildPathFromSteps(
            $this->history,
            $this->is_relative,
            $this->leads_to_exactly_one_element
        );
    }

    /**
     * @param StepInterface[] $steps
     */
    protected function buildPathFromSteps(array $steps, bool $is_relative, bool $leads_to_exactly_one): PathInterface
    {
        $builder = $this->path_factory->custom()
            ->withRelative($is_relative)
            ->withLeadsToExactlyOneElement($leads_to_exactly_one);
        foreach ($steps as $step) {
            $builder = $builder->withNextStepFromStep($step, false);
        }
        return $builder->get();
    }

    protected function isStepUpCommand(StepInterface $step): bool
    {
        return $step->name() === StepToken::SUPER;
    }

    protected function addStepToHistory(StepInterface $step): void
    {
        $this->history[] = $step;
    }

    protected function buildPathConditionsDict(PathInterface $path_interface): void
    {
        /**
         * @var StepInterface $step
         */
        foreach ($path_interface->steps() as $step) {
            $this->addStepToHistory($step);
            if ($this->isStepUpCommand($step)) {
                $this->buildPathCondition();
            }
        }
    }

    protected function hasPathConditionWithName(string $name): bool
    {
        return array_key_exists($name, $this->path_conditions);
    }

    protected function buildPathCondition(): void
    {
        /**
         * @var StepInterface $step
         * @var StepInterface $step_up
         * @var StepInterface $step_condition
         * @var StepInterface $target
         * @var PathInterface $nested_condition_path
         * @var PathInterface $existing_condition_path
         * @var StepInterface[] $steps
         */
        $step_up = array_pop($this->history);
        $step_condition = array_pop($this->history);
        $target = $this->history[count($this->history) - 1];
        $steps = [$step_condition, $step_up];

        // nested condition path found, wrap
        if ($this->hasPathConditionWithName($step_condition->name())) {
            $nested_condition_path = $this->path_conditions[$step_condition->name()];
            array_pop($steps); // Remove step up before adding steps
            foreach ($nested_condition_path->steps() as $step) {
                $steps[] = $step;
            }
            $steps[] = $step_up;
            unset($this->path_conditions[$step_condition->name()]);
        }

        // target has path, append
        if ($this->hasPathConditionWithName($target->name())) {
            $existing_condition_path = $this->path_conditions[$target->name()];
            $frontConditions = [];
            foreach ($existing_condition_path->steps() as $step) {
                $frontConditions[] = $step;
            }
            array_unshift($steps, ...$frontConditions);
            unset($this->path_conditions[$target->name()]);
        }

        $this->path_conditions[$target->name()] = $this->buildPathFromSteps(
            $steps,
            true,
            false
        );
    }
}
