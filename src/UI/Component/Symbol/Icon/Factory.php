<?php declare(strict_types=1);
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
     *     The outlined version is the silhouette of the Standard Icon.
     *     This outlined version therefore attracts less attention.
     *   rivals:
     *     Custom Icon: Custom Icons are constructed with a path to an (uploaded) image.
     * rules:
     *   usage:
     *     1: If they are layered upon a picture, the outlined version MUST be used.
     *     2:  >
     *        In any other case, the outlined version SHOULD be used except for scenarios
     *        where the icon should draw much more attention.
     *     3: For drawing much more attention the filled version SHOULD be used.
     *   style:
     *     1: CSS-Filters MAY be used for Standard Icons in their outlined version to manipulate the stroke to fit the context.
     *     2: >
     *        In their outlined version, Standard Icons MUST only use white as color for the stroke, to make filter easily
     *        applicable.
     *   accessibility:
     *     1: Icons MUST have alt-tags.
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
    ) : Standard;

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
     *
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
    ) : Custom;
}
