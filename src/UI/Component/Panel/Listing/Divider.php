<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Listing;

/**
 * Listing divider
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface Divider extends \ILIAS\UI\Component\Component {
	/**
	 * Set label of divider
	 *
	 * @param string $label label
	 */
	public function withLabel($label);

	/**
	 * Get label of divider
	 *
	 * @return string
	 */
	public function getLabel();
}
