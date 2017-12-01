<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Chart\Speedo;
use ILIAS\UI\Component\Component;

/**
 * Interface Speedo
 * @package ILIAS\UI\Component\Chart\Speedo
 */
interface Speedo extends Component {

    /**
     * Get goal value
     *
     * This value is used as 100%.
     * This value will always returned "raw" because it is used to calculate the
     * percentage values of score, minimum and diagnostic.
     *
     * @return int|float
     */
    public function getGoal();

    /**
     * Get minimum goal value
     *
     * This value represents the minimum score that users need, to fulfill the objective.
     * If this value is not set, it defaults to 100%.
     *
     * @param bool $getAsPercent Get Value as percentage value or not
     * @return int|float
     */
    public function getMinimum($getAsPercent = true);

    /**
     * Get user score value
     *
     * This value represents the users score. It is rendered as the speedo main bar.
     *
     * @param bool $getAsPercent Get Value as percentage value or not
     * @return int|float
     */
    public function getScore($getAsPercent = true);

    /**
     * Test if a diagnostic score is given
     *
     * This should always be used before you try to get the value.
     *
     * @return bool
     */
    public function hasDiagnostic();
}