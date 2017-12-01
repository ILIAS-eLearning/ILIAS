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
     *   purpose: The standard speedo is used if the speedo is to be rendered in it's original size.
     *
     * rules:
     *   composition:
     *     1: Speedos MUST contain a goal value. It MUST be numeric and MAY be a score equal to 100%.
     *     2: Speedos MUST contain a score. It MUST be numeric and MAY be equal to 0 or a equal to a value between 0% and 100% .
     *     3: Speedos SHOULD contain a minimum goal. It MUST be numeric and MAY be equal to 0 or a equal to a value between 0% and 100%.
     *     4: Speedos MAY contain a diagnostic score. It MUST be numeric and MAY be equal to 0 or a equal to a value between 0% and 100%.
     *     5: Speedos MAY contain a score text. It MUST be a string.
     *     6: Speedos MAY contain a goal text. It MUST be a string.
     * ----
     * @param array $scores => set of numeric values with identifiers: goal, score, minimum, diagnostic
     * @return \ILIAS\UI\Component\Chart\Speedo\Standard
     */
    public function standard(array $scores);

    /**
     * ---
     * description:
     *   purpose: The responsive speedo is used, if it needs to resize by the window size.
     *
     * rules:
     *   composition:
     *     1: Speedos MUST contain a goal value. It MUST be numeric and MAY be a score equal to 100%.
     *     2: Speedos MUST contain a score. It MUST be numeric and MAY be equal to 0 or a equal to a value between 0% and 100% .
     *     3: Speedos SHOULD contain a minimum goal. It MUST be numeric and MAY be equal to 0 or a equal to a value between 0% and 100%.
     *     4: Speedos MAY contain a diagnostic score. It MUST be numeric and MAY be equal to 0 or a equal to a value between 0% and 100%.
     *     5: Speedos MAY contain a score text. It MUST be a string.
     *     6: Speedos MAY contain a goal text. It MUST be a string.
     * ----
     * @param array $scores => set of numeric values with identifiers: goal, score, minimum, diagnostic
     * @return \ILIAS\UI\Component\Chart\Speedo\Responsive
     */
    public function responsive(array $scores);

    /**
     * ---
     * description:
     *   purpose: The mini speedo is used, if it needs to be as small as possible.
     *
     * rules:
     *   composition:
     *     1: Speedos MUST contain a goal value. It MUST be numeric and MAY be a score equal to 100%.
     *     2: Speedos MUST contain a score. It MUST be numeric and MAY be equal to 0 or a equal to a value between 0% and 100% .
     *     3: Speedos SHOULD contain a minimum goal. It MUST be numeric and MAY be equal to 0 or a equal to a value between 0% and 100%.
     * ----
     * @param array $scores => set of numeric values with identifiers: goal, score, minimum
     * @return \ILIAS\UI\Component\Chart\Speedo\Mini
     */
    public function mini(array $scores);

}