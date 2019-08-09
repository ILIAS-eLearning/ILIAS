<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Chart\ProgressMeter;

/**
 * Interface Standard
 * @package ILIAS\UI\Component\Chart\ProgressMeter
 */
interface Standard extends ProgressMeter
{

    /**
     * Get comparison value
     *
     * This value is represented as the second progress meter bar.
     *
     * @return int|float|null
     */
    public function getComparison();

    /**
     * Get clone of Progress Meter with main text
     *
     * It will be displayed above the main value percentage display.
     * Example: withMainText('Your Score')
     *
     * @param string $text
     * @return \ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter
     */
    public function withMainText($text);

    /**
     * Get main text value
     *
     * @return string|null
     */
    public function getMainText();

    /**
     * Get clone of Progress Meter with required text
     *
     * It will be displayed below the required percentage display.
     * Example: withRequiredText("Minimum Required")
     *
     * @param string $text
     * @return \ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter
     */
    public function withRequiredText($text);

    /**
     * Get required text value
     *
     * @return string|null
     */
    public function getRequiredText();
}
