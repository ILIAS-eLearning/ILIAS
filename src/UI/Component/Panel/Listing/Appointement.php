<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Listing;

/**
 * Interface Appointment
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface Appointment extends \ILIAS\UI\Component\Component {
	/**
	 * Gets the title of the appointment listing
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Get the link pointing back to target for getting more
	 * items (e.g. if selected a larger count of days to be displayed)
	 *
	 * @return string
	 */
	public function getAction();

	/**
	 * Get the Appointment Items the list is holding
	 *
	 * @return AppointmentItem[]
	 */
	public function getItems();
}
