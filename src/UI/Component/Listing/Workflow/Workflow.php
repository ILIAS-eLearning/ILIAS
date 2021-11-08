<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing\Workflow;

use ILIAS\UI\Component\Component;

/**
 * This describes a Workflow.
 */
interface Workflow extends Component
{
    /**
     * Get the title of this workflow.
     */
    public function getTitle() : string;

    /**
     * The step at this position is set to active.
     *
     * @throws \InvalidArgumentException 	if $active exceeds the amount of steps
     */
    public function withActive(int $active) : Workflow;

    /**
     * This is the index of the active step.
     */
    public function getActive() : int;

    /**
     * Get the steps of this workflow.
     *
     * @return Step[]
     */
    public function getSteps() : array;
}
