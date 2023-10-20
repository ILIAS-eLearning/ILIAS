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

namespace ILIAS\MetaData\Paths;

use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Paths\Steps\Step;
use ILIAS\MetaData\Paths\Filters\Filter;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;

class Builder implements BuilderInterface
{
    protected StructureSetInterface $structure;

    public function __construct(StructureSetInterface $structure)
    {
        $this->structure = $structure;
    }

    /**
     * @var Step[]
     */
    protected array $steps = [];

    protected string|StepToken|null $current_step_name = null;

    /**
     * @var Filter[]
     */
    protected array $current_step_filters = [];
    protected bool $current_add_as_first = false;

    protected bool $is_relative = false;
    protected bool $leads_to_one = false;

    public function withRelative(bool $is_relative): BuilderInterface
    {
        $clone = clone $this;
        $clone->is_relative = $is_relative;
        return $clone;
    }

    public function withLeadsToExactlyOneElement(
        bool $leads_to_one
    ): BuilderInterface {
        $clone = clone $this;
        $clone->leads_to_one = $leads_to_one;
        return $clone;
    }

    public function withNextStep(
        string $name,
        bool $add_as_first = false
    ): BuilderInterface {
        return $this->withNextStepFromName(
            $name,
            $add_as_first
        );
    }

    public function withNextStepToSuperElement(bool $add_as_first = false): BuilderInterface
    {
        return $this->withNextStepFromName(
            StepToken::SUPER,
            $add_as_first
        );
    }

    public function withNextStepFromStep(
        StepInterface $next_step,
        bool $add_as_first = false
    ): BuilderInterface {
        $builder = $this->withNextStepFromName($next_step->name(), $add_as_first);
        foreach ($next_step->filters() as $filter) {
            $builder = $builder->withAdditionalFilterAtCurrentStep(
                $filter->type(),
                ...$filter->values()
            );
        }
        return $builder;
    }

    public function withAdditionalFilterAtCurrentStep(
        FilterType $type,
        string ...$values
    ): BuilderInterface {
        if (!isset($this->current_step_name)) {
            throw new \ilMDPathException(
                'Cannot add filter because there is no current step.'
            );
        }
        $clone = clone $this;
        $clone->current_step_filters[] = new Filter(
            $type,
            ...$values
        );
        return $clone;
    }

    /**
     * @throws \ilMDPathException
     */
    public function get(): PathInterface
    {
        $clone = $this->withCurrentStepSaved();
        $path =  new Path(
            $clone->is_relative,
            $clone->leads_to_one,
            ...$clone->steps
        );

        if (!$path->isRelative()) {
            $this->validatePathFromRoot($path);
        }
        return $path;
    }

    /**
     * @throws \ilMDPathException
     */
    protected function validatePathFromRoot(PathInterface $path): void
    {
        $element = $this->structure->getRoot();
        foreach ($path->steps() as $step) {
            $name = $step->name();
            if ($name === StepToken::SUPER) {
                $element = $element->getSuperElement();
            } else {
                $element = $element->getSubElement($name);
            }
            if (is_null($element)) {
                $name = is_string($name) ? $name : $name->value;
                throw new \ilMDPathException(
                    "In the path '" . $path->toString() . "', the step '" . $name . "' is invalid."
                );
            }
        }
    }

    protected function withNextStepFromName(
        string|StepToken $name,
        bool $add_as_first = false
    ): BuilderInterface {
        $clone = $this->withCurrentStepSaved();
        $clone->current_step_name = $name;
        $clone->current_add_as_first = $add_as_first;
        return $clone;
    }

    protected function withCurrentStepSaved(): Builder
    {
        $clone = clone $this;
        if (!isset($clone->current_step_name)) {
            return $clone;
        }

        $new_step = new Step(
            $clone->current_step_name,
            ...$clone->current_step_filters
        );
        if ($clone->current_add_as_first) {
            array_unshift($clone->steps, $new_step);
        } else {
            $clone->steps[] = $new_step;
        }

        $clone->current_step_name = null;
        $clone->current_step_filters = [];
        $clone->current_add_as_first = false;

        return $clone;
    }
}
