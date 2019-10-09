<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing\Workflow;

/**
 * This describes a Workflow.
 */
interface Workflow extends \ILIAS\UI\Component\Component
{

    /**
     * Get the title of this workflow.
     *
     * @return 	string
     */
    public function getTitle();

    /**
     * The step at this position is set to active.
     *
     * @param 	int 	$active
     * @throws InvalidArgumentException 	if $active exceeds the amount of steps
     * @return 	Workflow
     */
    public function withActive($active);

    /**
     * This is the index of the active step.
     *
     * @return 	int
     */
    public function getActive();

    /**
     * Get the steps of this workflow.
     *
     * @return Step[]
     */
    public function getSteps();
}
