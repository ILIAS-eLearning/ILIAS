<?php declare(strict_types=1);

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
     * It will be displayed above the main value percentage display.
     * Example: withMainText('Your Score')
     */
    public function withMainText(string $text) : ProgressMeter;

    /**
     * Get main text value
     */
    public function getMainText() : ?string;

    /**
     * Get clone of Progress Meter with required text
     *
     * It will be displayed below the required percentage display.
     * Example: withRequiredText("Minimum Required")
     */
    public function withRequiredText(string $text) : ProgressMeter;

    /**
     * Get required text value
     */
    public function getRequiredText() : ?string;
}
