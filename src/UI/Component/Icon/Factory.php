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
	 *
	 *   composition: >
	 *     A Standard Icon is displayed as a block-element with a background-graphic.
	 *     By default, a fallback icon will be rendered based on an abbreviation;
	 *     this is until a background image is defined in the icon's CSS-class.
	 *   rivals:
	 *     1: >
	 *       Custom Icon
	 * rules:
	 *   usage:
	 *     1: >
	 *   style:
	 *
	 *   accessibility:
	 *     1:
	 *   wording:
	 *     1: >
	 *
	 *     2: >
	 *
	 * ---
	 *
	 * @param   string $class
	 * @param   string $aria_label
	 * @param   string $size
	 * @return 	\ILIAS\UI\Component\Icon\Standard
	 **/
	public function standard($class, $aria_label, $size='small');

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     ILIAS allows users to upload icons for certain repository objects.
	 *     Those, in opposite to the standard icons, need to be constructed with
	 *     a path.
	 *   composition: >
	 *   effect: >
	 *   rivals:
	 *     1: >
	 *       Standard Icons
	 *     2: >
	 * rules:
	 *   usage:
	 *     1: >
	 *   style:
	 *   accessibility:
	 *   wording:
	 *
	 * ---
	 *
	 * @param   string $icon_path
	 * @param   string $aria_label
	 * @param   string $size
	 * @return 	\ILIAS\UI\Component\Icon\Custom
	 **/
	public function custom($icon_path, $aria_label, $size='small');

}
