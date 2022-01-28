<?php declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use \ILIAS\Data\Color;

/**
 * This describes a tag(-button).
 */
interface Tag extends Button
{
    public const REL_VERYLOW = 'verylow';
    public const REL_LOW = 'low';
    public const REL_MID = 'mid';
    public const REL_HIGH = 'high';
    public const REL_VERYHIGH = 'veryhigh';

    /**
     * Set relevance of Tag (e.g. to distinguish visually)
     *
     * @throws 	\InvalidArgumentException 	if $relevance not in rel-constants
     */
    public function withRelevance(string $relevance) : Tag;

    /**
     * Get the relevance of the Tag.
     */
    public function getRelevance() : string;

    /**
     * Set a fix background-color.
     */
    public function withBackgroundColor(Color $col) : Tag;

    /**
     * Get the fix background-color.
     */
    public function getBackgroundColor() : ?Color;

    /**
     * Set the fix foreground-color
     */
    public function withForegroundColor(Color $col) : Tag;

    /**
     * Get the fix foreground-color.
     */
    public function getForegroundColor() : ?Color;

    /**
     * Replace or set additional classes.
     * Additional classes will be replaced in calling this function.
     *
     * @param	string[] $classes
     */
    public function withClasses(array $classes) : Tag;

    /**
     * Get additional classes.
     *
     * @return	string[]
     */
    public function getClasses() : array;
}
