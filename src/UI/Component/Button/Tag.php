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

namespace ILIAS\UI\Component\Button;

use ILIAS\Data\Color;

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
    public function withRelevance(string $relevance): Tag;

    /**
     * Get the relevance of the Tag.
     */
    public function getRelevance(): string;

    /**
     * Set a fix background-color.
     */
    public function withBackgroundColor(Color $col): Tag;

    /**
     * Get the fix background-color.
     */
    public function getBackgroundColor(): ?Color;

    /**
     * Set the fix foreground-color
     */
    public function withForegroundColor(Color $col): Tag;

    /**
     * Get the fix foreground-color.
     */
    public function getForegroundColor(): ?Color;

    /**
     * Replace or set additional classes.
     * Additional classes will be replaced in calling this function.
     *
     * @param	string[] $classes
     */
    public function withClasses(array $classes): Tag;

    /**
     * Get additional classes.
     *
     * @return	string[]
     */
    public function getClasses(): array;
}
