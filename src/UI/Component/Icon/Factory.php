<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Component\Icon;
/**
 * This is how a factory for icons looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *    Standard Icons represent ILIAS Objects.
	 *   composition: >
	 *     A Standard Icon is displayed as a block-element with a background-graphic.
	 *     By default, a fallback icon will be rendered; this is until a
	 *     background image is defined in the icon's CSS-class.
	 *   rivals:
	 *     1: Custom Icons are constructed with a path to an (uploaded) image.
	 * ---
	 *
	 * @param   string $name
	 * @param   string $aria_label
	 * @param   string $size
	 * @return 	\ILIAS\UI\Component\Icon\Standard
	 **/
	public function standard($name, $aria_label, $size='small');

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     ILIAS allows users to upload icons for repository objects.
	 *     Those, in opposite to the standard icons, need to be constructed with
	 *     a path.
	 *   composition: >
	 *     Instead of setting a background image via CSS-class, an image-tag is
	 *     contained in the icons's div.
	 *   rivals:
	 *     1: Standard Icons MUST be used for core-objects.
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
	 * @param   string $aria_label
	 * @param   string $size
	 * @return 	\ILIAS\UI\Component\Icon\Custom
	 **/
	public function custom($icon_path, $aria_label, $size='small');

}
