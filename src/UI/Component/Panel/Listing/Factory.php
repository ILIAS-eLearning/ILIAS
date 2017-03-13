<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Listing;

/**
 * Interface Factory
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      The Appointment Listing Panel is used to display a list of
	 *      appointments grouped by day.
	 *   composition: >
	 *      This Listing is composed of a set of Appointment Items, a dropdown
	 *      to select the amount of days to be shown in the Listing and a
	 *      Filter enabling to search for an appointment carrying a specific
	 *      title.
	 *
	 * rules:
	 *   wording:
	 *      1: >
	 *       The title SHOULD contain the period being displayed in the
	 *       listing.
	 * ---
	 * @param string $title Title of the Appointment Listing Panel
	 * @param AppointmentItem[] $items Set of Appointments to be displayed
	 * @param string $async_action link pointing back to target for getting more
	 *        items (e.g. if selected a larger count of days to be displayed)
	 * @return \ILIAS\UI\Component\Panel\Listing\Appointment
	 */
	public function appointment($title,$items,$async_action);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      The Appointment Item is used to summarize one single
	 *      appointment in a list.
	 *   composition: >
	 *      The Appointment Item is composed of a period of time, indicating
	 *      when this appointment takes place, a title, and a little bar on the
	 *      side indicating by color, which calendar holds this appointment.
	 *      They further might contain a description and some properties as
	 *      key-value pair holding information such as location or contact.
	 *      If there are actions possible to perform on the appointment they
	 *      are listed in a dropdown on the right.
	 *   effect: >
	 *      The title may open the details of the appointment on click in a
	 *      round-trip modal, if such details are available.
	 *      The description is blended out if larger than two lines.
	 *      On small screen sizes, the description and properties (except for the
	 *      location) is blended out completely. It is shown once the user
	 *      clicks the "Show More" link displayed if there is hidden content.
	 * ---
	 * @param string $title Title of the Appointment
	 * @param ilDateTime $from Starting point of the appointment.
	 * @param ilDateTime $to End point of the appointment.
	 * @param string Color of the calendar containing the item as color code
	 *        (hex).
	 * @return \ILIAS\UI\Component\Panel\Listing\AppointmentItem
	 */
	public function appointmentItem($title, $from, $to, $color);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Todo, this is a further candiate to make use of Listing Panels
	 * ---
	 * @return \ILIAS\UI\Component\Panel\Listing\Repository
	 */
	public function repository();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Todo, this is a further candiate to make use of Listing Panels
	 * ---
	 * @return \ILIAS\UI\Component\Panel\Listing\RepositoryItem
	 */
	public function repositoryItem();
}
