<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use \ILIAS\UI\Component\Counter\Counter;

/**
 * This describes a tag(-button).
 */
interface Tag extends Button
{
    const REL_VERYLOW = 'verylow';
    const REL_LOW = 'low';
    const REL_MID = 'mid';
    const REL_HIGH = 'high';
    const REL_VERYHIGH = 'veryhigh';

    /**
     * Set relevance of Tag (e.g. to distinguish visually)
     *
     * @param	string	 $relevance
     * @throws 	\InvalidArgumentException 	if $relevance not in rel-constatnts
     * @return	Tag
     */
    public function withRelevance($relevance);

    /**
     * Get the relevance of the Tag.
     *
     * @return	string
     */
    public function getRelevance();

    /**
     * Set a fix background-color.
     *
     * @param	Color $col
     * @return	Tag
     */
    public function withBackgroundColor(\ILIAS\Data\Color $col);

    /**
     * Get the fix background-color.
     *
     * @return	Color|null
     */
    public function getBackgroundColor();

    /**
     * Set the fix foreground-color
     *
     * @param	Color $col
     * @return	Tag
     */
    public function withForegroundColor(\ILIAS\Data\Color $col);

    /**
     * Get the fix foreground-color.
     *
     * @return	Color|null
     */
    public function getForegroundColor();

    /**
     * Replace or set additional classes.
     * Additional classes will be replaced in calling this function.
     *
     * @param	string[] $classes
     * @return	Tag
     */
    public function withClasses($classes);

    /**
     * Get additional classes.
     *
     * @return	string[]
     */
    public function getClasses();
}
