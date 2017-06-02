<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

/**
 * Interface Appointment
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface AppointmentItem extends Item {
	/**
	 * Get the starting point of the appointment.
	 * @return \ilDateTime
	 */
	public function getFrom();

	/**
	 * Get the ending point of the appointment.
	 * @return \ilDateTime
	 */
	public function getEnd();

	/**
	 * Get the color of the calendar containing the item as color code (hex).
	 * @return string
	 */
	public function getColor();
}
