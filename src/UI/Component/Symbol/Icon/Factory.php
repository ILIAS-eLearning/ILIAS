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

namespace ILIAS\UI\Component\Symbol\Icon;

/**
 * This is how a factory for icons looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *    Standard Icons represent ILIAS Objects, Services or ideas.
     *   composition: >
     *     An Icon is rendered as image-tag.
     *   rivals:
     *     Custom Icon: Custom Icons are constructed with a path to an (uploaded) image.
     *
     * rules:
     *   style:
     *     1: CSS-Filters MAY be used for Standard Icons to manipulate the stroke to fit the context.
     * ---
     * @param   string $name
     * @param   string $label
     * @param   string $size
     * @return 	\ILIAS\UI\Component\Symbol\Icon\Standard
     **/
    public function standard(
        string $name,
        string $label,
        string $size = 'small',
        bool $is_disabled = false
    ): Standard;

    /**
     * ---
     * description:
     *   purpose: >
     *     ILIAS allows users to upload icons for repository objects.
     *     Those, in opposite to the standard icons, need to be constructed with
     *     a path.
     *   composition: >
     *     An Icon is rendered as image-tag.
     *   rivals:
     *     Standard Icon: Standard Icons MUST be used for core-objects.
     * rules:
     *   usage:
     *     1: Custom Icons MAY still use an abbreviation.
     *   style:
     *     1: Custom Icons MUST use SVG as graphic.
     *     2: >
     *       Icons MUST have a transparent background so they could be put on
     *       all kinds of backgrounds.
     *     3: >
     *       Images used for Custom Icons SHOULD have equal width and height
     *       (=be quadratic) in order not to be distorted.
     * ---
     * @param   string $icon_path
     * @param   string $label
     * @param   string $size
     * @return 	\ILIAS\UI\Component\Symbol\Icon\Custom
     **/
    public function custom(
        string $icon_path,
        string $label,
        string $size = 'small',
        bool $is_disabled = false
    ): Custom;
}
