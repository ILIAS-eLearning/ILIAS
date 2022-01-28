<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Workflow;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use InvalidArgumentException;

/**
 * Class Workflow
 * @package ILIAS\UI\Implementation\Component\Listing\Workflow
 */
abstract class Workflow implements C\Listing\Workflow\Workflow
{
    use ComponentHelper;

    private string $title;
    private array $steps;
    private int $active;

    /**
     * Workflow constructor.
     * @param 	Step[] 	$steps
     */
    public function __construct(string $title, array $steps)
    {
        $types = array('string',Step::class);
        $this->checkArgListElements("steps", $steps, $types);
        $this->title = $title;
        $this->steps = $steps;
        $this->active = 0;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function withActive(int $active) : C\Listing\Workflow\Workflow
    {
        if ($active < 0 || $active > $this->getAmountOfSteps() - 1) {
            throw new InvalidArgumentException("active must be be within the amount of steps", 1);
        }
        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActive() : int
    {
        return $this->active;
    }

    /**
     * Return the amount of steps of this workflow.
     */
    public function getAmountOfSteps() : int
    {
        return count($this->steps);
    }

    /**
     * @inheritdoc
     */
    public function getSteps() : array
    {
        return $this->steps;
    }
}
