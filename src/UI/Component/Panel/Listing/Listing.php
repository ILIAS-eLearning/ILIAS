<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Listing;

/**
 * Interface Appointment
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface Listing extends \ILIAS\UI\Component\Component {
	/**
	 * Gets the title of the appointment listing
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Get item list
	 *
	 * @return \ILIAS\UI\Component\Item\Group[]
	 */
	public function getItemGroups();
}
