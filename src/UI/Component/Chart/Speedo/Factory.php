<?php

/* Copyright (c) 2017 Ralph Dittrich <dittrich@qualitus.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Chart\Speedo;

/**
 * Interface Factory
 * @package ILIAS\UI\Component\Chart\Speedo
 */
interface Factory {

    /**
     * ---
     * description:
     *   purpose: >
     *     The standard speedo is used if the speedo is to be rendered in it's original size, even on mobile devices.
     *   composition: >
     *     The standard speedo is composed of one bar representing a score achieved in relation to a maximum
     *     and a minimum value indicated by some pointer. The diagnostic value is represented by a second
     *     bar below the first one.
     *
     * rules:
     *   composition:
     *     1: Speedos MUST contain a maximum value. It MUST be numeric and represents the maximum score.
     *     2: Speedos MUST contain a score. It MUST be a numeric value between 0 and the maximum. It is represented as the main bar.
     *     3: Speedos SHOULD contain a minimum. It MUST be a numeric value between 0 and the maximum. It represents the minimum score that has to be reached.
     *     4: Speedos MAY contain a diagnostic score. It MUST be a numeric value between 0 and the maximum. It is represented as the second bar.
     *     5: Speedos MAY contain a score text.
     *     6: Speedos MAY contain a goal text.
     * ----
     * @param array $scores => set of numeric values with identifiers: maximum, score, minimum, diagnostic
     * @return \ILIAS\UI\Component\Chart\Speedo\Standard
     */
    public function standard($maximum, $score, $minimum = null, $diagnostic = null);

    /**
     * ---
     * description:
     *   purpose: >
     *     The responsive speedo is mostly identical with the standard speedo.
     *   composition: >
     *     See composition description for standard speedo.
     *   effect: >
     *     On changing screen size they decrease their size including font size in various steps.
     *
     * rules:
     *   composition:
     *     1: See composition rules for standard speedo.
     * ----
     * @param array $scores => set of numeric values with identifiers: maximum, score, minimum, diagnostic
     * @return \ILIAS\UI\Component\Chart\Speedo\Responsive
     */
    public function responsive($maximum, $score, $minimum = null, $diagnostic = null);

    /**
     * ---
     * description:
     *   purpose: >
     *     The mini speedo is used, if it needs to be as small as possible, like in an heading.
     *   composition: >
     *     Other than the standard and responsive speedo it does not allow a diagnostic value. Note
     *     that the achievement of reaching the minimum value is only indicated by color.
     *
     * rules:
     *   composition:
     *     1: Speedos MUST contain a maximum value. It MUST be numeric and represents the maximum score.
     *     2: Speedos MUST contain a score. It MUST be a numeric value between 0 and the maximum. It is represented as the main bar.
     *     3: Speedos SHOULD contain a minimum. It MUST be a numeric value between 0 and the maximum. It represents the minimum score that has to be reached.
     * ----
     * @param array $scores => set of numeric values with identifiers: maximum, score, minimum
     * @return \ILIAS\UI\Component\Chart\Speedo\Mini
     */
    public function mini($maximum, $score, $minimum = null);

}