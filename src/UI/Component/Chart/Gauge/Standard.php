<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Chart\Gauge;

/**
 * Interface Standard
 * @package ILIAS\UI\Component\Chart\Gauge
 */
interface Standard extends Gauge {

    /**
     * Get comparision value
     *
     * This value is represented as the second gauge bar.
     *
     * @return int|float
     */
    public function getComparision();

    /**
     * Get clone of Gauge with main text
     *
     * It will be displayed above the main value percentage display.
     * Example: withMainText('Your Score')
     *
     * @param string $text
     * @return \ILIAS\UI\Component\Chart\Gauge\Gauge
     */
    public function withMainText($text);

    /**
     * Get main text value
     *
     * @return string
     */
    public function getMainText();

    /**
     * Get clone of Gauge with required text
     *
     * It will be displayed below the required percentage display.
     * Example: withRequiredText("Minimum Required")
     *
     * @param string $text
     * @return \ILIAS\UI\Component\Chart\Gauge\Gauge
     */
    public function withRequiredText($text);

    /**
     * Get required text value
     *
     * @return string
     */
    public function getRequiredText();


}