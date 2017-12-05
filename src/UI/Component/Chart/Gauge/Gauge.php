<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Chart\Gauge;
use ILIAS\UI\Component\Component;

/**
 * Interface Gauge
 * @package ILIAS\UI\Component\Chart\Gauge
 */
interface Gauge extends Component {

    /**
     * Get maximum value
     *
     * This value is used as 100%.
     * This value will always returned "raw" because it is used to calculate the
     * percentage values of main, required and comparision.
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
     * @return int|float
     */
    public function getRequired();

    /**
     * Get main value
     *
     * This value is represented as the main gauge bar.
     *
     * @return int|float
     */
    public function getMainValue();

    /**
     * Test if a comparison value is given
     *
     * This test is used to decide whether one or two bars are rendered.
     *
     * @return bool
     */
    public function hasComparisonValue();
}