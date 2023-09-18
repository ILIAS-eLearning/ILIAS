<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
     * It will be displayed above the main value percentage display.
     * Example: withMainText('Your Score')
     */
    public function withMainText(string $text): ProgressMeter;

    /**
     * Get main text value
     */
    public function getMainText(): ?string;

    /**
     * Get clone of Progress Meter with required text
     *
     * It will be displayed below the required percentage display.
     * Example: withRequiredText("Minimum Required")
     */
    public function withRequiredText(string $text): ProgressMeter;

    /**
     * Get required text value
     */
    public function getRequiredText(): ?string;
}
