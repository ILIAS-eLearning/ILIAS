<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Listing;

use ILIAS\UI\Component\Item;
use ILIAS\UI\Component\Panel\Listing\Group;

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
	 * @param Item\AppointmentItem[]
	 * @param string $async_action link pointing back to target for getting more
	 *        items (e.g. if selected a larger count of days to be displayed)
	 * @return \ILIAS\UI\Component\Panel\Listing\Appointment
	 */
	public function appointment($title, array $items, $async_action);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Standard item lists should be used if possible.
	 * ---
	 * @param string $title Title of the Listing
	 * @param array<Divider|\ILIAS\UI\Component\Item\Item> $items List items
	 * @return \ILIAS\UI\Component\Panel\Listing\Standard
	 */
	public function standard($title, $items);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Divides lists into multiple sublists. Optionally contains a label.
	 * ---
	 * @param string $title Title of the Listing
	 * @return \ILIAS\UI\Component\Panel\Listing\Divider
	 */
	public function divider();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       TODO, this is a further candidate to make use of Listing Panels
	 * ---
	 * @return \ILIAS\UI\Component\Panel\Listing\Repository
	 */
	public function repository();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       TODO, this is a further candidate to make use of Listing Panels
	 * ---
	 * @return \ILIAS\UI\Component\Panel\Listing\Blog
	 */
	public function blog();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       TODO, this is a further candidate to make use of Listing Panels
	 * ---
	 * @return \ILIAS\UI\Component\Panel\Listing\Forum
	 */
	public function forum();
}
