<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Chart\ProgressMeter;

/**
 * Interface Factory
 * @package ILIAS\UI\Component\Chart\ProgressMeter
 */
interface Factory
{

    /**
     * ---
     * description:
     *   purpose: >
     *     The Standard Progress Meter is usually the tool of choice. The Progress Meter informs users about their
     *     Progress compared to a the required maximum.
     *   composition: >
     *     The Standard Progress Meter is composed of one bar representing a value achieved in relation to a maximum
     *     and a required value indicated by some pointer. The comparison value is represented by a second
     *     bar below the first one. Also the percentage values of main and required are shown as text.
     *   effect: >
     *     On changing screen size they decrease their size including font size in various steps.
     *
     * rules:
     *   composition:
     *     1: Standard Progress Meters MAY contain a comparison value. If there is a comparison value it MUST be a numeric value between 0 and the maximum. It is represented as the second bar.
     *     2: Standard Progress Meters MAY contain a main value text.
     *     3: Standard Progress Meters MAY contain a required value text.
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
     *     The Fixed Size Progress Meter ensures that the element is rendered exactly as set regardless of the screen size.
     *   composition: >
     *     See composition description for Standard Progress Meter.
     *
     * rules:
     *   composition:
     *     1: See composition rules for Standard Progress Meter.
     * ----
     * @param int|float $maximum          Maximum reachable value.
     * @param int|float $main             Main value to be displayed by main bar.
     * @param int|float|null $required    Required value to be reached by main value.
     * @param int|float|null $comparison  Comparison value to be displayed by second bar.
     * @return \ILIAS\UI\Component\Chart\ProgressMeter\FixedSize
     */
    public function fixedSize($maximum, $main, $required = null, $comparison = null);

    /**
     * ---
     * description:
     *   purpose: >
     *     The Mini Progress Meter is used, if it needs to be as small as possible, like in an heading. It
     *     is used to display only a single Progress or performance indicator.
     *   composition: >
     *     Other than the Standard and Fixed Size Progress Meter it does not allow a comparison value and
     *     only displays a single bar. It also does not display any text.
     *
     * rules:
     *   composition:
     *     1: See composition rules for Progress Meter.
     * ----
     * @param int|float $maximum          Maximum reachable value.
     * @param int|float $main             Main value to be displayed by main bar.
     * @param int|float|null $required    Required value to be reached by main value.
     * @return \ILIAS\UI\Component\Chart\ProgressMeter\Mini
     */
    public function mini($maximum, $main, $required = null);
}
