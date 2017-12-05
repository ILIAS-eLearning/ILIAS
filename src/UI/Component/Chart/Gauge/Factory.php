<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Chart\Gauge;

/**
 * Interface Factory
 * @package ILIAS\UI\Component\Chart\Gauge
 */
interface Factory {

    /**
     * ---
     * description:
     *   purpose: >
     *     The standard gauge is usually the tool of choice. It is to be used, if the speedo needs to adapt
     *     it's size depending on the screen size due to a more limited amount of space.
     *   composition: >
     *     The standard gauge is composed of one bar representing a value achieved in relation to a maximum
     *     and a required value indicated by some pointer. The comparision value is represented by a second
     *     bar below the first one. Also the percentage values of main and required are shown as text.
     *   effect: >
     *     On changing screen size they decrease their size including font size in various steps.
     *
     * rules:
     *   composition:
     *     1: Standard Gauges MAY contain a comparision value. It MUST be a numeric value between 0 and the maximum. It is represented as the second bar.
     *     2: Standard Gauges MAY contain a main value text.
     *     3: Standard Gauges MAY contain a required value text.
     * ----
     * @param int|float $maximum          Maximum reachable value.
     * @param int|float $main             Main value to be displayed by main bar.
     * @param int|float|null $required    Required value to be reached by main value.
     * @param int|float|null $comparision Comparision value to be displayed by second bar.
     * @return \ILIAS\UI\Component\Chart\Gauge\Standard
     */
    public function standard($maximum, $main, $required = null, $comparision = null);

    /**
     * ---
     * description:
     *   purpose: >
     *     The fixedSize gauge is mostly identical with the standard. It is used if
     *     the gauge is to be rendered in it's original size, even on mobile devices.
     *   composition: >
     *     See composition description for standard gauge.
     *
     * rules:
     *   composition:
     *     1: See composition rules for standard gauge.
     * ----
     * @param int|float $maximum          Maximum reachable value.
     * @param int|float $main             Main value to be displayed by main bar.
     * @param int|float|null $required    Required value to be reached by main value.
     * @param int|float|null $comparision Comparision value to be displayed by second bar.
     * @return \ILIAS\UI\Component\Chart\Gauge\FixedSize
     */
    public function fixedSize($maximum, $main, $required = null, $comparision = null);

    /**
     * ---
     * description:
     *   purpose: >
     *     The mini gauge is used, if it needs to be as small as possible, like in an heading. It
     *     is used to display only a single progress or performance indicator.
     *   composition: >
     *     Other than the standard and responsive gauge it does not allow a comparision value and
     *     only displays a single bar. It also does not display any text. Note that the achievement
     *     of reaching the required value is only indicated by color.
     *
     * rules:
     *   composition:
     *     1: See composition rules for gauge.
     * ----
     * @param int|float $maximum          Maximum reachable value.
     * @param int|float $main             Main value to be displayed by main bar.
     * @param int|float|null $required    Required value to be reached by main value.
     * @return \ILIAS\UI\Component\Chart\Gauge\Mini
     */
    public function mini($maximum, $main, $required = null);

}