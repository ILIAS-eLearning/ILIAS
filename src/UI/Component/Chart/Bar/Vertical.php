<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 ********************************************************************
 */

namespace ILIAS\UI\Component\Chart\Bar;

interface Vertical extends Bar
{
    public function getIndexAxis() : string;

    /**
     * Customize the y-axis.
     *
     * @param bool     $is_displayed  Should the y-axis be displayed?
     * @param string   $position      Bar::POSITION_XYZ (Default is POSITION_LEFT)
     * @param float    $step_size     Step size between each label on the y-axis. Only relevant if labels are numeric.
     *                                Default is 1.0.
     * @param bool     $begin_at_zero If true, bars start always at y=0. If false, bars start at the lowest number
     *                                of a dataset. Default is true.
     * @param int|null $min           Numeric label values below this number will not be shown on the y-axis. If not
     *                                defined, the chart determines the minimum automatically based on the Dataset.
     * @param int|null $max           Numeric label values above this number will not be shown on the y-axis. If not
     *                                defined, the chart determines the maximum automatically based on the Dataset.
     * @return self
     */
    public function withCustomYAxis(
        bool $is_displayed,
        ?string $position = self::POSITION_LEFT,
        ?float $step_size = 1.0,
        ?bool $begin_at_zero = true,
        ?int $min = null,
        ?int $max = null
    ) : self;

    /**
     * Reset the y-axis to default.
     */
    public function withResetYAxis() : self;

    public function getYAxis() : array;
}
