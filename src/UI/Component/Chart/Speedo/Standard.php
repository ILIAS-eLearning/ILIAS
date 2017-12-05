<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Chart\Speedo;

/**
 * Interface Standard
 * @package ILIAS\UI\Component\Chart\Speedo
 */
interface Standard extends Speedo {

    /**
     * Get diagnostic score value
     *
     * This value represents the diagnostic score. It is rendered as the second speedo bar.
     *
     * @param bool $getAsPercent Get Value as percentage value or not
     * @return int|float
     */
    public function getDiagnostic($getAsPercent = true);

    /**
     * Get clone of Speedo with score text
     *
     * It will be displayed above the score percentage display.
     * Example: withTxtScore('Your Score')
     *
     * @param string $txt
     * @return \ILIAS\UI\Component\Chart\Speedo\Speedo
     */
    public function withTxtScore($txt);

    /**
     * Get score text value
     *
     * @return string
     */
    public function getTxtScore();

    /**
     * Get clone of Speedo with goal text
     *
     * It will be displayed below the minimum percentage display.
     * Example: withTxtGoal("Minimum Goal")
     *
     * @param string $txt
     * @return \ILIAS\UI\Component\Chart\Speedo\Speedo
     */
    public function withTxtGoal($txt);

    /**
     * Get goal text value
     *
     * @return string
     */
    public function getTxtGoal();


}