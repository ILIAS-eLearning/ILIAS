<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Chart\ProgressMeter;

use ILIAS\UI\Component\Component;

/**
 * Interface ProgressMeter
 * @package ILIAS\UI\Component\Chart\ProgressMeter
 */
interface ProgressMeter extends Component
{

    /**
     * Get maximum value
     *
     * This value is used as 100%.
     * This value will always returned "raw" because it is used to calculate the
     * percentage values of main, required and comparison.
     *
     * @return int|float
     */
    public function getMaximum();

    /**
     * Get required value
     *
     * This value represents the required amount that is needed, to fulfill the objective.
     * If this value is not set, it defaults to the maximum.
     *
     * @return int|float|null
     */
    public function getRequired();

    /**
     * Get main value
     *
     * This value is represented as the main progress meter bar.
     *
     * @return int|float
     */
    public function getMainValue();
}
