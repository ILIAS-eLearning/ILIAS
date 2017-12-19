<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Chart\ProgressMeter;

/**
 * Interface Factory
 * @package ILIAS\UI\Component\Chart\ProgressMeter
 */
interface Factory {

    /**
     * ---
     * description:
     *   purpose: >
     *     The standard progressmeter is usually the tool of choice. It is to be used, if the speedo needs to adapt
     *     it's size depending on the screen size due to a more limited amount of space.
     *   composition: >
     *     The standard progressmeter is composed of one bar representing a value achieved in relation to a maximum
     *     and a required value indicated by some pointer. The comparison value is represented by a second
     *     bar below the first one. Also the percentage values of main and required are shown as text.
     *   effect: >
     *     On changing screen size they decrease their size including font size in various steps.
     *
     * rules:
     *   composition:
     *     1: Standard ProgressMeters MAY contain a comparison value. It MUST be a numeric value between 0 and the maximum. It is represented as the second bar.
     *     2: Standard ProgressMeters MAY contain a main value text.
     *     3: Standard ProgressMeters MAY contain a required value text.
     * ----
     * @param int|float $maximum          Maximum reachable value.
     * @param int|float $main             Main value to be displayed by main bar.
     * @param int|float|null $required    Required value to be reached by main value.
     * @param int|float|null $comparison Comparison value to be displayed by second bar.
     * @return \ILIAS\UI\Component\Chart\ProgressMeter\Standard
     */
    public function standard($maximum, $main, $required = null, $comparison = null);

    /**
     * ---
     * description:
     *   purpose: >
     *     The fixedSize progressmeter is mostly identical with the standard. It is used if
     *     the progressmeter is to be rendered in it's original size, even on mobile devices.
     *   composition: >
     *     See composition description for standard progressmeter.
     *
     * rules:
     *   composition:
     *     1: See composition rules for standard progressmeter.
     * ----
     * @param int|float $maximum          Maximum reachable value.
     * @param int|float $main             Main value to be displayed by main bar.
     * @param int|float|null $required    Required value to be reached by main value.
     * @param int|float|null $comparison Comparison value to be displayed by second bar.
     * @return \ILIAS\UI\Component\Chart\ProgressMeter\FixedSize
     */
    public function fixedSize($maximum, $main, $required = null, $comparison = null);

    /**
     * ---
     * description:
     *   purpose: >
     *     The mini progressmeter is used, if it needs to be as small as possible, like in an heading. It
     *     is used to display only a single progress or performance indicator.
     *   composition: >
     *     Other than the standard and responsive progressmeter it does not allow a comparison value and
     *     only displays a single bar. It also does not display any text. Note that the achievement
     *     of reaching the required value is only indicated by color.
     *
     * rules:
     *   composition:
     *     1: See composition rules for progressmeter.
     * ----
     * @param int|float $maximum          Maximum reachable value.
     * @param int|float $main             Main value to be displayed by main bar.
     * @param int|float|null $required    Required value to be reached by main value.
     * @return \ILIAS\UI\Component\Chart\ProgressMeter\Mini
     */
    public function mini($maximum, $main, $required = null);

}