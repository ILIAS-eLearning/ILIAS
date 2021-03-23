<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing\Workflow;

use ILIAS\UI\Component\Component;

/**
 * This describes a Workflow Step
 */
interface Step extends Component
{
    public const AVAILABLE = 1;
    public const NOT_AVAILABLE = 2;
    public const NOT_ANYMORE = 3;
    public const ACTIVE = 4;

    public const NOT_STARTED = 1;
    public const IN_PROGRESS = 2;
    public const SUCCESSFULLY = 3;
    public const UNSUCCESSFULLY = 4;

    /**
     * Get the label of this step.
     *
     * @return 	string
     */
    public function getLabel();

    /**
     * Get the description of this step.
     *
     * @return 	string
     */
    public function getDescription();


    /**
     * Get the availabilty status of this step.
     *
     * @return 	mixed
     */
    public function getAvailability();

    /**
     * Get a step like this with completion status according to parameter.
     *
     * @param 	mixed 	$status
     * @return 	Step
     */
    public function withAvailability($status);

    /**
     * Get the status of this step.
     *
     * @return 	mixed
     */
    public function getStatus();

    /**
     * Get a step like this with completion status according to parameter.
     *
     * @param 	mixed 	$status
     * @return 	Step
     */
    public function withStatus($status);

    /**
     * Get the action of this Step.
     *
     * @return	null | \ILIAS\UI\Component\Signal  | string
     */
    public function getAction();
}
