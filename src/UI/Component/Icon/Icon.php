<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Icon;

/**
 * This describes how a icon could be modified during construction of UI.
 */
interface Icon extends \ILIAS\UI\Component\Component {

	/**
	 * Get the CSS-class of the icon.
	 *
	 * @return string
	 */
	public function getCSSClass();

	/**
	 * Get the aria-label of this icon.
	 *
	 * @return string
	 */
	public function getAriaLabel();

	/**
	 * Set the abbreviation for this icon.
	 *
	 * @param string $abbreviation
	 * @return \ILIAS\UI\Component\Icon\Icon
	 */
	public function withAbbreviation($abbreviation);

	/**
	 * Get the abbreviation of this icon.
	 *
	 * @return string
	 */
	public function getAbbreviation();

	/**
	 * Set the size for this icon.
	 * Size can be'small','medium' or 'large'.
	 *
	 * @param string $size
	 * @return \ILIAS\UI\Component\Icon\Icon
	 */
	public function withSize($size);

	/**
	 * Get the size of this icon.
	 *
	 * @return string
	 */
	public function getSize();

}
