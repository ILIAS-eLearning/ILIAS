<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Listing;

/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      The Appointment Listing Panel is used to displayed a list of
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
	 * @param string $title Title of the Appointement Listing Panel
	 * @param AppointmentItem[] $items Set of Appointements to be displayed
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
	 *      when this appointement takes time, a title, and a little bar on the
	 *      side indicating by color, which calendar holds this appointment.
	 *      They further might contain a description and some meta data as
	 *      key-value pair holding information such as location of contact.
	 *      If there are actions possible to perform on the appointement they
	 *      are listed in a dropdown on the right.
	 *   effect: >
	 *      The description is blended out if larger than two lines.
	 *      On small screen sizes, the description and metadata (except for the
	 *      location) is blended out completely. It is shown once the user
	 *      clicks the "Show More" link displayed if there is hidden content.
	 * ---
	 * @param string $title Title of the Appointment
	 * @param ilDateTime $from Starting point of the appointmen.
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
