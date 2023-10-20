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

namespace ILIAS\MetaData\Paths\Navigator;

use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Paths\Steps\NavigatorBridge;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Steps\StepInterface;

abstract class BaseNavigator implements BaseNavigatorInterface
{
    private NavigatorBridge $bridge;

    /**
     * @var BaseElementInterface[]
     */
    private array $elements;

    /**
     * @var StepInterface[]
     */
    private array $previous_steps;

    /**
     * @var StepInterface[]
     */
    private array $remaining_steps;
    private ?StepInterface $current_step = null;
    private bool $leads_to_one;

    public function __construct(
        PathInterface $path,
        BaseElementInterface $start_element,
        NavigatorBridge $bridge
    ) {
        $this->bridge = $bridge;
        $this->previous_steps = [];
        $this->remaining_steps = iterator_to_array($path->steps());
        $this->leadsToOne($path->leadsToExactlyOneElement());
        if ($path->isRelative()) {
            $this->elements = [$start_element];
            return;
        }
        while (!$start_element->isRoot()) {
            $start_element = $start_element->getSuperElement();
            if (!isset($start_element)) {
                throw new \ilMDPathException(
                    'Can not navigate on an invalid metadata set.'
                );
            }
        }
        $this->elements = [$start_element];
    }

    protected function leadsToOne(bool $leads_to_one): void
    {
        $this->leads_to_one = $leads_to_one;
    }

    public function currentStep(): ?StepInterface
    {
        return $this->current_step;
    }

    public function nextStep(): ?BaseNavigatorInterface
    {
        if (empty($this->remaining_steps)) {
            return null;
        }
        $clone = clone $this;

        $clone->elements = iterator_to_array($clone->bridge->getNextElementsByStep(
            $clone->remaining_steps[0],
            ...$clone->elements
        ));
        $clone->previous_steps[] = $clone->current_step;
        $clone->current_step = $clone->remaining_steps[0];
        array_shift($clone->remaining_steps);
        return $clone;
    }

    public function previousStep(): ?BaseNavigatorInterface
    {
        if(empty($this->previous_steps)) {
            return null;
        }
        $clone = clone $this;
        $clone->elements = iterator_to_array($clone->bridge->getParents(...$clone->elements));
        $next_step = array_pop($clone->previous_steps);
        array_unshift($clone->remaining_steps, $clone->current_step);
        $clone->current_step = $next_step;
        return $clone;
    }

    public function hasPreviousStep(): bool
    {
        return count($this->previous_steps) > 0;
    }

    public function hasNextStep(): bool
    {
        return count($this->remaining_steps) > 0;
    }

    public function hasElements(): bool
    {
        return count($this->elements) > 0;
    }

    /**
     * @return BaseElementInterface[]
     * @throws \ilMDPathException
     */
    public function elementsAtFinalStep(): \Generator
    {
        $clone = clone $this;
        while ($next = $clone->nextStep()) {
            $clone = $next;
        }
        yield from $clone->elements();
    }

    /**
     * @throws \ilMDPathException
     */
    public function lastElementAtFinalStep(): ?BaseElementInterface
    {
        $return = null;
        foreach ($this->elementsAtFinalStep() as $element) {
            $return = $element;
        }
        return $return;
    }

    /**
     * @return BaseElementInterface[]
     * @throws \ilMDPathException
     */
    public function elements(): \Generator
    {
        $this->checkLeadsToOne();
        yield from $this->elements;
    }

    /**
     * @throws \ilMDPathException
     */
    public function lastElement(): ?BaseElementInterface
    {
        $return = null;
        foreach ($this->elements() as $element) {
            $return = $element;
        }
        return $return;
    }

    /**
     * @throws \ilMDPathException
     */
    protected function checkLeadsToOne(): void
    {
        if (!$this->leads_to_one) {
            return;
        }
        if (count($this->elements) !== 1) {
            throw new \ilMDPathException(
                'Path should lead to exactly one element but does not.'
            );
        }
    }
}
